<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'teachers' => User::where('role', 'teacher')->withCount('teachingGroups')->orderBy('name')->get(),
            'students' => User::where('role', 'student')->with(['groups', 'reviewers'])->orderBy('last_name')->orderBy('first_name')->get(),
            'groups' => Group::with(['teacher', 'students'])->withCount('students')->orderBy('name')->get(),
        ]);
    }

    public function createTeacher(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        User::create([...$data, 'username' => strtolower(str_replace(' ', '-', $data['name'])) . '-' . substr(md5($data['email']), 0, 6), 'role' => 'teacher', 'email_verified_at' => now(), 'password' => Hash::make($data['password'])]);
        return back()->with('status', 'Docent toegevoegd.');
    }

    public function showTeacher(User $teacher): View
    {
        abort_unless($teacher->isTeacher(), 404);
        $teacher->load('teachingGroups');
        $nameParts = preg_split('/\s+/', trim($teacher->name), 2);
        $firstName = $teacher->first_name ?: ($nameParts[0] ?? '');
        $lastName = $teacher->last_name ?: ($nameParts[1] ?? '');

        return view('admin.teacher', compact('teacher', 'firstName', 'lastName'));
    }

    public function showStudent(User $student): View
    {
        abort_unless($student->isStudent(), 404);
        $student->load(['groups', 'reviewers']);

        return view('admin.student', compact('student'));
    }

    public function createGroup(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'teacher_id' => ['nullable', 'exists:users,id']]);
        if (!empty($data['teacher_id'])) abort_unless(User::whereKey($data['teacher_id'])->where('role', 'teacher')->exists(), 422);
        $group = Group::create($data);
        foreach (['Afstemmen en plannen', 'Ontwerpen', 'Realisatie', 'Testen', 'Verbetervoorstellen', 'Projectteam', 'Presenteren', 'Evalueer'] as $index => $name) {
            $group->workProcesses()->create(['number' => $index + 1, 'name' => $name]);
        }
        return back()->with('status', 'Groep aangemaakt.');
    }

    public function showGroup(Group $group): View
    {
        $group->load(['teacher', 'students' => fn ($query) => $query->orderBy('last_name')->orderBy('first_name')]);
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        return view('admin.group', compact('group', 'teachers'));
    }

    public function updateGroup(Request $request, Group $group): RedirectResponse
    {
        abort_if($group->lifecycle_status !== null, 403, 'Vaste statusgroepen kunnen niet worden aangepast.');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);
        if (!empty($data['teacher_id'])) abort_unless(User::whereKey($data['teacher_id'])->where('role', 'teacher')->exists(), 422);
        if ($request->boolean('remove_photo') && $group->photo_path) {
            Storage::disk('public')->delete($group->photo_path);
            $group->photo_path = null;
        }
        if ($request->hasFile('photo')) {
            if ($group->photo_path) Storage::disk('public')->delete($group->photo_path);
            $group->photo_path = $request->file('photo')->store('groups', 'public');
        }
        $group->name = $data['name'];
        $group->teacher_id = $data['teacher_id'] ?? null;
        $group->save();
        return back()->with('status', 'Groepsgegevens opgeslagen.');
    }

    public function assignStudent(Request $request, User $student): RedirectResponse
    {
        abort_unless($student->role === 'student', 404);
        $data = $request->validate(['group_ids' => ['nullable', 'array'], 'group_ids.*' => ['integer', 'exists:groups,id']]);
        $ids = $data['group_ids'] ?? [];
        $statusIds = Group::whereNotNull('lifecycle_status')->whereIn('id', $ids)->pluck('id')->all();
        abort_if(count($statusIds) > 1, 422, 'Een student kan maar één statusgroep tegelijk hebben.');
        if (!$statusIds) {
            $statusIds = $student->groups()->whereNotNull('lifecycle_status')->pluck('groups.id')->all();
            if (!$statusIds) $statusIds = [Group::where('lifecycle_status', 'nieuw')->value('id')];
        }
        $ids = array_values(array_unique([...$ids, ...$statusIds]));
        $student->groups()->sync($ids);
        $student->update(['group_id' => $ids[0] ?? null]);
        return back()->with('status', 'Groepslidmaatschappen opgeslagen.');
    }

    public function removeUser(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422, 'Je kunt je eigen account niet verwijderen.');
        abort_if($user->isAdmin() && User::where('role', 'admin')->count() <= 1, 422, 'De laatste admin kan niet worden verwijderd.');
        foreach ($user->portfolios()->with('evidence')->get() as $portfolio) {
            foreach ($portfolio->evidence as $evidence) {
                if ($evidence->file_path) {
                    Storage::disk('local')->delete($evidence->file_path);
                    Storage::disk('public')->delete($evidence->file_path);
                }
            }
        }
        $section = $user->isStudent() ? 'students' : 'teachers';
        $user->delete();
        return redirect()->route('admin.dashboard', ['section' => $section])->with('status', 'Account verwijderd.');
    }
}
