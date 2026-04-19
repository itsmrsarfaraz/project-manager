<?php

namespace App\Actions\Tasks;

use App\Events\TaskStatusChanged;
use App\Events\TaskStatusUpdated;
use App\Models\Task;
use App\Models\User;

class UpdateTaskStatusAction
{
    public function execute(Task $task, string $newStatus, User $updatedBy): Task
    {
        $oldStatus = $task->status;

        if ($oldStatus === $newStatus) {
            return $task;
        }

        $task->update(['status' => $newStatus]);

        // Fire domain event — listeners log the change
        TaskStatusChanged::dispatch($task, $oldStatus, $newStatus, $updatedBy);

        // Fire broadcast event — for real-time UI updates
        broadcast(new TaskStatusUpdated($task, $updatedBy))->toOthers();

        return $task->fresh();
    }
}
