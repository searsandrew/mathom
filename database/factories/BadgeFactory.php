<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Family;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Badge>
 */
class BadgeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        $slug = str()->slug($name);

        return [
            'family_id' => Family::factory(),
            'name' => $name,
            'slug' => $slug,
            'description' => fake()->sentence(),
            'image' => fake()->imageUrl(256, 256, 'badge'),
            'criteria' => [
                'type' => fake()->randomElement(['streak', 'points', 'milestone']),
                'threshold' => fake()->numberBetween(1, 100),
            ],
        ];
    }

    /**
     * Associate the badge with a specific family.
     */
    public function forFamily($family): self
    {
        return $this->state(fn () => ['family_id' => $family]);
    }
}
