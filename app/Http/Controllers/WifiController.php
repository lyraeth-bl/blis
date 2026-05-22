<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Wifi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class WifiController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $accessibleUnitIds = $user?->accessibleUnitIds() ?? collect();
        $selectedUnitId = Unit::query()->whereKey($request->integer('unit'))->value('id');

        $wifis = Wifi::query()
            ->with('unitModel')
            ->when($selectedUnitId !== null, function ($query) use ($selectedUnitId): void {
                $query->where(function ($query) use ($selectedUnitId): void {
                    $query->whereNull('unit_id')
                        ->orWhere('unit_id', $selectedUnitId);
                });
            })
            ->where(function ($query) use ($user, $accessibleUnitIds): void {
                $query->where('is_private', false);

                if ($user === null) {
                    return;
                }

                $query->orWhere(function ($query) use ($accessibleUnitIds): void {
                    $query->where('is_private', true)
                        ->where(function ($query) use ($accessibleUnitIds): void {
                            $query->whereNull('unit_id');

                            if ($accessibleUnitIds->isNotEmpty()) {
                                $query->orWhereIn('unit_id', $accessibleUnitIds);
                            }
                        });
                });
            })
            ->orderBy('location')
            ->orderBy('ssid')
            ->get();

        $units = Unit::query()->orderBy('name')->orderBy('campus')->get();

        return view('wifi.index', compact('wifis', 'units', 'selectedUnitId'));
    }
}
