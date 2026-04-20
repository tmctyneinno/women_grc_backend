<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('domain');
            $table->string('region');
            $table->string('country');
            $table->text('bio');
            $table->text('expertise_summary');
            $table->enum('availability_status', ['available', 'busy', 'not_taking'])->default('available');
            $table->json('languages');
            $table->json('skills');
            $table->json('certifications');
            $table->unsignedInteger('max_mentees')->nullable();
            $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_applications');
    }
};
