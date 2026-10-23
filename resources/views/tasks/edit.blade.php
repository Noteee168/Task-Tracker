<x-app-layout>
    {{-- 
        FUNGSI FILE INI:
        Ini adalah tampilan Form Edit Task (resources/views/tasks/edit.blade.php).
        Menampilkan data tugas yang sedang diedit ($task), memungkinkan Superadmin mengubah status (belum, sedang, antrian, selesai),
        mengubah prioritas, memperpanjang deadline, serta mengubah judul dan deskripsi.
        Menggunakan method spoofing @method('PUT') karena HTML form secara standar hanya mendukung GET/POST.
    --}}

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:underline font-medium">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-900 font-semibold">Edit Task</span>
                </div>
                <h2 class="font-black text-2xl text-slate-900 leading-tight">
                    ✏️ Edit Task: <span class="text-indigo-600">{{ $task->judul }}</span>
                </h2>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                &larr; Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Overdue Status Alert if this task is overdue --}}
            @if ($task->isOverdue())
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 border-l-4 border-l-rose-500 shadow-xs flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">⚠️</span>
                        <div>
                            <p class="text-sm font-bold text-rose-900">
                                Task ini telah melewati batas deadline! (Terlambat {{ $task->days_overdue }} hari)
                            </p>
                            <p class="text-xs text-rose-700 mt-0.5">
                                Segera ubah status menjadi 'Selesai' atau perbarui tanggal deadline ke masa mendatang.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
                
                <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- 1. Judul Task --}}
                    <div>
                        <label for="judul" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            Judul Task <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="judul" id="judul" value="{{ old('judul', $task->judul) }}" required class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('judul') ? 'border-rose-400 ring-1 ring-rose-400' : 'border-slate-300' }} bg-slate-50 focus:bg-white text-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">
                        @error('judul')
                            <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 2. Nama Klien --}}
                    <div>
                        <label for="nama_klien" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            Nama Klien / Perusahaan <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                        </label>
                        <input type="text" name="nama_klien" id="nama_klien" value="{{ old('nama_klien', $task->nama_klien) }}" placeholder="Contoh: PT Maju Jaya" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white text-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">
                        @error('nama_klien')
                            <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 3. Status Pengerjaan (Sorotan Utama Edit) --}}
                    <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                        <label for="status" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Status Pengerjaan <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" id="status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">
                            <option value="belum_dikerjakan" {{ old('status', $task->status) === 'belum_dikerjakan' ? 'selected' : '' }}>⏳ Belum Dikerjakan</option>
                            <option value="sedang_dikerjakan" {{ old('status', $task->status) === 'sedang_dikerjakan' ? 'selected' : '' }}>🔄 Sedang Dikerjakan</option>
                            <option value="menunggu_antrian" {{ old('status', $task->status) === 'menunggu_antrian' ? 'selected' : '' }}>⏸️ Menunggu Antrian</option>
                            <option value="selesai" {{ old('status', $task->status) === 'selesai' ? 'selected' : '' }}>✅ Selesai</option>
                        </select>
                        <p class="mt-1.5 text-xs text-slate-600">
                            Ubah ke <span class="font-bold text-emerald-600">Selesai</span> setelah tugas rampung agar tidak muncul di daftar peringatan deadline.
                        </p>
                        @error('status')
                            <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 4. Prioritas & Tanggal Deadline (Grid 2 Kolom) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Prioritas --}}
                        <div>
                            <label for="prioritas" class="block text-sm font-semibold text-slate-800 mb-1.5">
                                Prioritas <span class="text-rose-500">*</span>
                            </label>
                            <select name="prioritas" id="prioritas" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white text-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">
                                <option value="rendah" {{ old('prioritas', $task->prioritas) === 'rendah' ? 'selected' : '' }}>🌱 Rendah</option>
                                <option value="sedang" {{ old('prioritas', $task->prioritas) === 'sedang' ? 'selected' : '' }}>⚡ Sedang</option>
                                <option value="tinggi" {{ old('prioritas', $task->prioritas) === 'tinggi' ? 'selected' : '' }}>🔥 Tinggi (Urgent)</option>
                            </select>
                            @error('prioritas')
                                <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tanggal Deadline --}}
                        <div>
                            <label for="tanggal_deadline" class="block text-sm font-semibold text-slate-800 mb-1.5">
                                Tanggal Deadline <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal_deadline" id="tanggal_deadline" value="{{ old('tanggal_deadline', $task->tanggal_deadline?->format('Y-m-d')) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white text-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">
                            @error('tanggal_deadline')
                                <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- 5. Deskripsi Task --}}
                    <div>
                        <label for="deskripsi" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            Deskripsi / Catatan Tugas <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                        </label>
                        <textarea name="deskripsi" id="deskripsi" rows="4" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white text-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">{{ old('deskripsi', $task->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Submit, Batal, dan Hapus --}}
                    <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                        <button type="button" onclick="if(confirm('Hapus task ini permanen?')) { document.getElementById('delete-task-form').submit(); }" class="text-rose-600 hover:text-rose-700 text-xs font-semibold px-3 py-2 rounded-lg hover:bg-rose-50 transition">
                            🗑️ Hapus Task
                        </button>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 hover:shadow-lg hover:shadow-indigo-200 transition">
                                💾 Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Hidden Delete Form for the Edit page delete button --}}
                <form id="delete-task-form" method="POST" action="{{ route('tasks.destroy', $task) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

            </div>

            {{-- 6. Checklist & Subtasks Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8"
                 x-data="{
                     subtasks: {{ Js::from($task->subtasks) }},
                     newTitle: '',
                     isLoading: false,
                     totalCount: {{ $task->subtasks_count }},
                     completedCount: {{ $task->completed_subtasks_count }},
                     progressPercentage: {{ $task->subtasks_progress_percentage }},

                     async addSubtask() {
                         if (!this.newTitle.trim() || this.isLoading) return;
                         this.isLoading = true;
                         const title = this.newTitle.trim();

                         try {
                             const token = document.querySelector('meta[name=\'csrf-token\']').getAttribute('content');
                             const res = await fetch('{{ route('subtasks.store', $task) }}', {
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
                                 this.subtasks.push(data.subtask);
                                 this.newTitle = '';
                                 this.updateProgress(data.progress);
                             }
                         } catch (e) {
                             console.error(e);
                         } finally {
                             this.isLoading = false;
                         }
                     },

                     async toggleSubtask(subtask) {
                         const original = subtask.is_completed;
                         subtask.is_completed = !original;

                         try {
                             const token = document.querySelector('meta[name=\'csrf-token\']').getAttribute('content');
                             const res = await fetch(`/subtasks/${subtask.id}/toggle`, {
                                 method: 'PATCH',
                                 headers: {
                                     'X-CSRF-TOKEN': token,
                                     'Accept': 'application/json',
                                 }
                             });
                             const data = await res.json();
                             if (data.success) {
                                 subtask.is_completed = data.subtask.is_completed;
                                 this.updateProgress(data.progress);
                             } else {
                                 subtask.is_completed = original;
                             }
                         } catch (e) {
                             subtask.is_completed = original;
                             console.error(e);
                         }
                     },

                     async deleteSubtask(subtaskId) {
                         try {
                             const token = document.querySelector('meta[name=\'csrf-token\']').getAttribute('content');
                             const res = await fetch(`/subtasks/${subtaskId}`, {
                                 method: 'DELETE',
                                 headers: {
                                     'X-CSRF-TOKEN': token,
                                     'Accept': 'application/json',
                                 }
                             });
                             const data = await res.json();
                             if (data.success) {
                                 this.subtasks = this.subtasks.filter(s => s.id !== subtaskId);
                                 this.updateProgress(data.progress);
                             }
                         } catch (e) {
                             console.error(e);
                         }
                     },

                     updateProgress(progress) {
                         this.totalCount = progress.total;
                         this.completedCount = progress.completed;
                         this.progressPercentage = progress.percentage;
                     }
                 }">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl text-lg font-bold">☑️</span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Checklist & Subtasks</h3>
                            <p class="text-xs text-slate-500">Pecah tugas ini menjadi beberapa to-do list terperinci.</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full"
                          :class="progressPercentage === 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800'"
                          x-text="`${completedCount} / ${totalCount} Selesai (${progressPercentage}%)`"></span>
                </div>

                {{-- Progress Bar --}}
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden mb-5">
                    <div class="h-full bg-gradient-to-r from-indigo-500 to-emerald-500 rounded-full transition-all duration-300"
                         :style="`width: ${progressPercentage}%`"></div>
                </div>

                {{-- Subtask list --}}
                <div class="space-y-2 mb-4">
                    <template x-if="subtasks.length === 0">
                        <div class="p-6 text-center border-2 border-dashed border-slate-200 rounded-2xl text-slate-400 text-xs">
                            Belum ada subtask untuk tugas ini. Tambahkan langkah pertama di bawah.
                        </div>
                    </template>

                    <template x-for="subtask in subtasks" :key="subtask.id">
                        <div class="group flex items-center justify-between gap-3 p-3 rounded-xl border transition-all"
                             :class="subtask.is_completed ? 'bg-slate-50/70 border-slate-200/60' : 'bg-white border-slate-200 hover:border-indigo-200'">
                            <label class="flex items-center gap-3 flex-1 cursor-pointer select-none">
                                <input type="checkbox" 
                                       :checked="subtask.is_completed" 
                                       @change="toggleSubtask(subtask)"
                                       class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500 transition cursor-pointer">
                                <span class="text-sm font-medium transition"
                                      :class="subtask.is_completed ? 'line-through text-slate-400' : 'text-slate-800'"
                                      x-text="subtask.judul"></span>
                            </label>
                            <button type="button" 
                                    @click="deleteSubtask(subtask.id)"
                                    class="text-slate-300 hover:text-rose-600 p-1 rounded-lg hover:bg-rose-50 transition opacity-0 group-hover:opacity-100 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Add Subtask Form --}}
                <form @submit.prevent="addSubtask()" class="flex items-center gap-2">
                    <input type="text" 
                           x-model="newTitle"
                           placeholder="Tambah subtask baru..." 
                           class="flex-1 px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white text-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs"
                           :disabled="isLoading">
                    <button type="submit" 
                            :disabled="isLoading || !newTitle.trim()"
                            class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-semibold transition cursor-pointer">
                        + Tambah
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
