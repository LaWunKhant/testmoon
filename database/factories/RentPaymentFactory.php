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
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'amount' => $this->faker->randomFloat(2, 100, 1000),
            'paid' => $this->faker->boolean(),
            'description' => $this->faker->optional()->sentence(),
            'reminder_sent' => $this->faker->boolean(20), // 20% chance of being true
            'reminder_sent_at' => $this->faker->optional(0.2)->dateTimeBetween('-1 week', 'now'), // Only set if reminder was sent
        ];
    }
}
