<?php

namespace App\Services;

use App\Actions\Tasks\CreateTaskAction;
use App\Actions\Tasks\UpdateTaskStatusAction;
use App\Mail\TaskAssignedMail;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TaskService
{
    public function __construct(
        private readonly CreateTaskAction       $createTaskAction,
        private readonly UpdateTaskStatusAction $updateTaskStatusAction,
    ) {}

    public function createTask(Project $project, array $data): Task
    {
        return $this->createTaskAction->execute($project, $data);
    }

    public function updateTask(Task $task, array $data): Task
    {
        $previousAssignee = $task->assigned_to;

        $task->update($data);

        // Handle assignee change notification
        if (
            $task->assigned_to
            && $task->assigned_to !== $previousAssignee
        ) {
            $assignee = User::find($task->assigned_to);
            if ($assignee && $assignee->id !== Auth::id()) {
                Mail::to($assignee->email)->send(
                    new TaskAssignedMail($task->load('project'), $assignee)
                );
            }
        }

        return $task->fresh();
    }

    public function updateStatus(Task $task, string $status, User $user): Task
    {
        return $this->updateTaskStatusAction->execute($task, $status, $user);
    }

    public function deleteTask(Task $task): void
    {
        $task->delete();
    }
}
