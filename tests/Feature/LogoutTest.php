<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\User;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_成功登出_刪除該用戶所有accessToken()
    {
        $user = User::factory()->create(['password' => '12345678']);

        $this->postJson(route('user.login'), [
            'email' => $user->email,
            'password' => '12345678'
        ]);

        $this->actingAs($user, 'sanctum')
                ->postJson(route('user.logout'))
                ->assertStatus(204);

        $this->assertDatabaseEmpty('personal_access_tokens');
    }

    public function test_尚未登入狀態_執行登出_回傳Unauthenticated_401HTTP()
    {
        $this->withHeader('Accept', 'application/json');
        $result = $this->postJson(route('user.logout'))
                ->assertStatus(401)->json();

        $this->assertEquals(['message' => 'Unauthenticated.'], $result);
    }
}
