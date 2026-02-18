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
            $table->string('phone_number')->nullable()->after('email');
            $table->string('job_title')->nullable()->after('phone_number');
            $table->string('company')->nullable()->after('job_title');
            $table->foreignId('timezone_id')->nullable()->constrained('timezone')->nullOnDelete()->after('company');            
            $table->string('profile_picture')->nullable()->after('timezone_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_number',
                'job_title',
                'company',
                'timezone_id',
                'profile_picture'
            ]);
        });
    }
};
