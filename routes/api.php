<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

// ── V1 API Routes ─────────────────────────────────────────────────────

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ── Public auth routes (no token required) ─────────────────────
    Route::post('/login',  [AuthController::class, 'login'])->name('login');

    // ── Protected routes (Sanctum token required) ──────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me',      [AuthController::class, 'me'])->name('me');

        // Projects
        Route::apiResource('projects', ProjectController::class);

        // Tasks (nested under projects)
        Route::apiResource('projects.tasks', TaskController::class);

        // Quick status update
        Route::patch(
            '/projects/{project}/tasks/{task}/status',
            [TaskController::class, 'updateStatus']
        )->name('projects.tasks.status');
    });
});
