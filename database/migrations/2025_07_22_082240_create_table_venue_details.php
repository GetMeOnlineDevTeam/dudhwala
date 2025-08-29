<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('venue_details', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name');
            $table->text('about')->nullable();
            $table->text('amenities')->nullable();
            $table->boolean('multi_floor')->default(false);
            $table->unsignedInteger('total_floor')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_venue_details');
    }
};
