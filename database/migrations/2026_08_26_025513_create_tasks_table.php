<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrasi ini bertugas membuat tabel 'tasks' di database MySQL
        // Kolom-kolom di bawah menyimpan seluruh informasi tugas dari Superadmin
        Schema::create('tasks', function (Blueprint $table) {
            $table->id(); // Primary Key auto-increment
            $table->string('judul'); // Judul tugas
            $table->text('deskripsi')->nullable(); // Penjelasan detail tugas (opsional)
            $table->string('nama_klien')->nullable(); // Nama klien yang terkait dengan tugas (opsional)
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi'])->default('sedang'); // Tingkat urgensi tugas
            $table->date('tanggal_deadline'); // Batas waktu penyelesaian tugas
            $table->enum('status', ['belum_dikerjakan', 'sedang_dikerjakan', 'menunggu_antrian', 'selesai'])->default('belum_dikerjakan'); // Status pengerjaan
            $table->timestamp('notified_at')->nullable(); // Menyimpan waktu kapan email peringatan overdue dikirim agar tidak terkirim ganda
            $table->timestamps(); // Otomatis membuat created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
