<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Log an activity on a project.
     *
     * @param  int    $projectId
     * @param  string $type         machine-readable type: 'task_created'
     * @param  string $description  human-readable: "Alice created task Design Login"
     * @param  array  $metadata     optional context data
     */
    public static function log(
        int    $projectId,
        string $type,
        string $description,
        array  $metadata = []
    ): void {
        Activity::create([
            'user_id'     => Auth::id(), // null if called outside a request (CLI, tests)
            'project_id'  => $projectId,
            'type'        => $type,
            'description' => $description,
            'metadata'    => empty($metadata) ? null : $metadata,
        ]);
    }
}
