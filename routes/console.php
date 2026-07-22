<?php

use Illuminate\Support\Facades\Schedule;

// Scheduler THRE.F.NET: cek pelanggan expired tiap menit.
Schedule::command('threfnet:check-expired')
    ->everyMinute()
    ->withoutOverlapping();
