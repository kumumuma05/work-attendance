<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceDetailController extends Controller
{
    public function index(Attendance $attendance)
    {
        return view('attendance.detail', compact('attendance'));
    }
}
