@extends('portal.layout')
@section('title', 'Beranda')

@section('content')
<h5 class="mb-3">Halo, {{ $customer->name }}</h5>

@unless($profileComplete)
    <div class="alert alert-warning border-0 shadow-sm">
        <div class="fw-semibold mb-1">Lengkapi profil pelanggan dulu</div>
        <div class="small mb-3">
            Mohon isi nama, alamat, nomor KTP, unggah foto KTP, dan titik lokasi pemasangan. Data ini akan dipakai sebagai acuan peta titik pelanggan yang sudah terpasang.
        </div>
        <a href="{{ url('/portal/profile') }}" class="btn btn-sm btn-dark">Lengkapi Profil</a>
    </div>
@endunless

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small">Status Layanan</div>
                <div class="fs-4 fw-bold text-{{ $customer->status === 'active' ? 'success' : 'danger' }}">
                    {{ $customer->status === 'active' ? 'Aktif' : 'Terisolir' }}
                </div>
                <div class="small text-muted mt-2">
                    Paket <b>{{ $customer->plan->name }}</b> ({{ $customer->plan->bandwidth }})<br>
                    Berlaku s/d <b>{{ $customer->expired_date->format('d/m/Y') }}</b>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex flex-column">
                <div class="text-muted small">Tagihan Bulan Ini</div>
                <div class="fs-4 fw-bold">Rp {{ number_format($customer->plan->price, 0, ',', '.') }}</div>
                <a href="{{ url('/bayar/' . $customer->username) }}" class="btn btn-primary btn-sm mt-auto">
                    Bayar Sekarang
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold">Pembayaran Terakhir</div>
            <ul class="list-group list-group-flush">
                @forelse($transactions as $t)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="small">
                            <code>{{ $t->order_id }}</code><br>
                            <span class="text-muted">{{ $t->created_at->format('d/m/Y') }}</span>
                        </div>
                        <span class="badge bg-{{ $t->status === 'paid' ? 'success' : 'warning text-dark' }}">
                            {{ $t->status }}
                        </span>
                    </li>
                @empty
                    <li class="list-group-item text-muted small">Belum ada transaksi. Tagihan muncul setelah pelanggan memulai pembayaran.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold">Laporan Gangguan Terakhir</div>
            <ul class="list-group list-group-flush">
                @forelse($tickets as $t)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="small">
                            {{ $t->title }}<br>
                            <span class="text-muted">{{ $t->created_at->format('d/m/Y') }}</span>
                        </div>
                        <span class="badge bg-{{ $t->status === 'selesai' ? 'success' : 'primary' }}">{{ $t->status }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted small">Belum ada laporan.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
