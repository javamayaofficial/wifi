@extends('layouts.admin')
@section('title', 'Tes Integrasi')

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Tes WhatsApp</h6>
                        <p class="text-muted small mb-0">
                            Menggunakan provider aktif:
                            <span class="fw-semibold text-body">{{ strtoupper($values['whatsapp_provider'] ?? 'gateway') }}</span>
                        </p>
                    </div>
                    <a href="{{ url('/settings/notification') }}" class="btn btn-sm btn-outline-secondary">Atur Provider</a>
                </div>

                <form method="POST" action="{{ url('/settings/integrations/test') }}" class="row g-2">
                    @csrf
                    <input type="hidden" name="channel" value="whatsapp">
                    <div class="col-12">
                        <label class="form-label">Nomor Tujuan</label>
                        <input type="text" name="target" class="form-control"
                               value="{{ old('target', $values['whatsapp_target'] ?? '') }}"
                               placeholder="08xxxxxxxxxx atau 628xxxxxxxxxx">
                        <div class="form-text">Cocok untuk tes Fonnte maupun gateway sendiri.</div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary">Kirim Tes WhatsApp</button>
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
                    Kirim email percobaan lewat Mailketing untuk memastikan token dan sender aktif.
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
                        <button class="btn btn-primary">Kirim Tes Email</button>
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
                    <p class="text-muted small mb-0">Tetap tersedia untuk alert internal router, backup, dan error sistem.</p>
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
