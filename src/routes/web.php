<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::post('/logout', function (Request $request) {

    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
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