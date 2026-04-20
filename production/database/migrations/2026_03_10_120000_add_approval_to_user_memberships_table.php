<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_memberships', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->after('status');
            $table->timestamp('approved_at')->nullable()->after('approval_status');
            $table->foreignId('approved_by_admin_id')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete()
                ->after('approved_at');
        });

        DB::table('user_memberships')
            ->update([
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_memberships', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by_admin_id');
            $table->dropColumn(['approval_status', 'approved_at']);
        });
    }
};
