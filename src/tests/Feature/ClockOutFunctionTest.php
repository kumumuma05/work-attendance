<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class ClockOutFunctionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 退勤ボタンが正しく機能することを確認
     */
    public function test_clock_out_button_works_correctly()
    {
        // ステータスが出勤中のユーザーにログイン
        $user = User::factory()->create();
        carbon::setTestNow(Carbon::create(2026, 1, 5, 9, 0));
        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now(),
            'clock_out' => null,
        ]);
        $this->actingAs($user, 'web');

        // 画面に退勤ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤</button', false);

        // 退勤処理を行うと画面に退勤済が表示される
        Carbon::setTestNow(Carbon::create(2026, 1, 5, 18, 0));
        $response = $this->post('/attendance/clock_out');
        $response = $this->followRedirects($response);
        $response->assertStatus(200);
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }

    /**
     * 退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_can_check_clock_out_time_on_the_attendance_list()
    {
        // ステータスが勤務外のユーザーにログイン
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務外');

        // 出勤と退勤の処理を行う
        Carbon::setTestNow(Carbon::create(2026, 1, 5, 9, 0));
        $response = $this->post('/attendance/clock_in');
        $response = $this->followRedirects($response);
        $response->assertStatus(200);

        Carbon::setTestNow(Carbon::create(2026, 1, 5, 18, 0));
        $response = $this->post('/attendance/clock_out');
        $response = $this->followRedirects($response);
        $response->assertStatus(200);

        // 勤怠一覧画面に退勤時刻が正確に記録されているのを確認
        $response = $this->get('/attendance/list?date=2026-01');
        $response->assertStatus(200);
        $response->assertSee('01/05');
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }
}