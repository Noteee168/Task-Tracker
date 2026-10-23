<x-app-layout>
    {{-- 
        FUNGSI FILE INI:
        Halaman Tong Sampah (Trash / Soft Deletes).
        Menampilkan daftar tugas yang telah dihapus sementara oleh pengguna.
        Fitur:
        1. Restore (Memulihkan kembali tugas ke dashboard aktif).
        2. Force Delete (Menghapus permanen tugas dari database).
        3. Kosongkan Semua Sampah (Membersihkan seluruh isi tong sampah).
    --}}

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:underline font-medium">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-900 font-semibold">Tong Sampah</span>
                </div>
                <h2 class="font-black text-2xl text-slate-900 leading-tight flex items-center gap-2.5">
                    <span class="p-2 bg-rose-100 text-rose-600 rounded-2xl border border-rose-200 inline-flex shadow-2xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </span>
                    <span>{{ __('Tong Sampah (Trash)') }}</span>
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Tugas di halaman ini dapat dipulihkan kembali ke Dashboard atau dihapus secara permanen dari database.
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs gap-1.5">
                    &larr; Kembali ke Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        showForceDeleteModal: false,
        showEmptyTrashModal: false,
        actionUrl: '',
        taskTitle: '',
        confirmForceDelete(title, url) {
            this.taskTitle = title;
            this.actionUrl = url;
            this.showForceDeleteModal = true;
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Message / Notifikasi Sukses --}}
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

            {{-- Toolbar Atas: Status Isi Tong Sampah & Tombol Kosongkan --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-lg">
                        🗑️
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Total Tugas di Tong Sampah</h3>
                        <p class="text-xs text-slate-500">
                            Terdapat <span class="font-extrabold text-slate-900">{{ $trashedCount }}</span> tugas yang saat ini diarsipkan di tong sampah.
                        </p>
                    </div>
                </div>

                @if($trashedCount > 0)
                    <button type="button" 
                            @click="showEmptyTrashModal = true"
                            class="inline-flex items-center justify-center px-4 py-2 bg-rose-50 text-rose-700 hover:bg-rose-600 hover:text-white rounded-xl border border-rose-200 font-bold text-xs transition duration-150 shadow-2xs gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Kosongkan Semua Sampah</span>
                    </button>
                @endif
            </div>

            {{-- Tabel Daftar Tugas di Tong Sampah --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/90 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3.5 px-4 sm:px-6">Tugas</th>
                                <th class="py-3.5 px-4 sm:px-6">Klien</th>
                                <th class="py-3.5 px-4 sm:px-6">Status Terakhir</th>
                                <th class="py-3.5 px-4 sm:px-6">Prioritas</th>
                                <th class="py-3.5 px-4 sm:px-6">Waktu Dihapus</th>
                                <th class="py-3.5 px-4 sm:px-6 text-right">Aksi Pemulihan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($trashedTasks as $task)
                                <tr class="transition-colors hover:bg-slate-50/70">
                                    {{-- Kolom Judul --}}
                                    <td class="py-4 px-4 sm:px-6">
                                        <div class="font-bold text-slate-800 leading-snug">
                                            {{ $task->judul }}
                                        </div>
                                        @if($task->deskripsi)
                                            <p class="text-xs text-slate-400 mt-0.5 line-clamp-1 max-w-sm">
                                                {{ $task->deskripsi }}
                                            </p>
                                        @endif
                                    </td>

                                    {{-- Kolom Klien --}}
                                    <td class="py-4 px-4 sm:px-6 whitespace-nowrap text-xs text-slate-600">
                                        {{ $task->nama_klien ?: '-' }}
                                    </td>

                                    {{-- Kolom Status --}}
                                    <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $task->status_badge_class }}">
                                            {{ $task->status_label }}
                                        </span>
                                    </td>

                                    {{-- Kolom Prioritas --}}
                                    <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $task->prioritas_badge_class }}">
                                            {{ $task->prioritas_label }}
                                        </span>
                                    </td>

                                    {{-- Kolom Tanggal Dihapus --}}
                                    <td class="py-4 px-4 sm:px-6 whitespace-nowrap text-xs text-slate-500">
                                        <div>{{ $task->deleted_at->translatedFormat('d M Y, H:i') }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $task->deleted_at->diffForHumans() }}</div>
                                    </td>

                                    {{-- Kolom Aksi --}}
                                    <td class="py-4 px-4 sm:px-6 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Tombol Restore (Pulihkan) --}}
                                            <form action="{{ route('tasks.restore', $task->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        title="Pulihkan tugas ke dashboard"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white rounded-xl text-xs font-bold border border-indigo-200 transition duration-150 cursor-pointer shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                    <span>Pulihkan</span>
                                                </button>
                                            </form>

                                            {{-- Tombol Force Delete (Hapus Permanen) --}}
                                            <button type="button" 
                                                    @click="confirmForceDelete('{{ addslashes($task->judul) }}', '{{ route('tasks.force-delete', $task->id) }}')"
                                                    title="Hapus permanen dari database"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white rounded-xl text-xs font-bold border border-rose-200 transition duration-150 cursor-pointer shadow-2xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span>Permanen</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-16 text-center text-slate-400">
                                        <div class="max-w-sm mx-auto space-y-3">
                                            <div class="text-4xl">✨</div>
                                            <p class="font-bold text-slate-700">Tong Sampah Bersih</p>
                                            <p class="text-xs text-slate-500">
                                                Tidak ada tugas yang sedang diarsipkan di tong sampah. Semua tugas Anda aktif dan aman.
                                            </p>
                                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-xl hover:bg-indigo-700 transition">
                                                Kembali ke Dashboard
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($trashedTasks->hasPages())
                    <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                        {{ $trashedTasks->links() }}
                    </div>
                @endif
            </div>

            {{-- Modal Konfirmasi Hapus Permanen Single Task --}}
            <div x-show="showForceDeleteModal" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 aria-labelledby="modal-title" role="dialog" aria-modal="true"
                 @keydown.escape.window="showForceDeleteModal = false">
                
                <div x-show="showForceDeleteModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
                     @click="showForceDeleteModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showForceDeleteModal"
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
                                    💥
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-900" id="modal-title">
                                        Hapus Permanen dari Database?
                                    </h3>
                                    <p class="mt-2 text-sm text-slate-500">
                                        Apakah Anda yakin ingin menghapus permanen tugas <span class="font-bold text-slate-900" x-text="'\'' + taskTitle + '\''"></span>?
                                    </p>
                                    <p class="mt-2 text-xs text-rose-600 font-medium bg-rose-50 p-2.5 rounded-xl border border-rose-100">
                                        ⚠️ Tindakan ini permanen dan tidak dapat dipulihkan lagi. Data akan dihapus seutuhnya dari MySQL.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100">
                            <button type="button" 
                                    @click="showForceDeleteModal = false"
                                    class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer shadow-2xs">
                                Batal
                            </button>
                            <form :action="actionUrl" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-sm font-semibold text-white shadow-md shadow-rose-200 transition cursor-pointer">
                                    Ya, Hapus Permanen
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Konfirmasi Kosongkan Semua Sampah --}}
            <div x-show="showEmptyTrashModal" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 aria-labelledby="modal-title" role="dialog" aria-modal="true"
                 @keydown.escape.window="showEmptyTrashModal = false">
                
                <div x-show="showEmptyTrashModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
                     @click="showEmptyTrashModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showEmptyTrashModal"
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
                                    🧹
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-900">
                                        Kosongkan Seluruh Tong Sampah?
                                    </h3>
                                    <p class="mt-2 text-sm text-slate-500">
                                        Seluruh <span class="font-extrabold text-slate-900">{{ $trashedCount }}</span> tugas di dalam tong sampah akan dihapus secara permanen dari database.
                                    </p>
                                    <p class="mt-2 text-xs text-rose-600 font-medium bg-rose-50 p-2.5 rounded-xl border border-rose-100">
                                        ⚠️ Tindakan ini tidak dapat dibatalkan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100">
                            <button type="button" 
                                    @click="showEmptyTrashModal = false"
                                    class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer shadow-2xs">
                                Batal
                            </button>
                            <form action="{{ route('tasks.empty-trash') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-sm font-semibold text-white shadow-md shadow-rose-200 transition cursor-pointer">
                                    Ya, Kosongkan Semua
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
