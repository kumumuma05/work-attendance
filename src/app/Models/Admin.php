<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * この管理者が承認した修正申請一覧を取得
     * - admins.id -> attendance_requests.approved_by
     */
    public function approvedRequests()
    {
        return $this->hasMany(AttendanceRequest::class, 'approved_by');
    }
}
