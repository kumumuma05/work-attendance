<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;

class CorrectionRequestController extends Controller
{
    /**
     * 申請一覧画面表示
     */
    public function index(Request $request) {

        $tab = $request->query('tab', 'pending');
        $requests = AttendanceRequest::with(['attendance.user'])
            ->where('status' ,$tab)
            ->latest()
            ->get();

        return view('attendance.correction_request', compact('tab', 'requests'));
    }
}
