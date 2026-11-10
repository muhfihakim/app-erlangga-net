# 🚀 Panduan Lengkap Instalasi & Konfigurasi Server VPS
## Web App Erlangga Net Management System (`app-erlangga-net`)

Dokumentasi ini berisi panduan *step-by-step* instalasi, konfigurasi web server, database, FreeRADIUS, MikroTik, OLT, GenieACS, Cron Job, hingga *Queue Worker* untuk menjalankan aplikasi **Erlangga Net** di server VPS (Ubuntu 22.04/24.04 atau aaPanel/OpenResty).

---

## 📑 Daftar Isi
1. [Prasyarat Server & Spesifikasi Minimum](#1-prasyarat-server--spesifikasi-minimum)
2. [Instalasi Dependensi Server (PHP, Composer, Node.js, Redis)](#2-instalasi-dependensi-server)
3. [Clone Repositori & Setup Direktori](#3-clone-repositori--setup-direktori)
4. [Konfigurasi Environment (.env)](#4-konfigurasi-environment-env)
5. [Migrasi Database & Database FreeRADIUS](#5-migrasi-database--database-freeradius)
6. [Konfigurasi Web Server (Nginx / aaPanel)](#6-konfigurasi-web-server-nginx--aapanel)
7. [Konfigurasi Cron Job (Otomasi Billing & Isolir)](#7-konfigurasi-cron-job-otomasi-billing--isolir)
8. [Konfigurasi Queue Worker (Supervisor)](#8-konfigurasi-queue-worker-supervisor)
9. [Prosedur Pembaruan Aplikasi (Git Pull & Deploy)](#9-prosedur-pembaruan-aplikasi-git-pull--deploy)
10. [Troubleshooting & Solusi Kendala Umum](#10-troubleshooting--solusi-kendala-umum)

---

## 1. Prasyarat Server & Spesifikasi Minimum

### Spesifikasi Rekomendasi VPS:
* **OS:** Ubuntu 20.04 / 22.04 / 24.04 LTS, Debian 11/12, atau AlmaLinux 9 (Mendukung aaPanel).
* **CPU:** Minimal 2 vCPU (Disarankan 4 vCPU untuk 1.000+ pelanggan).
* **RAM:** Minimal 4 GB (Disarankan 8 GB jika database MySQL & FreeRADIUS berada di VPS yang sama).
* **Storage:** 40 GB+ SSD / NVMe.
* **Konektivitas Jaringan:** IP Publik Statis dengan akses ke port API MikroTik (`8728`), OLT ZTE (`23`), dan database FreeRADIUS (`3306`).

---

## 2. Instalasi Dependensi Server

### A. Update Paket Sistem
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget git unzip zip software-properties-common ufw supervisor redis-server
```

### B. Instalasi PHP 8.2 / 8.3 & Ekstensi yang Dibutuhkan
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml \
php8.2-bcmath php8.2-curl php8.2-gd php8.2-zip php8.2-redis php8.2-intl \
php8.2-soap php8.2-sockets php8.2-cli
```

### C. Instalasi Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### D. Instalasi Node.js & NPM (v20 LTS)
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node -v && npm -v
```

---

## 3. Clone Repositori & Setup Direktori

Asumsi lokasi direktori web adalah `/www/wwwroot/stage.erlangga.net` (standar aaPanel) atau `/var/www/erlangga.net`.

```bash
# 1. Pindah ke direktori web
cd /www/wwwroot

# 2. Clone repositori
git clone https://github.com/muhfihakim/app-erlangga-net.git stage.erlangga.net
cd stage.erlangga.net

# 3. Salin file environment
cp .env.example .env

# 4. Install dependensi PHP & Node.js
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 5. Generate Application Key
php artisan key:generate

# 6. Buat symbolic link storage
php artisan storage:link

# 7. Berikan hak akses folder (User web: www-data atau www untuk aaPanel)
sudo chown -R www:www /www/wwwroot/stage.erlangga.net
sudo chmod -R 775 /www/wwwroot/stage.erlangga.net/storage
sudo chmod -R 775 /www/wwwroot/stage.erlangga.net/bootstrap/cache
```

---

## 4. Konfigurasi Environment (`.env`)

Buka dan sesuaikan file `.env`:
```bash
nano .env
```

Pastikan variabel-variabel kunci berikut terisi sesuai kondisi infrastruktur:

```ini
APP_NAME="ERLANGGA.NET"
APP_ENV=production
APP_KEY=base64:... # Dihasilkan oleh php artisan key:generate
APP_DEBUG=false
APP_URL=https://stage.erlangga.net

# 1. DATABASE UTAMA (MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erlangga_billing
DB_USERNAME=erlangga_user
DB_PASSWORD=PasswordDatabaseAnda

# 2. DATABASE FREERADIUS (Koneksi Kedua)
# Jika FreeRADIUS berada di server/VPS terpisah, arahkan IP dan port remote-nya
DB_RADIUS_HOST=127.0.0.1
DB_RADIUS_PORT=3306
DB_RADIUS_DATABASE=radius
DB_RADIUS_USERNAME=radius_user
DB_RADIUS_PASSWORD=PasswordRadiusAnda

# 3. DRIVER SESI, CACHE & QUEUE
SESSION_DRIVER=database
CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
QUEUE_CONNECTION=database

# 4. PENYIMPANAN BUKTI FOTO (S3 / MinIO)
FILESYSTEM_DISK=s3
S3_ACCESS_KEY_ID=access_key_minio_anda
S3_SECRET_ACCESS_KEY=secret_key_minio_anda
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

# 6. OLT ZTE (Monitoring Telnet)
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
XENDIT_WEBHOOK_TOKEN=token_verifikasi_webhook_anda

# 9. FIREBASE FCM (Push Notification ke HP Pelanggan)
FIREBASE_ENABLED=1
FIREBASE_PROJECT_ID=erlangga-net
# Letakkan path file JSON service account Google Firebase di storage
FIREBASE_CREDENTIALS_JSON=/www/wwwroot/stage.erlangga.net/storage/app/firebase-auth.json
```

---

## 5. Migrasi Database & Database FreeRADIUS

Setelah koneksi database di `.env` sudah benar:

```bash
# 1. Jalankan migrasi seluruh tabel (termasuk kolom penyesuaian & billing_period)
php artisan migrate --force

# 2. Inisialisasi atribut grup ISOLIR di radgroupreply FreeRADIUS
php artisan billing:process --dry-run
```

> [!IMPORTANT]
> **Database FreeRADIUS Remote:**  
> Jika MySQL FreeRADIUS berada di server terpisah dari VPS web, pastikan file konfigurasi MySQL di server RADIUS (`/etc/mysql/mysql.conf.d/mysqld.cnf`) memiliki baris `bind-address = 0.0.0.0`, dan berikan hak akses remote (`GRANT ALL ON radius.* TO 'radius_user'@'IP_VPS_WEB' IDENTIFIED BY 'password';`).

---

## 6. Konfigurasi Web Server (Nginx / aaPanel)

### A. Template Blok Server Nginx
Tambahkan atau sesuaikan konfigurasi virtual host Nginx Anda (misal pada `/etc/nginx/sites-available/stage.erlangga.net` atau menu **Site Settings -> Config file** di aaPanel):

```nginx
server {
    listen 80;
    server_name stage.erlangga.net;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name stage.erlangga.net;

    # SSL Certificate (Sesuaikan path sertifikat Anda)
    ssl_certificate /etc/letsencrypt/live/stage.erlangga.net/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/stage.erlangga.net/privkey.pem;

    root /www/wwwroot/stage.erlangga.net/public;
    index index.php index.html;

    charset utf-8;
    client_max_body_size 50M;

    # Gzip Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript image/svg+xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Konfigurasi PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock; # Sesuaikan dengan socket PHP VPS Anda
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;

        # Mencegah 504 Gateway Timeout saat export PDF laporan / broadcast berkapasitas besar
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    # Blokir akses ke file sensitif
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Uji dan reload Nginx:
```bash
sudo nginx -t && sudo systemctl reload nginx
```

---

## 7. Konfigurasi Cron Job (Otomasi Billing & Isolir)

Laravel menggunakan satu jadwal induk (*master scheduler*) yang dieksekusi setiap menit. Seluruh jadwal otomasi tagihan, pengingat jatuh tempo, dan isolir akan diatur otomatis oleh Laravel.

### A. Menambahkan Crontab
Buka crontab dengan pengguna web (`www` atau `www-data`):
```bash
crontab -e -u www
```
*(Atau jika menggunakan menu **Cron** di aaPanel, buat tugas bertipe **Shell Script** yang berjalan setiap menit)*

Tambahkan baris berikut:
```bash
* * * * * cd /www/wwwroot/stage.erlangga.net && php artisan schedule:run >> /dev/null 2>&1
```

### B. Jadwal yang Berjalan Otomatis di Dalam Sistem:
1. **Pukul 00:01 WIB Setiap Malam:**  
   Menjalankan `php artisan billing:process`:
   - Menerbitkan faktur baru bagi pelanggan yang telah memasuki masa penagihan (H-7 sebelum jatuh tempo).
   - Memeriksa faktur yang telah melewati tanggal jatuh tempo (`due_date < now()`) dan mengeksekusi **Auto-Isolir** ke FreeRADIUS & MikroTik.
   - Mengirim notifikasi push FCM ke smartphone pelanggan.
2. **Pukul 12:00 WIB Setiap Siang:**  
   Menjalankan `php artisan billing:process --only-isolir`:
   - Pemeriksaan tambahan tengah hari untuk memastikan tidak ada akun jatuh tempo yang lolos dari isolir.

### C. Perintah CLI Manual yang Berguna untuk Teknisi/Admin:
```bash
# Simulasi pengecekan tanpa mengubah database (Aman untuk cek jadwal):
php artisan billing:process --dry-run

# Paksa cek dan eksekusi isolir serta kick sesi MikroTik:
php artisan billing:process --only-isolir --kick

# Simulasi cek isolir saja:
php artisan billing:isolir --dry-run
```

---

## 8. Konfigurasi Queue Worker (Supervisor)

Untuk memastikan notifikasi push FCM broadcast, pemrosesan log, dan antrean data background berjalan tanpa membebani request web HTTP:

### A. Buat Konfigurasi Supervisor
```bash
sudo nano /etc/supervisor/conf.d/erlangga-worker.conf
```

Isi dengan konfigurasi berikut:
```ini
[program:erlangga-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /www/wwwroot/stage.erlangga.net/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www
numprocs=2
redirect_stderr=true
stdout_logfile=/www/wwwroot/stage.erlangga.net/storage/logs/worker.log
stopwaitsecs=3600
```

### B. Aktifkan Supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start erlangga-worker:*
sudo supervisorctl status
```

---

## 9. Prosedur Pembaruan Aplikasi (Git Pull & Deploy)

Setiap kali ada pembaruan kode pada branch `main`, jalankan rangkaian perintah berikut di terminal server:

```bash
# 1. Masuk ke direktori web
cd /www/wwwroot/stage.erlangga.net

# 2. Tarik kode terbaru dari GitHub
git pull origin main

# 3. Perbarui library jika ada penambahan package
composer install --no-dev --optimize-autoloader

# 4. Jalankan migrasi database otomatis jika ada tabel/kolom baru
php artisan migrate --force

# 5. Build asset frontend (Vite/Tailwind)
npm run build

# 6. Bersihkan dan perbarui cache optimasi
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart queue worker (agar membaca kode PHP terbaru)
sudo supervisorctl restart erlangga-worker:*
```

---

## 10. Troubleshooting & Solusi Kendala Umum

| Masalah | Penyebab Umum | Solusi |
|---|---|---|
| **Error 500 / Blank Screen** | Hak akses folder `storage` atau file `.env` belum terkonfigurasi. | Jalankan `chmod -R 775 storage bootstrap/cache` dan pastikan `APP_KEY` sudah terisi via `php artisan key:generate`. |
| **Gagal Koneksi Database FreeRADIUS** | Port 3306 server RADIUS ditolak firewall atau `bind-address` MySQL masih `127.0.0.1`. | Buka port 3306 di UFW server RADIUS dan ubah `bind-address = 0.0.0.0` pada `mysqld.cnf`. |
| **MikroTik Connection Refused / Timeout** | Port API `8728` belum dibuka di router MikroTik. | Di Terminal Winbox jalankan `/ip service enable api` dan periksa firewall filter input router. |
| **Isolir Berhasil tapi User Masih Bisa Internet** | Pool IP Isolir belum diarahkan ke profile firewall NAT isolir di MikroTik. | Pastikan IP Pool `pool-isolir` (misal `172.30.0.0/16`) memiliki aturan NAT redirect port 80/443 ke portal web isolir `/isolir`. |
| **504 Gateway Timeout Saat Broadcast** | Timeout PHP-FPM atau Nginx terlalu singkat. | Naikkan `fastcgi_read_timeout 300;` di Nginx dan `max_execution_time = 180` di `php.ini`. |
| **Cron Job Tidak Berjalan Otomatis** | User crontab salah atau path PHP tidak ditemukan. | Pastikan menggunakan full path: `* * * * * /usr/bin/php /www/wwwroot/stage.erlangga.net/artisan schedule:run >> /dev/null 2>&1`. |
