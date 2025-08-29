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
        Schema::table('venue_time_slots', function (Blueprint $table) {
            $table->foreignId('floor_id')
                ->nullable()
                ->constrained('venue_floors')
                ->onDelete('cascade')->after('venue_id'); // Ensure this is after the venue_id column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venue_time_slots', function (Blueprint $table) {
            $table->dropForeign(['floor_id']);
            $table->dropColumn('floor_id');
        });
    }
};
