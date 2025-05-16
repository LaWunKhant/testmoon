<?php

namespace Database\Seeders;

use App\Models\House;
use App\Models\User; // Import the User model
use Illuminate\Database\Seeder;

class HouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user from the database
        $user = User::first();

        if ($user) {
            // Use the user's ID when creating a house
            House::factory()->create([
                'owner_id' => $user->id,
            ]);
        }
        // You can 확장 this to create multiple houses with different owners
        //  $users = User::all();
        //   foreach ($users as $user) {
        //       House::factory()->count(2)->create([  //creates 2 houses for each user
        //           'owner_id' => $user->id,
        //       ]);
        //   }
    }
}
