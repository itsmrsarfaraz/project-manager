<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Project $project,
        public User    $invitee,
        public string  $role
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been invited to: {$this->project->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.projects.invitation',
            with: [
                'project' => $this->project,
                'invitee' => $this->invitee,
                'role'    => $this->role,
                'url'     => route('projects.show', $this->project->id),
            ],
        );
    }
}
