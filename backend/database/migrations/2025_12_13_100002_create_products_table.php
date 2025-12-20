<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create products table migration.
 *
 * @requirement DB-004 Create `products` table
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price_aud', 10, 2);
            $table->decimal('sale_price_aud', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('sku', 50)->unique();
            $table->string('unit', 10)->default('kg');
            $table->decimal('weight_kg', 10, 3)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for optimized queries
            $table->index('category_id');
            $table->index('is_featured');
            $table->index('is_active');
            $table->index('price_aud');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
