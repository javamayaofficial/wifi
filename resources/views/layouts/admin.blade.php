<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — THRE.F.NET Billing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark" style="background:#0d6efd;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ url('/dashboard') }}">THRE.F.NET</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/customers') }}">Pelanggan</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/plans') }}">Paket</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/tickets') }}">Tiket</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Jaringan</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ url('/mikrotik/monitor') }}">Monitoring</a></li>
                        <li><a class="dropdown-item" href="{{ url('/map') }}">Peta Pelanggan</a></li>
                        <li><a class="dropdown-item" href="{{ url('/routers') }}">Router</a></li>
                        <li><a class="dropdown-item" href="{{ url('/inventory') }}">Inventory</a></li>
                        <li><a class="dropdown-item" href="{{ url('/vouchers') }}">Voucher Hotspot</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Laporan</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ url('/transactions') }}">Transaksi</a></li>
                        <li><a class="dropdown-item" href="{{ url('/reports/arrears') }}">Tunggakan</a></li>
                        <li><a class="dropdown-item" href="{{ url('/reports/vouchers') }}">Penjualan Voucher</a></li>
                        @if(auth()->user()?->hasRole('owner','admin'))
                            <li><a class="dropdown-item" href="{{ url('/expenses') }}">Pengeluaran</a></li>
                            <li><a class="dropdown-item" href="{{ url('/reports/profit-loss') }}">Laba Rugi</a></li>
                            <li><a class="dropdown-item" href="{{ url('/resellers') }}">Mitra / Reseller</a></li>
                        @endif
                        @if(auth()->user()?->isOwner())
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ url('/audit') }}">Audit Log</a></li>
                        @endif
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/settings/payment') }}">Pembayaran</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/settings/notification') }}">Notifikasi</a></li>
                <li class="nav-item">
                    <form method="POST" action="{{ url('/logout') }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-light ms-2">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul></div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
