<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add delivery issue columns to orders table.
 *
 * @requirement DEL-009 Handle delivery issues
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('delivery_issue')->nullable()->after('delivery_instructions');
            $table->timestamp('delivery_issue_reported_at')->nullable()->after('delivery_issue');
            $table->timestamp('delivery_issue_resolved_at')->nullable()->after('delivery_issue_reported_at');
            $table->text('delivery_issue_resolution')->nullable()->after('delivery_issue_resolved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_issue',
                'delivery_issue_reported_at',
                'delivery_issue_resolved_at',
                'delivery_issue_resolution',
            ]);
        });
    }
};
