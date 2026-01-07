<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakFunctionTest extends TestCase
{
    /**
     * 休憩ボタンが正しく機能する
     */
    public function test_break_in_button_works_correctly()
    {
        // ステータスが出勤中のユーザーにログイン
        $user = User::factory()->create();
        carbon::setTestNow(Carbon::create(2026, 1, 5, 12, 0));
        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(3), // 9:00
            'clock_out' => null,
        ]);
        $this->actingAs($user, 'web');

        // 画面に「休憩入」ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('休憩入</button>', false);

        // 休憩入の処理を行う
        $response = $this->post('/attendance/break_in');

        // 画面上に表示されるステータスが「休憩中」になることを確認
        $response = $this->followRedirects($response);
        $response->assertSee('休憩中');

        carbon::setTestNow();
    }

    /**
     * 休憩は一日に何回もできることを確認
     */
    public function test_can_take_breaks_multiple_time_a_day()
    {
        // ステータスが出勤中のユーザーにログイン
        $user = User::factory()->create();
        carbon::setTestNow(Carbon::create(2026, 1, 5, 12, 0));
        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(3), // 9:00
            'clock_out' => null,
        ]);
        $this->actingAs($user, 'web');

        // 休憩入と休憩戻の処理を行う(1回目)
        $response = $this->post('/attendance/break_in');
        $response = $this->post('/attendance/break_out');

        // 休憩入と休憩戻の処理を行う(2回目)
        $response = $this->post('/attendance/break_in');
        $response = $this->post('/attendance/break_out');

        // 画面に「休憩入」ボタンが表示されていることを確認
        $response = $this->followRedirects($response);
        $response->assertSee('休憩入</button>', false);

        carbon::setTestNow();
    }

    /**
     * 休憩戻ボタンが正しく機能することを確認
     */
    public function test_return_to_break_button_works_correctly()
    {
        // ステータスが出勤中のユーザーにログイン
        $user = User::factory()->create();
        carbon::setTestNow(Carbon::create(2026, 1, 5, 12, 0));
        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(3), // 9:00
            'clock_out' => null,
        ]);
        $this->actingAs($user, 'web');

        // 休憩入の処理を行う(休憩戻ボタンが表示される)
        $response = $this->post('/attendance/break_in');
        $response = $this->followRedirects($response);
        $response->assertSee('休憩戻</button>', false);

        // 休憩戻の処理を行う
        $response = $this->post('/attendance/break_out');
        $response = $this->followRedirects($response);
        $response->assertSee('出勤中');

        Carbon::setTestNow();
    }

    /**
     * 休憩戻は一日に何回もできることを確認
     */
    public function test_can_return_from_break_multiple_time_a_day()
    {
        // ステータスが出勤中のユーザーにログイン
        $user = User::factory()->create();
        carbon::setTestNow(Carbon::create(2026, 1, 5, 12, 0));
        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(3), // 9:00
            'clock_out' => null,
        ]);
        $this->actingAs($user, 'web');

        // 休憩入と休憩戻の処理を行う
        $response = $this->post('/attendance/break_in');
        $response = $this->post('/attendance/break_out');

        // 再度休憩入の処理を行う
        $response = $this->post('/attendance/break_in');

        // 画面に「休憩戻」ボタンが表示されていることを確認
        $response = $this->followRedirects($response);
        $response->assertSee('休憩戻</button>', false);

        carbon::setTestNow();
    }

    /**
     * 休憩時刻が勤怠一覧画面で確認できることを確認
     */
    public function test_breaks_time_is_displayed_on_attendance_list()
    {
        // ステータスが出勤中のユーザーにログイン
        $user = User::factory()->create();
        carbon::setTestNow(Carbon::create(2026, 1, 5, 12, 0));
        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(3), // 9:00
            'clock_out' => null,
        ]);
        $this->actingAs($user, 'web');

        // 休憩入と休憩戻の処理を行う
        $response = $this->post('/attendance/break_in');
        $response = $this->post('/attendance/break_out');

        

    }
}
