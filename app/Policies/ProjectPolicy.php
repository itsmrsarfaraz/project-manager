<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Any authenticated user can see the projects list.
     * (They only see THEIR projects because of the query in the controller.)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Only project members can view a project's details.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->isMemberOf($project);
    }

    /**
     * Any authenticated user can create a project.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the owner or a manager can edit project details.
     */
    public function update(User $user, Project $project): bool
    {
        $role = $user->roleOn($project);

        return in_array($role, ['owner', 'manager']);
    }

    /**
     * Only the project owner can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->roleOn($project) === 'owner';
    }

    /**
     * Only owner or manager can add new members.
     * This is a CUSTOM policy method (beyond the 7 defaults).
     */
    public function addMember(User $user, Project $project): bool
    {
        $role = $user->roleOn($project);

        return in_array($role, ['owner', 'manager']);
    }

    /**
     * Only the owner can remove members.
     * Prevents managers from removing each other or the owner.
     */
    public function removeMember(User $user, Project $project): bool
    {
        return $user->roleOn($project) === 'owner';
    }
}
