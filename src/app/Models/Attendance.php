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
     * 日本語曜日を返すアクセサ
     */
    public function getWeekdayAttribute()
    {
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        return $weekdays[$this->date->dayOfWeek];
    }

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    // 合計休憩時間を計算するアクセサ
    public function getBreakMinutesAttribute()
    {
        // 休憩レコードがない場合は空欄を返す
        if ($this->breaks->isEmpty()) {
            return 0;
        }
        // すべての有効な休憩レコードの合計時間を返す
        return $this->breaks->sum(function ($break) {
            if (!$break->break_in || !$break->break_out) {
                return 0;
            }

            return $break->break_out->diffInMinutes($break->break_in);
        });

    }

    /**
     * 合計休憩時間を時間表示にするアクセサ
     */
    public function getBreakDurationAttribute()
    {
        $totalMinutes = $this->break_minutes;

        if ($totalMinutes === 0) {
            return "";
        }

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * 合計勤務時間を計算するアクセサ
     */
    public function getTotalHoursAttribute() {
        if (!$this->clock_in || !$this->clock_out) {
            return null;
        }

        $workMinutes = $this->clock_out->diffInMinutes($this->clock_in);

        $breakMinutes = $this->break_minutes;

        $total = $workMinutes - $breakMinutes;

        $hours = floor($total / 60);
        $minutes = $total % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

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
