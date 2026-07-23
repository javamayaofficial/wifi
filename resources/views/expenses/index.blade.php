@extends('layouts.admin')
@section('title', 'Pengeluaran')

@section('content')
<h4 class="mb-3">Pengeluaran</h4>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Catat Pengeluaran</h6>
                <form method="POST" action="{{ url('/expenses') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Tanggal</label>
                        <input type="date" name="date" class="form-control form-control-sm"
                               value="{{ old('date', now()->toDateString()) }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Kategori</label>
                        <select name="category" class="form-select form-select-sm" required>
                            @foreach(\App\Models\Expense::CATEGORIES as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Keterangan</label>
                        <input type="text" name="description" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Jumlah (Rp)</label>
                        <input type="number" name="amount" class="form-control form-control-sm" min="0" step="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Foto nota (opsional)</label>
                        <input type="file" name="attachment" class="form-control form-control-sm" accept="image/*">
                    </div>
                    <button class="btn btn-primary btn-sm w-100">Simpan</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Ringkasan Bulan Ini</h6>
                @forelse($perKategori as $kat => $total)
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ \App\Models\Expense::CATEGORIES[$kat] ?? $kat }}</span>
                        <b>Rp {{ number_format($total, 0, ',', '.') }}</b>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Belum ada pengeluaran.</p>
                @endforelse
                <hr>
                <div class="d-flex justify-content-between">
                    <b>Total</b>
                    <b>Rp {{ number_format($expenses->sum('amount'), 0, ',', '.') }}</b>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <form method="GET" class="mb-3">
            <input type="month" name="bulan" value="{{ $bulan }}" class="form-control form-control-sm"
                   style="max-width:200px" onchange="this.form.submit()">
        </form>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th class="text-end">Jumlah</th><th></th></tr>
                    </thead>
                    <tbody>
                    @forelse($expenses as $e)
                        <tr>
                            <td class="small">{{ $e->date->format('d/m/Y') }}</td>
                            <td class="small">{{ \App\Models\Expense::CATEGORIES[$e->category] ?? $e->category }}</td>
                            <td>
                                {{ $e->description }}
                                @if($e->attachment)
                                    <a href="{{ asset('storage/' . $e->attachment) }}" target="_blank" class="small">(nota)</a>
                                @endif
                            </td>
                            <td class="text-end">Rp {{ number_format($e->amount, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ url("/expenses/{$e->id}") }}"
                                      onsubmit="return confirm('Hapus catatan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">×</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada pengeluaran bulan ini.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
