<?php

use App\Models\Project;
use Illuminate\Support\Facades\Broadcast;

/*
 * Channel authorization.
 * Users can only subscribe to project channels they're a member of.
 * This runs when a client tries to subscribe to a private channel.
 */

Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    $project = Project::find($projectId);

    if (! $project) {
        return false;
    }

    return $user->isMemberOf($project); // only members can subscribe
});
