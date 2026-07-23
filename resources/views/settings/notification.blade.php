@extends('layouts.admin')
@section('title', 'Pengaturan Notifikasi')

@section('actions')
    <a href="{{ url('/settings/integrations') }}" class="btn btn-outline-primary btn-sm">Tes Integrasi</a>
@endsection

@section('content')
@php
    $countryCode = old('whatsapp_country_code', $values['whatsapp_country_code'] ?? '62');
    $activeProvider = old('whatsapp_provider', $values['whatsapp_provider'] ?? 'gateway');
@endphp

<form method="POST" action="{{ url('/settings/notification') }}" class="mb-4">
    @csrf
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h6 class="fw-bold mb-1">Rapikan Konfigurasi Pengiriman</h6>
                <p class="text-muted small mb-0">
                    Halaman ini khusus untuk menyimpan kredensial dan provider. Pengujian kirim dipisah ke menu
                    <span class="fw-semibold text-body">Tes Integrasi</span> agar pengaturan WA dan Email tetap bersih.
                </p>
            </div>
            <a href="{{ url('/settings/integrations') }}" class="btn btn-primary">Buka Tes Integrasi</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                        <div>
                            <h6 class="fw-bold mb-1">WhatsApp</h6>
                            <p class="text-muted small mb-0">Pilih provider aktif lalu isi kredensial yang sesuai di bagian bawah.</p>
                        </div>
                        <div class="w-100" style="max-width:320px">
                            <label class="form-label">Provider Aktif</label>
                            <select name="whatsapp_provider" class="form-select">
                                <option value="gateway" @selected($activeProvider === 'gateway')>Gateway Sendiri</option>
                                <option value="fonnte" @selected($activeProvider === 'fonnte')>Fonnte</option>
                            </select>
                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h6 class="fw-semibold mb-1">Gateway Sendiri</h6>
                                <p class="text-muted small mb-0">Pakai ini bila Anda sudah punya endpoint WA custom.</p>
                            </div>
                            @if($activeProvider === 'gateway')
                                <span class="badge text-bg-primary">Sedang aktif</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gateway URL</label>
                            <input type="url" name="whatsapp_gateway_url" class="form-control"
                                   value="{{ old('whatsapp_gateway_url', $values['whatsapp_gateway_url'] ?? '') }}"
                                   placeholder="https://your-gateway.com/send">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">API Key</label>
                            <input type="password" name="whatsapp_api_key" class="form-control"
                                   placeholder="{{ ($values['whatsapp_api_key'] ?? false) ? '•••• (tersimpan)' : 'Masukkan API key gateway' }}">
                        </div>
                    </div>

                    <div class="border rounded-3 p-3">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h6 class="fw-semibold mb-1">Fonnte</h6>
                                <p class="text-muted small mb-0">Pilih ini jika pengiriman WhatsApp pelanggan memakai Fonnte.</p>
                            </div>
                            @if($activeProvider === 'fonnte')
                                <span class="badge text-bg-success">Sedang aktif</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Endpoint Fonnte</label>
                            <input type="url" name="fonnte_url" class="form-control"
                                   value="{{ old('fonnte_url', $values['fonnte_url'] ?? 'https://api.fonnte.com/send') }}"
                                   placeholder="https://api.fonnte.com/send">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Token Fonnte</label>
                            <input type="password" name="fonnte_token" class="form-control"
                                   placeholder="{{ ($values['fonnte_token'] ?? false) ? '•••• (tersimpan)' : 'Masukkan token Fonnte' }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Format Nomor untuk Fonnte</label>
                            <select name="whatsapp_country_code" class="form-select">
                                <option value="62" @selected($countryCode === '62')>62 - nomor pelanggan disimpan lokal, mis. 08xxxxxxxxxx</option>
                                <option value="0" @selected($countryCode === '0')>0 - nomor sudah disimpan format internasional, mis. 628xxxxxxxxxx</option>
                            </select>
                            <div class="form-text">Pilihan ini lebih aman daripada input bebas, jadi tidak mudah salah format.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-1">Email</h6>
                    <p class="text-muted small mb-3">Isi identitas pengirim Mailketing yang akan dipakai untuk seluruh email sistem.</p>

                    <div class="mb-3">
                        <label class="form-label">API Token</label>
                        <input type="password" name="mailketing_api_token" class="form-control"
                               placeholder="{{ ($values['mailketing_api_token'] ?? false) ? '•••• (tersimpan)' : 'Masukkan API token Mailketing' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">From Name</label>
                        <input type="text" name="mailketing_from_name" class="form-control"
                               value="{{ old('mailketing_from_name', $values['mailketing_from_name'] ?? '') }}"
                               placeholder="THRE.F.NET">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">From Email</label>
                        <input type="email" name="mailketing_from_email" class="form-control"
                               value="{{ old('mailketing_from_email', $values['mailketing_from_email'] ?? '') }}"
                               placeholder="billing@domainanda.com">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="fw-bold mb-1">Telegram Internal</h6>
                    <p class="text-muted small mb-3">
                        Dipakai untuk alert internal seperti router down, backup gagal, atau error sistem. Tidak dikirim ke pelanggan.
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Bot Token</label>
                        <input type="password" name="telegram_bot_token" class="form-control"
                               placeholder="{{ ($values['telegram_bot_token'] ?? false) ? '•••• (tersimpan)' : 'Dari @BotFather' }}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Chat ID</label>
                        <input type="text" name="telegram_chat_id" class="form-control"
                               value="{{ old('telegram_chat_id', $values['telegram_chat_id'] ?? '') }}"
                               placeholder="ID grup atau chat pribadi">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="fw-bold mb-1">Template Pengingat Masa Aktif</h6>
                    <p class="text-muted small mb-3">
                        Notifikasi ini dikirim otomatis setiap hari mulai H-7 sampai H-1 sebelum masa aktif pelanggan berakhir.
                        Teks default sudah disediakan dan bisa Anda ubah kapan saja.
                    </p>

                    <div class="mb-3">
                        <label class="form-label">Teks Notifikasi Default</label>
                        <textarea name="reminder_h7_template" class="form-control" rows="8" placeholder="Tulis template pengingat di sini...">{{ old('reminder_h7_template', $values['reminder_h7_template'] ?? '') }}</textarea>
                    </div>

                    <div class="rounded-3 border p-3 small bg-light-subtle">
                        <div class="fw-semibold mb-2">Placeholder yang bisa dipakai</div>
                        <div class="d-flex flex-wrap gap-2">
                            <code>{customer_name}</code>
                            <code>{plan_name}</code>
                            <code>{expired_date}</code>
                            <code>{days_left}</code>
                            <code>{amount}</code>
                            <code>{payment_link}</code>
                            <code>{username}</code>
                            <code>{company_name}</code>
                        </div>
                        <div class="form-text mt-2 mb-0">
                            Jika dikosongkan, sistem akan kembali memakai template bawaan THRE.F.NET.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-3">
        <button class="btn btn-primary px-4">Simpan Pengaturan</button>
    </div>
</form>
@endsection
