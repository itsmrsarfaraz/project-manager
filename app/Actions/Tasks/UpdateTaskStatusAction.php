<?php

namespace App\Actions\Tasks;

use App\Events\TaskStatusUpdated;
use App\Models\Task;
use App\Models\User;

class UpdateTaskStatusAction
{
    /**
     * A focused action just for status changes.
     * This is used by both the web controller AND the API.
     * Having it as an Action means both go through the same code path.
     */
    public function execute(Task $task, string $newStatus, User $updatedBy): Task
    {
        $oldStatus = $task->status;

        if ($oldStatus === $newStatus) {
            return $task; // no-op — nothing to do
        }

        $task->update(['status' => $newStatus]);

        // Broadcast the change for real-time updates
        broadcast(new TaskStatusUpdated($task, $updatedBy))->toOthers();

        return $task->fresh();
    }
}
