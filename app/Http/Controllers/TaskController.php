<?php

namespace App\Http\Controllers;

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

    // UpdateTaskRequest handles authorization + validation + business rules
    public function update(UpdateTaskRequest $request, Project $project, Task $task): RedirectResponse
    {
        $previousAssignee = $task->assigned_to; // capture BEFORE update

        $task->update($request->validated());

        // Only send email if assignee CHANGED to a new person
        if (
            $task->assigned_to
            && $task->assigned_to !== $previousAssignee
        ) {
            $this->dispatchAssignmentEmail($task);
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
