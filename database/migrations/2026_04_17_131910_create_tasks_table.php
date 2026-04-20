<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete(); // tasks deleted when project is deleted

            $table->foreignId('assigned_to')       // nullable: task may be unassigned
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();               // if user deleted, task becomes unassigned

            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('status', ['todo', 'in_progress', 'done'])
                ->default('todo');

            $table->enum('priority', ['low', 'medium', 'high'])
                ->default('medium');

            $table->date('due_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
