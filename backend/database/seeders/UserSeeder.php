<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * User seeder.
 *
 * Creates test users for all roles.
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@zambezimeats.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'phone' => '+61 2 1234 5678',
            'status' => User::STATUS_ACTIVE,
        ]);

        // Create staff users
        User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@zambezimeats.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STAFF,
            'phone' => '+61 2 1234 5679',
            'status' => User::STATUS_ACTIVE,
        ]);

        // Create customer users
        User::factory()->count(3)->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);

        // Create suspended user
        User::factory()->suspended()->create([
            'name' => 'Suspended User',
            'email' => 'suspended@example.com',
            'role' => User::ROLE_CUSTOMER,
        ]);

        // Create inactive user
        User::factory()->inactive()->create([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'role' => User::ROLE_CUSTOMER,
        ]);

        // Create more random customers for pagination testing
        User::factory()->count(20)->customer()->create();
    }
}
