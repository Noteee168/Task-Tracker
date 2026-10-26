<?php

use App\Models\Task;
use App\Models\User;
use App\Services\TelegramNotificationService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->user = User::first() ?? User::factory()->create([
        'name' => 'Pengelola',
        'email' => 'pengelola@example.com',
    ]);
});

test('telegram service accurately categorizes overdue, approaching, and unstarted tasks', function () {
    // 1. Overdue task (deadline 2 hari lalu, belum selesai)
    Task::create([
        'judul' => 'Tugas Terlambat',
        'prioritas' => 'tinggi',
        'tanggal_deadline' => now()->subDays(2)->toDateString(),
        'status' => 'sedang_dikerjakan',
    ]);

    // 2. Approaching task (deadline 2 hari ke depan, belum selesai)
    Task::create([
        'judul' => 'Tugas Mendekati Deadline',
        'prioritas' => 'sedang',
        'tanggal_deadline' => now()->addDays(2)->toDateString(),
        'status' => 'sedang_dikerjakan',
    ]);

    // 3. Unstarted task (deadline 7 hari ke depan, tapi belum dikerjakan)
    Task::create([
        'judul' => 'Tugas Belum Mulai',
        'prioritas' => 'rendah',
        'tanggal_deadline' => now()->addDays(7)->toDateString(),
        'status' => 'belum_dikerjakan',
    ]);

    // 4. Completed task (tidak boleh masuk laporan)
    Task::create([
        'judul' => 'Tugas Sudah Rampung',
        'prioritas' => 'tinggi',
        'tanggal_deadline' => now()->subDays(5)->toDateString(),
        'status' => 'selesai',
    ]);

    $service = new TelegramNotificationService;
    $reminderData = $service->getTasksForReminder();

    expect($reminderData['overdue'])->toHaveCount(1)
        ->and($reminderData['overdue']->first()->judul)->toBe('Tugas Terlambat')
        ->and($reminderData['approaching'])->toHaveCount(1)
        ->and($reminderData['approaching']->first()->judul)->toBe('Tugas Mendekati Deadline')
        ->and($reminderData['unstarted'])->toHaveCount(1)
        ->and($reminderData['unstarted']->first()->judul)->toBe('Tugas Belum Mulai')
        ->and($reminderData['total'])->toBe(3);
});

test('telegram service sends notification via HTTP when configured', function () {
    Config::set('services.telegram.bot_token', 'fake-bot-token');
    Config::set('services.telegram.chat_id', '123456789');

    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]], 200),
    ]);

    Task::create([
        'judul' => 'Tugas Overdue Mendesak',
        'prioritas' => 'tinggi',
        'tanggal_deadline' => now()->subDays(1)->toDateString(),
        'status' => 'belum_dikerjakan',
    ]);

    $service = new TelegramNotificationService;
    $result = $service->sendDailyTaskReminder();

    expect($result['success'])->toBeTrue()
        ->and($result['sent'])->toBeTrue();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.telegram.org/botfake-bot-token/sendMessage')
            && $request['chat_id'] === '123456789'
            && str_contains($request['text'], 'Halo Pengelola')
            && str_contains($request['text'], 'Tugas Overdue Mendesak');
    });
});

test('artisan command tasks:notify-telegram runs successfully with fake telegram response', function () {
    Config::set('services.telegram.bot_token', 'test-token');
    Config::set('services.telegram.chat_id', '987654321');

    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    Task::create([
        'judul' => 'Tugas Uji Coba Command',
        'prioritas' => 'tinggi',
        'tanggal_deadline' => now()->addDays(1)->toDateString(),
        'status' => 'belum_dikerjakan',
    ]);

    $this->artisan('tasks:notify-telegram')
        ->assertSuccessful();

    Http::assertSentCount(1);
});

test('authenticated user can trigger telegram notification from web route', function () {
    Config::set('services.telegram.bot_token', 'test-token');
    Config::set('services.telegram.chat_id', '987654321');

    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    $response = $this->actingAs($this->user)->post('/tasks/notify-telegram');

    $response->assertRedirect();
    $response->assertSessionHas('success');
});
