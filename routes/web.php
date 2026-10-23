<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/**
 * FUNGSI FILE INI:
 * routes/web.php adalah tempat mendaftarkan semua alamat URL (Route) berbasis web di Laravel.
 * Setiap route mengarahkan URL tertentu ke Controller atau View yang sesuai,
 * serta menerapkan middleware seperti 'auth' untuk melindungi rute agar hanya bisa diakses setelah login.
 */

// Halaman utama: jika belum login langsung redirect ke login, jika sudah login redirect ke dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Route yang dilindungi middleware 'auth' (Hanya Superadmin yang sudah login)
Route::middleware(['auth'])->group(function () {
    // Dashboard Utama: Menampilkan ringkasan dan tabel tasks
    Route::get('/dashboard', [TaskController::class, 'index'])->name('dashboard');

    // Halaman Analitik & Grafik Visualisasi Tugas
    Route::get('/analytics', [TaskController::class, 'analytics'])->name('analytics');

    // Halaman Kalender Deadline Tugas
    Route::get('/calendar', [TaskController::class, 'calendar'])->name('calendar');

    // Halaman Tong Sampah & Pemulihan Tugas (Soft Deletes)
    Route::get('/tasks/trash', [TaskController::class, 'trash'])->name('tasks.trash');
    Route::patch('/tasks/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');
    Route::delete('/tasks/{id}/force-delete', [TaskController::class, 'forceDelete'])->name('tasks.force-delete');
    Route::post('/tasks/trash/empty', [TaskController::class, 'emptyTrash'])->name('tasks.empty-trash');

    // Resource CRUD Lengkap untuk Task (index, create, store, edit, update, destroy)
    Route::resource('tasks', TaskController::class);

    // Manajemen Subtasks / Checklist
    Route::get('/tasks/{task}/subtasks', [SubtaskController::class, 'index'])->name('subtasks.index');
    Route::post('/tasks/{task}/subtasks', [SubtaskController::class, 'store'])->name('subtasks.store');
    Route::patch('/subtasks/{subtask}/toggle', [SubtaskController::class, 'toggle'])->name('subtasks.toggle');
    Route::delete('/subtasks/{subtask}', [SubtaskController::class, 'destroy'])->name('subtasks.destroy');

    // Update Status Tugas secara Cepat (Kanban Drag & Drop / Quick Move)
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');

    // Kirim Notifikasi Rekap Tugas ke Telegram secara Instan
    Route::post('/tasks/notify-telegram', [TaskController::class, 'notifyTelegram'])->name('tasks.notify-telegram');

    // Profile Settings (bawaan Laravel Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route autentikasi (login, register, logout, dll bawaan Breeze)
require __DIR__.'/auth.php';
