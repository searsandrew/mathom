<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reward>
 */
class RewardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'image_path' => fake()->imageUrl(),
            'image_name' => fake()->word(),
            'is_active' => fake()->boolean(),
            'inventory' => fake()->numberBetween(1, 100),
            'price_points' => fake()->numberBetween(1, 100),
        ];
    }
}
