<?php

namespace App\Listeners;

use App\Events\ProjectMemberAdded;
use App\Mail\ProjectInvitationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendProjectInvitationEmail implements ShouldQueue
{
    public function handle(ProjectMemberAdded $event): void
    {
        Mail::to($event->invitee->email)->send(
            new ProjectInvitationMail(
                $event->project,
                $event->invitee,
                $event->role
            )
        );
    }
}
