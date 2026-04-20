<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()           // null = system action
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();   // delete log when project deleted

            $table->string('type');      // 'task_created', 'task_updated', etc.
            $table->string('description'); // human-readable log entry

            // Store context as JSON — flexible, no rigid schema
            $table->json('metadata')->nullable();
            // e.g. {"from": "todo", "to": "in_progress", "field": "status"}

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
