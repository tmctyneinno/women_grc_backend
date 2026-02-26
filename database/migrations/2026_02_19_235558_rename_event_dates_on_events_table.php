<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Check if the old columns exist before renaming
            if (Schema::hasColumn('events', 'event_date') && !Schema::hasColumn('events', 'start_date')) {
                $table->renameColumn('event_date', 'start_date');
            }
            
            if (Schema::hasColumn('events', 'end_event_date') && !Schema::hasColumn('events', 'end_date')) {
                $table->renameColumn('end_event_date', 'end_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Check if the new columns exist before renaming back
            if (Schema::hasColumn('events', 'start_date') && !Schema::hasColumn('events', 'event_date')) {
                $table->renameColumn('start_date', 'event_date');
            }
            
            if (Schema::hasColumn('events', 'end_date') && !Schema::hasColumn('events', 'end_event_date')) {
                $table->renameColumn('end_date', 'end_event_date');
            }
        });
    }
};
