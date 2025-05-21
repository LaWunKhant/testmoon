<?php

namespace Database\Factories;

use App\Models\RentPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentPaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RentPayment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tenant_id' => \App\Models\Tenant::factory(), // Ensure Tenant factory exists and is correct
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'amount' => $this->faker->randomFloat(2, 50, 1000),
            'paid' => $this->faker->boolean, // Or set a default value if needed
            // Add any other fields your RentPayment model has
        ];
    }
}
