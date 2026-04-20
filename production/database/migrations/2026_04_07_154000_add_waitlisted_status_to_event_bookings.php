<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE event_bookings MODIFY status ENUM('pending','confirmed','paid','cancelled','waitlisted') NOT NULL DEFAULT 'pending'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE event_bookings ALTER COLUMN status TYPE VARCHAR(20)");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE event_bookings MODIFY status ENUM('pending','confirmed','paid','cancelled') NOT NULL DEFAULT 'pending'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE event_bookings ALTER COLUMN status TYPE VARCHAR(20)");
        }
    }
};
