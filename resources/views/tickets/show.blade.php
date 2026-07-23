@extends('layouts.admin')
@section('title', $ticket->ticket_number)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="mb-1">{{ $ticket->title }}</h4>
        <code>{{ $ticket->ticket_number }}</code>
        <span class="badge bg-{{ $ticket->status === 'selesai' ? 'success' : ($ticket->status === 'baru' ? 'danger' : 'primary') }} ms-2">
            {{ $ticket->status }}
        </span>
        <span class="badge bg-{{ $ticket->priority === 'darurat' ? 'danger' : 'secondary' }}">{{ $ticket->priority }}</span>
    </div>
    <a href="{{ url('/tickets') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Informasi</h6>
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Pelanggan</td><td class="text-end">{{ $ticket->customer?->name }}</td></tr>
                    <tr><td class="text-muted">Username</td><td class="text-end"><code>{{ $ticket->customer?->username }}</code></td></tr>
                    <tr><td class="text-muted">No. HP</td><td class="text-end">{{ $ticket->customer?->phone ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Paket</td><td class="text-end">{{ $ticket->customer?->plan?->name }}</td></tr>
                    <tr><td class="text-muted">Kategori</td><td class="text-end">{{ \App\Models\Ticket::CATEGORIES[$ticket->category] ?? $ticket->category }}</td></tr>
                    <tr><td class="text-muted">Dibuat oleh</td><td class="text-end">{{ $ticket->creator?->name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Umur tiket</td><td class="text-end">{{ $ticket->ageInHours() }} jam</td></tr>
                </table>

                @if($ticket->description)
                    <div class="mt-3 small">
                        <div class="text-muted mb-1">Keterangan:</div>
                        {{ $ticket->description }}
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Penugasan</h6>
                <p class="small mb-2">Teknisi: <b>{{ $ticket->assignee?->name ?? 'belum ditugaskan' }}</b></p>
                <form method="POST" action="{{ url("/tickets/{$ticket->id}/assign") }}" class="row g-2">
                    @csrf
                    <div class="col">
                        <select name="assigned_to" class="form-select form-select-sm" required>
                            @foreach($teknisi as $u)
                                <option value="{{ $u->id }}" @selected($ticket->assigned_to === $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Tugaskan</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Tambah Catatan / Ubah Status</h6>
                <form method="POST" action="{{ url("/tickets/{$ticket->id}/updates") }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <textarea name="note" class="form-control" rows="3" placeholder="Catatan pengerjaan..." required></textarea>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small">Ubah status</label>
                            <select name="status_to" class="form-select form-select-sm">
                                @foreach(\App\Models\Ticket::STATUSES as $s)
                                    <option value="{{ $s }}" @selected($ticket->status === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Foto bukti (opsional)</label>
                            <input type="file" name="photo" class="form-control form-control-sm" accept="image/*">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary btn-sm w-100">Kirim</button>
                        </div>
                    </div>
                    <div class="form-text">Jika status diubah ke <b>selesai</b>, pelanggan otomatis diberi tahu via WhatsApp.</div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Riwayat</h6>
                @forelse($ticket->updatesLog as $u)
                    <div class="border-start ps-3 pb-3 mb-1" style="border-width:3px !important;border-color:#0d6efd !important">
                        <div class="small text-muted">
                            {{ $u->user?->name ?? 'sistem' }} — {{ $u->created_at->format('d/m/Y H:i') }}
                            @if($u->status_to && $u->status_from && $u->status_to !== $u->status_from)
                                <span class="badge bg-light text-dark">{{ $u->status_from }} → {{ $u->status_to }}</span>
                            @endif
                        </div>
                        <div>{{ $u->note }}</div>
                        @if($u->photo_path)
                            <a href="{{ asset('storage/' . $u->photo_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $u->photo_path) }}" class="rounded mt-2" style="max-height:140px">
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="text-muted small mb-0">Belum ada catatan.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
