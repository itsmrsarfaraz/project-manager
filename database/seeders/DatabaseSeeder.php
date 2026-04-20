<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ORDER MATTERS: Users must exist before Projects,
        // Projects must exist before Tasks
        $this->call([
            UserSeeder::class,
            ProjectSeeder::class,
            // TaskSeeder is handled inside ProjectSeeder for now
        ]);
    }
}
