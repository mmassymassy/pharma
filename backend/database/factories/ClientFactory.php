<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->numerify('+1##########'),
            'whatsapp' => $this->faker->boolean(70) ? $this->faker->numerify('+1##########') : null,
            'country' => $this->faker->randomElement(['USA', 'Canada', 'Morocco', 'France', 'Spain']),
            'city' => $this->faker->city(),
            'address' => $this->faker->streetAddress(),
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'source' => $this->faker->randomElement(['whatsapp', 'website', 'manual']),
        ];
    }
}
