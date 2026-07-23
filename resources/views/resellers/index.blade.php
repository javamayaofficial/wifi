@extends('layouts.admin')
@section('title', 'Mitra / Reseller')

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Tambah Mitra</h6>
                <form method="POST" action="{{ url('/resellers') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Nama</label>
                        <input type="text" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">No. HP</label>
                        <input type="text" name="phone" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Area</label>
                        <input type="text" name="area" class="form-control form-control-sm" placeholder="mis. Desa Sukamaju">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Komisi (%)</label>
                        <input type="number" name="commission_percent" class="form-control form-control-sm"
                               value="10" min="0" max="100" step="0.01" required>
                    </div>
                    <button class="btn btn-primary btn-sm w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr><th>Nama</th><th>Area</th><th>Pelanggan</th><th>Komisi</th><th class="text-end">Saldo</th><th></th></tr>
                    </thead>
                    <tbody>
                    @forelse($resellers as $r)
                        <tr>
                            <td>{{ $r->name }}<div class="small text-muted">{{ $r->phone }}</div></td>
                            <td class="small">{{ $r->area ?? '—' }}</td>
                            <td>{{ $r->customers_count }}</td>
                            <td>{{ rtrim(rtrim(number_format($r->commission_percent, 2), '0'), '.') }}%</td>
                            <td class="text-end fw-semibold {{ $r->deposit_balance < 0 ? 'text-danger' : '' }}">
                                Rp {{ number_format($r->deposit_balance, 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                <a href="{{ url("/resellers/{$r->id}") }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada mitra. Tambahkan warung atau agen yang menjual atas nama Anda.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
