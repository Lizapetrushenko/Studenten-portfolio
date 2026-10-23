<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use App\Models\Portfolio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isStudent(), 403);
        $student = $request->user()->load('groups.workProcesses');
        $portfolio = $student->portfolios()->with('evidence')->first();
        $processCollection = $student->groups->whereNull('lifecycle_status')->flatMap->workProcesses->unique('number')->sortBy('number');
        $processes = $processCollection->map(fn ($process) => [
            'code' => (string) $process->number,
            'name' => $process->name,
            'label' => $process->number . '. ' . $process->name,
            'kerntaak' => $process->number <= 5 ? 'K1' : 'K2',
            'work_label' => 'W' . ($process->number <= 5 ? $process->number : $process->number - 5),
        ])->values()->all();

        $spinChartData = $this->prepareSpinChartData($portfolio, $processes);
        $overallStatusCounts = $this->prepareOverallStatusCounts($portfolio);
        $hasGroup = $student->groups->contains(fn ($group) => $group->lifecycle_status === null);
        $portfolioLocked = $student->portfolioIsLocked();
        $submitOnly = $student->canOnlySubmitEvidence();

        return view('portfolio-overview', compact('portfolio', 'processes', 'spinChartData', 'overallStatusCounts', 'hasGroup', 'portfolioLocked', 'submitOnly'));
    }

    private function prepareSpinChartData(?Portfolio $portfolio, array $processes): ?string
    {
        if (!$portfolio) {
            return null;
        }

        $processCodes = collect($processes)->pluck('code')->toArray();
        $chartData = [];

        foreach ($processCodes as $code) {
            $processEvidence = $portfolio->evidence->filter(function ($item) use ($code) {
                return (string) $item->work_process === (string) $code;
            });

            $counts = [
                'akkoord' => $processEvidence->where('status', 'akkoord')->count(),
                'ingeleverd' => $processEvidence->where('status', 'ingeleverd')->count(),
                'in proces' => $processEvidence->where('status', 'in proces')->count(),
                'niet akkoord' => $processEvidence->where('status', 'niet akkoord')->count(),
                'idee' => $processEvidence->filter(fn (Evidence $item) => $item->status === 'idee' || filled($item->idea))->count(),
            ];

            $chartData[$code] = $counts;
        }

        return json_encode($chartData);
    }

    private function prepareOverallStatusCounts(?Portfolio $portfolio): array
    {
        if (!$portfolio) {
            return [];
        }

        return [
            'akkoord' => $portfolio->evidence->where('status', 'akkoord')->count(),
            'niet akkoord' => $portfolio->evidence->where('status', 'niet akkoord')->count(),
            'ingeleverd' => $portfolio->evidence->where('status', 'ingeleverd')->count(),
            'in proces' => $portfolio->evidence->where('status', 'in proces')->count(),
            'idee' => $portfolio->evidence->filter(fn (Evidence $item) => $item->status === 'idee' || filled($item->idea))->count(),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $student = $request->user();
        abort_if($student->portfolioIsLocked(), 403, 'Je portfolio is vergrendeld voor deze groepsstatus.');
        abort_if($student->canOnlySubmitEvidence() && $student->portfolios()->exists(), 403, 'In de groep Herkansen kun je alleen bewijsstukken inleveren.');
        $portfolio = $student->portfolios()->first();
        $rules = [
            'title' => ['required', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];

        $validated = $request->validate($rules);

        if ($portfolio) {
            $portfolio->update([
                'title' => $validated['title'],
                'bio' => $validated['bio'] ?? null,
            ]);
        } else {
            $portfolio = $student->portfolios()->create([...$validated, 'study' => $student->study_type]);
        }

        return back()->with('status', 'Portfolio opgeslagen.');
    }

    public function storeEvidence(Request $request): RedirectResponse
    {
        abort_if($request->user()->portfolioIsLocked(), 403, 'Je portfolio is vergrendeld voor deze groepsstatus.');
        $portfolio = $request->user()->portfolios()->firstOrFail();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'work_process' => ['required', 'integer', 'min:1'],
            'category' => ['required', 'string', 'max:60'],
            'status' => ['required', 'in:idee,in proces,ingeleverd'],
            'note' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:1000'],
            'submitted_where' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:500'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);
        abort_unless($request->user()->groups()->whereNull('lifecycle_status')->whereHas('workProcesses', fn ($query) => $query->where('number', $validated['work_process']))->exists(), 422, 'Dit werkproces hoort niet bij jouw groep.');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $validated['file_path'] = $file->store('evidence', 'local');
            $validated['original_file_name'] = mb_substr($file->getClientOriginalName(), 0, 255);
        }
        unset($validated['file']);
        $validated['item_number'] = $portfolio->evidence()->where('work_process', $validated['work_process'])->count() + 1;
        $portfolio->evidence()->create($validated);

        return back()->with('status', 'Bewijsstuk toegevoegd.');
    }

    public function updateEvidence(Request $request, Evidence $evidence): RedirectResponse
    {
        abort_unless($evidence->portfolio->user_id === $request->user()->id, 403);
        abort_if($request->user()->portfolioIsLocked() || $request->user()->canOnlySubmitEvidence(), 403, 'Je kunt bestaande bewijsstukken niet aanpassen in deze groepsstatus.');
        abort_if($evidence->status === 'akkoord', 422, 'Een akkoord bewijsstuk kan niet meer worden gewijzigd.');
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:2000'],
            'submitted_where' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:idee,in proces,ingeleverd'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);
        if ($request->hasFile('file')) {
            if ($evidence->file_path) {
                Storage::disk('local')->delete($evidence->file_path);
                Storage::disk('public')->delete($evidence->file_path);
            }
            $file = $request->file('file');
            $validated['file_path'] = $file->store('evidence', 'local');
            $validated['original_file_name'] = mb_substr($file->getClientOriginalName(), 0, 255);
        }
        unset($validated['file']);
        $evidence->update($validated);

        return back()->with('status', 'Bewijsplan opgeslagen.');
    }

    public function destroyIdea(Request $request, Evidence $evidence): RedirectResponse
    {
        abort_unless($evidence->portfolio->user_id === $request->user()->id, 403);
        abort_if($request->user()->portfolioIsLocked() || $request->user()->canOnlySubmitEvidence() || $evidence->status === 'akkoord', 403);
        $evidence->update(['idea' => null]);

        return back()->with('status', 'Idee verwijderd.');
    }

    public function downloadEvidence(Request $request, Evidence $evidence)
    {
        $disk = $this->authorizedEvidenceDisk($request, $evidence);
        return Storage::disk($disk)->download($evidence->file_path, $evidence->original_file_name ?: null);
    }

    public function openEvidence(Request $request, Evidence $evidence)
    {
        $disk = $this->authorizedEvidenceDisk($request, $evidence);
        return Storage::disk($disk)->response($evidence->file_path, $evidence->original_file_name ?: basename($evidence->file_path), [], 'inline');
    }

    private function authorizedEvidenceDisk(Request $request, Evidence $evidence): string
    {
        $user = $request->user();
        $isOwner = $user->isStudent() && $evidence->portfolio->user_id === $user->id;
        $student = $evidence->portfolio->user;
        $teacherCanSeeEvidence = $user->isTeacher()
            && in_array($evidence->status, ['ingeleverd', 'akkoord', 'niet akkoord'], true)
            && ($student->groups()->where('teacher_id', $user->id)->exists()
                || $student->reviewers()->whereKey($user->id)->exists());
        abort_unless($isOwner || $teacherCanSeeEvidence, 404);
        abort_unless($evidence->file_path, 404);

        $disk = Storage::disk('local')->exists($evidence->file_path) ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($evidence->file_path), 404);
        return $disk;
    }

    public function removeEvidenceFile(Request $request, Evidence $evidence): RedirectResponse
    {
        abort_unless($evidence->portfolio->user_id === $request->user()->id, 403);
        abort_if($request->user()->portfolioIsLocked() || $request->user()->canOnlySubmitEvidence() || $evidence->status === 'akkoord', 422, 'Dit bewijsbestand kan niet worden verwijderd.');
        if ($evidence->file_path) {
            Storage::disk('local')->delete($evidence->file_path);
            Storage::disk('public')->delete($evidence->file_path);
            $evidence->update(['file_path' => null, 'original_file_name' => null]);
        }
        return back()->with('status', 'Bestand verwijderd.');
    }

    public function destroyEvidence(Request $request, Evidence $evidence): RedirectResponse
    {
        abort_unless($evidence->portfolio->user_id === $request->user()->id, 403);
        abort_if($request->user()->portfolioIsLocked() || $request->user()->canOnlySubmitEvidence() || $evidence->status === 'akkoord', 422, 'Dit bewijsstuk kan niet worden verwijderd.');
        if ($evidence->file_path) {
            Storage::disk('local')->delete($evidence->file_path);
            Storage::disk('public')->delete($evidence->file_path);
        }
        $evidence->delete();

        return back()->with('status', 'Bewijsstuk verwijderd.');
    }

    public function destroy(Request $request, Portfolio $portfolio): RedirectResponse
    {
        abort_unless($portfolio->user_id === $request->user()->id, 403);
        abort_if($request->user()->portfolioIsLocked() || $request->user()->canOnlySubmitEvidence(), 422, 'Je portfolio kan niet worden verwijderd in deze groepsstatus.');
        abort_if($portfolio->evidence()->where('status', 'akkoord')->exists(), 422, 'Dit portfolio bevat goedgekeurde bewijsstukken en kan daarom niet worden verwijderd.');
        
        // Verwijder alle bewijsstukken en hun bestanden
        foreach ($portfolio->evidence as $evidence) {
            if ($evidence->file_path) {
                Storage::disk('local')->delete($evidence->file_path);
                Storage::disk('public')->delete($evidence->file_path);
            }
            $evidence->delete();
        }
        
        // Verwijder portfolio
        $portfolio->delete();

        return redirect()->route('dashboard')->with('status', 'Portfolio verwijderd. Je kunt nu een nieuw portfolio aanmaken.');
    }
}
