<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_成功_返回用戶資料和token()
    {
        $user = User::factory()->create(['password' => '12345678']);

        $result = $this->postJson(route('user.login'), [
            'email' => $user->email,
            'password' => '12345678'
        ])
        ->assertStatus(200);

        $result->assertJsonStructure(['user' => [
          'id', 'name', 'email', 'phone' , 'address'
        ], 'token']);
        $this->assertEquals($user->email, $result['user']['email']);
        $this->assertEquals($user->name, $result['user']['name']);
    }

    public function test_失敗_查無該email()
    {
        User::factory()->create(['password' => '12345678']);

        $result = $this->postJson(route('user.login'), [
            'email' => 'wrong_email@example.com',
            'password' => '12345678'
        ])->assertStatus(401)->json();

        $this->assertEquals(['message' => 'Credentials do not match records'], $result);
    }

    public function test_失敗_密碼錯誤()
    {
        User::factory()->create(['email' => 'allen@example.com']);

        $result = $this->postJson(route('user.login'), [
            'email' => 'allen@example.com',
            'password' => '12345678'
        ])->assertStatus(401)->json();

        $this->assertEquals(['message' => 'Credentials do not match records'], $result);
    }
}
