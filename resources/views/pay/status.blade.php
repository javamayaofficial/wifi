@extends('pay.layout')
@section('title', 'Status Pembayaran')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body text-center">
        @if($transaction->status === 'paid')
            <div class="display-6 text-success mb-2">&check;</div>
            <h5 class="mb-1">Pembayaran Diterima</h5>
            <p class="text-muted small">
                Layanan internet Anda aktif hingga
                <b>{{ $transaction->customer->expired_date->format('d/m/Y') }}</b>.
            </p>
        @else
            <div class="display-6 text-warning mb-2">&hellip;</div>
            <h5 class="mb-1">Menunggu Pembayaran</h5>
            <p class="text-muted small">
                Status akan diperbarui otomatis setelah pembayaran Anda terverifikasi.
            </p>
        @endif

        <table class="table table-sm text-start mt-3">
            <tr><td class="text-muted">Order ID</td><td class="text-end"><code>{{ $transaction->order_id }}</code></td></tr>
            <tr><td class="text-muted">Nominal</td><td class="text-end">Rp {{ number_format($transaction->amount_final, 0, ',', '.') }}</td></tr>
            <tr><td class="text-muted">Metode</td><td class="text-end">{{ $paymentLabel }}</td></tr>
            <tr><td class="text-muted">Status</td><td class="text-end">{{ $transaction->status }}</td></tr>
        </table>

        <a href="{{ url("/bayar/{$transaction->customer->username}") }}" class="btn btn-link">Kembali ke tagihan</a>
    </div>
</div>
@endsection
