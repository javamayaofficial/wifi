@extends('layouts.admin')
@section('title', 'Paket')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Paket Internet THRE.F.NET</h4>
    <a href="{{ url('/plans/create') }}" class="btn btn-primary btn-sm">+ Tambah Paket</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr><th>Nama</th><th>Harga</th><th>Bandwidth</th><th>Durasi</th><th>Profil MikroTik</th><th>Pelanggan</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
            @forelse($plans as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                    <td>{{ $p->bandwidth }}</td>
                    <td>{{ $p->duration_days }} hari</td>
                    <td><code>{{ $p->mikrotik_profile }}</code></td>
                    <td>{{ $p->customers_count }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ url("/plans/{$p->id}/edit") }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form method="POST" action="{{ url("/plans/{$p->id}") }}" class="d-inline" onsubmit="return confirm('Hapus paket ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada paket.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
