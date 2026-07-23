@extends('layouts.admin')
@section('title', 'Transaksi')

@section('content')
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
            @foreach($methodOptions as $code => $meta)
                <option value="{{ $code }}" @selected(request('method') === $code)>{{ $meta['label'] }}</option>
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
                    <td>{{ $methodOptions[$t->payment_method]['label'] ?? strtoupper($t->payment_method) }}</td>
                    <td>
                        @php $d = ['paid'=>'ok','pending'=>'warn','failed'=>'down'][$t->status] ?? 'idle'; @endphp
                        <span class="text-nowrap"><span class="dot dot-{{ $d }}"></span>{{ $t->status }}</span>
                    </td>
                    <td class="small">{{ $t->paid_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ url("/transactions/{$t->id}/invoice") }}" class="btn btn-sm btn-outline-secondary">
                            PDF
                        </a>
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
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada transaksi. Tagihan muncul setelah pelanggan memulai pembayaran.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $transactions->links() }}</div>
@endsection
