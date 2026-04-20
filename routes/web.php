<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;


// Email preview routes — DEVELOPMENT ONLY
if (app()->environment('local')) {
    Route::get('/mail-preview/task-assigned', function () {
        $task    = \App\Models\Task::with('project')->whereNotNull('assigned_to')->first();
        $assignee = $task->assignee;
        return new \App\Mail\TaskAssignedMail($task, $assignee);
    });

    Route::get('/mail-preview/project-invitation', function () {
        $project = \App\Models\Project::first();
        $invitee = \App\Models\User::find(2);
        return new \App\Mail\ProjectInvitationMail($project, $invitee, 'member');
    });
}

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

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

        // ── Comments ──────────────────────────────────────────────────
        Route::post(
            '/projects/{project}/tasks/{task}/comments',
            [CommentController::class, 'store']
        )->name('projects.tasks.comments.store');

        Route::delete(
            '/projects/{project}/tasks/{task}/comments/{comment}',
            [CommentController::class, 'destroy']
        )->name('projects.tasks.comments.destroy');

        // ── Attachments ───────────────────────────────────────────────
        Route::post(
            '/projects/{project}/tasks/{task}/attachments',
            [AttachmentController::class, 'store']
        )->name('projects.tasks.attachments.store');

        Route::get(
            '/projects/{project}/tasks/{task}/attachments/{attachment}',
            [AttachmentController::class, 'show']
        )->name('projects.tasks.attachments.show');

        Route::delete(
            '/projects/{project}/tasks/{task}/attachments/{attachment}',
            [AttachmentController::class, 'destroy']
        )->name('projects.tasks.attachments.destroy');

        // ── Labels ───────────────────────────────────────────────────
        // Label management (managers/owners)
        Route::post('/projects/{project}/labels', [LabelController::class, 'store'])
            ->name('projects.labels.store')
            ->middleware('project.member:manager');

        Route::delete('/projects/{project}/labels/{label}', [LabelController::class, 'destroy'])
            ->name('projects.labels.destroy')
            ->middleware('project.member:manager');

        // Sync labels on a task (any member)
        Route::post('/projects/{project}/tasks/{task}/labels', [LabelController::class, 'sync'])
            ->name('projects.tasks.labels.sync');
    });
});

require __DIR__ . '/auth.php';
