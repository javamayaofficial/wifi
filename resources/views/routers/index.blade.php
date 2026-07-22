@extends('layouts.admin')
@section('title', 'Router')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Router MikroTik</h4>
    <a href="{{ url('/routers/create') }}" class="btn btn-primary btn-sm">+ Tambah Router</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr><th>Nama</th><th>IP</th><th>Port API</th><th>User</th><th>TLS</th><th>Pelanggan</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
            @forelse($routers as $r)
                <tr>
                    <td>{{ $r->name }}</td>
                    <td><code>{{ $r->ip }}</code></td>
                    <td>{{ $r->api_port }}</td>
                    <td>{{ $r->username }}</td>
                    <td>{{ $r->use_tls ? 'Ya' : 'Tidak' }}</td>
                    <td>{{ $r->customers_count }}</td>
                    <td class="text-end text-nowrap">
                        <form method="POST" action="{{ url("/routers/{$r->id}/test") }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary">Test Koneksi</button>
                        </form>
                        <a href="{{ url("/routers/{$r->id}/edit") }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form method="POST" action="{{ url("/routers/{$r->id}") }}" class="d-inline" onsubmit="return confirm('Hapus router ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada router.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
