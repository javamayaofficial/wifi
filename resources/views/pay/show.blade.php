@extends('pay.layout')
@section('title', 'Tagihan')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-1">Halo, {{ $customer->name }}</h5>
        <div class="text-muted small mb-3">{{ $customer->username }}</div>

        <table class="table table-sm mb-3">
            <tr><td class="text-muted">Paket</td><td class="text-end fw-semibold">{{ $customer->plan->name }}</td></tr>
            <tr><td class="text-muted">Bandwidth</td><td class="text-end">{{ $customer->plan->bandwidth }}</td></tr>
            <tr><td class="text-muted">Masa aktif s/d</td><td class="text-end">{{ $customer->expired_date->format('d/m/Y') }}</td></tr>
            <tr><td class="text-muted">Status</td>
                <td class="text-end">
                    <span class="badge bg-{{ $customer->status === 'active' ? 'success' : 'danger' }}">{{ $customer->status }}</span>
                </td>
            </tr>
            <tr class="border-top">
                <td class="fw-semibold">Total Tagihan</td>
                <td class="text-end fs-5 fw-bold">Rp {{ number_format($customer->plan->price, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="small text-muted mb-3">
            Metode pembayaran: {{ implode(', ', array_map('strtoupper', $methods)) }}
        </div>

        <form method="POST" action="{{ url("/bayar/{$customer->username}") }}">
            @csrf
            <button class="btn btn-primary w-100">Bayar Sekarang</button>
        </form>

        @if($pending)
            <a href="{{ url("/bayar/{$customer->username}/status/{$pending->order_id}") }}"
               class="btn btn-link w-100 mt-2">Cek status pembayaran terakhir</a>
        @endif
    </div>
</div>
@endsection
