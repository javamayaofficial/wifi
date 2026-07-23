@extends('layouts.admin')
@section('title', 'Pengaturan Pembayaran')

@section('content')
<form method="POST" action="{{ url('/settings/payment') }}">
    @csrf
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <label class="form-label fw-semibold">Metode Utama</label>
            <select name="active_driver" class="form-select" style="max-width:320px">
                @foreach($drivers as $d)
                    <option value="{{ $d }}" @selected($active === $d)>{{ strtoupper($d) }}</option>
                @endforeach
            </select>
            <div class="form-text">Dipakai sebagai rekomendasi utama, sementara pelanggan tetap bisa melihat semua metode yang Anda aktifkan.</div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Metode yang Ditampilkan ke Pelanggan</h6>
            <div class="row g-3">
                @foreach($paymentOptions as $code => $option)
                    <div class="col-12 col-md-6">
                        <label class="border rounded-3 p-3 d-block h-100">
                            <input type="hidden" name="payment_option_{{ $code }}" value="0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       id="payment_option_{{ $code }}"
                                       name="payment_option_{{ $code }}" value="1"
                                       @checked(old('payment_option_' . $code, $values['payment_option_' . $code] ?? true))>
                                <span class="form-check-label fw-semibold">{{ $option['label'] }}</span>
                            </div>
                            <div class="small text-muted mt-2">{{ $option['description'] }}</div>
                            <div class="small mt-2">Driver: {{ strtoupper($option['driver']) }}</div>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @foreach($configFields as $driver => $fields)
        <div class="card shadow-sm border-0 mb-3">
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
@endsection
