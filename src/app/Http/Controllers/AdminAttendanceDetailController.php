<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Http\Requests\AdminDetailRequest;
use Carbon\Carbon;

class AdminAttendanceDetailController extends Controller
{
    /**
     * 管理者用勤怠詳細画面表示
     */
    public function show($id)
    {
        $attendance = Attendance::with('user')
            ->where('id', $id)
            ->firstOrFail();

        $breaks = $attendance->breaks;

        $pendingRequest = AttendanceRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->latest()
            ->first();
        $hasPendingRequest = $pendingRequest !== null;
        $displayBreaks = $breaks->toBase();
        if (!$hasPendingRequest) {
            $displayBreaks->push((object)[
                'break_in' => null,
                'break_out' => null,
            ]);
        }

        return view('attendance.admin_detail', compact('attendance', 'pendingRequest', 'hasPendingRequest', 'displayBreaks'));
    }

    /**
     * 勤怠・休憩テーブル修正
     */
    public function update(AdminDetailRequest $request, $id)
    {
        // レコードの抜き出し
        $attendance = Attendance::findOrFail($id);
        // 基準日設定
        $baseDate = $attendance->clock_in->format('Y-m-d');

        // 出勤・退勤時間をdatetimeに変換
        $clockIn  = Carbon::parse("{$baseDate} {$request->requested_clock_in}");
        $clockOut = Carbon::parse("{$baseDate} {$request->requested_clock_out}");

        // attendancesテーブルの修正
        $attendance->update([
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ]);

        // この勤怠データに紐づいている休憩レコードを削除する(->再作成)
        $attendance->breaks()->delete();

        if ($request->requested_breaks) {
            // もし休憩入りまたは休憩終わりが入力されていなかったらそのレコードは飛ばす
            foreach ($request->requested_breaks as $break) {
                if (empty($break['break_in']) || empty($break['break_out'])) {
                    continue;
                }

                $breakIn = Carbon::parse("{$baseDate} {$break['break_in']}");
                $breakOut = Carbon::parse("{$baseDate} {$break['break_out']}");
                // breaksテーブルの再作成
                $attendance->breaks()->create([
                    'break_in'  => $breakIn,
                    'break_out' => $breakOut,
                ]);
            }
        }

        return back()->with('status', '*修正が終了しました。');
    }
}

