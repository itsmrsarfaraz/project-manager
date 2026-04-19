<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'       => fake()->sentence(fake()->numberBetween(3, 7), false), // ← fixed
            'description' => fake()->optional(0.7)->paragraph(),
            'status'      => fake()->randomElement(['todo', 'todo', 'in_progress', 'done']),
            'priority'    => fake()->randomElement(['low', 'medium', 'medium', 'high']),
            'due_date'    => fake()->optional(0.6)->dateTimeBetween('now', '+3 months'),
        ];
    }

    public function todo(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'todo']);
    }

    public function inProgress(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'in_progress']);
    }

    public function done(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'done']);
    }
}
