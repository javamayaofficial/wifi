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
                <div class="fs-4 fw-bold">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
