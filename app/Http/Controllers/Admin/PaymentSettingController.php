<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    public function __construct(protected PaymentManager $payments) {}

    public function index(): View
    {
        $drivers = $this->payments->available();
        $active  = $this->payments->activeDriverName();

        // Kumpulkan field konfigurasi tiap driver untuk ditampilkan di form.
        $configFields = [];
        foreach ($drivers as $name) {
            $configFields[$name] = $this->payments->driver($name)->getConfigFields();
        }

        // Nilai tersimpan.
        $values = [];
        foreach ($configFields as $fields) {
            foreach ($fields as $key => $meta) {
                $values[$key] = Setting::get($key);
            }
        }

        return view('settings.payment', compact('drivers', 'active', 'configFields', 'values'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'active_driver' => ['required', 'in:' . implode(',', $this->payments->available())],
        ]);

        Setting::put('active_driver', $data['active_driver']);

        // Simpan semua field konfigurasi yang dikirim (kecuali _token & active_driver).
        foreach ($request->except(['_token', 'active_driver']) as $key => $value) {
            if ($value !== null && $value !== '') {
                Setting::put($key, $value);
            }
        }

        return back()->with('success', 'Pengaturan pembayaran THRE.F.NET tersimpan. Driver aktif: ' . $data['active_driver']);
    }
}
