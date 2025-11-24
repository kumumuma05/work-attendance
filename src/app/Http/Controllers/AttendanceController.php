<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 今日の勤怠を取得
    $attendance = Attendance::where('user_id', $user->id)
        ->where('date', today())
        ->first();

    // 状態判定
    if (!$attendance) {
        $state = 'before_work';
    } elseif ($attendance->clock_in && !$attendance->clock_out) {

        // 休憩中かどうか
        $lastBreak = $attendance->breaks()->latest()->first();

        if ($lastBreak && $lastBreak->break_in && !$lastBreak->break_out) {
            $state = 'on_break';   // 休憩中
        } else {
            $state = 'working';    // 勤務中
        }

    } elseif ($attendance->clock_out) {
        $state = 'after_work';
    }

    return view('attendance', [
        'attendance' => $attendance,
        'state'      => $state,
    ]);
}
}
