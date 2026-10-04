<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => 'ORD-' . now()->year . '-' . str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'client_id' => Client::factory(),
            'status' => $this->faker->randomElement(OrderStatus::cases())->value,
            'total' => 0,
            'delivery_name' => $this->faker->name(),
            'delivery_phone' => $this->faker->numerify('+1##########'),
            'delivery_country' => $this->faker->randomElement(['USA', 'Canada', 'Morocco', 'France', 'Spain']),
            'delivery_city' => $this->faker->city(),
            'delivery_address' => $this->faker->streetAddress(),
            'tracking_number' => null,
            'carrier' => null,
            'source' => $this->faker->randomElement(['website', 'whatsapp', 'manual']),
            'notes' => null,
        ];
    }
}
