@extends('layouts.admin')
@section('title', 'Import Pelanggan')

@section('content')
<h4 class="mb-4">Import Pelanggan THRE.F.NET dari Excel</h4>

<div class="card shadow-sm border-0" style="max-width:720px">
    <div class="card-body">
        <p class="mb-2">Format kolom pada baris pertama (header):</p>
        <pre class="bg-light p-3 rounded small mb-3">name | username | password | plan | router | expired_date | status | phone | email</pre>
        <ul class="small text-muted">
            <li>Kolom <b>plan</b> dan <b>router</b> diisi <b>nama</b> paket/router (harus sudah terdaftar).</li>
            <li><b>expired_date</b> format YYYY-MM-DD.</li>
            <li><b>status</b>: new, active, isolated, atau suspended.</li>
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
