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
    public function test_admin_sees_selected_attendance_details(){
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
}
