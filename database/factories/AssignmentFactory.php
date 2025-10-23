<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Family;
use App\Models\Chore;
use App\Models\User;

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
            'family_id' => Family::factory(),
            'chore_id' => Chore::factory()->state(function (array $attributes) {
                return ['family_id' => $attributes['family_id'] ?? Family::factory()];
            }),
            'user_id' => null,
            'frequency' => fake()->randomElement(['daily', 'weekly', 'monthly']),
            'points' => fake()->numberBetween(1, 100),
            'due_date' => fake()->dateTimeBetween('+1 week', '+1 month'),
            'started_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'completed_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Assign to a specific user (who will be ensured to belong to the same family by tests/seeding logic).
     */
    public function forUser(?User $user = null): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user?->id ?? User::factory(),
            ];
        });
    }
}
