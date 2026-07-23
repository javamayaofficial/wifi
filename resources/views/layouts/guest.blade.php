<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0F1B2D">

        <title>{{ config('app.name', 'THRE.F.NET') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="{{ asset('css/threfnet.css') }}" rel="stylesheet">

        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        <style>
            body { background: var(--ink); min-height: 100vh; }
            .auth-shell { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
            .auth-card { width: 100%; max-width: 430px; }
            .auth-card .card-body { padding: 26px; }
            .auth-brand { text-align: center; margin-bottom: 20px; }
            .auth-brand .brand-mark { justify-content: center; font-size: 1.3rem; }
            .auth-brand .brand-sub { text-align: center; color: #7A8CA5; }
        </style>
    </head>
    <body>
        <div class="auth-shell">
            <div class="auth-card">
                <div class="auth-brand">
                    <a href="{{ url('/signin') }}" class="text-decoration-none">
                        <span class="brand-mark"><span class="brand-dot"></span> THRE.F.NET</span>
                    </a>
                    <div class="brand-sub">Billing System</div>
                </div>

                <div class="card">
                    <div class="card-body">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
