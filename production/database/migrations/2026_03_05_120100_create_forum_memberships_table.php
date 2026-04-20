<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['creator', 'moderator', 'member'])->default('member');
            $table->enum('status', ['active', 'removed'])->default('active');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['forum_id', 'user_id']);
            $table->index(['forum_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_memberships');
    }
};

