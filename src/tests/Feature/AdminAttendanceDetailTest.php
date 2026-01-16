<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠詳細画面い表示されるデータが選択した者になっていることを確認
     */
    public function test_admin_sees_selected_attendance_details() {
        // 準備
        $user = User::factory()->create([
            'name' => 'user1',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);
        $break = $attendance->breaks()->create([
            'break_in' => Carbon::create(2026, 1, 5, 12, 0),
            'break_out' => Carbon::create(2026, 1, 5, 13, 0),
        ]);

        // 管理者ユーザーログイン
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 勤怠詳細ページを開く
        $response = $this->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $response->assertSee('user1');
        $response->assertSee('09:00');$response->assertSee('18:00');$response->assertSee('12:00');$response->assertSee('13:00');
    }

    /**
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示されるのを確認
     */
    public function test_error_message_is_displayed_when_clock_in_is_after_clock_out() {
        // 準備
        $user = User::factory()->create([
            'name' => 'user1',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);

        // 管理者ユーザーログイン
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 勤怠詳細ページを開く
        $response = $this->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);

        // 出勤時間を退勤時間より後に設定する
        $response = $this->post("/admin/attendance/{$attendance->id}", [
            'requested_clock_in' => '18:00',
            'requested_clock_out' => '09:00',
        ]);

        // エラーメッセージが表示される
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'requested_clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示されることを確認
     */
    public function test_error_message_is_displayed_when_break_in_is_after_clock_out() {
        // 準備
        $user = User::factory()->create([
            'name' => 'user1',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);
        $break = $attendance->breaks()->create([
            'break_in' => Carbon::create(2026, 1, 5, 12, 0),
            'break_out' => Carbon::create(2026, 1, 5, 13, 0),
        ]);

        // 管理者ユーザーログイン
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 勤怠詳細ページを開く
        $response = $this->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);

        // 休憩開始時間を退勤時間より後に設定する
        $response = $this->post("/admin/attendance/{$attendance->id}", [
            'requested_clock_out' => '18:00',
                'requested_breaks' => [
                $break->id => [
                    'break_in' => '19:00',
                    'break_out' => '20:00',
                ],
            ],
        ]);

        // エラーメッセージが表示される
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "requested_breaks.{$break->id}.break_in" => '休憩時間が不適切な値です',
        ]);
    }

    /**
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示されることを確認
     */
    public function test_error_message_is_displayed_when_break_out_is_after_clock_out() {
        // 準備
        $user = User::factory()->create([
            'name' => 'user1',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);
        $break = $attendance->breaks()->create([
            'break_in' => Carbon::create(2026, 1, 5, 12, 0),
            'break_out' => Carbon::create(2026, 1, 5, 13, 0),
        ]);

        // 管理者ユーザーログイン
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 勤怠詳細ページを開く
        $response = $this->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);

        // 休憩終了時間を退勤時間より後に設定する
        $response = $this->post("/admin/attendance/{$attendance->id}", [
            'requested_clock_out' => '18:00',
            'requested_breaks' => [
                $break->id => [
                    'break_in' => '12:00',
                    'break_out' => '20:00',
                ],
            ],
        ]);

        // エラーメッセージが表示される
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "requested_breaks.{$break->id}.break_out" => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**
     * 備考欄が未入力の場合エラーメッセージが表示されることを確認
     */
    public function test_error_message_is_displayed_when_remarks_is_null() {
        // 準備
        $user = User::factory()->create([
            'name' => 'user1',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);

        // 管理者ユーザーログイン
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 勤怠詳細ページを開く
        $response = $this->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);

        // 備考を未入力のまま処理
        $response = $this->post("/admin/attendance/{$attendance->id}", []);

        // エラーメッセージが表示される
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }
}
