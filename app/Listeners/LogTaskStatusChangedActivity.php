<?php

namespace App\Listeners;

use App\Events\TaskStatusChanged;
use App\Services\ActivityLogger;

class LogTaskStatusChangedActivity
{
    public function handle(TaskStatusChanged $event): void
    {
        $from = str_replace('_', ' ', $event->oldStatus);
        $to   = str_replace('_', ' ', $event->newStatus);

        ActivityLogger::log(
            projectId: $event->task->project_id,
            type: 'task_status_changed',
            description: "{$event->changedBy->name} changed \"{$event->task->title}\" status: {$from} → {$to}",
            metadata: [
                'task_id' => $event->task->id,
                'from'    => $event->oldStatus,
                'to'      => $event->newStatus,
            ]
        );
    }
}
