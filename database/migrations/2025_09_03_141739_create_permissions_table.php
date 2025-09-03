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
         Schema::create('permissions', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();    // e.g. manage_venues
            $t->string('label')->nullable(); // e.g. "Create/Update/Delete Venues"
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
