<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule billing process & auto-isolir daily at 00:01
Schedule::command('billing:process')->dailyAt('00:01')->withoutOverlapping();
