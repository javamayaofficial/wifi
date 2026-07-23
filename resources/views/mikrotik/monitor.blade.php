@extends('layouts.admin')
@section('title', 'Monitoring')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Monitoring MikroTik</h4>
    @if($router)
        <form method="POST" action="{{ url('/mikrotik/refresh') }}">
            @csrf
            <input type="hidden" name="router" value="{{ $router->id }}">
            <button class="btn btn-sm btn-outline-primary">Refresh</button>
        </form>
    @endif
</div>

<form method="GET" class="mb-3">
    <select name="router" class="form-select form-select-sm" style="max-width:320px" onchange="this.form.submit()">
        @foreach($routers as $r)
            <option value="{{ $r->id }}" @selected($router?->id === $r->id)>{{ $r->name }} ({{ $r->ip }})</option>
        @endforeach
    </select>
</form>

@if($error)
    <div class="alert alert-danger">{{ $error }}</div>
@endif

@if($resource)
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0"><div class="card-body">
                <div class="text-muted small">Online</div>
                <div class="fs-3 fw-bold text-success">{{ count($active) }}</div>
                <div class="small text-muted">dari {{ $totalCustomers }} pelanggan</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0"><div class="card-body">
                <div class="text-muted small">CPU Load</div>
                <div class="fs-3 fw-bold">{{ $resource['cpu-load'] ?? '-' }}%</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0"><div class="card-body">
                <div class="text-muted small">RAM Bebas</div>
                <div class="fs-5 fw-bold">
                    {{ isset($resource['free-memory']) ? round($resource['free-memory'] / 1048576, 1) . ' MB' : '-' }}
                </div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0"><div class="card-body">
                <div class="text-muted small">Uptime</div>
                <div class="fs-5 fw-bold">{{ $resource['uptime'] ?? '-' }}</div>
                <div class="small text-muted">RouterOS {{ $resource['version'] ?? '' }}</div>
            </div></div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-semibold">Sesi PPPoE Aktif</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr><th>Username</th><th>Nama</th><th>IP</th><th>Uptime</th><th>MAC</th><th class="text-end">Aksi</th></tr>
                </thead>
                <tbody>
                @forelse($active as $s)
                    @php $c = $customers[$s['name'] ?? ''] ?? null; @endphp
                    <tr>
                        <td><code>{{ $s['name'] ?? '-' }}</code></td>
                        <td>
                            {{ $c?->name ?? '—' }}
                            @unless($c)
                                <span class="badge bg-warning text-dark ms-1">tidak ada di dashboard</span>
                            @endunless
                        </td>
                        <td>{{ $s['address'] ?? '-' }}</td>
                        <td>{{ $s['uptime'] ?? '-' }}</td>
                        <td class="small text-muted">{{ $s['caller-id'] ?? '-' }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ url('/mikrotik/disconnect') }}" class="d-inline"
                                  onsubmit="return confirm('Putuskan sesi {{ $s['name'] ?? '' }}?')">
                                @csrf
                                <input type="hidden" name="router_id" value="{{ $router->id }}">
                                <input type="hidden" name="username" value="{{ $s['name'] ?? '' }}">
                                <button class="btn btn-sm btn-outline-danger">Putus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada sesi aktif.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="small text-muted mt-2">
        Data di-cache 30 detik agar router tidak terbebani. Klik Refresh untuk data terbaru.
    </p>
@endif
@endsection
