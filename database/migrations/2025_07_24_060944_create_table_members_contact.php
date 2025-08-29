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
        Schema::create('members_contact', function (Blueprint $table) {
            $table->id();                                // id
            $table->string('name');                      // name
            $table->enum('contact_type', ['phone','email']); // contact_type
            $table->string('contact');                   // phone number or email
            $table->boolean('is_active')->default(false); // is_active
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members_contact');
    }
};
