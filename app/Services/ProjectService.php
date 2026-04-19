<?php

namespace App\Services;

use App\Mail\ProjectInvitationMail;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class ProjectService
{
    /**
     * Create a new project and set up the owner membership.
     * This is the single source of truth for "what happens when a project is created".
     * Called from web controller, API controller, or any artisan command.
     */
    public function createProject(User $owner, array $data): Project
    {
        $project = Project::create([
            'owner_id'    => $owner->id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'status'      => $data['status'],
        ]);

        // Always add creator as owner in pivot table
        $project->members()->attach($owner->id, ['role' => 'owner']);

        return $project;
    }

    /**
     * Update a project's details.
     */
    public function updateProject(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh(); // return reloaded model with updated values
    }

    /**
     * Add a member to a project with a given role.
     * Handles the pivot attach + email notification.
     */
    public function addMember(Project $project, User $invitee, string $role): void
    {
        $project->members()->attach($invitee->id, ['role' => $role]);

        Mail::to($invitee->email)->send(
            new ProjectInvitationMail($project, $invitee, $role)
        );
    }

    /**
     * Remove a member from a project.
     */
    public function removeMember(Project $project, User $user): void
    {
        $project->members()->detach($user->id);
    }

    /**
     * Delete a project and all its related data.
     * The DB cascades handle tasks/members, but we log before deletion.
     */
    public function deleteProject(Project $project): void
    {
        $project->delete();
    }
}
