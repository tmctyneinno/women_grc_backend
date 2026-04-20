<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('domain')->nullable();
            $table->string('region')->nullable();
            $table->string('country')->nullable();
            $table->text('bio')->nullable();
            $table->text('expertise_summary')->nullable();
            $table->enum('availability_status', ['available', 'busy', 'not_taking'])->default('available');
            $table->json('languages')->nullable();
            $table->json('skills')->nullable();
            $table->json('certifications')->nullable();
            $table->unsignedInteger('mentorships_completed')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('max_mentees')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('user_id');
            $table->index(['availability_status', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentors');
    }
};
