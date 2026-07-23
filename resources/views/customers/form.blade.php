@extends('layouts.admin')
@section('title', $customer->exists ? 'Edit Pelanggan' : 'Tambah Pelanggan')

@section('content')
<h4 class="mb-4">{{ $customer->exists ? 'Edit' : 'Tambah' }} Pelanggan THRE.F.NET</h4>

<form method="POST" action="{{ $customer->exists ? url("/customers/{$customer->id}") : url('/customers') }}">
    @csrf
    @if($customer->exists) @method('PUT') @endif

    <div class="card shadow-sm border-0" style="max-width:720px">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username (PPPoE)</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username', $customer->username) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password PPPoE</label>
                    <input type="text" name="password" class="form-control"
                           placeholder="{{ $customer->exists ? 'Kosongkan bila tidak diubah' : '' }}"
                           {{ $customer->exists ? '' : 'required' }}>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Paket</label>
                    <select name="plan_id" class="form-select" required>
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}" @selected(old('plan_id', $customer->plan_id) == $p->id)>
                                {{ $p->name }} — Rp {{ number_format($p->price, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Router</label>
                    <select name="router_id" class="form-select" required>
                        @foreach($routers as $r)
                            <option value="{{ $r->id }}" @selected(old('router_id', $customer->router_id) == $r->id)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Expired</label>
                    <input type="date" name="expired_date" class="form-control"
                           value="{{ old('expired_date', $customer->expired_date?->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        @foreach(['new','active','isolated','suspended'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $customer->status) === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. HP (WhatsApp)</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}" placeholder="08xxxxxxxxxx">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-3" style="max-width:720px">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Data Teknis & Lokasi</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $customer->address) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Latitude</label>
                    <input type="text" name="latitude" class="form-control"
                           value="{{ old('latitude', $customer->latitude) }}" placeholder="-6.4025">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Longitude</label>
                    <input type="text" name="longitude" class="form-control"
                           value="{{ old('longitude', $customer->longitude) }}" placeholder="106.7942">
                    <div class="form-text">Salin dari Google Maps: klik kanan lokasi &rarr; koordinat.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama ODP / ODC</label>
                    <input type="text" name="odp_name" class="form-control" value="{{ old('odp_name', $customer->odp_name) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Port ODP</label>
                    <input type="text" name="odp_port" class="form-control" value="{{ old('odp_port', $customer->odp_port) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jenis Perangkat</label>
                    <input type="text" name="device_type" class="form-control"
                           value="{{ old('device_type', $customer->device_type) }}" placeholder="mis. ONU ZTE F609">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Serial Perangkat</label>
                    <input type="text" name="device_serial" class="form-control"
                           value="{{ old('device_serial', $customer->device_serial) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Pemasangan</label>
                    <input type="date" name="installed_at" class="form-control"
                           value="{{ old('installed_at', $customer->installed_at?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mitra / Reseller</label>
                    <select name="reseller_id" class="form-select">
                        <option value="">— Tidak ada —</option>
                        @foreach(\App\Models\Reseller::orderBy('name')->get() as $r)
                            <option value="{{ $r->id }}" @selected(old('reseller_id', $customer->reseller_id) == $r->id)>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ url('/customers') }}" class="btn btn-link">Batal</a>
    </div>
</form>
@endsection
