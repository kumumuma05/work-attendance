<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者が全一般ユーザーの「氏名」「メールアドレス」を確認できる
     */
    public function test_admin_can_view_all_users_name_and_email_addresses() {
        // 準備
        $users = [
            ['name' => 'user1', 'email' => 'user1@test.com'],
            ['name' => 'user2', 'email' => 'user2@test.com'],
            ['name' => 'user3', 'email' => 'user3@test.com'],
        ];
        foreach ($users as $user) {
            User::factory()->create($user);
        }

        // 管理者でログインする
        $admin =Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // スタッフ一覧を開く
        $response = $this->get('/admin/staff/list');
        $response->assertStatus(200);

        $response->assertSee('user1');
        $response->assertSee('user1@test.com');
        $response->assertSee('user2');
        $response->assertSee('user2@test.com');
        $response->assertSee('user3');
        $response->assertSee('user3@test.com');
    }

    /**
     * ユーザーの勤怠情報が正しく表示されることを確認
     */
    public function test_admin_sees_users_attendance_information_correctly() {
        // 準備
        Carbon::setTestNow(Carbon::create(2026, 1, 5));
        $user = User::factory()->create([
            'name' => 'user1',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);

        // 管理者でログインする
        $admin =Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 選択したユーザーの勤怠一覧ページを開く
        $response = $this->get("/admin/attendance/staff/{$user->id}");
        $response->assertStatus(200);

        $response->assertSee('user1さんの勤怠');
        $response->assertSee('01/05');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }

    /**
     * 「前月」を押下したときに表示月の前月の情報が表示されることを確認
     */
    public function test_admin_sees_previous_month_attendance_when_previous_month_is_selected() {
        // 準備
        Carbon::setTestNow(Carbon::create(2026, 1, 5));
        $user = User::factory()->create([
            'name' => 'user1',
        ]);
        $currentMonthAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);
        $previousMonthAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2025, 12, 5, 9, 0),
            'clock_out' => Carbon::create(2025, 12, 5, 18, 0),
        ]);
        $previousMonth = '2025-12';

        // 管理者でログインする
        $admin =Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 選択したユーザーの勤怠一覧ページを開く
        $response = $this->get("/admin/attendance/staff/{$user->id}");
        $response->assertStatus(200);
        $response->assertSee('前月');
        $response->assertSee('href="' . url("/admin/attendance/staff/{$user->id}?date={$previousMonth}") . '"', false);


        // 「前月」ボタンを押す
        $response = $this->get("/admin/attendance/staff/{$user->id}?date=2025-12");
        $response->assertStatus(200);

        $response->assertSee('user1さんの勤怠');
        $response->assertSee('2025/12');
        $response->assertSee('12/05');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertDontSee('2026/01');

        Carbon::setTestNow();
    }

    /**
     * 「翌月」を押下したときに表示月の翌月の情報が表示されることを確認
     */
    public function test_admin_sees_next_month_attendance_when_next_month_is_selected() {
        // 準備
        Carbon::setTestNow(Carbon::create(2026, 1, 5));
        $user = User::factory()->create([
            'name' => 'user1',
        ]);
        $currentMonthAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);
        $nextMonthAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 2, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 2, 5, 18, 0),
        ]);
        $nextMonth = '2026-02';

        // 管理者でログインする
        $admin =Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 選択したユーザーの勤怠一覧ページを開く
        $response = $this->get("/admin/attendance/staff/{$user->id}");
        $response->assertStatus(200);
        $response->assertSee('翌月');
        $response->assertSee('href="' . url("/admin/attendance/staff/{$user->id}?date={$nextMonth}") . '"', false);

        // 「翌月」ボタンを押す
        $response = $this->get("/admin/attendance/staff/{$user->id}?date=2026-02");
        $response->assertStatus(200);

        $response->assertSee('user1さんの勤怠');
        $response->assertSee('2026/02');
        $response->assertSee('02/05');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertDontSee('2026/01');

        Carbon::setTestNow();
    }

    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_admin_navigates_to_attendance_detail_page_when_detail_is_clicked() {
        // 準備
        Carbon::setTestNow(Carbon::create(2026, 1, 5));
        $user = User::factory()->create([
            'name' => 'user1',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);

        // 管理者でログインする
        $admin =Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 選択したユーザーの勤怠一覧ページを開く
        $response = $this->get("/admin/attendance/staff/{$user->id}");
        $response->assertStatus(200);
        $response->assertSee('>詳細<', false);
        $response->assertSee('href="/admin/attendance/' . $attendance->id . '"', false);

        // 「詳細」ボタンを押す
        $response = $this->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('user1');
        $response->assertSee('2026年');
        $response->assertSee('1月5日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }
}
