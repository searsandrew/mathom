<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Redemption>
 */
class RedemptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => fake()->randomElement(['pending', 'approved', 'fulfilled', 'cancelled']),
            'notes' => fake()->sentence(),
            'requested_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'approved_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'fulfilled_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'cancelled_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
