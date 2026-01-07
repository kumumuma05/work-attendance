<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        // 今日の勤怠を取得
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('clock_in', today())
            ->first();
        // 昨日からの勤務が残っているか確認
        $yesterdayAttendance = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->whereDate('clock_in', today()->subDay())
            ->first();
        // 最終的な勤務レコードを決定
        if ($todayAttendance) {
            $attendance = $todayAttendance;
        } elseif ($yesterdayAttendance) {
            $attendance = $yesterdayAttendance;
        } else {
            $attendance = null;
        }

        // 勤怠状態判定
        if (!$attendance) {
            $status = 'before_work';
        } elseif ($attendance->clock_in && !$attendance->clock_out) {

            // 休憩中判定
            $lastBreak = $attendance->breaks()
                ->whereNull('break_out')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastBreak && $lastBreak->break_in && !$lastBreak->break_out) {
                $status = 'on_break';
            } else {
                $status = 'working';
            }

        } elseif ($attendance->clock_out) {
            $status = 'after_work';
        }

        return view('attendance.index', [
            'attendance' => $attendance,
            'status'      => $status,
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

        // 今日の勤怠で休憩中の最新のレコードを取得
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
