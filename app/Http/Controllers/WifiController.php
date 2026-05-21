<?php

namespace App\Http\Controllers;

use App\Models\Wifi;

class WifiController extends Controller
{
    public function index()
    {
        $wifis = Wifi::orderBy('location')->orderBy('ssid')->get();

        return view('wifi.index', compact('wifis'));
    }
}
