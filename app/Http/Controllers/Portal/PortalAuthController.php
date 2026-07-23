<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * Login portal pelanggan.
 *
 * Password portal DIPISAH dari password PPPoE dan disimpan ter-hash (bcrypt).
 * Password PPPoE harus reversible untuk MikroTik, jadi tidak layak dipakai
 * sebagai kredensial login web.
 */
class PortalAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('portal.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Batasi percobaan agar password tidak bisa ditebak paksa.
        $key = 'portal-login:' . $request->ip() . ':' . $data['username'];

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $detik = RateLimiter::availableIn($key);

            return back()->with('error', "Terlalu banyak percobaan. Coba lagi dalam {$detik} detik.");
        }

        $customer = Customer::where('username', $data['username'])->first();

        if (! $customer || ! $customer->portal_password
            || ! Hash::check($data['password'], $customer->portal_password)) {
            RateLimiter::hit($key, 300);

            return back()->with('error', 'Username atau password salah.');
        }

        RateLimiter::clear($key);

        $request->session()->regenerate();
        $request->session()->put('portal_customer_id', $customer->id);

        return redirect('/portal');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('portal_customer_id');
        $request->session()->regenerate();

        return redirect('/portal/login');
    }
}
