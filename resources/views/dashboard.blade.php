@extends('layouts.admin')
@section('title', 'Dashboard')

@section('actions')
    <a href="{{ url('/customers/create') }}" class="btn btn-primary btn-sm">Tambah pelanggan</a>
@endsection

@section('content')

{{-- ============ STRIP TRIASE — apa yang perlu ditangani sekarang ============ --}}
<div class="triage {{ count($triage) ? 'is-alert' : 'is-clear' }}">
    <div class="triage-head">
        @if(count($triage))
            <span class="dot dot-down dot-live" style="color:var(--down)"></span> Perlu perhatian
        @else
            <span class="dot dot-ok"></span> Semua aman
        @endif
    </div>

    @if(count($triage))
        <div class="triage-list">
            @foreach($triage as $t)
                <a href="{{ url($t['url']) }}" class="triage-item">
                    <span class="triage-num" style="color: var(--{{ $t['tone'] }})">{{ $t['count'] }}</span>
                    <span class="triage-txt">
                        <span class="triage-title d-block">{{ $t['title'] }}</span>
                        <span class="triage-sub">{{ $t['sub'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    @else
        <div class="triage-clear">
            Tidak ada router yang turun, tiket mendesak, atau tunggakan.
            @if($jatuhTempo > 0)
                <a href="{{ url('/customers') }}">{{ $jatuhTempo }} pelanggan</a> jatuh tempo dalam 3 hari ke depan.
            @endif
        </div>
    @endif
</div>

{{-- ============ METRIK ============ --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <a href="{{ url('/customers') }}" class="metric">
            <div class="card h-100"><div class="card-body">
                <div class="metric-label">Pelanggan aktif</div>
                <div class="metric-value">{{ number_format($stats['active']) }}</div>
                <div class="metric-note">dari {{ number_format($stats['total']) }} terdaftar</div>
            </div></div>
        </a>
    </div>

    <div class="col-6 col-xl-3">
        <a href="{{ url('/customers?status=isolated') }}" class="metric">
            <div class="card h-100"><div class="card-body">
                <div class="metric-label">Tidak aktif</div>
                <div class="metric-value" style="color:var(--down)">{{ number_format($stats['inactive']) }}</div>
                <div class="metric-note">isolir, ditangguhkan, atau baru</div>
            </div></div>
        </a>
    </div>

    <div class="col-6 col-xl-3">
        <a href="{{ url('/transactions') }}" class="metric">
            <div class="card h-100"><div class="card-body">
                <div class="metric-label">Pemasukan bulan ini</div>
                <div class="metric-value is-money">
                    Rp {{ number_format($stats['revenue'] + $stats['voucher_revenue'], 0, ',', '.') }}
                </div>
                <div class="metric-note">
                    Billing {{ number_format($stats['revenue'] / 1000, 0, ',', '.') }}rb
                    · Voucher {{ number_format($stats['voucher_revenue'] / 1000, 0, ',', '.') }}rb
                </div>
            </div></div>
        </a>
    </div>

    <div class="col-6 col-xl-3">
        <a href="{{ url('/mikrotik/monitor') }}" class="metric">
            <div class="card h-100"><div class="card-body">
                <div class="metric-label">Router</div>
                <div class="metric-value" style="color: {{ $stats['routers_down'] ? 'var(--down)' : 'var(--ok)' }}">
                    {{ $stats['routers_down'] ? $stats['routers_down'] . ' turun' : 'Normal' }}
                </div>
                <div class="metric-note">{{ $stats['tickets_open'] }} tiket masih terbuka</div>
            </div></div>
        </a>
    </div>
</div>

{{-- ============ PANEL BAWAH ============ --}}
<div class="row g-3">

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                Pembayaran masuk terakhir
                <a href="{{ url('/transactions') }}" class="btn btn-sm btn-outline-secondary">Semua</a>
            </div>

            @forelse($lastPayments as $p)
                <div class="d-flex align-items-center gap-3 px-3 py-2"
                     style="border-top:1px solid var(--line-soft)">
                    <span class="dot dot-ok"></span>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate" style="font-size:.87rem">
                            {{ $p->customer?->name ?? '—' }}
                        </div>
                        <div class="small text-muted">
                            {{ strtoupper($p->payment_method) }} · {{ $p->paid_at?->diffForHumans() }}
                        </div>
                    </div>
                    <div class="fw-bold text-nowrap" style="font-variant-numeric:tabular-nums">
                        Rp {{ number_format($p->amount, 0, ',', '.') }}
                    </div>
                </div>
            @empty
                <div class="empty">
                    <div class="empty-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    </div>
                    <div class="empty-title">Belum ada pembayaran masuk</div>
                    <div class="empty-text">Pembayaran yang terverifikasi akan muncul di sini secara otomatis.</div>
                </div>
            @endforelse
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                Tiket yang sedang berjalan
                <a href="{{ url('/tickets') }}" class="btn btn-sm btn-outline-secondary">Semua</a>
            </div>

            @forelse($recentTickets as $t)
                <a href="{{ url("/tickets/{$t->id}") }}"
                   class="d-flex align-items-center gap-3 px-3 py-2 text-decoration-none text-body"
                   style="border-top:1px solid var(--line-soft)">
                    <span class="dot {{ in_array($t->priority, ['darurat','tinggi']) ? 'dot-down' : 'dot-warn' }}"></span>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate" style="font-size:.87rem">{{ $t->title }}</div>
                        <div class="small text-muted text-truncate">
                            {{ $t->customer?->name }} · {{ $t->assignee?->name ?? 'belum ditugaskan' }}
                        </div>
                    </div>
                    <span class="badge bg-{{ $t->status === 'baru' ? 'danger' : 'primary' }}">{{ $t->status }}</span>
                </a>
            @empty
                <div class="empty">
                    <div class="empty-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div class="empty-title">Tidak ada gangguan berjalan</div>
                    <div class="empty-text">Semua laporan pelanggan sudah tertangani.</div>
                    <a href="{{ url('/tickets/create') }}" class="btn btn-sm btn-outline-secondary">Buat tiket</a>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
