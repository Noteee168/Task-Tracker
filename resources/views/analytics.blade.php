<x-app-layout>
    {{-- 
        FUNGSI FILE INI:
        Halaman khusus Visualisasi & Grafik Analitik Tugas.
        Menghubungkan data tugas di database ke visualisasi grafik interaktif:
        1. Ringkasan KPI (Tingkat Penyelesaian, Tugas On-Track, Tugas Terlambat).
        2. Grafik Donut Distribusi Status Tugas (klik langsung filter ke dashboard).
        3. Grafik Bar Sebaran Prioritas Tugas.
        4. Grafik Bar Beban Waktu / Deadline (Overdue, Hari Ini, Minggu Ini, Minggu Depan, dll).
        5. Grafik Horizontal Bar Distribusi Beban per Klien (Total vs Selesai).
        6. Panel Tugas Paling Mendesak & Rekapitulasi Proyek Klien.
    --}}

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-slate-900 leading-tight flex items-center gap-2.5">
                    <span class="p-2 bg-indigo-100 text-indigo-600 rounded-2xl border border-indigo-200 inline-flex shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                    </span>
                    <span>{{ __('Grafik & Visualisasi Tugas') }}</span>
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Visualisasi data komprehensif untuk memantau performa, beban deadline, dan progres tugas Anda.
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2.5 bg-white hover:bg-slate-50 active:bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl border border-slate-300 shadow-xs transition gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span>Ke Tabel Tugas</span>
                </a>

                <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 hover:shadow-lg transition gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Task</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. KARTU METRIK KPI UTAMA --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Tasks --}}
                <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Tugas</span>
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-900">{{ $stats['total'] }}</span>
                        <span class="text-xs text-slate-500 font-medium">Tugas Terdaftar</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500">Tugas Aktif</span>
                        <span class="font-bold text-slate-800">{{ $chartData['metrics']['activeCount'] }} tugas</span>
                    </div>
                </div>

                {{-- Tingkat Penyelesaian (Completion Rate) --}}
                <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tingkat Penyelesaian</span>
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-emerald-600">{{ $chartData['metrics']['completionRate'] }}%</span>
                        <span class="text-xs text-slate-500 font-medium">({{ $stats['selesai'] }}/{{ $stats['total'] }} selesai)</span>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $chartData['metrics']['completionRate'] }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Tugas On-Track --}}
                <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tugas On-Track</span>
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-blue-600">{{ $chartData['metrics']['onTrackCount'] }}</span>
                        <span class="text-xs text-slate-500 font-medium">Sesuai Jadwal</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500">Status</span>
                        <span class="font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md">Berjalan Lancar</span>
                    </div>
                </div>

                {{-- Tugas Overdue / Terlambat --}}
                <div class="p-5 bg-white rounded-2xl border {{ $stats['overdue'] > 0 ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200/80' }} shadow-xs relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold {{ $stats['overdue'] > 0 ? 'text-rose-600' : 'text-slate-500' }} uppercase tracking-wider">Tugas Terlambat</span>
                        <div class="p-2 {{ $stats['overdue'] > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-600' }} rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-black {{ $stats['overdue'] > 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ $stats['overdue'] }}</span>
                        <span class="text-xs text-slate-500 font-medium">Lewat Batas</span>
                    </div>
                    <div class="mt-3 pt-3 border-t {{ $stats['overdue'] > 0 ? 'border-rose-100' : 'border-slate-100' }} flex items-center justify-between text-xs">
                        <span class="text-slate-500">Tindakan</span>
                        @if($stats['overdue'] > 0)
                            <a href="{{ route('dashboard', ['status' => 'overdue']) }}" class="font-bold text-rose-700 hover:underline">
                                Filter Tugas &rarr;
                            </a>
                        @else
                            <span class="font-bold text-emerald-600">Semua Terkendali ✓</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 2. GRID GRAFIK UTAMA --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- GRAFIK 1: DISTRIBUSI STATUS TUGAS (DOUGHNUT CHART) --}}
                <div class="p-6 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                    <span>Distribusi Status Tugas</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Proporsi pembagian seluruh status pengerjaan tugas saat ini</p>
                            </div>
                            <span class="text-xs bg-slate-100 text-slate-600 font-semibold px-2.5 py-1 rounded-lg">Interaktif</span>
                        </div>

                        {{-- Canvas Grafik --}}
                        <div class="my-6 relative flex items-center justify-center min-h-[260px]">
                            @if($stats['total'] > 0)
                                <canvas id="statusChart" class="max-h-[250px]"></canvas>
                            @else
                                <div class="text-center py-12 text-slate-400 text-sm">
                                    Belum ada data tugas untuk ditampilkan pada grafik.
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Quick Action Filter Links dari Grafik --}}
                    <div class="pt-4 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <a href="{{ route('dashboard', ['status' => 'belum_dikerjakan']) }}" class="p-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs text-slate-700 transition flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-400"></span> Belum Mulai</span>
                            <span class="font-bold">{{ $stats['belum_dikerjakan'] }}</span>
                        </a>
                        <a href="{{ route('dashboard', ['status' => 'sedang_dikerjakan']) }}" class="p-2 rounded-xl bg-blue-50/60 hover:bg-blue-100/70 border border-blue-200 text-xs text-blue-800 transition flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Dikerjakan</span>
                            <span class="font-bold">{{ $stats['sedang_dikerjakan'] }}</span>
                        </a>
                        <a href="{{ route('dashboard', ['status' => 'menunggu_antrian']) }}" class="p-2 rounded-xl bg-amber-50/60 hover:bg-amber-100/70 border border-amber-200 text-xs text-amber-800 transition flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Antrian</span>
                            <span class="font-bold">{{ $stats['menunggu_antrian'] }}</span>
                        </a>
                        <a href="{{ route('dashboard', ['status' => 'selesai']) }}" class="p-2 rounded-xl bg-emerald-50/60 hover:bg-emerald-100/70 border border-emerald-200 text-xs text-emerald-800 transition flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Selesai</span>
                            <span class="font-bold">{{ $stats['selesai'] }}</span>
                        </a>
                        <a href="{{ route('dashboard', ['status' => 'overdue']) }}" class="p-2 rounded-xl bg-rose-50/60 hover:bg-rose-100/70 border border-rose-200 text-xs text-rose-800 transition flex items-center justify-between col-span-2 sm:col-span-2">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Terlambat (Overdue)</span>
                            <span class="font-bold">{{ $stats['overdue'] }}</span>
                        </a>
                    </div>
                </div>

                {{-- GRAFIK 2: SEBARAN PRIORITAS TUGAS (BAR CHART) --}}
                <div class="p-6 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                    <span>Sebaran Tingkat Prioritas</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Perbandingan beban kerja berdasarkan tingkat urgensi tugas</p>
                            </div>
                            <span class="text-xs bg-slate-100 text-slate-600 font-semibold px-2.5 py-1 rounded-lg">Prioritas</span>
                        </div>

                        {{-- Canvas Grafik --}}
                        <div class="my-6 relative flex items-center justify-center min-h-[260px]">
                            @if($stats['total'] > 0)
                                <canvas id="priorityChart" class="max-h-[250px]"></canvas>
                            @else
                                <div class="text-center py-12 text-slate-400 text-sm">
                                    Belum ada data tugas untuk ditampilkan pada grafik.
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Quick Action Filter Links dari Prioritas --}}
                    <div class="pt-4 border-t border-slate-100 grid grid-cols-3 gap-2">
                        <a href="{{ route('dashboard', ['prioritas' => 'tinggi']) }}" class="p-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-center transition">
                            <div class="text-xs font-bold text-rose-700">🔥 Prioritas Tinggi</div>
                            <div class="text-lg font-black text-rose-900 mt-0.5">{{ $chartData['priority']['data'][0] }}</div>
                        </a>
                        <a href="{{ route('dashboard', ['prioritas' => 'sedang']) }}" class="p-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-center transition">
                            <div class="text-xs font-bold text-amber-700">⚡ Prioritas Sedang</div>
                            <div class="text-lg font-black text-amber-900 mt-0.5">{{ $chartData['priority']['data'][1] }}</div>
                        </a>
                        <a href="{{ route('dashboard', ['prioritas' => 'rendah']) }}" class="p-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-center transition">
                            <div class="text-xs font-bold text-emerald-700">🌱 Prioritas Rendah</div>
                            <div class="text-lg font-black text-emerald-900 mt-0.5">{{ $chartData['priority']['data'][2] }}</div>
                        </a>
                    </div>
                </div>

                {{-- GRAFIK 3: BEBAN WAKTU & TIMELINE DEADLINE (BAR CHART) --}}
                <div class="p-6 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span>Sebaran Deadline & Beban Waktu</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Pemetaan jatuh tempo tugas yang belum selesai berdasarkan rentang waktu</p>
                        </div>
                        <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 font-semibold px-2.5 py-1 rounded-lg">Timeline</span>
                    </div>

                    {{-- Canvas Grafik Deadline --}}
                    <div class="my-6 relative flex items-center justify-center min-h-[260px]">
                        @if($stats['total'] > 0)
                            <canvas id="deadlineChart" class="max-h-[250px]"></canvas>
                        @else
                            <div class="text-center py-12 text-slate-400 text-sm">
                                Belum ada data deadline tugas untuk ditampilkan.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- GRAFIK 4: DISTRIBUSI TUGAS PER KLIEN (HORIZONTAL BAR CHART) --}}
                <div class="p-6 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                                <span>Beban Tugas Berdasarkan Klien</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Top klien dengan jumlah tugas dan rasio penyelesaian</p>
                        </div>
                        <span class="text-xs bg-sky-50 text-sky-700 border border-sky-200 font-semibold px-2.5 py-1 rounded-lg">Klien</span>
                    </div>

                    {{-- Canvas Grafik Klien --}}
                    <div class="my-6 relative flex items-center justify-center min-h-[260px]">
                        @if(count($chartData['client']['labels']) > 0)
                            <canvas id="clientChart" class="max-h-[250px]"></canvas>
                        @else
                            <div class="text-center py-12 text-slate-400 text-sm">
                                Belum ada data nama klien pada tugas yang terdaftar.
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- 3. PANEL DETAIL: TUGAS MENDESAK & REKAP KLIEN --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                {{-- Tugas Paling Mendesak (Overdue / < 3 Hari) --}}
                <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
                                    <span>🚨 Tugas Paling Mendesak</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Tugas yang telah overdue atau jatuh tempo dalam 3 hari ke depan</p>
                            </div>
                            <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                                Semua Tugas &rarr;
                            </a>
                        </div>

                        <div class="divide-y divide-slate-100 mt-2">
                            @forelse($urgentTasks as $task)
                                @php
                                    $isOverdue = $task->isOverdue();
                                @endphp
                                <div class="py-3.5 flex items-center justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('tasks.edit', $task) }}" class="font-bold text-sm text-slate-900 hover:text-indigo-600 truncate">
                                                {{ $task->judul }}
                                            </a>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $task->prioritas_badge_class }}">
                                                {{ ucfirst($task->prioritas) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-3 text-xs text-slate-500 mt-1">
                                            @if($task->nama_klien)
                                                <span>👤 {{ $task->nama_klien }}</span>
                                                <span>&bull;</span>
                                            @endif
                                            <span class="inline-flex items-center gap-1 {{ $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-600' }}">
                                                📅 {{ $task->tanggal_deadline->format('d M Y') }}
                                                @if($isOverdue)
                                                    <span class="text-[10px] bg-rose-100 text-rose-700 px-1.5 py-0.2 rounded font-extrabold">Terlambat</span>
                                                @elseif($task->tanggal_deadline->isToday())
                                                    <span class="text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.2 rounded font-extrabold">Hari Ini</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    <div class="shrink-0 flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $task->status_badge_class }}">
                                            {{ $task->status_label }}
                                        </span>
                                        <a href="{{ route('tasks.edit', $task) }}" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-lg transition" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-slate-400 text-xs">
                                    ✨ Tidak ada tugas mendesak atau terlambat saat ini. Kerja bagus!
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Rekapitulasi Beban per Klien --}}
                <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
                                <span>👥 Rekapitulasi Klien</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Rincian performa penyelesaian per klien</p>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100 mt-2">
                        @forelse($clientsSummary as $client)
                            @php
                                $percent = $client->total_tasks > 0 ? round(($client->completed_tasks / $client->total_tasks) * 100) : 0;
                            @endphp
                            <div class="py-3">
                                <div class="flex items-center justify-between text-xs mb-1.5">
                                    <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                        {{ $client->nama_klien }}
                                    </span>
                                    <span class="text-slate-500">
                                        <strong class="text-slate-800">{{ $client->completed_tasks }}</strong>/{{ $client->total_tasks }} selesai ({{ $percent }}%)
                                        @if($client->overdue_tasks > 0)
                                            <span class="ms-1.5 text-rose-600 font-bold text-[10px] bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded">⚠️ {{ $client->overdue_tasks }} Overdue</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400 text-xs">
                                Belum ada data klien terdaftar.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- SCRIPTS INISIALISASI CHART.JS DENGAN DATA INTERAKTIF --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Pengaturan Font & Tooltip Global Chart.js
            Chart.defaults.font.family = 'Figtree, system-ui, sans-serif';
            Chart.defaults.color = '#64748b';
            Chart.defaults.plugins.tooltip.padding = 10;
            Chart.defaults.plugins.tooltip.cornerRadius = 10;

            const chartData = @json($chartData);
            const dashboardRoute = "{{ route('dashboard') }}";

            // -------------------------------------------------------------
            // 1. GRAFIK STATUS TUGAS (DOUGHNUT CHART DENGAN KLIK FILTER)
            // -------------------------------------------------------------
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.status.labels,
                        datasets: [{
                            data: chartData.status.data,
                            backgroundColor: chartData.status.colors,
                            hoverOffset: 8,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 15,
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
                                        return ` ${label}: ${value} tugas (${percentage}%)`;
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

            // -------------------------------------------------------------
            // 2. GRAFIK PRIORITAS TUGAS (BAR CHART)
            // -------------------------------------------------------------
            const priorityCtx = document.getElementById('priorityChart');
            if (priorityCtx) {
                new Chart(priorityCtx, {
                    type: 'bar',
                    data: {
                        labels: chartData.priority.labels,
                        datasets: [{
                            label: 'Jumlah Tugas',
                            data: chartData.priority.data,
                            backgroundColor: chartData.priority.colors,
                            borderRadius: 8,
                            barThickness: 36,
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

            // -------------------------------------------------------------
            // 3. GRAFIK DEADLINE TIMELINE (BAR CHART)
            // -------------------------------------------------------------
            const deadlineCtx = document.getElementById('deadlineChart');
            if (deadlineCtx) {
                new Chart(deadlineCtx, {
                    type: 'bar',
                    data: {
                        labels: chartData.deadline.labels,
                        datasets: [{
                            label: 'Tugas Belum Selesai',
                            data: chartData.deadline.data,
                            backgroundColor: chartData.deadline.colors,
                            borderRadius: 8,
                            barThickness: 32,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
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
                        }
                    }
                });
            }

            // -------------------------------------------------------------
            // 4. GRAFIK TOP KLIEN (HORIZONTAL BAR CHART)
            // -------------------------------------------------------------
            const clientCtx = document.getElementById('clientChart');
            if (clientCtx && chartData.client.labels.length > 0) {
                new Chart(clientCtx, {
                    type: 'bar',
                    data: {
                        labels: chartData.client.labels,
                        datasets: [
                            {
                                label: 'Tugas Selesai',
                                data: chartData.client.completed,
                                backgroundColor: '#10b981',
                                borderRadius: 6,
                            },
                            {
                                label: 'Total Tugas',
                                data: chartData.client.total,
                                backgroundColor: '#6366f1',
                                borderRadius: 6,
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { boxWidth: 12, font: { size: 11, weight: '600' } }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, precision: 0 },
                                grid: { color: '#f1f5f9' }
                            },
                            y: {
                                grid: { display: false }
                            }
                        },
                        onClick: function (event, elements) {
                            if (elements && elements.length > 0) {
                                const index = elements[0].index;
                                const clientName = chartData.client.labels[index];
                                if (clientName) {
                                    window.location.href = `${dashboardRoute}?search=${encodeURIComponent(clientName)}`;
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
