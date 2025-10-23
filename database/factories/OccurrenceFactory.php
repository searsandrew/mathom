<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Family;
use App\Models\Assignment;

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
            'family_id' => Family::factory(),
            'assignment_id' => Assignment::factory()->state(function (array $attributes) {
                return ['family_id' => $attributes['family_id'] ?? Family::factory()];
            }),
            'due_date' => fake()->dateTimeBetween('now', '+1 year'),
            'points_awarded' => fake()->numberBetween(1, 100),
            'status' => fake()->randomElement(['pending', 'submitted', 'approved', 'rejected', 'missed']),
        ];
    }
}
