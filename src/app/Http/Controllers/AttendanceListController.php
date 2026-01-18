<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;

class AttendanceListController extends Controller
{

    /**
     * 勤怠一覧表示
     */
    public function index(Request $request)
    {
        // 表示対象月の基準日
        $current = $request->date
            ? Carbon::parse($request->date)
            : Carbon::now()->startOfMonth();

        // 前月と翌月を定義
        $previous = $current->copy()->subMonth()->format('Y-m');
        $next = $current->copy()->addMonth()->format('Y-m');

        // ログインしているユーザーの１か月分のデータを取得
        $start = $current->copy()->startOfMonth();
        $end = $current->copy()->endOfMonth();
        $attendances = Attendance::where('user_id', auth()->id())
            ->whereBetween('clock_in', [$start, $end])
            ->orderBy('clock_in', 'asc')
            ->get()
            ->keyBy(function ($attendance) {
                return $attendance->clock_in->toDateString();
            });

        // 一か月のカレンダーを作成し、勤怠情報を紐づける
        $calendar = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $calendar[] = [
                'date' => $date->copy(),
                'attendance' => $attendances->get($date->toDateString()),
            ];
        }

        return view('user_attendance_list', [
            'attendances' => $attendances,
            'currentMonth' => $current,
            'previousMonth' => $previous,
            'nextMonth' => $next,
            'calendar' => $calendar,
        ]);
    }
}
