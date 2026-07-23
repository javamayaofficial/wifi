<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\MapController;
use App\Http\Controllers\Admin\ResellerController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\MikrotikController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\NotificationSettingController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\RouterController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\PortalController;
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
Route::get('/bayar/{username}/invoice/{orderId}', [InvoiceController::class, 'publicDownload']);

/*
 |--------------------------------------------------------------------------
 | Portal Pelanggan THRE.F.NET
 |--------------------------------------------------------------------------
 */
Route::get('/portal/login', [PortalAuthController::class, 'showLogin']);
Route::post('/portal/login', [PortalAuthController::class, 'login']);
Route::post('/portal/logout', [PortalAuthController::class, 'logout']);

Route::middleware('portal')->group(function () {
    Route::get('/portal', [PortalController::class, 'dashboard']);
    Route::get('/portal/invoices', [PortalController::class, 'invoices']);
    Route::get('/portal/tickets', [PortalController::class, 'tickets']);
    Route::post('/portal/tickets', [PortalController::class, 'storeTicket']);
});

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

    // MikroTik: import dari router + monitoring live
    Route::get('/mikrotik/import', [MikrotikController::class, 'importForm']);
    Route::post('/mikrotik/import', [MikrotikController::class, 'import']);
    Route::get('/mikrotik/monitor', [MikrotikController::class, 'monitor']);
    Route::post('/mikrotik/refresh', [MikrotikController::class, 'refresh']);
    Route::post('/mikrotik/disconnect', [MikrotikController::class, 'disconnect']);

    // Transaksi + aktivasi manual (AC-5)
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions/{transaction}/activate', [TransactionController::class, 'activate']);

    // Tiket gangguan (teknisi juga boleh)
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/create', [TicketController::class, 'create']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
    Route::post('/tickets/{ticket}/updates', [TicketController::class, 'addUpdate']);
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign'])
        ->middleware('role:admin');

    // Reset password portal pelanggan
    Route::post('/customers/{customer}/portal-password', [CustomerController::class, 'resetPortalPassword']);

    // Peta sebaran pelanggan
    Route::get('/map', [MapController::class, 'index']);

    // Inventory perangkat
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory', [InventoryController::class, 'store']);
    Route::put('/inventory/{inventory}', [InventoryController::class, 'update']);
    Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy']);

    // Voucher hotspot
    Route::get('/vouchers', [VoucherController::class, 'index']);
    Route::get('/vouchers/print', [VoucherController::class, 'print']);
    Route::post('/vouchers/profiles', [VoucherController::class, 'storeProfile']);
    Route::post('/vouchers/generate', [VoucherController::class, 'generate']);
    Route::post('/vouchers/sync-usage', [VoucherController::class, 'syncUsage']);
    Route::post('/vouchers/handover', [VoucherController::class, 'handOver']);
    Route::post('/vouchers/settle', [VoucherController::class, 'settle']);
    Route::post('/vouchers/{voucher}/sold', [VoucherController::class, 'markSold']);

    // Invoice / kwitansi PDF (dari dashboard)
    Route::get('/transactions/{transaction}/invoice', [InvoiceController::class, 'download']);

    // Keuangan: pengeluaran & laba rugi (owner + admin)
    Route::middleware('role:admin')->group(function () {
        Route::get('/expenses', [ExpenseController::class, 'index']);
        Route::post('/expenses', [ExpenseController::class, 'store']);
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);
        Route::get('/reports/profit-loss', [ExpenseController::class, 'profitLoss']);

        // Mitra / reseller
        Route::get('/resellers', [ResellerController::class, 'index']);
        Route::post('/resellers', [ResellerController::class, 'store']);
        Route::get('/resellers/{reseller}', [ResellerController::class, 'show']);
        Route::put('/resellers/{reseller}', [ResellerController::class, 'update']);
        Route::post('/resellers/{reseller}/transactions', [ResellerController::class, 'addTransaction']);
    });

    // Laporan tunggakan (owner, admin, kasir)
    Route::get('/reports/arrears', [ReportController::class, 'arrears'])
        ->middleware('role:admin,kasir');

    Route::get('/reports/vouchers', [ReportController::class, 'vouchers'])
        ->middleware('role:admin,kasir');

    // Audit log (owner saja)
    Route::get('/audit', [AuditLogController::class, 'index'])
        ->middleware('role:owner');

    // Pengaturan
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings/payment', [PaymentSettingController::class, 'index']);
        Route::post('/settings/payment', [PaymentSettingController::class, 'update']);
        Route::get('/settings/notification', [NotificationSettingController::class, 'index']);
        Route::post('/settings/notification', [NotificationSettingController::class, 'update']);
        Route::post('/settings/notification/test', [NotificationSettingController::class, 'test']);
    });
});

require __DIR__ . '/auth.php'; // Laravel Breeze/Fortify untuk login admin
