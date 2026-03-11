<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $table->foreignId('forum_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_post_id')->nullable()->constrained('forum_posts')->nullOnDelete();
            $table->foreignId('quote_post_id')->nullable()->constrained('forum_posts')->nullOnDelete();
            $table->text('content');
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->index(['forum_thread_id', 'parent_post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_posts');
    }
};

