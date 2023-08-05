<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Cart;

class UserInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_獲取用戶資料()
    {
        $user = User::factory()->create();
        Category::factory()->create();
        Product::factory()->create();
        Inventory::factory()->count(5)->create(['quantity' => 10])
                    ->each(function ($inventory) {
                        Cart::factory()->create(['inventory_id' => $inventory->id, 'product_quantity' => 3]);
                    });

        $this->actingAs($user, 'sanctum')
                            ->getJson(route('user.show'))
                            ->assertStatus(200)
                            ->assertJsonStructure([
                                'id',
                                'name',
                                'email',
                                'phone',
                                'address',
                                'carts' => [
                                    [
                                        'id',
                                        'color',
                                        'size',
                                        'product_quantity',
                                        'price',
                                        'category',
                                        'image'
                                    ]
                                ]
                            ])
                            ->json();
    }

    public function test_尚未登入狀態_401HTTP()
    {
        $this->withHeader('Accept', 'application/json');
        $result = $this->getJson(route('user.show'))
                        ->assertStatus(401)
                        ->json();

        $this->assertEquals(['message' => 'Unauthenticated.'], $result);
    }
}
