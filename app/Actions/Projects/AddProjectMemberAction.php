<?php

namespace App\Actions\Projects;

use App\Mail\ProjectInvitationMail;
use App\Models\Project;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AddProjectMemberAction
{
    public function execute(Project $project, User $invitee, string $role, User $invitedBy): void
    {
        // Wrap in a transaction — if email dispatch fails,
        // the pivot row is rolled back. Atomicity.
        DB::transaction(function () use ($project, $invitee, $role, $invitedBy) {

            // Step 1: Attach to pivot
            $project->members()->attach($invitee->id, ['role' => $role]);

            // Step 2: Send invitation email (queued — won't delay the response)
            Mail::to($invitee->email)->send(
                new ProjectInvitationMail($project, $invitee, $role)
            );

            // Step 3: Log the activity
            ActivityLogger::log(
                projectId: $project->id,
                type: 'member_added',
                description: "{$invitedBy->name} added {$invitee->name} as {$role}",
                metadata: [
                    'invited_by' => $invitedBy->id,
                    'invitee_id' => $invitee->id,
                    'role'       => $role,
                ]
            );
        });
    }
}
