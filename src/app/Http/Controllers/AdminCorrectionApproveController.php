<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use Carbon\Carbon;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminCorrectionApproveController extends Controller
{
    /**
     * 修正申請承認画面表示
     */
    public function show($attendance_correct_request_id) {
        // 対象の申請データ特定
        $attendanceRequest = AttendanceRequest::with(['attendance.user'])
            ->findOrFail($attendance_correct_request_id);

        // 名前・日付表示用
        $attendance = $attendanceRequest->attendance;

        // 出勤・退勤表示用
        $displayClockIn = optional($attendanceRequest->requested_clock_in ?? $attendance->clock_in)->format('H:i');
        $displayClockOut = optional($attendanceRequest->requested_clock_out ?? $attendance->clock_out)->format('H:i');

        // 修正申請があった休憩をbreak_idをキーにして抜き出す
        $requestedBreakId = [];
        foreach (($attendanceRequest->requested_breaks ?? []) as $requestedBreak) {
                if (!empty($requestedBreak['break_id'])) {
                    $requestedBreakId[(int)$requestedBreak['break_id']] = [
                        'break_in' => $requestedBreak['break_in'] ?? null,
                        'break_out' => $requestedBreak['break_out'] ?? null,
                    ];
                }
        }

        // 表示用の休憩データを作成
        $displayBreaks = [];

        // 休憩の修正申請があればそれを表示、修正がなければ元の休憩データを表示させる
        foreach ($attendance->breaks as $break) {
            if (isset($requestedBreakId[$break->id])) {
                $displayBreaks[] = (object)[
                    'id' => $break->id,
                    'break_in' => !empty($requestedBreakId[$break->id]['break_in'])
                        ? Carbon::parse($requestedBreakId[$break->id]['break_in'])->format('H:i')
                        : optional($break->break_in)->format('H:i'),
                    'break_out' => !empty($requestedBreakId[$break->id]['break_out'])
                        ? Carbon::parse($requestedBreakId[$break->id]['break_out'])->format('H:i')
                        : optional($break->break_out)->format('H:i'),
                ];
            } else {
                $displayBreaks[] = (object)[
                    'id' => $break->id,
                    'break_in' => optional($break->break_in)->format('H:i'),
                    'break_out' =>optional($break->break_out)->format('H:i'),
                ];
            }
        }

        // 承認済みかどうかを判断
        $isApproved = $attendanceRequest->status === 'approved';

        return view('attendance.admin_correction_approve', compact('attendanceRequest', 'attendance', 'displayClockIn', 'displayClockOut', 'displayBreaks', 'isApproved'));
    }

    /**
     * 承認実行（勤怠テーブルと休憩テーブルの書き換え実行）
     */
    public function approve($attendance_correct_request_id)
    {
        DB::transaction (function () use ($attendance_correct_request_id) {
            // 対象の申請データ特定
            $request = AttendanceRequest::with(['attendance.user'])
            ->findOrFail($attendance_correct_request_id);

            // 二重承認防止
            if ($request->status === 'approved') {
                return;
            }

            // attendancesテーブル書き換え
            $attendance = $request->attendance;
            $attendance->update([
                'clock_in' => $request->requested_clock_in,
                'clock_out' => $request->requested_clock_out,
            ]);

            // breaksテーブル書き換え
            $attendance->breaks()->delete();
            foreach ($request->requested_breaks as $break) {
                if ($break['break_in'] && $break['break_out']) {
                    $attendance->breaks()->create([
                        'break_in' => $break['break_in'],
                        'break_out' => $break['break_out'],
                    ]);
                }
            }
            // 修正申請を承認済みに変更
            $request->update([
                'status' => 'approved',
                'approved_by' => Auth::guard('admin')->id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->back();
    }
}
