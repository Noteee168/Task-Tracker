<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Services\TelegramNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller: TaskController
 *
 * FUNGSI FILE INI:
 * Controller adalah pusat logika aplikasi (C dalam MVC).
 * Controller ini menangani seluruh aksi CRUD (Create, Read, Update, Delete) untuk Task:
 * 1. index()   -> Menampilkan daftar tugas di dashboard, menghitung statistik, filter pencarian, dan melakukan real-time check task overdue.
 * 2. create()  -> Menampilkan formulir tambah tugas baru.
 * 3. store()   -> Memvalidasi input dan menyimpan data tugas baru ke database MySQL.
 * 4. edit()    -> Menampilkan formulir untuk mengedit data tugas yang sudah ada.
 * 5. update()  -> Memvalidasi input dan memperbarui data tugas di database.
 * 6. destroy() -> Menghapus tugas dari database.
 */
class TaskController extends Controller
{
    /**
     * Menampilkan dashboard utama dengan daftar task, ringkasan statistik, dan real-time overdue check.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // ---------------------------------------------------------------------
        // 1. STATISTIK RINGKAS (Untuk kartu ringkasan di bagian atas dashboard)
        // ---------------------------------------------------------------------
        $stats = [
            'total' => $user->tasks()->count(),
            'belum_dikerjakan' => $user->tasks()->where('status', 'belum_dikerjakan')->count(),
            'sedang_dikerjakan' => $user->tasks()->where('status', 'sedang_dikerjakan')->count(),
            'menunggu_antrian' => $user->tasks()->where('status', 'menunggu_antrian')->count(),
            'selesai' => $user->tasks()->where('status', 'selesai')->count(),
            'overdue' => $user->tasks()->overdue()->count(),
            'trashed' => $user->tasks()->onlyTrashed()->count(),
        ];

        // ---------------------------------------------------------------------
        // 2. QUERY BUILDER & FILTER TUGAS
        // ---------------------------------------------------------------------
        $query = $user->tasks()->with('subtasks');

        // Filter berdasarkan kata kunci pencarian (judul, nama klien, deskripsi)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('nama_klien', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $statusFilter = $request->input('status');
            if ($statusFilter === 'overdue') {
                // Filter khusus untuk menampilkan hanya yang overdue
                $query->overdue();
            } elseif (in_array($statusFilter, ['belum_dikerjakan', 'sedang_dikerjakan', 'menunggu_antrian', 'selesai'])) {
                $query->where('status', $statusFilter);
            }
        }

        // Filter berdasarkan prioritas
        if ($request->filled('prioritas') && in_array($request->input('prioritas'), ['rendah', 'sedang', 'tinggi'])) {
            $query->where('prioritas', $request->input('prioritas'));
        }

        // Urutan default: Task yang overdue & prioritas tinggi diutamakan, disusul tanggal deadline terdekat
        $todayString = today()->toDateString();
        $tasks = $query->orderByRaw("
            CASE 
                WHEN status != 'selesai' AND tanggal_deadline < '{$todayString}' THEN 1
                WHEN status != 'selesai' THEN 2
                ELSE 3
            END ASC
        ")
            ->orderByRaw("
                CASE prioritas
                    WHEN 'tinggi' THEN 1
                    WHEN 'sedang' THEN 2
                    WHEN 'rendah' THEN 3
                    ELSE 4
                END ASC
            ")
            ->orderBy('tanggal_deadline', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // ---------------------------------------------------------------------
        // 4. DATA UNTUK PAPAN KANBAN (Dikelompokkan per status pengerjaan)
        // ---------------------------------------------------------------------
        $kanbanTasks = (clone $query)->orderBy('tanggal_deadline', 'asc')->get()->groupBy('status');

        // ---------------------------------------------------------------------
        // 5. DATA GRAFIK UNTUK VISUALISASI INTERAKTIF
        // ---------------------------------------------------------------------
        $chartData = $this->getChartData($user, $stats);

        return view('dashboard', compact('tasks', 'stats', 'chartData', 'kanbanTasks'));
    }

    /**
     * Menampilkan halaman analitik & grafik visualisasi tugas secara mendalam.
     */
    public function analytics(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'total' => $user->tasks()->count(),
            'belum_dikerjakan' => $user->tasks()->where('status', 'belum_dikerjakan')->count(),
            'sedang_dikerjakan' => $user->tasks()->where('status', 'sedang_dikerjakan')->count(),
            'menunggu_antrian' => $user->tasks()->where('status', 'menunggu_antrian')->count(),
            'selesai' => $user->tasks()->where('status', 'selesai')->count(),
            'overdue' => $user->tasks()->overdue()->count(),
        ];

        $chartData = $this->getChartData($user, $stats);

        // Tugas mendesak (overdue atau deadline <= 3 hari ke depan dan belum selesai)
        $urgentTasks = $user->tasks()->where('status', '!=', 'selesai')
            ->whereDate('tanggal_deadline', '<=', today()->addDays(3))
            ->orderBy('tanggal_deadline', 'asc')
            ->limit(5)
            ->get();

        $todayStr = today()->toDateString();
        // Klien dengan tugas terbanyak dan rinciannya
        $clientsSummary = $user->tasks()->whereNotNull('nama_klien')
            ->where('nama_klien', '!=', '')
            ->selectRaw("nama_klien, count(*) as total_tasks, sum(case when status = 'selesai' then 1 else 0 end) as completed_tasks, sum(case when status != 'selesai' and tanggal_deadline < '{$todayStr}' then 1 else 0 end) as overdue_tasks")
            ->groupBy('nama_klien')
            ->orderByDesc('total_tasks')
            ->limit(8)
            ->get();

        return view('analytics', compact('stats', 'chartData', 'urgentTasks', 'clientsSummary'));
    }

    /**
     * Menampilkan tampilan kalender deadline tugas yang interaktif.
     */
    public function calendar(Request $request): View
    {
        $user = $request->user();

        // Ambil seluruh tugas pengguna untuk dipetakan ke dalam kalender
        $tasks = $user->tasks()
            ->with('subtasks')
            ->orderBy('tanggal_deadline', 'asc')
            ->get()
            ->map(function (Task $task) {
                return [
                    'id' => $task->id,
                    'judul' => $task->judul,
                    'deskripsi' => $task->deskripsi,
                    'nama_klien' => $task->nama_klien,
                    'prioritas' => $task->prioritas,
                    'prioritas_label' => $task->prioritas_label,
                    'prioritas_badge_class' => $task->prioritas_badge_class,
                    'tanggal_deadline' => $task->tanggal_deadline?->toDateString(),
                    'formatted_deadline' => $task->tanggal_deadline?->translatedFormat('d F Y'),
                    'status' => $task->status,
                    'status_label' => $task->status_label,
                    'status_badge_class' => $task->status_badge_class,
                    'is_overdue' => $task->isOverdue(),
                    'edit_url' => route('tasks.edit', $task),
                    'subtasks' => $task->subtasks,
                    'subtasks_count' => $task->subtasks_count,
                    'completed_subtasks_count' => $task->completed_subtasks_count,
                    'subtasks_progress_percentage' => $task->subtasks_progress_percentage,
                ];
            });

        $stats = [
            'total' => $user->tasks()->count(),
            'selesai' => $user->tasks()->where('status', 'selesai')->count(),
            'overdue' => $user->tasks()->overdue()->count(),
            'mendatang' => $user->tasks()->where('status', '!=', 'selesai')->whereDate('tanggal_deadline', '>=', today())->count(),
        ];

        return view('calendar', compact('tasks', 'stats'));
    }

    /**
     * Helper: Menyiapkan dataset terstruktur untuk grafik Chart.js
     */
    private function getChartData(User $user, array $stats): array
    {
        // 1. Data Distribusi Status
        $statusChart = [
            'labels' => ['Belum Dikerjakan', 'Sedang Dikerjakan', 'Menunggu Antrian', 'Selesai', 'Terlambat (Overdue)'],
            'data' => [
                $stats['belum_dikerjakan'],
                $stats['sedang_dikerjakan'],
                $stats['menunggu_antrian'],
                $stats['selesai'],
                $stats['overdue'],
            ],
            'keys' => ['belum_dikerjakan', 'sedang_dikerjakan', 'menunggu_antrian', 'selesai', 'overdue'],
            'colors' => ['#94a3b8', '#3b82f6', '#f59e0b', '#10b981', '#f43f5e'],
        ];

        // 2. Data Distribusi Prioritas
        $priorityHigh = $user->tasks()->where('prioritas', 'tinggi')->count();
        $priorityMed = $user->tasks()->where('prioritas', 'sedang')->count();
        $priorityLow = $user->tasks()->where('prioritas', 'rendah')->count();

        $priorityChart = [
            'labels' => ['Tinggi (High)', 'Sedang (Medium)', 'Rendah (Low)'],
            'data' => [$priorityHigh, $priorityMed, $priorityLow],
            'keys' => ['tinggi', 'sedang', 'rendah'],
            'colors' => ['#ef4444', '#f59e0b', '#10b981'],
        ];

        // 3. Data Distribusi Deadline & Beban Waktu (Khusus tugas belum selesai)
        $today = today();
        $endOfWeek = today()->endOfWeek();
        $startOfNextWeek = today()->addWeek()->startOfWeek();
        $endOfNextWeek = today()->addWeek()->endOfWeek();

        $deadlineOverdue = $user->tasks()->overdue()->count();
        $deadlineToday = $user->tasks()->where('status', '!=', 'selesai')->whereDate('tanggal_deadline', $today)->count();
        $deadlineThisWeek = $user->tasks()->where('status', '!=', 'selesai')
            ->whereDate('tanggal_deadline', '>', $today)
            ->whereDate('tanggal_deadline', '<=', $endOfWeek)
            ->count();
        $deadlineNextWeek = $user->tasks()->where('status', '!=', 'selesai')
            ->whereDate('tanggal_deadline', '>=', $startOfNextWeek)
            ->whereDate('tanggal_deadline', '<=', $endOfNextWeek)
            ->count();
        $deadlineLater = $user->tasks()->where('status', '!=', 'selesai')
            ->whereDate('tanggal_deadline', '>', $endOfNextWeek)
            ->count();

        $deadlineChart = [
            'labels' => ['Terlambat', 'Hari Ini', 'Minggu Ini', 'Minggu Depan', 'Mendatang (>2 Mgg)'],
            'data' => [$deadlineOverdue, $deadlineToday, $deadlineThisWeek, $deadlineNextWeek, $deadlineLater],
            'colors' => ['#f43f5e', '#ec4899', '#8b5cf6', '#3b82f6', '#10b981'],
        ];

        // 4. Data Top Klien
        $topClientsQuery = $user->tasks()->whereNotNull('nama_klien')
            ->where('nama_klien', '!=', '')
            ->selectRaw('nama_klien, count(*) as total, sum(case when status = "selesai" then 1 else 0 end) as completed')
            ->groupBy('nama_klien')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $clientChart = [
            'labels' => $topClientsQuery->pluck('nama_klien')->toArray(),
            'total' => $topClientsQuery->pluck('total')->toArray(),
            'completed' => $topClientsQuery->pluck('completed')->map(fn ($v) => (int) $v)->toArray(),
        ];

        // 5. Completion Rate & Health Metrics
        $total = $stats['total'];
        $completed = $stats['selesai'];
        $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
        $activeCount = $total - $completed;
        $onTrackCount = max(0, $activeCount - $stats['overdue']);

        return [
            'status' => $statusChart,
            'priority' => $priorityChart,
            'deadline' => $deadlineChart,
            'client' => $clientChart,
            'metrics' => [
                'completionRate' => $completionRate,
                'activeCount' => $activeCount,
                'onTrackCount' => $onTrackCount,
                'overdueCount' => $stats['overdue'],
            ],
        ];
    }

    /**
     * Menampilkan form pembuatan task baru.
     */
    public function create(): View
    {
        return view('tasks.create');
    }

    /**
     * Menyimpan data task baru ke database setelah validasi.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi input dari user
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'nama_klien' => ['nullable', 'string', 'max:255'],
            'prioritas' => ['required', 'in:rendah,sedang,tinggi'],
            'tanggal_deadline' => ['required', 'date'],
            'status' => ['required', 'in:belum_dikerjakan,sedang_dikerjakan,menunggu_antrian,selesai'],
        ], [
            'judul.required' => 'Judul task wajib diisi.',
            'prioritas.required' => 'Prioritas task wajib dipilih.',
            'tanggal_deadline.required' => 'Tanggal deadline wajib diisi.',
            'tanggal_deadline.date' => 'Format tanggal deadline tidak valid.',
            'status.required' => 'Status task wajib dipilih.',
        ]);

        $request->user()->tasks()->create($validated);

        return redirect()->route('dashboard')->with('success', '🎉 Task baru berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit untuk task tertentu.
     */
    public function edit(Task $task): View
    {
        abort_if($task->user_id !== auth()->id(), 403, 'Anda tidak memiliki akses ke tugas ini.');

        return view('tasks.edit', compact('task'));
    }

    /**
     * Memperbarui data task di database.
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        abort_if($task->user_id !== auth()->id(), 403, 'Anda tidak memiliki akses ke tugas ini.');

        // Validasi input
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'nama_klien' => ['nullable', 'string', 'max:255'],
            'prioritas' => ['required', 'in:rendah,sedang,tinggi'],
            'tanggal_deadline' => ['required', 'date'],
            'status' => ['required', 'in:belum_dikerjakan,sedang_dikerjakan,menunggu_antrian,selesai'],
        ], [
            'judul.required' => 'Judul task wajib diisi.',
            'prioritas.required' => 'Prioritas task wajib dipilih.',
            'tanggal_deadline.required' => 'Tanggal deadline wajib diisi.',
            'status.required' => 'Status task wajib dipilih.',
        ]);

        $task->update($validated);

        return redirect()->route('dashboard')->with('success', '✅ Task berhasil diperbarui!');
    }

    /**
     * Memindahkan task ke Tong Sampah (Soft Delete).
     */
    public function destroy(Task $task): RedirectResponse
    {
        abort_if($task->user_id !== auth()->id(), 403, 'Anda tidak memiliki akses ke tugas ini.');

        $taskJudul = $task->judul;
        $task->delete();

        return redirect()->back()->with('success', "🗑️ Task '{$taskJudul}' berhasil dipindahkan ke Tong Sampah.");
    }

    /**
     * Menampilkan daftar tugas yang berada di Tong Sampah (Soft Deleted).
     */
    public function trash(Request $request): View
    {
        $user = $request->user();

        $trashedTasks = $user->tasks()
            ->onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(15);

        $trashedCount = $trashedTasks->total();

        return view('tasks.trash', compact('trashedTasks', 'trashedCount'));
    }

    /**
     * Memulihkan (restore) tugas dari Tong Sampah kembali ke aktif.
     */
    public function restore(Request $request, int|string $id): RedirectResponse
    {
        $task = Task::onlyTrashed()->where('id', $id)->firstOrFail();

        abort_if($task->user_id !== auth()->id(), 403, 'Anda tidak memiliki akses ke tugas ini.');

        $taskJudul = $task->judul;
        $task->restore();

        return redirect()->back()->with('success', "♻️ Task '{$taskJudul}' berhasil dipulihkan!");
    }

    /**
     * Menghapus tugas secara permanen dari database.
     */
    public function forceDelete(Request $request, int|string $id): RedirectResponse
    {
        $task = Task::onlyTrashed()->where('id', $id)->firstOrFail();

        abort_if($task->user_id !== auth()->id(), 403, 'Anda tidak memiliki akses ke tugas ini.');

        $taskJudul = $task->judul;
        $task->forceDelete();

        return redirect()->back()->with('success', "💥 Task '{$taskJudul}' telah dihapus permanen dari database.");
    }

    /**
     * Mengosongkan seluruh isi Tong Sampah pengguna secara permanen.
     */
    public function emptyTrash(Request $request): RedirectResponse
    {
        $user = $request->user();
        $count = $user->tasks()->onlyTrashed()->count();

        $user->tasks()->onlyTrashed()->forceDelete();

        return redirect()->back()->with('success', "🧹 Seluruh ({$count}) tugas di Tong Sampah berhasil dibersihkan permanen.");
    }

    /**
     * Mengirimkan notifikasi ringkasan tugas ke Telegram secara instan.
     */
    public function notifyTelegram(Request $request, TelegramNotificationService $telegramService): RedirectResponse
    {
        $result = $telegramService->sendDailyTaskReminder(forceSendIfEmpty: true, user: $request->user());

        if ($result['success']) {
            return redirect()->back()->with('success', '🤖 Notifikasi rekap tugas berhasil dikirim ke Telegram!');
        }

        return redirect()->back()->with('error', '⚠️ '.$result['message']);
    }

    /**
     * Memperbarui status task secara cepat (digunakan oleh Kanban board drag-and-drop atau tombol aksi).
     */
    public function updateStatus(Request $request, Task $task): JsonResponse|RedirectResponse
    {
        abort_if($task->user_id !== auth()->id(), 403, 'Anda tidak memiliki akses ke tugas ini.');

        // Tugas yang sudah selesai tidak dapat di-unsubmit melalui tombol cepat (hanya melalui halaman Edit)
        if ($task->status === 'selesai') {
            $errorMessage = 'Tugas yang sudah selesai tidak dapat di-unsubmit melalui tombol cepat. Silakan ubah status melalui halaman Edit.';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 422);
            }

            return redirect()->back()->with('error', $errorMessage);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:belum_dikerjakan,sedang_dikerjakan,menunggu_antrian,selesai'],
        ]);

        $task->update([
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Status tugas '{$task->judul}' berhasil diubah ke {$task->status_label}.",
                'task' => [
                    'id' => $task->id,
                    'status' => $task->status,
                    'status_label' => $task->status_label,
                    'is_overdue' => $task->isOverdue(),
                ],
            ]);
        }

        return redirect()->back()->with('success', "Status tugas '{$task->judul}' berhasil diubah ke {$task->status_label}.");
    }
}
