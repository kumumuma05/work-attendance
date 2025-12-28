<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceAtatusConfirmationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤務外の場合、勤怠ステータスが正しく表示される
     */
    public function test_attendance_status_is_displayed_correctly_when_before_work()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('勤務外');
    }

    /**
     * 勤務中の場合、勤怠ステータスが正しく表示される
     */
    public function test_attendance_status_is_displayed_correctly_when_working()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 5, 10, 0));

        $user = User::factory()->create([]);
        Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(1), //9:30
            'clock_out' => null,
        ]);

        $this->actingAs($user, 'web');

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('勤務中');

        Carbon::setTestNow();
    }

    /**
     * 休憩中の場合、勤怠ステータスが正しく表示される
     */
    public function test_attendance_status_is_displayed_correctly_when_on_break()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 5, 12, 30));

        $user = User::factory()->create([]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(3), // 9:30
            'clock_out' => null,
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in' => now()->subMinutes(30), // 12:00
            'break_out' => null,
        ]);

        $this->actingAs($user, 'web');

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('休憩中');

        Carbon::setTestNow();
    }

    /**
     * 退勤済みの場合、勤怠ステータスが正しく表示される
     */
    public function test_attendance_status_is_displayed_correctly_when_after_work()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 5, 20, 00));

        $user = User::factory()->create([]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(11), // 9:00
            'clock_out' => now()->subHours(2), //18:00
        ]);
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in' => now()->subHours(8), // 12:00
            'break_out' => now()->subHours(7), //13:00
        ]);

        $this->actingAs($user, 'web');

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }
}
