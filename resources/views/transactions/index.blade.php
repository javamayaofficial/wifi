@extends('layouts.admin')
@section('title', 'Transaksi')

@section('content')
<h4 class="mb-3">Laporan Transaksi THRE.F.NET</h4>

<form method="GET" class="row g-2 mb-3">
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="">Semua status</option>
            @foreach(['pending','paid','expired','failed'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="method" class="form-select form-select-sm">
            <option value="">Semua metode</option>
            @foreach(['doku','moota','manual'] as $m)
                <option value="{{ $m }}" @selected(request('method') === $m)>{{ $m }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Filter</button></div>
</form>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr><th>Order ID</th><th>Pelanggan</th><th>Nominal</th><th>Metode</th><th>Status</th><th>Dibayar</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td><code class="small">{{ $t->order_id }}</code></td>
                    <td>{{ $t->customer?->name }}<div class="small text-muted">{{ $t->customer?->username }}</div></td>
                    <td>Rp {{ number_format($t->amount_final, 0, ',', '.') }}</td>
                    <td>{{ strtoupper($t->payment_method) }}</td>
                    <td>
                        <span class="badge bg-{{ $t->status === 'paid' ? 'success' : ($t->status === 'pending' ? 'warning text-dark' : 'secondary') }}">
                            {{ $t->status }}
                        </span>
                    </td>
                    <td class="small">{{ $t->paid_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="text-end">
                        @if($t->status === 'pending')
                            <form method="POST" action="{{ url("/transactions/{$t->id}/activate") }}" class="d-inline"
                                  onsubmit="return confirm('Konfirmasi pembayaran ini dan aktifkan internet pelanggan?')">
                                @csrf
                                <button class="btn btn-sm btn-success">Aktifkan</button>
                            </form>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $transactions->links() }}</div>
@endsection
