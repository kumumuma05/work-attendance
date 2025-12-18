<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceRequest;

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
    public function update(AdminAttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            'clock_in' => $request->requested_clock_in,
            'clock_out' => $request->requested_clock_out,
            'updated_by_admin_id' => auth('admin')->id(),
        ]);
    }

}

