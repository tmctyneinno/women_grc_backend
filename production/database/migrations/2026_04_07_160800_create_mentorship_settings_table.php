<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentorship_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('max_mentees_per_mentor')->default(3);
            $table->unsignedInteger('default_duration_months')->default(3);
            $table->json('reminder_intervals')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentorship_settings');
    }
};
