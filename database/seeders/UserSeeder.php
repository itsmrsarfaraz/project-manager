<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create ONE known user you can always log in as
        // This is your "developer account" — predictable credentials
        User::factory()->create([
            'name'  => 'Alice Admin',
            'email' => 'alice@example.com',
            // password is 'password' (from factory default)
        ]);

        User::factory()->create([
            'name'  => 'Bob Builder',
            'email' => 'bob@example.com',
        ]);

        User::factory()->create([
            'name'  => 'Carol Coder',
            'email' => 'carol@example.com',
        ]);

        // Then create 7 more random users (total: 10 users)
        User::factory(7)->create();
    }
}
