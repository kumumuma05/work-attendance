<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;

Route::get('/attendance', [AttendanceController::class, 'index'])->middleware(['auth', 'verified']);

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::post('/logout', function (Request $request) {

    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
});

Route::post('/attendance/clock_in', [AttendanceController::class, 'clockIn'])->middleware('auth');
Route::post('/attendance/break_in', [AttendanceController::class, 'breakIn'])->middleware('auth');
Route::post('/attendance/break_out', [AttendanceController::class, 'breakOut'])->middleware('auth');
Route::post('/attendance/clock_out', [AttendanceController::class, 'clockOut'])->middleware('auth');