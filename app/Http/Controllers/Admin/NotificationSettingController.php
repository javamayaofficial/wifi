<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Notifications\MailketingService;
use App\Services\Notifications\TelegramService;
use App\Services\Notifications\WhatsAppGatewayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationSettingController extends Controller
{
    public function index(): View
    {
        $values = [
            'whatsapp_provider'    => Setting::get('whatsapp_provider', config('threfnet.whatsapp.provider', 'gateway')),
            'whatsapp_gateway_url' => Setting::get('whatsapp_gateway_url', config('threfnet.whatsapp.gateway_url')),
            'whatsapp_api_key'     => Setting::get('whatsapp_api_key'),
            'fonnte_url'           => Setting::get('fonnte_url', config('threfnet.whatsapp.fonnte_url')),
            'fonnte_token'         => Setting::get('fonnte_token'),
            'whatsapp_country_code'=> Setting::get('whatsapp_country_code', config('threfnet.whatsapp.country_code', '62')),
            'mailketing_api_token' => Setting::get('mailketing_api_token'),
            'mailketing_from_name' => Setting::get('mailketing_from_name', config('threfnet.mailketing.from_name')),
            'mailketing_from_email'=> Setting::get('mailketing_from_email', config('threfnet.mailketing.from_email')),
            'telegram_bot_token'   => Setting::get('telegram_bot_token'),
            'telegram_chat_id'     => Setting::get('telegram_chat_id'),
        ];

        return view('settings.notification', compact('values'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'whatsapp_provider'     => ['nullable', 'in:gateway,fonnte'],
            'whatsapp_country_code' => ['nullable', 'string', 'max:10'],
        ]);

        foreach ([
            'whatsapp_provider', 'whatsapp_gateway_url', 'whatsapp_api_key',
            'fonnte_url', 'fonnte_token', 'whatsapp_country_code',
            'mailketing_api_token', 'mailketing_from_name', 'mailketing_from_email',
            'telegram_bot_token', 'telegram_chat_id',
        ] as $key) {
            if ($request->filled($key)) {
                Setting::put($key, $request->input($key));
            }
        }

        if ($request->has('whatsapp_provider')) {
            Setting::put('whatsapp_provider', $request->input('whatsapp_provider', 'gateway'));
        }

        return back()->with('success', 'Pengaturan notifikasi THRE.F.NET tersimpan.');
    }

    /** Tombol "Test": kirim WA + Email percobaan. */
    public function test(Request $request, WhatsAppGatewayService $wa, MailketingService $mail, TelegramService $tg): RedirectResponse
    {
        $data = $request->validate([
            'channel' => ['required', 'in:whatsapp,email,telegram'],
            'target'  => ['nullable', 'string'],
        ]);

        if ($data['channel'] === 'telegram') {
            $res = $tg->send('Tes alert THRE.F.NET Billing System. Abaikan pesan ini.');
        } elseif ($data['channel'] === 'whatsapp') {
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
