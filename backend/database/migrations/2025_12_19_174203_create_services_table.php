<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @requirement SERV-003 Services listing with billing cycles
     * @requirement SERV-007 Billing cycle options (one_time, monthly, quarterly, yearly)
     * @requirement SERV-014 Features list component
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained('service_categories')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('features')->nullable(); // Array of feature strings
            $table->decimal('price_aud', 10, 2);
            $table->decimal('price_usd', 10, 2)->nullable();
            $table->decimal('price_zwl', 12, 2)->nullable();
            $table->enum('billing_cycle', ['one_time', 'monthly', 'quarterly', 'yearly'])->default('one_time');
            $table->string('icon')->nullable(); // Icon path or name
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            // Indexes for performance
            $table->index('service_category_id');
            $table->index('slug');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('billing_cycle');

            // Fulltext index for MySQL only (SQLite doesn't support it)
            if (config('database.default') !== 'sqlite') {
                $table->fullText(['name', 'description']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
