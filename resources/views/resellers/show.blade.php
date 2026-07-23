@extends('layouts.admin')
@section('title', $reseller->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">{{ $reseller->name }}</h4>
    <a href="{{ url('/resellers') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="text-muted small">Saldo Deposit</div>
                <div class="fs-3 fw-bold {{ $reseller->deposit_balance < 0 ? 'text-danger' : 'text-primary' }}">
                    Rp {{ number_format($reseller->deposit_balance, 0, ',', '.') }}
                </div>
                <hr>
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Area</td><td class="text-end">{{ $reseller->area ?? '—' }}</td></tr>
                    <tr><td class="text-muted">No. HP</td><td class="text-end">{{ $reseller->phone ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Komisi</td><td class="text-end">{{ $reseller->commission_percent }}%</td></tr>
                    <tr><td class="text-muted">Pelanggan</td><td class="text-end">{{ $reseller->customers->count() }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Catat Mutasi Saldo</h6>
                <form method="POST" action="{{ url("/resellers/{$reseller->id}/transactions") }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Jenis</label>
                        <select name="type" class="form-select form-select-sm" required>
                            <option value="deposit">Deposit (menambah)</option>
                            <option value="komisi">Komisi (menambah)</option>
                            <option value="penarikan">Penarikan (mengurangi)</option>
                            <option value="penyesuaian">Penyesuaian</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Jumlah (Rp)</label>
                        <input type="number" name="amount" class="form-control form-control-sm" step="1" required>
                        <div class="form-text">Penyesuaian boleh negatif.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Keterangan</label>
                        <input type="text" name="description" class="form-control form-control-sm">
                    </div>
                    <button class="btn btn-primary btn-sm w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white fw-semibold">Riwayat Saldo</div>
            <div class="table-responsive" style="max-height:320px">
                <table class="table table-sm mb-0">
                    <thead class="table-light sticky-top">
                    <tr><th>Tanggal</th><th>Jenis</th><th>Keterangan</th><th class="text-end">Jumlah</th><th class="text-end">Saldo</th></tr>
                    </thead>
                    <tbody>
                    @forelse($reseller->transactions as $t)
                        <tr>
                            <td class="small">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge bg-light text-dark">{{ $t->type }}</span></td>
                            <td class="small">{{ $t->description ?? '—' }}</td>
                            <td class="text-end {{ $t->amount < 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($t->amount, 0, ',', '.') }}
                            </td>
                            <td class="text-end small">Rp {{ number_format($t->balance_after, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada mutasi.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold">Pelanggan Mitra Ini</div>
            <div class="table-responsive" style="max-height:320px">
                <table class="table table-sm mb-0">
                    <thead class="table-light sticky-top">
                    <tr><th>Nama</th><th>Username</th><th>Paket</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    @forelse($reseller->customers as $c)
                        <tr>
                            <td>{{ $c->name }}</td>
                            <td><code class="small">{{ $c->username }}</code></td>
                            <td class="small">{{ $c->plan?->name }}</td>
                            <td><span class="badge bg-{{ $c->status === 'active' ? 'success' : 'secondary' }}">{{ $c->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pelanggan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
