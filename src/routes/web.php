<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Requests\AttendanceDetailRequest;
use App\Http\Controllers\AdminAttendanceListController;
use App\Http\Controllers\AdminAttendanceDetailController;
use App\Http\Controllers\CorrectionRequestController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\AdminCorrectionRequestController;
use App\Http\Controllers\AdminCorrectionApproveController;

// 一般ログイン認証用
Route::get('/login', function() {
    return view('auth.login');
})->middleware('guest:web');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth:web')->name('verification.notice');
Route::post('/logout', function (Request $request) {

    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
});

// 管理者認証用
Route::get('/admin/login', function () {
    return view('auth.admin_login');
})->middleware('guest:admin');
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login');
Route::post('/admin/logout', function (Request $request) {
    Auth::guard('admin')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/admin/login');
});

// 一般ユーザ用
Route::middleware(['auth:web', 'verified'])->group(function () {
    // 勤怠登録画面表示
    Route::get('/attendance', [AttendanceController::class, 'index']);
    // 勤怠登録
    Route::post('/attendance/clock_in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/break_in', [AttendanceController::class, 'breakIn']);
    Route::post('/attendance/break_out', [AttendanceController::class, 'breakOut']);
    Route::post('/attendance/clock_out', [AttendanceController::class, 'clockOut']);
    // 勤怠一覧画面表示
    Route::get('/attendance/list', [AttendanceListController::class, 'index']);
    // 勤怠詳細画面表示
    Route::get('/attendance/detail/{id}', [AttendanceDetailController::class, 'show']);
    // 勤怠修正依頼登録
    Route::post('/attendance/detail/{id}', [AttendanceDetailController::class, 'store']);
    // 申請一覧画面表示
    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index']);
});

// 管理者用
Route::middleware('auth:admin')->group(function () {
    // 勤怠一覧画面表示
    Route::get('/admin/attendance/list', [AdminAttendanceListController::class, 'index']);
    // 勤怠詳細画面表示
    Route::get('/admin/attendance/{id}', [AdminAttendanceDetailController::class, 'show']);
    // 勤怠詳細修正
    Route::post('/admin/attendance/{id}', [AdminAttendanceDetailController::class, 'update']);
    // 修正申請承認画面表示
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminCorrectionApproveController::class, 'show']);
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminCorrectionApproveController::class, 'approve']);
});