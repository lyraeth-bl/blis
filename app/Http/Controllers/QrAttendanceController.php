<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class QrAttendanceController extends Controller
{
    public function __invoke(): View
    {
        return view('qr-attendance.index');
    }
}
