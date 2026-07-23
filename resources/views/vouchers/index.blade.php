@extends('layouts.admin')
@section('title', 'Voucher Hotspot')

@section('content')
<h4 class="mb-3">Voucher Hotspot</h4>

<div class="alert alert-success d-flex justify-content-between align-items-center">
    <span>Omzet voucher bulan ini</span>
    <b class="fs-5">Rp {{ number_format($omzetBulan, 0, ',', '.') }}</b>
</div>

<div class="row g-3 mb-3">
    @foreach(['tersedia' => 'primary', 'terjual' => 'success', 'terpakai' => 'secondary', 'kadaluarsa' => 'danger'] as $st => $warna)
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
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Generate Voucher</h6>
                @if($profiles->isEmpty())
                    <p class="text-muted small">Buat profil voucher dulu di bawah.</p>
                @else
                    <form method="POST" action="{{ url('/vouchers/generate') }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small">Profil</label>
                            <select name="voucher_profile_id" class="form-select form-select-sm" required>
                                @foreach($profiles as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->name }} — Rp {{ number_format($p->price, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Router</label>
                            <select name="router_id" class="form-select form-select-sm" required>
                                @foreach($routers as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Jumlah (maks 500)</label>
                            <input type="number" name="count" class="form-control form-control-sm"
                                   value="50" min="1" max="500" required>
                        </div>
                        <button class="btn btn-primary btn-sm w-100">Generate & Kirim ke Router</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-1">Titip ke Agen / Warung</h6>
                <p class="text-muted small">Stok berpindah ke agen. Belum dihitung sebagai omzet.</p>
                @if($resellers->isEmpty())
                    <p class="text-muted small mb-0">
                        Belum ada mitra. Tambahkan dulu di <a href="{{ url('/resellers') }}">Mitra / Reseller</a>.
                    </p>
                @else
                    <form method="POST" action="{{ url('/vouchers/handover') }}" class="mb-3">
                        @csrf
                        <select name="reseller_id" class="form-select form-select-sm mb-2" required>
                            @foreach($resellers as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                        <select name="voucher_profile_id" class="form-select form-select-sm mb-2" required>
                            @foreach($profiles as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="count" class="form-control form-control-sm mb-2"
                               placeholder="Jumlah" value="20" min="1" required>
                        <button class="btn btn-outline-primary btn-sm w-100">Serahkan ke Agen</button>
                    </form>

                    <h6 class="fw-bold mb-1">Setoran dari Agen</h6>
                    <p class="text-muted small">Tandai terjual + catat margin agen. Diakui sebagai omzet.</p>
                    <form method="POST" action="{{ url('/vouchers/settle') }}">
                        @csrf
                        <select name="reseller_id" class="form-select form-select-sm mb-2" required>
                            @foreach($resellers as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                        <select name="voucher_profile_id" class="form-select form-select-sm mb-2" required>
                            @foreach($profiles as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="count" class="form-control form-control-sm mb-2"
                               placeholder="Jumlah terjual" min="1" required>
                        <button class="btn btn-success btn-sm w-100">Catat Setoran</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Profil Voucher</h6>
                @foreach($profiles as $p)
                    <div class="small mb-2 pb-2 border-bottom">
                        <b>{{ $p->name }}</b> — Rp {{ number_format($p->price, 0, ',', '.') }}<br>
                        <span class="text-muted">profile: {{ $p->hotspot_profile }} · masa: {{ $p->validity }}</span>
                    </div>
                @endforeach

                <form method="POST" action="{{ url('/vouchers/profiles') }}" class="mt-3">
                    @csrf
                    <div class="mb-2">
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Nama profil, mis. Harian" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="hotspot_profile" class="form-control form-control-sm"
                               placeholder="Nama profile di MikroTik" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <input type="number" name="price" class="form-control form-control-sm" placeholder="Harga jual" min="0" required>
                        </div>
                        <div class="col-6">
                            <input type="number" name="agent_price" class="form-control form-control-sm" placeholder="Harga agen" min="0">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <input type="text" name="validity" class="form-control form-control-sm" placeholder="1d" value="1d" required>
                        </div>
                        <div class="col-4">
                            <input type="number" name="shelf_life_days" class="form-control form-control-sm"
                                   value="0" min="0" placeholder="Hangus (hari)" title="0 = tidak pernah hangus" required>
                        </div>
                        <div class="col-4">
                            <input type="number" name="code_length" class="form-control form-control-sm"
                                   value="6" min="4" max="12" placeholder="Digit" required>
                        </div>
                    </div>
                    <button class="btn btn-outline-primary btn-sm w-100">Tambah Profil</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-auto">
                <select name="batch" class="form-select form-select-sm">
                    <option value="">Semua batch</option>
                    @foreach($batches as $b)
                        <option value="{{ $b }}" @selected(request('batch') === $b)>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua status</option>
                    @foreach(['tersedia','terjual','terpakai','kadaluarsa'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Filter</button></div>
            @if(request('batch'))
                <div class="col-auto">
                    <a href="{{ url('/vouchers/print?batch=' . request('batch')) }}" target="_blank"
                       class="btn btn-sm btn-outline-success">Cetak Batch Ini</a>
                </div>
            @endif
        </form>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr><th>Kode</th><th>Profil</th><th>Agen</th><th>Harga Jual</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                    @forelse($vouchers as $v)
                        <tr>
                            <td><code class="fw-bold">{{ $v->code }}</code></td>
                            <td class="small">{{ $v->profile?->name }}</td>
                            <td class="small">{{ $v->reseller?->name ?? '—' }}</td>
                            <td class="small">{{ $v->sale_price ? 'Rp ' . number_format($v->sale_price, 0, ',', '.') : '—' }}</td>
                            <td>
                                <span class="badge bg-{{ ['tersedia'=>'primary','terjual'=>'success','terpakai'=>'secondary','kadaluarsa'=>'danger'][$v->status] }}">
                                    {{ $v->status }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($v->status === 'tersedia')
                                    <form method="POST" action="{{ url("/vouchers/{$v->id}/sold") }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success"
                                                title="Jual langsung dengan harga jual profil">Jual</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada voucher.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $vouchers->links() }}</div>
    </div>
</div>
@endsection
