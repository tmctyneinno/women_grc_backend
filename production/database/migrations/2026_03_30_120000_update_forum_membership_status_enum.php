<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE forum_memberships MODIFY COLUMN status ENUM('pending','active','removed','rejected') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE forum_memberships MODIFY COLUMN status ENUM('active','removed') DEFAULT 'active'");
        }
    }
};
