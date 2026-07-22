<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Notifications\MailketingService;
use App\Services\Notifications\WhatsAppGatewayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationSettingController extends Controller
{
    public function index(): View
    {
        $values = [
            'whatsapp_gateway_url' => Setting::get('whatsapp_gateway_url', config('threfnet.whatsapp.gateway_url')),
            'whatsapp_api_key'     => Setting::get('whatsapp_api_key'),
            'mailketing_api_token' => Setting::get('mailketing_api_token'),
            'mailketing_from_name' => Setting::get('mailketing_from_name', config('threfnet.mailketing.from_name')),
            'mailketing_from_email'=> Setting::get('mailketing_from_email', config('threfnet.mailketing.from_email')),
        ];

        return view('settings.notification', compact('values'));
    }

    public function update(Request $request): RedirectResponse
    {
        foreach ([
            'whatsapp_gateway_url', 'whatsapp_api_key',
            'mailketing_api_token', 'mailketing_from_name', 'mailketing_from_email',
        ] as $key) {
            if ($request->filled($key)) {
                Setting::put($key, $request->input($key));
            }
        }

        return back()->with('success', 'Pengaturan notifikasi THRE.F.NET tersimpan.');
    }

    /** Tombol "Test": kirim WA + Email percobaan. */
    public function test(Request $request, WhatsAppGatewayService $wa, MailketingService $mail): RedirectResponse
    {
        $data = $request->validate([
            'channel' => ['required', 'in:whatsapp,email'],
            'target'  => ['required', 'string'],
        ]);

        if ($data['channel'] === 'whatsapp') {
            $res = $wa->send($data['target'], 'Tes notifikasi THRE.F.NET Billing System. Abaikan pesan ini.');
        } else {
            $html = MailketingService::template('Tes Notifikasi', '<p>Ini email percobaan dari THRE.F.NET Billing System.</p>');
            $res  = $mail->send($data['target'], 'THRE.F.NET - Tes Notifikasi', $html);
        }

        return back()->with(
            ($res['ok'] ?? false) ? 'success' : 'error',
            ($res['ok'] ?? false) ? 'Tes terkirim.' : ('Tes gagal: ' . ($res['error'] ?? 'unknown'))
        );
    }
}
