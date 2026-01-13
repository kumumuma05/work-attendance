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

        return view('attendance.admin_detail', compact('attendance', 'pendingRequest', 'hasPendingRequest', 'displayClockIn', 'displayClockOut', 'displayBreaks'));
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

