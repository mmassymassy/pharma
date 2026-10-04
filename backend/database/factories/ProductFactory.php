<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 5, 500);

        return [
            'supplier_id' => null,
            'name' => ucfirst($this->faker->words(3, true)),
            'reference' => strtoupper($this->faker->unique()->bothify('REF-###??')),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(['Electronics', 'Home', 'Beauty', 'Accessories', 'Toys']),
            'price' => $price,
            'buy_price' => round($price * 0.6, 2),
            'image' => null,
            'availability' => $this->faker->randomElement(['in_stock', 'on_request', 'unavailable']),
            'is_active' => true,
        ];
    }
}
