<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            // Polymorphic columns — always together, always named {relation}_id/_type
            $table->morphs('commentable');
            // ↑ This shorthand creates BOTH:
            //   $table->unsignedBigInteger('commentable_id');
            //   $table->string('commentable_type');
            //   AND a composite index on both columns for performance

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete(); // delete comments when user is deleted

            $table->text('body');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
