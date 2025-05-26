<?php

namespace Database\Seeders;

use App\Models\House;
use App\Models\Tenant; // Ensure Tenant model is imported
use Faker\Factory; // Ensure House model is imported
use Illuminate\Database\Seeder; // Ensure Factory is imported
use Illuminate\Support\Arr; // Ensure Arr facade is imported (for random element)
use Illuminate\Support\Facades\Log; // Use Log for warnings

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();

        // *** Get the IDs of houses specifically owned by owner ID 1 ***
        $ownerHouseIds = House::where('owner_id', 1)->pluck('id')->toArray();

        // Ensure there are houses owned by owner 1 before creating tenants
        if (empty($ownerHouseIds)) {
            Log::warning('No houses found for owner ID 1. Skipping tenant seeding for dashboard display.');

            return; // Stop seeding tenants if no houses are available for the owner
        }

        // *** Create tenants and link them ONLY to houses owned by owner 1 ***
        // Create 10 tenants, for example, and link them to the owner's houses
        for ($i = 0; $i < 10; $i++) {
            $tenant = Tenant::factory()->make(); // Create a tenant model instance (not saved yet)

            // Assign attributes using Faker (ensure your factory also defines these or set defaults)
            $tenant->phone = $faker->phoneNumber();
            // Set the house_id using a random element from ONLY the owner's house IDs
            $tenant->house_id = Arr::random($ownerHouseIds); // Randomly pick a house ID from the array

            $tenant->save(); // Save the tenant record to the database
        }

        Log::info('Finished seeding tenants, linked to houses owned by owner ID 1.');

        // You might still create other tenants not linked to these houses for testing
        // \App\Models\Tenant::factory(5)->create(); // Example: Create other tenants (will likely have null house_id if not explicitly set)
    }
}
