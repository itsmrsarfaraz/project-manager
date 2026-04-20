<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tasks: most common queries filter by project + status/priority
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'priority']);
            $table->index(['assigned_to', 'status']); // "my tasks" query on dashboard
            $table->index('due_date');                 // overdue tasks query
        });

        // Activities: always queried by project + ordered by date
        Schema::table('activities', function (Blueprint $table) {
            $table->index(['project_id', 'created_at']);
        });

        // Comments: always queried by commentable + ordered by date
        Schema::table('comments', function (Blueprint $table) {
            $table->index(['commentable_type', 'commentable_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'status']);
            $table->dropIndex(['project_id', 'priority']);
            $table->dropIndex(['assigned_to', 'status']);
            $table->dropIndex(['due_date']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'created_at']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['commentable_type', 'commentable_id', 'created_at']);
        });
    }
};
