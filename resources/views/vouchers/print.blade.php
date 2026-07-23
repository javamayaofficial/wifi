@php
    $mode = request('mode', 'a4');            // a4 | thermal
    $loginUrl = \App\Models\Setting::get('hotspot_login_url', '');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Voucher — THRE.F.NET</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 10mm; }

        /* ---- Mode A4: grid siap gunting ---- */
        .grid { display: flex; flex-wrap: wrap; gap: 4mm; }
        .v {
            width: 52mm; border: 1px dashed #999; border-radius: 3mm;
            padding: 3mm; box-sizing: border-box; text-align: center;
        }

        /* ---- Mode thermal 58mm ---- */
        .thermal { width: 54mm; margin: 0 0 4mm 0; padding-bottom: 3mm;
                   border-bottom: 1px dashed #666; text-align: center; }

        .brand { background: #0d6efd; color: #fff; font-weight: bold;
                 padding: 1.5mm; border-radius: 2mm; font-size: 10pt; letter-spacing: .5px; }
        .code { font-size: 17pt; font-weight: bold; letter-spacing: 2px;
                margin: 2.5mm 0; font-family: monospace; }
        .meta { font-size: 8pt; color: #444; }
        .note { font-size: 7pt; color: #777; margin-top: 1.5mm; }
        .qr { margin: 2mm auto 0; }
        .qr img, .qr canvas { margin: 0 auto; }

        @media print {
            .noprint { display: none; }
            body { margin: 3mm; }
            @page { margin: 5mm; }
        }
    </style>
</head>
<body>
<div class="noprint" style="margin-bottom:8mm">
    <button onclick="window.print()" style="padding:8px 18px;font-size:14px;cursor:pointer">Cetak</button>
    <a href="?batch={{ request('batch') }}&mode=a4"
       style="margin-left:10px;{{ $mode === 'a4' ? 'font-weight:bold' : '' }}">A4 (grid)</a>
    <a href="?batch={{ request('batch') }}&mode=thermal"
       style="margin-left:8px;{{ $mode === 'thermal' ? 'font-weight:bold' : '' }}">Thermal 58mm</a>
    <span style="margin-left:14px;color:#666">
        Batch {{ $vouchers->first()->batch }} — {{ $vouchers->count() }} voucher
    </span>
    @if(! $loginUrl)
        <div style="margin-top:6px;color:#a16207;font-size:12px">
            QR code nonaktif. Isi <b>hotspot_login_url</b> di Pengaturan agar QR muncul.
        </div>
    @endif
</div>

<div class="{{ $mode === 'thermal' ? '' : 'grid' }}">
    @foreach($vouchers as $v)
        <div class="{{ $mode === 'thermal' ? 'thermal' : 'v' }}">
            <div class="brand">THRE.F.NET</div>
            <div class="code">{{ $v->code }}</div>
            <div class="meta">
                {{ $v->profile?->name }}<br>
                Rp {{ number_format($v->profile?->price ?? 0, 0, ',', '.') }} · {{ $v->profile?->validity }}
            </div>
            @if($loginUrl)
                <div class="qr" data-qr="{{ $loginUrl }}?username={{ $v->code }}&password={{ $v->code }}"></div>
            @endif
            <div class="note">Hubungkan ke WiFi, lalu masukkan kode di atas.</div>
        </div>
    @endforeach
</div>

@if($loginUrl)
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        document.querySelectorAll('[data-qr]').forEach(function (el) {
            new QRCode(el, { text: el.dataset.qr, width: 70, height: 70, correctLevel: QRCode.CorrectLevel.M });
        });
    </script>
@endif
</body>
</html>
