<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class UserAttendanceDetailAcquisitionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠詳細画面の名前がログインユーザーの氏名になっていることを確認
     */
    public function test_attendance_detail_displays_logged_in_user_name()
    {
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create([
            'name' => 'user1',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);
        $this->actingAs($user, 'web');

        // 勤怠詳細ページを開く
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        // 氏名欄の名前がログインユーザの名前になっていることを確認
        $response->assertSee('名前');
        $response->assertSee('user1');
    }

    /**
     * 勤怠詳細画面の名前がログインユーザーの氏名になっていることを確認
     */
    public function test_attendance_detail_displays_selected_date()
    {
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5),
        ]);
        $this->actingAs($user, 'web');

        // 勤怠詳細ページを開く
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        // 氏名欄の名前がログインユーザの名前になっていることを確認
        $response->assertSee('日付');
        $response->assertSee('2026年');
        $response->assertSee('1月5日');
    }

    /**
     * 「出勤・退勤」に記されている時間がログインユーザーの打刻と一致していることを確認
     */
    public function test_attendance_detail_displays_clock_in_and_out_times_of_logged_in_user()
    {
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create([
            'name' =>'user1',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);
        $this->actingAs($user, 'web');

        // 勤怠詳細ページを開く
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        // 出勤・退勤欄の表示がログインユーザーの打刻と一致している
        $response->assertSee('名前');
        $response->assertSee('user1');
        $response->assertSee('出勤・退勤');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 「休憩」に記されている時間がログインユーザーの打刻と一致していることを確認
     */
    public function test_attendance_detail_displays_break_in_and_out_times_of_logged_in_user()
    {
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create([
            'name' =>'user1',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::create(2026, 1, 5, 9, 0),
            'clock_out' => Carbon::create(2026, 1, 5, 18, 0),
        ]);
        $attendance->breaks()->create([
            'break_in' => Carbon::create(2026, 1, 5, 12, 0),
            'break_out' => Carbon::create(2026, 1, 5, 13, 0),
        ]);
        $this->actingAs($user, 'web');

        // 勤怠詳細ページを開く
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        // 休憩欄の表示がログインユーザーの打刻と一致している
        $response->assertSee('名前');
        $response->assertSee('user1');
        $response->assertSee('休憩');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
