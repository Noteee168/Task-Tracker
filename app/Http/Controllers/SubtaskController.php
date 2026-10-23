<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    /**
     * Mengambil daftar subtask untuk tugas tertentu (API).
     */
    public function index(Request $request, Task $task): JsonResponse
    {
        abort_if($task->user_id !== auth()->id(), 403, 'Anda tidak memiliki akses ke tugas ini.');

        return response()->json([
            'success' => true,
            'task_id' => $task->id,
            'task_title' => $task->judul,
            'subtasks' => $task->subtasks,
            'progress' => [
                'total' => $task->subtasks_count,
                'completed' => $task->completed_subtasks_count,
                'percentage' => $task->subtasks_progress_percentage,
            ],
        ]);
    }

    /**
     * Menyimpan subtask baru untuk tugas tertentu.
     */
    public function store(Request $request, Task $task): JsonResponse|RedirectResponse
    {
        abort_if($task->user_id !== auth()->id(), 403, 'Anda tidak memiliki akses ke tugas ini.');

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
        ], [
            'judul.required' => 'Judul subtask wajib diisi.',
            'judul.max' => 'Judul subtask maksimal 255 karakter.',
        ]);

        $maxUrutan = (int) ($task->subtasks()->max('urutan') ?? 0);

        $subtask = $task->subtasks()->create([
            'judul' => trim($validated['judul']),
            'is_completed' => false,
            'urutan' => $maxUrutan + 1,
        ]);

        // Muat ulang relasi untuk menghitung progres terbaru
        $task->unsetRelation('subtasks');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Subtask berhasil ditambahkan!',
                'subtask' => $subtask,
                'progress' => [
                    'total' => $task->subtasks_count,
                    'completed' => $task->completed_subtasks_count,
                    'percentage' => $task->subtasks_progress_percentage,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Subtask berhasil ditambahkan!');
    }

    /**
     * Mengubah status centang (selesai / belum selesai) pada subtask.
     */
    public function toggle(Request $request, Subtask $subtask): JsonResponse|RedirectResponse
    {
        $task = $subtask->task;
        abort_if($task->user_id !== auth()->id(), 403, 'Anda tidak memiliki akses ke tugas ini.');

        $subtask->update([
            'is_completed' => ! $subtask->is_completed,
        ]);

        // Muat ulang relasi task untuk kalkulasi progres terbaru
        $task->unsetRelation('subtasks');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $subtask->is_completed ? 'Subtask ditandai selesai!' : 'Subtask ditandai belum selesai!',
                'subtask' => $subtask,
                'progress' => [
                    'total' => $task->subtasks_count,
                    'completed' => $task->completed_subtasks_count,
                    'percentage' => $task->subtasks_progress_percentage,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Status subtask diperbarui!');
    }

    /**
     * Menghapus subtask tertentu.
     */
    public function destroy(Request $request, Subtask $subtask): JsonResponse|RedirectResponse
    {
        $task = $subtask->task;
        abort_if($task->user_id !== auth()->id(), 403, 'Anda tidak memiliki akses ke tugas ini.');

        $subtask->delete();

        // Muat ulang relasi task untuk kalkulasi progres terbaru
        $task->unsetRelation('subtasks');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Subtask berhasil dihapus!',
                'progress' => [
                    'total' => $task->subtasks_count,
                    'completed' => $task->completed_subtasks_count,
                    'percentage' => $task->subtasks_progress_percentage,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Subtask berhasil dihapus!');
    }
}
