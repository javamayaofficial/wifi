<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\Auth\PortalWhatsappOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Login portal pelanggan via OTP WhatsApp.
 */
class PortalAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('portal.login');
    }

    public function requestOtp(Request $request, PortalWhatsappOtpService $otp): RedirectResponse
    {
        $data = $request->validate([
            'otp_phone' => ['required', 'string', 'max:20'],
        ]);

        $result = $otp->requestOtp($data['otp_phone'], (string) $request->ip());

        if (! $result['ok']) {
            return back()
                ->withInput(['otp_phone' => $data['otp_phone']])
                ->with('error', $result['error']);
        }

        return back()
            ->withInput(['otp_phone' => $data['otp_phone']])
            ->with('success', 'Kode OTP sudah dikirim ke WhatsApp ' . $result['masked_phone'] . '.');
    }

    public function login(Request $request, PortalWhatsappOtpService $otp): RedirectResponse
    {
        $data = $request->validate([
            'otp_phone' => ['required', 'string', 'max:20'],
            'otp_code' => ['required', 'digits:6'],
        ]);

        $result = $otp->verifyOtp($data['otp_phone'], $data['otp_code'], (string) $request->ip());

        if (! $result['ok']) {
            return back()
                ->withInput(['otp_phone' => $data['otp_phone']])
                ->with('error', $result['error']);
        }

        $request->session()->regenerate();
        $request->session()->put('portal_customer_id', $result['customer']->id);

        return redirect('/portal');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('portal_customer_id');
        $request->session()->regenerate();

        return redirect('/portal/login');
    }
}
