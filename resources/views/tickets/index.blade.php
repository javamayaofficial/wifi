@extends('layouts.admin')
@section('title', 'Tiket Gangguan')

@section('actions')
    <a href="{{ url('/tickets/create') }}" class="btn btn-primary btn-sm">Buat tiket</a>
@endsection

@section('content')

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0"><div class="card-body py-2">
            <div class="text-muted small">Tiket Baru</div>
            <div class="fs-4 fw-bold text-danger">{{ $counts['baru'] }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0"><div class="card-body py-2">
            <div class="text-muted small">Sedang Ditangani</div>
            <div class="fs-4 fw-bold text-warning">{{ $counts['proses'] }}</div>
        </div></div>
    </div>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari no. tiket / judul">
    </div>
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="">Semua status</option>
            @foreach(\App\Models\Ticket::STATUSES as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="priority" class="form-select form-select-sm">
            <option value="">Semua prioritas</option>
            @foreach(['darurat','tinggi','normal','rendah'] as $p)
                <option value="{{ $p }}" @selected(request('priority') === $p)>{{ $p }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Filter</button></div>
</form>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr><th>No. Tiket</th><th>Pelanggan</th><th>Judul</th><th>Prioritas</th><th>Status</th><th>Teknisi</th><th>Umur</th></tr>
            </thead>
            <tbody>
            @forelse($tickets as $t)
                <tr onclick="location.href='{{ url("/tickets/{$t->id}") }}'" style="cursor:pointer">
                    <td><code class="small">{{ $t->ticket_number }}</code></td>
                    <td>{{ $t->customer?->name }}<div class="small text-muted">{{ $t->customer?->username }}</div></td>
                    <td>{{ $t->title }}</td>
                    <td>
                        <span class="badge bg-{{ $t->priority === 'darurat' ? 'danger' : ($t->priority === 'tinggi' ? 'warning text-dark' : 'secondary') }}">
                            {{ $t->priority }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $t->status === 'selesai' ? 'success' : ($t->status === 'baru' ? 'danger' : 'primary') }}">
                            {{ $t->status }}
                        </span>
                    </td>
                    <td class="small">{{ $t->assignee?->name ?? '—' }}</td>
                    <td class="small text-muted">{{ $t->ageInHours() }} jam</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada tiket. Laporan gangguan dari pelanggan akan muncul di sini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $tickets->links() }}</div>
@endsection
