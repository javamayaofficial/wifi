@extends('layouts.admin')
@section('title', 'Penjualan Voucher')

@section('content')
<div class="d-flex justify-content-end align-items-center mb-3">
        <form method="GET">
        <input type="month" name="bulan" value="{{ $bulan }}" class="form-control form-control-sm"
               onchange="this.form.submit()">
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted small">Omzet Voucher Bulan Ini</div>
            <div class="fs-4 fw-bold text-success">Rp {{ number_format($omzet, 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted small">Voucher Terjual</div>
            <div class="fs-4 fw-bold">{{ number_format($terjual->count()) }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted small">Stok Siap Jual</div>
            <div class="fs-4 fw-bold text-primary">{{ number_format($stok->sum('gudang')) }}</div>
            <div class="small text-muted">+ {{ number_format($stok->sum('di_agen')) }} di agen</div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white fw-semibold">Per Profil Voucher</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                    <tr><th>Profil</th><th class="text-end">Jumlah</th><th class="text-end">Omzet</th></tr>
                    </thead>
                    <tbody>
                    @forelse($perProfil as $nama => $d)
                        <tr>
                            <td>{{ $nama }}</td>
                            <td class="text-end">{{ $d['jumlah'] }}</td>
                            <td class="text-end">Rp {{ number_format($d['omzet'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Belum ada penjualan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold">Per Agen / Penjual</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                    <tr><th>Agen</th><th class="text-end">Jumlah</th><th class="text-end">Omzet</th></tr>
                    </thead>
                    <tbody>
                    @forelse($perAgen as $nama => $d)
                        <tr>
                            <td>{{ $nama }}</td>
                            <td class="text-end">{{ $d['jumlah'] }}</td>
                            <td class="text-end">Rp {{ number_format($d['omzet'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white fw-semibold">Stok per Profil</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                    <tr><th>Profil</th><th class="text-end">Di Gudang</th><th class="text-end">Di Agen</th></tr>
                    </thead>
                    <tbody>
                    @forelse($stok as $s)
                        <tr>
                            <td>{{ $s['nama'] }}</td>
                            <td class="text-end {{ $s['gudang'] < 20 ? 'text-danger fw-bold' : '' }}">
                                {{ $s['gudang'] }}
                                @if($s['gudang'] < 20)<span class="small">(menipis)</span>@endif
                            </td>
                            <td class="text-end">{{ $s['di_agen'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Belum ada profil voucher.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold">Sisa Titipan di Agen</div>
            <div class="card-body">
                <p class="small text-muted">
                    Voucher yang sudah diserahkan tapi belum disetor hasilnya.
                    Gunakan ini saat menagih setoran.
                </p>
                @forelse($titipan as $agen => $perProfilAgen)
                    <div class="border-bottom py-2">
                        <b>{{ $agen }}</b>
                        @foreach($perProfilAgen as $profil => $jumlah)
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">{{ $profil }}</span>
                                <span>{{ $jumlah }} lembar</span>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <p class="text-muted small mb-0">Tidak ada titipan yang belum disetor.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<p class="small text-muted mt-3">
    Omzet diakui saat voucher <b>terjual</b> (jual langsung) atau saat agen <b>menyetorkan</b>
    hasil penjualan. Voucher yang masih dititipkan belum dihitung sebagai pendapatan,
    supaya laporan tidak menghitung uang yang belum diterima.
</p>
@endsection
