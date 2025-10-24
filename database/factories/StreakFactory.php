<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Family;
use App\Models\Assignment;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Streak>
 */
class StreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $current = fake()->numberBetween(0, 15);
        $best = max($current, fake()->numberBetween($current, 30));
        $started = fake()->dateTimeBetween('-2 months', '-1 week');
        $last = fake()->boolean(80) ? fake()->dateTimeBetween('-1 week', 'now') : null;

        return [
            'family_id' => Family::factory(),
            'assignment_id' => Assignment::factory()->state(function (array $attributes) {
                return [
                    'family_id' => $attributes['family_id'] ?? Family::factory(),
                ];
            }),
            'current_streak' => $current,
            'best_streak' => $best,
            'streak_started_on' => $started,
            'last_completed_on' => $last,
        ];
    }

    /**
     * Associate the streak with a specific family and ensure assignment matches it.
     */
    public function forFamily($family): static
    {
        return $this->state(function () use ($family) {
            return [
                'family_id' => $family,
                'assignment_id' => Assignment::factory()->state([
                    'family_id' => $family,
                ]),
            ];
        });
    }
}
