<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\ActivityLogger;

class TaskObserver
{
    /**
     * Called after a task is created.
     * The model is fully saved at this point.
     */
    public function created(Task $task): void
    {
        ActivityLogger::log(
            projectId: $task->project_id,
            type: 'task_created',
            description: $this->userName() . " created task \"{$task->title}\"",
            metadata: [
                'task_id'    => $task->id,
                'task_title' => $task->title,
                'priority'   => $task->priority,
                'status'     => $task->status,
            ]
        );
    }

    /**
     * Called after a task is updated.
     * $task->getChanges() returns only the fields that changed.
     * $task->getOriginal() returns the values BEFORE the change.
     */
    public function updated(Task $task): void
    {
        $changes  = $task->getChanges();   // what changed (new values)
        $original = $task->getOriginal();  // what it was before

        // Remove timestamps from changes — we don't want to log those
        unset($changes['updated_at'], $changes['created_at']);

        if (empty($changes)) {
            return; // nothing meaningful changed
        }

        // Build a human-readable description of what changed
        $changeParts = [];

        if (isset($changes['status'])) {
            $from = str_replace('_', ' ', $original['status']);
            $to   = str_replace('_', ' ', $changes['status']);
            $changeParts[] = "status from \"{$from}\" → \"{$to}\"";
        }

        if (isset($changes['assigned_to'])) {
            $assigneeName = $task->assignee?->name ?? 'nobody';
            $changeParts[] = "assigned to \"{$assigneeName}\"";
        }

        if (isset($changes['priority'])) {
            $changeParts[] = "priority from \"{$original['priority']}\" → \"{$changes['priority']}\"";
        }

        if (isset($changes['title'])) {
            $changeParts[] = "title to \"{$changes['title']}\"";
        }

        if (isset($changes['due_date'])) {
            $newDate = $changes['due_date']
                ? \Carbon\Carbon::parse($changes['due_date'])->format('M d, Y')
                : 'none';
            $changeParts[] = "due date to \"{$newDate}\"";
        }

        $summary = empty($changeParts)
            ? 'updated task details'
            : 'changed ' . implode(', ', $changeParts);

        ActivityLogger::log(
            projectId: $task->project_id,
            type: 'task_updated',
            description: $this->userName() . " {$summary} on \"{$task->title}\"",
            metadata: [
                'task_id'  => $task->id,
                'changes'  => $changes,
                'original' => array_intersect_key($original, $changes),
            ]
        );
    }

    /**
     * Called after a task is deleted.
     */
    public function deleted(Task $task): void
    {
        ActivityLogger::log(
            projectId: $task->project_id,
            type: 'task_deleted',
            description: $this->userName() . " deleted task \"{$task->title}\"",
            metadata: ['task_title' => $task->title]
        );
    }

    /**
     * Helper: get the current authenticated user's name.
     */
    private function userName(): string
    {
        return \Illuminate\Support\Facades\Auth::user()?->name ?? 'System';
    }
}
