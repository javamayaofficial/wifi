@extends('pay.layout')
@section('title', 'Tagihan')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-1">Halo, {{ $customer->name }}</h5>
        <div class="text-muted small mb-3">{{ $customer->username }}</div>

        <table class="table table-sm mb-3">
            <tr><td class="text-muted">Paket</td><td class="text-end fw-semibold">{{ $customer->plan->name }}</td></tr>
            <tr><td class="text-muted">Bandwidth</td><td class="text-end">{{ $customer->plan->bandwidth }}</td></tr>
            <tr><td class="text-muted">Masa aktif s/d</td><td class="text-end">{{ $customer->expired_date->format('d/m/Y') }}</td></tr>
            <tr><td class="text-muted">Status</td>
                <td class="text-end">
                    <span class="badge bg-{{ $customer->status === 'active' ? 'success' : 'danger' }}">{{ $customer->status }}</span>
                </td>
            </tr>
            <tr class="border-top">
                <td class="fw-semibold">Total Tagihan</td>
                <td class="text-end fs-5 fw-bold">Rp {{ number_format($customer->plan->price, 0, ',', '.') }}</td>
            </tr>
        </table>

        <form method="POST" action="{{ url("/bayar/{$customer->username}") }}">
            @csrf

            <div class="mb-3">
                <div class="fw-semibold mb-2">Pilih Metode Pembayaran</div>
                <div class="d-grid gap-2">
                    @foreach($options as $option)
                        @php
                            $checked = old('option', $recommendedOption['code'] ?? null) === $option['code'];
                        @endphp
                        <label class="border rounded-3 p-3 {{ $option['ready'] ? '' : 'bg-light text-muted' }}">
                            <div class="d-flex align-items-start gap-2">
                                <input type="radio" name="option" value="{{ $option['code'] }}"
                                       class="form-check-input mt-1"
                                       @checked($checked)
                                       @disabled(! $option['ready'])>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between gap-2">
                                        <span class="fw-semibold">{{ $option['label'] }}</span>
                                        <span class="badge text-bg-{{ $option['ready'] ? 'success' : 'secondary' }}">
                                            {{ $option['status_label'] }}
                                        </span>
                                    </div>
                                    <div class="small">{{ $option['description'] }}</div>
                                    <div class="small text-muted mt-1">{{ $option['group'] }}</div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('option')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn-primary w-100">Lanjutkan Pembayaran</button>
        </form>

        @if($pending)
            <a href="{{ url("/bayar/{$customer->username}/status/{$pending->order_id}") }}"
               class="btn btn-link w-100 mt-2">Cek status pembayaran terakhir</a>
        @endif
    </div>
</div>
@endsection
