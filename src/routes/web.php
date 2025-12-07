<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Requests\AttendanceDetailRequest;
use App\Http\Controllers\AdminAttendanceController;

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::post('/logout', function (Request $request) {

    $user = Auth::user();
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    if ($user && $user->is_admin) {
        return redirect('/admin/login');
    }
    return redirect('/login');
});

Route::get('/admin/login', function () {
    return view('auth.admin_login');
});
Route::middleware('auth')->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index']);
});


Route::middleware('auth', 'verified')->group(function () {
    // 勤怠登録画面表示
    Route::get('/attendance', [AttendanceController::class, 'index']);
    // 勤怠登録
    Route::post('/attendance/clock_in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/break_in', [AttendanceController::class, 'breakIn']);
    Route::post('/attendance/break_out', [AttendanceController::class, 'breakOut']);
    Route::post('/attendance/clock_out', [AttendanceController::class, 'clockOut']);

});

// 勤怠一覧画面表示
    Route::get('/attendance/list', [AttendanceListController::class, 'index']);

    Route::get('/attendance/detail/{id}', [AttendanceDetailController::class, 'show']);

    // バリデテスト
    Route::post('/attendance/detail/test', function(AttendanceDetailRequest $request) {
    return back()->withInput();
    });