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
        Schema::create('booking_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('booking_id');
            $t->string('name');                     // e.g. "Chair"
            $t->unsignedInteger('qty')->default(1);
            $t->decimal('unit_price', 10, 2);       // rental charge per unit
            $t->decimal('total', 10, 2);       // qty * unit_price
            $t->timestamps();

            $t->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
