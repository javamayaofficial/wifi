@extends('layouts.admin')
@section('title', 'Audit Log')

@section('content')
<h4 class="mb-1">Audit Log</h4>
<p class="text-muted small mb-4">
    Catatan siapa mengubah apa. Nilai sensitif (password, token) tidak pernah disimpan di sini.
</p>

<form method="GET" class="row g-2 mb-3">
    <div class="col-auto">
        <select name="model" class="form-select form-select-sm">
            <option value="">Semua objek</option>
            @foreach($models as $m)
                <option value="{{ $m }}" @selected(request('model') === $m)>{{ $m }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="event" class="form-select form-select-sm">
            <option value="">Semua aksi</option>
            @foreach(['created','updated','deleted'] as $e)
                <option value="{{ $e }}" @selected(request('event') === $e)>{{ $e }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Filter</button></div>
</form>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
            <tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Objek</th><th>Perubahan</th><th>IP</th></tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td class="small text-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td class="small">{{ $log->user_name }}</td>
                    <td>
                        <span class="badge bg-{{ $log->event === 'deleted' ? 'danger' : ($log->event === 'created' ? 'success' : 'primary') }}">
                            {{ $log->event }}
                        </span>
                    </td>
                    <td class="small">{{ $log->model_type }}<div class="text-muted">{{ $log->label }}</div></td>
                    <td class="small" style="max-width:360px">
                        @if($log->changes)
                            @foreach($log->changes as $field => $val)
                                <div>
                                    <b>{{ $field }}</b>:
                                    @if(is_array($val))
                                        <span class="text-muted">{{ $val['dari'] ?? '' }}</span> →
                                        <span>{{ $val['ke'] ?? '' }}</span>
                                    @else
                                        {{ $val }}
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $log->ip }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada catatan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $logs->links() }}</div>
@endsection
