@extends('layouts.admin')
@section('title', 'Pengaturan Notifikasi')

@section('content')
<h4 class="mb-4">Pengaturan Notifikasi THRE.F.NET</h4>

<form method="POST" action="{{ url('/settings/notification') }}" class="mb-4">
    @csrf
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <h6 class="fw-bold mb-3">WhatsApp Gateway (dikembangkan sendiri)</h6>
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
