@extends('layouts.admin')
@section('title', 'Pengaturan Notifikasi')

@section('actions')
    <a href="{{ url('/settings/integrations') }}" class="btn btn-outline-primary btn-sm">Tes Integrasi</a>
@endsection

@section('content')
<form method="POST" action="{{ url('/settings/notification') }}" class="mb-4">
    @csrf
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <h6 class="fw-bold mb-3">WhatsApp Provider</h6>
            <div class="mb-3">
                <label class="form-label">Provider Aktif</label>
                <select name="whatsapp_provider" class="form-select">
                    <option value="gateway" @selected(($values['whatsapp_provider'] ?? 'gateway') === 'gateway')>Gateway Sendiri</option>
                    <option value="fonnte" @selected(($values['whatsapp_provider'] ?? 'gateway') === 'fonnte')>Fonnte</option>
                </select>
                <div class="form-text">Semua notifikasi pelanggan via WhatsApp akan memakai provider yang dipilih di sini.</div>
            </div>

            <hr>

            <h6 class="fw-bold mb-3">Gateway Sendiri</h6>
            <div class="mb-3">
                <label class="form-label">Gateway URL</label>
                <input type="text" name="whatsapp_gateway_url" class="form-control"
                       value="{{ $values['whatsapp_gateway_url'] ?? '' }}" placeholder="https://your-gateway.com/send">
            </div>
            <div class="mb-3">
                <label class="form-label">API Key</label>
                <input type="password" name="whatsapp_api_key" class="form-control"
                       placeholder="{{ ($values['whatsapp_api_key'] ?? false) ? '•••• (tersimpan)' : '' }}">
            </div>

            <hr>

            <h6 class="fw-bold mb-3">Fonnte</h6>
            <div class="mb-3">
                <label class="form-label">Endpoint</label>
                <input type="text" name="fonnte_url" class="form-control"
                       value="{{ $values['fonnte_url'] ?? 'https://api.fonnte.com/send' }}" placeholder="https://api.fonnte.com/send">
            </div>
            <div class="mb-3">
                <label class="form-label">Token Fonnte</label>
                <input type="password" name="fonnte_token" class="form-control"
                       placeholder="{{ ($values['fonnte_token'] ?? false) ? '•••• (tersimpan)' : '' }}">
            </div>
            <div class="mb-0">
                <label class="form-label">Country Code</label>
                <input type="text" name="whatsapp_country_code" class="form-control"
                       value="{{ $values['whatsapp_country_code'] ?? '62' }}" placeholder="62">
                <div class="form-text">Gunakan <code>62</code> untuk nomor lokal berawalan <code>08</code>. Isi <code>0</code> bila nomor sudah selalu disimpan format internasional.</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Email — Mailketing</h6>
            <div class="mb-3">
                <label class="form-label">API Token</label>
                <input type="password" name="mailketing_api_token" class="form-control"
                       placeholder="{{ ($values['mailketing_api_token'] ?? false) ? '•••• (tersimpan)' : '' }}">
            </div>
            <div class="mb-3">
                <label class="form-label">From Name</label>
                <input type="text" name="mailketing_from_name" class="form-control"
                       value="{{ $values['mailketing_from_name'] ?? '' }}">
            </div>
            <div class="mb-3">
                <label class="form-label">From Email</label>
                <input type="email" name="mailketing_from_email" class="form-control"
                       value="{{ $values['mailketing_from_email'] ?? '' }}">
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <h6 class="fw-bold mb-1">Telegram — Alert Internal</h6>
            <p class="text-muted small">
                Untuk alert ke Anda sendiri (router down, backup gagal), bukan ke pelanggan.
            </p>
            <div class="mb-3">
                <label class="form-label">Bot Token</label>
                <input type="password" name="telegram_bot_token" class="form-control"
                       placeholder="{{ ($values['telegram_bot_token'] ?? false) ? '•••• (tersimpan)' : 'dari @BotFather' }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Chat ID</label>
                <input type="text" name="telegram_chat_id" class="form-control"
                       value="{{ $values['telegram_chat_id'] ?? '' }}" placeholder="ID grup atau chat pribadi">
            </div>
        </div>
    </div>

    <button class="btn btn-primary">Simpan</button>
</form>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Tes Kirim</h6>
        <p class="text-muted small">Tes WhatsApp akan memakai provider aktif yang tersimpan di atas.</p>
        <form method="POST" action="{{ url('/settings/notification/test') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-auto">
                <label class="form-label">Kanal</label>
                <select name="channel" class="form-select">
                    <option value="whatsapp">WhatsApp</option>
                    <option value="email">Email</option>
                    <option value="telegram">Telegram (alert internal)</option>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label">Tujuan (no. HP / email)</label>
                <input type="text" name="target" class="form-control" placeholder="kosongkan untuk Telegram">
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary">Kirim Tes</button>
            </div>
        </form>
    </div>
</div>
@endsection
