<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

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
            $attendanceRequest = AttendanceRequest::with(['attendance.user'])
            ->findOrFail($attendance_correct_request_id);

            // 二重承認防止
            if ($request->status === 'approved') {
                return;
            }

            // attendancsテーブル書き換え
            $attendance => $request->attendance;
            $attendance->updadte([
                'clock_in' => $request->requested_clock_in,
                'clock_out' => $request->requested_clock_out,
            ])

            // breaksテーブル書き換え
            $attendance->breaks()->delete();


        })
    } 
}
