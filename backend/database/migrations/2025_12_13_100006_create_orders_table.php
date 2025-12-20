<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create orders table migration.
 *
 * @requirement DB-008 Create `orders` table
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('delivery_zone_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_number', 20)->unique();
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'ready',
                'out_for_delivery',
                'delivered',
                'cancelled',
            ])->default('pending');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('currency', ['AUD', 'USD'])->default('AUD');
            $table->decimal('exchange_rate', 10, 6)->default(1);
            $table->string('promotion_code', 50)->nullable();
            $table->text('notes')->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->string('scheduled_time_slot', 50)->nullable();
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            // Indexes for optimized queries
            $table->index('user_id');
            $table->index('status');
            $table->index('assigned_staff_id');
            $table->index('created_at');
            $table->index('delivered_at');
            $table->index('scheduled_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
