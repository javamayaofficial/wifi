<?php

use App\Http\Controllers\Api\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
 | Webhook pembayaran — TANPA CSRF (route api sudah di luar grup web).
 | DOKU  : POST https://thre.f.net/api/payment/webhook/doku
 | Moota : POST https://thre.f.net/api/payment/webhook/moota
 |
 | Catatan Laravel 11: agar file ini aktif, jalankan `php artisan install:api`
 | atau daftarkan api routing di bootstrap/app.php (->withRouting(api: ...)).
 */
Route::post('/payment/webhook/{driver}', [PaymentWebhookController::class, 'handle'])
    ->where('driver', 'doku|moota');
