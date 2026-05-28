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
            ->with('units')
            ->when($selectedUnitId !== null, function ($query) use ($selectedUnitId): void {
                $query->where(function ($query) use ($selectedUnitId): void {
                    $query->doesntHave('units')
                        ->orWhereHas('units', fn ($query) => $query->whereKey($selectedUnitId));
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
                            $query->doesntHave('units');

                            if ($accessibleUnitIds->isNotEmpty()) {
                                $query->orWhereHas('units', fn ($query) => $query->whereKey($accessibleUnitIds->all()));
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
