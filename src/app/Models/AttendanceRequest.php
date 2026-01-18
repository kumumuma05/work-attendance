<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    /**
     * 一括代入可能カラム
     */
    protected $fillable = [
        'attendance_id',
        'user_id',
        'requested_clock_in',
        'requested_clock_out',
        'requested_breaks',
        'remarks',
        'status',
        'approved_by',
        'approved_at',
    ];

    /**
     * 配列に変換
     */
    protected $casts = [
        'requested_clock_in' => 'datetime',
        'requested_clock_out' => 'datetime',
        'requested_breaks' => 'array',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
    ];

    /**
     * この修正申請が属する勤怠情報を取得
     * - attendance_requests.attendance_id -> attendances.id
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * この修正申請が属するユーザー情報を取得
     * - attendance_requests.user_id -> users.id
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * この修正申請が属する管理者情報を取得
     * - attendance_requests.approved_by -> admins.id
     */
    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }
}

