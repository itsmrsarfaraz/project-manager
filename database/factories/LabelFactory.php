<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LabelFactory extends Factory
{
    public function definition(): array
    {
        $labels = [
            ['name' => 'bug',      'color' => '#ef4444'],
            ['name' => 'feature',  'color' => '#6366f1'],
            ['name' => 'urgent',   'color' => '#f97316'],
            ['name' => 'docs',     'color' => '#22c55e'],
            ['name' => 'review',   'color' => '#eab308'],
            ['name' => 'backend',  'color' => '#3b82f6'],
            ['name' => 'frontend', 'color' => '#ec4899'],
        ];

        $label = fake()->unique()->randomElement($labels);

        return [
            'name'  => $label['name'],
            'color' => $label['color'],
        ];
    }
}
