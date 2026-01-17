<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;

class AdminStaffAttendanceListController extends Controller
{
    /**
     * スタッフ別勤怠一覧画面表示
     */
    public function index(Request $request, $id)
    {
        // 表示対象月の基準日
        $current = $request->date
            ? Carbon::parse($request->date)
            : Carbon::now()->startOfMonth();

        // 前月と翌月を定義
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

        return view('admin_staff_attendance_list', [
            'user' => $user,
            'attendances' => $attendances,
            'currentMonth' => $current,
            'previousMonth' => $previous,
            'nextMonth' => $next,
            'calendar' => $calendar,
        ]);
    }

    /**
     * CSV出力
     */
    public function exportCsv(Request $request, $id)
    {
        // 作成する月を特定
        $month = $request->input('date', now()->format('Y-m'));
        $start = Carbon::parse($month)->startOfMonth();
        $end   = Carbon::parse($month)->endOfMonth();

        // 特定した月のデータを取得
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('clock_in',[$start, $end])
            ->orderBy('clock_in')
            ->get();

        // CSVで使う配列を作成
        $user = User::findOrFail($id);

        $csv = [];
        $csv[] = ["ユーザー名: $user->name"];
        $csv[] = ["対象月: {$month}"];
        $csv[] = [];
        $csv[] = ['日付', '出勤', '退勤', '休憩', '合計'];
        foreach ($attendances as $attendance) {
            $csv[] = [
                $attendance->clock_in->isoFormat('YYYY/MM/DD(ddd)'),
                optional($attendance->clock_in)->format('H:i'),
                optional($attendance->clock_out)->format('H:i'),
                $attendance->break_duration ?? '',
                $attendance->total_hours ?? '',
            ];
        }

        // CSVを作成
        return response()->streamDownload(function () use ($csv) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            foreach ($csv as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, "attendance_{$user->id}_{$month}.csv");
    }
}
