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
        // 当月を表示
        $current = $request->month ? Carbon::parse($request->month . '-01') : Carbon::now()->startOfMonth();

        // 前月と翌月を定義
        $previous = $current->copy()->subMonth()->format('Y-m');
        $next = $current->copy()->addMonth()->format('Y-m');

        // ログインしているユーザーの１か月分のデータを取得
        $attendances = Attendance::where('user_id', auth()->id())->whereBetween('clock_in', [
            $current->copy()->startOfMonth(),
            $current->copy()->endOfMonth()
        ])
        ->orderBy('clock_in', 'asc')
        ->get();

        return view('attendance.list', [
            'attendances' => $attendances,
            'currentMonth' => $current->format('Y/m'),
            'previousMonth' => $previous,
            'nextMonth' => $next,
        ]);
    }

}
