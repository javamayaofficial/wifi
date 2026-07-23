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
        $paymentOptions = $this->payments->optionCatalog();

        $configFields = [];
        foreach ($drivers as $name) {
            $configFields[$name] = $this->payments->driver($name)->getConfigFields();
        }

        $values = [];
        foreach ($configFields as $fields) {
            foreach ($fields as $key => $meta) {
                $values[$key] = Setting::get($key, match ($key) {
                    'manual_bank_info' => config('threfnet.payments.manual.bank_info'),
                    'manual_qris_image_url' => config('threfnet.payments.manual.qris_image_url'),
                    'manual_qris_note' => config('threfnet.payments.manual.qris_note'),
                    'manual_cash_note' => config('threfnet.payments.manual.cash_note'),
                    default => null,
                });
            }
        }

        foreach (array_keys($paymentOptions) as $code) {
            $values['payment_option_' . $code] = filter_var(
                Setting::get('payment_option_' . $code, '1'),
                FILTER_VALIDATE_BOOLEAN
            );
        }

        return view('settings.payment', compact('drivers', 'active', 'configFields', 'values', 'paymentOptions'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'active_driver' => ['required', 'in:' . implode(',', $this->payments->available())],
        ]);

        Setting::put('active_driver', $data['active_driver']);

        $knownKeys = [];
        foreach ($this->payments->available() as $driver) {
            $knownKeys = array_merge($knownKeys, array_keys($this->payments->driver($driver)->getConfigFields()));
        }

        $knownKeys = array_merge(
            $knownKeys,
            array_map(fn ($code) => 'payment_option_' . $code, array_keys($this->payments->optionCatalog()))
        );

        foreach ($knownKeys as $key) {
            $value = $request->input($key);

            if (str_starts_with($key, 'payment_option_')) {
                Setting::put($key, $request->boolean($key) ? '1' : '0');
                continue;
            }

            Setting::put($key, is_string($value) ? trim($value) : (string) $value);
        }

        return back()->with('success', 'Pengaturan pembayaran THRE.F.NET tersimpan. Driver aktif: ' . $data['active_driver']);
    }
}
