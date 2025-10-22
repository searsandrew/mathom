<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Occurrence>
 */
class OccurrenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'due_date' => fake()->dateTimeBetween('now', '+1 year'),
            'points_awarded' => fake()->numberBetween(1, 100),
            'status' => fake()->randomElement(['pending', 'submitted', 'approved', 'rejected', 'missed']),
        ];
    }
}
