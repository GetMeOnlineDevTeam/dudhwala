<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')
                ->constrained('venue_details')
                ->onDelete('cascade');


            $table->string('name');

            $table->time('start_time');
            $table->time('end_time');

            $table->boolean('single_time')->default(false);
            $table->boolean('full_venue')->default(false);
            $table->boolean('full_time')->default(false);

            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_time_slots');
    }
};
