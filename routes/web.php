<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\TokenAuthController;
use App\Http\Controllers\MfaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeacherController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/screens', function () {
    return view('screens.index');
})->name('screens.index');

Route::get('/dashboard', function () {
    return match (auth()->user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'teacher' => redirect()->route('teacher.dashboard'),
        default => app(PortfolioController::class)->index(request()),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/auth/google/redirect', [SocialAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [SocialAuthController::class, 'callback'])->name('google.callback');
Route::get('/token-login', [TokenAuthController::class, 'show'])->name('token.login');
Route::post('/token-login/generate', [TokenAuthController::class, 'issue'])->name('token.issue');
Route::post('/token-login', [TokenAuthController::class, 'authenticate'])->name('token.authenticate');
Route::post('/api/token-login', [TokenAuthController::class, 'login'])->name('token.api.login');
Route::get('/mfa', [MfaController::class, 'challenge'])->name('mfa.challenge');
Route::post('/mfa', [MfaController::class, 'verify'])->name('mfa.verify');
Route::post('/register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'store'])->name('register');
Route::get('/auth/github/redirect', [SocialAuthController::class, 'redirectToGithub'])->name('github.redirect');
Route::get('/auth/github/callback', [SocialAuthController::class, 'handleGithubCallback'])->name('github.callback');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/mfa/setup', [MfaController::class, 'setup'])->name('mfa.setup');
    Route::post('/mfa/setup', [MfaController::class, 'enable'])->name('mfa.enable');
    Route::post('/mfa/disable', [MfaController::class, 'disable'])->name('mfa.disable');
    Route::get('/bewijs/{evidence}/download', [PortfolioController::class, 'downloadEvidence'])->name('evidence.download');
    Route::get('/bewijs/{evidence}/open', [PortfolioController::class, 'openEvidence'])->name('evidence.open');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/instellingen', [\App\Http\Controllers\EducationSettingsController::class, 'index'])->name('settings');
    Route::post('/instellingen/opleidingen', [\App\Http\Controllers\EducationSettingsController::class, 'storeProgram'])->name('programs.store');
    Route::patch('/instellingen/opleidingen/{program}', [\App\Http\Controllers\EducationSettingsController::class, 'updateProgram'])->name('programs.update');
    Route::post('/instellingen/opleidingen/{program}/werkprocessen', [\App\Http\Controllers\EducationSettingsController::class, 'storeProcess'])->name('processes.store');
    Route::patch('/instellingen/opleidingen/{program}/werkprocessen/{process}', [\App\Http\Controllers\EducationSettingsController::class, 'updateProcess'])->name('processes.update');
    Route::delete('/instellingen/opleidingen/{program}/werkprocessen/{process}', [\App\Http\Controllers\EducationSettingsController::class, 'destroyProcess'])->name('processes.destroy');
    Route::patch('/instellingen/groepen/{group}/opleiding', [\App\Http\Controllers\EducationSettingsController::class, 'assignProgram'])->name('groups.program');
    Route::post('/docenten', [AdminController::class, 'createTeacher'])->name('teachers.store');
    Route::get('/docenten/{teacher}', [AdminController::class, 'showTeacher'])->name('teachers.show');
    Route::post('/groepen', [AdminController::class, 'createGroup'])->name('groups.store');
    Route::get('/groepen/{group}', [AdminController::class, 'showGroup'])->name('groups.show');
    Route::patch('/groepen/{group}', [AdminController::class, 'updateGroup'])->name('groups.update');
    Route::patch('/studenten/{student}/groep', [AdminController::class, 'assignStudent'])->name('students.group');
    Route::get('/studenten/{student}', [AdminController::class, 'showStudent'])->name('students.show');
    Route::delete('/gebruikers/{user}', [AdminController::class, 'removeUser'])->name('users.destroy');
});

Route::middleware(['auth', 'verified', 'role:teacher'])->prefix('docent')->name('teacher.')->group(function () {
    Route::get('/', [TeacherController::class, 'index'])->name('dashboard');
    Route::post('/groepen', [TeacherController::class, 'createGroup'])->name('groups.store');
    Route::get('/groepen', [TeacherController::class, 'groups'])->name('groups.index');
    Route::get('/werkprocessen', fn () => abort(403))->name('work-processes.index');
    Route::get('/groepen/{group}', [TeacherController::class, 'showGroup'])->name('groups.show');
    Route::patch('/groepen/{group}', [TeacherController::class, 'updateGroup'])->name('groups.update');
    Route::get('/studenten', [TeacherController::class, 'students'])->name('students.index');
    Route::get('/studenten/{student}', [TeacherController::class, 'showStudent'])->name('students.show');
    Route::get('/groepen/{group}/voortgang', [TeacherController::class, 'groupProgress'])->name('groups.progress');
    Route::get('/groepen/{group}/instellingen', fn () => abort(403))->name('groups.settings');
    Route::post('/groepen/{group}/werkprocessen', fn () => abort(403))->name('groups.work-processes.store');
    Route::patch('/groepen/{group}/werkprocessen/{workProcess}', fn () => abort(403))->name('groups.work-processes.update');
    Route::delete('/groepen/{group}/werkprocessen/{workProcess}', fn () => abort(403))->name('groups.work-processes.destroy');
    Route::post('/groepen/{group}/studenten', [TeacherController::class, 'addStudent'])->name('groups.students.store');
    Route::get('/studenten/{student}/portfolio', [TeacherController::class, 'showPortfolio'])->name('students.portfolio');
    Route::patch('/studenten/{student}/gegevens', [TeacherController::class, 'updateStudent'])->name('students.update');
    Route::delete('/studenten/{student}', [TeacherController::class, 'removeStudent'])->name('students.destroy');
    Route::patch('/bewijs/{evidence}/beoordeling', [TeacherController::class, 'reviewEvidence'])->name('evidence.review');
});

Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::post('/portfolio', [PortfolioController::class, 'store'])->name('portfolio.store');
    Route::delete('/portfolio/{portfolio}', [PortfolioController::class, 'destroy'])->name('portfolio.destroy');
    Route::post('/portfolio/evidence', [PortfolioController::class, 'storeEvidence'])->name('evidence.store');
    Route::patch('/portfolio/evidence/{evidence}', [PortfolioController::class, 'updateEvidence'])->name('evidence.update');
    Route::delete('/portfolio/evidence/{evidence}/idea', [PortfolioController::class, 'destroyIdea'])->name('evidence.idea.destroy');
    Route::delete('/portfolio/evidence/{evidence}/bestand', [PortfolioController::class, 'removeEvidenceFile'])->name('evidence.file.destroy');
    Route::delete('/portfolio/evidence/{evidence}', [PortfolioController::class, 'destroyEvidence'])->name('evidence.destroy');
});

require __DIR__.'/auth.php';
