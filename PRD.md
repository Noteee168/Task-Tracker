# Product Requirement Document (PRD) & Dokumentasi Teknis
# Proyek: Task Tracker Application (Pro Edition)

---

## 1. Executive Summary & Visi Produk

**Task Tracker** adalah aplikasi web manajemen tugas, kolaborasi alur kerja, dan pemantauan proyek berbasis Laravel yang dirancang untuk produktivitas modern. Aplikasi ini memfasilitasi pengorganisasian tugas terpadu dari berbagai klien, pelacakan deadline kritis, deteksi keterlambatan (*overdue*), manajemen subtask/checklist hierarkis, sistem peringatan terjadwal via Telegram Bot, serta visualisasi data komprehensif melalui Dasbor Tabel, Papan Kanban (*Drag & Drop*), Kalender Deadline, dan Dasbor Analitik interaktif.

Aplikasi menerapkan **Isolasi Data Pengguna (Personal Workspace Isolation)** di mana setiap pengguna terautentikasi memiliki lingkungan tugas pribadinya secara independen, aman, dan tidak dapat diakses atau diubah oleh pengguna lain.

### 1.1 Masalah yang Diselesaikan
* **Missed Deadlines**: Tugas yang mendekati batas waktu atau terlambat sering luput jika dicatat manual; diselesaikan dengan sistem deteksi real-time, badge selisih hari, dan notifikasi harian Telegram Bot.
* **Kurangnya Visibilitas Klien**: Kesulitan memetakan beban kerja per klien; diselesaikan dengan analitik distribusi klien dan rekapitulasi performa.
* **Alur Kerja Kaku**: Pengguna membutuhkan fleksibilitas dalam melihat tugas; diselesaikan dengan multi-view: Tabel Ringkas, Papan Kanban interaktif dengan drag-and-drop, serta Kalender Bulanan.
* **Tugas Kompleks Tanpa Struktur**: Tugas besar sulit diukur progresnya; diselesaikan dengan modul Subtask / Checklist dengan perhitungan persentase penyelesaian otomatis.
* **Risiko Kehilangan Data**: Penghapusan tugas yang tidak disengaja; dicegah melalui sistem Tong Sampah (*Soft Deletes*) dengan kemampuan pemulihan (*restore*) maupun pembersihan permanen (*force delete*).

---

## 2. Arsitektur Sistem & Spesifikasi Teknologi

| Komponen | Teknologi / Paket | Versi Terpasang | Deskripsi |
|---|---|---|---|
| **Framework Backend** | Laravel Framework | `^13.29.0` (Laravel 12) | PHP 8.3+, Arsitektur MVC, Eloquent ORM, Task Scheduler |
| **Autentikasi** | Laravel Breeze | `^2.4.2` | Autentikasi sesi aman, proteksi CSRF, throttle, isolasi `user_id` |
| **Database** | MySQL / MariaDB (Laragon Stack) | 8.0+ / 10.4+ | Skema relasional teroptimasi dengan index, foreign keys, & soft deletes |
| **Frontend Layout** | Blade Templating + Tailwind CSS | Tailwind `^3.1.0` | Antarmuka responsif, modern, glassmorphism, dan dark-mode aware |
| **Frontend Interactivity** | Alpine.js & Chart.js | Alpine `^3.4.2`, Chart.js (CDN) | Reaktivitas UI ringan, HTML5 Drag & Drop, rendering grafik canvas |
| **Asset Bundler** | Vite + Laravel Vite Plugin | Vite `^8.0.0`, Plugin `^3.1` | Hot-Module Replacement (HMR) dan kompilasi CSS/JS cepat |
| **Notifikasi Eksternal** | Telegram Bot API | Telegram Bot API (HTTP Client) | Notifikasi rekap harian tugas via bot chat instant messaging |
| **Testing Suite** | Pest PHP + Pest Plugin Laravel | Pest `^4.7.8`, Plugin `^4.1.0` | 60 test otomatis (Feature & Unit) dengan 161 assertions |
| **Timezone Standar** | PHP / Laravel Config | `Asia/Jakarta` (WIB) | Penyelarasan waktu server, database, dan cron scheduling |

---

## 3. Struktur Database (Data Model & Schema)

### 3.1 Skema Tabel `tasks`
Migrasi:
* [`2026_08_26_025513_create_tasks_table.php`](file:///c:/laragon/www/task-tracker/database/migrations/2026_08_26_025513_create_tasks_table.php)
* [`2026_10_13_012852_drop_notified_at_from_tasks_table.php`](file:///c:/laragon/www/task-tracker/database/migrations/2026_10_13_012852_drop_notified_at_from_tasks_table.php)
* [`2026_10_13_064254_add_user_id_to_tasks_table.php`](file:///c:/laragon/www/task-tracker/database/migrations/2026_10_13_064254_add_user_id_to_tasks_table.php)
* [`2026_10_13_082846_add_soft_deletes_to_tasks_table.php`](file:///c:/laragon/www/task-tracker/database/migrations/2026_10_13_082846_add_soft_deletes_to_tasks_table.php)

```sql
CREATE TABLE `tasks` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `judul` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT NULL,
  `nama_klien` VARCHAR(255) NULL,
  `prioritas` ENUM('rendah', 'sedang', 'tinggi') DEFAULT 'sedang' NOT NULL,
  `tanggal_deadline` DATE NOT NULL,
  `status` ENUM('belum_dikerjakan', 'sedang_dikerjakan', 'menunggu_antrian', 'selesai') DEFAULT 'belum_dikerjakan' NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);
```

### 3.2 Skema Tabel `subtasks`
Migrasi: [`2026_10_14_062128_create_subtasks_table.php`](file:///c:/laragon/www/task-tracker/database/migrations/2026_10_14_062128_create_subtasks_table.php)

```sql
CREATE TABLE `subtasks` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `task_id` BIGINT UNSIGNED NOT NULL,
  `judul` VARCHAR(255) NOT NULL,
  `is_completed` TINYINT(1) DEFAULT 0 NOT NULL,
  `urutan` INT UNSIGNED DEFAULT 0 NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  INDEX `subtasks_task_id_is_completed_index` (`task_id`, `is_completed`),
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE
);
```

### 3.3 Kamus Data (Data Dictionary)

#### Tabel `tasks`
| Kolom | Tipe Data | Nullable | Default | Fungsi & Catatan Bisnis |
|---|---|---|---|---|
| `id` | Bigint (PK) | No | Auto | ID unik tugas |
| `user_id` | Bigint (FK) | No | - | Referensi pemilik tugas (`users.id`) untuk isolasi multi-user workspace |
| `judul` | Varchar(255) | No | - | Judul atau nama tugas |
| `deskripsi` | Text | Yes | NULL | Penjelasan mendalam mengenai ruang lingkup pengerjaan |
| `nama_klien` | Varchar(255) | Yes | NULL | Nama instansi atau klien pemilik tugas/proyek |
| `prioritas` | Enum | No | `sedang` | Tingkat urgensi: `rendah`, `sedang`, `tinggi` |
| `tanggal_deadline` | Date | No | - | Batas akhir tanggal penyelesaian tugas |
| `status` | Enum | No | `belum_dikerjakan` | Status pengerjaan: `belum_dikerjakan`, `sedang_dikerjakan`, `menunggu_antrian`, `selesai` |
| `created_at` | Timestamp | Yes | NULL | Waktu pembuatan record |
| `updated_at` | Timestamp | Yes | NULL | Waktu pembaruan terakhir record |
| `deleted_at` | Timestamp | Yes | NULL | Waktu penghapusan lunak (*Soft Delete*). Jika tidak null, tugas berada di Tong Sampah |

#### Tabel `subtasks`
| Kolom | Tipe Data | Nullable | Default | Fungsi & Catatan Bisnis |
|---|---|---|---|---|
| `id` | Bigint (PK) | No | Auto | ID unik subtask |
| `task_id` | Bigint (FK) | No | - | Referensi tugas induk (`tasks.id`) |
| `judul` | Varchar(255) | No | - | Rincian langkah/checklist pengerjaan |
| `is_completed` | Boolean | No | `false` (0) | Status centang: `0` (belum selesai), `1` (sudah selesai) |
| `urutan` | Unsigned Int | No | `0` | Urutan penataan checklist |
| `created_at` | Timestamp | Yes | NULL | Waktu pembuatan subtask |
| `updated_at` | Timestamp | Yes | NULL | Waktu perubahan terakhir |

---

## 4. Fitur Utama & Functional Requirements

### 4.1 Modul Dashboard Utama ([`dashboard.blade.php`](file:///c:/laragon/www/task-tracker/resources/views/dashboard.blade.php))
* **Ringkasan Kartu Statistik (KPI Bar)**:
  * Total Tugas Pengguna
  * Belum Mulai (`belum_dikerjakan`)
  * Sedang Dikerjakan (`sedang_dikerjakan`)
  * Menunggu Antrian (`menunggu_antrian`)
  * Selesai (`selesai`)
  * Terlambat / Overdue (`tanggal_deadline < today` & `status != selesai`)
  * Tong Sampah (`onlyTrashed()->count()`)
* **Header Action & Telegram Trigger**:
  * Tombol **"Kirim ke Telegram"**: Memicu pengiriman rekap instan ke Telegram Bot Superadmin.
  * Tombol shortcut **"Tong Sampah"** dengan badge counter tugas terhapus.
  * Tombol **"Tambah Task Baru"**.
* **Banner Peringatan Overdue**: Notifikasi peringatan mencolok dengan animasi ping merah jika terdapat tugas aktif melewati batas deadline.
* **Dual View Switcher (Tabel vs Kanban Board)**:
  * Switcher interaktif berbasis Alpine.js dengan persistensi pilihan di `localStorage` (`task_view_mode`).
* **Papan Kanban Interaktif (Drag & Drop)**:
  * 4 Kolom Status: *Belum Dikerjakan*, *Sedang Dikerjakan*, *Menunggu Antrian*, *Selesai*.
  * Fitur HTML5 drag-and-drop antar kolom yang langsung memperbarui status ke server via API PATCH JSON (`tasks.update-status`).
  * Proteksi integritas status: Tugas yang sudah berstatus 'selesai' tidak dapat di-unsubmit sembarangan melalui tombol cepat (harus melalui formulir Edit).
* **Tabel Tugas Interaktif**:
  * Badge visual status & prioritas Tailwind.
  * Smart Ordering: Tugas terlambat (*overdue*) dan prioritas tinggi diposisikan paling atas, disusul tanggal deadline terdekat.
  * Progress Bar Subtask: Menampilkan rasio checklist yang rampung (misal: 3/5 subtask - 60%).
  * Modal Quick Checklist: Tombol checklist di baris tabel untuk membuka pop-up subtask tanpa meninggalkan dashboard.
  * Paginasi otomatis (15 item) dengan mempertahankan parameter filter (`withQueryString()`).
* **Pencarian & Filter Multi-Kriteria**:
  * Pencarian keyword (judul, klien, deskripsi).
  * Filter status (termasuk filter khusus `overdue`).
  * Filter prioritas (`rendah`, `sedang`, `tinggi`).

### 4.2 Modul Kalender Deadline ([`calendar.blade.php`](file:///c:/laragon/www/task-tracker/resources/views/calendar.blade.php))
* **Tampilan Kalender Bulanan Interaktif**:
  * Navigasi bulan (Maju/Mundur/Hari Ini) dengan rendering tanggal dinamis.
  * Pemetaan tugas ke dalam sel tanggal berdasarkan `tanggal_deadline`.
  * Visual badge tugas berdasarkan prioritas dan indikator overdue.
* **Ringkasan Metrik Kalender**:
  * Total Tugas, Tugas Selesai, Tugas Terlambat, dan Tugas Mendatang.
* **Modal Detail Tugas**:
  * Menampilkan informasi lengkap tugas, klien, deadline, status, dan daftar subtasks saat item kalender diklik.

### 4.3 Modul Subtasks & Checklist ([`SubtaskController.php`](file:///c:/laragon/www/task-tracker/app/Http/Controllers/SubtaskController.php))
* **Dukungan Dua Antarmuka**:
  1. *Dashboard Modal*: Mengelola subtask secara asinkron via AJAX/JSON langsung dari baris tugas di dashboard.
  2. *Halaman Edit Task*: Komponen Checklist & Subtasks terintegrasi di bawah formulir edit tugas ([`tasks/edit.blade.php`](file:///c:/laragon/www/task-tracker/resources/views/tasks/edit.blade.php)).
* **Fitur Checklist**:
  * Tambah subtask baru secara instan.
  * Toggle centang selesai/belum selesai dengan efek visual *line-through*.
  * Hapus subtask dari daftar.
  * Kalkulasi otomatis atribut model:
    * `subtasks_count`: Total subtask.
    * `completed_subtasks_count`: Jumlah subtask selesai.
    * `subtasks_progress_percentage`: Persentase progres penyelesaian (0 - 100%).
* **Isolasi Keamanan**: Pengguna hanya dapat melihat, menambah, mengubah, atau menghapus subtask dari tugas miliknya sendiri (HTTP 403 jika melanggar).

### 4.4 Modul Tong Sampah & Pemulihan (Soft Deletes / Trash) ([`tasks/trash.blade.php`](file:///c:/laragon/www/task-tracker/resources/views/tasks/trash.blade.php))
* **Penghapusan Aman**: Menghapus tugas di dashboard tidak langsung melenyapkan data dari database, melainkan mengisi `deleted_at` (*Soft Delete*).
* **Halaman Tong Sampah (`/tasks/trash`)**:
  * Menampilkan daftar tugas terhapus beserta tanggal penghapusannya.
  * Aksi **Pulihkan (Restore)**: Mengembalikan tugas ke dashboard aktif (`PATCH /tasks/{id}/restore`).
  * Aksi **Hapus Permanen (Force Delete)**: Menghapus permanen baris dari MySQL (`DELETE /tasks/{id}/force-delete`).
  * Aksi **Kosongkan Tong Sampah**: Membersihkan seluruh data terhapus milik pengguna sekaligus (`POST /tasks/trash/empty`).
* **Isolasi Keamanan**: Pemulihan dan force delete divalidasi ketat terhadap kepemilikan `user_id`.

### 4.5 Modul Notifikasi Telegram Bot ([`TelegramNotificationService.php`](file:///c:/laragon/www/task-tracker/app/Services/TelegramNotificationService.php))
* **Layanan Notifikasi Terpadu**:
  * Menggunakan Telegram Bot API (`sendMessage`) dengan format HTML rapi.
  * Mendukung pengaturan SSL Verification bypass (`TELEGRAM_SSL_VERIFY`) untuk kompatibilitas lingkungan lokal (Laragon/cURL Windows).
* **Kategori Rekap Tugas**:
  1. 🚨 **Tugas Overdue**: Tugas belum selesai yang telah melewati batas deadline beserta hitungan selisih hari keterlambatan.
  2. ⏳ **Mendekati Deadline (H-3 s.d. Hari Ini)**: Peringatan dini tugas yang jatuh tempo dalam 3 hari ke depan.
  3. 📝 **Belum Dikerjakan**: Tugas yang statusnya masih belum disentuh.
* **Metode Pemicu (Dual Trigger)**:
  * **Otomatis Terjadwal (Cron Job)**: Dieksekusi setiap hari pukul 08:00 WIB via Laravel Scheduler:
    ```php
    Schedule::command('tasks:notify-telegram')->dailyAt('08:00')->timezone('Asia/Jakarta');
    ```
  * **Pemicu Manual (On-Demand)**:
    * Melalui tombol *"Kirim ke Telegram"* di Dashboard utama (`POST /tasks/notify-telegram`).
    * Melalui Artisan CLI: `php artisan tasks:notify-telegram {--test} {--force}`.

### 4.6 Modul Analitik & Visualisasi Data ([`analytics.blade.php`](file:///c:/laragon/www/task-tracker/resources/views/analytics.blade.php))
* **KPI Health Cards**:
  * Completion Rate (%) dengan dynamic progress bar.
  * Volume tugas aktif on-track vs tugas terlambat.
* **4 Grafik Analitik Interaktif (Chart.js)**:
  1. *Distribusi Status Tugas (Donut Chart)*.
  2. *Sebaran Tingkat Prioritas (Bar Chart)*.
  3. *Beban Waktu Deadline (Bar Chart)*: Kluster Terlambat, Hari Ini, Minggu Ini, Minggu Depan, >2 Minggu.
  4. *Distribusi Beban Klien (Horizontal Multi-Bar)*: Perbandingan total tugas vs selesai per klien.
* **Radar Tugas Mendesak**: Daftar 5 tugas non-selesai teratas dengan batas deadline $\le 3$ hari.
* **Tabel Rekapitulasi Klien**: Metrik lengkap total tugas, rasio selesai, dan tingkat keterlambatan per klien.

---

## 5. Rute & Endpoint Aplikasi

| HTTP Method | URI | Nama Route | Controller & Action | Middleware | Deskripsi |
|---|---|---|---|---|---|
| `GET` | `/` | - | Closure | `web` | Redirect ke `/dashboard` jika login, ke `/login` jika belum |
| `GET` | `/dashboard` | `dashboard` | `TaskController@index` | `auth` | Dashboard utama (Tabel, Kanban, KPI, Telegram button) |
| `GET` | `/calendar` | `calendar` | `TaskController@calendar` | `auth` | Tampilan Kalender Deadline bulanan |
| `GET` | `/analytics` | `analytics` | `TaskController@analytics` | `auth` | Dasbor analitik, KPI, & visualisasi grafik Chart.js |
| `GET` | `/tasks/trash` | `tasks.trash` | `TaskController@trash` | `auth` | Halaman Tong Sampah (*soft-deleted tasks*) |
| `PATCH` | `/tasks/{id}/restore` | `tasks.restore` | `TaskController@restore` | `auth` | Memulihkan tugas dari tong sampah |
| `DELETE` | `/tasks/{id}/force-delete` | `tasks.force-delete` | `TaskController@forceDelete` | `auth` | Menghapus permanen tugas dari database |
| `POST` | `/tasks/trash/empty` | `tasks.empty-trash` | `TaskController@emptyTrash` | `auth` | Mengosongkan seluruh isi tong sampah pengguna |
| `GET` | `/tasks` | `tasks.index` | `TaskController@index` | `auth` | Alias redirect ke dashboard tugas |
| `GET` | `/tasks/create` | `tasks.create` | `TaskController@create` | `auth` | Form tambah tugas baru |
| `POST` | `/tasks` | `tasks.store` | `TaskController@store` | `auth` | Validasi & simpan tugas baru |
| `GET` | `/tasks/{task}/edit` | `tasks.edit` | `TaskController@edit` | `auth` | Form edit tugas + manajemen subtasks |
| `PUT/PATCH` | `/tasks/{task}` | `tasks.update` | `TaskController@update` | `auth` | Validasi & update data tugas |
| `DELETE` | `/tasks/{task}` | `tasks.destroy` | `TaskController@destroy` | `auth` | Pindahkan tugas ke Tong Sampah (Soft Delete) |
| `PATCH` | `/tasks/{task}/status` | `tasks.update-status` | `TaskController@updateStatus` | `auth` | Pembaruan status cepat (Kanban drag-and-drop) |
| `POST` | `/tasks/notify-telegram` | `tasks.notify-telegram` | `TaskController@notifyTelegram` | `auth` | Kirim rekap tugas ke bot Telegram instan |
| `GET` | `/tasks/{task}/subtasks` | `subtasks.index` | `SubtaskController@index` | `auth` | API JSON daftar subtask suatu tugas |
| `POST` | `/tasks/{task}/subtasks` | `subtasks.store` | `SubtaskController@store` | `auth` | Tambah subtask baru ke tugas |
| `PATCH` | `/subtasks/{subtask}/toggle` | `subtasks.toggle` | `SubtaskController@toggle` | `auth` | Toggle centang selesai/belum subtask |
| `DELETE` | `/subtasks/{subtask}` | `subtasks.destroy` | `SubtaskController@destroy` | `auth` | Hapus subtask |
| `GET` | `/profile` | `profile.edit` | `ProfileController@edit` | `auth` | Halaman pengaturan profil pengguna |
| `PATCH` | `/profile` | `profile.update` | `ProfileController@update` | `auth` | Update profil pengguna |
| `DELETE` | `/profile` | `profile.destroy` | `ProfileController@destroy` | `auth` | Hapus akun pengguna |

---

## 6. Model & Business Logic

### 6.1 Model `Task` ([`app/Models/Task.php`](file:///c:/laragon/www/task-tracker/app/Models/Task.php))
* **Traits**: `HasFactory`, `SoftDeletes`.
* **Relasi**:
  * `user(): BelongsTo`: Relasi kepemilikan ke model `User`.
  * `subtasks(): HasMany`: Relasi ke koleksi `Subtask` yang diurutkan berdasarkan `urutan` dan `id`.
* **Accessors & Appends**:
  * `subtasks_count`: Total jumlah subtask.
  * `completed_subtasks_count`: Jumlah subtask yang telah selesai dicentang.
  * `subtasks_progress_percentage`: Rasio persentase penyelesaian subtask ($0 - 100\%$).
  * `isOverdue(): bool`: Memeriksa apakah deadline $<$ hari ini dan status $\neq$ 'selesai'.
  * `getDaysOverdueAttribute(): int`: Menghitung selisih hari keterlambatan.
  * `getStatusLabelAttribute()` & `getStatusBadgeClassAttribute()`.
  * `getPrioritasLabelAttribute()` & `getPrioritasBadgeClassAttribute()`.
* **Query Scopes**:
  * `scopeOverdue($query)`: Filter tugas yang melewati deadline dan belum selesai.

### 6.2 Model `Subtask` ([`app/Models/Subtask.php`](file:///c:/laragon/www/task-tracker/app/Models/Subtask.php))
* **Relasi**:
  * `task(): BelongsTo`: Relasi ke tugas induk (`Task`).
* **Casts**:
  * `is_completed` $\rightarrow$ `boolean`.
  * `urutan` $\rightarrow$ `integer`.

### 6.3 Model `User` ([`app/Models/User.php`](file:///c:/laragon/www/task-tracker/app/Models/User.php))
* **Relasi**:
  * `tasks(): HasMany`: Koleksi tugas yang dimiliki pengguna untuk menjamin pemisahan data antar pengguna (*multi-tenancy workspace*).

---

## 7. Pengujian & Jaminan Kualitas (QA & Testing)

Aplikasi memiliki **60 automated tests** (161 assertions) yang dibangun di atas framework **Pest PHP** dan seluruhnya lulus verifikasi:

```bash
php artisan test --compact
# {"tool":"pest","result":"passed","tests":60,"passed":60,"assertions":161}
```

### 7.1 Cakupan Uji Fitur Utama
1. **[`TaskTrackerTest.php`](file:///c:/laragon/www/task-tracker/tests/Feature/TaskTrackerTest.php)**:
   * *Isolasi Data Pengguna*: Memastikan pengguna hanya melihat tugas miliknya di dashboard dan tidak bisa melihat tugas milik orang lain.
   * *Otorisasi CRUD*: Mencegah pengeditan, pengubahan, atau penghapusan tugas milik pengguna lain (HTTP 403 Forbidden).
   * *Kanban Board & Quick Status*: Verifikasi rendering elemen papan kanban, pembaruan status via API JSON, dan proteksi unsubmit tugas berstatus 'selesai'.
   * *Kalender Deadline*: Verifikasi aksesibilitas kalender dan penyaringan tugas eksklusif per pengguna.
   * *Soft Deletes & Trash Recovery*: Menguji mekanisme soft-delete, halaman tong sampah, pemulihan (*restore*), hapus permanen (*force delete*), dan pengosongan tong sampah massal (*empty trash*).
2. **[`SubtaskTest.php`](file:///c:/laragon/www/task-tracker/tests/Feature/SubtaskTest.php)**:
   * Verifikasi API index subtask dan proteksi akses tugas pengguna lain.
   * Pengujian validasi penambahan subtask baru.
   * Pengujian toggle status centang (selesai/belum selesai).
   * Pengujian penghapusan subtask.
   * Pengujian kalkulasi akurat persentase progres (`subtasks_progress_percentage`).
3. **[`TelegramNotificationTest.php`](file:///c:/laragon/www/task-tracker/tests/Feature/TelegramNotificationTest.php)**:
   * Pengujian kategorisasi tugas reminder (overdue, H-3 deadline, dan belum dikerjakan).
   * Pengujian HTTP Mocking Telegram API (`api.telegram.org`) saat notifikasi dikirim.
   * Pengujian eksekusi Artisan Command `tasks:notify-telegram`.
   * Pengujian web trigger route `/tasks/notify-telegram`.
4. **Auth & Profile Tests** ([`tests/Feature/Auth/*`](file:///c:/laragon/www/task-tracker/tests/Feature/Auth)):
   * Pendaftaran akun, login, logout, reset password, dan pembaruan profil pengguna.

---

## 8. Panduan Konfigurasi & Environment

### 8.1 Persyaratan Sistem
* PHP 8.3+ (Ekstensi: `pdo_mysql`, `curl`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`).
* Composer 2.x
* Node.js 18+ & NPM
* Database MySQL / MariaDB (Laragon Stack)

### 8.2 Konfigurasi File `.env`

Pastikan variabel-variabel penting berikut terkonfigurasi di file `.env`:

```env
APP_NAME="Task Tracker"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://task-tracker.test

# Database MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_tracker
DB_USERNAME=root
DB_PASSWORD=

# Integrasi Telegram Bot
TELEGRAM_BOT_TOKEN="your_bot_token_here"
TELEGRAM_CHAT_ID="your_chat_id_here"
TELEGRAM_SSL_VERIFY=false
```

> [!NOTE]
> Parameter `TELEGRAM_SSL_VERIFY=false` berguna untuk mencegah kendala sertifikat cURL CA pada instalasi lokal Windows / Laragon saat menghubungi `api.telegram.org`.

### 8.3 Perintah Artisan Terkait
```bash
# Uji coba koneksi bot Telegram
php artisan tasks:notify-telegram --test

# Jalankan pengiriman rekap tugas ke Telegram secara manual (force kirim meskipun kosong)
php artisan tasks:notify-telegram --force

# Menjalankan scheduler Laravel secara lokal untuk pengujian cron
php artisan schedule:work

# Menjalankan seluruh test suite Pest
php artisan test --compact
```

### 8.4 Penjadwalan Cron Job Server
Di environment server production, pasang satu baris crontab berikut:
```cron
* * * * * cd /path-to-task-tracker && php artisan schedule:run >> /dev/null 2>&1
```
Laravel akan otomatis mengeksekusi pengingat Telegram setiap hari pada pukul **08:00 WIB**.

---

## 9. Rencana Pengembangan Selanjutnya (Roadmap)

### Fitur yang Telah Selesai (Completed)
* [x] **Multi-User Workspace & Data Isolation**: Setiap pengguna mengelola tugas pribadinya secara terisolasi dan aman.
* [x] **Papan Kanban Interaktif**: Alur kerja visual drag-and-drop untuk memperbarui status pengerjaan secara real-time.
* [x] **Manajemen Subtasks & Checklist**: Pemecahan tugas menjadi subtugas terukur dengan progres persentase otomatis.
* [x] **Kalender Deadline Visual**: Penjadwalan bulanan interaktif untuk memantau tugas yang jatuh tempo.
* [x] **Tong Sampah & Pemulihan (Soft Deletes)**: Mekanisme pemulihan data tugas yang tidak sengaja terhapus.
* [x] **Integrasi Notifikasi Telegram Bot**: Rekap harian otomatis jam 08:00 WIB dan tombol manual di dasbor.
* [x] **Dasbor Analitik & Grafik KPI**: Visualisasi distribusi status, prioritas, waktu, dan klien via Chart.js.

### Rencana Selanjutnya (Future Iterations)
* [ ] **Ekspor Laporan (PDF & Excel/CSV)**: Fasilitas unduh rekapitulasi tugas dan performa klien dalam format PDF dan spreadsheet.
* [ ] **Kolaborasi Tim (Team Task Sharing)**: Kemampuan membagikan atau menugaskan tugas ke anggota tim lain dengan hak akses *read-only* atau *editor*.
* [ ] **Activity Log / Riwayat Perubahan**: Pencatatan histori perubahan status, deadline, dan aktivitas subtask.
* [ ] **Integrasi Notifikasi WhatsApp Bot**: Saluran alternatif pengiriman pengingat via WhatsApp Gateway.
