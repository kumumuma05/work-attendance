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
     * この勤怠記録をつかうユーザー情報を取得
     * attendance.user_id -> users.id
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
