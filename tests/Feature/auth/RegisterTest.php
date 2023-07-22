<?php

namespace Tests\Feature\auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function setUp():void
    {
        parent::setUp();
        $this->withHeaders(['Accept' => 'application/json']);
    }

    public function test_註冊成功_資料庫有資料_密碼經過hash()
    {
        $this->postJson(route('user.register'), [
            'name' => 'allen',
            'email' => 'allen@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'phone' => '0912345678',
            'address' => '台北市信義區市府路45號'
        ])->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => 'allen',
            'email' => 'allen@example.com',
            'phone' => '0912345678',
            'address' => '台北市信義區市府路45號'
        ]);
        $this->assertTrue(Hash::check('12345678', User::first()->password));
    }

    public function test_註冊成功_電話_地址_可以不用填寫()
    {
        $this->postJson(route('user.register'), [
            'name' => 'allen',
            'email' => 'allen@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'phone' => '',
            'address' => ''
        ])->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => 'allen',
            'email' => 'allen@example.com',
            'phone' => null,
            'address' => null
        ]);
        $this->assertTrue(Hash::check('12345678', User::first()->password));
    }

    public function test_註冊失敗_名稱太長_email不符合格式_密碼必填()
    {
        $result = $this->postJson(route('user.register'), [
            'name' => str_repeat('allen', 100),
            'email' => 'error email fomat',
            'error_password_key' => '12345678'
        ])->assertStatus(422)->json();



        $this->assertEquals('The name field must not be greater than 100 characters. (and 2 more errors)', $result['message']);
        $this->assertEquals([
                                'name' => ['The name field must not be greater than 100 characters.'],
                                'email' => ['The email field must be a valid email address.'],
                                'password' => ['The password field is required.']
                            ], $result['errors']);
    }

    public function test_註冊失敗_email不能重複()
    {
        User::factory()->create(['email' => 'allen@example.com']);

        $result = $this->postJson(route('user.register'), [
            'name' => 'allen',
            'email' => 'allen@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678'
        ])->assertStatus(409)->json();

        $this->assertEquals(['message' => 'email already exist'], $result);
    }
}
