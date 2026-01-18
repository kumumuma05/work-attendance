<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Http\Requests\UserAttendanceCorrectionRequest;
use Carbon\Carbon;

class AttendanceDetailController extends Controller
{
    /**
     * 勤怠詳細画面表示
     */
    public function show($id)
    {
        // ログインユーザの勤怠データを取得し、紐づく休憩データも取得
        $user = auth()->user();
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        // 修正申請待ちかどうかを判別
        $pendingRequest = AttendanceRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->latest()
            ->first();
        $hasPendingRequest = $pendingRequest !== null;

        // 修正申請待ちの勤怠データがあればそれを表示、なければ元のデータを表示する
        $displayClockIn = optional($pendingRequest->requested_clock_in ?? $attendance->clock_in)->format('H:i');
        $displayClockOut = optional($pendingRequest->requested_clock_out ?? $attendance->clock_out)->format('H:i');

        // 修正申請があった休憩を取り出す
        $requestedBreakId = [];
        $requestedCreates = [];
        if ($hasPendingRequest) {
            $req = $pendingRequest->requested_breaks ?? [];
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
                    'break_in'  => optional($break->break_in)->format('H:i'),
                    'break_out' => optional($break->break_out)->format('H:i'),
                ];
            }
        }

        // 申請で追加された休憩を表示する
        foreach ($requestedCreates as $createBreak) {
            $displayBreaks[] = (object)[
                'id' => null,
                'break_in' => !empty($createBreak['break_in']) ? Carbon::parse($createBreak['break_in'])->format('H:i') : null,
                'break_out' => !empty($createBreak['break_out']) ? Carbon::parse($createBreak['break_out'])->format('H:i') : null,
            ];
        }

        // 通常画面（申請待ち画面ではない）時、空の休憩行を１つ追加する
        if (!$hasPendingRequest) {
            $displayBreaks[] = (object)[
                'id' => null,
                'break_in' => null,
                'break_out' => null,
            ];
        }

        return view('user_attendance_detail', compact('attendance', 'user', 'displayBreaks', 'hasPendingRequest', 'pendingRequest', 'displayClockIn', 'displayClockOut'));
    }

    /**
     * 勤怠修正依頼登録
     */
    public function store(UserAttendanceCorrectionRequest $request, $id)
    {
        // レコードの抜き出し
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();
        // 基準日設定
        $baseDate = $attendance->clock_in->format('Y-m-d');

        // 元データ（時刻だけ）を用意
        $originalClockIn = optional($attendance->clock_in)->format('H:i');
        $originalClockOut = optional($attendance->clock_out)->format('H:i');

        // 勤怠修正リクエスト
        $reqClockIn = $request->requested_clock_in;
        $reqClockOut = $request->requested_clock_out;

        // 勤怠修正リクエストが元のデータと異なるときのみ日時に変換
        $clockIn  = null;
        if (!empty($reqClockIn) && $reqClockIn !== $originalClockIn) {
            $clockIn = Carbon::parse("{$baseDate} {$reqClockIn}");
        }
        $clockOut  = null;
        if (!empty($reqClockOut) && $reqClockOut !== $originalClockOut) {
            $clockOut = Carbon::parse("{$baseDate} {$reqClockOut}");
        }

        // 休憩の元データ（時刻だけ）を用意
        $originalBreaks = [];

        foreach ($attendance->breaks as $break) {
            $originalBreaks[$break->id] = [
                'break_in'  => optional($break->break_in)->format('H:i'),
                'break_out' => optional($break->break_out)->format('H:i'),
            ];
        }

        // 休憩修正リクエストだけdatetimeに変換
        $updateBreaks = [];
        $createBreaks = [];
        foreach (($request->requested_breaks ?? []) as $break) {
            $breakId = $break['break_id'] ?? null;
            $in = $break['break_in'] ?? null;
            $out = $break['break_out'] ?? null;

            // 余剰の休憩欄が空欄のままだったらスキップ
            if (empty($in) && empty($out)) {
                continue;
            }

            $breakIn = !empty($in) ? Carbon::parse("{$baseDate} {$in}")->toDateTimeString() : null;
            $breakOut = !empty($out) ? Carbon::parse("{$baseDate} {$out}")->toDateTimeString() : null;

            // 休憩が未変更時はスキップ
            if (!empty($breakId)) {
                if (isset($originalBreaks[$breakId])) {
                    $original = $originalBreaks[$breakId];
                    if (($in ?? '') === ($original['break_in'] ?? '') && ($out ?? '') === ($original['break_out'] ?? '')) {
                        continue;
                    }
                }
                // 更新用
                $updateBreaks[(int)$breakId] = [
                    'break_in' => $breakIn,
                    'break_out' =>$breakOut,
                ];
            } else {
                // 新規作成用
                $createBreaks[] = [
                    'break_in' => $breakIn,
                    'break_out' => $breakOut,
                ];
            }
        }

        $requestedBreaks = [];
        if (!empty($updateBreaks)) {
            $requestedBreaks['update'] = $updateBreaks;
        }
        if (!empty($createBreaks)) {
            $requestedBreaks['create'] = $createBreaks;
        }

        AttendanceRequest::create([
            'attendance_id' => $id,
            'user_id' => auth()->id(),
            'requested_clock_in' => $clockIn,
            'requested_clock_out' => $clockOut,
            'requested_breaks' => $requestedBreaks ?: null,
            'remarks' => $request->remarks,
            'status' => 'pending',
        ]);

        return back()->with('status', '*承認待ちのため修正はできません。');
    }
}

