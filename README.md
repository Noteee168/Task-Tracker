# Task Tracker

Aplikasi web untuk membantu satu pengelola di kantor mencatat dan memantau tugas dari berbagai klien. Aplikasi menyediakan daftar tugas, Kanban, kalender deadline, analitik, checklist, Tong Sampah, dan pengingat Telegram.

## Alur penggunaan

1. Pengelola masuk ke akun aplikasi.
2. Pengelola menambahkan tugas dengan judul, klien (opsional), prioritas, dan deadline.
3. Tugas dipantau dari Dashboard, Kalender, atau Analitik.
4. Status tugas diperbarui dari tabel atau dengan memindahkan kartu di Kanban. Checklist dapat dipakai untuk merinci pekerjaan.
5. Tugas yang dihapus masuk ke Tong Sampah dan dapat dipulihkan atau dihapus permanen.
6. Jika Telegram dikonfigurasi, aplikasi dapat mengirim ringkasan tugas secara manual atau terjadwal setiap hari pukul 08.00 WIB.

## Persyaratan

- PHP 8.3 atau lebih baru dengan ekstensi yang dibutuhkan Laravel, termasuk PDO untuk database yang dipilih dan cURL untuk Telegram.
- Composer 2.
- Node.js dan npm.
- MySQL/MariaDB atau SQLite.

## Menyiapkan aplikasi

1. Pasang dependency PHP dan JavaScript:

   ```bash
   composer install
   npm install
   ```

2. Salin `.env.example` menjadi `.env`, lalu atur nama aplikasi, URL, dan koneksi database. Contoh konfigurasi MySQL untuk Laragon:

   ```env
   APP_NAME="Task Tracker"
   APP_ENV=local
   APP_KEY=
   APP_DEBUG=true
   APP_URL=http://task-tracker.test
   APP_TIMEZONE=Asia/Jakarta

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=task_tracker
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Buat database `task_tracker` terlebih dahulu di MySQL. Sesuaikan nama database dan kredensial dengan komputer kantor.

3. Buat application key dan jalankan migrasi:

   ```bash
   php artisan key:generate
   php artisan migrate
   ```

4. Bangun aset frontend:

   ```bash
   npm run build
   ```

5. Jalankan aplikasi secara lokal:

   ```bash
   php artisan serve
   ```

   Buka alamat yang ditampilkan Artisan, biasanya `http://127.0.0.1:8000`. Di Laragon, aplikasi juga dapat dibuka melalui domain lokal yang sudah dikonfigurasi.

## Akun pengelola

Aplikasi memakai autentikasi Laravel Breeze. Buat akun pengelola melalui halaman pendaftaran yang tersedia saat penyiapan, lalu gunakan akun tersebut untuk masuk. Karena aplikasi ini ditujukan untuk satu pengelola pada satu komputer kantor, jangan bagikan akun atau buka pendaftaran ke pengguna yang tidak berwenang.

> Catatan: Pendaftaran hanya dibuka saat penyiapan awal ketika belum ada akun pengelola. Setelah akun dibuat, pendaftaran otomatis ditutup dan tugas terpusat untuk akun pengelola tersebut.

## Pengingat Telegram (opsional)

Isi variabel berikut di `.env` untuk mengirim notifikasi ke satu chat Telegram milik pengelola:

```env
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=
TELEGRAM_SSL_VERIFY=false
```

Jangan bagikan token bot. Untuk mengetes konfigurasi dan mengirim ringkasan manual:

```bash
php artisan tasks:notify-telegram --test
php artisan tasks:notify-telegram --force
```

Tombol pengiriman manual juga tersedia di Dashboard. Untuk menjalankan jadwal harian pukul 08.00 WIB di komputer kantor, pastikan scheduler Laravel berjalan terus-menerus:

```bash
php artisan schedule:work
```

Jika komputer mati atau scheduler tidak berjalan pada waktunya, pengingat terjadwal tidak akan terkirim saat itu. Pengiriman Telegram memerlukan koneksi internet.

## Fitur

- Dashboard tabel dan Kanban dengan pencarian, filter, statistik, serta pembaruan status.
- Checklist/subtask untuk merinci pekerjaan.
- Kalender deadline dan analitik tugas per klien.
- Tong Sampah dengan pemulihan dan penghapusan permanen.
- Notifikasi Telegram manual dan terjadwal.
- Login, logout, dan pengaturan akun pengelola.

## Pengembangan

Jalankan Vite dalam mode development agar perubahan frontend langsung terlihat:

```bash
npm run dev
```

Jalankan pengujian:

```bash
php artisan test --compact
```
