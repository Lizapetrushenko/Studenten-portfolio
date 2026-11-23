<?php
namespace App\Http\Controllers;
use App\Models\EducationProgram;
use App\Models\CoreTask;
use App\Models\WorkProcess;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EducationSettingsController extends Controller
{
    public function index(Request $request)
    {
        $programs = EducationProgram::withCount('workProcesses')->orderBy('id')->get();
        $program = $request->filled('program') ? EducationProgram::findOrFail($request->query('program')) : EducationProgram::where('name', 'Software Developer')->firstOrFail();
        $program->load(['coreTasks.workProcesses', 'workProcesses']);
        $groups = Group::whereNull('lifecycle_status')->with('educationProgram')->withCount('students')->orderBy('name')->get();
        return view('admin.settings', compact('programs', 'program', 'groups'));
    }
    public function storeProgram(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:education_programs,code'],
            'name' => ['required', 'string', 'max:120', 'unique:education_programs,name'],
        ]);
        $program = EducationProgram::create($data);
        return redirect()->route('admin.settings', ['program' => $program->id])->with('status', 'Opleiding toegevoegd.');
    }
    public function updateProgram(Request $request, EducationProgram $program)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100', Rule::unique('education_programs')->ignore($program->id)],
            'name' => ['required', 'string', 'max:120', Rule::unique('education_programs')->ignore($program->id)],
        ]);
        // The standard program stays available for automatic registration and new groups.
        if ($program->name === 'Software Developer' && $data['name'] !== $program->name) abort(422, 'Software Developer is de standaardopleiding en kan niet worden hernoemd.');
        $program->update($data);
        return back()->with('status', 'Opleiding opgeslagen.');
    }
    public function savePrograms(Request $request)
    {
        $data = $request->validate([
            'programs' => ['required', 'array', 'min:1'],
            'programs.*.id' => ['nullable', 'integer', 'exists:education_programs,id'],
            'programs.*.code' => ['required', 'string', 'max:100'],
            'programs.*.name' => ['required', 'string', 'max:120'],
            'current_program' => ['nullable', 'integer', 'exists:education_programs,id'],
        ]);

        $errors = [];
        $seenCodes = [];
        $seenNames = [];
        $seenIds = [];
        foreach ($data['programs'] as $index => $row) {
            $rules = [
                'code' => ['required', 'string', 'max:100', Rule::unique('education_programs', 'code')->ignore($row['id'] ?? null)],
                'name' => ['required', 'string', 'max:120', Rule::unique('education_programs', 'name')->ignore($row['id'] ?? null)],
            ];
            $validator = Validator::make($row, $rules);
            foreach ($validator->errors()->messages() as $field => $messages) {
                foreach ($messages as $message) $errors["programs.$index.$field"] = $message;
            }
            $codeKey = mb_strtolower(trim((string) ($row['code'] ?? '')));
            if ($codeKey !== '' && isset($seenCodes[$codeKey])) $errors["programs.$index.code"] = 'Deze opleidingscode staat meer dan één keer in de lijst.';
            $seenCodes[$codeKey] = true;
            $nameKey = mb_strtolower(trim((string) ($row['name'] ?? '')));
            if ($nameKey !== '' && isset($seenNames[$nameKey])) $errors["programs.$index.name"] = 'Deze opleidingsnaam staat meer dan één keer in de lijst.';
            $seenNames[$nameKey] = true;
            if (! empty($row['id'])) {
                if (isset($seenIds[$row['id']])) $errors["programs.$index.id"] = 'Deze opleiding staat meer dan één keer in de lijst.';
                $seenIds[$row['id']] = true;
                $existing = EducationProgram::findOrFail($row['id']);
                if ($existing->name === 'Software Developer' && $row['name'] !== $existing->name) {
                    $errors["programs.$index.name"] = 'Software Developer is de standaardopleiding en kan niet worden hernoemd.';
                }
            }
        }
        if ($errors) throw ValidationException::withMessages($errors);

        DB::transaction(function () use ($data) {
            foreach ($data['programs'] as $row) {
                $id = $row['id'] ?? null;
                unset($row['id']);
                if ($id) EducationProgram::whereKey($id)->update($row);
                else EducationProgram::create($row);
            }
        });

        return redirect()->route('admin.settings', ['program' => $data['current_program'] ?? null])->with('status', 'Alle opleidingen zijn opgeslagen.');
    }
    public function destroyProgram(EducationProgram $program)
    {
        if ($program->name === 'Software Developer') {
            return back()->withErrors(['program' => 'De standaardopleiding Software Developer kan niet worden verwijderd.']);
        }
        if ($program->groups()->exists()) {
            return back()->withErrors(['program' => 'Deze opleiding is nog gekoppeld aan groepen. Koppel die groepen eerst aan een andere opleiding.']);
        }

        $program->delete();
        $nextProgram = EducationProgram::orderBy('id')->value('id');

        return redirect()->route('admin.settings', ['program' => $nextProgram])->with('status', 'Opleiding verwijderd.');
    }
    public function storeCoreTask(Request $request, EducationProgram $program)
    {
        $data = $request->validate([
            'task_number' => ['required', 'string', 'max:50', 'regex:/^B\\d+-K\\d+$/', Rule::unique('core_tasks', 'task_number')->where('education_program_id', $program->id)],
            'name' => ['required', 'string', 'max:255'],
        ]);
        $program->coreTasks()->create($data);
        return back()->with('status', 'Kerntaak toegevoegd.');
    }
    public function updateCoreTask(Request $request, EducationProgram $program, CoreTask $task)
    {
        abort_unless((int) $task->education_program_id === (int) $program->id, 404);
        $data = $request->validate([
            'task_number' => ['required', 'string', 'max:50', 'regex:/^B\\d+-K\\d+$/', Rule::unique('core_tasks', 'task_number')->where('education_program_id', $program->id)->ignore($task->id)],
            'name' => ['required', 'string', 'max:255'],
        ]);
        DB::transaction(function () use ($task, $data) {
            $oldNumber = $task->task_number;
            $task->update($data);
            if ($oldNumber !== $data['task_number']) {
                foreach ($task->workProcesses as $process) {
                    $suffix = preg_match('/-W(\\d+)$/', (string) $process->code, $match) ? $match[1] : $process->number;
                    $process->update(['code' => $data['task_number'].'-W'.$suffix]);
                }
            }
        });
        return back()->with('status', 'Kerntaak opgeslagen. De codes van de werkprocessen zijn bijgewerkt.');
    }
    public function destroyCoreTask(EducationProgram $program, CoreTask $task)
    {
        abort_unless((int) $task->education_program_id === (int) $program->id, 404);
        if ($task->workProcesses()->exists()) return back()->withErrors(['core_task' => 'Deze kerntaak bevat werkprocessen. Verwijder die eerst.']);
        $task->delete();
        return back()->with('status', 'Kerntaak verwijderd.');
    }
    public function storeProcess(Request $request, EducationProgram $program)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'core_task_id' => ['required', 'integer', Rule::exists('core_tasks', 'id')->where('education_program_id', $program->id)],
        ]);

        DB::transaction(function () use ($program, $data) {
            $program = EducationProgram::whereKey($program->id)->lockForUpdate()->firstOrFail();
            $task = $program->coreTasks()->whereKey($data['core_task_id'])->firstOrFail();

            $taskProcessPattern = '/^'.preg_quote($task->task_number, '/').'-W(\d+)$/';
            $taskProcesses = $task->workProcesses()->orderBy('number')->get();
            $usedTaskProcessNumbers = $taskProcesses->map(function (WorkProcess $process) use ($taskProcessPattern): ?int {
                return preg_match($taskProcessPattern, (string) $process->code, $match) ? (int) $match[1] : null;
            })->filter()->values()->all();
            $nextTaskProcessNumber = $usedTaskProcessNumbers ? max($usedTaskProcessNumbers) + 1 : 1;

            foreach ($taskProcesses as $existingProcess) {
                if (preg_match($taskProcessPattern, (string) $existingProcess->code)) continue;
                while (in_array($nextTaskProcessNumber, $usedTaskProcessNumbers, true)) $nextTaskProcessNumber++;
                $existingProcess->update(['code' => $task->task_number.'-W'.$nextTaskProcessNumber]);
                $usedTaskProcessNumbers[] = $nextTaskProcessNumber++;
            }

            $number = (int) $program->workProcesses()->max('number') + 1;
            $task->workProcesses()->create([
                'number' => $number,
                'code' => $task->task_number.'-W'.$nextTaskProcessNumber,
                'name' => $data['name'],
            ]);
        });
        return back()->with('status', 'Werkproces toegevoegd aan de gekozen kerntaak.');
    }
    public function updateProcess(Request $request, EducationProgram $program, WorkProcess $process)
    {
        abort_unless($process->coreTask?->education_program_id === $program->id, 404);
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $process->update($data);
        return back()->with('status', 'Werkproces opgeslagen voor alle groepen.');
    }
    public function destroyProcess(EducationProgram $program, WorkProcess $process)
    {
        abort_unless($process->coreTask?->education_program_id === $program->id, 404);
        $hasEvidence = $program->groups()->whereHas('students.portfolios.evidence', fn ($q) => $q->where('work_process', (string) $process->number))->exists();
        if ($hasEvidence) return back()->withErrors(['work_process' => 'Dit werkproces heeft bewijsstukken en kan niet worden verwijderd.']);
        $process->delete();
        return back()->with('status', 'Werkproces verwijderd.');
    }
    public function assignProgram(Request $request, Group $group)
    {
        abort_if($group->lifecycle_status !== null, 404);
        $data = $request->validate(['education_program_id' => ['required', 'exists:education_programs,id']]);
        if ((int) $group->education_program_id === (int) $data['education_program_id']) return back();
        if ($group->students()->whereHas('portfolios.evidence')->exists()) return back()->withErrors(['education_program_id' => 'Deze groep heeft bewijsstukken. De opleiding kan daarom niet worden gewijzigd.']);
        $group->update($data);
        return back()->with('status', 'Opleiding opgeslagen. De groep gebruikt nu automatisch de gedeelde werkprocessen.');
    }
}
