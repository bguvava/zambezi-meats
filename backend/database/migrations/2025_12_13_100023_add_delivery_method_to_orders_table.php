<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add delivery method and pickup/delivery date columns to orders table.
 *
 * @requirement STAFF-009 Today's Deliveries list
 * @requirement STAFF-024 Pickup management
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('delivery_method', ['delivery', 'pickup'])->default('delivery')->after('exchange_rate');
            $table->date('delivery_date')->nullable()->after('delivery_method');
            $table->string('delivery_time_slot', 50)->nullable()->after('delivery_date');
            $table->date('pickup_date')->nullable()->after('delivery_time_slot');
            $table->string('pickup_time_slot', 50)->nullable()->after('pickup_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_method',
                'delivery_date',
                'delivery_time_slot',
                'pickup_date',
                'pickup_time_slot',
            ]);
        });
    }
};
