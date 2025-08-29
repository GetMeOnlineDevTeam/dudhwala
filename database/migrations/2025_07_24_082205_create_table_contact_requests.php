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
        Schema::create('contact_requests', function (Blueprint $table) {
            $table->id();                       // id
            $table->string('first_name');      // first_name
            $table->string('last_name');       // last_name
            $table->string('phone_no');        // phone_no
            $table->string('subject');         // subject
            $table->text('message');           // message
            $table->timestamps();              // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_requests');
    }
};
