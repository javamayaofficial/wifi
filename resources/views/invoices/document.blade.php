<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #1f2937; margin: 0; }
        .header { background: #0d6efd; color: #fff; padding: 18px 28px; }
        .brand { font-size: 22px; font-weight: bold; letter-spacing: .5px; }
        .wrap { padding: 24px 28px; }
        .row { width: 100%; }
        .col { display: inline-block; vertical-align: top; width: 48%; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.items th { background: #f1f5f9; text-align: left; padding: 8px; border-bottom: 2px solid #e2e8f0; }
        table.items td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        .right { text-align: right; }
        .total { font-size: 16px; font-weight: bold; }
        .lunas { color: #16a34a; border: 3px solid #16a34a; padding: 6px 16px;
                 display: inline-block; font-size: 18px; font-weight: bold;
                 transform: rotate(-8deg); border-radius: 6px; }
        .muted { color: #6b7280; }
        .footer { margin-top: 28px; padding-top: 12px; border-top: 1px solid #e5e7eb;
                  font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
<div class="header">
    <span class="brand">{{ $company['name'] }}</span>
    <span style="float:right;font-size:16px;">{{ $isPaid ? 'KWITANSI' : 'INVOICE' }}</span>
</div>

<div class="wrap">
    <div class="row">
        <div class="col">
            <div class="muted">Ditagihkan kepada:</div>
            <b>{{ $customer->name }}</b><br>
            {{ $customer->username }}<br>
            @if($customer->address){{ $customer->address }}<br>@endif
            @if($customer->phone){{ $customer->phone }}@endif
        </div>
        <div class="col right">
            <div class="muted">No. {{ $isPaid ? 'Kwitansi' : 'Invoice' }}</div>
            <b>{{ $trx->order_id }}</b><br>
            <div class="muted" style="margin-top:6px">Tanggal</div>
            {{ $trx->created_at->format('d/m/Y') }}
            @if($isPaid)
                <div class="muted" style="margin-top:6px">Dibayar</div>
                {{ $trx->paid_at?->format('d/m/Y H:i') }}
            @endif
        </div>
    </div>

    <table class="items">
        <thead>
        <tr><th>Keterangan</th><th class="right">Jumlah</th></tr>
        </thead>
        <tbody>
        <tr>
            <td>
                Paket <b>{{ $customer->plan->name }}</b> ({{ $customer->plan->bandwidth }})<br>
                <span class="muted">Masa aktif {{ $customer->plan->duration_days }} hari
                    — s/d {{ $customer->expired_date->format('d/m/Y') }}</span>
            </td>
            <td class="right">Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
        </tr>
        @if($trx->late_fee > 0)
            <tr><td>Denda keterlambatan</td>
                <td class="right">Rp {{ number_format($trx->late_fee, 0, ',', '.') }}</td></tr>
        @endif
        @if($trx->discount > 0)
            <tr><td>Diskon</td>
                <td class="right">- Rp {{ number_format($trx->discount, 0, ',', '.') }}</td></tr>
        @endif
        <tr>
            <td class="right total">TOTAL</td>
            <td class="right total">Rp {{ number_format($trx->grandTotal(), 0, ',', '.') }}</td>
        </tr>
        </tbody>
    </table>

    @if($isPaid)
        <div style="margin-top:24px"><span class="lunas">LUNAS</span></div>
    @else
        <p style="margin-top:20px">
            Metode pembayaran: <b>{{ strtoupper($trx->payment_method) }}</b><br>
            @if($trx->payment_method === 'moota')
                <span class="muted">Transfer tepat sampai 3 digit terakhir:</span>
                <b>Rp {{ number_format($trx->amount_final, 0, ',', '.') }}</b>
            @endif
        </p>
    @endif

    @if($trx->note)
        <p class="muted" style="margin-top:12px">Catatan: {{ $trx->note }}</p>
    @endif

    <div class="footer">
        {{ $company['name'] }}
        @if($company['address']) — {{ $company['address'] }} @endif
        @if($company['phone']) — {{ $company['phone'] }} @endif
        <br>Dokumen ini dibuat otomatis oleh sistem dan sah tanpa tanda tangan.
    </div>
</div>
</body>
</html>
