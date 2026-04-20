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

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete(); // delete comments when user is deleted

            // These two columns together form the polymorphic relationship
            $table->morphs('commentable');
            // ↑ shorthand for:
            // $table->unsignedBigInteger('commentable_id');
            // $table->string('commentable_type');
            // + adds a composite index on both columns automatically

            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
