<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // References the user who made the booking
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // References the venue
            $table->foreignId('venue_id')
                ->constrained('venue_details')
                ->cascadeOnDelete();

            // References the floor within that venue
            // $table->foreignId('floor_id')
            //     ->constrained('venue_floors')
            //     ->cascadeOnDelete();
            $table->boolean('single_time')->default(false);
            $table->boolean('full_venue')->default(false);
            $table->boolean('full_time')->default(false);

            // References the selected time slot
            $table->foreignId('time_slot_id')
                ->constrained('venue_time_slots')
                ->cascadeOnDelete();

            // References the payment record (nullable until paid)
            $table->foreignId('payment_id')
                ->nullable()
                ->constrained('payments')
                ->nullOnDelete();

            // Booking status
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
