<?php
namespace App\Http\Controllers;
use App\Models\EducationProgram;
use App\Models\EducationWorkProcess;
use App\Models\Group;
use App\Models\GroupWorkProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EducationSettingsController extends Controller
{
    public function index(Request $request)
    {
        $programs = EducationProgram::withCount('workProcesses')->orderBy('name')->get();
        $program = $request->filled('program') ? EducationProgram::findOrFail($request->query('program')) : EducationProgram::where('name', 'Software Developer')->firstOrFail();
        $program->load('workProcesses');
        $groups = Group::whereNull('lifecycle_status')->with('educationProgram')->withCount('students')->orderBy('name')->get();
        return view('admin.settings', compact('programs', 'program', 'groups'));
    }
    public function storeProgram(Request $request)
    {
        $program = EducationProgram::create($request->validate(['name' => ['required', 'string', 'max:120', 'unique:education_programs,name']]));
        return redirect()->route('admin.settings', ['program' => $program->id])->with('status', 'Opleiding toegevoegd. Voeg nu de werkprocessen toe.');
    }
    public function updateProgram(Request $request, EducationProgram $program)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120', Rule::unique('education_programs')->ignore($program->id)]]);
        // The standard program stays available for automatic registration and new groups.
        if ($program->name === 'Software Developer' && $data['name'] !== $program->name) abort(422, 'Software Developer is de standaardopleiding en kan niet worden hernoemd.');
        $program->update($data);
        return back()->with('status', 'Opleiding opgeslagen.');
    }
    public function storeProcess(Request $request, EducationProgram $program)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        DB::transaction(function () use ($program, $data) {
            $program = EducationProgram::whereKey($program->id)->lockForUpdate()->firstOrFail();
            $number = max((int) $program->workProcesses()->max('number'), (int) GroupWorkProcess::whereIn('group_id', $program->groups()->select('id'))->max('number')) + 1;
            $program->workProcesses()->create(['number' => $number, ...$data]);
            foreach ($program->groups()->get() as $group) $group->workProcesses()->create(['number' => $number, ...$data]);
        });
        return back()->with('status', 'Werkproces toegevoegd aan de opleiding en de gekoppelde groepen.');
    }
    public function updateProcess(Request $request, EducationProgram $program, EducationWorkProcess $process)
    {
        abort_unless($process->education_program_id === $program->id, 404);
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        DB::transaction(function () use ($program, $process, $data) {
            $process->update($data);
            foreach ($program->groups()->get() as $group) $group->workProcesses()->updateOrCreate(['number' => $process->number], $data);
        });
        return back()->with('status', 'Werkproces opgeslagen voor alle gekoppelde groepen.');
    }
    public function destroyProcess(EducationProgram $program, EducationWorkProcess $process)
    {
        abort_unless($process->education_program_id === $program->id, 404);
        $hasEvidence = $program->groups()->whereHas('students.portfolios.evidence', fn ($q) => $q->where('work_process', (string) $process->number))->exists();
        if ($hasEvidence) return back()->withErrors(['work_process' => 'Dit werkproces heeft bewijsstukken en kan niet worden verwijderd.']);
        DB::transaction(function () use ($program, $process) {
            GroupWorkProcess::whereIn('group_id', $program->groups()->select('id'))->where('number', $process->number)->delete();
            $process->delete();
        });
        return back()->with('status', 'Werkproces verwijderd.');
    }
    public function assignProgram(Request $request, Group $group)
    {
        abort_if($group->lifecycle_status !== null, 404);
        $data = $request->validate(['education_program_id' => ['required', 'exists:education_programs,id']]);
        if ((int) $group->education_program_id === (int) $data['education_program_id']) return back();
        if ($group->students()->whereHas('portfolios.evidence')->exists()) return back()->withErrors(['education_program_id' => 'Deze groep heeft bewijsstukken. De opleiding kan daarom niet worden gewijzigd.']);
        DB::transaction(function () use ($group, $data) {
            $program = EducationProgram::findOrFail($data['education_program_id']);
            $group->update($data);
            $group->workProcesses()->delete();
            foreach ($program->workProcesses as $process) $group->workProcesses()->create(['number' => $process->number, 'name' => $process->name]);
        });
        return back()->with('status', 'Opleiding en werkprocessen ingesteld voor deze groep.');
    }
}
