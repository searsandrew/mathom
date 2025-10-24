<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
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
            'family_id' => \App\Models\Family::factory(),
            'name' => $name,
            'slug' => $slug,
            'applies_to' => fake()->randomElement(['chores', 'rewards', 'both']),
        ];
    }

    /**
     * Associate the category with a specific family.
     */
    public function forFamily($family): self
    {
        return $this->state(fn () => ['family_id' => $family]);
    }
}
