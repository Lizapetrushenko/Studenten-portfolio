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
        $user->delete();
        return back()->with('status', 'Account verwijderd.');
    }
}
