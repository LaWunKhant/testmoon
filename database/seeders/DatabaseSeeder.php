<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder; // Ensure User model is imported
use Illuminate\Support\Facades\Hash; // Ensure Hash facade is imported

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // *** Create a specific owner account with fixed credentials ***
        User::create([
            'name' => 'Test Owner', // You can set the name
            'email' => 'owner@example.com', // *** FIXED EMAIL ***
            'password' => Hash::make('password'), // *** FIXED PASSWORD (hashed) ***
            // Add any other required user fields here...
            // 'email_verified_at' => now(),
        ]);

        // *** Adjust or comment out your existing user factory/seeder calls ***
        // If your existing seeders create many random users, you might comment out
        // the call to create users for testing this specific owner account.
        // User::factory(10)->create(); // Example of creating 10 random users (comment out if needed)

        // Call other seeders for other tables (tenants, houses, etc.)
        // Make sure your seeders for Tenants and Houses create records linked to owner_id = 1 (the fixed owner user's ID)
        $this->call([
            // ... other seeders you might call ...
            HouseSeeder::class, // *** Ensure this line is present and NOT commented out ***
            TenantSeeder::class, // Ensure TenantSeeder is also called
            // ...
        ]);
    }
}
