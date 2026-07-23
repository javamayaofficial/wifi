@extends('layouts.admin')
@section('title', 'Peta Pelanggan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Peta Sebaran Pelanggan</h4>
    @if($tanpaKoordinat > 0)
        <span class="badge bg-warning text-dark">{{ $tanpaKoordinat }} pelanggan belum punya koordinat</span>
    @endif
</div>

<div class="row g-3">
    <div class="col-lg-9">
        <div class="card shadow-sm border-0">
            <div id="peta" style="height:560px;border-radius:.5rem"></div>
        </div>
        <p class="small text-muted mt-2">
            Hijau = aktif, merah = terisolir. Klik penanda untuk melihat detail pelanggan.
        </p>
    </div>

    <div class="col-lg-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Pelanggan per ODP</h6>
                <p class="small text-muted">
                    Saat satu ODP bermasalah, daftar ini menunjukkan siapa saja yang terdampak.
                </p>
                @forelse($perOdp as $o)
                    <div class="d-flex justify-content-between small border-bottom py-1">
                        <span>{{ $o->odp_name }}</span>
                        <b>{{ $o->total }}</b>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Belum ada data ODP.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const pelanggan = @json($customers);

const peta = L.map('peta').setView(
    pelanggan.length ? [pelanggan[0].lat, pelanggan[0].lng] : [-6.4, 106.8],
    pelanggan.length ? 14 : 11
);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap',
    maxZoom: 19
}).addTo(peta);

const grup = [];

pelanggan.forEach(function (p) {
    const warna = p.status === 'active' ? '#16a34a' : '#dc2626';

    const m = L.circleMarker([p.lat, p.lng], {
        radius: 7, color: warna, fillColor: warna, fillOpacity: .8, weight: 2
    }).addTo(peta);

    m.bindPopup(
        '<b>' + p.name + '</b><br>' +
        '<code>' + p.username + '</code><br>' +
        'Paket: ' + (p.plan || '-') + '<br>' +
        'ODP: ' + (p.odp || '-') + '<br>' +
        'Status: ' + p.status
    );

    grup.push(m);
});

if (grup.length) {
    peta.fitBounds(L.featureGroup(grup).getBounds().pad(0.2));
}
</script>
@endsection
