<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceController extends Controller
{

    /**
     * 勤怠登録画面表示
     */
    public function index()
    {
        $user = auth()->user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('clock_in', today())
            ->first();

        // 勤怠状態判定
        if (!$attendance) {
            $status = 'before_work';
        } elseif ($attendance->clock_in && !$attendance->clock_out) {

            $lastBreak = $attendance->breaks()
                ->whereNull('break_out')
                ->latest()
                ->first();
            $status = $lastBreak ? 'on_break' : 'working';

        } else {
            $status = 'after_work';
        }

         // 想定外データ検知（昨日以前で clock_out が null のものが残っている）
        $hasStaleOpenAttendance = Attendance::where('user_id', $user->id)
        ->whereNull('clock_out')
        ->whereDate('clock_in', '<', today())
        ->exists();

        return view('user_attendance_index', [
            'attendance' => $attendance,
            'status'      => $status,
            'hasStaleOpenAttendance' => $hasStaleOpenAttendance,
        ]);
    }

    /**
     * 出勤時間登録
     */
    public function clockIn()
    {
        Attendance::create([
            'user_id' => auth()->id(),
            'clock_in' => now(),
        ]);

        return redirect('/attendance');
    }

    /**
     * 休憩時間登録
     */
    public function breakIn()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereNull('clock_out')
            ->firstOrFail();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in' => now(),
        ]);

        return redirect('/attendance');
    }

    /**
     * 休憩終了時間登録（レコードのアップデート）
     */
    public function breakOut()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereNull('clock_out')
            ->firstOrFail();

        // 勤務中の勤怠で休憩中の最新のレコードを取得
        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_out')
            ->orderBy('created_at', 'desc')
            ->firstOrFail();

        $break->update([
            'break_out' => now(),
        ]);

        return redirect('/attendance');
    }

    /**
     * 勤務終了時間登録（レコードのアップデート）
     */
    public function clockOut()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereNull('clock_out')
            ->firstOrFail();

        $attendance->update([
            'clock_out' => now(),
        ]);

        return redirect('/attendance');
    }
}
