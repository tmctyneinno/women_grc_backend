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
        Schema::table('events', function (Blueprint $table) {
            $table->renameColumn('event_date', 'start_date');
            $table->renameColumn('end_event_date', 'end_date');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->renameColumn('start_date', 'event_date');
            $table->renameColumn('end_date', 'end_event_date');
        });
    }
};
