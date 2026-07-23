<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\WhatsappOtpLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsappOtpLoginController extends Controller
{
    public function request(Request $request, WhatsappOtpLoginService $otp): RedirectResponse
    {
        $data = $request->validate([
            'otp_phone' => ['required', 'string', 'max:20'],
        ]);

        $result = $otp->requestOtp($data['otp_phone'], (string) $request->ip());

        if (! $result['ok']) {
            return back()
                ->withInput(['otp_phone' => $data['otp_phone']])
                ->with('otp_error', $result['error']);
        }

        return back()
            ->withInput(['otp_phone' => $data['otp_phone']])
            ->with('otp_success', 'Kode OTP sudah dikirim ke WhatsApp ' . $result['masked_phone'] . '.');
    }

    public function verify(Request $request, WhatsappOtpLoginService $otp): RedirectResponse
    {
        $data = $request->validate([
            'otp_phone' => ['required', 'string', 'max:20'],
            'otp_code' => ['required', 'digits:6'],
            'otp_remember' => ['nullable', 'boolean'],
        ]);

        $result = $otp->verifyOtp($data['otp_phone'], $data['otp_code'], (string) $request->ip());

        if (! $result['ok']) {
            return back()
                ->withInput(['otp_phone' => $data['otp_phone']])
                ->with('otp_error', $result['error']);
        }

        Auth::login($result['user'], $request->boolean('otp_remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
