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
            // Drop old name column
            $table->dropColumn('name');

            // Add first_name and last_name
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');

            // Add status column: pending by default
            $table->enum('status', ['pending', 'verified', 'blocked'])->default('pending')->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the new columns
            $table->dropColumn(['first_name', 'last_name', 'status']);

            // Add back the old name column
            $table->string('name')->after('id');
        });
    }
};
