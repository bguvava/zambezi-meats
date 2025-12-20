<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CurrencyRate;
use Illuminate\Database\Seeder;

/**
 * Seeder for creating initial currency rates.
 */
class CurrencyRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // AUD to USD rate
        CurrencyRate::setRate('AUD', 'USD', 0.65);

        // USD to AUD rate
        CurrencyRate::setRate('USD', 'AUD', 1.54);
    }
}
