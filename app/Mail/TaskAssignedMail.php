<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// ShouldQueue → this email is sent via the queue, not inline
class TaskAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * SerializesModels is critical for queued jobs.
     * When a job is pushed to the queue, PHP serializes the Mailable to JSON.
     * Eloquent models can't be JSON-serialized directly (they contain DB connections etc).
     * SerializesModels replaces the model with just its ID in the queue,
     * then re-fetches it from the DB when the worker picks up the job.
     * This prevents stale data and memory issues.
     */
    public function __construct(
        public Task $task,   // the task that was assigned
        public User $assignee // the user being notified
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been assigned a task: {$this->task->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tasks.assigned',
            with: [
                'task'     => $this->task,
                'assignee' => $this->assignee,
                'project'  => $this->task->project,
                'url'      => route('projects.tasks.show', [
                    $this->task->project_id,
                    $this->task->id
                ]),
            ],
        );
    }
}
