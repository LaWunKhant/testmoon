<?php

namespace Database\Seeders;

use App\Models\House;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import the House model
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create();

        // Get all existing house IDs
        $houseIds = House::pluck('id')->toArray();

        // If no houses exist, create one
        if (empty($houseIds)) {
            $house = House::create([
                'name' => 'Default House', //  set appropriate values
                'address' => '123 Main St',   //  set appropriate values
                //  Add other required house fields
            ]);
            $houseIds = [$house->id]; // Add the new house's ID to the array
        }

        for ($i = 0; $i < 10; $i++) {
            DB::table('tenants')->insert([
                'name' => Str::random(10),
                'email' => Str::random(10).'@example.com',
                'phone' => $faker->phoneNumber(),
                'house_id' => $faker->randomElement($houseIds), //  use a valid house ID
            ]);
        }
    }
}
