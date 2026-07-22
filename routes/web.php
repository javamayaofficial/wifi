<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationSettingController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\RouterController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/dashboard'));

/*
 |--------------------------------------------------------------------------
 | Halaman publik untuk Pelanggan THRE.F.NET (tanpa login)
 |--------------------------------------------------------------------------
 | Untuk produksi, pertimbangkan signed URL agar tagihan tidak bisa ditebak.
 */
Route::get('/bayar/{username}', [PaymentController::class, 'show']);
Route::post('/bayar/{username}', [PaymentController::class, 'pay']);
Route::get('/bayar/{username}/instruksi/{orderId}', [PaymentController::class, 'instructions']);
Route::get('/bayar/{username}/status/{orderId}', [PaymentController::class, 'status']);

/*
 |--------------------------------------------------------------------------
 | Area Admin THRE.F.NET (butuh login)
 |--------------------------------------------------------------------------
 */
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pelanggan
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/create', [CustomerController::class, 'create']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/import', [CustomerController::class, 'importForm']);
    Route::post('/customers/import', [CustomerController::class, 'import']);
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit']);
    Route::put('/customers/{customer}', [CustomerController::class, 'update']);
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);
    Route::post('/customers/{customer}/toggle', [CustomerController::class, 'toggle']);

    // Paket
    Route::get('/plans', [PlanController::class, 'index']);
    Route::get('/plans/create', [PlanController::class, 'create']);
    Route::post('/plans', [PlanController::class, 'store']);
    Route::get('/plans/{plan}/edit', [PlanController::class, 'edit']);
    Route::put('/plans/{plan}', [PlanController::class, 'update']);
    Route::delete('/plans/{plan}', [PlanController::class, 'destroy']);

    // Router
    Route::get('/routers', [RouterController::class, 'index']);
    Route::get('/routers/create', [RouterController::class, 'create']);
    Route::post('/routers', [RouterController::class, 'store']);
    Route::get('/routers/{router}/edit', [RouterController::class, 'edit']);
    Route::put('/routers/{router}', [RouterController::class, 'update']);
    Route::delete('/routers/{router}', [RouterController::class, 'destroy']);
    Route::post('/routers/{router}/test', [RouterController::class, 'test']);

    // Transaksi + aktivasi manual (AC-5)
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions/{transaction}/activate', [TransactionController::class, 'activate']);

    // Pengaturan
    Route::get('/settings/payment', [PaymentSettingController::class, 'index']);
    Route::post('/settings/payment', [PaymentSettingController::class, 'update']);
    Route::get('/settings/notification', [NotificationSettingController::class, 'index']);
    Route::post('/settings/notification', [NotificationSettingController::class, 'update']);
    Route::post('/settings/notification/test', [NotificationSettingController::class, 'test']);
});

require __DIR__ . '/auth.php'; // Laravel Breeze/Fortify untuk login admin
