<x-app-layout>
    {{-- 
        FUNGSI FILE INI:
        Halaman Kalender Deadline Tugas (resources/views/calendar.blade.php).
        Menampilkan jadwal dan deadline tugas per bulan dengan tampilan kalender visual yang interaktif.
        Fitur:
        1. Navigasi bulan & tahun (Bulan Lalu, Bulan Depan, Hari Ini).
        2. Filter tugas di kalender (Semua, Prioritas Tinggi, Overdue, Belum Selesai, Selesai).
        3. Chip tugas berwarna sesuai status & badge prioritas.
        4. Klik tanggal untuk langsung tambah tugas pada tanggal tersebut.
        5. Klik tugas untuk membuka modal detail lengkap tugas (dengan aksi edit dan tandai selesai).
        6. Panel tugas mendesak 7 hari ke depan.
    --}}

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:underline font-medium">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-900 font-semibold">Kalender</span>
                </div>
                <h2 class="font-black text-2xl text-slate-900 leading-tight flex items-center gap-2.5">
                    <span class="p-2 bg-indigo-100 text-indigo-600 rounded-2xl border border-indigo-200 inline-flex shadow-2xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <span>{{ __('Kalender Deadline Tugas') }}</span>
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Pantau agenda deadline dan distribusikan jadwal pengerjaan proyek Anda secara visual.
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 hover:shadow-lg hover:shadow-indigo-200 transition-all duration-150 ease-in-out gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Task</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        currentYear: new Date().getFullYear(),
        currentMonth: new Date().getMonth(),
        activeFilter: 'all',
        allTasks: {{ Js::from($tasks) }},
        showDetailModal: false,
        selectedTask: null,
        monthNames: [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ],
        dayNames: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],

        get currentMonthName() {
            return this.monthNames[this.currentMonth] + ' ' + this.currentYear;
        },

        prevMonth() {
            if (this.currentMonth === 0) {
                this.currentMonth = 11;
                this.currentYear--;
            } else {
                this.currentMonth--;
            }
        },

        nextMonth() {
            if (this.currentMonth === 11) {
                this.currentMonth = 0;
                this.currentYear++;
            } else {
                this.currentMonth++;
            }
        },

        goToToday() {
            const now = new Date();
            this.currentYear = now.getFullYear();
            this.currentMonth = now.getMonth();
        },

        formatDate(y, m, d) {
            const mm = String(m + 1).padStart(2, '0');
            const dd = String(d).padStart(2, '0');
            return `${y}-${mm}-${dd}`;
        },

        get calendarDays() {
            const days = [];
            const year = this.currentYear;
            const month = this.currentMonth;

            const firstDay = new Date(year, month, 1);
            const firstDayIndex = (firstDay.getDay() + 6) % 7; // Senin = 0, Minggu = 6

            const prevMonthLastDay = new Date(year, month, 0).getDate();
            const currentMonthDays = new Date(year, month + 1, 0).getDate();

            const todayFormatted = this.formatDate(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());

            // Hari padding dari bulan sebelumnya
            for (let i = firstDayIndex - 1; i >= 0; i--) {
                const dayNum = prevMonthLastDay - i;
                const prevMonthIndex = month === 0 ? 11 : month - 1;
                const prevYear = month === 0 ? year - 1 : year;
                const dateStr = this.formatDate(prevYear, prevMonthIndex, dayNum);

                days.push({
                    dayNum: dayNum,
                    dateStr: dateStr,
                    isCurrentMonth: false,
                    isToday: dateStr === todayFormatted,
                    isWeekend: false,
                });
            }

            // Hari di bulan aktif
            for (let d = 1; d <= currentMonthDays; d++) {
                const dateStr = this.formatDate(year, month, d);
                const dayOfWeek = (new Date(year, month, d).getDay() + 6) % 7;

                days.push({
                    dayNum: d,
                    dateStr: dateStr,
                    isCurrentMonth: true,
                    isToday: dateStr === todayFormatted,
                    isWeekend: dayOfWeek >= 5,
                });
            }

            // Hari padding bulan berikutnya hingga genap barisnya (kelipatan 7)
            let nextDay = 1;
            while (days.length % 7 !== 0 || days.length < 35) {
                const nextMonthIndex = month === 11 ? 0 : month + 1;
                const nextYear = month === 11 ? year + 1 : year;
                const dateStr = this.formatDate(nextYear, nextMonthIndex, nextDay);

                days.push({
                    dayNum: nextDay,
                    dateStr: dateStr,
                    isCurrentMonth: false,
                    isToday: dateStr === todayFormatted,
                    isWeekend: false,
                });
                nextDay++;
            }

            return days;
        },

        getFilteredTasksForDate(dateStr) {
            return this.allTasks.filter(task => {
                if (task.tanggal_deadline !== dateStr) return false;
                if (this.activeFilter === 'all') return true;
                if (this.activeFilter === 'tinggi') return task.prioritas === 'tinggi';
                if (this.activeFilter === 'overdue') return task.is_overdue;
                if (this.activeFilter === 'belum_selesai') return task.status !== 'selesai';
                if (this.activeFilter === 'selesai') return task.status === 'selesai';
                return true;
            });
        },

        get monthSummary() {
            const ym = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}`;
            const monthTasks = this.allTasks.filter(t => t.tanggal_deadline && t.tanggal_deadline.startsWith(ym));
            return {
                total: monthTasks.length,
                selesai: monthTasks.filter(t => t.status === 'selesai').length,
                overdue: monthTasks.filter(t => t.is_overdue).length,
                pending: monthTasks.filter(t => t.status !== 'selesai').length,
            };
        },

        get upcomingDeadlines() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const in7Days = new Date();
            in7Days.setDate(today.getDate() + 7);
            in7Days.setHours(23, 59, 59, 999);

            return this.allTasks
                .filter(t => {
                    if (!t.tanggal_deadline) return false;
                    const d = new Date(t.tanggal_deadline + 'T00:00:00');
                    return d >= today && d <= in7Days && t.status !== 'selesai';
                })
                .slice(0, 6);
        },

        openTask(task) {
            this.selectedTask = task;
            this.showDetailModal = true;
        },

        closeTask() {
            this.showDetailModal = false;
            this.selectedTask = null;
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. Kartu Ringkasan KPI Bulan Ini --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tugas Bulan Ini</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-black text-slate-900" x-text="monthSummary.total"></span>
                        <span class="text-xs text-slate-500 font-medium">deadline</span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tugas Berjalan</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-black text-blue-600" x-text="monthSummary.pending"></span>
                        <span class="text-xs text-blue-500 font-medium">aktif</span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Selesai</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-black text-emerald-600" x-text="monthSummary.selesai"></span>
                        <span class="text-xs text-emerald-500 font-medium">tuntas</span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Terlambat (Overdue)</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-black text-rose-600" x-text="monthSummary.overdue"></span>
                        <span class="text-xs text-rose-500 font-medium">perlu tindakan</span>
                    </div>
                </div>
            </div>

            {{-- 2. Toolbar Kalender: Navigasi Bulan, Hari Ini, dan Filter Kategori --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                
                {{-- Kontrol Navigasi Bulan --}}
                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-slate-100 rounded-xl p-1 border border-slate-200">
                        <button @click="prevMonth()" 
                                title="Bulan Sebelumnya" 
                                class="p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-white transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <button @click="goToToday()" 
                                class="px-3 py-1.5 text-xs font-bold text-slate-700 hover:text-indigo-600 hover:bg-white rounded-lg transition cursor-pointer">
                            Hari Ini
                        </button>

                        <button @click="nextMonth()" 
                                title="Bulan Berikutnya" 
                                class="p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-white transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <h3 class="text-xl font-black text-slate-900 tracking-tight" x-text="currentMonthName"></h3>
                </div>

                {{-- Filter Kategori Tugas di Kalender --}}
                <div class="flex flex-wrap items-center gap-1.5">
                    <button @click="activeFilter = 'all'" 
                            :class="activeFilter === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" 
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer">
                        Semua
                    </button>
                    <button @click="activeFilter = 'tinggi'" 
                            :class="activeFilter === 'tinggi' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-700 hover:bg-rose-100'" 
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer">
                        🔥 Prioritas Tinggi
                    </button>
                    <button @click="activeFilter = 'overdue'" 
                            :class="activeFilter === 'overdue' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'" 
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer">
                        ⚠️ Overdue
                    </button>
                    <button @click="activeFilter = 'belum_selesai'" 
                            :class="activeFilter === 'belum_selesai' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'" 
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer">
                        ⏳ Berjalan
                    </button>
                    <button @click="activeFilter = 'selesai'" 
                            :class="activeFilter === 'selesai' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'" 
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer">
                        ✓ Selesai
                    </button>
                </div>
            </div>

            {{-- 3. Grid Kalender Bulanan --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                
                {{-- Header Nama Hari (Senin - Minggu) --}}
                <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50/80 text-center py-3 text-xs font-extrabold uppercase tracking-wider text-slate-600">
                    <template x-for="(dayName, idx) in dayNames" :key="idx">
                        <div :class="idx >= 5 ? 'text-rose-500 font-black' : 'text-slate-600'" x-text="dayName"></div>
                    </template>
                </div>

                {{-- Kotak Tanggal (7 Kolom) --}}
                <div class="grid grid-cols-7 divide-x divide-y divide-slate-100">
                    <template x-for="(day, index) in calendarDays" :key="index">
                        <div class="min-h-[110px] sm:min-h-[135px] p-1.5 sm:p-2 transition relative group flex flex-col justify-between"
                             :class="{
                                'bg-slate-50/60 text-slate-400': !day.isCurrentMonth,
                                'bg-white hover:bg-slate-50/70': day.isCurrentMonth,
                                'bg-indigo-50/30 ring-2 ring-indigo-500/20 ring-inset': day.isToday
                             }">
                            
                            {{-- Header Sel Tanggal: Angka Tanggal + Tombol Cepat Tambah Task --}}
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs sm:text-sm font-bold flex items-center justify-center rounded-lg transition"
                                      :class="{
                                          'w-6 h-6 sm:w-7 sm:h-7 bg-indigo-600 text-white shadow-xs font-extrabold': day.isToday,
                                          'text-slate-800': day.isCurrentMonth && !day.isToday,
                                          'text-slate-400': !day.isCurrentMonth
                                      }"
                                      x-text="day.dayNum">
                                </span>

                                {{-- Tombol Cepat Tambah Task pada Tanggal Ini --}}
                                <a :href="'{{ route('tasks.create') }}?deadline=' + day.dateStr"
                                   title="Tambah tugas dengan deadline tanggal ini"
                                   class="opacity-0 group-hover:opacity-100 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-100 p-1 rounded-md transition duration-150 cursor-pointer text-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                    </svg>
                                </a>
                            </div>

                            {{-- Daftar Tugas pada Tanggal Tersebut --}}
                            <div class="space-y-1 overflow-y-auto max-h-[85px] sm:max-h-[95px] pr-0.5 custom-scroll">
                                <template x-for="task in getFilteredTasksForDate(day.dateStr)" :key="task.id">
                                    <div @click="openTask(task)"
                                         class="px-2 py-1 rounded-lg text-[11px] font-semibold border transition shadow-2xs cursor-pointer truncate flex items-center gap-1.5 select-none"
                                         :class="{
                                             'bg-rose-50 text-rose-800 border-rose-200 hover:bg-rose-100': task.is_overdue,
                                             'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100 line-through opacity-80': task.status === 'selesai',
                                             'bg-blue-50 text-blue-800 border-blue-200 hover:bg-blue-100': !task.is_overdue && task.status === 'sedang_dikerjakan',
                                             'bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100': !task.is_overdue && task.status === 'menunggu_antrian',
                                             'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200': !task.is_overdue && task.status === 'belum_dikerjakan'
                                         }"
                                         :title="task.judul + ' (' + task.status_label + ')'">
                                        
                                        {{-- Indikator Prioritas (Titik Berwarna) --}}
                                        <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                              :class="{
                                                  'bg-rose-500': task.prioritas === 'tinggi',
                                                  'bg-amber-500': task.prioritas === 'sedang',
                                                  'bg-emerald-500': task.prioritas === 'rendah'
                                              }"></span>

                                        <span class="truncate" x-text="task.judul"></span>
                                    </div>
                                </template>
                            </div>

                            {{-- Footer Kosong untuk Jarak Rapi --}}
                            <div class="h-0.5"></div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 4. Panel Bawah: Deadline Mendekati (7 Hari Ke Depan) --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-7">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                            <span>⚡</span>
                            <span>Deadline 7 Hari Ke Depan</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Tugas yang mendekati batas waktu penyelesaian dan butuh perhatian segera.
                        </p>
                    </div>
                    <a href="{{ route('dashboard') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                        Lihat Semua di Dashboard &rarr;
                    </a>
                </div>

                <template x-if="upcomingDeadlines.length === 0">
                    <div class="text-center py-8 text-slate-400 text-sm">
                        🎉 Tidak ada tugas mendesak dalam 7 hari ke depan! Semua jadwal terkontrol rapi.
                    </div>
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <template x-for="task in upcomingDeadlines" :key="task.id">
                        <div @click="openTask(task)" 
                             class="p-4 rounded-2xl border border-slate-200 hover:border-indigo-300 hover:shadow-md transition duration-150 cursor-pointer bg-slate-50/50 hover:bg-white flex flex-col justify-between gap-3">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider"
                                          :class="{
                                              'bg-rose-100 text-rose-700': task.prioritas === 'tinggi',
                                              'bg-amber-100 text-amber-700': task.prioritas === 'sedang',
                                              'bg-emerald-100 text-emerald-700': task.prioritas === 'rendah'
                                          }"
                                          x-text="task.prioritas_label">
                                    </span>
                                    <span class="text-xs font-bold text-indigo-600 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span x-text="task.formatted_deadline"></span>
                                    </span>
                                </div>
                                <h4 class="font-bold text-sm text-slate-900 line-clamp-1" x-text="task.judul"></h4>
                                <template x-if="task.nama_klien">
                                    <p class="text-xs text-slate-500 mt-0.5 truncate flex items-center gap-1">
                                        <span>🏢</span>
                                        <span x-text="task.nama_klien"></span>
                                    </p>
                                </template>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                                <span class="font-semibold text-slate-600" x-text="task.status_label"></span>
                                <span class="text-indigo-600 font-bold hover:underline">Detail &rarr;</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 5. Modal Popout Detail Tugas (Slide-in / Pop-up Elegan) --}}
            <div x-show="showDetailModal" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto" 
                 aria-labelledby="modal-title" 
                 role="dialog" 
                 aria-modal="true"
                 @keydown.escape.window="closeTask()">
                
                {{-- Backdrop Blur --}}
                <div x-show="showDetailModal" 
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                     @click="closeTask()"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showDetailModal" 
                         x-transition:enter="ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 sm:p-7 border border-slate-200"
                         @click.outside="closeTask()">
                        
                        <template x-if="selectedTask">
                            <div>
                                {{-- Header Modal --}}
                                <div class="flex items-start justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold"
                                                  :class="{
                                                      'bg-rose-100 text-rose-800 border border-rose-200': selectedTask.prioritas === 'tinggi',
                                                      'bg-amber-100 text-amber-800 border border-amber-200': selectedTask.prioritas === 'sedang',
                                                      'bg-emerald-100 text-emerald-800 border border-emerald-200': selectedTask.prioritas === 'rendah'
                                                  }">
                                                Prioritas: <span x-text="selectedTask.prioritas_label"></span>
                                            </span>

                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold"
                                                  :class="{
                                                      'bg-emerald-100 text-emerald-800 border border-emerald-200': selectedTask.status === 'selesai',
                                                      'bg-blue-100 text-blue-800 border border-blue-200': selectedTask.status === 'sedang_dikerjakan',
                                                      'bg-amber-100 text-amber-800 border border-amber-200': selectedTask.status === 'menunggu_antrian',
                                                      'bg-slate-100 text-slate-800 border border-slate-200': selectedTask.status === 'belum_dikerjakan'
                                                  }">
                                                Status: <span x-text="selectedTask.status_label"></span>
                                            </span>
                                        </div>
                                        <h3 class="text-xl font-black text-slate-900 mt-2 leading-snug" x-text="selectedTask.judul"></h3>
                                    </div>

                                    <button @click="closeTask()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                {{-- Peringatan jika Overdue --}}
                                <template x-if="selectedTask.is_overdue">
                                    <div class="mt-4 p-3 rounded-xl bg-rose-50 border border-rose-200 flex items-center gap-2 text-rose-800 text-xs font-semibold">
                                        <span>⚠️</span>
                                        <span>Tugas ini sudah melewati batas waktu deadline dan belum diselesaikan!</span>
                                    </div>
                                </template>

                                {{-- Detail Klien & Deadline --}}
                                <div class="mt-5 grid grid-cols-2 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                                    <div>
                                        <p class="text-xs text-slate-500 font-semibold uppercase">Deadline</p>
                                        <p class="text-sm font-bold text-slate-800 mt-0.5 flex items-center gap-1.5">
                                            <span>📅</span>
                                            <span x-text="selectedTask.formatted_deadline"></span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500 font-semibold uppercase">Klien / Perusahaan</p>
                                        <p class="text-sm font-bold text-slate-800 mt-0.5 flex items-center gap-1.5">
                                            <span>🏢</span>
                                            <span x-text="selectedTask.nama_klien || 'Tidak ada klien'"></span>
                                        </p>
                                    </div>
                                </div>

                                {{-- Deskripsi Tugas --}}
                                <div class="mt-4">
                                    <p class="text-xs text-slate-500 font-semibold uppercase mb-1.5">Deskripsi / Catatan</p>
                                    <div class="p-3.5 bg-slate-50/70 rounded-2xl border border-slate-200 text-sm text-slate-700 whitespace-pre-line leading-relaxed min-h-[70px]">
                                        <span x-text="selectedTask.deskripsi || 'Tidak ada deskripsi detail untuk tugas ini.'"></span>
                                    </div>
                                </div>

                                {{-- Subtasks / Checklist di Modal Kalender --}}
                                <div class="mt-4">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <p class="text-xs text-slate-500 font-semibold uppercase">Subtasks / Checklist</p>
                                        <template x-if="selectedTask.subtasks_count > 0">
                                            <span class="text-xs font-bold text-indigo-600"
                                                  x-text="`${selectedTask.completed_subtasks_count || 0}/${selectedTask.subtasks_count} Selesai (${selectedTask.subtasks_progress_percentage || 0}%)`"></span>
                                        </template>
                                    </div>
                                    <template x-if="!selectedTask.subtasks || selectedTask.subtasks.length === 0">
                                        <p class="text-xs text-slate-400 italic">Belum ada subtask untuk tugas ini.</p>
                                    </template>
                                    <template x-if="selectedTask.subtasks && selectedTask.subtasks.length > 0">
                                        <div class="space-y-1.5 max-h-36 overflow-y-auto">
                                            <template x-for="st in selectedTask.subtasks" :key="st.id">
                                                <div class="flex items-center gap-2 text-xs p-2 rounded-lg bg-slate-50 border border-slate-200">
                                                    <span x-text="st.is_completed ? '✅' : '⬜'"></span>
                                                    <span :class="st.is_completed ? 'line-through text-slate-400' : 'text-slate-700 font-medium'" x-text="st.judul"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                {{-- Aksi Modal: Edit & Tutup --}}
                                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                                    <a :href="selectedTask.edit_url"
                                       class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span>Edit Tugas Ini</span>
                                    </a>

                                    <button @click="closeTask()" 
                                            class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
