<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service: TelegramNotificationService
 *
 * Layanan untuk mengirimkan notifikasi dan rekap tugas ke Telegram Bot Superadmin.
 * Menggunakan Telegram Bot API sendMessage dengan format HTML.
 */
class TelegramNotificationService
{
    protected ?string $botToken;

    protected ?string $chatId;

    protected bool $sslVerify;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
        $this->sslVerify = filter_var(config('services.telegram.ssl_verify', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Memeriksa apakah konfigurasi bot token dan chat ID sudah terisi.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->botToken) && ! empty($this->chatId);
    }

    /**
     * Mengirimkan pesan teks kustom ke Telegram.
     */
    public function sendMessage(string $text): array
    {
        if (! $this->isConfigured()) {
            $msg = 'Telegram Bot Token atau Chat ID belum dikonfigurasi di file .env.';
            Log::warning($msg);

            return [
                'success' => false,
                'message' => $msg,
            ];
        }

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

            $httpClient = Http::timeout(10);
            if (! $this->sslVerify) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->post($url, [
                'chat_id' => $this->chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful()) {
                Log::info('Notifikasi Telegram berhasil dikirim.');

                return [
                    'success' => true,
                    'message' => 'Notifikasi Telegram berhasil dikirim.',
                    'response' => $response->json(),
                ];
            }

            $errorMsg = 'Telegram API error: '.$response->body();
            Log::error($errorMsg);

            return [
                'success' => false,
                'message' => $errorMsg,
            ];
        } catch (\Throwable $e) {
            $errorMsg = 'Gagal menghubungi Telegram Bot API: '.$e->getMessage();
            Log::error($errorMsg);

            return [
                'success' => false,
                'message' => $errorMsg,
            ];
        }
    }

    /**
     * Mengambil kumpulan tugas yang perlu diingatkan:
     * 1. Status Overdue (melewati deadline dan belum selesai)
     * 2. Mendekati Deadline (H-3 s/d hari ini dan belum selesai)
     * 3. Belum Dikerjakan (status 'belum_dikerjakan')
     */
    public function getTasksForReminder(?User $user = null): array
    {
        $today = today();
        $threeDaysLater = today()->addDays(3);

        $baseQuery = $user ? $user->tasks() : Task::query();

        // 1. Tugas Overdue (tenggat waktu sebelum hari ini & status != selesai)
        $overdueTasks = (clone $baseQuery)->where('status', '!=', 'selesai')
            ->whereDate('tanggal_deadline', '<', $today)
            ->orderBy('tanggal_deadline', 'asc')
            ->get();

        // 2. Tugas Mendekati Deadline (H-3 s/d hari ini & status != selesai)
        $approachingTasks = (clone $baseQuery)->where('status', '!=', 'selesai')
            ->whereDate('tanggal_deadline', '>=', $today)
            ->whereDate('tanggal_deadline', '<=', $threeDaysLater)
            ->orderBy('tanggal_deadline', 'asc')
            ->get();

        // 3. Tugas Belum Dikerjakan lainnya (deadline > H+3 namun statusnya masih 'belum_dikerjakan')
        $unstartedTasks = (clone $baseQuery)->where('status', 'belum_dikerjakan')
            ->whereDate('tanggal_deadline', '>', $threeDaysLater)
            ->orderBy('tanggal_deadline', 'asc')
            ->get();

        return [
            'overdue' => $overdueTasks,
            'approaching' => $approachingTasks,
            'unstarted' => $unstartedTasks,
            'total' => $overdueTasks->count() + $approachingTasks->count() + $unstartedTasks->count(),
        ];
    }

    /**
     * Mengirimkan laporan harian tugas ke Telegram.
     */
    public function sendDailyTaskReminder(bool $forceSendIfEmpty = false, ?User $user = null): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Telegram Bot Token atau Chat ID belum disetel di file .env (TELEGRAM_BOT_TOKEN & TELEGRAM_CHAT_ID).',
            ];
        }

        $reminderData = $this->getTasksForReminder($user);
        $totalCount = $reminderData['total'];

        if ($totalCount === 0 && ! $forceSendIfEmpty) {
            return [
                'success' => true,
                'message' => 'Tidak ada tugas yang overdue, mendekati deadline (H-3), atau belum dikerjakan. Tidak ada notifikasi yang dikirim.',
                'sent' => false,
            ];
        }

        $message = $this->formatReminderMessage($reminderData);

        $result = $this->sendMessage($message);
        $result['task_count'] = $totalCount;
        $result['sent'] = $result['success'];

        return $result;
    }

    /**
     * Memformat teks HTML notifikasi Telegram agar rapi dan informatif.
     */
    protected function formatReminderMessage(array $data): string
    {
        $todayFormatted = Carbon::now()->translatedFormat('l, d F Y');
        $dashboardUrl = url('/dashboard');

        $html = "📋 <b>PENGINGAT TUGAS HARIAN - TASK TRACKER</b>\n";
        $html .= "📅 <i>{$todayFormatted}</i>\n\n";

        if ($data['total'] === 0) {
            $html .= "✅ <b>Semua tugas dalam kondisi aman!</b>\n";
            $html .= "Tidak ada tugas yang overdue, mendekati deadline (H-3), atau tertunda.\n\n";
            $html .= "🔗 <a href=\"{$dashboardUrl}\">Buka Dashboard</a>";

            return $html;
        }

        $html .= "Halo Superadmin, terdapat <b>{$data['total']} tugas</b> yang memerlukan perhatian:\n\n";

        // 1. Kategori Overdue
        if ($data['overdue']->isNotEmpty()) {
            $html .= "🚨 <b>TUGAS OVERDUE (TERLAMBAT) [{$data['overdue']->count()}]:</b>\n";
            foreach ($data['overdue']->take(10) as $index => $task) {
                $num = $index + 1;
                $days = $task->days_overdue;
                $klien = $task->nama_klien ? " (Klien: <i>{$task->nama_klien}</i>)" : '';
                $deadline = $task->tanggal_deadline?->translatedFormat('d M Y');
                $prioritas = ucfirst($task->prioritas);

                $html .= "{$num}. <b>{$task->judul}</b>{$klien}\n";
                $html .= "   ⚠️ Deadline: {$deadline} (Lewat {$days} hari)\n";
                $html .= "   📌 Prioritas: {$prioritas} | Status: {$task->status_label}\n";
            }
            if ($data['overdue']->count() > 10) {
                $remaining = $data['overdue']->count() - 10;
                $html .= "   <i>...dan {$remaining} tugas overdue lainnya</i>\n";
            }
            $html .= "\n";
        }

        // 2. Kategori Mendekati Deadline (H-3 s/d Hari ini)
        if ($data['approaching']->isNotEmpty()) {
            $html .= "⏳ <b>MENDEKATI DEADLINE (H-3 s.d. Hari Ini) [{$data['approaching']->count()}]:</b>\n";
            foreach ($data['approaching']->take(10) as $index => $task) {
                $num = $index + 1;
                $klien = $task->nama_klien ? " (Klien: <i>{$task->nama_klien}</i>)" : '';
                $deadline = $task->tanggal_deadline?->translatedFormat('d M Y');
                $diffDays = (int) today()->diffInDays($task->tanggal_deadline, false);
                $diffText = $diffDays === 0 ? 'Hari ini!' : "Sisa {$diffDays} hari";
                $prioritas = ucfirst($task->prioritas);

                $html .= "{$num}. <b>{$task->judul}</b>{$klien}\n";
                $html .= "   ⏱️ Deadline: {$deadline} ({$diffText})\n";
                $html .= "   📌 Prioritas: {$prioritas} | Status: {$task->status_label}\n";
            }
            if ($data['approaching']->count() > 10) {
                $remaining = $data['approaching']->count() - 10;
                $html .= "   <i>...dan {$remaining} tugas mendekati deadline lainnya</i>\n";
            }
            $html .= "\n";
        }

        // 3. Kategori Belum Dikerjakan lainnya
        if ($data['unstarted']->isNotEmpty()) {
            $html .= "📝 <b>BELUM DIKERJAKAN [{$data['unstarted']->count()}]:</b>\n";
            foreach ($data['unstarted']->take(10) as $index => $task) {
                $num = $index + 1;
                $klien = $task->nama_klien ? " (Klien: <i>{$task->nama_klien}</i>)" : '';
                $deadline = $task->tanggal_deadline?->translatedFormat('d M Y');
                $prioritas = ucfirst($task->prioritas);

                $html .= "{$num}. <b>{$task->judul}</b>{$klien}\n";
                $html .= "   📅 Deadline: {$deadline} | Prioritas: {$prioritas}\n";
            }
            if ($data['unstarted']->count() > 10) {
                $remaining = $data['unstarted']->count() - 10;
                $html .= "   <i>...dan {$remaining} tugas belum dikerjakan lainnya</i>\n";
            }
            $html .= "\n";
        }

        $html .= "👉 Segera tindak lanjuti di <a href=\"{$dashboardUrl}\">Dashboard Task Tracker</a>";

        return $html;
    }
}
