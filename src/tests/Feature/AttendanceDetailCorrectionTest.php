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

    public function test_error_message_is_displayed_when_clock_in_is_after_clock_out(){
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

    public function test_error_message_is_displayed_when_break_in_is_after_clock_out(){
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

    public function test_error_message_is_displayed_when_break_out_is_after_clock_out(){
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

    public function test_error_message_is_displayed_when_remarks_is_null(){
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

    public function test_correction_equest_process_is_executed(){
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
}