<?php

namespace App\Services;

use App\Mail\TaskAssignedMail;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TaskService
{
    /**
     * Create a task inside a project.
     */
    public function createTask(Project $project, array $data): Task
    {
        $task = $project->tasks()->create($data);

        // Send assignment notification if assigned
        $this->notifyAssignee($task, null);

        return $task;
    }

    /**
     * Update a task, detecting assignee changes for notifications.
     */
    public function updateTask(Task $task, array $data): Task
    {
        $previousAssignee = $task->assigned_to;

        $task->update($data);

        // Only notify if assignee actually changed
        if ($task->assigned_to !== $previousAssignee) {
            $this->notifyAssignee($task, $previousAssignee);
        }

        return $task->fresh();
    }

    /**
     * Delete a task.
     */
    public function deleteTask(Task $task): void
    {
        $task->delete();
    }

    /**
     * Send assignment email when a task is assigned to someone new.
     * Private — only called internally by this service.
     */
    private function notifyAssignee(Task $task, ?int $previousAssigneeId): void
    {
        if (! $task->assigned_to) {
            return; // unassigned — no email
        }

        $assignee = User::find($task->assigned_to);

        if (! $assignee) {
            return;
        }

        // Don't email the person who made the change
        if ($assignee->id === Auth::id()) {
            return;
        }

        Mail::to($assignee->email)->send(
            new TaskAssignedMail($task->load('project'), $assignee)
        );
    }
}
