<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create currency_rates table migration.
 *
 * @requirement DB-018 Create `currency_rates` table
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3)->default('AUD');
            $table->string('target_currency', 3);
            $table->decimal('rate', 16, 8);
            $table->timestamp('fetched_at');
            $table->timestamps();

            // Unique constraint for currency pair
            $table->unique(['base_currency', 'target_currency']);

            // Indexes
            $table->index('fetched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_rates');
    }
};
