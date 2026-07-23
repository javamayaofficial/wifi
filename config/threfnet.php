<?php

return [
    'doku' => [
        'client_id'   => env('DOKU_CLIENT_ID'),
        'secret_key'  => env('DOKU_SECRET_KEY'),
        'environment' => env('DOKU_ENVIRONMENT', 'sandbox'),
        'base_url'    => [
            'sandbox'    => 'https://api-sandbox.doku.com',
            'production' => 'https://api.doku.com',
        ],
    ],

    'moota' => [
        'secret_token'    => env('MOOTA_SECRET_TOKEN'),
        'bank_account_id' => env('MOOTA_BANK_ACCOUNT_ID'),
        'bank_number'     => env('MOOTA_BANK_NUMBER', ''),
        'bank_holder'     => env('MOOTA_BANK_HOLDER', 'THRE.F.NET'),
    ],

    'mailketing' => [
        'api_token'  => env('MAILKETING_API_TOKEN'),
        'from_name'  => env('MAILKETING_FROM_NAME', 'THRE.F.NET'),
        'from_email' => env('MAILKETING_FROM_EMAIL', 'threfnet@javamaya.id'),
        'sandbox'    => env('MAILKETING_SANDBOX', true),
        'endpoint'   => 'https://api.mailketing.co.id/api/v1/send',
    ],

    'whatsapp' => [
        'provider'    => env('WHATSAPP_PROVIDER', 'gateway'),
        'gateway_url' => env('WHATSAPP_GATEWAY_URL'),
        'api_key'     => env('WHATSAPP_API_KEY'),
        'fonnte_url'  => env('FONNTE_URL', 'https://api.fonnte.com/send'),
        'fonnte_token'=> env('FONNTE_TOKEN'),
        'country_code'=> env('WHATSAPP_COUNTRY_CODE', '62'),
    ],

    'auth_otp' => [
        'ttl_minutes'         => (int) env('AUTH_OTP_TTL_MINUTES', 5),
        'resend_cooldown'     => (int) env('AUTH_OTP_RESEND_COOLDOWN', 60),
        'max_verify_attempts' => (int) env('AUTH_OTP_MAX_VERIFY_ATTEMPTS', 5),
    ],

    'reminders' => [
        'h7_daily_template' => env(
            'REMINDER_H7_DAILY_TEMPLATE',
            "[THRE.F.NET - Pengingat Masa Aktif]\n"
            . "Halo {customer_name}, masa aktif layanan internet Anda ({plan_name}) akan berakhir pada {expired_date}.\n"
            . "Sisa masa aktif: {days_left} hari.\n"
            . "Tagihan: {amount}\n"
            . "Silakan lakukan pembayaran melalui: {payment_link}\n"
            . "Terima kasih."
        ),
    ],

    'mikrotik' => [
        'default_ip'   => env('MIKROTIK_IP', '192.168.1.1'),
        'default_port' => (int) env('MIKROTIK_PORT', 8728),
        'default_user' => env('MIKROTIK_USER', 'admin'),
        'default_pass' => env('MIKROTIK_PASSWORD', ''),
        'isolir_profile' => env('MIKROTIK_ISOLIR_PROFILE', 'isolir'),
    ],
];
