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

        // 管理者がログイン中の場合は管理者用のコントローラへ移動
        if (auth('admin')->check()) {
            return app(AdminCorrectionRequestController::class)->index($request);
        }

        $tab = $request->query('tab', 'pending');
        $requests = AttendanceRequest::with(['attendance.user'])
            ->where('user_id', auth()->id())
            ->where('status' ,$tab)
            ->latest()
            ->get();

        return view('attendance.correction_request', compact('tab', 'requests'));
    }
}
