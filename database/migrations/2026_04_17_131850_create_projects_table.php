<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')          // FK to users table
                ->constrained('users')           // enforces referential integrity
                ->cascadeOnDelete();             // if user deleted, delete their projects

            $table->string('name');                // project name, max 255 chars
            $table->text('description')->nullable(); // optional description

            $table->enum('status', ['active', 'archived', 'completed'])
                ->default('active');             // controlled vocabulary, not free text

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
