<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add POD details columns to delivery_proofs table.
 *
 * @requirement STAFF-012 POD capture interface
 * @requirement STAFF-013 Store POD with order
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_proofs', function (Blueprint $table) {
            $table->text('signature_data')->nullable()->after('photo_path');
            $table->string('recipient_name')->nullable()->after('signature_data');
            $table->boolean('left_at_door')->default(false)->after('notes');
            $table->timestamp('captured_at')->nullable()->after('captured_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_proofs', function (Blueprint $table) {
            $table->dropColumn([
                'signature_data',
                'recipient_name',
                'left_at_door',
                'captured_at',
            ]);
        });
    }
};
