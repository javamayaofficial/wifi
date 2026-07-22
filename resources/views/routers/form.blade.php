@extends('layouts.admin')
@section('title', $router->exists ? 'Edit Router' : 'Tambah Router')

@section('content')
<h4 class="mb-4">{{ $router->exists ? 'Edit' : 'Tambah' }} Router MikroTik</h4>

<form method="POST" action="{{ $router->exists ? url("/routers/{$router->id}") : url('/routers') }}">
    @csrf
    @if($router->exists) @method('PUT') @endif

    <div class="card shadow-sm border-0" style="max-width:600px">
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama Router</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $router->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">IP Address</label>
                <input type="text" name="ip" class="form-control" value="{{ old('ip', $router->ip) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Port API</label>
                <input type="number" name="api_port" class="form-control" value="{{ old('api_port', $router->api_port) }}" required>
                <div class="form-text">8728 (non-TLS) atau 8729 (TLS).</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="{{ old('username', $router->username) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="{{ $router->exists ? 'Kosongkan bila tidak diubah' : '' }}"
                       {{ $router->exists ? '' : 'required' }}>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check">
                    <input type="hidden" name="use_tls" value="0">
                    <input type="checkbox" name="use_tls" value="1" class="form-check-input" id="tls" @checked(old('use_tls', $router->use_tls))>
                    <label class="form-check-label" for="tls">Gunakan TLS</label>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ url('/routers') }}" class="btn btn-link">Batal</a>
    </div>
</form>
@endsection
