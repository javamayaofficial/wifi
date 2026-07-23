@extends('portal.layout')
@section('title', 'Tagihan')

@section('content')
<h5 class="mb-3">Riwayat Tagihan</h5>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr><th>Order ID</th><th>Tanggal</th><th>Jumlah</th><th>Status</th><th class="text-end">Dokumen</th></tr>
            </thead>
            <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td><code class="small">{{ $t->order_id }}</code></td>
                    <td class="small">{{ $t->created_at->format('d/m/Y') }}</td>
                    <td>Rp {{ number_format($t->grandTotal(), 0, ',', '.') }}</td>
                    <td>
                        <span class="badge bg-{{ $t->status === 'paid' ? 'success' : 'warning text-dark' }}">{{ $t->status }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ url("/bayar/{$customer->username}/invoice/{$t->order_id}") }}"
                           class="btn btn-sm btn-outline-primary">
                            {{ $t->status === 'paid' ? 'Kwitansi' : 'Invoice' }} PDF
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada tagihan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $transactions->links() }}</div>
@endsection
