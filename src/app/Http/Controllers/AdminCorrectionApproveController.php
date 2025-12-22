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

        // 申請された休憩
        $requestedBreaks = collect($attendanceRequest->requested_breaks ?? [])
            ->map(function($break) {
                return (object) [
                    'break_in' => isset($break['break_in'])
                        ? Carbon::parse($break['break_in'])
                        : null,
                    'break_out' => isset($break['break_out'])
                        ? Carbon::parse($break['break_out'])
                        : null
                ];
            });

        // 承認済みかどうかを判断
        $isApproved = $attendanceRequest->status === 'approved';

        return view('attendance.admin_correction_approve', compact('attendanceRequest', 'attendance',  'requestedBreaks', 'isApproved'));
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
