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

}
