<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login')
        ->middleware('throttle:login');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me',      [AuthController::class, 'me'])->name('me');

        Route::apiResource('projects', ProjectController::class);
        Route::apiResource('projects.tasks', TaskController::class);

        Route::post(
            '/projects/{project}/tasks/{task}/attachments',
            [\App\Http\Controllers\AttachmentController::class, 'store']
        )->middleware('throttle:heavy');

        Route::patch(
            '/projects/{project}/tasks/{task}/status',
            [TaskController::class, 'updateStatus']
        )->name('projects.tasks.status');
    });
});
