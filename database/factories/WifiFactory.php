<?php

namespace Database\Factories;

use App\Models\Wifi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wifi>
 */
class WifiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ssid' => fake()->company().' WiFi',
            'location' => fake()->address(),
            'ip_address' => fake()->ipv4(),
            'password' => fake()->password(),
            'router_type' => fake()->randomElement(['tenda', 'tp-link', 'ruijie']),
            'admin_username' => fake()->userName(),
            'admin_password' => fake()->password(),
            'link' => 'http://'.fake()->ipv4(),
            'description' => fake()->sentence(),
        ];
    }
}
