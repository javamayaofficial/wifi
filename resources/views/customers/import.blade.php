@extends('layouts.admin')
@section('title', 'Import Pelanggan')

@section('content')
<div class="card shadow-sm border-0" style="max-width:720px">
    <div class="card-body">
        <p class="mb-2">Sistem sekarang akan mencoba membaca berbagai format header secara otomatis.</p>
        <pre class="bg-light p-3 rounded small mb-3">name | nama | customer_name
username | user | pppoe | mikrotik_id
plan | paket | profile
router | server | nas
expired_date | expire | jatuh_tempo
phone | whatsapp | no_hp
address | alamat
national_id_number | nik | no_ktp</pre>
        <ul class="small text-muted">
            <li>Kolom tambahan di luar kebutuhan sistem akan diabaikan otomatis.</li>
            <li><b>plan</b> bisa diisi nama paket, profile MikroTik, atau ID paket yang sudah ada.</li>
            <li><b>router</b> bisa diisi nama router, IP router, atau ID router yang sudah ada.</li>
            <li><b>expired_date</b> bisa format Excel date, YYYY-MM-DD, atau tanggal biasa yang bisa dikenali sistem.</li>
            <li><b>status</b> fleksibel: aktif/active, isolir/isolated, suspended, baru/new akan dinormalisasi otomatis.</li>
            <li>Baris yang gagal divalidasi akan dilewati dan dilaporkan; baris valid tetap tersimpan.</li>
        </ul>

        <form method="POST" action="{{ url('/customers/import') }}" enctype="multipart/form-data" class="mt-3">
            @csrf
            <div class="mb-3">
                <label class="form-label">File Excel / CSV</label>
                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
            </div>
            <button class="btn btn-primary">Import</button>
            <a href="{{ url('/customers') }}" class="btn btn-link">Batal</a>
        </form>
    </div>
</div>
@endsection
