<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Portal') — THRE.F.NET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark" style="background:#0d6efd;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ url('/portal') }}">THRE.F.NET</a>
        @isset($portalCustomer)
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="{{ url('/portal') }}">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/portal/invoices') }}">Tagihan</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/portal/tickets') }}">Laporan</a></li>
                    <li class="nav-item">
                        <form method="POST" action="{{ url('/portal/logout') }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-light ms-lg-2">Keluar</button>
                        </form>
                    </li>
                </ul>
            </div>
        @endisset
    </div>
</nav>

<div class="container py-4" style="max-width:900px">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
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
