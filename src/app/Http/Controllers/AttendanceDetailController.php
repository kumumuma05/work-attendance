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
        $hasPendingRequest = AttendanceRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();
        $displayBreaks = $breaks->toBase();
        if (!$hasPendingRequest) {
            $displayBreaks->push((object)[
                'break_in' => null,
                'break_out' => null,
            ]);
        }
        $pendingRequest = AttendanceRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return view('attendance.detail', compact('attendance', 'user', 'displayBreaks', 'hasPendingRequest', 'pendingRequest'));
    }

    /**
     * 勤怠修正依頼登録
     */
    public function create(AttendanceDetailRequest $request, $id)
    {
        // レコードの抜き出し
        $attendance = Attendance::findOrFail($id);
        // 基準日設定
        $baseDate = $attendance->clock_in->format('Y-m-d');

        // 出勤・退勤時間をdatetimeに変換
        $clockIn  = Carbon::parse("{$baseDate} {$request->requested_clock_in}");
        $clockOut = Carbon::parse("{$baseDate} {$request->requested_clock_out}");
        // 日またぎ勤務の補正
        if ($clockOut->lt($clockIn)) {
            $clockOut->addDay();
        }

        // 休憩時間をdatetimeに変換
        $breaks = [];
        if ($request->requested_breaks) {
            foreach ($request->requested_breaks as $break) {
                $breakIn = $break['break_in']
                    ? Carbon::parse("{$baseDate} {$break['break_in']}")
                    : null;
                $breakOut = $break['break_out']
                    ? Carbon::parse("{$baseDate} {$break['break_out']}")
                    : null;

                if ($breakOut && $breakIn && $breakOut->lt($breakIn)) {
                    $breakOut->addDay();
                }

                $breaks[] = [
                    'break_in' => $breakIn ? $breakIn->toDateTimeString() : null,
                    'break_out' => $breakOut ? $breakOut->toDateTimeString() : null,
                ];
            }
        }

            AttendanceRequest::create([
            'attendance_id' => $id,
            'user_id' => auth()->id(),
            'requested_clock_in' => $clockIn,
            'requested_clock_out' => $clockOut,
            'requested_breaks' => json_encode($breaks),
            'remarks' => $request->remarks,
            'status' => 'pending',
        ]);

        return back()->with('status', '*承認待ちのため修正はできません。');
    }
}

