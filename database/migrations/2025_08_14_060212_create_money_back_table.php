<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('money_back', function (Blueprint $table) {
            $table->id();

            // Booking reference
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();

            // 'refund' or 'return'
            $table->enum('type', ['refund', 'return']);

            // Optional but typically useful for manual entries:
            $table->decimal('amount', 10, 2)->nullable();   
            $table->string('reference')->nullable();        
            $table->text('note')->nullable();               
            $table->timestamp('processed_at')->nullable();  

            $table->timestamps();

            // Helpful index for filtering in admin
            $table->index(['booking_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('money_back');
    }
};
