<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceListController extends Controller
{
    public function index()
    {

        $attendances = Attendance::all();
        $currentMonth = now()->format('Y/m');
        return view('attendance.list', compact('attendances', 'currentMonth'));
    }
}
