<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminStaffListController extends Controller
{
    public function index()
    {
        $users = User::select('id', 'name', 'email')->get();

        return view('admin_staff_list', compact('users'));
    }
}
