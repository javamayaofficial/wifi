@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<h4 class="mb-4">Dashboard THRE.F.NET</h4>

<div class="row g-3">
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Total Pelanggan</div>
                <div class="fs-3 fw-bold">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Aktif</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($stats['active']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Nonaktif / Isolir</div>
                <div class="fs-3 fw-bold text-danger">{{ number_format($stats['inactive']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Pendapatan Bulan Ini</div>
                <div class="fs-4 fw-bold">
                    Rp {{ number_format($stats['revenue'] + $stats['voucher_revenue'], 0, ',', '.') }}
                </div>
                <div class="small text-muted">
                    Billing Rp {{ number_format($stats['revenue'], 0, ',', '.') }}
                    · Voucher Rp {{ number_format($stats['voucher_revenue'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-6 col-lg-4">
        <a href="{{ url('/tickets') }}" class="text-decoration-none">
            <div class="card shadow-sm border-0"><div class="card-body">
                <div class="text-muted small">Tiket Terbuka</div>
                <div class="fs-3 fw-bold text-warning">{{ number_format($stats['tickets_open']) }}</div>
            </div></div>
        </a>
    </div>
    <div class="col-6 col-lg-4">
        <a href="{{ url('/reports/arrears') }}" class="text-decoration-none">
            <div class="card shadow-sm border-0"><div class="card-body">
                <div class="text-muted small">Pelanggan Menunggak</div>
                <div class="fs-3 fw-bold text-danger">{{ number_format($stats['arrears_count']) }}</div>
            </div></div>
        </a>
    </div>
    <div class="col-6 col-lg-4">
        <a href="{{ url('/routers') }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 {{ $stats['routers_down'] > 0 ? 'border-danger border-2' : '' }}">
                <div class="card-body">
                    <div class="text-muted small">Router Bermasalah</div>
                    <div class="fs-3 fw-bold {{ $stats['routers_down'] > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($stats['routers_down']) }}
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
