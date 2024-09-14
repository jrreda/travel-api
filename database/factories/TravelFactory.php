<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Travel>
 */
class TravelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true); // Generate a unique name

        return [
            'name'           => $name,
            'slug'           => Str::slug($name),
            'is_public'      => fake()->boolean(),
            'description'    => fake()->paragraphs(3, true),
            'number_of_days' => fake()->numberBetween(1, 10),
        ];
    }

    // public function configure()
    // {
    //     return $this->afterCreating(function (Travel $travel) {
    //         Tour::factory()->count(3)->create(['travel_id' => $travel->id]);
    //     });
    // }
}
