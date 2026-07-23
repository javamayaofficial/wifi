@extends('portal.layout')
@section('title', 'Masuk')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h5 class="mb-1">Portal Pelanggan</h5>
                <p class="text-muted small mb-4">Masuk untuk melihat tagihan dan melaporkan gangguan.</p>

                <form method="POST" action="{{ url('/portal/login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Masuk</button>
                </form>

                <p class="small text-muted mt-3 mb-0">
                    Belum punya password? Hubungi admin THRE.F.NET untuk mendapatkannya.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
