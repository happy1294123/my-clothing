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

    public function test_獲取所有商品()
    {
        $this->create_db_data();

        $product_list = $this->getJson(route('products.indexAll'))
                            ->assertStatus(200)
                            ->json();

        $this->assertCount(6, $product_list);
    }

    public function test_路徑輸入分類_獲取指定分類的商品()
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

    public function test_若路徑輸入不存在的分類_回傳訊息_category_name_error()
    {
        $this->create_db_data();

        $result = $this->getJson(route('products.index', ['category_name' => 'error_category_name']))
                ->assertStatus(422)
                ->json();

        $this->assertEquals(['message' => 'category name error'], $result);
    }

    public function test_路徑輸入分類和商品id_獲取指定商品()
    {
        ['first_product' => $first_fake_product] = $this->create_db_data();

        $found_product = $this->getJson(route('products.show', ['category_name' => 'clothing', 'product' => $first_fake_product['id']]))
                                ->assertStatus(200)
                                ->json();

        $this->assertEquals($first_fake_product['name'], $found_product['name']);
        $this->assertEquals('clothing', $found_product['category']['name']);
        $this->assertCount(4, $found_product['images']);
    }

    public function test_若指定商品與商品分類不一樣_回傳訊息_category_name_error()
    {
        ['first_product' => $first_fake_product] = $this->create_db_data();

        $result = $this->getJson(route('products.show', ['category_name' => 'error_category', 'product' => $first_fake_product['id']]))
                ->assertStatus(422)
                ->json();

        $this->assertEquals(['message' => 'category name error'], $result);
    }

    public function test_指定單一商品_包含該商品的存貨資料_照片_分類_其他訊息()
    {
        ['first_product' => $first_fake_product] = $this->create_db_data();

        $this->getJson((route('products.show', ['category_name' => 'clothing', 'product' => $first_fake_product['id']])))
                        ->assertStatus(200)
                        ->assertJsonStructure([
                            'id',
                            'name',
                            'price',
                            'intro',
                            'status',
                            'created_at',
                            'updated_at',
                            'images' => [
                                ['id', 'url']
                            ],
                            'category' => [
                                'id',
                                'name'
                            ],
                            'inventories' => [
                                ['id', 'color', 'size', 'amount']
                            ]
                        ]);
    }


    protected function create_db_data()
    {
        // 生成 2 種分類
        $category_name = 'clothing';
        $other_category_name = 'Pants';
        $category = Category::factory()->create(['name' => $category_name]);
        $other_category = Category::factory()->create(['name' => $other_category_name]);
        
        // 每個分類生成 3 個產品，每個產品生成 4 個圖片，每個產品生成 5 個存貨
        $productsAmount = 3;
        $imagesAmount = 4;
        $inventoryAmount = 5;
        $products = Product::factory()
                    ->hasImages($imagesAmount)
                    ->hasInventories($inventoryAmount)
                    ->count($productsAmount)
                    ->create(['category_id' => $category->id]);
        Product::factory()
            ->hasImages($imagesAmount)
            ->hasInventories($inventoryAmount)
            ->count($productsAmount)
            ->create(['category_id' => $other_category->id]);

        return [
            'first_product' => $products->first()
        ];
    }
}
