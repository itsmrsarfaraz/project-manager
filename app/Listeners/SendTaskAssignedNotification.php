<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Mail\TaskAssignedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

// ShouldQueue → this listener runs in the background queue
class SendTaskAssignedNotification implements ShouldQueue
{
    public function handle(TaskCreated $event): void
    {
        $task     = $event->task;
        $assignee = $task->assignee; // loaded via relationship

        if (! $assignee) {
            return; // unassigned
        }

        if ($assignee->id === $event->createdBy->id) {
            return; // don't email yourself
        }

        Mail::to($assignee->email)->send(
            new TaskAssignedMail($task->load('project'), $assignee)
        );
    }
}
