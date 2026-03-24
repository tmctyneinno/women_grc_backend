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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('slug')->unique();
            $table->string('featured_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('venue');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('zip_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->enum('type', ['conference', 'workshop', 'seminar', 'meeting', 'networking', 'other'])->default('other');
            $table->enum('visibility', ['public', 'private', 'members_only'])->default('public');
            $table->integer('capacity')->nullable();
            $table->integer('registered_count')->default(0);
            $table->decimal('price', 10, 2)->default(0.00)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->json('registration_fields')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_online')->default(false);
            $table->text('meeting_link')->nullable();
            $table->json('speakers')->nullable();
            $table->json('sponsors')->nullable();
            $table->json('tags')->nullable();
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('status');
            $table->index('type');
            $table->index('start_date');
            $table->index('is_featured');
            $table->index(['status', 'start_date']);
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
