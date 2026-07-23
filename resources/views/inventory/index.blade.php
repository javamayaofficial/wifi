@extends('layouts.admin')
@section('title', 'Inventory')

@section('content')
<div class="row g-3 mb-3">
    @foreach(['gudang' => 'secondary', 'terpasang' => 'success', 'rusak' => 'warning', 'hilang' => 'danger'] as $st => $warna)
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0"><div class="card-body py-2">
                <div class="text-muted small text-capitalize">{{ $st }}</div>
                <div class="fs-4 fw-bold text-{{ $warna }}">{{ $summary[$st] ?? 0 }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Tambah Perangkat</h6>
                <form method="POST" action="{{ url('/inventory') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Nama</label>
                        <input type="text" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Jenis</label>
                        <select name="type" class="form-select form-select-sm" required>
                            @foreach(\App\Models\InventoryItem::TYPES as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Serial Number</label>
                        <input type="text" name="serial" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select form-select-sm" required onchange="togglePelanggan(this.value)">
                            @foreach(['gudang','terpasang','rusak','hilang'] as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2" id="blokPelanggan" style="display:none">
                        <label class="form-label small">Terpasang di pelanggan</label>
                        <select name="customer_id" class="form-select form-select-sm">
                            <option value="">— pilih —</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->username }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small">Tgl beli</label>
                            <input type="date" name="purchase_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Harga</label>
                            <input type="number" name="purchase_price" class="form-control form-control-sm" min="0">
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-auto">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari nama / serial">
            </div>
            <div class="col-auto">
                <select name="type" class="form-select form-select-sm">
                    <option value="">Semua jenis</option>
                    @foreach(\App\Models\InventoryItem::TYPES as $k => $label)
                        <option value="{{ $k }}" @selected(request('type') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua status</option>
                    @foreach(['gudang','terpasang','rusak','hilang'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Filter</button></div>
        </form>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr><th>Nama</th><th>Jenis</th><th>Serial</th><th>Status</th><th>Terpasang di</th><th></th></tr>
                    </thead>
                    <tbody>
                    @forelse($items as $i)
                        <tr>
                            <td>{{ $i->name }}</td>
                            <td class="small">{{ \App\Models\InventoryItem::TYPES[$i->type] ?? $i->type }}</td>
                            <td><code class="small">{{ $i->serial ?? '—' }}</code></td>
                            <td>
                                <span class="badge bg-{{ ['gudang'=>'secondary','terpasang'=>'success','rusak'=>'warning text-dark','hilang'=>'danger'][$i->status] }}">
                                    {{ $i->status }}
                                </span>
                            </td>
                            <td class="small">{{ $i->customer?->name ?? '—' }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ url("/inventory/{$i->id}") }}"
                                      onsubmit="return confirm('Hapus perangkat ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">×</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada perangkat. Catat router, ONU, dan radio agar mudah dilacak.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $items->links() }}</div>
    </div>
</div>

<script>
function togglePelanggan(v) {
    document.getElementById('blokPelanggan').style.display = v === 'terpasang' ? '' : 'none';
}
</script>
@endsection
