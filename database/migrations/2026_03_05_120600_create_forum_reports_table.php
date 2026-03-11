<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_post_id')->constrained('forum_posts')->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->text('details')->nullable();
            $table->enum('status', ['open', 'resolved', 'dismissed'])->default('open');
            $table->timestamps();

            $table->unique(['forum_post_id', 'reported_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_reports');
    }
};

