<?php

namespace Tests;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create a verified user.
     * Shorthand used across all feature tests.
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
        // Factory default already has email_verified_at = now()
    }

    /**
     * Create a project with the given owner already
     * attached in the pivot table as 'owner'.
     * Mirrors exactly what ProjectService::createProject() does.
     */
    protected function createProject(User $owner, array $attributes = []): Project
    {
        $project = Project::factory()->create(array_merge(
            ['owner_id' => $owner->id],
            $attributes
        ));

        $project->members()->attach($owner->id, ['role' => 'owner']);

        return $project;
    }

    /**
     * Set up a full project with owner + manager + member.
     * Returns all actors for use in tests.
     */
    protected function setupProjectWithRoles(): array
    {
        $owner   = $this->createUser(['name' => 'Owner User']);
        $manager = $this->createUser(['name' => 'Manager User']);
        $member  = $this->createUser(['name' => 'Member User']);

        $project = $this->createProject($owner);
        $project->members()->attach($manager->id, ['role' => 'manager']);
        $project->members()->attach($member->id,  ['role' => 'member']);

        return compact('owner', 'manager', 'member', 'project');
    }
}
