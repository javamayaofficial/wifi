<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0F1B2D">
    <title>Masuk · THRE.F.NET</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/threfnet.css') }}" rel="stylesheet">
    <style>
        body { background: var(--ink); display: grid; place-items: center; min-height: 100vh; padding: 24px; }
        .box { width: 100%; max-width: 380px; }
        .box .card-body { padding: 26px; }
        .lead-brand { text-align: center; margin-bottom: 20px; }
        .lead-brand .brand-mark { justify-content: center; font-size: 1.3rem; }
        .lead-brand .brand-sub { text-align: center; }
    </style>
</head>
<body>
<div class="box">
    <div class="lead-brand">
        <span class="brand-mark"><span class="brand-dot"></span> THRE.F.NET</span>
        <div class="brand-sub">Billing System</div>
    </div>

    <div class="card">
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger mb-3">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" style="flex:0 0 auto;margin-top:2px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if(session('otp_success'))
                <div class="alert alert-success mb-3">
                    <div>{{ session('otp_success') }}</div>
                </div>
            @endif

            @if(session('otp_error'))
                <div class="alert alert-danger mb-3">
                    <div>{{ session('otp_error') }}</div>
                </div>
            @endif

            <div class="mb-4">
                <div class="small text-uppercase fw-semibold mb-2" style="letter-spacing:.08em;color:#7A8CA5">Masuk dengan password</div>

                <form method="POST" action="{{ url('/login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Username Admin</label>
                        <input type="text" name="login" class="form-control" value="{{ old('login') }}" placeholder="admin" required autofocus>
                        @error('login')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="remember" value="1" class="form-check-input" id="rm">
                        <label class="form-check-label small" for="rm">Ingat saya di perangkat ini</label>
                    </div>
                    <button class="btn btn-primary w-100">Masuk</button>
                </form>
            </div>
        </div>
    </div>

    <p class="text-center small mt-3 mb-0" style="color:#7A8CA5">
        Pelanggan? <a href="{{ url('/portal/login') }}" style="color:#A9B8CC">Masuk ke portal pelanggan via OTP WhatsApp</a>
    </p>
</div>
</body>
</html>
