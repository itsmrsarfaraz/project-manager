<?php

namespace App\Listeners;

use App\Events\TaskStatusChanged;
use App\Services\ProjectStatsService;

class InvalidateTaskStatsCache
{
    public function __construct(
        private readonly ProjectStatsService $statsService
    ) {}

    public function handle(TaskStatusChanged $event): void
    {
        $this->statsService->invalidateProjectStats($event->task->project_id);
        $this->statsService->invalidateUserStats($event->changedBy->id);

        if ($event->task->assigned_to) {
            $this->statsService->invalidateUserStats($event->task->assigned_to);
        }
    }
}
