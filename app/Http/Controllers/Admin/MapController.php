<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\View\View;

class MapController extends Controller
{
    public function index(): View
    {
        $customers = Customer::query()
            ->with('plan')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'username' => $c->username,
                'plan' => $c->plan?->name,
                'status' => $c->status,
                'address' => $c->address,
                'odp' => $c->odp_name,
                'identity_card' => $c->hasIdentityCard(),
                'profile_complete' => $c->profileIsComplete(),
                'lat' => (float) $c->latitude,
                'lng' => (float) $c->longitude,
            ])
            ->values();

        $tanpaKoordinat = Customer::whereNull('latitude')->count();

        // Kelompok per ODP: berguna saat gangguan massal satu ODP.
        $perOdp = Customer::whereNotNull('odp_name')
            ->selectRaw('odp_name, COUNT(*) as total')
            ->groupBy('odp_name')
            ->orderByDesc('total')
            ->get();

        return view('map.index', compact('customers', 'tanpaKoordinat', 'perOdp'));
    }
}
