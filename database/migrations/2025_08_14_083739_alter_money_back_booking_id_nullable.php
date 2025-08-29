<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('money_back', function (Blueprint $table) {
            // Drop old FK first (name may vary; adjust if needed)
            $table->dropForeign(['booking_id']);

            // Make column nullable
            $table->unsignedBigInteger('booking_id')->nullable()->change();

            // Re-add FK with nullOnDelete
            $table->foreign('booking_id')
                  ->references('id')->on('bookings')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('money_back', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->unsignedBigInteger('booking_id')->nullable(false)->change();
            $table->foreign('booking_id')
                  ->references('id')->on('bookings')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
        });
    }
};
