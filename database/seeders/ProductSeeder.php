<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Category::count() === 0) {
            $this->call([CategorySeeder::class]);
        }
        $categories = Category::all()->toArray();

        foreach($categories as $category) {
            Product::factory()
                     ->hasImages(5, function (array $_, Product $product) {
                         return [
                             'url' => fake()->imageUrl(),
                             'product_id' => $product->id
                         ];
                     })
                     ->hasInventories(5, function (array $_, Product $product) {
                         return [
                            'product_id' => $product->id
                         ];
                     })
                     ->count(5)
                     ->create(['category_id' => $category['id']]);
            
        }
    }
}
