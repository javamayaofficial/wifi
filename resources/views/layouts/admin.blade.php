@php
    use App\Models\Ticket;
    use App\Models\Router as RouterModel;

    $u = auth()->user();
    $customerOnly = $u?->isCustomerAccessOnly() ?? false;

    // Penanda menu aktif berdasarkan segmen URL.
    $seg = request()->path();
    $is = fn (string ...$p) => collect($p)->contains(fn ($x) => $seg === $x || str_starts_with($seg, $x . '/'));

    // Lencana pada menu — hanya dihitung untuk peran yang boleh melihatnya.
    $badgeTiket  = Ticket::whereIn('status', ['baru', 'ditugaskan', 'proses'])->count();
    $badgeRouter = RouterModel::where('is_up', false)->count();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0F1B2D">
    <title>@yield('title', 'Dashboard') · THRE.F.NET</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/threfnet.css') }}" rel="stylesheet">
</head>
<body>

<div class="app">

    {{-- ============ SIDEBAR ============ --}}
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <a href="{{ url('/dashboard') }}" class="brand-mark">
                <span class="brand-dot"></span> THRE.F.NET
            </a>
            <div class="brand-sub">Billing System</div>
        </div>

        <nav class="nav-scroll">

            <div class="nav-group">
                <div class="nav-label">Operasional</div>

                <a href="{{ url('/dashboard') }}" class="nav-item {{ $is('dashboard') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                    Dashboard
                </a>

                <a href="{{ url('/customers') }}" class="nav-item {{ $is('customers') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
                    Pelanggan
                </a>

                @unless($customerOnly)
                    <a href="{{ url('/plans') }}" class="nav-item {{ $is('plans') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        Paket
                    </a>

                    <a href="{{ url('/tickets') }}" class="nav-item {{ $is('tickets') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        Tiket
                        @if($badgeTiket) <span class="nav-count">{{ $badgeTiket }}</span> @endif
                    </a>
                @endunless
            </div>

            <div class="nav-group">
                <div class="nav-label">{{ $customerOnly ? 'Pelanggan' : 'Jaringan' }}</div>

                @unless($customerOnly)
                    <a href="{{ url('/mikrotik/monitor') }}" class="nav-item {{ $is('mikrotik/monitor') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        Monitoring
                    </a>
                @endunless

                <a href="{{ url('/map') }}" class="nav-item {{ $is('map') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Peta Pelanggan
                </a>

                @unless($customerOnly)
                    <a href="{{ url('/routers') }}" class="nav-item {{ $is('routers') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="18" x2="6.01" y2="18"/><line x1="10" y1="18" x2="10.01" y2="18"/><path d="M12 14V6"/><path d="M8.5 6a3.5 3.5 0 0 1 7 0"/></svg>
                        Router
                        @if($badgeRouter) <span class="nav-count">{{ $badgeRouter }}</span> @endif
                    </a>

                    <a href="{{ url('/inventory') }}" class="nav-item {{ $is('inventory') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                        Inventory
                    </a>

                    <a href="{{ url('/vouchers') }}" class="nav-item {{ $is('vouchers') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><line x1="13" y1="5" x2="13" y2="19" stroke-dasharray="2 3"/></svg>
                        Voucher
                    </a>
                @endunless
            </div>

            @unless($customerOnly)
                <div class="nav-group">
                    <div class="nav-label">Keuangan</div>

                    <a href="{{ url('/transactions') }}" class="nav-item {{ $is('transactions') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Transaksi
                    </a>

                    @if($u?->hasRole('owner', 'admin', 'kasir'))
                        <a href="{{ url('/reports/arrears') }}" class="nav-item {{ $is('reports/arrears') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            Tunggakan
                        </a>

                        <a href="{{ url('/reports/vouchers') }}" class="nav-item {{ $is('reports/vouchers') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>
                            Penjualan Voucher
                        </a>
                    @endif

                    @if($u?->hasRole('owner', 'admin'))
                        <a href="{{ url('/expenses') }}" class="nav-item {{ $is('expenses') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            Pengeluaran
                        </a>

                        <a href="{{ url('/reports/profit-loss') }}" class="nav-item {{ $is('reports/profit-loss') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            Laba Rugi
                        </a>

                        <a href="{{ url('/resellers') }}" class="nav-item {{ $is('resellers') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1-5h16l1 5"/><path d="M4 9v11a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V9"/><path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/></svg>
                            Mitra
                        </a>
                    @endif
                </div>
            @endunless

            @if($u?->hasRole('owner', 'admin') && ! $customerOnly)
                <div class="nav-group">
                    <div class="nav-label">Sistem</div>

                    <a href="{{ url('/settings/payment') }}" class="nav-item {{ $is('settings/payment') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M7 15h4"/></svg>
                        Pembayaran
                    </a>

                    <a href="{{ url('/settings/notification') }}" class="nav-item {{ $is('settings/notification') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        Notifikasi
                    </a>

                    <a href="{{ url('/settings/integrations') }}" class="nav-item {{ $is('settings/integrations') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/><path d="M7 13a5 5 0 0 0 10 0"/></svg>
                        Tes Integrasi
                    </a>

                    @if($u?->isOwner())
                        <a href="{{ url('/audit') }}" class="nav-item {{ $is('audit') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Audit Log
                        </a>
                    @endif
                </div>
            @endif
        </nav>

        <div class="sidebar-foot">
            <div class="avatar">{{ strtoupper(substr($u?->name ?? 'A', 0, 1)) }}</div>
            <div class="who">
                <div class="who-name text-truncate">{{ $u?->name ?? 'Admin' }}</div>
                <div class="who-role">{{ $u?->role ?? '—' }}</div>
            </div>
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button class="signout" title="Keluar" aria-label="Keluar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </form>
        </div>
    </aside>

    <div class="scrim" id="scrim" aria-hidden="true"></div>

    {{-- ============ KONTEN ============ --}}
    <div class="main">
        <header class="topbar">
            <button class="burger" id="burger" aria-label="Buka menu" aria-expanded="false" aria-controls="sidebar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>

            <h1 class="page-title">@yield('title', 'Dashboard')</h1>

            <div class="topbar-actions">
                @yield('actions')
            </div>
        </header>

        <main class="content">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="flex:0 0 auto;margin-top:2px"><polyline points="20 6 9 17 4 12"/></svg>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="flex:0 0 auto;margin-top:2px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="flex:0 0 auto;margin-top:2px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>
                        <div class="fw-semibold mb-1">Periksa kembali isian berikut</div>
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    var burger = document.getElementById('burger');
    var scrim  = document.getElementById('scrim');

    function setNav(open) {
        document.body.classList.toggle('nav-open', open);
        burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    burger.addEventListener('click', function () {
        setNav(!document.body.classList.contains('nav-open'));
    });
    scrim.addEventListener('click', function () { setNav(false); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') setNav(false);
    });
})();
</script>
</body>
</html>
