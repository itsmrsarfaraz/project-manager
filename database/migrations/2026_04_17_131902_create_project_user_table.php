<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot table naming convention: ALPHABETICAL order, singular, snake_case
        // "project_user" not "user_project" or "projects_users"
        Schema::create('project_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete(); // member removed when project deleted

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete(); // membership removed when user deleted

            $table->enum('role', ['owner', 'manager', 'member'])
                ->default('member');

            $table->timestamps();

            // A user can only appear ONCE per project
            $table->unique(['project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_user');
    }
};
