<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use App\Models\Portfolio;
use App\Models\WorkProcess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $portfolio = $request->user()->portfolios()->with(['evidence', 'workProcesses'])->first();
        $processes = $portfolio?->workProcesses->map(fn (WorkProcess $process) => [
            'code' => (string) $process->number,
            'name' => $process->name,
            'label' => $process->number . '. ' . $process->name,
        ])->all() ?? [];

        $spinChartData = $this->prepareSpinChartData($portfolio, $processes);
        $overallStatusCounts = $this->prepareOverallStatusCounts($portfolio);

        return view('portfolio-overview', compact('portfolio', 'processes', 'spinChartData', 'overallStatusCounts'));
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
                return strpos($item->work_process, $code) === 0;
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
        $portfolio = $request->user()->portfolios()->first();
        $rules = [
            'title' => ['required', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];

        if (!$portfolio) {
            $rules['study'] = ['required', 'string', 'max:120'];
        }

        $validated = $request->validate($rules);

        if ($portfolio) {
            $portfolio->update([
                'title' => $validated['title'],
                'bio' => $validated['bio'] ?? null,
            ]);
        } else {
            $portfolio = $request->user()->portfolios()->create($validated);
            $this->createDefaultWorkProcesses($portfolio);
        }

        return back()->with('status', 'Portfolio opgeslagen.');
    }

    public function storeEvidence(Request $request): RedirectResponse
    {
        $portfolio = $request->user()->portfolios()->firstOrFail();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'work_process' => ['required', 'integer', 'exists:work_processes,number'],
            'category' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:1000'],
            'submitted_where' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:500'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ]);
        $portfolio->workProcesses()->where('number', $validated['work_process'])->firstOrFail();

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('evidence', 'public');
        }
        unset($validated['file']);
        $validated['item_number'] = $portfolio->evidence()->where('work_process', $validated['work_process'])->count() + 1;
        $portfolio->evidence()->create($validated);

        return back()->with('status', 'Bewijsstuk toegevoegd.');
    }

    public function updateEvidence(Request $request, Evidence $evidence): RedirectResponse
    {
        abort_unless($evidence->portfolio->user_id === $request->user()->id, 403);
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'idea' => ['nullable', 'string', 'max:2000'],
            'note' => ['nullable', 'string', 'max:2000'],
            'submitted_where' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:niet bekeken,in proces,ingeleverd,akkoord,niet akkoord,idee,moet aangepast worden'],
            'is_completed' => ['nullable', 'boolean'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ]);
        if ($request->hasFile('file')) {
            if ($evidence->file_path) Storage::disk('public')->delete($evidence->file_path);
            $validated['file_path'] = $request->file('file')->store('evidence', 'public');
        }
        unset($validated['file']);
        $validated['is_completed'] = $request->boolean('is_completed');
        $evidence->update($validated);

        return back()->with('status', 'Bewijsplan opgeslagen.');
    }

    public function destroyIdea(Request $request, Evidence $evidence): RedirectResponse
    {
        abort_unless($evidence->portfolio->user_id === $request->user()->id, 403);
        $evidence->update(['idea' => null]);

        return back()->with('status', 'Idee verwijderd.');
    }

    public function storeWorkProcess(Request $request): RedirectResponse
    {
        $portfolio = $request->user()->portfolios()->firstOrFail();
        $validated = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $nextNumber = (int) $portfolio->workProcesses()->max('number') + 1;
        $portfolio->workProcesses()->create(['number' => $nextNumber, 'name' => $validated['name']]);

        return back()->with('status', 'Werkproces toegevoegd.');
    }

    public function updateWorkProcess(Request $request, WorkProcess $workProcess): RedirectResponse
    {
        abort_unless($workProcess->portfolio->user_id === $request->user()->id, 403);
        $validated = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $workProcess->update($validated);

        return back()->with('status', 'Naam van werkproces opgeslagen.');
    }

    private function createDefaultWorkProcesses(Portfolio $portfolio): void
    {
        foreach ([
            'Afstemmen en plannen', 'Ontwerpen', 'Realisatie', 'Testen',
            'Verbetervoorstellen', 'Projectteam', 'Presenteren', 'Evalueer',
        ] as $number => $name) {
            $portfolio->workProcesses()->create(['number' => $number + 1, 'name' => $name]);
        }
    }

    public function destroyEvidence(Request $request, Evidence $evidence): RedirectResponse
    {
        abort_unless($evidence->portfolio->user_id === $request->user()->id, 403);
        if ($evidence->file_path) {
            Storage::disk('public')->delete($evidence->file_path);
        }
        $evidence->delete();

        return back()->with('status', 'Bewijsstuk verwijderd.');
    }

    public function destroy(Request $request, Portfolio $portfolio): RedirectResponse
    {
        abort_unless($portfolio->user_id === $request->user()->id, 403);
        
        // Verwijder alle bewijsstukken en hun bestanden
        foreach ($portfolio->evidence as $evidence) {
            if ($evidence->file_path) {
                Storage::disk('public')->delete($evidence->file_path);
            }
            $evidence->delete();
        }
        
        // Verwijder portfolio
        $portfolio->delete();

        return redirect()->route('dashboard')->with('status', 'Portfolio verwijderd. Je kunt nu een nieuw portfolio aanmaken.');
    }
}
