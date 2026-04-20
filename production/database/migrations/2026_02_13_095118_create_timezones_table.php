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
        Schema::create('timezone', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3);
            $table->string('timezone', 125);
            $table->float('gmt_offset', 10, 2)->nullable();
            $table->float('dst_offset', 10, 2)->nullable();
            $table->float('raw_offset', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['country_code', 'timezone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timezone');
    }
};
