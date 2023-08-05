<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(), 'sanctum');
    }

    public function test_body填入存貨id_quantity_新增存貨_至購物車中()
    {
        $category = Category::factory()->create(['name' => 'test_category']);
        Product::factory()->hasInventories(5)->count(5)->create(['category_id' => $category->id]);
        $first_inventory = ['inventory_id' => 1, 'product_quantity' => 3];
        $second_inventory = ['inventory_id' => 5, 'product_quantity' => 2];

        $this->postJson(route('carts.store'), [
                [
                    'inventory_id' => $first_inventory['inventory_id'],
                    'product_quantity' => $first_inventory['product_quantity']
                ],
                [
                    'inventory_id' => $second_inventory['inventory_id'],
                    'product_quantity' => $second_inventory['product_quantity']
                ]
            ])
            ->assertStatus(201);

        $this->assertEquals([
            'id' => 1,
            'user_id' => 1,
            'inventory_id' => 1,
            'product_quantity' => 3
        ], Cart::find(1)->toArray());

        $this->assertEquals([
            'id' => 2,
            'user_id' => 1,
            'inventory_id' => 5,
            'product_quantity' => 2
        ], Cart::find(2)->toArray());
    }

    public function test_存貨進購物車_inventory_id是必填選項()
    {
        $result = $this->postJson(route('carts.store'), [['product_quantity' => 3]])
            ->assertStatus(422);

        $this->assertEquals('The 0.inventory_id field is required.', $result['message']);
    }

    public function test_存貨進購物車_product_quantity是必填選項()
    {
        $result = $this->postJson(route('carts.store'), [['inventory_id' => 3]])
            ->assertStatus(422);

        $this->assertEquals('The 0.product_quantity field is required.', $result['message']);
    }

    public function test_存貨進購物車_必須填入存貨請求()
    {
        $result = $this->postJson(route('carts.store'))
            ->assertStatus(422);

        $this->assertEquals('The request body is required.', $result['message']);
    }

    public function test_再次將存貨放進購物車_將覆蓋原本購物車內容()
    {
        $category = Category::factory()->create(['name' => 'test_category']);
        Product::factory()->hasInventories(5)->count(5)->create(['category_id' => $category->id]);
        $first_inventory = ['inventory_id' => 1, 'product_quantity' => 3];
        $second_inventory = ['inventory_id' => 5, 'product_quantity' => 2];

        $this->postJson(route('carts.store'), [
                [
                    'inventory_id' => $first_inventory['inventory_id'],
                    'product_quantity' => $first_inventory['product_quantity']
                ],
                [
                    'inventory_id' => $second_inventory['inventory_id'],
                    'product_quantity' => $second_inventory['product_quantity']
                ]
            ])
            ->assertStatus(201);

        $this->assertDatabaseCount(Cart::getTableName(), 2);

        $this->assertEquals([
            'id' => 1,
            'user_id' => 1,
            'inventory_id' => 1,
            'product_quantity' => 3
        ], Cart::find(1)->toArray());

        $this->assertEquals([
            'id' => 2,
            'user_id' => 1,
            'inventory_id' => 5,
            'product_quantity' => 2
        ], Cart::find(2)->toArray());

        $this->postJson(route('carts.store'), [
                [
                    'inventory_id' => 10,
                    'product_quantity' => 10
                ],
                [
                    'inventory_id' => 20,
                    'product_quantity' => 20
                ]
            ])
            ->assertStatus(201);

        $this->assertDatabaseCount(Cart::getTableName(), 2);

        $this->assertEquals([
            'id' => 3,
            'user_id' => 1,
            'inventory_id' => 10,
            'product_quantity' => 10
        ], Cart::find(3)->toArray());

        $this->assertEquals([
            'id' => 4,
            'user_id' => 1,
            'inventory_id' => 20,
            'product_quantity' => 20
        ], Cart::find(4)->toArray());
    }

    public function test_url中輸入購物車id_刪除指定的購物車商品()
    {
        $this->make_5_fake_cart();
        $delete_cart_id = 2;

        $this->deleteJson(route('carts.delete', ['cart_id' => $delete_cart_id]))->assertStatus(204);

        $this->assertDatabaseMissing(Cart::getTableName(), ['id' => $delete_cart_id]);
    }

    public function test_清空所有購物車內容()
    {
        $this->make_5_fake_cart();

        $this->deleteJson(route('carts.deleteAll'))
                ->assertStatus(204);
       
        $this->assertDatabaseEmpty(Cart::getTableName());
    }

    public function test_結帳_清空購物車_並扣除存貨()
    {
        $this->make_5_fake_cart(['product_quantity' => 3]);

        $this->postJson(route('carts.checkout'))
                ->assertStatus(204);

        foreach(Inventory::all() as $inventory) {
            $this->assertEquals(7, $inventory->quantity);
        }
        $this->assertEquals(0, Cart::count());
    }

    public function test_獲取該用戶的購物車資料()
    {
        $this->make_5_fake_cart();

        $this->getJson(route('carts.index'))
                ->assertStatus(200)
                ->assertJsonStructure([
                        [
                            'id',
                            'color',
                            'size',
                            'inventory_quantity',
                            'name',
                            'price',
                            'category',
                            'image'
                        ]
                    ])->json();
    }

    private function make_5_fake_cart($arg=[])
    {
        Category::factory()->create();
        Product::factory()->create();
        Inventory::factory()->count(5)->create(['quantity' => 10])
                    ->each(function ($inventory) use ($arg) {
                        Cart::factory()->create(array_merge(['inventory_id' => $inventory->id], $arg));
                    });
    }
}
