<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Family;
use App\Models\Wallet;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Allowance>
 */
class AllowanceFactory extends Factory
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
            // Ensure wallet belongs to the same family
            'wallet_id' => function (array $attributes) {
                return Wallet::factory()->state([
                    'family_id' => $attributes['family_id'] ?? Family::factory(),
                ]);
            },
            'status' => 'active',
            'frequency' => fake()->randomElement(['weekly', 'biweekly', 'monthly', 'custom']),
            'day_of_week' => fake()->numberBetween(0, 6),
            'day_of_month' => fake()->numberBetween(1, 28),
            'rrule_text' => null,
            'timezone' => config('app.timezone'),
            'mode' => fake()->randomElement(['fixed', 'earned', 'min_threshold', 'mixed']),
            'fixed_points' => fake()->numberBetween(0, 100),
            'min_approved_occurrences' => fake()->numberBetween(0, 10),
            'bonus_points_on_threshold' => fake()->numberBetween(0, 50),
            'starts_at' => now()->subWeek(),
            'ends_at' => null,
        ];
    }

    /**
     * Force the family for the allowance and align the wallet's family.
     */
    public function forFamily($family): self
    {
        return $this->state(function () use ($family) {
            return [
                'family_id' => $family,
                'wallet_id' => Wallet::factory()->state([
                    'family_id' => $family,
                ]),
            ];
        });
    }
}
