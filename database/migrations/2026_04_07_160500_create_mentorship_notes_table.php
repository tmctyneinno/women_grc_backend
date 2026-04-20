<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentorship_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentorship_id')->constrained('mentorships')->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('mentorship_sessions')->nullOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->enum('visibility', ['shared', 'mentor_private', 'mentee_private'])->default('shared');
            $table->text('content');
            $table->timestamps();

            $table->index(['mentorship_id', 'visibility']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentorship_notes');
    }
};
