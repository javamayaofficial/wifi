@extends('layouts.admin')
@section('title', 'Pelanggan')

@section('actions')
    <a href="{{ url('/mikrotik/import') }}" class="btn btn-outline-secondary btn-sm">Import dari router</a>
    <a href="{{ url('/customers/import') }}" class="btn btn-outline-secondary btn-sm">Import Excel</a>
    <a href="{{ url('/customers/create') }}" class="btn btn-primary btn-sm">Tambah pelanggan</a>
@endsection

@section('content')

@if(session('import_errors'))
    <div class="alert alert-warning">
        <div class="fw-semibold mb-1">Baris yang gagal diimport:</div>
        <ul class="mb-0 small">
            @foreach(session('import_errors') as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

@if(session('import_detected_columns'))
    <div class="alert alert-info">
        <div class="fw-semibold mb-1">Pemetaan kolom yang terbaca otomatis:</div>
        <div class="small">{{ implode(' • ', session('import_detected_columns')) }}</div>
    </div>
@endif

<form method="GET" class="row g-2 mb-3 page-filters">
    <div class="col-12 col-md">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari nama / username">
    </div>
    <div class="col-12 col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">Semua status</option>
            @foreach(['new','active','isolated','suspended'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-auto"><button class="btn btn-sm btn-outline-primary">Filter</button></div>
</form>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 mobile-card-table">
            <thead class="table-light">
            <tr>
                <th>Nama</th><th>Username</th><th>Paket</th><th>Router</th>
                <th>Expired</th><th>Status</th><th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($customers as $c)
                <tr>
                    <td data-label="Nama">{{ $c->name }}</td>
                    <td data-label="Username"><code>{{ $c->username }}</code></td>
                    <td data-label="Paket">{{ $c->plan?->name }}</td>
                    <td data-label="Router">{{ $c->router?->name }}</td>
                    <td data-label="Expired">{{ $c->expired_date?->format('d/m/Y') }}</td>
                    <td data-label="Status">
                        @php $d = ['active'=>'ok','isolated'=>'down','suspended'=>'warn','new'=>'idle'][$c->status] ?? 'idle'; @endphp
                        <span class="text-nowrap"><span class="dot dot-{{ $d }}"></span>{{ $c->status }}</span>
                        @if($c->sync_error)
                            <div class="small text-danger" title="{{ $c->sync_error }}">sync gagal</div>
                        @elseif(! $c->synced_at)
                            <div class="small text-muted">belum tersinkron</div>
                        @endif
                    </td>
                    <td data-label="Aksi" class="text-end text-nowrap customer-actions">
                        <a href="{{ url("/customers/{$c->id}/edit") }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form method="POST" action="{{ url("/customers/{$c->id}/toggle") }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-{{ $c->status === 'active' ? 'danger' : 'success' }}">
                                {{ $c->status === 'active' ? 'Isolir' : 'Aktifkan' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ url("/customers/{$c->id}/portal-password") }}" class="d-inline"
                              onsubmit="return confirm('Kirim panduan akses portal via OTP WhatsApp untuk pelanggan ini?')">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary" title="Kirim akses portal via OTP WhatsApp">Portal</button>
                        </form>
                        <form method="POST" action="{{ url("/customers/{$c->id}") }}" class="d-inline"
                              onsubmit="return confirm('Hapus pelanggan ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pelanggan. Tambahkan satu per satu, atau tarik langsung dari router.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $customers->links() }}</div>
@endsection
