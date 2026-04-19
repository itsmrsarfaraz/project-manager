<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    // RefreshDatabase wraps EVERY test in a transaction that rolls back.
    // Each test starts with a completely empty database.
    // No test can affect another test's data.
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Create a project owned by a user, with the owner in the pivot table.
     * This replicates what ProjectController::store() does.
     */
    private function createProject(User $owner, array $overrides = []): Project
    {
        /** @var Project $project */
        $project = Project::factory()->create(array_merge(
            ['owner_id' => $owner->id],
            $overrides
        ));
        $project->members()->attach($owner->id, ['role' => 'owner']);
        return $project;
    }

    // ── Authentication tests ──────────────────────────────────────────

    #[Test]
    public function guests_cannot_access_projects(): void
    {
        // No login — unauthenticated request
        $response = $this->get(route('projects.index'));

        // Should redirect to login, not show the page
        $response->assertRedirect(route('login'));
    }

    // ── Index tests ───────────────────────────────────────────────────

    #[Test]
    public function user_can_view_their_projects(): void
    {
        /** @var User $user */
        $user    = User::factory()->create();
        /** @var Project $project */
        $project = $this->createProject($user);

        // actingAs() logs in as this user for this request only
        $response = $this->actingAs($user)
            ->get(route('projects.index'));

        $response->assertOk();                          // HTTP 200
        $response->assertSee($project->name);           // project name is on the page
    }

    #[Test]
    public function user_cannot_see_projects_they_are_not_a_member_of(): void
    {
        $owner   = User::factory()->create();
        $other   = User::factory()->create();
        $project = $this->createProject($owner);

        /** @var User $other */
        $response = $this->actingAs($other)
            ->get(route('projects.index'));

        $response->assertOk();
        $response->assertDontSee($project->name); // other user doesn't see it
    }

    // ── Create/Store tests ────────────────────────────────────────────

    #[Test]
    public function user_can_create_a_project(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name'        => 'My Test Project',
            'description' => 'A project for testing',
            'status'      => 'active',
        ]);

        // Should redirect after successful creation
        $response->assertRedirect();

        // Project exists in the database
        $this->assertDatabaseHas('projects', [
            'name'     => 'My Test Project',
            'owner_id' => $user->id,
        ]);

        // Owner is in the pivot table with 'owner' role
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
        $user = User::factory()->create();

        /** @var User $user */
        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name'   => '',  // empty name
            'status' => 'active',
        ]);

        // Should redirect back with validation errors
        $response->assertSessionHasErrors('name');

        // Nothing created
        $this->assertDatabaseCount('projects', 0);
    }

    #[Test]
    public function project_name_must_be_at_least_3_characters(): void
    {
        $user = User::factory()->create();

        /** @var User $user */
        $this->actingAs($user)->post(route('projects.store'), [
            'name'   => 'AB', // too short
            'status' => 'active',
        ])->assertSessionHasErrors('name');
    }

    // ── Show tests ────────────────────────────────────────────────────

    #[Test]
    public function member_can_view_project(): void
    {
        $owner  = User::factory()->create();
        $member = User::factory()->create();
        $project = $this->createProject($owner);
        $project->members()->attach($member->id, ['role' => 'member']);

        /** @var User $member */
        $this->actingAs($member)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee($project->name);
    }

    #[Test]
    public function non_member_cannot_view_project(): void
    {
        $owner   = User::factory()->create();
        $other   = User::factory()->create();
        $project = $this->createProject($owner);

        /** @var User $other */
        $this->actingAs($other)
            ->get(route('projects.show', $project))
            ->assertForbidden(); // 403
    }

    // ── Update tests ──────────────────────────────────────────────────

    #[Test]
    public function owner_can_update_project(): void
    {
        $owner   = User::factory()->create();
        $project = $this->createProject($owner);

        /** @var User $owner */
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
        $owner   = User::factory()->create();
        $manager = User::factory()->create();
        $project = $this->createProject($owner);
        $project->members()->attach($manager->id, ['role' => 'manager']);

        /** @var User $manager */
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
        $owner   = User::factory()->create();
        $member  = User::factory()->create();
        $project = $this->createProject($owner);
        $project->members()->attach($member->id, ['role' => 'member']);

        /** @var User $member */
        $this->actingAs($member)
            ->put(route('projects.update', $project), [
                'name'   => 'Should Fail',
                'status' => 'active',
            ])
            ->assertForbidden(); // 403

        // Name should NOT have changed
        $this->assertDatabaseMissing('projects', ['name' => 'Should Fail']);
    }

    // ── Delete tests ──────────────────────────────────────────────────

    #[Test]
    public function owner_can_delete_project(): void
    {
        $owner   = User::factory()->create();
        $project = $this->createProject($owner);

        /** @var User $owner */
        $this->actingAs($owner)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    #[Test]
    public function manager_cannot_delete_project(): void
    {
        $owner   = User::factory()->create();
        $manager = User::factory()->create();
        $project = $this->createProject($owner);
        $project->members()->attach($manager->id, ['role' => 'manager']);

        /** @var User $manager */
        $this->actingAs($manager)
            ->delete(route('projects.destroy', $project))
            ->assertForbidden();

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }
}
