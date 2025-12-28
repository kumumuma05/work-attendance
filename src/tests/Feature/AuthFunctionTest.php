<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AuthFunctionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 新規登録の名前が未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_error_is_displayed_when_name_is_missing()
    {
        $validData = [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $data = array_merge($validData, [
            'name' => '',
        ]);

        $response = $this
            ->from('/register')
            ->followingRedirects()
            ->post('/register', $data);
        $response->assertSee('お名前を入力してください');
    }

    /**
     * 新規登録のメールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_error_is_displayed_when_email_is_missing()
    {
        $validData = [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $data = array_merge($validData, [
            'email' => '',
        ]);

        $response = $this
            ->from('/register')
            ->followingRedirects()
            ->post('/register', $data);
        $response->assertSee('メールアドレスを入力してください');
    }

    /**
     * 新規登録のパスワードが8文字未満の場合、バリデーションメッセージが表示される
     */
    public function test_validation_error_is_displayed_when_password_is_less_than_8()
    {
        $validData = [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $data = array_merge($validData, [
            'password' => 'pass',
        ]);

        $response = $this
            ->from('/register')
            ->followingRedirects()
            ->post('/register', $data);
        $response->assertSee('パスワードは8文字以上で入力してください');
    }

    /**
     * 新規登録のパスワードが一致しない場合、バリデーションメッセージが表示される
     */
    public function test_validation_error_is_displayed_when_password_mismatch()
    {
        $validData = [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $data = array_merge($validData, [
            'password_confirmation' => 'passpass',
        ]);

        $response = $this
            ->from('/register')
            ->followingRedirects()
            ->post('/register', $data);
        $response->assertSee('パスワードと一致しません');
    }

    /**
     * 新規登録のパスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_error_is_displayed_when_password_is_missing()
    {
        $validData = [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $data = array_merge($validData, [
            'password' => '',
        ]);

        $response = $this
            ->from('/register')
            ->followingRedirects()
            ->post('/register', $data);
        $response->assertSee('パスワードを入力してください');
    }

    /**
     * 新規登録のデータが正常に保存される
     */
    public function test_new_registration_data_is_saved_successfully()
    {
        $data = [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $this->post('/register', $data);

        $this->assertDatabaseHas('users', [
            'name' => '山田太郎',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(
            Hash::check('password', $user->password)
        );
    }
}
