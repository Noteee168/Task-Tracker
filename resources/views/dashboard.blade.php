<x-app-layout>
    {{-- 
        FUNGSI FILE INI:
        Ini adalah tampilan Dashboard Utama Task Tracker.
        Menampilkan:
        1. Kartu statistik ringkas (Total, Sedang Dikerjakan, Antrian, Selesai, Terlambat/Overdue).
        2. Filter dan pencarian task real-time (berdasarkan status, prioritas, kata kunci).
        3. Tabel task interaktif lengkap dengan badge warna per status & prioritas.
        4. Highlight visual mencolok untuk task yang melewati batas deadline dan belum selesai.
        5. Tombol aksi untuk Tambah, Edit, dan Hapus Task.
    --}}

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="relative shrink-0">
                    <x-application-logo class="w-12 h-12 shadow-md shadow-indigo-500/20 rounded-xl" />
                    <span class="absolute -bottom-0.5 -right-0.5 flex h-3.5 w-3.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
                        <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 border-2 border-white" title="Sistem Aktif & Terhubung"></span>
                    </span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="font-black text-2xl text-slate-900 leading-tight">
                            {{ __('Task Tracker Dashboard') }}
                        </h2>
                        <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider bg-gradient-to-r from-indigo-50 to-sky-50 text-indigo-700 rounded-md border border-indigo-200/70 shadow-2xs hidden sm:inline-block">Pro</span>
                    </div>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Kelola dan pantau seluruh tugas, deadline, serta progres proyek Anda secara real-time.
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <form action="{{ route('tasks.notify-telegram') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            title="Kirim notifikasi rekap tugas (overdue, H-3 deadline, belum dikerjakan) ke Telegram Pengelola"
                            class="inline-flex items-center px-3.5 py-2.5 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-sky-200 hover:shadow-lg hover:shadow-sky-200 transition-all duration-150 ease-in-out gap-2 cursor-pointer">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                        </svg>
                        <span class="hidden sm:inline">Kirim ke Telegram</span>
                    </button>
                </form>

                <a href="{{ route('tasks.trash') }}" 
                   title="Lihat tugas yang berada di Tong Sampah"
                   class="inline-flex items-center px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 text-sm font-semibold rounded-xl border border-slate-200 shadow-2xs hover:shadow-xs transition gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span class="hidden sm:inline">Tong Sampah</span>
                    @if(($stats['trashed'] ?? 0) > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-700">
                            {{ $stats['trashed'] }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 hover:shadow-lg hover:shadow-indigo-200 transition-all duration-150 ease-in-out gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Task Baru</span>
                </a>
            </div>
        </div>
    </x-slot>

    <script>
        function taskTrackerDashboard() {
            return {
                viewMode: localStorage.getItem('task_view_mode') || 'table',
                showDeleteModal: false, 
                deleteUrl: '', 
                deleteTitle: '', 
                confirmDelete(title, url) { 
                    this.deleteTitle = title; 
                    this.deleteUrl = url; 
                    this.showDeleteModal = true; 
                },

                // Kanban Drag & Drop State
                draggedTaskId: null,
                draggedOriginalStatus: null,
                dragOverColumn: null,
                toastMessage: '',
                toastType: 'success',
                showToast: false,

                handleDragStart(event, taskId, currentStatus) {
                    this.draggedTaskId = taskId;
                    const cardEl = document.getElementById('kanban-task-' + taskId);
                    // Selalu gunakan status aktual dari DOM dataset jika tersedia
                    this.draggedOriginalStatus = (cardEl && cardEl.dataset.status) ? cardEl.dataset.status : currentStatus;
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', taskId);
                    setTimeout(() => {
                        if (event.target) {
                            event.target.classList.add('opacity-40', 'scale-95');
                        }
                    }, 0);
                },

                handleDragEnd(event) {
                    if (event.target) {
                        event.target.classList.remove('opacity-40', 'scale-95');
                    }
                    this.dragOverColumn = null;
                    this.draggedTaskId = null;
                    this.draggedOriginalStatus = null;
                },

                handleDragOver(event, status) {
                    event.preventDefault();
                    event.dataTransfer.dropEffect = 'move';
                    this.dragOverColumn = status;
                },

                handleDragLeave(event, status) {
                    if (this.dragOverColumn === status) {
                        this.dragOverColumn = null;
                    }
                },

                async handleDrop(event, targetStatus) {
                    event.preventDefault();
                    const taskId = this.draggedTaskId;
                    const cardEl = document.getElementById('kanban-task-' + taskId);
                    const sourceStatus = (cardEl && cardEl.dataset.status) ? cardEl.dataset.status : this.draggedOriginalStatus;
                    this.dragOverColumn = null;

                    if (!taskId || !sourceStatus || sourceStatus === targetStatus) {
                        return;
                    }

                    const sourceCol = document.getElementById('kanban-col-' + sourceStatus);
                    const targetCol = document.getElementById('kanban-col-' + targetStatus);

                    if (!cardEl || !targetCol) return;

                    // Optimistic DOM move
                    const targetContainer = targetCol.querySelector('.kanban-cards-container');
                    if (targetContainer) {
                        targetContainer.prepend(cardEl);
                    }
                    cardEl.dataset.status = targetStatus;
                    this.draggedOriginalStatus = targetStatus;
                    this.updateColumnCounts();

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch('/tasks/' + taskId + '/status', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ status: targetStatus })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.triggerToast(data.message, 'success');
                        } else {
                            // Kembalikan kartu jika gagal
                            if (sourceCol) {
                                const sourceContainer = sourceCol.querySelector('.kanban-cards-container');
                                if (sourceContainer) {
                                    sourceContainer.prepend(cardEl);
                                }
                            }
                            cardEl.dataset.status = sourceStatus;
                            this.draggedOriginalStatus = sourceStatus;
                            this.updateColumnCounts();
                            this.triggerToast(data.message || 'Gagal memindahkan tugas.', 'error');
                        }
                    } catch (err) {
                        // Kembalikan kartu jika terjadi gangguan jaringan
                        if (sourceCol) {
                            const sourceContainer = sourceCol.querySelector('.kanban-cards-container');
                            if (sourceContainer) {
                                sourceContainer.prepend(cardEl);
                            }
                        }
                        cardEl.dataset.status = sourceStatus;
                        this.draggedOriginalStatus = sourceStatus;
                        this.updateColumnCounts();
                        this.triggerToast('Terjadi kesalahan koneksi saat memindahkan tugas.', 'error');
                    }
                },

                updateColumnCounts() {
                    ['belum_dikerjakan', 'sedang_dikerjakan', 'menunggu_antrian', 'selesai'].forEach(status => {
                        const col = document.getElementById('kanban-col-' + status);
                        if (col) {
                            const countEl = col.querySelector('.kanban-col-count');
                            const cards = col.querySelectorAll('.kanban-task-card');
                            if (countEl) countEl.textContent = cards.length;

                            const emptyPlaceholder = col.querySelector('.kanban-empty-placeholder');
                            if (emptyPlaceholder) {
                                emptyPlaceholder.style.display = cards.length === 0 ? 'block' : 'none';
                            }
                        }
                    });
                },

                triggerToast(message, type = 'success') {
                    this.toastMessage = message;
                    this.toastType = type;
                    this.showToast = true;
                    setTimeout(() => {
                        this.showToast = false;
                    }, 3500);
                },

                // Subtask State & Methods
                showSubtaskModal: false,
                activeSubtaskTask: {
                    id: null,
                    judul: '',
                    nama_klien: '',
                    subtasks_count: 0,
                    completed_subtasks_count: 0,
                    subtasks_progress_percentage: 0
                },
                subtasksList: [],
                newSubtaskTitle: '',
                isSubtaskLoading: false,

                openSubtaskModal(taskId, judul, namaKlien, count, completed, percentage) {
                    this.activeSubtaskTask = {
                        id: taskId,
                        judul: judul || '',
                        nama_klien: namaKlien || '',
                        subtasks_count: count || 0,
                        completed_subtasks_count: completed || 0,
                        subtasks_progress_percentage: percentage || 0
                    };
                    this.subtasksList = [];
                    this.newSubtaskTitle = '';
                    this.showSubtaskModal = true;
                    this.refreshSubtasks(taskId);
                },

                async refreshSubtasks(taskId) {
                    try {
                        const res = await fetch('/tasks/' + taskId + '/subtasks', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.subtasksList = data.subtasks;
                            this.updateTaskSubtaskMetrics(taskId, data.progress);
                        }
                    } catch (err) {
                        console.error(err);
                    }
                },

                async toggleSubtask(subtask) {
                    const originalCompleted = subtask.is_completed;
                    subtask.is_completed = !originalCompleted;
                    
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const res = await fetch('/subtasks/' + subtask.id + '/toggle', {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            subtask.is_completed = data.subtask.is_completed;
                            this.updateTaskSubtaskMetrics(this.activeSubtaskTask.id, data.progress);
                        } else {
                            subtask.is_completed = originalCompleted;
                        }
                    } catch (err) {
                        subtask.is_completed = originalCompleted;
                        console.error(err);
                    }
                },

                async addSubtask() {
                    if (!this.newSubtaskTitle.trim() || this.isSubtaskLoading) return;
                    this.isSubtaskLoading = true;
                    const title = this.newSubtaskTitle.trim();

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const res = await fetch('/tasks/' + this.activeSubtaskTask.id + '/subtasks', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ judul: title })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.subtasksList.push(data.subtask);
                            this.newSubtaskTitle = '';
                            this.updateTaskSubtaskMetrics(this.activeSubtaskTask.id, data.progress);
                        }
                    } catch (err) {
                        console.error(err);
                    } finally {
                        this.isSubtaskLoading = false;
                    }
                },

                async deleteSubtask(subtaskId) {
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const res = await fetch('/subtasks/' + subtaskId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.subtasksList = this.subtasksList.filter(s => s.id !== subtaskId);
                            this.updateTaskSubtaskMetrics(this.activeSubtaskTask.id, data.progress);
                        }
                    } catch (err) {
                        console.error(err);
                    }
                },

                updateTaskSubtaskMetrics(taskId, progress) {
                    if (this.activeSubtaskTask && this.activeSubtaskTask.id === taskId) {
                        this.activeSubtaskTask.subtasks_count = progress.total;
                        this.activeSubtaskTask.completed_subtasks_count = progress.completed;
                        this.activeSubtaskTask.subtasks_progress_percentage = progress.percentage;
                    }

                    const badges = document.querySelectorAll(`[data-task-subtask-badge="${taskId}"]`);
                    badges.forEach(badge => {
                        const countText = badge.querySelector('.subtask-count-text');
                        if (countText) {
                            countText.textContent = progress.total > 0 ? `${progress.completed}/${progress.total}` : '+ Subtask';
                        }
                        const bar = badge.querySelector('.subtask-progress-bar');
                        if (bar) {
                            bar.style.width = `${progress.percentage}%`;
                            if (progress.percentage === 100) {
                                bar.classList.remove('bg-indigo-500');
                                bar.classList.add('bg-emerald-500');
                            } else {
                                bar.classList.remove('bg-emerald-500');
                                bar.classList.add('bg-indigo-500');
                            }
                        }
                    });
                }
            };
        }
    </script>

    <div class="py-8" x-data="taskTrackerDashboard()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Message / Notifikasi Sukses (Auto hilang dalam 3 detik) --}}
            @if (session('success'))
                <div x-data="{ show: true }" 
                     x-init="setTimeout(() => show = false, 3000)" 
                     x-show="show" 
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 shadow-xs flex items-center justify-between transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold shrink-0 shadow-xs">
                            ✓
                        </div>
                        <span class="text-sm font-semibold text-emerald-900">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold p-1 rounded-lg hover:bg-emerald-100/60">
                        &times;
                    </button>
                </div>
            @endif

            {{-- Flash Message / Peringatan Error (Auto hilang dalam 3 detik) --}}
            @if (session('error'))
                <div x-data="{ show: true }" 
                     x-init="setTimeout(() => show = false, 3000)" 
                     x-show="show" 
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="p-4 rounded-2xl bg-amber-50 border border-amber-200 shadow-xs flex items-center justify-between transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-bold shrink-0 shadow-xs">
                            ⚠️
                        </div>
                        <span class="text-sm font-semibold text-amber-900">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-amber-600 hover:text-amber-800 text-sm font-bold p-1 rounded-lg hover:bg-amber-100/60">
                        &times;
                    </button>
                </div>
            @endif

            {{-- Floating Toast Notifikasi AJAX (Kanban Drag & Drop) --}}
            <div x-show="showToast" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-3 sm:translate-y-0 sm:translate-x-3 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="fixed bottom-6 right-6 z-50 max-w-sm p-4 rounded-2xl shadow-xl border flex items-center gap-3 backdrop-blur-md"
                 :class="toastType === 'success' ? 'bg-emerald-600/95 text-white border-emerald-500 shadow-emerald-200' : 'bg-rose-600/95 text-white border-rose-500 shadow-rose-200'">
                <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center font-bold text-sm shrink-0" 
                     x-text="toastType === 'success' ? '✓' : '⚠️'"></div>
                <div class="text-xs font-bold flex-1 leading-snug" x-text="toastMessage"></div>
                <button @click="showToast = false" class="text-white/80 hover:text-white font-bold p-1 rounded-lg hover:bg-white/10 transition">&times;</button>
            </div>

            {{-- Warning Banner jika ada Task yang Melewati Batas Deadline (Overdue) --}}
            @if ($stats['overdue'] > 0)
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 border-l-4 border-l-rose-500 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-3 w-3 relative shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-600"></span>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-rose-900">
                                Perhatian: Terdapat {{ $stats['overdue'] }} tugas yang telah melewati batas deadline!
                            </p>
                            <p class="text-xs text-rose-700 mt-0.5">
                                Segera tindak lanjuti tugas bertanda merah di bawah.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('dashboard', ['status' => 'overdue']) }}" class="text-xs font-semibold px-3.5 py-2 bg-rose-600 text-white hover:bg-rose-700 rounded-xl shrink-0 transition text-center shadow-xs">
                        Lihat Task Overdue &rarr;
                    </a>
                </div>
            @endif

            {{-- 1. KARTU STATISTIK RINGKAS --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                {{-- Total Tasks --}}
                <a href="{{ route('dashboard') }}" class="block p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:shadow-md hover:border-indigo-300 transition group">
                    <div class="text-xs font-semibold text-slate-500 group-hover:text-indigo-600 transition">Total Tugas</div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <div class="text-2xl font-black text-slate-900">{{ $stats['total'] }}</div>
                        <span class="text-xs text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-lg font-bold">Semua</span>
                    </div>
                </a>

                {{-- Belum Dikerjakan --}}
                <a href="{{ route('dashboard', ['status' => 'belum_dikerjakan']) }}" class="block p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:shadow-md hover:border-slate-300 transition">
                    <div class="text-xs font-semibold text-slate-500">Belum Mulai</div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <div class="text-2xl font-black text-slate-800">{{ $stats['belum_dikerjakan'] }}</div>
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-400 inline-block"></span>
                    </div>
                </a>

                {{-- Sedang Dikerjakan --}}
                <a href="{{ route('dashboard', ['status' => 'sedang_dikerjakan']) }}" class="block p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:shadow-md hover:border-blue-300 transition">
                    <div class="text-xs font-semibold text-blue-600">Dikerjakan</div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <div class="text-2xl font-black text-blue-700">{{ $stats['sedang_dikerjakan'] }}</div>
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse inline-block"></span>
                    </div>
                </a>

                {{-- Menunggu Antrian --}}
                <a href="{{ route('dashboard', ['status' => 'menunggu_antrian']) }}" class="block p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:shadow-md hover:border-amber-300 transition">
                    <div class="text-xs font-semibold text-amber-600">Antrian</div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <div class="text-2xl font-black text-amber-700">{{ $stats['menunggu_antrian'] }}</div>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span>
                    </div>
                </a>

                {{-- Selesai --}}
                <a href="{{ route('dashboard', ['status' => 'selesai']) }}" class="block p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:shadow-md hover:border-emerald-300 transition">
                    <div class="text-xs font-semibold text-emerald-600">Selesai</div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <div class="text-2xl font-black text-emerald-700">{{ $stats['selesai'] }}</div>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                    </div>
                </a>

                {{-- Overdue / Terlambat --}}
                <a href="{{ route('dashboard', ['status' => 'overdue']) }}" class="block p-4 rounded-2xl bg-white border {{ $stats['overdue'] > 0 ? 'border-rose-300 bg-rose-50/40' : 'border-slate-200' }} shadow-xs hover:shadow-md hover:border-rose-300 transition">
                    <div class="text-xs font-bold text-rose-600 flex items-center gap-1">
                        <span>⚠️ Terlambat</span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <div class="text-2xl font-black text-rose-600">{{ $stats['overdue'] }}</div>
                        @if($stats['overdue'] > 0)
                            <span class="flex h-2.5 w-2.5 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-600"></span>
                            </span>
                        @endif
                    </div>
                </a>
            </div>

            {{-- 2. VISUALISASI GRAFIK RINGKAS (INTERAKTIF & TERHUBUNG KE TUGAS) --}}
            <div x-data="{ openChart: true }" class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="font-bold text-sm text-slate-900">Grafik Visualisasi Progres & Prioritas Tugas</h3>
                            <p class="text-xs text-slate-500">Klik bagian grafik atau legend untuk memfilter daftar tugas secara langsung</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('analytics') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition inline-flex items-center gap-1">
                            <span>Buka Analisis Penuh</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                        <button @click="openChart = !openChart" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition" :title="openChart ? 'Sembunyikan Grafik' : 'Tampilkan Grafik'">
                            <svg class="w-4 h-4 transform transition-transform duration-200" :class="{ 'rotate-180': !openChart }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div x-show="openChart" x-transition class="p-5">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                        {{-- Donut Chart Status --}}
                        <div class="lg:col-span-6 bg-slate-50/60 p-4 rounded-xl border border-slate-100 flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-700">Distribusi Status Tugas</span>
                                <span class="text-[11px] text-slate-400">Total: {{ $stats['total'] }}</span>
                            </div>
                            <div class="relative h-56 flex items-center justify-center">
                                @if($stats['total'] > 0)
                                    <canvas id="dashboardStatusChart"></canvas>
                                @else
                                    <span class="text-xs text-slate-400">Belum ada data tugas.</span>
                                @endif
                            </div>
                        </div>

                        {{-- Bar Chart Prioritas & Deadline --}}
                        <div class="lg:col-span-6 bg-slate-50/60 p-4 rounded-xl border border-slate-100 flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-700">Sebaran Prioritas & Urgensi</span>
                                <span class="text-[11px] text-slate-400">Penyelesaian: {{ $chartData['metrics']['completionRate'] }}%</span>
                            </div>
                            <div class="relative h-56 flex items-center justify-center">
                                @if($stats['total'] > 0)
                                    <canvas id="dashboardPriorityChart"></canvas>
                                @else
                                    <span class="text-xs text-slate-400">Belum ada data tugas.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. TOOLBAR FILTER & PENCARIAN --}}
            <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs">
                <form method="GET" action="{{ route('dashboard') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                    
                    {{-- Input Pencarian --}}
                    <div class="md:col-span-4 relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul, klien, atau deskripsi..." class="w-full pl-10 pr-3 py-2 text-sm rounded-xl border border-slate-300 bg-slate-50 focus:bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">
                    </div>

                    {{-- Filter Status --}}
                    <div class="md:col-span-3">
                        <select name="status" class="w-full py-2 px-3 text-sm rounded-xl border border-slate-300 bg-slate-50 focus:bg-white text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">
                            <option value="">-- Semua Status --</option>
                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>⚠️ Terlambat (Overdue)</option>
                            <option value="belum_dikerjakan" {{ request('status') === 'belum_dikerjakan' ? 'selected' : '' }}>Belum Dikerjakan</option>
                            <option value="sedang_dikerjakan" {{ request('status') === 'sedang_dikerjakan' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                            <option value="menunggu_antrian" {{ request('status') === 'menunggu_antrian' ? 'selected' : '' }}>Menunggu Antrian</option>
                            <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>

                    {{-- Filter Prioritas --}}
                    <div class="md:col-span-3">
                        <select name="prioritas" class="w-full py-2 px-3 text-sm rounded-xl border border-slate-300 bg-slate-50 focus:bg-white text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">
                            <option value="">-- Semua Prioritas --</option>
                            <option value="tinggi" {{ request('prioritas') === 'tinggi' ? 'selected' : '' }}>🔥 Prioritas Tinggi</option>
                            <option value="sedang" {{ request('prioritas') === 'sedang' ? 'selected' : '' }}>⚡ Prioritas Sedang</option>
                            <option value="rendah" {{ request('prioritas') === 'rendah' ? 'selected' : '' }}>🌱 Prioritas Rendah</option>
                        </select>
                    </div>

                    {{-- Tombol Terapkan & Reset --}}
                    <div class="md:col-span-2 flex items-center gap-2">
                        <button type="submit" class="w-full py-2 px-3 bg-slate-900 hover:bg-black text-white text-sm font-semibold rounded-xl shadow-xs transition">
                            Filter
                        </button>
                        @if(request()->hasAny(['search', 'status', 'prioritas']))
                            <a href="{{ route('dashboard') }}" class="p-2 text-slate-500 hover:text-slate-900 rounded-xl hover:bg-slate-100 transition" title="Reset Filter">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- HEADER PILIHAN TAMPILAN (TABEL VS KANBAN BOARD) --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span x-show="viewMode === 'table'">📋 Tabel Daftar Tugas</span>
                        <span x-show="viewMode === 'kanban'" x-cloak>📌 Papan Kanban Interaktif</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5" x-show="viewMode === 'table'">
                        Tabel ringkas dengan sorting prioritas otomatis dan aksi cepat baris.
                    </p>
                    <p class="text-xs text-slate-500 mt-0.5" x-show="viewMode === 'kanban'" x-cloak>
                        Tarik dan lepas (drag & drop) kartu tugas antar kolom untuk memperbarui status pengerjaan secara instan.
                    </p>
                </div>

                {{-- Switcher Mode Tampilan: Tabel vs Kanban --}}
                <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200 self-start sm:self-auto shadow-2xs">
                    <button type="button"
                            @click="viewMode = 'table'; localStorage.setItem('task_view_mode', 'table')"
                            :class="viewMode === 'table' ? 'bg-white text-indigo-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        <span>Tabel</span>
                    </button>

                    <button type="button"
                            @click="viewMode = 'kanban'; localStorage.setItem('task_view_mode', 'kanban')"
                            :class="viewMode === 'kanban' ? 'bg-white text-indigo-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                        <span>Kanban Board</span>
                    </button>
                </div>
            </div>

            {{-- 3. TAMPILAN TABEL DAFTAR TASK --}}
            <div x-show="viewMode === 'table'">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/90 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3.5 px-4 sm:px-6">Status</th>
                                <th class="py-3.5 px-4 sm:px-6">Tugas & Deskripsi</th>
                                <th class="py-3.5 px-4 sm:px-6">Klien</th>
                                <th class="py-3.5 px-4 sm:px-6">Prioritas</th>
                                <th class="py-3.5 px-4 sm:px-6">Deadline</th>
                                <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($tasks as $task)
                                @php
                                    $isOverdue = $task->isOverdue();
                                @endphp
                                <tr class="transition-colors hover:bg-slate-50/80 {{ $isOverdue ? 'bg-rose-50/40 hover:bg-rose-50/70 border-l-4 border-rose-500' : '' }}">
                                    
                                    {{-- Kolom Status --}}
                                    <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $task->status_badge_class }}">
                                            @if($task->status === 'selesai')
                                                <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @elseif($task->status === 'sedang_dikerjakan')
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                            @elseif($task->status === 'menunggu_antrian')
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            @else
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            @endif
                                            {{ $task->status_label }}
                                        </span>
                                    </td>

                                    {{-- Kolom Judul & Deskripsi --}}
                                    <td class="py-4 px-4 sm:px-6">
                                        <div class="flex items-start gap-2">
                                            <div>
                                                <div class="font-bold text-slate-900 hover:text-indigo-600 transition">
                                                    <a href="{{ route('tasks.edit', $task) }}">{{ $task->judul }}</a>
                                                </div>
                                                @if ($task->deskripsi)
                                                    <p class="text-xs text-slate-500 mt-0.5 line-clamp-2 max-w-md">
                                                        {{ $task->deskripsi }}
                                                    </p>
                                                @endif

                                                {{-- Subtask Pill Trigger --}}
                                                <div class="mt-2 flex items-center gap-2">
                                                    <button type="button" 
                                                            @click="openSubtaskModal({{ $task->id }}, '{{ addslashes($task->judul) }}', '{{ addslashes($task->nama_klien ?? '') }}', {{ (int) $task->subtasks_count }}, {{ (int) $task->completed_subtasks_count }}, {{ (int) $task->subtasks_progress_percentage }})"
                                                            data-task-subtask-badge="{{ $task->id }}"
                                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold border transition cursor-pointer {{ $task->subtasks_count > 0 ? ($task->subtasks_progress_percentage === 100 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200') : 'bg-slate-50 text-slate-500 border-dashed border-slate-300 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                                        <svg class="w-3.5 h-3.5 {{ $task->subtasks_progress_percentage === 100 ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                        </svg>
                                                        <span>Subtask:</span>
                                                        <span class="subtask-count-text font-bold">{{ $task->subtasks_count > 0 ? "{$task->completed_subtasks_count}/{$task->subtasks_count}" : '+ Tambah' }}</span>
                                                        @if($task->subtasks_count > 0)
                                                            <div class="w-12 h-1.5 bg-slate-200 rounded-full overflow-hidden ml-1">
                                                                <div class="subtask-progress-bar h-full {{ $task->subtasks_progress_percentage === 100 ? 'bg-emerald-500' : 'bg-indigo-500' }} rounded-full transition-all duration-300" style="width: {{ $task->subtasks_progress_percentage }}%"></div>
                                                            </div>
                                                        @endif
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kolom Nama Klien --}}
                                    <td class="py-4 px-4 sm:px-6 whitespace-nowrap text-slate-700">
                                        @if ($task->nama_klien)
                                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 border border-slate-200">
                                                👤 {{ $task->nama_klien }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-xs">-</span>
                                        @endif
                                    </td>

                                    {{-- Kolom Prioritas --}}
                                    <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold border {{ $task->prioritas_badge_class }}">
                                            @if($task->prioritas === 'tinggi')
                                                🔥 Tinggi
                                            @elseif($task->prioritas === 'sedang')
                                                ⚡ Sedang
                                            @else
                                                🌱 Rendah
                                            @endif
                                        </span>
                                    </td>

                                    {{-- Kolom Deadline & Highlight Overdue --}}
                                    <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                        @if ($isOverdue)
                                            {{-- Highlight Visual Jika Lewat Deadline & Belum Selesai --}}
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold text-rose-600 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    {{ $task->tanggal_deadline->format('d M Y') }}
                                                </span>
                                                <span class="inline-block mt-0.5 text-[10px] font-extrabold uppercase px-1.5 py-0.5 bg-rose-100 text-rose-700 rounded border border-rose-300 w-max">
                                                    Terlambat {{ $task->days_overdue }} hari
                                                </span>
                                            </div>
                                        @else
                                            <div class="text-xs font-semibold text-slate-800">
                                                {{ $task->tanggal_deadline->format('d M Y') }}
                                                @if($task->status !== 'selesai')
                                                    @if($task->tanggal_deadline->isToday())
                                                        <span class="block text-[10px] text-amber-600 font-bold">Hari ini!</span>
                                                    @elseif($task->tanggal_deadline->isTomorrow())
                                                        <span class="block text-[10px] text-blue-600 font-bold">Besok</span>
                                                    @endif
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Kolom Aksi (Icon-Only: Submit, Edit, Hapus dengan Popout Modal) --}}
                                    <td class="py-4 px-4 sm:px-6 whitespace-nowrap text-right text-xs font-medium">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            {{-- Aksi Submit / Tandai Selesai (Langsung tanpa perlu ke halaman Edit) --}}
                                            @if ($task->status !== 'selesai')
                                                <form method="POST" action="{{ route('tasks.update-status', $task) }}" class="inline-block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="selesai">
                                                    <button type="submit" 
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 active:bg-emerald-200 text-emerald-600 hover:text-emerald-700 border border-emerald-200 transition shadow-2xs cursor-pointer" 
                                                            title="Submit / Selesaikan Tugas"
                                                            aria-label="Submit / Selesaikan Tugas">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @else
                                                {{-- Jika sudah selesai: tidak bisa di-unsubmit dengan klik tombol (hanya via halaman Edit) --}}
                                                <button type="button" 
                                                        disabled 
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 cursor-not-allowed opacity-75" 
                                                        title="Tugas sudah selesai (Hanya bisa di-unsubmit di halaman Edit)"
                                                        aria-label="Tugas sudah selesai">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                            @endif

                                            {{-- Tombol Edit (Logo Saja) --}}
                                            <a href="{{ route('tasks.edit', $task) }}" 
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white hover:bg-slate-50 text-slate-600 hover:text-indigo-600 border border-slate-300 transition shadow-2xs cursor-pointer" 
                                               title="Edit Tugas"
                                               aria-label="Edit Tugas">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            {{-- Tombol Hapus (Logo Saja dengan Popout Modal Konfirmasi) --}}
                                            <button type="button" 
                                                    @click="confirmDelete('{{ addslashes($task->judul) }}', '{{ route('tasks.destroy', $task) }}')"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 active:bg-rose-200 text-rose-600 border border-rose-200 transition shadow-2xs cursor-pointer" 
                                                    title="Hapus Tugas"
                                                    aria-label="Hapus Tugas">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 px-4 text-center">
                                        <div class="max-w-sm mx-auto flex flex-col items-center">
                                            <div class="w-16 h-16 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-3xl mb-3">
                                                📭
                                            </div>
                                            <h3 class="text-base font-bold text-slate-900">Belum Ada Task Ditemukan</h3>
                                            <p class="text-xs text-slate-500 mt-1">
                                                @if(request()->hasAny(['search', 'status', 'prioritas']))
                                                    Tidak ada task yang sesuai dengan filter pencarian saat ini.
                                                @else
                                                    Mulai catat pekerjaan Anda dengan menambahkan task baru pertama.
                                                @endif
                                            </p>
                                            <div class="mt-4">
                                                @if(request()->hasAny(['search', 'status', 'prioritas']))
                                                    <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                                                        Reset Filter & Tampilkan Semua &rarr;
                                                    </a>
                                                @else
                                                    <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                                                        + Tambah Task Pertama
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($tasks->hasPages())
                    <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                        {{ $tasks->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- 4. TAMPILAN PAPAN KANBAN (DRAG & DROP) --}}
        <div x-show="viewMode === 'kanban'" x-cloak class="space-y-4">
            @php
                $kanbanColumns = [
                    'belum_dikerjakan' => [
                        'title' => 'Belum Dikerjakan',
                        'header_bg' => 'bg-slate-100/90 border-slate-200 text-slate-800',
                        'border_top' => 'border-t-slate-400',
                        'dot' => 'bg-slate-400',
                        'count_bg' => 'bg-slate-200 text-slate-700',
                    ],
                    'sedang_dikerjakan' => [
                        'title' => 'Sedang Dikerjakan',
                        'header_bg' => 'bg-blue-50/90 border-blue-200 text-blue-800',
                        'border_top' => 'border-t-blue-500',
                        'dot' => 'bg-blue-500 animate-pulse',
                        'count_bg' => 'bg-blue-100 text-blue-700',
                    ],
                    'menunggu_antrian' => [
                        'title' => 'Menunggu Antrian',
                        'header_bg' => 'bg-amber-50/90 border-amber-200 text-amber-800',
                        'border_top' => 'border-t-amber-500',
                        'dot' => 'bg-amber-500',
                        'count_bg' => 'bg-amber-100 text-amber-700',
                    ],
                    'selesai' => [
                        'title' => 'Selesai',
                        'header_bg' => 'bg-emerald-50/90 border-emerald-200 text-emerald-800',
                        'border_top' => 'border-t-emerald-500',
                        'dot' => 'bg-emerald-500',
                        'count_bg' => 'bg-emerald-100 text-emerald-700',
                    ],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 items-start">
                @foreach ($kanbanColumns as $statusKey => $col)
                    @php
                        $colTasks = $kanbanTasks->get($statusKey, collect());
                    @endphp
                    <div id="kanban-col-{{ $statusKey }}"
                         class="bg-slate-50/70 rounded-3xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col min-h-[480px] transition-all duration-150 {{ $col['border_top'] }} border-t-4"
                         :class="{ 'ring-2 ring-indigo-500 bg-indigo-50/50 border-indigo-400 border-dashed': dragOverColumn === '{{ $statusKey }}' }"
                         @dragover.prevent="handleDragOver($event, '{{ $statusKey }}')"
                         @dragleave="handleDragLeave($event, '{{ $statusKey }}')"
                         @drop.prevent="handleDrop($event, '{{ $statusKey }}')">

                        {{-- Column Header --}}
                        <div class="p-3.5 border-b {{ $col['header_bg'] }} flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $col['dot'] }}"></span>
                                <h4 class="font-extrabold text-xs tracking-tight">{{ $col['title'] }}</h4>
                                <span class="kanban-col-count px-2 py-0.5 rounded-full text-[11px] font-black {{ $col['count_bg'] }}">
                                    {{ $colTasks->count() }}
                                </span>
                            </div>

                            <a href="{{ route('tasks.create') }}" 
                               title="Tambah tugas baru"
                               class="p-1 rounded-lg hover:bg-white/70 text-slate-500 hover:text-slate-800 transition text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </a>
                        </div>

                        {{-- Cards Container --}}
                        <div class="kanban-cards-container p-3 space-y-2.5 flex-1 overflow-y-auto max-h-[70vh] custom-scroll"
                             @dragover.prevent="handleDragOver($event, '{{ $statusKey }}')"
                             @drop.prevent.stop="handleDrop($event, '{{ $statusKey }}')">
                            {{-- Empty Column Placeholder --}}
                            <div class="kanban-empty-placeholder p-6 text-center border-2 border-dashed border-slate-200 rounded-2xl text-slate-400 text-xs {{ $colTasks->isEmpty() ? 'block' : 'hidden' }}">
                                <p class="font-medium">Belum ada tugas</p>
                                <p class="text-[10px] text-slate-400 mt-1">Tarik kartu tugas ke kolom ini</p>
                            </div>

                            @foreach ($colTasks as $task)
                                @php
                                    $isOverdue = $task->isOverdue();
                                @endphp
                                <div id="kanban-task-{{ $task->id }}"
                                     data-task-id="{{ $task->id }}"
                                     data-status="{{ $task->status }}"
                                     draggable="true"
                                     @dragstart="handleDragStart($event, {{ $task->id }}, $el.dataset.status || '{{ $task->status }}')"
                                     @dragend="handleDragEnd($event)"
                                     class="kanban-task-card p-3.5 bg-white rounded-2xl border border-slate-200 shadow-2xs hover:shadow-md hover:border-indigo-300 transition-all duration-150 cursor-grab active:cursor-grabbing select-none group {{ $isOverdue ? 'border-l-4 border-l-rose-500 bg-rose-50/20' : '' }}">

                                    {{-- Priority & Deadline Row --}}
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-slate-300 group-hover:text-slate-500 transition cursor-grab" title="Tarik untuk memindahkan">
                                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                                    <path d="M7 4a2 2 0 11-4 0 2 2 0 014 0zm0 6a2 2 0 11-4 0 2 2 0 014 0zm0 6a2 2 0 11-4 0 2 2 0 014 0zm8-12a2 2 0 11-4 0 2 2 0 014 0zm0 6a2 2 0 11-4 0 2 2 0 014 0zm0 6a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </span>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $task->prioritas_badge_class }}">
                                                {{ $task->prioritas_label }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-1 text-[11px] font-bold {{ $isOverdue ? 'text-rose-600' : 'text-slate-500' }}">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ $task->tanggal_deadline?->translatedFormat('d M') }}</span>
                                        </div>
                                    </div>

                                    {{-- Judul Task --}}
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug line-clamp-2 {{ $task->status === 'selesai' ? 'line-through text-slate-400' : '' }}">
                                        {{ $task->judul }}
                                    </h4>

                                    @if($task->nama_klien)
                                        <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1 truncate font-medium">
                                            <span>🏢</span>
                                            <span class="truncate">{{ $task->nama_klien }}</span>
                                        </p>
                                    @endif

                                    {{-- Subtask Progress Pill --}}
                                    <div class="mt-2.5">
                                        <button type="button"
                                                @mousedown.stop
                                                @click.stop="openSubtaskModal({{ $task->id }}, '{{ addslashes($task->judul) }}', '{{ addslashes($task->nama_klien ?? '') }}', {{ (int) $task->subtasks_count }}, {{ (int) $task->completed_subtasks_count }}, {{ (int) $task->subtasks_progress_percentage }})"
                                                data-task-subtask-badge="{{ $task->id }}"
                                                class="w-full inline-flex items-center justify-between px-2.5 py-1.5 rounded-xl text-[11px] font-semibold border transition cursor-pointer {{ $task->subtasks_count > 0 ? ($task->subtasks_progress_percentage === 100 ? 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-indigo-50 hover:text-indigo-700') : 'bg-slate-50 text-slate-400 border-dashed border-slate-200 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-300' }}">
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 {{ $task->subtasks_progress_percentage === 100 ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                </svg>
                                                <span>Subtask</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="subtask-count-text">{{ $task->subtasks_count > 0 ? "{$task->completed_subtasks_count}/{$task->subtasks_count}" : '+ Tambah' }}</span>
                                                @if($task->subtasks_count > 0)
                                                    <div class="w-10 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                                        <div class="subtask-progress-bar h-full {{ $task->subtasks_progress_percentage === 100 ? 'bg-emerald-500' : 'bg-indigo-500' }} rounded-full transition-all duration-300" style="width: {{ $task->subtasks_progress_percentage }}%"></div>
                                                    </div>
                                                @endif
                                            </div>
                                        </button>
                                    </div>

                                    {{-- Footer: Overdue badge & Quick Action Buttons --}}
                                    <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                                        <div class="text-[11px]">
                                            @if($isOverdue)
                                                <span class="text-rose-600 font-bold flex items-center gap-0.5">
                                                    <span>⚠️</span>
                                                    <span>Terlambat {{ $task->days_overdue }} hr</span>
                                                </span>
                                            @elseif($task->status === 'selesai')
                                                <span class="text-emerald-600 font-bold flex items-center gap-0.5">
                                                    <span>✓</span>
                                                    <span>Selesai</span>
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-1" @mousedown.stop @click.stop>
                                            <a href="{{ route('tasks.edit', $task) }}" 
                                               title="Edit Task"
                                               class="p-1 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            <button type="button" 
                                                    @click="confirmDelete('{{ addslashes($task->judul) }}', '{{ route('tasks.destroy', $task) }}')" 
                                                    title="Hapus Task" 
                                                    class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

            {{-- Modal Popout Konfirmasi Hapus Tugas --}}
            <div x-show="showDeleteModal" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 aria-labelledby="modal-title" role="dialog" aria-modal="true"
                 @keydown.escape.window="showDeleteModal = false">
                
                {{-- Backdrop Overlay Blur & Animasi Fade --}}
                <div x-show="showDeleteModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
                     @click="showDeleteModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showDeleteModal"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200"
                         @click.stop>
                        
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-base font-bold text-slate-900 leading-6" id="modal-title">
                                        Pindahkan ke Tong Sampah?
                                    </h3>
                                    <p class="mt-2 text-sm text-slate-500">
                                        Apakah Anda yakin ingin memindahkan tugas <span class="font-bold text-slate-900" x-text="'\'' + deleteTitle + '\''"></span> ke Tong Sampah?
                                    </p>
                                    <p class="mt-2 text-xs text-amber-700 font-medium bg-amber-50 p-2.5 rounded-xl border border-amber-200">
                                        🗑️ Tugas tidak akan langsung hilang permanen dan dapat dipulihkan kembali dari halaman Tong Sampah.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100">
                            <button type="button" 
                                    @click="showDeleteModal = false"
                                    class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs cursor-pointer">
                                Batal
                            </button>
                            <form :action="deleteUrl" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-sm font-semibold text-white shadow-md shadow-rose-200 transition cursor-pointer">
                                    Ya, Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Popout Subtasks / Checklist --}}
            <div x-show="showSubtaskModal" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 aria-labelledby="subtask-modal-title" role="dialog" aria-modal="true"
                 @keydown.escape.window="showSubtaskModal = false">
                
                {{-- Backdrop --}}
                <div x-show="showSubtaskModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
                     @click="showSubtaskModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <template x-if="showSubtaskModal">
                        <div x-show="showSubtaskModal"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200"
                             @click.stop>
                            
                            {{-- Modal Header --}}
                            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg font-bold shrink-0">
                                            ☑️
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                                                    Subtasks / Checklist
                                                </span>
                                                <template x-if="activeSubtaskTask && activeSubtaskTask.nama_klien">
                                                    <span class="text-[11px] font-medium text-slate-500" x-text="'🏢 ' + activeSubtaskTask.nama_klien"></span>
                                                </template>
                                            </div>
                                            <h3 class="text-base font-bold text-slate-900 leading-snug mt-1" id="subtask-modal-title" x-text="activeSubtaskTask ? activeSubtaskTask.judul : ''">
                                            </h3>
                                        </div>
                                    </div>
                                    <button type="button" @click="showSubtaskModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                {{-- Progress Bar Container --}}
                                <div class="mt-4 pt-3 border-t border-slate-200/60">
                                    <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                                        <span class="text-slate-600">Progres Checklist</span>
                                        <span class="text-indigo-600 font-bold" x-text="activeSubtaskTask ? `${activeSubtaskTask.completed_subtasks_count || 0} dari ${activeSubtaskTask.subtasks_count || 0} selesai (${activeSubtaskTask.subtasks_progress_percentage || 0}%)` : '0%'"></span>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-indigo-500 to-emerald-500 rounded-full transition-all duration-300"
                                             :style="`width: ${activeSubtaskTask ? (activeSubtaskTask.subtasks_progress_percentage || 0) : 0}%`"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Body: List of Subtasks --}}
                            <div class="p-6 max-h-[50vh] overflow-y-auto space-y-2.5 custom-scroll">
                                <template x-if="subtasksList.length === 0">
                                    <div class="text-center py-8 text-slate-400">
                                        <span class="text-3xl block mb-2">📝</span>
                                        <p class="text-sm font-medium text-slate-600">Belum ada subtask</p>
                                        <p class="text-xs text-slate-400 mt-1">Pecah tugas ini menjadi beberapa langkah kecil di bawah ini.</p>
                                    </div>
                                </template>

                                <template x-for="subtask in subtasksList" :key="subtask.id">
                                    <div class="group flex items-center justify-between gap-3 p-3 rounded-xl border transition-all duration-150"
                                         :class="subtask.is_completed ? 'bg-slate-50/70 border-slate-200/60' : 'bg-white border-slate-200 hover:border-indigo-200 shadow-2xs'">
                                        
                                        {{-- Custom Clickable Checkbox --}}
                                        <label class="flex items-center gap-3 flex-1 cursor-pointer select-none">
                                            <input type="checkbox" 
                                                   :checked="subtask.is_completed" 
                                                   @change="toggleSubtask(subtask)"
                                                   class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500 transition cursor-pointer">
                                            <span class="text-sm font-medium transition"
                                                  :class="subtask.is_completed ? 'line-through text-slate-400' : 'text-slate-800'"
                                                  x-text="subtask.judul"></span>
                                        </label>

                                        {{-- Delete Button --}}
                                        <button type="button" 
                                                @click="deleteSubtask(subtask.id)"
                                                title="Hapus Subtask"
                                                class="text-slate-300 hover:text-rose-600 p-1 rounded-lg hover:bg-rose-50 transition opacity-0 group-hover:opacity-100 focus:opacity-100 cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            {{-- Modal Footer: Form Tambah Subtask Cepat --}}
                            <div class="p-4 bg-slate-50 border-t border-slate-100">
                                <form @submit.prevent="addSubtask()" class="flex items-center gap-2">
                                    <input type="text" 
                                           x-model="newSubtaskTitle"
                                           placeholder="Tambah langkah / subtask baru... (Tekan Enter)" 
                                           class="flex-1 px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs"
                                           :disabled="isSubtaskLoading">
                                    <button type="submit" 
                                            :disabled="isSubtaskLoading || !newSubtaskTitle.trim()"
                                            class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-50 text-white text-sm font-semibold shadow-xs hover:shadow-indigo-200 transition shrink-0 cursor-pointer">
                                        <span x-show="!isSubtaskLoading">+ Tambah</span>
                                        <span x-show="isSubtaskLoading">...</span>
                                    </button>
                                </form>
                            </div>

                        </div>
                    </template>
                </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Script Inisialisasi Grafik Dashboard & Interaksi Filter --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') return;

            Chart.defaults.font.family = 'Figtree, system-ui, sans-serif';
            Chart.defaults.color = '#64748b';
            Chart.defaults.plugins.tooltip.padding = 8;
            Chart.defaults.plugins.tooltip.cornerRadius = 8;

            const chartData = @json($chartData);
            const dashboardRoute = "{{ route('dashboard') }}";

            // 1. Dashboard Status Chart
            const dashStatusCtx = document.getElementById('dashboardStatusChart');
            if (dashStatusCtx && chartData.status) {
                new Chart(dashStatusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.status.labels,
                        datasets: [{
                            data: chartData.status.data,
                            backgroundColor: chartData.status.colors,
                            hoverOffset: 6,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 10,
                                    padding: 10,
                                    font: { size: 11, weight: '600' }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                        return ` ${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        onClick: function (event, elements) {
                            if (elements && elements.length > 0) {
                                const index = elements[0].index;
                                const key = chartData.status.keys[index];
                                if (key) {
                                    window.location.href = `${dashboardRoute}?status=${key}`;
                                }
                            }
                        }
                    }
                });
            }

            // 2. Dashboard Priority Chart
            const dashPriorityCtx = document.getElementById('dashboardPriorityChart');
            if (dashPriorityCtx && chartData.priority) {
                new Chart(dashPriorityCtx, {
                    type: 'bar',
                    data: {
                        labels: chartData.priority.labels,
                        datasets: [{
                            label: 'Jumlah Tugas',
                            data: chartData.priority.data,
                            backgroundColor: chartData.priority.colors,
                            borderRadius: 6,
                            barThickness: 28,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return ` ${context.parsed.y} tugas`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, precision: 0 },
                                grid: { color: '#f1f5f9' }
                            },
                            x: {
                                grid: { display: false }
                            }
                        },
                        onClick: function (event, elements) {
                            if (elements && elements.length > 0) {
                                const index = elements[0].index;
                                const key = chartData.priority.keys[index];
                                if (key) {
                                    window.location.href = `${dashboardRoute}?prioritas=${key}`;
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
