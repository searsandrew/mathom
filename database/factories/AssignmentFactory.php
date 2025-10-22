<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'frequency' => fake()->randomElement(['daily', 'weekly', 'monthly']),
            'points' => fake()->numberBetween(1, 100),
            'due_date' => fake()->dateTimeBetween('+1 week', '+1 month'),
            'started_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'completed_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
