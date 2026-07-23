@extends('layouts.admin')
@section('title', 'Laporan Tunggakan')

@section('content')
<h4 class="mb-1">Laporan Tunggakan (Aging)</h4>
<p class="text-muted small mb-4">
    Pelanggan yang sudah lewat jatuh tempo dan belum membayar, dikelompokkan
    berdasarkan lama tunggakan. Semakin lama, semakin kecil peluang tertagih.
</p>

<div class="row g-3 mb-4">
    @foreach($buckets as $label => $b)
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0"><div class="card-body">
                <div class="text-muted small">{{ $label }}</div>
                <div class="fs-4 fw-bold">{{ $b['items']->count() }}</div>
                <div class="small text-muted">Rp {{ number_format($b['total'], 0, ',', '.') }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="alert alert-secondary d-flex justify-content-between align-items-center">
    <span>Total potensi piutang</span>
    <b class="fs-5">Rp {{ number_format($grandTotal, 0, ',', '.') }}</b>
</div>

@foreach($buckets as $label => $b)
    @if($b['items']->count())
        <h6 class="fw-bold mt-4 mb-2">{{ $label }} ({{ $b['items']->count() }})</h6>
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr><th>Nama</th><th>Username</th><th>Paket</th><th>Jatuh Tempo</th><th>Telat</th><th>Nominal</th><th>Status</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody>
                    @foreach($b['items'] as $c)
                        <tr>
                            <td>{{ $c->name }}</td>
                            <td><code class="small">{{ $c->username }}</code></td>
                            <td class="small">{{ $c->plan?->name }}</td>
                            <td class="small">{{ $c->expired_date->format('d/m/Y') }}</td>
                            <td><span class="badge bg-danger">{{ $c->overdue_days }} hari</span></td>
                            <td>Rp {{ number_format($c->plan?->price ?? 0, 0, ',', '.') }}</td>
                            <td><span class="badge bg-{{ $c->status === 'isolated' ? 'danger' : 'secondary' }}">{{ $c->status }}</span></td>
                            <td class="text-end">
                                @if($c->phone)
                                    <a class="btn btn-sm btn-outline-success"
                                       target="_blank"
                                       href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $c->phone)) }}">
                                        WhatsApp
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endforeach

@if($grandTotal == 0)
    <div class="text-center text-muted py-5">Tidak ada tunggakan. 🎉</div>
@endif
@endsection
