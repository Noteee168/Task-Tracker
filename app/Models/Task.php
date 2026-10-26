<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Task
 *
 * FUNGSI FILE INI:
 * Model di Laravel bertindak sebagai jembatan antara aplikasi dan tabel 'tasks' di database (ORM - Object Relational Mapping).
 * Di sini kita mendefinisikan kolom yang boleh diisi (mass assignment), konversi tipe data (casts),
 * query scope untuk mempermudah filter query, serta helper method untuk tampilan badge di Blade.
 */
class Task extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nama tabel di database (secara default Laravel akan mencari jamak: 'tasks')
     */
    protected $table = 'tasks';

    /**
     * The "booted" method of the model.
     * Mengaitkan task baru secara otomatis ke akun pengelola jika belum ditentukan.
     */
    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if (! $task->user_id) {
                $task->user_id = auth()->id() ?? User::first()?->id;
            }
        });
    }

    /**
     * Kolom-kolom yang diizinkan untuk diisi secara massal (Mass Assignment Protection)
     * Ini digunakan saat memanggil Task::create($request->all()) atau $task->update(...)
     */
    protected $fillable = [
        'user_id',
        'judul',
        'deskripsi',
        'nama_klien',
        'prioritas',
        'tanggal_deadline',
        'status',
    ];

    /**
     * Atribut tambahan yang otomatis disertakan saat model dikonversi ke array / JSON
     */
    protected $appends = [
        'subtasks_count',
        'completed_subtasks_count',
        'subtasks_progress_percentage',
    ];

    /**
     * Relasi ke model User (Setiap task dimiliki oleh satu user).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke model Subtask (Setiap task dapat memiliki banyak subtasks / checklist).
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class)->orderBy('urutan')->orderBy('id');
    }

    /**
     * Accessor: Menghitung total subtask yang dimiliki task ini
     */
    public function getSubtasksCountAttribute(): int
    {
        return $this->relationLoaded('subtasks') ? $this->subtasks->count() : $this->subtasks()->count();
    }

    /**
     * Accessor: Menghitung subtask yang sudah dicentang (selesai)
     */
    public function getCompletedSubtasksCountAttribute(): int
    {
        return $this->relationLoaded('subtasks')
            ? $this->subtasks->where('is_completed', true)->count()
            : $this->subtasks()->where('is_completed', true)->count();
    }

    /**
     * Accessor: Persentase penyelesaian subtask (0 - 100%)
     */
    public function getSubtasksProgressPercentageAttribute(): int
    {
        $total = $this->subtasks_count;
        if ($total === 0) {
            return 0;
        }

        return (int) round(($this->completed_subtasks_count / $total) * 100);
    }

    /**
     * Konversi tipe data otomatis saat kolom diakses dari database
     */
    protected function casts(): array
    {
        return [
            'tanggal_deadline' => 'date',
        ];
    }

    /**
     * Helper Method: Mengecek apakah task ini sudah melewati batas waktu deadline dan belum selesai.
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'selesai') {
            return false;
        }

        // Jika deadline sebelum hari ini (kemarin atau sebelumnya)
        return $this->tanggal_deadline && $this->tanggal_deadline->startOfDay()->isBefore(today()->startOfDay());
    }

    /**
     * Helper: Menghitung berapa hari terlambat dari deadline
     */
    public function getDaysOverdueAttribute(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        return abs((int) today()->startOfDay()->diffInDays($this->tanggal_deadline->startOfDay()));
    }

    /**
     * Query Scope: Mengambil semua task yang melewati deadline dan belum selesai
     * Pemanggilan: Task::overdue()->get()
     */
    public function scopeOverdue($query)
    {
        return $query->whereDate('tanggal_deadline', '<', today())
            ->where('status', '!=', 'selesai');
    }

    /**
     * Accessor: Label yang mudah dibaca untuk status
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'belum_dikerjakan' => 'Belum Dikerjakan',
            'sedang_dikerjakan' => 'Sedang Dikerjakan',
            'menunggu_antrian' => 'Menunggu Antrian',
            'selesai' => 'Selesai',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Accessor: Class Tailwind CSS untuk warna badge status
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'belum_dikerjakan' => 'bg-slate-100 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
            'sedang_dikerjakan' => 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-900/50 dark:text-blue-300 dark:border-blue-700',
            'menunggu_antrian' => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/50 dark:text-amber-300 dark:border-amber-700',
            'selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/50 dark:text-emerald-300 dark:border-emerald-700',
            default => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }

    /**
     * Accessor: Label yang mudah dibaca untuk prioritas
     */
    public function getPrioritasLabelAttribute(): string
    {
        return match ($this->prioritas) {
            'rendah' => 'Rendah',
            'sedang' => 'Sedang',
            'tinggi' => 'Tinggi',
            default => ucfirst($this->prioritas),
        };
    }

    /**
     * Accessor: Class Tailwind CSS untuk warna badge prioritas
     */
    public function getPrioritasBadgeClassAttribute(): string
    {
        return match ($this->prioritas) {
            'rendah' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800',
            'sedang' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-800',
            'tinggi' => 'bg-rose-100 text-rose-800 border-rose-300 font-semibold dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}
