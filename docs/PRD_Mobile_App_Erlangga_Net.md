# Product Requirements Document (PRD)
## Erlangga Net - Mobile Application (Flutter)

**Versi Dokumen:** 1.0
**Tanggal:** 30 Mei 2026
**Platform Tujuan:** Android & iOS (Flutter / Dart)
**Backend / API:** Laravel 11 (REST API via Sanctum)

---

## 1. Ringkasan Eksekutif
Aplikasi Mobile Erlangga Net adalah ekstensi dari sistem manajemen Web FTTH (Fiber to the Home) & ISP berbasis Laravel yang sudah ada. Aplikasi ini dirancang untuk memudahkan mobilitas tim lapangan (Teknisi) dan tim pemasaran (Sales) dalam menjalankan tugas mereka langsung dari *smartphone*, serta memberikan kontrol penuh bagi Administrator. Aplikasi mobile ini akan mereplikasi seluruh *flow* bisnis, peran (role), dan modul yang ada di versi Web.

## 2. Tujuan & Sasaran
* **Mobilitas:** Memungkinkan teknisi memperbarui status pemasangan, perbaikan, dan dismantle langsung dari lokasi pelanggan (termasuk unggah foto lapangan dan absensi lokasi).
* **Pemantauan Real-time:** Memungkinkan sales memantau prospek (pre-order) dan pelanggan yang terisolir (suspend) secara real-time.
* **Integrasi MikroTik:** Meneruskan kapabilitas *real-time monitoring* PPPoE dan Traffic dari web ke mobile khusus untuk Administrator.
* **Standarisasi UI/UX:** Membawa komponen visual dari web (kartu statistik, *badge* warna, *bottom sheet* sebagai ganti modal) ke desain *Material 3 / Cupertino* di Flutter.

---

## 3. Manajemen Akses & Peran (Role-Based Access Control)

Aplikasi memiliki 3 *Role* utama dengan batasan akses menu yang ketat:

| Modul / Menu | Administrator | Sales | Teknisi |
| :--- | :---: | :---: | :---: |
| **Dashboard** | ✅ (Global Stats & Charts) | ✅ (Personal Stats) | ✅ (Personal Task Stats) |
| **Pre-Order** | ✅ (All & Delegate) | ✅ (Own Data Only) | ✅ (Assigned Tasks Only)|
| **Pelanggan Aktif** | ✅ (All & Isolir) | ✅ (Own Data & Isolir) | ✅ (Semua untuk referensi)|
| **Pemeliharaan** | ✅ (All & Delegate) | ❌ | ✅ (Assigned Tasks Only)|
| **Dismantle** | ✅ (All & Delegate) | ❌ | ✅ (Assigned Tasks Only)|
| **Manajemen ODP**| ✅ | ❌ | ❌ |
| **PPPoE Aktif** | ✅ (Real-time Mikrotik)| ❌ | ❌ |
| **Monitoring (Traffic)**| ✅ (Real-time Mikrotik)| ❌ | ❌ |
| **Keuangan Teknisi**| ✅ | ❌ | ✅ (Own Income) |
| **Akun / Users** | ✅ | ❌ | ❌ |
| **Log Aktivitas** | ✅ | ❌ | ❌ |

---

## 4. Spesifikasi Fitur Utama & Modul (Flow Aplikasi)

### 4.1. Modul Autentikasi & Akun
* **Login:** Menggunakan Email & Password via API Laravel Sanctum.
* **2FA (Opsional):** Jika user mengaktifkan Google Authenticator di Web, Mobile harus menyediakan form OTP setelah login.
* **Logout:** Menghapus token di sisi perangkat dan server.

### 4.2. Dashboard Overview
Tampilan dashboard dinamis berdasarkan *Role*:
* **Administrator:**
  * Statistik Total PO, PO Bulan Lalu, Total Aktivasi, Total Pending.
  * Grafik (Charts): Tren Aktivasi Mingguan, Top Teknisi, Top Sales, Tren Maintenance & Dismantle.
  * Daftar Aktivitas Terbaru (Log Sistem).
* **Sales:**
  * Total Pelanggan Aktif (miliknya).
  * Total Pelanggan Terisolir (berdasarkan deteksi IP `172.30.*.*` dari MikroTik).
* **Teknisi:**
  * Pengerjaan Aktivasi PO (Sabtu - Jumat): Bekerja Sendiri, Berdua, Bertiga, Total.
  * Pengerjaan Maintenance (Tgl 29 bulan lalu - 28 bulan ini): Sendiri, Berdua, Bertiga, Total.
  * Daftar Tugas Delegasi Khusus (Urgent/Expired cards).

### 4.3. Modul Pre-Order (Pemasangan Baru)
* **Flow Sales:**
  * Input data prospek (Nama, Email, WA, Paket, Alamat, Kordinat Maps).
  * Status awal: `Pending`.
* **Flow Administrator:**
  * Mendelegasikan (Assign) PO yang pending ke Teknisi tertentu.
* **Flow Teknisi:**
  * Menerima PO yang didelegasikan.
  * **Aktivasi (Selesai):** Input PPPoE User, PPPoE Pass, Jenis Modem, SN Modem, Pembayaran. Upload 3 Foto (Lokasi, Router, Speedtest).
  * **Unspek (Gagal/Batal):** Memasukkan alasan kenapa pemasangan gagal (misal: tiang penuh). Status berubah menjadi `Unspek`.
* **Flow Lanjutan:** Administrator dapat meninjau PO Unspek dan mengubahnya kembali menjadi Pending (Re-Open).

### 4.4. Modul Pelanggan
* **Daftar Pelanggan Aktif:** Menampilkan list pelanggan yang sudah diaktivasi.
* **Pelanggan Terisolir (Peringatan):** (Hanya untuk Admin & Sales terkait). Ditampilkan di bagian teratas. Mendeteksi user dengan IP MikroTik `172.30.*.*`. Terdapat tombol akses cepat ke WhatsApp pelanggan.
* **Detail Pelanggan:** Menggunakan *Bottom Sheet* atau navigasi halaman baru berisi: Biodata, Data Teknis (termasuk SN ONT, User PPPoE), dan Dokumentasi (Foto pemasangan).
* **Action:** Admin dapat menghapus (Soft Delete) atau me-restore pelanggan.

### 4.5. Modul Pemeliharaan (Maintenance)
* **Flow Admin:** Menerima komplain -> Membuat tiket Maintenance -> Assign ke Teknisi -> Melampirkan Foto Kendala.
* **Flow Teknisi:**
  * Melihat tiket di tab "Tugas Delegasi Khusus".
  * Klik "Ambil / Selesaikan".
  * **Form Penyelesaian:**
    1. Memilih *Partner* kerja (Teknisi lain yang ikut ke lokasi).
    2. Memilih **Kategori Kendala** (ONT, Kabel Fiber Optic, Lainnya).
    3. *Jika ONT:* Sistem memunculkan **SN ONT Lama**, Teknisi wajib menginput **SN ONT Baru**. (Aplikasi akan otomatis mengupdate SN di tabel Customer).
    4. Input penjelasan solusi teknis.
    5. Upload 4 Foto Wajib: Sebelum Perbaikan, Sesudah Perbaikan, Speedtest, Selfie Tim.
* **Hasil:** Admin dapat melihat detail hasil pengerjaan lengkap dengan foto dan data perubahan SN.

### 4.6. Modul Dismantle (Pencabutan)
* **Flow Admin:** Membuat tiket Dismantle -> Assign ke Teknisi.
* **Flow Teknisi:** Mengerjakan tiket -> Upload 2 foto (Perangkat yang ditarik & Rumah Pelanggan) -> Input Keterangan.
* **Post-Action:** Setelah selesai, pelanggan otomatis dihapus (*Soft Delete*) dari sistem agar tidak tertagih bulan depannya.

### 4.7. Modul Infrastruktur (Khusus Admin)
* **Manajemen ODP:** List lokasi ODP, koordinat (terintegrasi dengan Maps/Leaflet di mobile), Port Terpakai vs Total Port.
* **PPPoE Aktif:** 
  * List *Real-time* user yang terhubung di Router MikroTik.
  * Menampilkan: Uptime (Format HH:MM:SS), Remote Address, Caller ID (MAC), dan Traffic (RX/TX Byte).
  * Di Mobile, grafik *sparkline* bisa diganti dengan indikator *up/down* sederhana untuk menghemat *resource rendering*.
* **Monitoring:** Pengecekan status router MikroTik (Online/Offline) dan total traffic interface utama.

### 4.8. Modul Keuangan Teknisi
* **Fitur:** Menghitung estimasi pendapatan teknisi berdasarkan rumus tarif dasar dibagi jumlah orang (partner) yang mengerjakan aktivasi.
* **Filter:** Berdasarkan bulan & tahun tertentu.

### 4.9. Modul Tracking Lokasi (Background/Foreground)
* **Fitur:** Aplikasi mobile teknisi secara berkala mengirim koordinat GPS (`latitude`, `longitude`) ke backend `/api/location/update`.
* **Admin View:** Admin dapat melihat posisi terkini (Live Tracking) para teknisi di atas peta.

---

## 5. Kebutuhan API (Backend Readiness)
Agar aplikasi Flutter dapat berfungsi, Laravel *Web App* saat ini harus diperluas pada file `routes/api.php`. Sebagian besar logika yang saat ini me-return `view('blade')` harus dibuatkan versi Controller API yang me-return `response()->json()`.

* **Dibutuhkan Endpoint API Untuk:**
  1. `POST /api/login` & `POST /api/logout`
  2. `GET /api/dashboard/stats` (Membawa data charts dan cards)
  3. `GET /api/pre-orders`, `POST /api/pre-orders`, `POST /api/pre-orders/{id}/activate`, `POST /api/pre-orders/{id}/unspek`
  4. `GET /api/customers`, `GET /api/customers/isolir`
  5. `GET /api/maintenances`, `POST /api/maintenances/{id}/complete` (Multipart/form-data untuk foto)
  6. `GET /api/dismantles`, `POST /api/dismantles/{id}/complete`
  7. `GET /api/mikrotik/pppoe` (Menggunakan cache yang sudah dibuat oleh `MikrotikMonitor`)
  8. `POST /api/location/update` (Sudah tersedia)

---

## 6. Persyaratan Non-Fungsional (Mobile App)
* **State Management:** Disarankan menggunakan `Provider`, `Riverpod`, atau `GetX` untuk mengelola data state dan API cache di perangkat.
* **Routing:** `go_router` atau `auto_route` untuk manajemen navigasi dan *Role-guarding*.
* **Penyimpanan Lokal:** `shared_preferences` atau `flutter_secure_storage` untuk menyimpan *Sanctum Token* dan data User.
* **Kamera & Lokasi:** Memerlukan integrasi `image_picker` (kamera & galeri) dan `geolocator` (mendapatkan titik koordinat saat pemasangan PO/Maintenance).
* **Maps:** Menggunakan `flutter_map` atau `google_maps_flutter` untuk memvisualisasikan data Titik Koordinat ODP dan Lokasi Pelanggan.
* **Kinerja Jaringan:** 
  * Wajib menggunakan kompresi gambar (*image compression*) di sisi Flutter sebelum mengirim 3-4 foto sekaligus pada modul Aktivasi/Maintenance untuk menghemat bandwidth.
  * Menerapkan *Debouncing* pada fitur pencarian (Search).

---

## 7. Panduan UI/UX Mobile
* **Theme:** Mengikuti palet warna dari web (Primary: Blue/Sky, Success: Emerald, Warning: Amber, Danger: Rose, Background: Slate-50).
* **Cards & Shadows:** Komponen *Cards* dengan bayangan (*box-shadow* tipis) dan *border-radius* (12-16px) khas *modern design*.
* **Tables vs Lists:** Karena layar mobile kecil, tabel kompleks (seperti pada web) harus diubah menjadi **List View / Card View**. Informasi sekunder disembunyikan dan hanya muncul ketika *Card* di-tap (*Bottom Sheet* Detail).
* **Forms:** Form yang panjang (seperti aktivasi PO) sebaiknya menggunakan *Stepper* atau desain *Scrollable Form* dengan validasi yang jelas.

---

## 8. Catatan Rencana Pengembangan
* Developer Flutter **wajib** menyesuaikan pengiriman foto dengan tipe `MultipartRequest` di protokol HTTP Dart.
* Sistem API MikroTik di Laravel sudah dibuat menggunakan Cache (oleh *Background Worker*). Aplikasi Flutter cukup melakukan *HTTP GET Request* biasa, tidak perlu terkoneksi WebSockets secara langsung jika hanya menggunakan *polling* 10 detik.
