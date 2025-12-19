<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;


class AdminAttendanceListController extends Controller
{
    /**
     * 勤怠一覧表示
     */
    public function index(Request $request)
    {
        // 当日を表示
        $current = $request->date ? Carbon::parse($request->date) : Carbon::today();

        // 前日と翌日を定義
        $previous = $current->copy()->subDay()->format('Y-m-d');
        $next = $current->copy()->addDay()->format('Y-m-d');

        // 一般ユーザーの当日のデータを取得
        $attendances = Attendance::whereDate('clock_in', $current)
            ->orderBy('clock_in', 'asc')
            ->get();

        return view('.attendance.admin_list', [
            'attendances' => $attendances,
            'currentDay' => $current,
            'previousDay' => $previous,
            'nextDay' => $next,
        ]);
    }
}
