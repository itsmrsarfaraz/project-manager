<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_with_no_role_gets_403_on_project_routes(): void
    {
        ['project' => $project] = $this->setupProjectWithRoles();
        $outsider = $this->createUser();

        $this->actingAs($outsider)
            ->get(route('projects.show', $project))
            ->assertForbidden();

        $this->actingAs($outsider)
            ->get(route('projects.edit', $project))
            ->assertForbidden();

        $this->actingAs($outsider)
            ->get(route('projects.tasks.create', $project))
            ->assertForbidden();
    }

    #[Test]
    public function member_gets_403_on_manager_routes(): void
    {
        ['member' => $member, 'project' => $project] = $this->setupProjectWithRoles();

        $this->actingAs($member)
            ->get(route('projects.edit', $project))
            ->assertForbidden();

        $newUser = $this->createUser();

        $this->actingAs($member)
            ->post(route('projects.members.store', $project), [
                'email' => $newUser->email,
                'role'  => 'member',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function manager_gets_403_on_owner_only_routes(): void
    {
        ['manager' => $manager, 'project' => $project] = $this->setupProjectWithRoles();

        $this->actingAs($manager)
            ->delete(route('projects.destroy', $project))
            ->assertForbidden();
    }
}
