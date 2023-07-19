<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
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
        $priceEnum = [1100, 1300, 1500];

        return [
            'name' => fake()->sentence(),
            'price' => $priceEnum[rand(0, 2)],
            'intro' => fake()->sentence(25),
            'status' => fake()->randomElement(['刪除', '上架', '下架']),
            'category_id' => fake()->randomDigit()
        ];
    }
}
