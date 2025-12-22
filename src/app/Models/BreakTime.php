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

    protected $casts = [
        'break_in' => 'datetime',
        'break_out' => 'datetime',
    ];

    /**
     * この休憩が属する勤怠記録を取得
     * - breaks.attendance_id -> attendances.id
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
