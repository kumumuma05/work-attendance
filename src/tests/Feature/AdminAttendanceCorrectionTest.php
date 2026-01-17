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

class AdminAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 承認待ちの修正申請が全て表示されていることを確認
     */
    public function test_all_pending_correction_requests_are_displayed() {
        // 準備
        $pendingUsers = [
            User::factory()->create(['name' => 'pending1']),
            User::factory()->create(['name' => 'pending2']),
        ];
        $approvedUsers = [
            User::factory()->create(['name' => 'approved1']),
            User::factory()->create(['name' => 'approved2']),
        ];
        foreach ($pendingUsers as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
            ]);
            AttendanceRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'status' => 'pending',
                'requested_clock_in' => '09:00',
                'remarks' => 'テストP',
            ]);
        }
        foreach ($approvedUsers as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
            ]);
            AttendanceRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'status' => 'approved',
                'requested_clock_in' => '09:00',
                'remarks' => 'テストA',
            ]);
        }

        // 管理者ユーザーログイン
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 修正申請一覧ページを開き、承認待ちのタブを開く
        $response = $this->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);
        $response->assertSee('pending1');
        $response->assertSee('pending2');
        $response->assertDontSee('approved1');
    }

    /**
     * 承認済みの修正申請が全て表示されていることを確認
     */
    public function test_all_approved_correction_requests_are_displayed() {
        // 準備
        $pendingUsers = [
            User::factory()->create(['name' => 'pending1']),
            User::factory()->create(['name' => 'pending2']),
        ];
        $approvedUsers = [
            User::factory()->create(['name' => 'approved1']),
            User::factory()->create(['name' => 'approved2']),
        ];
        foreach ($pendingUsers as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
            ]);
            AttendanceRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'status' => 'pending',
                'requested_clock_in' => '09:00',
                'remarks' => 'テストP',
            ]);
        }
        foreach ($approvedUsers as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
            ]);
            AttendanceRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'status' => 'approved',
                'requested_clock_in' => '09:00',
                'remarks' => 'テストA',
            ]);
        }

        // 管理者ユーザーログイン
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 修正申請一覧ページを開き、承認済みのタブを開く
        $response = $this->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);
        $response->assertSee('approved1');
        $response->assertSee('approved2');
        $response->assertDontSee('pending1');
    }

    /**
     * 修正申請の詳細内容が正しく表示されていることを確認
     */
    public function test_correction_request_are_displayed_correctly() {
        // 準備
        $user = User::factory()->create([
            'name' => 'user1',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 8, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);
        $attendanceRequest = AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'requested_clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'remarks' => 'テストP',
        ]);

        // 管理者ユーザーログイン
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 修正申請ページから詳細画面を開く
        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertSee('>詳細<', false);
        $response->assertSee('href="/stamp_correction_request/approve/' . $attendanceRequest->id . '"', false);

        $response = $this->get("/stamp_correction_request/approve/{$attendanceRequest->id}");
        $response->assertStatus(200);
        $response->assertSee('2026年');
        $response->assertSee('1月5日');
        $response->assertSee('user1');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 修正申請の承認処理が正しく行われることを確認
     */
    public function test_correction_request_approval_is_executed_correctly() {
        // 準備
        $user = User::factory()->create([
            'name' => 'user1',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 8, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);
        $attendanceRequest = AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'requested_clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'remarks' => 'テストP',
        ]);

        // 勤怠テーブルを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'clock_in' => '2026-01-05 08:00',
            'clock_out' => '2026-01-05 18:00',
        ]);

        // 管理者ユーザーログイン
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 修正申請の詳細画面を開く
        $response = $this->get("/stamp_correction_request/approve/{$attendanceRequest->id}");
        $response->assertStatus(200);
        $response->assertSee('承認</button>', false);

        // 「承認」ボタンを押す
        $response = $this->post("/stamp_correction_request/approve/{$attendanceRequest->id}");
        $response->assertStatus(302);

        // 勤怠テーブルを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'clock_in' => '2026-01-05 09:00',
            'clock_out' => '2026-01-05 18:00',
        ]);
    }
}
