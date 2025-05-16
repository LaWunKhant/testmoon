<?php

namespace Database\Factories;

use App\Models\RentPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentPaymentFactory extends Factory
{
    protected $model = RentPayment::class;

    public function definition()
    {
        return [
            'tenant_id' => $this->faker->randomNumber(),
            'house_id' => $this->faker->randomNumber(),
            'amount' => $this->faker->randomFloat(2, 100, 1000),
            'paid' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => $this->faker->randomElement(['paid', 'pending']),
        ];
    }
}
