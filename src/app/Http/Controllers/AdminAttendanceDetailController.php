<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Http\Requests\AttendanceCorrectionRequest;
use Carbon\Carbon;

class AdminAttendanceDetailController extends Controller
{
    /**
     * 管理者用勤怠詳細画面表示
     */
    public function show($id)
    {
        // 選択したスタッフの勤怠データとそれに紐づく休憩データを取得
        $attendance = Attendance::with('user')
            ->where('id', $id)
            ->firstOrFail();
        $breaks = $attendance->breaks;

        // 修正申請待ちを判別
        $pendingRequest = AttendanceRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->latest()
            ->first();
        $hasPendingRequest = $pendingRequest !== null;

        // 修正申請待ちの勤怠データがあればそれを表示、なければ元のデータを表示する
        $displayClockIn = optional($pendingRequest->requested_clock_in ?? $attendance->clock_in)->format('H:i');
        $displayClockOut = optional($pendingRequest->requested_clock_out ?? $attendance->clock_out)->format('H:i');

        // 修正申請があった休憩のbreak_idをキーにして抜き出す
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

        return view('admin_attendance_detail', compact('attendance', 'pendingRequest', 'hasPendingRequest', 'displayClockIn', 'displayClockOut', 'displayBreaks'));
    }

    /**
     * 勤怠・休憩テーブル修正
     */
    public function update(AttendanceCorrectionRequest $request, $id)
    {
        // レコードの抜き出し
        $attendance = Attendance::with('breaks')->findOrFail($id);
        // 基準日設定
        $baseDate = $attendance->clock_in->format('Y-m-d');

        // 元の勤怠データ（時刻だけ）を用意
        $originalClockIn = optional($attendance->clock_in)->format('H:i');
        $originalClockOut = optional($attendance->clock_out)->format('H:i');

        // 勤怠修正リクエスト
        $reqClockIn = $request->requested_clock_in;
        $reqClockOut = $request->requested_clock_out;

        // 勤怠修正リクエストが元データと異なる場合のみ日時に変換
        $clockIn  = null;
        if (!empty($reqClockIn) && $reqClockIn !== $originalClockIn) {
            $clockIn = Carbon::parse("{$baseDate} {$reqClockIn}");
        }
        $clockOut  = null;
        if (!empty($reqClockOut) && $reqClockOut !== $originalClockOut) {
            $clockOut = Carbon::parse("{$baseDate} {$reqClockOut}");
        }

        // 元の休憩データ（時刻だけ）を用意
        $originalBreaks = [];

        foreach ($attendance->breaks as $break) {
            $originalBreaks[$break->id] = [
                'break_in'  => optional($break->break_in)->format('H:i'),
                'break_out' => optional($break->break_out)->format('H:i'),
            ];
        }

        // 休憩をupdateとcreateに分解
        $updateBreaks = [];
        $createBreaks = [];

        foreach (($request->requested_breaks ?? []) as $requestedBreak) {
            $breakId = $requestedBreak['break_id'] ?? null;
            $in = $requestedBreak['break_in'] ?? null;
            $out = $requestedBreak['break_out'] ?? null;

            // 修正がなければスキップ
            if (empty($in) && empty($out)) {
                continue;
            }

            $breakIn = !empty($in) ? Carbon::parse("{$baseDate} {$in}")->toDateTimeString() : null;
            $breakOut = !empty($out) ? Carbon::parse("{$baseDate} {$out}")->toDateTimeString() : null;

            // 元データと同じ（差分なし）ならスキップ
            if (!empty($breakId)) {
                if (isset($originalBreaks[$breakId])) {
                    $original = $originalBreaks[$breakId];
                    if (($in ?? '') === ($original['break_in'] ?? '') && ($out ?? '') === ($original['break_out'] ?? '')) {
                        continue;
                    }
                }
                $updateBreaks[(int)$breakId] = [
                    'break_in' => $breakIn,
                    'break_out' => $breakOut,
                ];
            } else {
                $createBreaks[] = [
                    'break_in' => $breakIn,
                    'break_out' => $breakOut,
                ];
            }
        }

        $requestedBreaks = [];
        if (!empty($updateBreaks)) $requestedBreaks['update'] = $updateBreaks;
        if (!empty($createBreaks)) $requestedBreaks['create'] = $createBreaks;

        DB::transaction(function() use ($request, $attendance, $clockIn, $clockOut, $requestedBreaks) {
            // attendance_requestsにデータ登録する（履歴を残す）
            $attendanceRequest = AttendanceRequest::create([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'requested_clock_in' => $clockIn,
                'requested_clock_out' => $clockOut,
                'requested_breaks' => $requestedBreaks ?: null,
                'remarks' => $request->remarks,
                'status' => 'approved',
                'approved_by' => Auth::guard('admin')->id(),
                'approved_at' => now(),
            ]);

            // attendancesテーブル書き換え（差分のみ）
            $attendanceUpdate = [];
            if (!is_null($clockIn)) {
                $attendanceUpdate['clock_in'] = $clockIn;
            }
            if (!is_null($clockOut)) {
                $attendanceUpdate['clock_out'] = $clockOout;
            }
            if (!empty($attendanceUpdate)) {
                $attendance->update($attendanceUpdate);
            }

            // 既存のbreaksテーブル書き換え（差分のみ）
            $reqBreaks = $attendanceRequest->requested_breaks ?? [];
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
        });

        return back()->with('status', '*修正が終了しました。');
    }
}

