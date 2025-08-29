<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Who paid
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // // (Optional) Link back to the booking record
            // $table->foreignId('booking_id')
            //       ->nullable()
            //       ->constrained('bookings')
            //       ->nullOnDelete();

            // How much was charged
            $table->decimal('amount', 10, 2);

            // Method: online via Razorpay, or offline (cash/cheque/etc)
            $table->enum('method', ['online', 'offline'])->default('online');

            // Razorpay fields (populated only for online payments)
            $table->string('razorpay_order_id')->nullable()->unique();
            $table->string('razorpay_payment_id')->nullable()->unique();

            // Offline reference (e.g. cheque number, UPI txn ID, etc)
            $table->string('offline_reference')->nullable();

            // Status of the payment
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');

            // When the payment was actually captured
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
