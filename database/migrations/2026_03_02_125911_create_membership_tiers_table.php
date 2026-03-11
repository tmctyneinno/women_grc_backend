<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('membership_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., Student Member, Associate Member
            $table->decimal('annual_fee', 10, 2);
            $table->string('target_audience');
            $table->json('benefits'); // ["Monthly webinars", "Career support", ...]
            $table->string('invitation_only')->nullable(); // optional, for by invitation tiers
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_tiers');
    }
};
