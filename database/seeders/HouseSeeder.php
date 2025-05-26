<?php

namespace Database\Seeders;

use App\Models\House;
use Illuminate\Database\Seeder; // Ensure House model is imported

class HouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // *** Create houses with explicit create calls and set owner_id to 1 ***
        // Example if your HouseSeeder uses explicit create calls:
        House::create([
            'name' => 'Example House 1',
            'address' => '123 Main St',
            'owner_id' => 1, // *** Set the owner_id to 1 ***
            'description' => null, // Ensure these match your columns
            'price' => 0.00,
            'capacity' => 1,
            'photo_path' => null,
            // ... other attributes ...
        ]);

        // Add more explicit create calls for the owner's houses (e.g., 3 houses total)
        House::create([
            'name' => 'Example House 2',
            'address' => '456 Oak Ave',
            'owner_id' => 1, // *** Set the owner_id to 1 ***
            'description' => null,
            'price' => 0.00,
            'capacity' => 1,
            'photo_path' => null,
            // ... other attributes ...
        ]);

        House::create([
            'name' => 'Example House 3',
            'address' => '789 Pine Ln',
            'owner_id' => 1, // *** Set the owner_id to 1 ***
            'description' => null,
            'price' => 0.00,
            'capacity' => 1,
            'photo_path' => null,
            // ... other attributes ...
        ]);
    }
}
