@extends('layouts.admin')
@section('title', 'Laba Rugi')

@section('content')
<div class="d-flex justify-content-end align-items-center mb-3">
        <form method="GET">
        <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
            @for($y = now()->year; $y >= now()->year - 4; $y--)
                <option value="{{ $y }}" @selected($tahun === $y)>{{ $y }}</option>
            @endfor
        </select>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted small">Total Pemasukan</div>
            <div class="fs-4 fw-bold text-success">Rp {{ number_format($totalIn, 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted small">Total Pengeluaran</div>
            <div class="fs-4 fw-bold text-danger">Rp {{ number_format($totalOut, 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 {{ $totalIn - $totalOut < 0 ? 'border-danger border-2' : '' }}">
            <div class="card-body">
                <div class="text-muted small">Laba Bersih</div>
                <div class="fs-4 fw-bold {{ $totalIn - $totalOut < 0 ? 'text-danger' : 'text-primary' }}">
                    Rp {{ number_format($totalIn - $totalOut, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Bulan</th>
                        <th class="text-end">Billing</th>
                        <th class="text-end">Voucher</th>
                        <th class="text-end">Total Masuk</th>
                        <th class="text-end">Keluar</th>
                        <th class="text-end">Laba</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($rows as $r)
                        <tr>
                            <td>{{ $r['bulan'] }}</td>
                            <td class="text-end small text-muted">Rp {{ number_format($r['billing'], 0, ',', '.') }}</td>
                            <td class="text-end small text-muted">Rp {{ number_format($r['voucher'], 0, ',', '.') }}</td>
                            <td class="text-end text-success">Rp {{ number_format($r['masuk'], 0, ',', '.') }}</td>
                            <td class="text-end text-danger">Rp {{ number_format($r['keluar'], 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold {{ $r['laba'] < 0 ? 'text-danger' : '' }}">
                                Rp {{ number_format($r['laba'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Pengeluaran per Kategori</h6>
                @forelse($perKategori as $kat => $total)
                    @php $persen = $totalOut > 0 ? round($total / $totalOut * 100) : 0; @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small">
                            <span>{{ \App\Models\Expense::CATEGORIES[$kat] ?? $kat }}</span>
                            <b>Rp {{ number_format($total, 0, ',', '.') }}</b>
                        </div>
                        <div class="progress" style="height:6px">
                            <div class="progress-bar" style="width: {{ $persen }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Belum ada data.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<p class="small text-muted mt-3">
    Pemasukan = tagihan PPPoE berstatus lunas + penjualan voucher yang sudah diakui
    (terjual/terpakai). Voucher yang masih dititipkan di agen belum dihitung sebagai omzet. Laporan ini bersifat ringkas
    untuk pemantauan usaha, bukan pengganti pembukuan akuntansi resmi.
</p>
@endsection
