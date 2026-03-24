<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('podcast_listens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('podcast_id')->constrained('podcasts')->cascadeOnDelete();
            $table->integer('last_position_seconds')->default(0);
            $table->integer('duration_seconds')->nullable();
            $table->integer('progress_seconds')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_listened_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'podcast_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('podcast_listens');
    }
};
