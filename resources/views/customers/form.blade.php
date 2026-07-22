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

    <div class="mt-3">
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ url('/customers') }}" class="btn btn-link">Batal</a>
    </div>
</form>
@endsection
