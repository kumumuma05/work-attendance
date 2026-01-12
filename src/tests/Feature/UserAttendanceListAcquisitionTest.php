<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class UserAttendanceListAcquisitionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 自分が行った勤怠情報が全て表示されていることを確認
     */
    public function test_all_attendance_information_is_display()
    {
        // 勤怠情報が5件分登録されたユーザにログイン
        $user = User::factory()->create();
        $days = [5, 6, 7, 8, 9];
        foreach ($days as $day) {
            Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, $day, 9, 0),
            'clock_out' => Carbon::create(2026, 1, $day, 18, 0),
            ]);
        }
        $this->actingAs($user, 'web');

        // 勤怠一覧ページを開く
        $response = $this->get('/attendance/list?date=2026-01');
        $response->assertStatus(200);

        // 勤怠情報が全て表示されていることを確認
        foreach ($days as $day) {
            $response->assertSee(sprintf('01/%02d', $day));
            $response->assertSee('09:00');
            $response->assertSee('18:00');
        }
    }

    /**
     * 勤怠一覧画面に遷移した際現在の月が表示されることを確認
     */
    public function test_attendance_list_displays_current_month_by_default()
    {
        // ユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        // 勤怠一覧ページを開くと現在の月が表示されていることを確認
        Carbon::setTestNow(Carbon::create(2026, 1, 5));
        $response = $this->get('/attendance/list');
        $response->assertSee('2026/01');
    }

    /**
     * 「前月」を押下時、表示月の前月の情報が表示されることを確認
     */
    public function test_attendance_list_display_previous_month_when_previous_button_is_clicked()
    {
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create();
        $dates = [
            Carbon::create(2026, 1, 1, 9, 0),
            Carbon::create(2025, 12, 1, 9, 0),
        ];
        foreach ($dates as $clockIn) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'clock_in' => $clockIn,
                'clock_out' => $clockIn->copy()->setTime(18, 0),

            ]);
        }
        $this->actingAs($user, 'web');

        // 勤怠一覧ページを開く
        $response = $this->get('/attendance/list?date=2026-01');
        $response->assertStatus(200);
        $response->assertSee('2026/01');
        $response->assertSee('01/01');
        $response->assertSee('前月');

        // 「前月」ボタンを押すと前月の情報が表示されていることを確認
        $response = $this->get('/attendance/list?date=2025-12');
        $response->assertStatus(200);
        $response->assertSee('2025/12');
        $response->assertSee('12/01');
    }

    /**
     * 「翌月」を押下時、表示月の翌月の情報が表示されることを確認
     */
    public function test_attendance_list_display_next_month_when_next_button_is_clicked()
    {
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create();
        $dates = [
            Carbon::create(2026, 1, 1, 9, 0),
            Carbon::create(2026, 2, 1, 9, 0),
        ];
        foreach ($dates as $clockIn) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'clock_in' => $clockIn,
                'clock_out' => $clockIn->copy()->setTime(18, 0),

            ]);
        }
        $this->actingAs($user, 'web');

        // 勤怠一覧ページを開く
        $response = $this->get('/attendance/list?date=2026-01');
        $response->assertStatus(200);
        $response->assertSee('2026/01');
        $response->assertSee('01/01');
        $response->assertSee('翌月');

        // 「翌月」ボタンを押すと翌月の情報が表示されていることを確認
        $response = $this->get('/attendance/list?date=2026-02');
        $response->assertStatus(200);
        $response->assertSee('2026/02');
        $response->assertSee('02/01');
    }

    /**
     * 詳細を押下すると、その日の勤怠詳細画面に遷移することを確認
     */
    public function test_attendance_list_navigates_to_detail_page_when_detail_link_is_clicked()
    {
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
            ]);
        $this->actingAs($user, 'web');

        // 勤怠一覧ページを開く
        $response = $this->get('/attendance/list?date=2026-01');
        $response->assertStatus(200);
        $response->assertSee('詳細');

        // 詳細ボタンを押下する
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        // その日の勤怠詳細画面が表示されていることを確認
        $response->assertSee('2026年');
        $response->assertSee('1月5日');
    }
}