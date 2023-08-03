<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'color' => fake()->randomElement(['黑色', '白色', '灰色']),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
            'amount' => fake()->randomDigit(),
            'product_id' => Product::pluck('id')->random()
        ];
    }
}
