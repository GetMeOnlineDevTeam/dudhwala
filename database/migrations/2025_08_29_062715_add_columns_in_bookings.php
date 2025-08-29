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
        Schema::table('bookings', function (Blueprint $t) {
            $t->decimal('deposit_amount', 10, 2)->default(0)->after('venue_id');     // security deposit charged
            $t->decimal('items_amount', 10, 2)->default(0)->after('deposit_amount');       // total of rental items
            $t->string('settlement_status')->default('pending')->after('items_amount'); // pending|refund_due|collect_due|settled
            $t->timestamp('settled_at')->nullable()->after('settlement_status');
        });
    }
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $t) {
            $t->dropColumn(['deposit_amount', 'items_amount', 'settlement_status', 'settled_at']);
        });
    }
};
