<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private function setupProject(): array
    {
        $owner   = User::factory()->create();
        $manager = User::factory()->create();
        $member  = User::factory()->create();

        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $project->members()->attach($owner->id,   ['role' => 'owner']);
        $project->members()->attach($manager->id, ['role' => 'manager']);
        $project->members()->attach($member->id,  ['role' => 'member']);

        return compact('owner', 'manager', 'member', 'project');
    }

    #[Test]
    public function any_member_can_create_a_task(): void
    {
        ['member' => $member, 'project' => $project] = $this->setupProject();

        $this->actingAs($member)
            ->post(route('projects.tasks.store', $project), [
                'title'    => 'New Task',
                'status'   => 'todo',
                'priority' => 'medium',
            ])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('tasks', [
            'title'      => 'New Task',
            'project_id' => $project->id,
        ]);
    }

    #[Test]
    public function non_member_cannot_create_task(): void
    {
        ['project' => $project] = $this->setupProject();
        $outsider = User::factory()->create();


        /** @var User $outsider */

        $this->actingAs($outsider)
            ->post(route('projects.tasks.store', $project), [
                'title'    => 'Sneaky Task',
                'status'   => 'todo',
                'priority' => 'low',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('tasks', 0);
    }

    #[Test]
    public function task_requires_title(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->setupProject();

        $this->actingAs($owner)
            ->post(route('projects.tasks.store', $project), [
                'title'    => '',
                'status'   => 'todo',
                'priority' => 'medium',
            ])
            ->assertSessionHasErrors('title');
    }

    #[Test]
    public function assigned_user_must_be_a_project_member(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->setupProject();
        $outsider = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('projects.tasks.store', $project), [
                'title'       => 'Test Task',
                'status'      => 'todo',
                'priority'    => 'medium',
                'assigned_to' => $outsider->id, // not a member!
            ])
            ->assertSessionHasErrors('assigned_to');
    }

    #[Test]
    public function member_can_edit_task(): void
    {
        ['member' => $member, 'project' => $project] = $this->setupProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status'     => 'todo',
        ]);

        $this->actingAs($member)
            ->put(route('projects.tasks.update', [$project, $task]), [
                'title'    => 'Updated Title',
                'status'   => 'in_progress',
                'priority' => 'high',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id'     => $task->id,
            'status' => 'in_progress',
        ]);
    }

    #[Test]
    public function only_assignee_member_can_delete_task(): void
    {
        ['member' => $member, 'owner' => $owner, 'project' => $project] = $this->setupProject();

        // Task assigned to owner, NOT member
        $task = Task::factory()->create([
            'project_id'  => $project->id,
            'assigned_to' => $owner->id,
        ]);

        // Member tries to delete a task they're not assigned to
        $this->actingAs($member)
            ->delete(route('projects.tasks.destroy', [$project, $task]))
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    #[Test]
    public function owner_can_delete_any_task(): void
    {
        ['owner' => $owner, 'member' => $member, 'project' => $project] = $this->setupProject();

        $task = Task::factory()->create([
            'project_id'  => $project->id,
            'assigned_to' => $member->id, // assigned to member, deleted by owner
        ]);

        $this->actingAs($owner)
            ->delete(route('projects.tasks.destroy', [$project, $task]))
            ->assertRedirect();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
