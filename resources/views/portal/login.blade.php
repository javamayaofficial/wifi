@extends('portal.layout')
@section('title', 'Masuk')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h5 class="mb-1">Portal Pelanggan</h5>
                <p class="text-muted small mb-4">Masuk dengan OTP WhatsApp untuk melihat tagihan dan melaporkan gangguan.</p>

                @if(session('success'))
                    <div class="alert alert-success mb-3">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger mb-3">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ url('/portal/login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nomor WhatsApp</label>
                        <input type="text" name="otp_phone" class="form-control" value="{{ old('otp_phone') }}" placeholder="08xxxxxxxxxx" required autofocus>
                        @error('otp_phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">Kode OTP</label>
                            <button class="btn btn-link btn-sm p-0 text-decoration-none" formaction="{{ url('/portal/login/request-otp') }}">Kirim OTP</button>
                        </div>
                        <input type="text" name="otp_code" class="form-control" inputmode="numeric" maxlength="6" placeholder="6 digit OTP" required>
                        @error('otp_code')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <button class="btn btn-primary w-100">Masuk ke Portal</button>
                </form>

                <p class="small text-muted mt-3 mb-0">
                    Pastikan nomor WhatsApp pelanggan sudah terdaftar di sistem THRE.F.NET.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
