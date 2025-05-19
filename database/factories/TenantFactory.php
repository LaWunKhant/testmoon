<?php

namespace Database\Factories;

use App\Models\House;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory; // Import the House model

class TenantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Ensure a house exists.
        $house = House::factory()->create();

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'house_id' => $house->id, // Use the House ID
            'rent' => $this->faker->randomFloat(2, 100, 1000),
        ];
    }
}
