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
        $portfolio = $request->user()->portfolios()->with('evidence')->first();
        $processes = $this->workProcesses();

        // Bereid data voor spin diagram
        $spinChartData = $this->prepareSpinChartData($portfolio, $processes);

        return view('dashboard', compact('portfolio', 'processes', 'spinChartData'));
    }

    private function prepareSpinChartData($portfolio, $processes)
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
                'in-proces' => $processEvidence->where('status', 'in-proces')->count(),
                'niet bekeken' => $processEvidence->where('status', 'niet bekeken')->count(),
            ];

            $chartData[$code] = $counts;
        }

        return json_encode($chartData);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'study' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->portfolios()->updateOrCreate([], $validated);

        return back()->with('status', 'Portfolio opgeslagen.');
    }

    public function storeEvidence(Request $request): RedirectResponse
    {
        $portfolio = $request->user()->portfolios()->firstOrFail();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'work_process' => ['required', 'string', 'in:K1W1,K1W2,K1W3,K1W4,K1W5,K2W1,K2W2,K2W3'],
            'category' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:1000'],
            'submitted_where' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:500'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ]);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('evidence', 'public');
        }
        unset($validated['file']);
        $validated['item_number'] = $portfolio->evidence()->where('work_process', $validated['work_process'])->count() + 1;
        $validated['work_process'] = collect($this->workProcesses())->firstWhere('code', $validated['work_process'])['label'];
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
            'status' => ['nullable', 'in:niet bekeken,in proces,ingeleverd,akkoord,niet akkoord,moet aangepast worden'],
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

    private function workProcesses(): array
    {
        return collect([
            ['code' => 'K1W1', 'name' => 'Afstemmen en plannen'],
            ['code' => 'K1W2', 'name' => 'Ontwerpen'],
            ['code' => 'K1W3', 'name' => 'Realisatie'],
            ['code' => 'K1W4', 'name' => 'Testen'],
            ['code' => 'K1W5', 'name' => 'Verbetervoorstellen'],
            ['code' => 'K2W1', 'name' => 'Projectteam'],
            ['code' => 'K2W2', 'name' => 'Presenteren'],
            ['code' => 'K2W3', 'name' => 'Evalueer'],
        ])->map(fn (array $process) => [...$process, 'label' => $process['code'] . ' - ' . $process['name']])->all();
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
