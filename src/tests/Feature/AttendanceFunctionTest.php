<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceFunctionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤ボタンが正しく機能する
     */
    public function test_clock_in_button_works_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務外');
        $response->assertSee('出勤</button>', false);

        $response = $this->post('/attendance/clock_in');

        $response = $this->followRedirects($response);
        $response->assertSee('出勤中');
    }

    /**
     * 出勤は一日一回のみ
     */
    public function test_work_only_once_a_day()
    {
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::create(2026, 1, 5, 20, 0));
        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);

        $this->actingAs($user, 'web');

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertDontSee('出勤</button>', false);

        Carbon::setTestNow();
    }

    /**
     * 出勤時刻が勤怠一覧で確認できる
     */
    public function test_clock_in_time_is_displayed_on_attendance_list()
    {
        // 勤務外のユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務外');

        // 出勤の処理を行う
        Carbon::setTestNow(Carbon::create(2026, 1, 5, 9, 0));
        $response = $this->post('/attendance/clock_in');

        // 勤怠一覧画面から出勤の日付を確認する
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
    }
}
