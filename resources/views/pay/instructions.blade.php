@extends('pay.layout')
@section('title', 'Instruksi Pembayaran')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-3">Instruksi Pembayaran</h5>

        <div class="alert alert-warning small">
            @if($transaction->payment_method === 'moota_bank_transfer')
                Transfer <b>TEPAT</b> sesuai nominal di bawah agar pembayaran bisa dicocokkan otomatis.
            @else
                Ikuti instruksi di bawah, lalu tunggu verifikasi pembayaran dari admin THRE.F.NET.
            @endif
        </div>

        <table class="table table-sm">
            <tr><td class="text-muted">Order ID</td><td class="text-end"><code>{{ $transaction->order_id }}</code></td></tr>
            <tr><td class="text-muted">Metode</td><td class="text-end fw-semibold">{{ $paymentLabel }}</td></tr>
            @if(!empty($result->instructions['bank_holder']))
                <tr><td class="text-muted">Atas Nama</td><td class="text-end">{{ $result->instructions['bank_holder'] }}</td></tr>
            @endif
            @if(!empty($result->instructions['bank_number']))
                <tr><td class="text-muted">No. Rekening</td>
                    <td class="text-end fw-bold">{{ $result->instructions['bank_number'] }}</td></tr>
            @endif
            <tr class="border-top">
                <td class="fw-semibold">Nominal Transfer</td>
                <td class="text-end fs-5 fw-bold text-primary">
                    Rp {{ number_format($transaction->amount_final, 0, ',', '.') }}
                </td>
            </tr>
        </table>

        @if(!empty($result->instructions['qris_image_url']))
            <div class="border rounded-3 p-3 text-center mb-3">
                <div class="small text-muted mb-2">Scan QRIS berikut untuk membayar tagihan Anda</div>
                <img src="{{ $result->instructions['qris_image_url'] }}" alt="QRIS THRE.F.NET"
                     class="img-fluid rounded-3 border" style="max-height: 360px;">
            </div>
        @endif

        @if(!empty($result->instructions['bank_info']))
            <div class="border rounded-3 p-3 small mb-3" style="white-space: pre-line;">{{ $result->instructions['bank_info'] }}</div>
        @endif

        @if(!empty($result->instructions['note']))
            <p class="small text-muted">{{ $result->instructions['note'] }}</p>
        @endif

        <a href="{{ url("/bayar/{$transaction->customer->username}/status/{$transaction->order_id}") }}"
           class="btn btn-outline-primary w-100">Cek Status Pembayaran</a>
    </div>
</div>
@endsection
