<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot table: alphabetical order → label_task
        Schema::create('label_task', function (Blueprint $table) {
            $table->foreignId('label_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('task_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->primary(['label_id', 'task_id']); // composite primary key
            // No timestamps needed on simple pivot tables
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('label_task');
    }
};
