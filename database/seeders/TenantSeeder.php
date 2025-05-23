<?php

namespace Database\Seeders;

use App\Models\House;
use App\Models\Tenant;
use Faker\Factory;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create();
        $houseIds = House::pluck('id')->toArray();

        // If no houses exist, create one
        if (empty($houseIds)) {
            $house = House::create([
                'name' => 'Default House',
                'address' => '123 Main St',
            ]);
            $houseIds = [$house->id]; // Add the new house's ID to the array
        }

        for ($i = 0; $i < 10; $i++) {
            $tenant = Tenant::factory()->make();
            $tenant->phone = $faker->phoneNumber();
            $tenant->house_id = $faker->randomElement($houseIds);
            $tenant->save();
        }
    }
}
