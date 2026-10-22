<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Shortcut command khusus untuk cek & eksekusi isolir tagihan jatuh tempo
Artisan::command('billing:isolir {--dry-run : Simulasi pengecekan isolir tanpa mengubah database}', function () {
    $this->call('billing:process', [
        '--only-isolir' => true,
        '--dry-run' => (bool) $this->option('dry-run'),
    ]);
})->purpose('Cek tagihan jatuh tempo dan eksekusi isolir FreeRADIUS');

// Schedule billing process & auto-isolir:
// 1. Proses generate tagihan & isolir harian setiap tengah malam (00:01)
Schedule::command('billing:process')->dailyAt('00:01')->withoutOverlapping();

// 2. Pemeriksaan isolir tambahan pada siang hari (12:00)
Schedule::command('billing:process --only-isolir')->dailyAt('12:00')->withoutOverlapping();
