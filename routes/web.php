<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // ── Profile (Breeze) ──────────────────────────────────────────────
    // These were accidentally removed when we rewrote web.php in Step 3
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Projects (index/create/store — no membership check needed) ────
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');

    // ── Project routes that require membership ────────────────────────
    Route::middleware('project.member')->group(function () {

        Route::get('/projects/{project}', [ProjectController::class, 'show'])
            ->name('projects.show');

        Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])
            ->name('projects.edit')
            ->middleware('project.member:manager');

        Route::put('/projects/{project}', [ProjectController::class, 'update'])
            ->name('projects.update')
            ->middleware('project.member:manager');

        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])
            ->name('projects.destroy')
            ->middleware('project.member:owner');

        // ── Member management ─────────────────────────────────────────
        Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store'])
            ->name('projects.members.store')
            ->middleware('project.member:manager');

        Route::delete('/projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])
            ->name('projects.members.destroy')
            ->middleware('project.member:owner');

        // ── Tasks ─────────────────────────────────────────────────────
        Route::resource('projects.tasks', TaskController::class)
            ->only(['create', 'store', 'show', 'edit', 'update', 'destroy']);
    });
});

require __DIR__ . '/auth.php';
