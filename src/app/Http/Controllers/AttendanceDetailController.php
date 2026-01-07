<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Http\Requests\AttendanceDetailRequest;
use Carbon\Carbon;

class AttendanceDetailController extends Controller
{
    // 勤怠詳細画面表示
    public function show($id)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $user = auth()->user();

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

        return view('attendance.detail', compact('attendance', 'user', 'displayBreaks', 'hasPendingRequest', 'pendingRequest'));
    }

    /**
     * 勤怠修正依頼登録
     */
    public function store(AttendanceDetailRequest $request, $id)
    {
        // レコードの抜き出し
        $attendance = Attendance::findOrFail($id);
        // 基準日設定
        $baseDate = $attendance->clock_in->format('Y-m-d');

        // 元データ（時刻だけ）を用意
        $originalClockIn = optional($attendance->clock_in)->format('H:i');
        $originalClockOut = optional($attendance->clock_out)->format('H:i');

        // 勤怠修正リクエスト
        $reqClockIn = $request->requested_clock_in;
        $reqClockOut = $request->requested_clock_out;

        // 勤怠修正リクエストだけdatetimeに変換
        $clockIn  = null;
        if (!empty($reqClockIn) && $reqClockIn !== $originalClockIn) {
            $clockIn = Carbon::parse("{$baseDate} {$reqClockIn}");
        }
        $clockOut  = null;
        if (!empty($reqClockOut) && $reqClockOut !== $originalClockOut) {
            $clockOut = Carbon::parse("{$baseDate} {$reqClockOut}");
        }

        // 休憩の元データ（時刻だけ）を用意
        $originalBreaks = $attendance->breaks()
            ->get()
            ->keyBy('id')
            ->map(function($break) {
                return [
                    'break_in' => optional($break->break_in)->format('H:i'),
                    'break_out' => optional($break->break_out)->format('H:i'),
                ];
            });

        // 休憩修正リクエストだけdatetimeに変換
        $breaks = [];
        foreach(($request->requested_breaks ?? []) as $index => $break) {
            $breakId = $break['break_id'] ?? null;
            $in = $break['break_in'] ?? null;
            $out = $break['break_out'] ?? null;

            // 余剰の休憩欄が空欄のままだったらスキップ
            if (empty($in) && empty($out)) {
                continue;
            }
            // 休憩が未変更時はスキップ
            if (!empty($breakId) && isset($originalBreaks[$breakId])) {
                $original = $originalBreaks[$breakId];

                if (($in ?? '') === ($original['break_in'] ?? '') && ($out ?? '') === ($original['break_out'] ?? '')) {
                    continue;
                }
            }

            $breakIn = !empty($in) ? Carbon::parse("{$baseDate} {$in}") : null;
            $breakOut = !empty($out) ? Carbon::parse("{$baseDate} {$out}") : null;

            $breaks[] = [
                    'break_id' => !empty($breakId) ? (int)$breakId : null,
                    'break_in' => $breakIn?->toDateTimeString(),
                    'break_out' => $breakOut?->toDateTimeString(),
                ];
            }

        AttendanceRequest::create([
            'attendance_id' => $id,
            'user_id' => auth()->id(),
            'requested_clock_in' => $clockIn,
            'requested_clock_out' => $clockOut,
            'requested_breaks' => $breaks ?: null,
            'remarks' => $request->remarks,
            'status' => 'pending',
        ]);

        return back()->with('status', '*承認待ちのため修正はできません。');
    }
}

