<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

class AttendanceDetailCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示されるのを確認
     */
    public function test_error_message_is_displayed_when_clock_in_is_after_clock_out() {
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);
        $this->actingAs($user, 'web');

        // 勤怠詳細ページを開く
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        // 出勤時間を退勤時間より後に設定する
        $response = $this->post("/attendance/detail/{$attendance->id}", [
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
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);
        $break = $attendance->breaks()->create([
            'break_in' => Carbon::create(2026, 1, 5, 12, 0),
            'break_out' => Carbon::create(2026, 1, 5, 13, 0),
        ]);
        $this->actingAs($user, 'web');

        // 勤怠詳細ページを開く
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        // 休憩開始時間を退勤時間より後に設定する
        $response = $this->post("/attendance/detail/{$attendance->id}", [
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
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);
        $break = $attendance->breaks()->create([
            'break_in' => Carbon::create(2026, 1, 5, 12, 0),
            'break_out' => Carbon::create(2026, 1, 5, 13, 0),
        ]);
        $this->actingAs($user, 'web');

        // 勤怠詳細ページを開く
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        // 休憩終了時間を退勤時間より後に設定する
        $response = $this->post("/attendance/detail/{$attendance->id}", [
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
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);
        $this->actingAs($user, 'web');

        // 勤怠詳細ページを開く
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        // 備考を未入力のまま処理
        $response = $this->post("/attendance/detail/{$attendance->id}", []);

        // エラーメッセージが表示される
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }

    /**
     * 修正申請処理が実行されることを確認
     */
    public function test_correction_equest_process_is_executed() {
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);
        $break = $attendance->breaks()->create([
            'break_in' => '2026-01-05 12:00:00',
            'break_out' => '2026-01-05 13:00:00',
        ]);
        $this->actingAs($user, 'web');

        // 勤怠詳細を修正し保存処理
        $response = $this->post("/attendance/detail/{$attendance->id}", [
            'requested_clock_in' => '08:00','requested_clock_out' => '18:00',
            'requested_breaks' => [
                $break->id => [
                    'break_in' => '12:00',
                    'break_out' => '13:00',
                ],
            ],
            'remarks' => 'テスト',
        ]);
        $response->assertStatus(302);

        // 申請が作成されたことを確認
        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'remarks' => 'テスト',
            'status' => 'pending',
        ]);

        // 管理者ユーザーの承認画面と申請一画面を確認する
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 申請承認画面の表示を確認
        $request = attendanceRequest::where('attendance_id',$attendance->id)
            ->latest()
            ->first();
        $response = $this->get("/stamp_correction_request/approve/{$request->id}");
        $response->assertStatus(200);
        $response->assertSee('テスト');

        // 申請一覧画面の表示を確認
        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertSee('テスト');
    }

    /**
     * すべての修正申請が表示されていることを確認
     */
    public function test_all_correction_requests_is_displayed() {
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create();
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2026-01-05 10:00:00',
        ]);
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2026-01-06 10:00:00',
        ]);
        $this->actingAs($user, 'web');

        // 勤怠詳細を修正し保存処理
        $response1 = $this->post("/attendance/detail/{$attendance1->id}", [
            'requested_clock_in' => '09:00',
            'remarks' => 'test1',
        ]);
        $response2 = $this->post("/attendance/detail/{$attendance2->id}", [
            'requested_clock_in' => '09:00',
            'remarks' => 'test2',
        ]);

        $response = $this->get('/stamp_correction_request/list');$response->assertStatus(200);
        $response->assertSee('test1');
        $response->assertSee('test2');
    }

    /**
     * 管理者が承認した申請がすべて表示されていることを確認
     */
    public function test_approved_correction_requested_are_displayed() {
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2026-01-05 10:00',
        ]);
        $this->actingAs($user, 'web');

        // 勤怠詳細を修正し保存処理
        $response = $this->post("/attendance/detail/{$attendance->id}", [
            'requested_clock_in' => '09:00',
            'remarks' => 'test',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();


        // 修正認証する
        $request = AttendanceRequest::where('attendance_id', $attendance->id)
            ->latest()
            ->first();
        $request->update([
            'status' => 'approved',
        ]);

        // 申請一覧を開く(承認済み画面)
        $response = $this->get('stamp_correction_request/list?tab=approved');
        $response->assertSee(200);
        $response->assertSee('test');
    }

    /**
     * 各申請の「詳細」を押下すると勤怠詳細画面に遷移することを確認
     */
    public function test_attendance_list_navigates_to_detail_page_when_detail_is_clicked() {
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2026-01-05 10:00',
        ]);
        $this->actingAs($user, 'web');

        // // 勤怠詳細を修正し保存処理
        $response = $this->post("/attendance/detail/{$attendance->id}", [
            'requested_clock_in' => '09:00',
            'remarks' => 'test',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        // 申請一覧画面を開く
        $response = $this->get('/stamp_correction_request/list');
        $response->assertSee(200);
        $response->assertSee('test');
        $response->assertSee('>詳細<', false);
        $response->assertSee('href="/attendance/detail/' . $attendance->id . '"', false);

        // 「詳細」ボタンを押す
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }
}