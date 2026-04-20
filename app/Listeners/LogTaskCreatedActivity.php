<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Services\ActivityLogger;
use App\Services\ProjectStatsService;

class LogTaskCreatedActivity
{
    public string $queue = 'low';

    public function __construct(
        private readonly ProjectStatsService $statsService
    ) {}

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

        $this->statsService->invalidateProjectStats($event->task->project_id);
        $this->statsService->invalidateUserStats($event->createdBy->id);

        if ($event->task->assigned_to) {
            $this->statsService->invalidateUserStats($event->task->assigned_to);
        }
    }
}
