<?php

namespace App\Http\Controllers;

use App\Events\TaskStatusUpdated;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Mail\TaskAssignedMail;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Send assignment notification email.
     * Private helper — keeps store() and update() clean.
     */
    private function dispatchAssignmentEmail(Task $task): void
    {
        if (! $task->assigned_to) {
            return; // unassigned task — no email needed
        }

        $assignee = \App\Models\User::find($task->assigned_to);

        if (! $assignee) {
            return;
        }

        // Don't email someone who assigned the task to themselves
        if ($assignee->id === Auth::id()) {
            return;
        }

        // Mail::to() queues the email because TaskAssignedMail implements ShouldQueue
        Mail::to($assignee->email)->send(
            new TaskAssignedMail($task->load('project'), $assignee)
        );
    }

    public function create(Project $project): View
    {
        $this->authorize('addTask', $project);

        $members = $project->members;

        return view('tasks.create', compact('project', 'members'));
    }

    // StoreTaskRequest handles authorization + validation + business rules
    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $task = $project->tasks()->create($request->validated());

        // Send assignment email if task was assigned to someone
        $this->dispatchAssignmentEmail($task);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Task created.');
    }

    public function show(Project $project, Task $task): View
    {
        $this->authorize('view', $task);

        $task->load([
            'comments.author',
            'attachments.uploader', // load attachments with who uploaded
            'labels',
        ]);

        $project->load('labels');

        return view('tasks.show', compact('project', 'task'));
    }

    public function edit(Project $project, Task $task): View
    {
        $this->authorize('update', $task);

        $members = $project->members;

        return view('tasks.edit', compact('project', 'task', 'members'));
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task): RedirectResponse
    {
        $previousAssignee = $task->assigned_to;
        $previousStatus   = $task->status;

        $task->update($request->validated());

        // Fire email if assignee changed
        if ($task->assigned_to && $task->assigned_to !== $previousAssignee) {
            $this->dispatchAssignmentEmail($task);
        }

        // Broadcast status change to all project members viewing the project
        if ($task->status !== $previousStatus) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            broadcast(new TaskStatusUpdated($task, $user))->toOthers();
            // toOthers() → don't send to the person who made the change
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Task updated.');
    }

    public function destroy(Project $project, Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Task deleted.');
    }
}
