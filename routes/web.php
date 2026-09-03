<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\TokenAuthController;
use App\Http\Controllers\MfaController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/screens', function () {
    return view('screens.index');
})->name('screens.index');

Route::get('/dashboard', [PortfolioController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/auth/google/redirect', [SocialAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [SocialAuthController::class, 'callback'])->name('google.callback');
Route::get('/token-login', [TokenAuthController::class, 'show'])->name('token.login');
Route::post('/token-login/generate', [TokenAuthController::class, 'issue'])->name('token.issue');
Route::post('/token-login', [TokenAuthController::class, 'authenticate'])->name('token.authenticate');
Route::post('/api/token-login', [TokenAuthController::class, 'login'])->name('token.api.login');
Route::get('/mfa', [MfaController::class, 'challenge'])->name('mfa.challenge');
Route::post('/mfa', [MfaController::class, 'verify'])->name('mfa.verify');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/mfa/setup', [MfaController::class, 'setup'])->name('mfa.setup');
    Route::post('/mfa/setup', [MfaController::class, 'enable'])->name('mfa.enable');
    Route::post('/portfolio', [PortfolioController::class, 'store'])->name('portfolio.store');
    Route::delete('/portfolio/{portfolio}', [PortfolioController::class, 'destroy'])->name('portfolio.destroy');
    Route::post('/portfolio/evidence', [PortfolioController::class, 'storeEvidence'])->name('evidence.store');
    Route::patch('/portfolio/evidence/{evidence}', [PortfolioController::class, 'updateEvidence'])->name('evidence.update');
    Route::delete('/portfolio/evidence/{evidence}/idea', [PortfolioController::class, 'destroyIdea'])->name('evidence.idea.destroy');
    Route::delete('/portfolio/evidence/{evidence}', [PortfolioController::class, 'destroyEvidence'])->name('evidence.destroy');
});

require __DIR__.'/auth.php';
