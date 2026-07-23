@extends('layouts.admin')
@section('title', 'Import dari Router')

@section('content')
<p class="text-muted small">
    Menarik PPP secret yang sudah ada di router menjadi Pelanggan THRE.F.NET.
    Username yang <b>sudah terdaftar</b> di dashboard akan dilewati — data billing
    yang ada tidak akan tertimpa.
</p>

@if($error)<div class="alert alert-danger">{{ $error }}</div>@endif

<form method="GET" class="row g-2 mb-4">
    <div class="col-auto">
        <select name="router" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">— Pilih Router —</option>
            @foreach($routers as $r)
                <option value="{{ $r->id }}" @selected($router?->id === $r->id)>{{ $r->name }} ({{ $r->ip }})</option>
            @endforeach
        </select>
    </div>
</form>

@if($preview !== null)
    @php
        $baru = collect($preview)->where('exists', false)->count();
        $ada  = collect($preview)->where('exists', true)->count();
    @endphp

    <div class="alert alert-info">
        Ditemukan <b>{{ count($preview) }}</b> secret:
        <b>{{ $baru }}</b> akan diimport, <b>{{ $ada }}</b> sudah ada (dilewati).
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="table-responsive" style="max-height:420px">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light sticky-top">
                <tr><th>Username</th><th>Profile</th><th>Paket Cocok</th><th>Password</th><th>Status</th></tr>
                </thead>
                <tbody>
                @foreach($preview as $row)
                    <tr class="{{ $row['exists'] ? 'text-muted' : '' }}">
                        <td><code>{{ $row['username'] }}</code></td>
                        <td>{{ $row['profile'] }}</td>
                        <td>
                            @if($row['plan_id'])
                                <span class="badge bg-success-subtle text-success-emphasis">cocok otomatis</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis">pakai paket default</span>
                            @endif
                        </td>
                        <td>
                            @if($row['has_password'])
                                <span class="text-success small">terbaca</span>
                            @else
                                <span class="text-warning small">dibuatkan baru</span>
                            @endif
                        </td>
                        <td>
                            @if($row['exists'])
                                <span class="badge bg-secondary">sudah ada</span>
                            @else
                                <span class="badge bg-primary">akan diimport</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($baru > 0)
        <form method="POST" action="{{ url('/mikrotik/import') }}"
              onsubmit="return confirm('Import {{ $baru }} pelanggan baru dari router ini?')">
            @csrf
            <input type="hidden" name="router_id" value="{{ $router->id }}">
            <div class="card shadow-sm border-0 mb-3" style="max-width:520px">
                <div class="card-body row g-3">
                    <div class="col-md-7">
                        <label class="form-label">Paket default</label>
                        <select name="plan_id" class="form-select" required>
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Dipakai bila profile tidak cocok dengan paket mana pun.</div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Masa aktif awal (hari)</label>
                        <input type="number" name="days" class="form-control" value="30" min="1" required>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary">Import {{ $baru }} Pelanggan</button>
        </form>
    @endif
@endif
@endsection
