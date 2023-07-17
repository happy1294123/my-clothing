<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Process\FakeProcessResult;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    use RefreshDatabase;

    protected function create_db_data()
    {
        // 生成 2 種分類
        $category_name = 'clothing';
        $other_category_name = 'Pants';
        $category = Category::factory()->create(['name' => $category_name]);
        $other_category = Category::factory()->create(['name' => $other_category_name]);
        
        // 每個分類生成 3 個產品，每個產品生成 4 個圖片
        $productsAmount = 3;
        $imagesAmount = 4;
        $products = Product::factory()
                    ->hasImages($imagesAmount)
                    ->count($productsAmount)
                    ->create(['category_id' => $category->id]);
        Product::factory()
            ->hasImages($imagesAmount)
            ->count($productsAmount)
            ->create(['category_id' => $other_category->id]);

        return [
            'first_product' => $products->first()
        ];
    }

    public function test_get_clothing_list_by_category()
    {
        ['first_product' => $first_fake_product] = $this->create_db_data();
                    
        $product_list = $this->getJson(route('products.index', ['category_name' => 'clothing']))
                            ->assertStatus(200)
                            ->json();
        $first_product = $product_list[0];

        $this->assertCount(3, $product_list);
        $this->assertCount(4, $first_product['images']);
        $this->assertEquals($first_fake_product->name, $first_product['name']);
        $this->assertEquals('clothing', $first_product['category']['name']);
    }

    public function test_get_one_clothing_by_category_product_id()
    {
        ['first_product' => $first_fake_product] = $this->create_db_data();

        $found_product = $this->getJson(route('products.show', ['category_name' => 'clothing', 'product' => $first_fake_product['id']]))
                                ->assertStatus(200)
                                ->json();

        $this->assertEquals($first_fake_product['name'], $found_product['name']);
    }

    public function test_get_error_category_for_product()
    {
        ['first_product' => $first_fake_product] = $this->create_db_data();

        $result = $this->getJson(route('products.show', ['category_name' => 'error_category', 'product' => $first_fake_product['id']]))
                ->assertStatus(422)
                ->json();

        $this->assertEquals('category error', $result['message']);
    }
}
