<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Pembayaran') — THRE.F.NET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar" style="background:#0d6efd;">
    <div class="container">
        <span class="navbar-brand text-white fw-bold mb-0">THRE.F.NET</span>
    </div>
</nav>

<div class="container py-4" style="max-width:560px">
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @yield('content')
    <p class="text-center text-muted small mt-4 mb-0">THRE.F.NET Billing System</p>
</div>
</body>
</html>
