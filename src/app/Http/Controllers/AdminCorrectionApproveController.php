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

        // 修正申請があった休憩を取り出す
        $requestedBreakId = [];
        $requestedCreates = [];

        $req = $attendanceRequest->requested_breaks ?? [];

        // 休憩の修正申請があればそれを表示、修正がなければ元の休憩データを表示させる
        foreach (($req['update'] ?? []) as $breakId => $requestedBreak) {
            $requestedBreakId[(int)$breakId] = [
                'break_in' => $requestedBreak['break_in'] ?? null,
                'break_out' => $requestedBreak['break_out'] ?? null,
            ];
        }
        foreach (($req['create'] ?? []) as $createBreak) {
            $requestedCreates[] = [
                'break_in' => $createBreak['break_in'] ?? null,
                'break_out' => $createBreak['break_out'] ?? null,
            ];
        }

        $displayBreaks = [];
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
                    'break_out' => optional($break->break_out)->format('H:i'),
                ];
            }
        }

        // 申請で追加された休憩を表示
        foreach ($requestedCreates as $createBreak) {
            $displayBreaks[] = (object)[
                'id' => null,
                'break_in'  => !empty($createBreak['break_in'])  ? Carbon::parse($createBreak['break_in'])->format('H:i')  : null,
                'break_out' => !empty($createBreak['break_out']) ? Carbon::parse($createBreak['break_out'])->format('H:i') : null,
            ];
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
        // 対象の申請データ特定
        $request = AttendanceRequest::with(['attendance.user'])
        ->findOrFail($attendance_correct_request_id);

        // 二重承認防止
        if ($request->status === 'approved') {
            return;
        }

        DB::transaction (function () use ($request) {

            $attendance = $request->attendance;

            // attendancesテーブル書き換え(差分のみ)
            $attendanceUpdate = [];
            if (!is_null($request->requested_clock_in)) {
                $attendanceUpdate['clock_in'] = $request->requested_clock_in;
            }
            if (!is_null($request->requested_clock_out)) {
                $attendanceUpdate['clock_out'] = $request->requested_clock_out;
            }
            if (!empty($attendanceUpdate)) {
                $attendance->update($attendanceUpdate);
            }

            // 既存のbreaksテーブル書き換え（差分のみ）
            $reqBreaks = $request->requested_breaks ?? [];
            foreach (($reqBreaks['update'] ?? []) as $breakId => $break) {
                $breakId = (int)$breakId;
                if (empty($break['break_in']) && empty($break['break_out'])) {
                    continue;
                }

                $attendance->breaks()
                    ->where('id', $breakId)
                    ->update([
                        'break_in' => $break['break_in'] ?? null,
                        'break_out' => $break['break_out'] ?? null,
                    ]);
            }

            // 新規休憩の追加
            foreach (($reqBreaks['create'] ?? []) as $break) {
                if (empty($break['break_in']) && empty($break['break_out'])) {
                    continue;
                }

                $attendance->breaks()->create([
                    'break_in' => $break['break_in'] ?? null,
                    'break_out' => $break['break_out'] ?? null,
                ]);
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
