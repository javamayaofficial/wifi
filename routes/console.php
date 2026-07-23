<?php

use Illuminate\Support\Facades\Schedule;

/*
 | Scheduler THRE.F.NET
 | Cron server cukup satu baris:
 |   * * * * * cd /var/www/threfnet && php artisan schedule:run >> /dev/null 2>&1
 */

// Isolir pelanggan yang lewat jatuh tempo.
Schedule::command('threfnet:check-expired')
    ->everyMinute()
    ->withoutOverlapping();

// Reminder berjenjang H-7, H-3, H-1, H+1 (sekali sehari pagi hari).
Schedule::command('threfnet:send-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping();

// Pantau kesehatan router + alert Telegram bila turun/pulih.
Schedule::command('threfnet:check-routers')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Backup database harian (simpan 14 file terakhir).
Schedule::command('threfnet:backup --keep=14')
    ->dailyAt('02:00')
    ->withoutOverlapping();

// Backup konfigurasi MikroTik (mingguan).
Schedule::command('threfnet:backup-routers --keep=10')
    ->weeklyOn(1, '03:00')
    ->withoutOverlapping();

// Sinkronkan pemakaian voucher hotspot dari router (tiap jam).
Schedule::command('threfnet:sync-vouchers')
    ->hourly()
    ->withoutOverlapping();

// Tandai voucher lama sebagai kadaluarsa (harian).
Schedule::command('threfnet:expire-vouchers')
    ->dailyAt('01:00')
    ->withoutOverlapping();
