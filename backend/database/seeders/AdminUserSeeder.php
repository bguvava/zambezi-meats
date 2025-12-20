<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder for creating admin user.
 */
class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@zambezimeats.com.au'],
            [
                'name' => 'Admin User',
                'email' => 'admin@zambezimeats.com.au',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'phone' => '+61 400 000 000',
                'is_active' => true,
                'currency_preference' => User::CURRENCY_AUD,
                'email_verified_at' => now(),
            ]
        );

        // Create a staff user for testing
        User::updateOrCreate(
            ['email' => 'staff@zambezimeats.com.au'],
            [
                'name' => 'Staff User',
                'email' => 'staff@zambezimeats.com.au',
                'password' => Hash::make('password'),
                'role' => User::ROLE_STAFF,
                'phone' => '+61 400 000 001',
                'is_active' => true,
                'currency_preference' => User::CURRENCY_AUD,
                'email_verified_at' => now(),
            ]
        );

        // Create a customer user for testing
        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Test Customer',
                'email' => 'customer@example.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CUSTOMER,
                'phone' => '+61 400 000 002',
                'is_active' => true,
                'currency_preference' => User::CURRENCY_AUD,
                'email_verified_at' => now(),
            ]
        );
    }
}
