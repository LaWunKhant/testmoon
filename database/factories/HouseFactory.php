<?php

namespace Database\Factories;

use App\Models\House;
use App\Models\User; // Import User Model
use Illuminate\Database\Eloquent\Factories\Factory;

class HouseFactory extends Factory
{
    protected $model = House::class;

    public function definition()
    {
        // Get a user.  Create one if none exists.
        $user = User::first() ?? User::factory()->create();

        return [
            'name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'owner_id' => $user->id, // Use the user's id
        ];
    }
}
