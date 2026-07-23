@php $c = $portalCustomer ?? null; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0F1B2D">
    <title>@yield('title', 'Portal') · THRE.F.NET</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/threfnet.css') }}" rel="stylesheet">
    <style>
        .pub-bar { background: var(--ink); padding: 14px 0; }
        .pub-wrap { max-width: 880px; margin: 0 auto; padding: 0 18px; }
        .pub-nav a { color: #A9B8CC; font-size: .85rem; font-weight: 500; text-decoration: none; }
        .pub-nav a:hover, .pub-nav a.on { color: #fff; text-decoration: none; }
        .pub-main { max-width: 880px; margin: 0 auto; padding: 24px 18px 40px; }
    </style>
</head>
<body>

<div class="pub-bar">
    <div class="pub-wrap d-flex align-items-center gap-3 flex-wrap">
        <a href="{{ url($c ? '/portal' : '/portal/login') }}" class="brand-mark">
            <span class="brand-dot"></span> THRE.F.NET
        </a>

        @if($c)
            <nav class="pub-nav d-flex align-items-center gap-3 ms-auto">
                <a href="{{ url('/portal') }}" class="{{ request()->is('portal') ? 'on' : '' }}">Beranda</a>
                <a href="{{ url('/portal/invoices') }}" class="{{ request()->is('portal/invoices') ? 'on' : '' }}">Tagihan</a>
                <a href="{{ url('/portal/tickets') }}" class="{{ request()->is('portal/tickets') ? 'on' : '' }}">Laporan</a>
                <form method="POST" action="{{ url('/portal/logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary">Keluar</button>
                </form>
            </nav>
        @endif
    </div>
</div>

<main class="pub-main">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger">
            <div><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        </div>
    @endif

    @yield('content')

    <p class="text-center text-muted small mt-4 mb-0">THRE.F.NET Billing System</p>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
