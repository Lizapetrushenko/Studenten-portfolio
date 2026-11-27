<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortfolioApiController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isStudent()) {
            return response()->json(['message' => 'Deze API is bedoeld voor studentaccounts.'], 403);
        }

        $portfolio = $user->portfolios()->with('evidence')->first();

        if (! $portfolio) {
            return response()->json(['message' => 'Je hebt nog geen portfolio.'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $portfolio->id,
                'title' => $portfolio->title,
                'bio' => $portfolio->bio,
                'study' => $portfolio->study,
                'evidence' => $portfolio->evidence->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'category' => $item->category,
                    'work_process' => $item->work_process,
                    'status' => $item->status,
                    'note' => $item->note,
                    'feedback' => $item->feedback,
                    'created_at' => $item->created_at,
                ])->values(),
            ],
        ]);
    }

    public function workProcesses(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isStudent()) {
            return response()->json(['message' => 'Deze API is bedoeld voor studentaccounts.'], 403);
        }

        $processes = $user->groups()
            ->whereNull('lifecycle_status')
            ->with('workProcesses.coreTask')
            ->get()
            ->flatMap(fn ($group) => $group->workProcesses)
            ->unique('number')
            ->sortBy('number')
            ->values()
            ->map(fn ($process) => [
                'number' => $process->number,
                'code' => $process->code,
                'name' => $process->name,
                'core_task' => $process->coreTask?->task_number,
            ]);

        return response()->json(['data' => $processes]);
    }
}
