@extends('layouts.admin')
@section('title', 'Pengaturan Pembayaran')

@section('content')
<h4 class="mb-4">Pengaturan Payment Gateway THRE.F.NET</h4>

<form method="POST" action="{{ url('/settings/payment') }}">
    @csrf
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <label class="form-label fw-semibold">Driver Aktif</label>
            <select name="active_driver" class="form-select" style="max-width:320px" onchange="toggleCfg(this.value)">
                @foreach($drivers as $d)
                    <option value="{{ $d }}" @selected($active === $d)>{{ strtoupper($d) }}</option>
                @endforeach
            </select>
            <div class="form-text">Perubahan berlaku langsung untuk transaksi berikutnya tanpa restart.</div>
        </div>
    </div>

    @foreach($configFields as $driver => $fields)
        <div class="card shadow-sm border-0 mb-3 cfg-block" data-driver="{{ $driver }}"
             style="{{ $active === $driver ? '' : 'display:none' }}">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Konfigurasi {{ strtoupper($driver) }}</h6>
                @foreach($fields as $key => $meta)
                    <div class="mb-3">
                        <label class="form-label">{{ $meta['label'] ?? $key }}</label>
                        @if(($meta['type'] ?? 'text') === 'select')
                            <select name="{{ $key }}" class="form-select" style="max-width:320px">
                                @foreach($meta['options'] as $opt)
                                    <option value="{{ $opt }}" @selected(($values[$key] ?? '') === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                        @elseif(($meta['type'] ?? 'text') === 'textarea')
                            <textarea name="{{ $key }}" class="form-control" rows="3">{{ $values[$key] ?? '' }}</textarea>
                        @else
                            <input type="{{ $meta['type'] === 'password' ? 'password' : 'text' }}"
                                   name="{{ $key }}" class="form-control" style="max-width:420px"
                                   value="{{ $meta['type'] === 'password' ? '' : ($values[$key] ?? '') }}"
                                   placeholder="{{ $meta['type'] === 'password' && ($values[$key] ?? false) ? '•••• (tersimpan, isi untuk mengganti)' : '' }}">
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <button class="btn btn-primary">Simpan</button>
</form>

<script>
function toggleCfg(driver) {
    document.querySelectorAll('.cfg-block').forEach(function (el) {
        el.style.display = el.dataset.driver === driver ? '' : 'none';
    });
}
</script>
@endsection
