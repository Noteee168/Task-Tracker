# Product Requirement Document (PRD) & Dokumentasi Teknis
# Proyek: Task Tracker Application

---

## 1. Executive Summary & Visi Produk

**Task Tracker** adalah aplikasi web manajemen tugas dan pemantauan proyek internal berbasis Laravel yang dirancang untuk **Superadmin**. Aplikasi ini memfasilitasi pengorganisasian tugas terpadu dari berbagai klien, pelacakan deadline kritis, deteksi otomatis keterlambatan (overdue), pengiriman notifikasi email peringatan, serta visualisasi beban kerja melalui dasbor analitik interaktif.

### 1.1 Masalah yang Diselesaikan
* **Missed Deadlines**: Tugas yang terlambat sering kali luput dari pantauan jika hanya dicatat manual.
* **Kurangnya Visibilitas Klien**: Kesulitan memetakan beban kerja dan distribusi tugas per klien.
* **Pencegahan Keterlambatan Proaktif**: Belum tersedianya sistem peringatan ganda (real-time saat login + cron job terjadwal).
* **Kebutuhan Analitik Real-Time**: Manajemen membutuhkan visualisasi KPI, tingkat penyelesaian (*completion rate*), dan beban waktu tanpa perlu ekspor spreadsheet manual.

---

## 2. Arsitektur Sistem & Spesifikasi Teknologi

| Komponen | Teknologi / Paket | Deskripsi |
|---|---|---|
| **Framework Backend** | Laravel Framework 11/12 (`^13.17`) | PHP 8.3+, Arsitektur MVC, Eloquent ORM |
| **Autentikasi** | Laravel Breeze (`^2.4`) | Autentikasi berbasis sesi aman, proteksi CSRF, throttle |
| **Database** | MySQL / MariaDB (Laragon Stack) | Skema relasional teroptimasi dengan index & query scopes |
| **Frontend Layout** | Blade Templating + Tailwind CSS | Antarmuka responsif, modern, clean, dan dark-mode aware |
| **Frontend Interactivity** | Alpine.js (`^3.4`) & Chart.js (CDN) | Reaktivitas UI ringan dan rendering grafik canvas interaktif |
| **Asset Bundler** | Vite (`^8.0`) + Laravel Vite Plugin | Hot-module replacement dan kompilasi CSS/JS cepat |
| **Testing Suite** | Pest PHP (`^4.7`) + Pest Plugin Laravel | Unit & Feature tests otomatis |

---

## 3. Struktur Database (Data Model & Schema)

### 3.1 Skema Tabel `tasks`
Migrasi: [`2026_08_26_025513_create_tasks_table.php`](file:///c:/laragon/www/task-tracker/database/migrations/2026_08_26_025513_create_tasks_table.php)

```sql
CREATE TABLE `tasks` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT NULL,
  `nama_klien` VARCHAR(255) NULL,
  `prioritas` ENUM('rendah', 'sedang', 'tinggi') DEFAULT 'sedang' NOT NULL,
  `tanggal_deadline` DATE NOT NULL,
  `status` ENUM('belum_dikerjakan', 'sedang_dikerjakan', 'menunggu_antrian', 'selesai') DEFAULT 'belum_dikerjakan' NOT NULL,
  `notified_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
);
```

### 3.2 Detail Kamus Data (Data Dictionary)

| Kolom | Tipe Data | Nullable | Default | Fungsi & Catatan Bisnis |
|---|---|---|---|---|
| `id` | Bigint (PK) | No | Auto | ID unik tugas |
| `judul` | Varchar(255) | No | - | Judul atau nama tugas |
| `deskripsi` | Text | Yes | NULL | Penjelasan mendalam mengenai ruang lingkup tugas |
| `nama_klien` | Varchar(255) | Yes | NULL | Nama instansi atau klien pemilik tugas/proyek |
| `prioritas` | Enum | No | `sedang` | Tingkat urgensi: `rendah`, `sedang`, `tinggi` |
| `tanggal_deadline` | Date | No | - | Tanggal batas akhir penyelesaian tugas |
| `status` | Enum | No | `belum_dikerjakan` | Status pengerjaan: `belum_dikerjakan`, `sedang_dikerjakan`, `menunggu_antrian`, `selesai` |
| `notified_at` | Timestamp | Yes | NULL | Waktu pengiriman email peringatan. Digunakan sebagai flag idempotensi agar email tidak terkirim ganda |
| `created_at` | Timestamp | Yes | NULL | Timestamp pembuatan record |
| `updated_at` | Timestamp | Yes | NULL | Timestamp modifikasi terakhir |

---

## 4. Fitur Utama & Functional Requirements

### 4.1 Modul Dashboard Utama ([`dashboard.blade.php`](file:///c:/laragon/www/task-tracker/resources/views/dashboard.blade.php))
* **Ringkasan Kartu Statistik (KPI Bar)**:
  * Total Tugas
  * Belum Mulai (`belum_dikerjakan`)
  * Sedang Dikerjakan (`sedang_dikerjakan`)
  * Menunggu Antrian (`menunggu_antrian`)
  * Selesai (`selesai`)
  * Terlambat / Overdue (`tanggal_deadline < today` & `status != selesai`)
* **Banner Peringatan Overdue**: Muncul secara dinamis dan mencolok dengan animasi ping merah saat ada tugas yang melewati deadline. Dilengkapi tombol direct shortcut untuk memfilter tugas terlambat.
* **Tabel Tugas Interaktif**:
  * Badge visual status dan prioritas dengan styling Tailwind khusus.
  * Indikator selisih hari keterlambatan (`getDaysOverdueAttribute()`).
  * Urutan prioritas cerdas (*Smart Ordering*): Tugas overdue dan belum selesai diprioritaskan di paling atas, disusul tanggal deadline terdekat.
  * Paginasi otomatis (15 item per halaman) dengan mempertahankan query filter (`withQueryString()`).
* **Sistem Pencarian & Filter Multi-Kriteria**:
  * Pencarian instan berdasarkan judul, nama klien, atau deskripsi tugas.
  * Filter berdasarkan status (termasuk filter khusus `overdue`).
  * Filter berdasarkan tingkat prioritas (`rendah`, `sedang`, `tinggi`).

### 4.2 Modul Analitik & Visualisasi ([`analytics.blade.php`](file:///c:/laragon/www/task-tracker/resources/views/analytics.blade.php))
* **KPI Metrics Card**:
  * **Tingkat Penyelesaian (*Completion Rate*)**: Dihitung dalam persentase dengan progress bar dinamis.
  * **Tugas On-Track**: Menampilkan volume tugas aktif yang masih berada dalam batas toleransi deadline.
  * **Tugas Terlambat**: Angka absolut tugas yang butuh penanganan darurat.
* **Visualisasi Grafik (Chart.js Integration)**:
  1. *Distribusi Status Tugas (Donut Chart)*: Proporsi perbandingan antarfase status.
  2. *Sebaran Tingkat Prioritas (Bar Chart)*: Volume tugas tinggi, sedang, dan rendah.
  3. *Beban Waktu Deadline (Bar Chart)*: Kluster waktu penyelesaian (Terlambat, Hari Ini, Minggu Ini, Minggu Depan, >2 Minggu).
  4. *Distribusi Beban Klien (Horizontal Multi-Bar)*: Membandingkan total tugas vs tugas selesai untuk masing-masing klien utama.
* **Radar Tugas Mendesak**: Menampilkan 5 tugas non-selesai dengan batas deadline $\le 3$ hari ke depan.
* **Tabel Rekapitulasi Klien**: Menampilkan total tugas, rasio selesai, dan tingkat keterlambatan per klien.

### 4.3 Modul Manajemen Tugas (CRUD Lengkap)
* **Tambah Tugas Baru ([`tasks.create`](file:///c:/laragon/www/task-tracker/resources/views/tasks/create.blade.php))**:
  * Validasi form lengkap di backend.
  * Default prioritas 'sedang' dan status 'belum_dikerjakan'.
* **Ubah Data Tugas ([`tasks.edit`](file:///c:/laragon/www/task-tracker/resources/views/tasks/edit.blade.php))**:
  * Modifikasi judul, klien, deskripsi, prioritas, deadline, dan status.
* **Hapus Tugas**:
  * Konfirmasi modal/dialog sebelum penghapusan permanen dari database.

### 4.4 Pelacakan Tugas Terlambat (Overdue Tracking)
* **Peringatan Visual**: Banner peringatan otomatis di dasbor jika ada tugas belum selesai yang melewati batas deadline.
* **Filter Khusus Overdue**: Filter cepat untuk melihat tugas terlambat dan badge penanda jumlah hari keterlambatan.

---

## 5. Rute & Endpoint Aplikasi

| HTTP Method | URI | Nama Route | Controller & Action | Middleware | Deskripsi |
|---|---|---|---|---|---|
| `GET` | `/` | - | Closure | `web` | Landing page atau redirect ke `/dashboard` jika sudah login |
| `GET` | `/dashboard` | `dashboard` | `TaskController@index` | `auth` | Dashboard utama, KPI summary, tabel tugas |
| `GET` | `/analytics` | `analytics` | `TaskController@analytics` | `auth` | Dashboard analitik grafik & visualisasi data |
| `GET` | `/tasks` | `tasks.index` | `TaskController@index` | `auth` | Alias redirect ke dashboard tugas |
| `GET` | `/tasks/create` | `tasks.create` | `TaskController@create` | `auth` | Form tambah tugas baru |
| `POST` | `/tasks` | `tasks.store` | `TaskController@store` | `auth` | Validasi & persistensi tugas baru |
| `GET` | `/tasks/{task}/edit` | `tasks.edit` | `TaskController@edit` | `auth` | Form edit tugas |
| `PUT/PATCH` | `/tasks/{task}` | `tasks.update` | `TaskController@update` | `auth` | Validasi & update tugas |
| `DELETE` | `/tasks/{task}` | `tasks.destroy` | `TaskController@destroy` | `auth` | Menghapus tugas |
| `GET` | `/profile` | `profile.edit` | `ProfileController@edit` | `auth` | Edit profil user |
| `PATCH` | `/profile` | `profile.update` | `ProfileController@update` | `auth` | Simpan perubahan profil |
| `DELETE` | `/profile` | `profile.destroy` | `ProfileController@destroy` | `auth` | Hapus akun |

---

## 6. Model & Business Logic ([`Task.php`](file:///c:/laragon/www/task-tracker/app/Models/Task.php))

### 6.1 Scopes & Helpers Utama
* **`isOverdue(): bool`**: Menentukan apakah sebuah tugas terlambat (status $\neq$ 'selesai' dan deadline $<$ hari ini).
* **`getDaysOverdueAttribute(): int`**: Menghitung selisih hari keterlambatan terhadap tanggal hari ini.
* **`scopeOverdue($query)`**: Filter query Eloquent: `whereDate('tanggal_deadline', '<', today())->where('status', '!=', 'selesai')`.
* **Accessors Label & Badge**:
  * `getStatusLabelAttribute()` & `getStatusBadgeClassAttribute()`
  * `getPrioritasLabelAttribute()` & `getPrioritasBadgeClassAttribute()`

---

## 7. Pengujian & Jaminan Kualitas (QA & Testing)

Pengujian diimplementasikan menggunakan framework **Pest PHP** pada file [`tests/Feature/TaskTrackerTest.php`](file:///c:/laragon/www/task-tracker/tests/Feature/TaskTrackerTest.php):

1. `dashboard can be rendered for authenticated user`: Memastikan dashboard berhasil diakses dengan kode HTTP 200.
2. `superadmin can create a new task`: Memvalidasi operasi simpan tugas baru ke database.
3. `superadmin can edit and update task status`: Memvalidasi operasi update data dan perubahan status.
4. `superadmin can delete a task`: Memvalidasi penghapusan record tugas.
5. `dashboard renders overdue tasks without sending email notification`: Memvalidasi peringatan visual tugas overdue di dasbor tanpa pengiriman email.
6. `analytics page can be rendered with chart datasets for authenticated user`: Memvalidasi kalkulasi metrik analitik dan rendering view grafik.

---

## 8. Panduan Menjalankan & Konfigurasi Sistem

### 8.1 Persyaratan Sistem
* PHP 8.3 atau lebih baru (ekstensi: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, curl).
* Composer 2.x
* Node.js 18+ & NPM
* Database MySQL / MariaDB

### 8.2 Langkah Instalasi
```bash
# 1. Clone repository & masuk ke direktori
cd c:/laragon/www/task-tracker

# 2. Instalasi dependensi PHP & Node
composer install
npm install

# 3. Konfigurasi Environment
cp .env.example .env
php artisan key:generate

# 4. Migrasi Database
php artisan migrate

# 5. Build Asset Frontend
npm run build
# Atau untuk mode development:
npm run dev

# 6. Menjalankan Server Aplikasi
php artisan serve
```

### 8.3 Konfigurasi Mail & Cron Job
Pastikan konfigurasi SMTP di file `.env` telah disesuaikan:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="admin@tasktracker.com"
MAIL_FROM_NAME="Task Tracker System"
```

Untuk menjalankan Task Scheduler di environment server/production, tambahkan satu baris cron berikut di server OS:
```cron
* * * * * cd /path-to-task-tracker && php artisan schedule:run >> /dev/null 2>&1
```

---

## 9. Rencana Pengembangan Selanjutnya (Roadmap)

* [ ] **Multi-User Assignment**: Penugasan tugas ke banyak staf/anggota tim dengan role granular.
* [ ] **Activity Log / Audit Trail**: Pencatatan riwayat perubahan status tugas (siapa yang mengubah dan kapan).
* [ ] **Export Laporan**: Fasilitas unduh laporan tugas dalam format PDF dan Excel/CSV pada menu analitik.
* [ ] **Integrasi Notifikasi WhatsApp/Telegram**: Notifikasi keterlambatan langsung via webhook bot pesan instan selain email.
