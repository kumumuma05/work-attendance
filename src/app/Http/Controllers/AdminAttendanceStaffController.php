<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;

class AdminAttendanceStaffController extends Controller
{
    /**
     * スッタフ別勤怠一覧画面表示
     */
    public function index(Request $request, $id)
    {
        // 表示対象月の基準日
        $current = $request->date
            ? Carbon::parse($request->date)
            : Carbon::now()->startOfMonth();

        // 先月と翌月を定義
        $previous = $current->copy()->subMonth()->format('Y-m');
        $next = $current->copy()->addMonth()->format('Y-m');

        // 指定したユーザの１か月の勤怠情報を取得
        $user = User::findOrFail($id);

        $start = $current->copy()->startOfMonth();
        $end = $current->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('clock_in', [$start, $end])
            ->orderBy('clock_in', 'asc')
            ->get()
            ->keyBy(function ($attendance) {
                return $attendance->clock_in->toDateString();
            });

        // 一か月のカレンダ―を作成し、勤怠情報を紐づける
        $calendar = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $calendar[] = [
                'date' => $date->copy(),
                'attendance' => $attendances->get($date->toDateString()),
            ];
        }

        return view('attendance.staff_attendance', [
            'user' =>$user,
            'attendances' => $attendances,
            'currentMonth' => $current,
            'previousMonth' => $previous,
            'nextMonth' => $next,
            'calendar' => $calendar,
        ]);
    }
}
