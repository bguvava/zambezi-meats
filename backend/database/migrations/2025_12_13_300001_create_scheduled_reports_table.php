<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create scheduled reports table for automated report delivery.
 *
 * @requirement RPT-020 Schedule automated reports
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('report_type'); // sales-summary, revenue, orders, etc.
            $table->string('frequency'); // daily, weekly, monthly
            $table->string('day_of_week')->nullable(); // For weekly: monday, tuesday, etc.
            $table->unsignedTinyInteger('day_of_month')->nullable(); // For monthly: 1-28
            $table->time('time_of_day')->default('08:00:00');
            $table->json('recipients'); // Array of email addresses
            $table->string('format')->default('pdf'); // pdf, csv
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'next_run_at']);
            $table->index('report_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
};
