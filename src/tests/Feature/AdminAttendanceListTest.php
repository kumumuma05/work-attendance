<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * その日の全ユーザーの勤怠情報が確認できる
     */
    public function test_admin_can_view_all_users_attendance_for_the_selected_date() {
        // 準備
        Carbon::setTestNow(Carbon::create(2026, 1, 5));
        $user1 = User::factory()->create([
            'name' => 'user1',
        ]);
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);
        $user2 = User::factory()->create([
            'name' => 'user2',
        ]);
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'clock_in' => Carbon::create(2026, 1, 5, 10, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 19, 0),
        ]);
        $otherUser = User::factory()->create([
            'name' => 'other']);
        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'clock_in'  => Carbon::create(2026, 1, 6, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 6, 18, 0),
        ]);

        // 管理者ユーザーにログインする
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 勤怠一覧画面を開く
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('2026年1月5日の勤怠');
        $response->assertSee('user1');
        $response->assertSee('user2');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        $response->assertDontSee('other');

        Carbon::setTestNow();
    }

    /**
     * 遷移した際、現在の日付が表示されることを確認
     */
    public function test_current_date_is_display_when_page_is_opened() {
        // 準備
        Carbon::setTestNow(Carbon::create(2026, 1, 5));

        // 管理者ユーザーにログインする
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 勤怠一覧画面を開く
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('2026年1月5日の勤怠');
        Carbon::setTestNow();
    }

    /**
     * 「前日」を押下したとき前日の勤怠情報が表示される
     */
    public function test_previous_day_attendance_is_displayed_when_previous_day_button_is_clicked() {
        // 準備
        Carbon::setTestNow(Carbon::create(2026, 1, 5));
        $user1 = User::factory()->create([
            'name' => 'user1',
        ]);
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);
        $user2 = User::factory()->create([
            'name' => 'user2',
        ]);
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'clock_in' => Carbon::create(2026, 1, 4, 10, 0),
            'clock_out' => Carbon::create(2026, 1, 4, 19, 0),
        ]);

        // 管理者ユーザーにログインする
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 勤怠一覧画面を開く
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('2026年1月5日の勤怠');
        $response->assertSee('user1');
        $response->assertDontSee('user2');

        // 「前日」に遷移
        $response = $this->get('/admin/attendance/list?date=2026-01-04');
        $response->assertStatus(200);
        $response->assertSee('2026年1月4日の勤怠');
        $response->assertSee('user2');
        $response->assertDontSee('user1');

        Carbon::setTestNow();
    }

    /**
     * 「前日」を押下したとき前日の勤怠情報が表示される
     */
    public function test_previous_day_attendance_is_displayed_when_next_day_button_is_clicked() {
        // 準備
        Carbon::setTestNow(Carbon::create(2026, 1, 5));
        $user1 = User::factory()->create([
            'name' => 'user1',
        ]);
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);
        $user2 = User::factory()->create([
            'name' => 'user2',
        ]);
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'clock_in' => Carbon::create(2026, 1, 6, 10, 0),
            'clock_out' => Carbon::create(2026, 1, 6, 19, 0),
        ]);

        // 管理者ユーザーにログインする
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 勤怠一覧画面を開く
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('2026年1月5日の勤怠');
        $response->assertSee('user1');
        $response->assertDontSee('user2');

        // 「前日」に遷移
        $response = $this->get('/admin/attendance/list?date=2026-01-06');
        $response->assertStatus(200);
        $response->assertSee('2026年1月6日の勤怠');
        $response->assertSee('user2');
        $response->assertDontSee('user1');

        Carbon::setTestNow();
    }
}
