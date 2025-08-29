<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('available_time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venue_details')->cascadeOnDelete();
            $table->unsignedSmallInteger('floor_no')->nullable()
                  ->comment('Null means “whole venue”');
            $table->foreignId('time_slot_id')->constrained('venue_time_slots')->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['venue_id','floor_no','time_slot_id','date'], 
                           'avail_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('available_time_slots');
    }
};
