<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0F1B2D">
    <title>@yield('title', 'Pembayaran') · THRE.F.NET</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/threfnet.css') }}" rel="stylesheet">
    <style>
        .pay-bar { background: var(--ink); padding: 14px 0; }
        .pay-wrap { max-width: 460px; margin: 0 auto; padding: 0 18px; }
        .pay-main { max-width: 460px; margin: 0 auto; padding: 26px 18px 40px; }
    </style>
</head>
<body>

<div class="pay-bar">
    <div class="pay-wrap">
        <span class="brand-mark"><span class="brand-dot"></span> THRE.F.NET</span>
    </div>
</div>

<main class="pay-main">
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @yield('content')
    <p class="text-center text-muted small mt-4 mb-0">Butuh bantuan? Hubungi admin THRE.F.NET.</p>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
