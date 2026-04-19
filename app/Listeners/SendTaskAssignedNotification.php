<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Mail\TaskAssignedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendTaskAssignedNotification implements ShouldQueue
{
    public string $queue = 'high';
    public int    $tries = 3;
    public int    $backoff = 60;

    public function handle(TaskCreated $event): void
    {
        $task     = $event->task;
        $assignee = $task->assignee;

        if (! $assignee) return;
        if ($assignee->id === $event->createdBy->id) return;

        Mail::to($assignee->email)->send(
            new TaskAssignedMail($task->load('project'), $assignee)
        );
    }

    public function failed(TaskCreated $event, \Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error('Failed to send task assignment email', [
            'task_id'   => $event->task->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
