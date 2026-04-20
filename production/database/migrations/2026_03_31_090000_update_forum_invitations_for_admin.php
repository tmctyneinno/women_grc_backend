<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_invitations', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('forum_id')->constrained('admins')->nullOnDelete();
            $table->string('token', 80)->nullable()->after('invited_user_id')->unique();
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE forum_invitations MODIFY COLUMN invited_by BIGINT UNSIGNED NULL");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE forum_invitations MODIFY COLUMN invited_by BIGINT UNSIGNED NOT NULL");
        }

        Schema::table('forum_invitations', function (Blueprint $table) {
            $table->dropUnique(['token']);
            $table->dropColumn(['admin_id', 'token']);
        });
    }
};
