<x-app-layout>
    {{-- 
        FUNGSI FILE INI:
        Ini adalah tampilan Form Tambah Task Baru (resources/views/tasks/create.blade.php).
        Menyediakan formulir input untuk judul, deskripsi, nama klien, prioritas, tanggal deadline, dan status awal.
        Dilengkapi penanganan pesan error validasi ($errors) dan fungsi old() agar data tidak hilang jika validasi gagal.
    --}}

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:underline font-medium">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-900 font-semibold">Tambah Task</span>
                </div>
                <h2 class="font-black text-2xl text-slate-900 leading-tight">
                    ➕ Buat Task Baru
                </h2>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                &larr; Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
                
                <form method="POST" action="{{ route('tasks.store') }}" class="space-y-6">
                    @csrf

                    {{-- 1. Judul Task --}}
                    <div>
                        <label for="judul" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            Judul Task <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="judul" id="judul" value="{{ old('judul') }}" required placeholder="Contoh: Selesaikan API Integrasi Pembayaran" class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('judul') ? 'border-rose-400 ring-1 ring-rose-400' : 'border-slate-300' }} bg-slate-50 focus:bg-white text-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">
                        @error('judul')
                            <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 2. Nama Klien --}}
                    <div>
                        <label for="nama_klien" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            Nama Klien / Perusahaan <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                        </label>
                        <input type="text" name="nama_klien" id="nama_klien" value="{{ old('nama_klien') }}" placeholder="Contoh: PT Maju Jaya" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white text-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">
                        @error('nama_klien')
                            <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 3. Prioritas & Tanggal Deadline (Grid 2 Kolom) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Prioritas --}}
                        <div>
                            <label for="prioritas" class="block text-sm font-semibold text-slate-800 mb-1.5">
                                Prioritas <span class="text-rose-500">*</span>
                            </label>
                            <select name="prioritas" id="prioritas" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white text-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">
                                <option value="rendah" {{ old('prioritas') === 'rendah' ? 'selected' : '' }}>🌱 Rendah (Bisa santai)</option>
                                <option value="sedang" {{ old('prioritas', 'sedang') === 'sedang' ? 'selected' : '' }}>⚡ Sedang (Normal)</option>
                                <option value="tinggi" {{ old('prioritas') === 'tinggi' ? 'selected' : '' }}>🔥 Tinggi (Segera / Urgent)</option>
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
                            <input type="date" name="tanggal_deadline" id="tanggal_deadline" value="{{ old('tanggal_deadline', request('deadline', date('Y-m-d'))) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white text-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">
                            @error('tanggal_deadline')
                                <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- 4. Status Pengerjaan Awal --}}
                    <div>
                        <label for="status" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            Status Awal <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" id="status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white text-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">
                            <option value="belum_dikerjakan" {{ old('status', 'belum_dikerjakan') === 'belum_dikerjakan' ? 'selected' : '' }}>⏳ Belum Dikerjakan</option>
                            <option value="sedang_dikerjakan" {{ old('status') === 'sedang_dikerjakan' ? 'selected' : '' }}>🔄 Sedang Dikerjakan</option>
                            <option value="menunggu_antrian" {{ old('status') === 'menunggu_antrian' ? 'selected' : '' }}>⏸️ Menunggu Antrian</option>
                            <option value="selesai" {{ old('status') === 'selesai' ? 'selected' : '' }}>✅ Selesai</option>
                        </select>
                        @error('status')
                            <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 5. Deskripsi Task --}}
                    <div>
                        <label for="deskripsi" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            Deskripsi / Catatan Tugas <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                        </label>
                        <textarea name="deskripsi" id="deskripsi" rows="4" placeholder="Tuliskan catatan teknis, link referensi, atau detail persyaratan tugas..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white text-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-2xs">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Submit & Batal --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                        <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 hover:shadow-lg hover:shadow-indigo-200 transition">
                            💾 Simpan Task Baru
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
