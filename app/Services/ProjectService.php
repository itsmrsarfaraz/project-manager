<?php

namespace App\Services;

use App\Actions\Projects\AddProjectMemberAction;
use App\Actions\Projects\CreateProjectAction;
use App\Models\Project;
use App\Models\User;

class ProjectService
{
    public function __construct(
        private readonly CreateProjectAction    $createProjectAction,
        private readonly AddProjectMemberAction $addProjectMemberAction,
    ) {}

    public function createProject(User $owner, array $data): Project
    {
        return $this->createProjectAction->execute($owner, $data);
    }

    public function addMember(Project $project, User $invitee, string $role, User $invitedBy): void
    {
        $this->addProjectMemberAction->execute($project, $invitee, $role, $invitedBy);
    }

    public function removeMember(Project $project, User $user): void
    {
        $project->members()->detach($user->id);
    }

    public function updateProject(Project $project, array $data): Project
    {
        $project->update($data);
        return $project->fresh();
    }

    public function deleteProject(Project $project): void
    {
        $project->delete();
    }
}
