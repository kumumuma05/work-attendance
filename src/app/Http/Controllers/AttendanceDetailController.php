<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceDetailController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $user = auth()->user();

        $breaks = $attendance->breaks;
        $displayBreaks = $breaks->toBase();
        $displayBreaks->push((object)[
            'break_in' => null,
            'break_out' => null,
        ]);
        return view('attendance.detail', compact('attendance', 'user', 'displayBreaks'));
    }

    public function update(AttendanceUpdateRequest $request, $id)
{
    dd($request->all());
}
}

