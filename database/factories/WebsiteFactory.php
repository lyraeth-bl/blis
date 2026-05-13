<?php

namespace Database\Factories;

use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Website>
 */
class WebsiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'url' => fake()->url(),
            'username' => fake()->userName(),
            'password' => fake()->password(),
            'category' => fake()->randomElement(['Business', 'Personal', 'Social Media', 'E-commerce', 'Other']),
            'description' => fake()->sentence(),
        ];
    }
}
