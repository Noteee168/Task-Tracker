<?php

namespace App\Console\Commands;

use App\Services\TelegramNotificationService;
use Illuminate\Console\Command;

/**
 * Command: tasks:notify-telegram
 *
 * Mengirimkan laporan harian tugas (overdue, H-3 deadline, dan belum dikerjakan)
 * ke Telegram Superadmin secara terjadwal atau saat dipanggil manual.
 */
class SendDailyTelegramNotification extends Command
{
    /**
     * Nama dan signature perintah artisan
     *
     * @var string
     */
    protected $signature = 'tasks:notify-telegram 
                            {--test : Uji coba kirim pesan tes ke bot Telegram untuk memverifikasi token dan chat ID}
                            {--force : Paksa kirim pesan rekap meskipun tidak ada tugas yang perlu dilaporkan}';

    /**
     * Deskripsi perintah console
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi rekap tugas harian (overdue, H-3 deadline, belum dikerjakan) ke Telegram';

    /**
     * Eksekusi perintah console command.
     */
    public function handle(TelegramNotificationService $telegramService): int
    {
        $this->info('🤖 Memeriksa konfigurasi Telegram Bot...');

        if (! $telegramService->isConfigured()) {
            $this->error('❌ TELEGRAM_BOT_TOKEN atau TELEGRAM_CHAT_ID belum diisi di file .env.');
            $this->line('👉 Silakan tambahkan konfigurasi berikut di file .env Anda:');
            $this->line('   TELEGRAM_BOT_TOKEN=your_bot_token_here');
            $this->line('   TELEGRAM_CHAT_ID=your_chat_id_here');

            return self::FAILURE;
        }

        // Mode Uji Coba (--test)
        if ($this->option('test')) {
            $this->info('📤 Mengirim pesan uji coba ke Telegram...');
            $testMsg = "✅ <b>Uji Coba Notifikasi Telegram Task Tracker Berhasil!</b>\n"
                     .'Waktu server: '.now()->translatedFormat('d F Y, H:i:s')."\n"
                     .'Bot Telegram Anda siap menerima laporan tugas harian.';

            $res = $telegramService->sendMessage($testMsg);

            if ($res['success']) {
                $this->info('🎉 '.$res['message']);

                return self::SUCCESS;
            }

            $this->error('❌ '.$res['message']);

            return self::FAILURE;
        }

        // Mode Rekap Harian
        $this->info('🔍 Mengumpulkan tugas overdue, H-3 deadline, dan belum dikerjakan...');
        $force = (bool) $this->option('force');

        $res = $telegramService->sendDailyTaskReminder($force);

        if (! $res['success']) {
            $this->error('❌ '.$res['message']);

            return self::FAILURE;
        }

        if (isset($res['sent']) && ! $res['sent']) {
            $this->line('ℹ️ '.$res['message']);

            return self::SUCCESS;
        }

        $this->info("✅ Notifikasi Telegram berhasil terkirim! (Total tugas terpantau: {$res['task_count']})");

        return self::SUCCESS;
    }
}
