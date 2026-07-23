@extends('layouts.admin')
@section('title', $plan->exists ? 'Edit Paket' : 'Tambah Paket')

@section('content')
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
                <div class="d-flex gap-2">
                    <input type="text" name="mikrotik_profile" id="mkProfile" list="mkList"
                           class="form-control" value="{{ old('mikrotik_profile', $plan->mikrotik_profile) }}" required>
                    <select id="mkRouter" class="form-select" style="max-width:190px">
                        @foreach(\App\Models\Router::orderBy('name')->get() as $r)
                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" id="mkFetch" class="btn btn-outline-secondary text-nowrap">Ambil</button>
                </div>
                <datalist id="mkList"></datalist>
                <div class="form-text" id="mkHint">
                    Nama PPP profile di router yang sesuai bandwidth paket ini.
                    Klik <b>Ambil</b> untuk memuat daftar dari router — salah ketik di sini
                    membuat aktivasi pelanggan gagal.
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ url('/plans') }}" class="btn btn-link">Batal</a>
    </div>
</form>
<script>
document.getElementById('mkFetch')?.addEventListener('click', function () {
    var hint = document.getElementById('mkHint');
    var id   = document.getElementById('mkRouter').value;

    hint.textContent = 'Menghubungi router...';

    fetch('{{ url("/mikrotik/profiles") }}?router=' + id)
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (d.error) { hint.textContent = 'Gagal: ' + d.error; return; }
            var list = document.getElementById('mkList');
            list.innerHTML = d.profiles.map(function (p) {
                return '<option value="' + p + '">';
            }).join('');
            hint.textContent = d.profiles.length + ' profile ditemukan. Klik kolom di atas untuk memilih.';
        })
        .catch(function () { hint.textContent = 'Gagal menghubungi server.'; });
});
</script>
@endsection
