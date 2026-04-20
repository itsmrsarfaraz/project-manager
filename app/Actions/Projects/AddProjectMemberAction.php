<?php

namespace App\Actions\Projects;

use App\Events\ProjectMemberAdded;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AddProjectMemberAction
{
    public function execute(Project $project, User $invitee, string $role, User $invitedBy): void
    {
        DB::transaction(function () use ($project, $invitee, $role, $invitedBy) {

            $project->members()->attach($invitee->id, ['role' => $role]);

            // Fire event — listeners handle email + activity log
            ProjectMemberAdded::dispatch($project, $invitee, $role, $invitedBy);
        });
    }
}
