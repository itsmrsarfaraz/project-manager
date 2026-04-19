<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    // ✅ Remove the private createProject() method — now inherited from TestCase

    #[Test]
    public function guests_cannot_access_projects(): void
    {
        $response = $this->get(route('projects.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function user_can_view_their_projects(): void
    {
        $user    = $this->createUser(); // ← use helper
        $project = $this->createProject($user); // ← use helper

        $this->actingAs($user)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee($project->name);
    }

    #[Test]
    public function user_cannot_see_projects_they_are_not_a_member_of(): void
    {
        $owner   = $this->createUser();
        $other   = $this->createUser();
        $project = $this->createProject($owner);

        $this->actingAs($other)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertDontSee($project->name);
    }

    #[Test]
    public function user_can_create_a_project(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->post(route('projects.store'), [
            'name'        => 'My Test Project',
            'description' => 'A project for testing',
            'status'      => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name'     => 'My Test Project',
            'owner_id' => $user->id,
        ]);

        $project = Project::where('name', 'My Test Project')->first();

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id'    => $user->id,
            'role'       => 'owner',
        ]);
    }

    #[Test]
    public function project_creation_requires_a_name(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->post(route('projects.store'), [
            'name'   => '',
            'status' => 'active',
        ])->assertSessionHasErrors('name');

        $this->assertDatabaseCount('projects', 0);
    }

    #[Test]
    public function project_name_must_be_at_least_3_characters(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->post(route('projects.store'), [
            'name'   => 'AB',
            'status' => 'active',
        ])->assertSessionHasErrors('name');
    }

    #[Test]
    public function member_can_view_project(): void
    {
        $owner   = $this->createUser();
        $member  = $this->createUser();
        $project = $this->createProject($owner);
        $project->members()->attach($member->id, ['role' => 'member']);

        $this->actingAs($member)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee($project->name);
    }

    #[Test]
    public function non_member_cannot_view_project(): void
    {
        $owner   = $this->createUser();
        $other   = $this->createUser();
        $project = $this->createProject($owner);

        $this->actingAs($other)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    #[Test]
    public function owner_can_update_project(): void
    {
        $owner   = $this->createUser();
        $project = $this->createProject($owner);

        $this->actingAs($owner)
            ->put(route('projects.update', $project), [
                'name'   => 'Updated Name',
                'status' => 'active',
            ])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('projects', ['name' => 'Updated Name']);
    }

    #[Test]
    public function manager_can_update_project(): void
    {
        $owner   = $this->createUser();
        $manager = $this->createUser();
        $project = $this->createProject($owner);
        $project->members()->attach($manager->id, ['role' => 'manager']);

        $this->actingAs($manager)
            ->put(route('projects.update', $project), [
                'name'   => 'Manager Update',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', ['name' => 'Manager Update']);
    }

    #[Test]
    public function member_cannot_update_project(): void
    {
        $owner   = $this->createUser();
        $member  = $this->createUser();
        $project = $this->createProject($owner);
        $project->members()->attach($member->id, ['role' => 'member']);

        $this->actingAs($member)
            ->put(route('projects.update', $project), [
                'name'   => 'Should Fail',
                'status' => 'active',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('projects', ['name' => 'Should Fail']);
    }

    #[Test]
    public function owner_can_delete_project(): void
    {
        $owner   = $this->createUser();
        $project = $this->createProject($owner);

        $this->actingAs($owner)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    #[Test]
    public function manager_cannot_delete_project(): void
    {
        $owner   = $this->createUser();
        $manager = $this->createUser();
        $project = $this->createProject($owner);
        $project->members()->attach($manager->id, ['role' => 'manager']);

        $this->actingAs($manager)
            ->delete(route('projects.destroy', $project))
            ->assertForbidden();

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }
}
