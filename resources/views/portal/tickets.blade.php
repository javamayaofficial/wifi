@extends('portal.layout')
@section('title', 'Laporan Gangguan')

@section('content')
<h5 class="mb-3">Laporan Gangguan</h5>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Laporkan Gangguan Baru</h6>
        <form method="POST" action="{{ url('/portal/tickets') }}">
            @csrf
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small">Jenis gangguan</label>
                    <select name="category" class="form-select form-select-sm" required>
                        @foreach(\App\Models\Ticket::CATEGORIES as $k => $label)
                            <option value="{{ $k }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label small">Judul singkat</label>
                    <input type="text" name="title" class="form-control form-control-sm"
                           placeholder="mis. Internet mati sejak pagi" required>
                </div>
                <div class="col-12">
                    <label class="form-label small">Keterangan</label>
                    <textarea name="description" class="form-control form-control-sm" rows="3"
                              placeholder="Ceritakan detail gangguannya..."></textarea>
                </div>
            </div>
            <button class="btn btn-primary btn-sm mt-3">Kirim Laporan</button>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr><th>No. Tiket</th><th>Judul</th><th>Tanggal</th><th>Status</th></tr>
            </thead>
            <tbody>
            @forelse($tickets as $t)
                <tr>
                    <td><code class="small">{{ $t->ticket_number }}</code></td>
                    <td>{{ $t->title }}</td>
                    <td class="small">{{ $t->created_at->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge bg-{{ $t->status === 'selesai' ? 'success' : 'primary' }}">{{ $t->status }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada laporan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $tickets->links() }}</div>
@endsection
