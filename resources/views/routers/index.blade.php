@extends('layouts.admin')
@section('title', 'Router')

@section('actions')
    <a href="{{ url('/routers/create') }}" class="btn btn-primary btn-sm">Tambah router</a>
@endsection

@section('content')

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr><th>Nama</th><th>Status</th><th>IP</th><th>Port API</th><th>User</th><th>TLS</th><th>Pelanggan</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
            @forelse($routers as $r)
                <tr>
                    <td>{{ $r->name }}</td>
                    <td>
                        @if($r->is_up)
                            <span class="text-nowrap"><span class="dot dot-ok"></span>Normal</span>
                        @else
                            <span class="text-nowrap" style="color:var(--down)">
                                <span class="dot dot-down dot-live"></span>Tidak terjangkau
                            </span>
                            @if($r->down_since)
                                <div class="small text-muted">sejak {{ $r->down_since->diffForHumans() }}</div>
                            @endif
                        @endif
                    </td>
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
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada router. Tambahkan MikroTik Anda, lalu uji koneksinya.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
