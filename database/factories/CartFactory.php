<?php

namespace Database\Factories;

use App\Models\Inventory;
use \App\Models\User;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::pluck('id')->random(),
            'inventory_id' => Inventory::pluck('id')->random(),
            'product_quantity' => fake()->randomDigit()
        ];
    }
}
