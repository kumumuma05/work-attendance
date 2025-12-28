<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserLoginAuthenicationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログイン時、メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_error_is_displayed_when_email_is_missing()
    {
        User::factory()->create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this
            ->from('/login')
            ->followingRedirects()
            ->post('/login', [
                'email' => '',
                'password' => 'password'
            ]);
        $response->assertSee('メールアドレスを入力してください');
    }

    /**
     * ログイン時、パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_error_is_displayed_when_password_is_missing()
    {
        User::factory()->create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this
            ->from('/login')
            ->followingRedirects()
            ->post('/login', [
                'email' => 'test@example.com',
                'password' => ''
            ]);
        $response->assertSee('パスワードを入力してください');
    }

    /**
     * ログイン時、登録内容と一致しない場合、バリエーションメッセージが表示される
    */
    public function test_validation_error_is_displayed_when_registration_detail_do_not_match()
    {
        User::factory()->create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this
            ->from('/login')
            ->followingRedirects()
            ->post('/login', [
                'email' => 'nomatch@example.com',
                'password' => 'password'
            ]);
        $response->assertSee('ログイン情報が登録されていません');
    }
}
