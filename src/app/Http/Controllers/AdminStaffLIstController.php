<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminStaffLIstController extends Controller
{
    public function index()
    {
        return view('attendance.staff_list');
    }
}
