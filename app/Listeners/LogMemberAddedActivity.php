<?php

namespace App\Listeners;

use App\Events\ProjectMemberAdded;
use App\Services\ActivityLogger;

class LogMemberAddedActivity
{
    public string $queue = 'low';

    public function handle(ProjectMemberAdded $event): void
    {
        ActivityLogger::log(
            projectId: $event->project->id,
            type: 'member_added',
            description: "{$event->invitedBy->name} added {$event->invitee->name} as {$event->role}",
            metadata: [
                'invitee_id' => $event->invitee->id,
                'role'       => $event->role,
            ]
        );
    }
}
