<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'contact_name' => $this->faker->name(),
            'phone' => $this->faker->numerify('+1##########'),
            'email' => $this->faker->companyEmail(),
            'country' => $this->faker->randomElement(['USA', 'China', 'Germany', 'India']),
            'lead_time_days' => $this->faker->numberBetween(3, 30),
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
        ];
    }
}
