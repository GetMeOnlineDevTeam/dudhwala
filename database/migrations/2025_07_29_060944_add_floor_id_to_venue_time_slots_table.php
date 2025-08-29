<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // /**
    //  * Run the migrations.
    //  */
    // public function up(): void
    // {
    //     Schema::table('venue_time_slots', function (Blueprint $table) {
    //         $table->unsignedBigInteger('floor_id')->nullable()->after('venue_id');
    //         $table->foreign('floor_id')->references('id')->on('venue_floors')->onDelete('cascade');
    //     });
    // }

    // /**
    //  * Reverse the migrations.
    //  */
    // public function down(): void
    // {
    //     Schema::table('venue_time_slots', function (Blueprint $table) {
    //         $table->dropForeign(['floor_id']);
    //         $table->dropColumn('floor_id');
    //     });
    // }
};
