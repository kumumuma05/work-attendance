<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

class AdminAttendanceDetailController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::with('user')
            ->where('id', $id)
            ->firstOrFail();

        $breaks = $attendance->breaks;
        $displayBreaks = $breaks->toBase();
        $displayBreaks->push((object)[
            'break_in' => null,
            'break_out' => null,
        ]);
        return view('attendance.admin_detail', compact('attendance', 'displayBreaks'));
    }

    
}

