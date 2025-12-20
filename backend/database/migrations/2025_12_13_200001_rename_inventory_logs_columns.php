<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rename inventory_logs columns from quantity_before/after to stock_before/after.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_logs', function (Blueprint $table) {
            $table->renameColumn('quantity_before', 'stock_before');
            $table->renameColumn('quantity_after', 'stock_after');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_logs', function (Blueprint $table) {
            $table->renameColumn('stock_before', 'quantity_before');
            $table->renameColumn('stock_after', 'quantity_after');
        });
    }
};
