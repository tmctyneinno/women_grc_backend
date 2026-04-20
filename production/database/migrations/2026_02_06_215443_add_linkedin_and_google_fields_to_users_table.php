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
            // Add the required fields from your registration form
            $table->string('linkedin_profile')->nullable()->after('email');
            
            // For Google OAuth
            $table->string('google_id')->nullable()->after('linkedin_profile');
            $table->boolean('is_google_account')->default(false)->after('google_id');
            
            // Add indexes for better performance
            $table->index('linkedin_profile');
            $table->index('google_id');
            $table->index('is_google_account');
        });
        
        // Optionally, make email nullable if you want to support Google users without email
        // Schema::table('users', function (Blueprint $table) {
        //     $table->string('email')->nullable()->change();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove indexes first
            $table->dropIndex(['linkedin_profile']);
            $table->dropIndex(['google_id']);
            $table->dropIndex(['is_google_account']);
            
            // Remove columns
            $table->dropColumn(['linkedin_profile', 'google_id', 'is_google_account']);
        });
    }
};