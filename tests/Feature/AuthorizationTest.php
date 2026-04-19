<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_with_no_role_gets_403_on_project_routes(): void
    {
        $owner   = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $project->members()->attach($owner->id, ['role' => 'owner']);

        $outsider = User::factory()->create();


        /** @var User $outsider */

        // All these routes should return 403 for non-members
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
        $owner   = User::factory()->create();
        $member  = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $project->members()->attach($owner->id,  ['role' => 'owner']);
        $project->members()->attach($member->id, ['role' => 'member']);


        /** @var User $member */

        // Member cannot edit the project
        $this->actingAs($member)
            ->get(route('projects.edit', $project))
            ->assertForbidden();

        // Member cannot invite other members
        $this->actingAs($member)
            ->post(route('projects.members.store', $project), [
                'email' => 'someone@example.com',
                'role'  => 'member',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function manager_gets_403_on_owner_only_routes(): void
    {
        $owner   = User::factory()->create();
        $manager = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $project->members()->attach($owner->id,   ['role' => 'owner']);
        $project->members()->attach($manager->id, ['role' => 'manager']);

        // Manager cannot delete the project
        /** @var User $manager */
        $this->actingAs($manager)
            ->delete(route('projects.destroy', $project))
            ->assertForbidden();
    }
}
