<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('venue_floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venue_details')->onDelete('cascade');
            $table->integer('floor_no')->nullable();
            $table->decimal('floor_price', 10, 2)->nullable();         // <-- allow null
            $table->decimal('full_time_price', 10, 2)->nullable();    // <-- allow null
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_floors');
    }
};
