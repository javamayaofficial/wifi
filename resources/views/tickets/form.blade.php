@extends('layouts.admin')
@section('title', 'Tiket Baru')

@section('content')
<form method="POST" action="{{ url('/tickets') }}">
    @csrf
    <div class="card shadow-sm border-0" style="max-width:720px">
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Pelanggan</label>
                <select name="customer_id" class="form-select" required>
                    <option value="">— Pilih pelanggan —</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>
                            {{ $c->name }} ({{ $c->username }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kategori</label>
                <select name="category" class="form-select" required>
                    @foreach(\App\Models\Ticket::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Judul</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}"
                       placeholder="mis. Internet mati sejak pagi" required>
            </div>
            <div class="col-12">
                <label class="form-label">Keterangan</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Prioritas</label>
                <select name="priority" class="form-select" required>
                    @foreach(['rendah','normal','tinggi','darurat'] as $p)
                        <option value="{{ $p }}" @selected(old('priority', 'normal') === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tugaskan ke (opsional)</label>
                <select name="assigned_to" class="form-select">
                    <option value="">— Belum ditugaskan —</option>
                    @foreach($teknisi as $u)
                        <option value="{{ $u->id }}" @selected(old('assigned_to') == $u->id)>{{ $u->name }} ({{ $u->role }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary">Simpan Tiket</button>
        <a href="{{ url('/tickets') }}" class="btn btn-link">Batal</a>
    </div>
</form>
@endsection
