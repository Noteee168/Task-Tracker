<?php

namespace App\Models;

use Database\Factories\SubtaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subtask extends Model
{
    /** @use HasFactory<SubtaskFactory> */
    use HasFactory;

    protected $table = 'subtasks';

    protected $fillable = [
        'task_id',
        'judul',
        'is_completed',
        'urutan',
    ];

    /**
     * Konversi tipe data otomatis
     */
    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    /**
     * Relasi ke Task induk
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
