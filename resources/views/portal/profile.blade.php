@extends('portal.layout')
@section('title', 'Profil Pelanggan')

@section('content')
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
    <div>
        <h5 class="mb-1">Profil Pelanggan</h5>
        <div class="text-muted small">Lengkapi data identitas dan titik lokasi pemasangan agar admin punya acuan peta yang akurat.</div>
    </div>
    <span class="badge text-bg-{{ $customer->profileIsComplete() ? 'success' : 'warning' }}">
        {{ $customer->profileIsComplete() ? 'Profil lengkap' : 'Masih perlu dilengkapi' }}
    </span>
</div>

<form method="POST" action="{{ url('/portal/profile') }}" enctype="multipart/form-data">
    @csrf

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Data Identitas</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name', $customer->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor WhatsApp</label>
                    <input type="text" name="phone" class="form-control"
                           value="{{ old('phone', $customer->phone) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email', $customer->email) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Nomor KTP</label>
                    <input type="text" name="national_id_number" class="form-control"
                           inputmode="numeric" maxlength="20"
                           value="{{ old('national_id_number', $customer->national_id_number) }}"
                           placeholder="16 digit nomor KTP" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Upload Foto KTP</label>
                    <input type="file" name="identity_card" class="form-control" accept="image/*" {{ $customer->hasIdentityCard() ? '' : 'required' }}>
                    <div class="form-text">Format gambar JPG/PNG, maksimal 4 MB.</div>
                    @if($customer->identity_card_path)
                        <div class="mt-3">
                            <div class="small text-muted mb-2">File KTP saat ini</div>
                            <a href="{{ asset('storage/' . $customer->identity_card_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $customer->identity_card_path) }}" alt="KTP Pelanggan"
                                     class="img-fluid rounded-3 border" style="max-height: 220px;">
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Alamat & Titik Lokasi</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Alamat Lengkap Pemasangan</label>
                    <textarea name="address" class="form-control" rows="3" required>{{ old('address', $customer->address) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Latitude</label>
                    <input type="text" name="latitude" id="latitude" class="form-control"
                           value="{{ old('latitude', $customer->latitude) }}" placeholder="-6.4025" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Longitude</label>
                    <input type="text" name="longitude" id="longitude" class="form-control"
                           value="{{ old('longitude', $customer->longitude) }}" placeholder="106.7942" required>
                </div>
                <div class="col-12">
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="button" class="btn btn-outline-primary" id="useCurrentLocation">Gunakan Lokasi Saya</button>
                        <span class="small text-muted align-self-center">Atau geser pin di peta sampai sesuai lokasi rumah.</span>
                    </div>
                </div>
                <div class="col-12">
                    <div id="customer-location-map" class="rounded-3 border" style="height: 360px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2">
        <button class="btn btn-primary btn-lg">Simpan Profil Pelanggan</button>
    </div>
</form>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const latInput = document.getElementById('latitude');
const lngInput = document.getElementById('longitude');
const hasExistingCoords = latInput.value !== '' && lngInput.value !== '';
const defaultLat = parseFloat(latInput.value || '-6.4025');
const defaultLng = parseFloat(lngInput.value || '106.7942');
const map = L.map('customer-location-map').setView([defaultLat, defaultLng], 16);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap',
    maxZoom: 19
}).addTo(map);

const marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

function syncInputs(latlng) {
    latInput.value = Number(latlng.lat).toFixed(7);
    lngInput.value = Number(latlng.lng).toFixed(7);
}

marker.on('dragend', function (event) {
    syncInputs(event.target.getLatLng());
});

map.on('click', function (event) {
    marker.setLatLng(event.latlng);
    syncInputs(event.latlng);
});

latInput.addEventListener('change', function () {
    const latlng = L.latLng(parseFloat(latInput.value), parseFloat(lngInput.value));
    if (! Number.isNaN(latlng.lat) && ! Number.isNaN(latlng.lng)) {
        marker.setLatLng(latlng);
        map.panTo(latlng);
    }
});

lngInput.addEventListener('change', function () {
    const latlng = L.latLng(parseFloat(latInput.value), parseFloat(lngInput.value));
    if (! Number.isNaN(latlng.lat) && ! Number.isNaN(latlng.lng)) {
        marker.setLatLng(latlng);
        map.panTo(latlng);
    }
});

document.getElementById('useCurrentLocation').addEventListener('click', function () {
    if (! navigator.geolocation) {
        alert('Browser ini tidak mendukung GPS lokasi.');
        return;
    }

    navigator.geolocation.getCurrentPosition(function (position) {
        const latlng = L.latLng(position.coords.latitude, position.coords.longitude);
        marker.setLatLng(latlng);
        map.setView(latlng, 18);
        syncInputs(latlng);
    }, function () {
        alert('Lokasi tidak bisa diambil. Silakan aktifkan izin lokasi atau pilih titik secara manual di peta.');
    }, {
        enableHighAccuracy: true,
        timeout: 10000,
    });
});

if (hasExistingCoords) {
    syncInputs(marker.getLatLng());
}
</script>
@endsection
