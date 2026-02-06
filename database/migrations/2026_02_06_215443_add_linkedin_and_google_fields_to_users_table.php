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
             // Add linkedin_profile column
            $table->string('linkedin_profile')->nullable()->after('email');
            
            // Add google_id column for OAuth
            $table->string('google_id')->nullable()->after('linkedin_profile');
            
            // Add is_google_account column
            $table->boolean('is_google_account')->default(false)->after('google_id');
            
            // Add index for faster lookups
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
            //
        });
    }
};
