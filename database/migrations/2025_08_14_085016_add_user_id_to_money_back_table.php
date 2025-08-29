<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Add user_id right after `id` (which places it before `booking_id`)
        Schema::table('money_back', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')                // ensures it's before booking_id
                ->constrained('users')
                ->nullOnDelete();            // keep record if user is deleted
        });

        // 2) Backfill user_id from existing bookings (if any)
        DB::statement('
            UPDATE money_back mb
            JOIN bookings b ON b.id = mb.booking_id
            SET mb.user_id = b.user_id
            WHERE mb.user_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('money_back', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
