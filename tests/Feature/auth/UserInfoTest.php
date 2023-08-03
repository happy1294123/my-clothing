<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_獲取用戶資料()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
                            ->getJson(route('user.show'))
                            ->assertStatus(200)
                            ->assertJsonStructure([
                                'id',
                                'name',
                                'email',
                                'phone',
                                'address'
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
