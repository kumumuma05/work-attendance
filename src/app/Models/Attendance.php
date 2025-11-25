<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * 一括代入可能カラム
     */
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
    ];

    /**
     * この勤怠記録に紐づくユーザー情報を取得
     * - attendance.user_id -> users.id
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * この勤怠記録に紐づく休憩記録一覧を取得     * - attendances.id -> breaks.attendance_id
     */
    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }
}
