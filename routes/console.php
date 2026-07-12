<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('ews:hitung-harian')
    ->dailyAt('23:05')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(
        function () {
            Log::error('[Schedule] ews-hitung-harian GAGAL dijalankan');
        }
    )->onSuccess(
        function () {
            Log::info('[Schedule] ews-hitung-harian selesai dijalankan');
        }
    );
