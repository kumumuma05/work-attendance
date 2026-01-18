<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestDispatchController extends Controller
{
    public function index(
        CorrectionRequestController $userController,
        AdminCorrectionRequestController $adminController
    ) {
        return Auth::guard('admin')->check()
            ? $adminController->index(request())
            : $userController->index(request());
    }
}