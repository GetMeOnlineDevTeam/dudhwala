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
        Schema::create('venue_addr', function (Blueprint $table) {
            $table->id();

            // Foreign key to venue_details
            $table->unsignedBigInteger('venue_id');
            $table->foreign('venue_id')->references('id')->on('venue_details')->onDelete('cascade');

            $table->string('city');
            $table->string('state');
            $table->text('addr');
            $table->string('pincode', 10);
            $table->text('google_link')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_venue_addr');
    }
};
