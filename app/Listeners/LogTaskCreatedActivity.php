<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Services\ActivityLogger;

class LogTaskCreatedActivity
{
    public function handle(TaskCreated $event): void
    {
        ActivityLogger::log(
            projectId: $event->task->project_id,
            type: 'task_created',
            description: "{$event->createdBy->name} created task \"{$event->task->title}\"",
            metadata: [
                'task_id'    => $event->task->id,
                'task_title' => $event->task->title,
                'priority'   => $event->task->priority,
            ]
        );
    }
}
