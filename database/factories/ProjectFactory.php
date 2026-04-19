<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            // We don't set owner_id here — the Seeder will pass it in.
            // This factory only defines WHAT a project looks like, not WHO owns it.
            'name'        => fake()->bs() . ' ' . fake()->randomElement([
                'Platform',
                'System',
                'Portal',
                'Dashboard',
                'App'
            ]),
            'description' => fake()->paragraph(),
            'status'      => fake()->randomElement(['active', 'active', 'active', 'archived', 'completed']),
            //                                       ↑ repeated 3x so 'active' is more likely (weighted randomness)
        ];
    }

    // A "state" — a named variation of this factory
    // Usage: Project::factory()->active()->create()
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
