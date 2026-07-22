@extends('layouts.admin')
@section('title', $plan->exists ? 'Edit Paket' : 'Tambah Paket')

@section('content')
<h4 class="mb-4">{{ $plan->exists ? 'Edit' : 'Tambah' }} Paket THRE.F.NET</h4>

<form method="POST" action="{{ $plan->exists ? url("/plans/{$plan->id}") : url('/plans') }}">
    @csrf
    @if($plan->exists) @method('PUT') @endif

    <div class="card shadow-sm border-0" style="max-width:600px">
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama Paket</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Harga (Rp)</label>
                <input type="number" name="price" class="form-control" value="{{ old('price', $plan->price) }}" min="0" step="1" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Bandwidth</label>
                <input type="text" name="bandwidth" class="form-control" value="{{ old('bandwidth', $plan->bandwidth) }}" placeholder="20M/20M" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Durasi (hari)</label>
                <input type="number" name="duration_days" class="form-control" value="{{ old('duration_days', $plan->duration_days) }}" min="1" required>
            </div>
            <div class="col-12">
                <label class="form-label">Profil MikroTik</label>
                <input type="text" name="mikrotik_profile" class="form-control" value="{{ old('mikrotik_profile', $plan->mikrotik_profile) }}" required>
                <div class="form-text">Nama PPP profile di router yang sesuai bandwidth paket ini.</div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ url('/plans') }}" class="btn btn-link">Batal</a>
    </div>
</form>
@endsection
