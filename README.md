# Erlangga Net Management System

Sebuah sistem informasi manajemen pelanggan ISP (Internet Service Provider) berbasis web yang dirancang khusus untuk operasional **Erlangga Net**. Aplikasi ini membantu dalam mengelola siklus hidup pelanggan mulai dari Pre-Order (PO) oleh tim Sales, proses instalasi oleh tim Teknisi, hingga pemantauan status koneksi pelanggan yang telah aktif.

## 🚀 Fitur Utama

- **Role-Based Access Control (RBAC):** Memiliki role Administrator, Sales, dan Teknisi dengan hak akses yang disesuaikan.
- **Manajemen Pre-Order (PO):** Tim Sales dapat mendaftarkan calon pelanggan baru lengkap dengan lokasi (titik koordinat peta) dan paket yang dipilih.
- **Manajemen Pelanggan Aktif:** Menampilkan daftar pelanggan yang telah diaktifkan lengkap dengan fitur *Server-Side Processing* untuk memuat ribuan data dengan sangat cepat.
- **Integrasi Mikrotik PPPoE:** Melacak status **Online / Offline** pelanggan secara real-time berdasarkan data active PPPoE dari router Mikrotik.
- **Monitoring Pelanggan Isolir:** Mendeteksi pelanggan yang terisolir secara otomatis berdasarkan IP Address khusus (misal: `172.30.x.x`).
- **Pemetaan Pelanggan (Maps):** Integrasi Leaflet.js untuk memetakan lokasi pelanggan secara visual berdasarkan status jaringan (Hijau: Online, Merah: Offline).
- **Laporan & Export PDF:** Laporan terperinci performa Sales dan laporan tagihan/isolir per Sales yang dapat diexport dalam format PDF.
- **Dokumentasi Instalasi:** Mendukung penyimpanan dan preview dokumentasi foto lapangan (Foto Petugas, Perangkat Terpasang, dan Hasil Speedtest).

## 🛠️ Teknologi & Tools

- **Framework:** [Laravel](https://laravel.com/) (PHP 8.2+)
- **Database:** MySQL / SQLite
- **Frontend UI:** HTML5, CSS3, Blade Templating, FontAwesome
- **Maps:** [Leaflet.js](https://leafletjs.com/) & OpenStreetMap
- **DataTables:** [Yajra DataTables](https://yajrabox.com/docs/laravel-datatables) untuk *Server-Side Processing*
- **PDF Generator:** [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf)
- **Mikrotik API:** [zilleali/mikrotik-laravel](https://github.com/zilleali/mikrotik-laravel)

## 📋 Prasyarat

Pastikan server Anda memenuhi persyaratan berikut sebelum melakukan instalasi:
- PHP >= 8.2
- Composer
- Node.js & NPM
- Ekstensi PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, cURL

## ⚙️ Instalasi

1. **Clone repositori**
   ```bash
   git clone https://github.com/muhfihakim/app-erlangga-net.git
   cd app-erlangga-net
   ```

2. **Install dependensi PHP dan Node.js**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Konfigurasi Environment**
   Salin file `.env.example` menjadi `.env` lalu sesuaikan konfigurasi Database dan Mikrotik Anda.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Migrasi Database & Seeder (Jika Ada)**
   ```bash
   php artisan migrate --seed
   ```

5. **Jalankan Aplikasi**
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui browser pada alamat `http://localhost:8000`.

## 📂 Struktur Penting

- **`CustomerController.php`** - Mengelola logika daftar pelanggan aktif, integrasi DataTables, perhitungan statistik bulanan/isolir, dan export PDF.
- **`active_list.blade.php`** - Halaman utama interaktif yang memuat ringkasan statistik, peta penyebaran pelanggan, dan tabel server-side.
- **Mikrotik Cache** - Aplikasi ini menggunakan caching Laravel untuk menyimpan status dari Mikrotik agar load halaman tetap ringan.

## 📄 Lisensi

Aplikasi ini bersifat tertutup (proprietary) dan dirancang khusus untuk kebutuhan internal **Erlangga Net**.
