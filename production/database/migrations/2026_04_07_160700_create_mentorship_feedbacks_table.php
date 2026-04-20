<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentorship_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentorship_id')->constrained('mentorships')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['mentor', 'mentee']);
            $table->unsignedTinyInteger('communication_quality');
            $table->unsignedTinyInteger('goal_achievement');
            $table->unsignedTinyInteger('engagement_frequency');
            $table->unsignedTinyInteger('satisfaction');
            $table->text('comments')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['mentorship_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentorship_feedbacks');
    }
};
