<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AdminLoginAuthenicationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者ログイン時、メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_error_is_displayed_when_email_is_missing()
    {
        User::factory()->create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this
            ->from('/admin/login')
            ->followingRedirects()
            ->post('/admin/login', [
                'email' => '',
                'password' => 'password123'
            ]);
        $response->assertSee('メールアドレスを入力してください');
    }

    /**
     * 管理者ログイン時、パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_error_is_displayed_when_password_is_missing()
    {
        User::factory()->create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this
            ->from('/admin/login')
            ->followingRedirects()
            ->post('/admin/login', [
                'email' => 'admin@example.com',
                'password' => ''
            ]);
        $response->assertSee('パスワードを入力してください');
    }

    /**
     * 管理者ログイン時、登録内容と一致しない場合、バリエーションメッセージが表示される
    */
    public function test_validation_error_is_displayed_when_registration_detail_do_not_match()
    {
        User::factory()->create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this
            ->from('/admin/login')
            ->followingRedirects()
            ->post('/admin/login', [
                'email' => 'nomatch@example.com',
                'password' => 'password123'
            ]);
        $response->assertSee('ログイン情報が登録されていません');
    }
}
