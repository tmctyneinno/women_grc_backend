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
        Schema::table('users', function (Blueprint $table) {
            $table->string('linkedin_profile')->nullable()->after('email');
            
            $table->string('google_id')->nullable()->after('linkedin_profile');
            
            $table->boolean('is_google_account')->default(false)->after('google_id');
            
            $table->index('linkedin_profile');
            $table->index('google_id');
            $table->index('is_google_account');
            $table->index(['email', 'google_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['linkedin_profile']);
            $table->dropIndex(['google_id']);
            $table->dropIndex(['is_google_account']);
            $table->dropIndex(['email', 'google_id']);
            
            // Drop columns
            $table->dropColumn(['linkedin_profile', 'google_id', 'is_google_account']);
        });
    }
};
