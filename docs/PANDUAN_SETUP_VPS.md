# 🚀 Panduan Lengkap Instalasi & Konfigurasi di aaPanel Hosting
## Web App Erlangga Net Management System (`app-erlangga-net`)

Dokumentasi ini disusun khusus dan presisi untuk panel hosting **aaPanel** (Linux Nginx / OpenResty), mulai dari instalasi environment di App Store, penyesuaian PHP, konfigurasi website, Cron Job, hingga Supervisor Daemon Worker.

---

## 📑 Daftar Isi
1. [Langkah 1: Instalasi Paket Environment di App Store aaPanel](#1-langkah-1-instalasi-paket-environment-di-app-store-aapanel)
2. [Langkah 2: Penyesuaian PHP 8.2 (Ekstensi & Disabled Functions)](#2-langkah-2-penyesuaian-php-82-ekstensi--disabled-functions)
3. [Langkah 3: Pembuatan Website di aaPanel](#3-langkah-3-pembuatan-website-di-aapanel)
4. [Langkah 4: Setup Direktori & Deploy Kode via Terminal aaPanel](#4-langkah-4-setup-direktori--deploy-kode-via-terminal-aapanel)
5. [Langkah 5: Konfigurasi File Environment (.env)](#5-langkah-5-konfigurasi-file-environment-env)
6. [Langkah 6: Migrasi Database & Setup FreeRADIUS](#6-langkah-6-migrasi-database--setup-freeradius)
7. [Langkah 7: Konfigurasi Nginx (Running Dir, URL Rewrite, Timeout)](#7-langkah-7-konfigurasi-nginx-running-dir-url-rewrite-timeout)
8. [Langkah 8: Konfigurasi Cron Job di Menu Cron aaPanel](#8-langkah-8-konfigurasi-cron-job-di-menu-cron-aapanel)
9. [Langkah 9: Konfigurasi Queue Worker via Supervisor Manager](#9-langkah-9-konfigurasi-queue-worker-via-supervisor-manager)
10. [Langkah 10: Prosedur Pembaruan Rutin (Git Pull)](#10-langkah-10-prosedur-pembaruan-rutin-git-pull)
11. [Troubleshooting Khusus aaPanel](#11-troubleshooting-khusus-aapanel)

---

## 1. Langkah 1: Instalasi Paket Environment di App Store aaPanel

Buka dashboard aaPanel Anda, masuk ke menu **App Store**, lalu pastikan paket-paket berikut sudah terinstal:

| Software / Plugin | Versi Rekomendasi | Keterangan |
|---|---|---|
| **Nginx** (atau OpenResty) | Nginx 1.22+ / OpenResty 1.21+ | Web Server utama |
| **PHP** | **PHP-8.2** (atau PHP-8.3) | Engine utama Laravel 12 |
| **MySQL** | MySQL 8.0 / MariaDB 10.6+ | Database server |
| **Redis** | Redis 7.x | Caching berkecepatan tinggi |
| **Node.js Version Manager** | Versi terbaru | Digunakan untuk build asset Tailwind/Vite (Pilih Node v20 LTS) |
| **Supervisor Manager** | Versi terbaru | Menjalankan background queue worker push notification |

---

## 2. Langkah 2: Penyesuaian PHP 8.2 (Ekstensi & Disabled Functions)

> [!IMPORTANT]
> **WAJIB DILAKUKAN DI AAPANEL:**  
> Secara default, aaPanel mematikan beberapa fungsi penting PHP yang dibutuhkan Laravel (`symlink`, `putenv`, `proc_open`). Jika tidak dihapus dari daftar blokir, instalasi Composer dan upload file akan gagal!

Buka menu **App Store** -> Cari **PHP-8.2** -> Klik **Setting**:

### A. Tab "Install extensions"
Instal ekstensi berikut dengan mengklik tombol **Install** di sampingnya:
* `fileinfo` (Wajib untuk validasi upload foto bukti instalasi)
* `redis` (Wajib untuk Redis cache)
* `opcache` (Optimasi performa eksekusi script)
* `exif` (Membaca metadata foto kamera teknisi)

### B. Tab "Disabled functions" (SANGAT PENTING)
Cari dan **HAPUS (Delete)** fungsi-fungsi berikut dari daftar blokir:
* `putenv`
* `proc_open`
* `proc_get_status`
* `symlink`
* `pcntl_signal`
* `exec`

### C. Tab "Configuration"
Sesuaikan batasan upload dan waktu eksekusi agar export laporan/PDF tidak timeout:
* `max_execution_time`: **300**
* `max_input_time`: **300**
* `memory_limit`: **512M**
* `post_max_size`: **50M**
* `upload_max_filesize`: **50M**

Setelah selesai, klik tab **Service** -> Klik **Restart PHP**.

---

## 3. Langkah 3: Pembuatan Website di aaPanel

1. Buka menu **Website** -> Klik tombol **Add site**.
2. Isi formulir pembuatan website:
   * **Domain:** `stage.erlangga.net` (sesuaikan dengan domain/subdomain Anda).
   * **Description:** Erlangga Net Management System.
   * **Root directory:** `/www/wwwroot/stage.erlangga.net`.
   * **FTP:** Do not create.
   * **Database:** Pilih **MySQL** -> buat database baru (misal `erlangga_billing`). Catat username dan password yang digenerate oleh aaPanel.
   * **PHP Version:** Pilih **PHP-82**.
3. Klik **Submit**.

---

## 4. Langkah 4: Setup Direktori & Deploy Kode via Terminal aaPanel

Buka menu **Terminal** di sidebar kiri aaPanel, lalu jalankan perintah berikut secara berurutan:

```bash
# 1. Pindah ke direktori website
cd /www/wwwroot/stage.erlangga.net

# 2. Hapus file default bawaan aaPanel (404.html, index.html)
rm -rf * .user.ini

# 3. Clone repositori ke dalam folder website
git clone https://github.com/muhfihakim/app-erlangga-net.git .

# 4. Salin file environment .env
cp .env.example .env

# 5. Install dependensi composer (Gunakan path PHP 8.2 aaPanel)
/www/server/php/82/bin/php /usr/bin/composer install --no-dev --optimize-autoloader

# 6. Install dependensi Node.js & Build Asset frontend
npm install
npm run build

# 7. Generate Application Key Laravel
/www/server/php/82/bin/php artisan key:generate

# 8. Buat symlink storage publik
/www/server/php/82/bin/php artisan storage:link

# 9. Atur kepemilikan hak akses (User aaPanel adalah www:www)
chown -R www:www /www/wwwroot/stage.erlangga.net
chmod -R 775 storage bootstrap/cache

# 10. Kunci hak akses file .env demi keamanan maksimal
chmod 640 .env
chown www:www .env
```

---

## 5. Langkah 5: Konfigurasi File Environment (`.env`)

Buka menu **Files** di aaPanel -> Masuk ke `/www/wwwroot/stage.erlangga.net` -> Klik ganda file `.env` untuk mengeditnya (atau edit via Terminal dengan `nano .env`).

Sesuaikan konfigurasi berikut:

```ini
APP_NAME="ERLANGGA.NET"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://stage.erlangga.net

# 1. DATABASE UTAMA (Sesuai database yang dibuat di Langkah 3)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erlangga_billing
DB_USERNAME=erlangga_billing_user
DB_PASSWORD=PasswordDatabaseAaPanelAnda

# 2. DATABASE FREERADIUS (Koneksi Kedua)
# Jika FreeRADIUS ada di VPS ini, isi database radius lokal
# Jika FreeRADIUS di server terpisah, isi IP Publik server FreeRADIUS
DB_RADIUS_HOST=127.0.0.1
DB_RADIUS_PORT=3306
DB_RADIUS_DATABASE=radius
DB_RADIUS_USERNAME=radius_user
DB_RADIUS_PASSWORD=PasswordRadiusAnda

# 3. CACHE & REDIS
CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
QUEUE_CONNECTION=database

# 4. PENYIMPANAN FOTO BUKTI (S3 / MinIO)
FILESYSTEM_DISK=s3
S3_ACCESS_KEY_ID=access_key_anda
S3_SECRET_ACCESS_KEY=secret_key_anda
S3_DEFAULT_REGION=us-east-1
S3_BUCKET=erlangga-storage
S3_ENDPOINT=https://s3.mybbs.id
S3_USE_PATH_STYLE_ENDPOINT=true
S3_URL=https://s3.mybbs.id/erlangga-storage

# 5. MIKROTIK ROUTEROS API
MIKROTIK_HOST=192.168.10.1
MIKROTIK_PORT=8728
MIKROTIK_USER=api_billing
MIKROTIK_PASS=PasswordMikrotikAnda
MIKROTIK_INTERFACE=ether1

# 6. OLT ZTE MONITORING
OLT_HOST=10.10.10.2
OLT_PORT=23
OLT_USERNAME=zte_admin
OLT_PASSWORD=zte_password

# 7. GENIEACS TR-069
GENIEACS_URL=http://127.0.0.1:3000
GENIEACS_USERNAME=admin
GENIEACS_PASSWORD=admin

# 8. PAYMENT GATEWAY XENDIT
XENDIT_SECRET_KEY=xnd_production_...
XENDIT_WEBHOOK_TOKEN=token_webhook_anda
```

Klik **Save** untuk menyimpan perubahan `.env`.

> [!TIP]
> **Arsitektur Keamanan Kredensial (.env vs Pengaturan Database):**
> 1. **Kredensial Keuangan (Xendit Secret Key & Token):** Sangat disarankan diisi di file `.env`. Sistem secara otomatis memprioritaskan `.env` dan mengunci input di web GUI sehingga kunci rahasia transaksi tidak pernah disimpan di database MySQL (mencegah kebocoran saat backup/dump database).
> 2. **Perangkat Jaringan (MikroTik, OLT, FreeRADIUS, GenieACS, WhatsApp):** Dapat diatur melalui `.env` atau langsung via menu web GUI **Pengaturan Sistem**. Semua password yang disimpan ke database otomatis dienkripsi dengan standar **AES-256-CBC** (`Crypt::encryptString`) sehingga tidak tersimpan dalam bentuk teks terbuka (plain-text).
> 3. **Proteksi File:** Pastikan permission `.env` adalah `chmod 640 .env` dengan kepemilikan `chown www:www .env` agar tidak dapat diintip oleh user lain di server.

---

## 6. Langkah 6: Migrasi Database & Setup FreeRADIUS

Buka menu **Terminal** aaPanel, jalankan perintah migrasi:

```bash
cd /www/wwwroot/stage.erlangga.net
/www/server/php/82/bin/php artisan migrate --force
```

Uji kesiapan sistem dan konfigurasi FreeRADIUS dengan mode simulasi (dry-run):
```bash
/www/server/php/82/bin/php artisan billing:process --dry-run
```
*Jika output menampilkan `PROSES SELESAI`, berarti database Laravel dan koneksi FreeRADIUS sudah 100% tersambung sempurna!*

> [!NOTE]
> **Migrasi Data Pelanggan dari Radboox / Sistem Lama:**  
> Jika Anda sedang memindahkan data dari Radboox (pelanggan, akun PPPoE, invoice, dan riwayat transaksi), silakan ikuti panduan khusus di **[PANDUAN_MIGRASI_RADBOOX.md](file:///Users/bbs/Documents/Project/app-erlangga-net/docs/PANDUAN_MIGRASI_RADBOOX.md)**. Panduan tersebut menjelaskan alur import via Web GUI (`/migration`) maupun via Terminal CLI aaPanel.

---

## 7. Langkah 7: Konfigurasi Nginx (Running Dir, URL Rewrite, Timeout)

Buka menu **Website** di aaPanel -> Klik nama domain website Anda (`stage.erlangga.net`):

### A. Tab "Site directory"
* **Running directory:** Pilih dropdown **/public** -> Klik tombol **Save**.
* Matikan / hapus centang jika ada opsi pembatasan *anti-XSS attack (open_basedir)* agar symlink storage terbaca lancar.

### B. Tab "URL rewrite"
* Pada dropdown pilihan preset rewrite, pilih **laravel5** (aaPanel sudah menyediakan preset resmi).
* Isinya akan otomatis seperti berikut:
  ```nginx
  location / {
      try_files $uri $uri/ /index.php?$query_string;
  }
  ```
* Klik **Save**.

### C. Tab "SSL"
* Pilih tab **Let's Encrypt** -> Centang domain Anda -> Klik **Apply**.
* Setelah berhasil terbit, aktifkan sakelar **Force HTTPS**.

### D. Tab "Config file" (Cegah Timeout 504)
Cari blok `location ~ \.php$` lalu tambahkan baris berikut di dalamnya:
```nginx
    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-82.sock;
        fastcgi_index index.php;
        include fastcgi.conf;
        include pathinfo.conf;

        # Tambahkan konfigurasi buffer & timeout berikut:
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }
```
Dan pastikan di bagian atas file ada baris `client_max_body_size 50M;`. Klik **Save**.

---

## 8. Langkah 8: Konfigurasi Cron Job di Menu Cron aaPanel

aaPanel memiliki menu **Cron** khusus di sidebar kiri. Sistem scheduler Laravel wajib didaftarkan agar penagihan otomatis, jatuh tempo, dan auto-isolir berjalan setiap hari.

1. Buka menu **Cron** di sidebar kiri aaPanel.
2. Tambahkan task baru dengan detail:
   * **Type of Task:** Pilih `Shell Script`
   * **Name of Task:** `Laravel Scheduler Erlangga Net`
   * **Period:** Pilih `N Minutes` -> isi angka `1` (Berjalan setiap 1 menit).
   * **Script content:**
     ```bash
     cd /www/wwwroot/stage.erlangga.net && /www/server/php/82/bin/php artisan schedule:run >> /dev/null 2>&1
     ```
3. Klik **Add Task**.

### ⏰ Apa yang Dijalankan oleh Task Ini Secara Otomatis?
* **Setiap Pukul 00:01 WIB:**  
  Mengecek pelanggan yang memasuki H-7 masa aktif untuk diterbitkan faktur baru, serta mengeksekusi **Auto-Isolir** ke FreeRADIUS & MikroTik bagi pelanggan yang telah melewati tanggal jatuh tempo (`due_date < now()`).
* **Setiap Pukul 12:00 WIB:**  
  Pemeriksaan isolir tambahan tengah hari untuk memastikan tidak ada akun overdue yang terlewat.

---

## 9. Langkah 9: Konfigurasi Queue Worker via Supervisor Manager

Queue Worker bertugas mengirim notifikasi push FCM broadcast ke ratusan/ribuan HP pelanggan di latar belakang tanpa membuat halaman admin loading lama.

1. Buka menu **App Store** di aaPanel -> Cari **Supervisor Manager** -> Buka **Setting**.
2. Klik tab **Add Daemon**:
   * **Name:** `erlangga-worker`
   * **Run User:** `www`
   * **Run Dir:** `/www/wwwroot/stage.erlangga.net`
   * **Start Command:**
     ```bash
     /www/server/php/82/bin/php artisan queue:work database --sleep=3 --tries=3 --max-time=3600
     ```
   * **Processes:** `2`
3. Klik **Submit**.
4. Pastikan status daemon bertuliskan **Running** (berwarna hijau).

---

## 10. Langkah 10: Prosedur Pembaruan Rutin (Git Pull)

Jika di kemudian hari ada pembaruan kode dari repositori GitHub, Anda cukup membuka menu **Terminal** di aaPanel dan menjalankan:

```bash
cd /www/wwwroot/stage.erlangga.net
git pull origin main
/www/server/php/82/bin/php artisan migrate --force
npm run build
/www/server/php/82/bin/php artisan optimize:clear
/www/server/php/82/bin/php artisan config:cache
/www/server/php/82/bin/php artisan route:cache
/www/server/php/82/bin/php artisan view:cache
```

Jika ada pembaruan pada proses background, restart worker di menu **Supervisor Manager** -> Klik tombol **Restart** pada `erlangga-worker`.

---

## 11. Troubleshooting Khusus aaPanel

### 1. Halaman web menampilkan "500 Internal Server Error"
* **Penyebab:** Hak akses file kepemilikan berubah atau file `.env` belum ada.
* **Solusi:** Jalankan di Terminal aaPanel:
  ```bash
  cd /www/wwwroot/stage.erlangga.net
  chown -R www:www .
  chmod -R 775 storage bootstrap/cache
  /www/server/php/82/bin/php artisan config:clear
  ```

### 2. Composer Error: "The Process class relies on proc_open, which is not available on your PHP installation"
* **Penyebab:** Fungsi `proc_open` atau `putenv` masih diblokir oleh aaPanel.
* **Solusi:** Masuk ke **App Store** -> **PHP-8.2** -> Tab **Disabled functions** -> Cari dan **Hapus (Delete)** `proc_open` dan `putenv`, lalu restart PHP.

### 3. Foto Bukti Instalasi / KTP Tidak Tampil (404 Not Found)
* **Penyebab:** Symlink storage publik belum terbentuk atau diarahkan salah oleh aaPanel.
* **Solusi:** Jalankan di Terminal aaPanel:
  ```bash
  cd /www/wwwroot/stage.erlangga.net
  rm -rf public/storage
  /www/server/php/82/bin/php artisan storage:link
  ```

### 4. Database FreeRADIUS Connection Refused
* **Penyebab:** MySQL di server FreeRADIUS menolak koneksi dari IP VPS aaPanel Anda.
* **Solusi:** 
  1. Di server FreeRADIUS, edit `/etc/mysql/mysql.conf.d/mysqld.cnf` dan ubah `bind-address = 0.0.0.0`.
  2. Buka port `3306` pada menu **Security** di aaPanel dan firewall server RADIUS.
  3. Berikan izin grant user MySQL FreeRADIUS untuk IP VPS aaPanel Anda.
