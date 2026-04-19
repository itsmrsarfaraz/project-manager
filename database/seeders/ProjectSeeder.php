<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Get all users from DB (UserSeeder must run first)
        $users = User::all();

        // Each user owns 1–3 projects
        foreach ($users as $owner) {
            $projectCount = rand(1, 3);

            for ($i = 0; $i < $projectCount; $i++) {
                // Create the project owned by this user
                $project = Project::factory()->create([
                    'owner_id' => $owner->id,
                ]);

                // Step A: Add the owner to the pivot table as 'owner' role
                // attach() inserts a row into project_user pivot table
                $project->members()->attach($owner->id, ['role' => 'owner']);

                // Step B: Add 2–4 other random members
                $otherUsers = $users
                    ->where('id', '!=', $owner->id) // exclude the owner
                    ->random(rand(2, 4));

                foreach ($otherUsers as $member) {
                    // Give first member 'manager' role, rest are 'member'
                    $role = $otherUsers->first()->id === $member->id
                        ? 'manager'
                        : 'member';

                    $project->members()->attach($member->id, ['role' => $role]);
                }

                // Step C: Create 3–8 tasks for this project
                $allMembers = $project->members; // all members including owner

                Task::factory(rand(3, 8))->create([
                    'project_id'  => $project->id,
                    'assigned_to' => fake()->randomElement(
                        $allMembers->pluck('id')->push(null)->toArray()
                        // pluck IDs + add null so some tasks are unassigned
                    ),
                ]);

                // After the Task::factory() block, add:
                $tasks = $project->tasks;

                foreach ($tasks as $task) {
                    // Add 0–3 comments per task
                    $commentCount = rand(0, 3);
                    for ($c = 0; $c < $commentCount; $c++) {
                        Comment::factory()->create([
                            'user_id'          => $allMembers->random()->id,
                            'commentable_id'   => $task->id,
                            'commentable_type' => \App\Models\Task::class,
                        ]);
                    }
                }
            }
        }
    }
}
