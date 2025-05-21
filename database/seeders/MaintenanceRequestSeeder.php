<?php

namespace Database\Seeders;

use App\Models\House;
use App\Models\MaintenanceRequest;
use App\Models\Tenant;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class MaintenanceRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Ensure there are houses and tenants to link requests to
        $houseIds = House::pluck('id')->toArray();
        $tenantIds = Tenant::pluck('id')->toArray();

        // If no houses or tenants exist, create some first (for robust seeding)
        if (empty($houseIds)) {
            $house = House::factory()->create();
            $houseIds[] = $house->id;
        }
        if (empty($tenantIds)) {
            $tenant = Tenant::factory()->create(['house_id' => $houseIds[array_rand($houseIds)]]);
            $tenantIds[] = $tenant->id;
        }

        // Create 10 dummy maintenance requests
        for ($i = 0; $i < 10; $i++) {
            MaintenanceRequest::create([
                'house_id' => $faker->randomElement($houseIds),
                'tenant_id' => $faker->randomElement($tenantIds), // Link to a tenant
                'description' => $faker->sentence(5),
                'status' => $faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
                'scheduled_date' => $faker->optional()->dateTimeBetween('now', '+3 months'), // Can be null
            ]);
        }
    }
}
