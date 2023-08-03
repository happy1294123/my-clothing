<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InventoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_根據一串id_取得存貨資料()
    {
        $category = Category::factory()->create(['name' => 'test_category']);
        Product::factory()
                    ->hasImages(5)
                    ->hasInventories(5)
                    ->count(5)
                    ->create(['category_id' => $category->id]);
        $id_ary = [1,5,10,15,20];

        $result = $this->getJson(route('inventories.index', ['id' => join(',', $id_ary)]));

        $result->assertStatus(200)
                ->assertJsonCount(5)
                ->assertJsonStructure([
                    [
                        'id',
                        'color',
                        'size',
                        'amount',
                        'name',
                        'price',
                        'category',
                        'image'
                    ]
                ]);

        foreach($id_ary as $index => $id) {
            $this->assertEquals($result[$index]['id'], $id);
        }
    }

    public function test_若沒有輸入id值_將回傳錯誤訊息required_id()
    {
        $result = $this->getJson(route('inventories.index'))
                        ->assertStatus(422)
                        ->json();

        $this->assertEquals(['message' => 'inventories id is required'], $result);

    }
}
