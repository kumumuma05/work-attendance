<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $table = 'breaks';

    /**
     * 一括代入可能カラム
     */
    protected $fillable = [
        'attendance_id',
        'break_in',
        'break_out',
    ];

    /**
     * この休憩が属する勤怠記録を取得
     * - breaks.attendance_id -> attendance.id
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
