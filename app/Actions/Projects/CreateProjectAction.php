<?php

namespace App\Actions\Projects;

use App\Models\Project;
use App\Models\User;

class CreateProjectAction
{
    /**
     * Execute the action.
     *
     * An Action class has ONE public method: execute() or __invoke().
     * Using __invoke() lets you call it as $action() instead of $action->execute().
     * Both conventions are widely used — pick one and be consistent.
     */
    public function execute(User $owner, array $data): Project
    {
        // Step 1: Create the project record
        $project = Project::create([
            'owner_id'    => $owner->id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? 'active',
        ]);

        // Step 2: Attach creator as owner in pivot table
        $project->members()->attach($owner->id, ['role' => 'owner']);

        // Step 3: Return the created project
        return $project;
    }
}
