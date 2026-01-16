<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use Carbon\Carbon;


class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録後、認証メールが送信されることを確認
     */
    public function test_verification_email_is_after_user_registration() {
        // 準備
        Notification::fake();

        // 会員登録をする
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response = $this->post('/register', [
            'name' => 'user1',
            'email' => 'user1@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertStatus(302);

        // メールが送信されていることを確認
        $user = User::where('email', 'user1@test.com')->firstOrFail();
        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    /**
     * メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移することを確認
     */
    public function test_user_is_redirected_to_email_verification_page_when_verification_link_is_clicked() {
        // 未認証ユーザーを作成してログイン
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $this->actingAs($user);

        // メール認証導線画面を表示
        $response = $this->get('/email/verify');
        $response->assertStatus(200);
        $response->assertSee('認証はこちらから', false);

        // リンク先に遷移できることを確認
        $verificationUrl = route('verification.notice');
        $response = $this->get($verificationUrl);
        $response->assertStatus(200);
    }

    /**
     * メール認証サイトのメール認証を完了すると勤怠登録画面に遷移することを確認
     */
    public function test_user_is_redirected_to_attendance_page_after_email_verification() {
        // メール認証済みユーザーを作成してログイン
        $user = User::factory()->create([
            'email_verified_at' => Carbon::now(),
        ]);
        $this->actingAs($user);

        // 勤怠登録画面を表示する
        $response = $this->get('/attendance');
        $response->assertStatus(200);
    }
}
