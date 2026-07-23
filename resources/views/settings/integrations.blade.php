@extends('layouts.admin')
@section('title', 'Tes Integrasi')

@section('actions')
    <a href="{{ url('/settings/notification') }}" class="btn btn-outline-primary btn-sm">Kembali ke Pengaturan</a>
@endsection

@section('content')
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h6 class="fw-bold mb-1">Uji Pengiriman Tanpa Mengubah Konfigurasi</h6>
            <p class="text-muted small mb-0">
                Pakai halaman ini untuk memastikan WhatsApp, Email, dan Telegram benar-benar bisa mengirim.
            </p>
        </div>
        <div class="text-muted small">
            Provider WA aktif:
            <span class="fw-semibold text-body">{{ strtoupper($values['whatsapp_provider'] ?? 'gateway') }}</span>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-1">Tes WhatsApp</h6>
                <p class="text-muted small mb-3">Kirim pesan percobaan ke nomor Anda sendiri atau nomor admin lain.</p>

                <form method="POST" action="{{ url('/settings/integrations/test') }}" class="row g-2">
                    @csrf
                    <input type="hidden" name="channel" value="whatsapp">
                    <div class="col-12">
                        <label class="form-label">Nomor Tujuan</label>
                        <input type="text" name="target" class="form-control"
                               value="{{ old('target', $values['whatsapp_target'] ?? '') }}"
                               placeholder="08xxxxxxxxxx atau 628xxxxxxxxxx">
                        <div class="form-text">Nomor ini akan dikirim dengan provider aktif yang tersimpan di pengaturan.</div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary w-100">Kirim Tes WhatsApp</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-1">Tes Email</h6>
                <p class="text-muted small mb-3">
                    Kirim email percobaan lewat Mailketing untuk memastikan token, sender, dan alamat pengirim sudah benar.
                </p>

                <form method="POST" action="{{ url('/settings/integrations/test') }}" class="row g-2">
                    @csrf
                    <input type="hidden" name="channel" value="email">
                    <div class="col-12">
                        <label class="form-label">Email Tujuan</label>
                        <input type="email" name="target" class="form-control"
                               value="{{ old('target', $values['email_target'] ?? '') }}"
                               placeholder="admin@domain.com">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary w-100">Kirim Tes Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h6 class="fw-bold mb-1">Tes Telegram Internal</h6>
                    <p class="text-muted small mb-0">Dipakai untuk alert internal router, backup, dan error sistem.</p>
                </div>
                <form method="POST" action="{{ url('/settings/integrations/test') }}">
                    @csrf
                    <input type="hidden" name="channel" value="telegram">
                    <button class="btn btn-outline-primary">Kirim Tes Telegram</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
