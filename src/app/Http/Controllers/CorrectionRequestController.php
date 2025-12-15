<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CorrectionRequestController extends Controller
{
    public function index() {
        return view('attendance.correction_request');
    }
}
