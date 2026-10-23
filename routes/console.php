<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/**
 * FUNGSI FILE INI:
 * routes/console.php digunakan untuk mendefinisikan perintah console berbasis closure dan
 * mengatur penjadwalan tugas (Task Scheduling) di Laravel.
 *
 * Di server production, cron job server hanya perlu memanggil `php artisan schedule:run` setiap menit,
 * dan Laravel yang akan mengatur kapan setiap perintah di bawah ini dieksekusi secara otomatis.
 */

// Contoh command bawaan
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Penjadwalan Otomatis:
// Menjalankan pengingat harian Telegram setiap hari jam 08:00 pagi (WIB)
Schedule::command('tasks:notify-telegram')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta')
    ->description('Pengingat harian Telegram untuk tugas overdue, H-3 deadline, dan belum dikerjakan');
