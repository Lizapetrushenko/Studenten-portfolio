<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use App\Models\Group;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $groups = $request->user()->teachingGroups();
        return view('teacher.index', [
            'groupCount' => (clone $groups)->count(),
            'studentCount' => User::where('role', 'student')->count(),
            'workProcessCount' => (clone $groups)->with('educationProgram.workProcesses')->get()->sum(fn ($group) => $group->educationProgram?->workProcesses->count() ?? 0),
        ]);
    }

    public function groups(Request $request): View
    {
        $groups = $request->user()->teachingGroups()->withCount('students')->orderBy('name')->get();
        $baseGroups = Group::whereNotNull('lifecycle_status')
            ->withCount('students')
            ->orderByRaw("CASE lifecycle_status WHEN 'nieuw' THEN 0 WHEN 'herkansen' THEN 1 WHEN 'geslaagd' THEN 2 WHEN 'weg' THEN 3 ELSE 4 END")
            ->get();
        $availableStudents = User::where('role', 'student')->orderBy('last_name')->orderBy('first_name')->get();
        return view('teacher.groups', compact('groups', 'baseGroups', 'availableStudents'));
    }

    public function workProcesses(Request $request): View
    {
        $groups = $request->user()->teachingGroups()->with('workProcesses')->orderBy('name')->get();
        return view('teacher.work-processes', compact('groups'));
    }

    public function createGroup(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $group = $request->user()->teachingGroups()->create($data);
        $program = \App\Models\EducationProgram::where('name', 'Software Developer')->firstOrFail();
        $group->update(['education_program_id' => $program->id]);
        return back()->with('status', 'Groep aangemaakt.');
    }

    public function addStudent(Request $request, Group $group): RedirectResponse
    {
        $this->authorizeGroup($request, $group);
        abort_if($group->lifecycle_status !== null, 403, 'Studenten kunnen niet rechtstreeks aan een statusgroep worden toegevoegd.');
        $data = $request->validate(['student_id' => ['required', 'exists:users,id']]);
        $student = User::findOrFail($data['student_id']);
        abort_unless($student->isStudent(), 422, 'Alleen studenten kunnen aan een groep worden toegevoegd.');
        $student->groups()->syncWithoutDetaching([$group->id]);
        if (! $student->group_id) $student->update(['group_id' => $group->id]);
        return back()->with('status', 'Student aan de groep toegevoegd.');
    }

    public function showGroup(Request $request, Group $group): View
    {
        $this->authorizeGroup($request, $group);
        $group->load(['students.portfolios.evidence', 'students.reviewers', 'workProcesses']);
        $availableStudents = User::where('role', 'student')->whereDoesntHave('groups', fn ($query) => $query->whereKey($group->id))->orderBy('last_name')->orderBy('first_name')->get();
        return view('teacher.group', compact('group', 'availableStudents'));
    }

    public function groupProgress(Request $request, Group $group): View
    {
        $this->authorizeGroup($request, $group);
        $filters = $request->validate([
            'zoek' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:niet ingeleverd,niet akkoord,ingeleverd,akkoord,nakijken'],
        ]);
        $search = trim($filters['zoek'] ?? '');
        $status = $filters['status'] ?? '';
        $statuses = ['niet ingeleverd' => 'Nog niet ingeleverd', 'niet akkoord' => 'Niet akkoord', 'ingeleverd' => 'Ingeleverd', 'akkoord' => 'Akkoord', 'nakijken' => 'Nakijken'];
        $group->load(['workProcesses', 'students.portfolios.evidence', 'students.groups.workProcesses', 'students.reviewers']);
        $processes = $group->isTeacherLinked()
            ? $group->workProcesses
            : $group->students->flatMap(fn ($student) => $student->groups->filter(fn ($studentGroup) => $studentGroup->isTeacherLinked())->flatMap->workProcesses)->unique('number')->sortBy('number')->values();
        $statusOf = fn ($item) => array_key_exists(mb_strtolower(trim($item->status ?? '')), $statuses) ? mb_strtolower(trim($item->status)) : 'niet ingeleverd';
        $rows = $group->students->sortBy(fn ($student) => mb_strtolower($student->displayName()))
            ->filter(fn ($student) => $search === '' || str_contains(mb_strtolower($student->displayName().' '.$student->name), mb_strtolower($search)))
            ->map(function ($student) use ($processes, $status, $statusOf, $request) {
                $portfolio = $student->portfolios->first();
                $evidence = ($portfolio?->evidence ?? collect())->filter(fn ($item) => $processes->contains(fn ($process) => (string) $process->number === (string) $item->work_process))
                    ->filter(fn ($item) => $status === '' || $statusOf($item) === $status);
                return ['student' => $student, 'portfolio' => $portfolio, 'evidence' => $evidence->groupBy('work_process'), 'count' => $evidence->count(), 'canReview' => $student->groups->contains('teacher_id', $request->user()->id) || $student->reviewers->contains('id', $request->user()->id)];
            })->filter(fn ($row) => $status === '' || $row['count'] > 0)->values();

        return view('teacher.group-progress', compact('group', 'processes', 'rows', 'search', 'status', 'statuses'));
    }
    public function updateGroup(Request $request, Group $group): RedirectResponse
    {
        $this->authorizeGroup($request, $group);
        abort_if($group->lifecycle_status !== null, 403, 'Statusgroepen kunnen niet worden aangepast.');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);
        if ($request->boolean('remove_photo') && $group->photo_path) {
            Storage::disk('public')->delete($group->photo_path);
            $group->photo_path = null;
        }
        if ($request->hasFile('photo')) {
            if ($group->photo_path) Storage::disk('public')->delete($group->photo_path);
            $group->photo_path = $request->file('photo')->store('groups', 'public');
        }
        $group->name = $data['name'];
        $group->save();
        return back()->with('status', 'Groepsgegevens opgeslagen.');
    }

    public function showGroupSettings(Request $request, Group $group): View
    {
        $this->authorizeGroup($request, $group);
        abort_if($group->lifecycle_status !== null, 404);
        $group->load('workProcesses');
        return view('teacher.group-settings', compact('group'));
    }

    public function showPortfolio(Request $request, User $student): View
    {
        abort_unless($student->isStudent(), 404);
        $student->load(['groups.workProcesses', 'reviewers']);
        $portfolio = $student->portfolios()->with('evidence')->firstOrFail();
        $workProcesses = $student->groups->filter(fn ($group) => $group->isTeacherLinked())->flatMap->workProcesses->unique('number')->sortBy('number');
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        $teacherGroups = $request->user()->teachingGroups()->orderBy('name')->get();
        $canReview = $student->groups()->where('teacher_id', $request->user()->id)->exists() || $student->reviewers()->whereKey($request->user()->id)->exists();
        return view('teacher.portfolio', compact('student', 'portfolio', 'workProcesses', 'teachers', 'teacherGroups', 'canReview'));
    }

    public function showStudent(Request $request, User $student): View
    {
        abort_unless($student->isStudent(), 404);
        $student->load(['groups', 'reviewers', 'portfolios.evidence']);
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        $groups = Group::orderBy('name')->get();
        return view('teacher.student-profile', compact('student', 'teachers', 'groups'));
    }

    public function reviewEvidence(Request $request, Evidence $evidence): RedirectResponse
    {
        $student = $evidence->portfolio->user;
        abort_unless($student->isStudent() && ($student->groups()->where('teacher_id', $request->user()->id)->exists() || $student->reviewers()->whereKey($request->user()->id)->exists()), 404);
        abort_if($evidence->status === 'idee', 404, 'Ideeën zijn alleen zichtbaar voor de student.');
        abort_if($evidence->status === 'akkoord', 422, 'Een akkoord bewijsstuk kan niet opnieuw worden beoordeeld.');
        $data = $request->validate([
            'status' => ['required', 'in:akkoord,niet akkoord,nakijken'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);
        if (array_key_exists('feedback', $data) && $data['feedback'] !== $evidence->feedback) {
            $data['feedback_author_name'] = filled($data['feedback']) ? $request->user()->displayName() : null;
        }
        $evidence->update($data);
        return back()->with('status', 'Beoordeling opgeslagen.');
    }

    public function students(Request $request): View
    {
        $students = User::where('role', 'student')
            ->with(['groups', 'reviewers'])
            ->orderBy('last_name')->orderBy('first_name')->get();
        return view('teacher.students', compact('students'));
    }

    public function updateStudent(Request $request, User $student): RedirectResponse
    {
        abort_unless($student->isStudent(), 404);
        $data = $request->validate([
            'group_ids' => ['required', 'array', 'min:1'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
            'reviewer_ids' => ['required', 'array', 'min:2'],
            'reviewer_ids.*' => ['integer', 'distinct', Rule::exists('users', 'id')->where('role', 'teacher')],
            'study_type' => ['required', 'in:BOL,BBL,Flex'],
        ], [
            'group_ids.required' => 'Selecteer minimaal één groep.',
            'group_ids.min' => 'Selecteer minimaal één groep.',
            'reviewer_ids.required' => 'Selecteer minimaal twee beoordelaars.',
            'reviewer_ids.min' => 'Selecteer minimaal twee beoordelaars.',
            'reviewer_ids.array' => 'Selecteer beoordelaars uit de lijst.',
        ]);
        $selectedGroupIds = array_map('intval', $data['group_ids'] ?? []);
        $selectedStatusGroups = Group::whereIn('id', $selectedGroupIds)->whereNotNull('lifecycle_status')->get();
        $selectedStatusGroupIds = $selectedStatusGroups->modelKeys();
        abort_if(count($selectedStatusGroupIds) > 1, 422, 'Kies maximaal één statusgroep.');
        $selectedGroups = Group::whereIn('id', $selectedGroupIds)->whereNull('lifecycle_status')->pluck('id')->all();
        abort_if($selectedGroups === [], 422, 'Selecteer minimaal één gewone lesgroep.');
        $currentStatusGroupId = $student->groups()->whereNotNull('lifecycle_status')->value('groups.id');
        $statusGroupIdsToKeep = $selectedStatusGroupIds ?: ($currentStatusGroupId ? [$currentStatusGroupId] : []);
        $reviewerIds = array_unique(array_map('intval', $data['reviewer_ids']));
        abort_if(User::whereIn('id', $reviewerIds)->where('role', '!=', 'teacher')->exists(), 422, 'Beoordelaars moeten docenten zijn.');
        abort_if(count($reviewerIds) < 2, 422, 'Kies minimaal twee beoordelaars.');

        $student->groups()->sync(array_unique([...$selectedGroups, ...$statusGroupIdsToKeep]));
        $student->reviewers()->sync($reviewerIds);
        $student->update(['group_id' => $selectedGroups[0], 'study_type' => $data['study_type'], 'leerweg' => strtoupper($data['study_type'])]);
        $student->portfolios()->update(['study' => $data['study_type']]);
        return redirect()->route('teacher.students.show', $student)->with('status', 'Studentgegevens opgeslagen.');
    }

    public function removeStudent(Request $request, User $student): RedirectResponse
    {
        abort_unless($student->isStudent() && $student->hasLifecycleGroup('weg'), 404);
        foreach ($student->portfolios()->with('evidence')->get() as $portfolio) {
            foreach ($portfolio->evidence as $item) {
                if ($item->file_path) {
                    Storage::disk('local')->delete($item->file_path);
                    Storage::disk('public')->delete($item->file_path);
                }
            }
        }
        $student->delete();
        return redirect()->route('teacher.students.index')->with('status', 'Student uit het systeem verwijderd.');
    }

    private function authorizeGroup(Request $request, Group $group): void
    {
        abort_unless($group->lifecycle_status !== null || $group->teacher_id === $request->user()->id, 404);
    }
}
