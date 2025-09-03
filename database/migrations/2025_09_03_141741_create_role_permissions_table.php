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
        Schema::create('role_permissions', function (Blueprint $t) {
            $t->id();
            $t->string('role');                       // 'user' | 'admin' | 'superadmin'
            $t->foreignId('permission_id')
              ->constrained('permissions')
              ->cascadeOnDelete();
            $t->timestamps();

            $t->unique(['role','permission_id']);
            $t->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
