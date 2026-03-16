<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('podcasts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->nullable()->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('audio_path');
            $table->string('cover_path')->nullable();
            $table->string('duration')->nullable();
            $table->string('tag')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('podcasts');
    }
};
