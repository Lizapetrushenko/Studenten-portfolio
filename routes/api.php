<?php

use App\Http\Controllers\Api\PortfolioApiController;
use App\Http\Controllers\TokenAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/token-login', [TokenAuthController::class, 'login'])->name('token.api.login');

Route::middleware('api.token')->group(function () {
    Route::get('/me', fn (\Illuminate\Http\Request $request) => response()->json([
        'data' => [
            'id' => $request->user()->id,
            'name' => $request->user()->displayName(),
            'email' => $request->user()->email,
            'role' => $request->user()->role,
        ],
    ]));
    Route::get('/portfolio', [PortfolioApiController::class, 'show']);
    Route::get('/work-processes', [PortfolioApiController::class, 'workProcesses']);
});
