# Product Requirements Document (PRD)

## Erlangga Net - Mobile Application (Flutter)

**Versi Dokumen:** 1.0
**Tanggal:** 30 Mei 2026
**Platform Tujuan:** Android & iOS (Flutter / Dart)
**Backend / API:** Laravel 11 (REST API via Sanctum)

---

## 1. Ringkasan Eksekutif

Aplikasi Mobile Erlangga Net adalah ekstensi dari sistem manajemen Web FTTH (Fiber to the Home) & ISP berbasis Laravel yang sudah ada. Aplikasi ini dirancang untuk memudahkan mobilitas tim lapangan (Teknisi) dan tim pemasaran (Sales) dalam menjalankan tugas mereka langsung dari _smartphone_, serta memberikan kontrol penuh bagi Administrator. Aplikasi mobile ini akan mereplikasi seluruh _flow_ bisnis, peran (role), dan modul yang ada di versi Web.

## 2. Tujuan & Sasaran

- **Mobilitas:** Memungkinkan teknisi memperbarui status pemasangan, perbaikan, dan dismantle langsung dari lokasi pelanggan (termasuk unggah foto lapangan dan absensi lokasi).
- **Pemantauan Real-time:** Memungkinkan sales memantau prospek (pre-order) dan pelanggan yang terisolir (suspend) secara real-time.
- **Integrasi MikroTik:** Meneruskan kapabilitas _real-time monitoring_ PPPoE dan Traffic dari web ke mobile khusus untuk Administrator.
- **Standarisasi UI/UX:** Membawa komponen visual dari web (kartu statistik, _badge_ warna, _bottom sheet_ sebagai ganti modal) ke desain _Material 3 / Cupertino_ di Flutter.

---

## 3. Manajemen Akses & Peran (Role-Based Access Control)

Aplikasi memiliki 3 _Role_ utama dengan batasan akses menu yang ketat:

| Modul / Menu             |       Administrator        |         Sales          |          Teknisi           |
| :----------------------- | :------------------------: | :--------------------: | :------------------------: |
| **Dashboard**            | ✅ (Global Stats & Charts) |  ✅ (Personal Stats)   |  ✅ (Personal Task Stats)  |
| **Pre-Order**            |    ✅ (All & Delegate)     |   ✅ (Own Data Only)   |  ✅ (Assigned Tasks Only)  |
| **Pelanggan Aktif**      |     ✅ (All & Isolir)      | ✅ (Own Data & Isolir) | ✅ (Semua untuk referensi) |
| **Pemeliharaan**         |    ✅ (All & Delegate)     |           ❌           |  ✅ (Assigned Tasks Only)  |
| **Dismantle**            |    ✅ (All & Delegate)     |           ❌           |  ✅ (Assigned Tasks Only)  |
| **Manajemen ODP**        |             ✅             |           ❌           |             ❌             |
| **PPPoE Aktif**          |  ✅ (Real-time Mikrotik)   |           ❌           |             ❌             |
| **Monitoring (Traffic)** |  ✅ (Real-time Mikrotik)   |           ❌           |             ❌             |
| **Keuangan Teknisi**     |             ✅             |           ❌           |      ✅ (Own Income)       |
| **Akun / Users**         |             ✅             |           ❌           |             ❌             |
| **Log Aktivitas**        |             ✅             |           ❌           |             ❌             |

---

## 4. Spesifikasi Fitur Utama & Modul (Flow Aplikasi)

### 4.1. Modul Autentikasi & Akun

- **Login:** Menggunakan Email & Password via API Laravel Sanctum.
- **2FA (Opsional):** Jika user mengaktifkan Google Authenticator di Web, Mobile harus menyediakan form OTP setelah login.
- **Logout:** Menghapus token di sisi perangkat dan server.

### 4.2. Dashboard Overview

Tampilan dashboard dinamis berdasarkan _Role_:

- **Administrator:**
    - Statistik Total PO, PO Bulan Lalu, Total Aktivasi, Total Pending.
    - Grafik (Charts): Tren Aktivasi Mingguan, Top Teknisi, Top Sales, Tren Maintenance & Dismantle.
    - Daftar Aktivitas Terbaru (Log Sistem).
- **Sales:**
    - Total Pelanggan Aktif (miliknya).
    - Total Pelanggan Terisolir (berdasarkan deteksi IP `172.30.*.*` dari MikroTik).
- **Teknisi:**
    - Pengerjaan Aktivasi PO (Sabtu - Jumat): Bekerja Sendiri, Berdua, Bertiga, Total.
    - Pengerjaan Maintenance (Tgl 29 bulan lalu - 28 bulan ini): Sendiri, Berdua, Bertiga, Total.
    - Daftar Tugas Delegasi Khusus (Urgent/Expired cards).

### 4.3. Modul Pre-Order (Pemasangan Baru)

- **Flow Sales:**
    - Input data prospek (Nama, Email, WA, Paket, Alamat, Kordinat Maps).
    - Status awal: `Pending`.
- **Flow Administrator:**
    - Mendelegasikan (Assign) PO yang pending ke Teknisi tertentu.
- **Flow Teknisi:**
    - Menerima PO yang didelegasikan.
    - **Aktivasi (Selesai):** Input PPPoE User, PPPoE Pass, Jenis Modem, SN Modem, Pembayaran. Upload 3 Foto (Lokasi, Router, Speedtest).
    - **Unspek (Gagal/Batal):** Memasukkan alasan kenapa pemasangan gagal (misal: tiang penuh). Status berubah menjadi `Unspek`.
- **Flow Lanjutan:** Administrator dapat meninjau PO Unspek dan mengubahnya kembali menjadi Pending (Re-Open).

### 4.4. Modul Pelanggan

- **Daftar Pelanggan Aktif:** Menampilkan list pelanggan yang sudah diaktivasi.
- **Pelanggan Terisolir (Peringatan):** (Hanya untuk Admin & Sales terkait). Ditampilkan di bagian teratas. Mendeteksi user dengan IP MikroTik `172.30.*.*`. Terdapat tombol akses cepat ke WhatsApp pelanggan.
- **Detail Pelanggan:** Menggunakan _Bottom Sheet_ atau navigasi halaman baru berisi: Biodata, Data Teknis (termasuk SN ONT, User PPPoE), dan Dokumentasi (Foto pemasangan).
- **Action:** Admin dapat menghapus (Soft Delete) atau me-restore pelanggan.

### 4.5. Modul Pemeliharaan (Maintenance)

- **Flow Admin:** Menerima komplain -> Membuat tiket Maintenance -> Assign ke Teknisi -> Melampirkan Foto Kendala.
- **Flow Teknisi:**
    - Melihat tiket di tab "Tugas Delegasi Khusus".
    - Klik "Ambil / Selesaikan".
    - **Form Penyelesaian:**
        1. Memilih _Partner_ kerja (Teknisi lain yang ikut ke lokasi).
        2. Memilih **Kategori Kendala** (ONT, Kabel Fiber Optic, Lainnya).
        3. _Jika ONT:_ Sistem memunculkan **SN ONT Lama**, Teknisi wajib menginput **SN ONT Baru**. (Aplikasi akan otomatis mengupdate SN di tabel Customer).
        4. Input penjelasan solusi teknis.
        5. Upload 4 Foto Wajib: Sebelum Perbaikan, Sesudah Perbaikan, Speedtest, Selfie Tim.
- **Hasil:** Admin dapat melihat detail hasil pengerjaan lengkap dengan foto dan data perubahan SN.

### 4.6. Modul Dismantle (Pencabutan)

- **Flow Admin:** Membuat tiket Dismantle -> Assign ke Teknisi.
- **Flow Teknisi:** Mengerjakan tiket -> Upload 2 foto (Perangkat yang ditarik & Rumah Pelanggan) -> Input Keterangan.
- **Post-Action:** Setelah selesai, pelanggan otomatis dihapus (_Soft Delete_) dari sistem agar tidak tertagih bulan depannya.

### 4.7. Modul Infrastruktur (Khusus Admin)

- **Manajemen ODP:** List lokasi ODP, koordinat (terintegrasi dengan Maps/Leaflet di mobile), Port Terpakai vs Total Port.
- **PPPoE Aktif:**
    - List _Real-time_ user yang terhubung di Router MikroTik.
    - Menampilkan: Uptime (Format HH:MM:SS), Remote Address, Caller ID (MAC), dan Traffic (RX/TX Byte).
    - Di Mobile, grafik _sparkline_ bisa diganti dengan indikator _up/down_ sederhana untuk menghemat _resource rendering_.
- **Monitoring:** Pengecekan status router MikroTik (Online/Offline) dan total traffic interface utama.

### 4.8. Modul Keuangan Teknisi

- **Fitur:** Menghitung estimasi pendapatan teknisi berdasarkan rumus tarif dasar dibagi jumlah orang (partner) yang mengerjakan aktivasi.
- **Filter:** Berdasarkan bulan & tahun tertentu.

### 4.9. Modul Tracking Lokasi (Background/Foreground)

- **Fitur:** Aplikasi mobile teknisi secara berkala mengirim koordinat GPS (`latitude`, `longitude`) ke backend `/api/location/update`.
- **Admin View:** Admin dapat melihat posisi terkini (Live Tracking) para teknisi di atas peta.

---

## 5. Dokumentasi API (Status: READY & IMPLEMENTED)
Seluruh endpoint di bawah ini sudah tersedia di `routes/api.php` dan dilindungi oleh `auth:sanctum`.

### 5.1. Auth & Profile
* `POST /api/login` : Login user (mendapatkan token).
* `POST /api/logout` : Menghapus token aktif.
* `GET /api/me` : Informasi profil user yang sedang login.

### 5.2. Dashboard & Stats
* `GET /api/dashboard/stats` : Mengambil data kartu statistik dan data grafik (Chart) sesuai role.

### 5.3. Pre-Order & Aktivasi
* `GET /api/pre-orders` : List PO (Support filter status & sales).
* `POST /api/pre-orders` : Sales input PO baru (termasuk upload foto).
* `POST /api/pre-orders/{id}/delegate` : Admin menugaskan teknisi.
* `POST /api/pre-orders/{id}/unspek` : Teknisi menandai kegagalan pasang.
* `POST /api/pre-orders/{id}/reopen` : Admin membuka kembali status unspek.
* `POST /api/customers` : **(Aksi Aktivasi)** Teknisi menyelesaikan pemasangan (Modem, PPPoE, Foto).

### 5.4. Pelanggan & Isolir
* `GET /api/customers` : List pelanggan aktif.
* `GET /api/customers/isolir` : List khusus pelanggan yang terdeteksi suspend di MikroTik.
* `DELETE /api/customers/{id}` : Admin melakukan penonaktifan (Soft Delete).
* `POST /api/customers/{id}/restore` : Admin mengaktifkan kembali pelanggan.

### 5.5. Maintenance & Dismantle
* `POST /api/maintenances/{id}/complete` : Teknisi upload hasil perbaikan (inc. Logic ONT SN).
* `POST /api/dismantles/{id}/complete` : Teknisi upload hasil pencabutan.

### 5.6. Infrastruktur & Monitoring
* `GET /api/odps` : List manajemen ODP dan koordinat.
* `GET /api/mikrotik/traffic` : Data real-time RX/TX Bandwidth.
* `GET /api/mikrotik/status` : Status koneksi router.
* `GET /api/mikrotik/pppoe-active` : List user online di MikroTik (Real-time via Cache).

### 5.7. Administrator Only
* `GET /api/accounts` : Manajemen User & Role.
* `GET /api/activity-logs` : Log riwayat sistem.
* `GET /api/finance` : Laporan estimasi pendapatan teknisi.

---

---

## 6. Persyaratan Non-Fungsional (Mobile App)

- **State Management:** Disarankan menggunakan `Provider`, `Riverpod`, atau `GetX` untuk mengelola data state dan API cache di perangkat.
- **Routing:** `go_router` atau `auto_route` untuk manajemen navigasi dan _Role-guarding_.
- **Penyimpanan Lokal:** `shared_preferences` atau `flutter_secure_storage` untuk menyimpan _Sanctum Token_ dan data User.
- **Kamera & Lokasi:** Memerlukan integrasi `image_picker` (kamera & galeri) dan `geolocator` (mendapatkan titik koordinat saat pemasangan PO/Maintenance).
- **Maps:** Menggunakan `flutter_map` atau `google_maps_flutter` untuk memvisualisasikan data Titik Koordinat ODP dan Lokasi Pelanggan.
- **Kinerja Jaringan:**
    - Wajib menggunakan kompresi gambar (_image compression_) di sisi Flutter sebelum mengirim 3-4 foto sekaligus pada modul Aktivasi/Maintenance untuk menghemat bandwidth.
    - Menerapkan _Debouncing_ pada fitur pencarian (Search).

---

## 7. Panduan UI/UX Mobile

### 7.1 Karakteristik Desain & Tema (Modern UI/UX)

- **Design System & Tokens:** Mengadopsi prinsip _Modern Clean & Spacious_ dengan memanfaatkan _white space_ yang cukup untuk meningkatkan keterbacaan serta mengurangi beban kognitif pengguna.
- **Palet Warna (Tailwind-based):**
    - **Primary & Brand:** Blue / Sky Blue (Aksen utama untuk interaksi penting, tombol aktif, dan penanda navigasi).
    - **Semantic/Status:** Emerald (Success), Amber (Warning), Rose (Danger).
    - **Surface & Background:** Slate-50 (Main background aplikasi) dan Pure White `#FFFFFF` (Latar belakang komponen Card/Form).
- **Elevasi & Komponen:**
    - Menggunakan komponen _Cards_ dengan sudut tumpul (_border-radius_: `12px` hingga `16px`) untuk menciptakan kesan visual yang modern dan lembut.
    - **Shadow:** Menggunakan _soft-shadow_ (efek elevasi tipis/subtle) untuk memisahkan konten dari background secara elegan tanpa membuat layar terlihat penuh atau kotor.

### 7.2 Navigasi Utama: Floating Navbar

- **Struktur & Tata Letak:** Menggunakan _Floating Bottom Navigation Bar_ yang melayang di atas konten utama dengan jarak (_padding_) sebesar `16px` dari sisi bawah, kiri, dan kanan layar ponsel.
- **Visual & Estetika:** Memiliki latar belakang Pure White atau semi-transparan (_glassmorphism_ tipis dengan efek _backdrop-blur_), sudut membulat sempurna (_border-radius_: `24px`), dan dilengkapi dengan _soft shadow_ bertipe _ambient_.
- **Responsivitas & Batasan:** Memuat maksimal 4 hingga 5 menu utama yang paling sering diakses. Menu yang sedang aktif akan ditandai dengan perubahan warna menjadi warna _Primary_ dan disertai animasi indikator perpindahan yang halus (_micro-interactions_).

### 7.3 Transformasi Data: List/Card View & Bottom Sheet Menu

- **Layout Adaptif:** Dilarang keras menggunakan komponen tabel konvensional pada layar mobile. Seluruh data tabel kompleks dari versi web wajib ditransformasikan menjadi bentuk **Card View** yang disusun secara vertikal (_scrollable_).
- **Hierarki Informasi:** Setiap _Card_ hanya diperbolehkan menampilkan informasi kritikal (misalnya: ID Transaksi, Status Utama, Total Harga, dan Tanggal).
- **Bottom Sheet Menu (Detail & Action):** Informasi sekunder atau detail lengkap disembunyikan dari halaman utama. Ketika salah satu _Card_ di-tap oleh pengguna, sistem akan memicu _Action Sheet_ atau _Bottom Sheet Menu_ yang muncul dari bawah layar (_slide up_).
    - **Ketentuan Bottom Sheet:** Harus menutup maksimal 75% dari tinggi layar (kecuali jika ditarik penuh oleh pengguna ke atas), memiliki _handle bar_ (garis penunjuk) di bagian atas, dan latar belakang di belakangnya (_overlay_) secara otomatis berubah menjadi gelap (_backdrop-dimmed_ 50%) untuk menjaga fokus pengguna.

### 7.4 Penanganan Formulir Panjang (Complex Forms)

- **Multi-step Form (Stepper):** Untuk proses input data yang panjang dan kompleks (seperti Aktivasi PO atau Pendaftaran), wajib dipecah menjadi beberapa langkah menggunakan komponen _Linear Stepper_ dengan batasan maksimal 3 sampai 4 langkah saja agar pengguna tidak merasa lelah.
- **Sticky Action Button:** Tombol aksi utama (seperti "Lanjut", "Simpan", atau "Kirim") harus bersifat _sticky_ atau melayang tepat di bagian atas _floating navbar_. Aturan ini memastikan pengguna tidak perlu melakukan _scroll_ panjang ke bagian paling bawah formulir hanya untuk menekan tombol aksi.
- **Real-time Validation:** Validasi input (pesan error atau indikator sukses) harus muncul secara _inline_ tepat di bawah _text field_ yang bersangkutan sesaat setelah pengguna selesai mengetik dan berpindah fokus (_on blur_), bukan mendadak muncul setelah tombol _submit_ tekanan.

---

## 8. Catatan Rencana Pengembangan

- Developer Flutter **wajib** menyesuaikan pengiriman foto dengan tipe `MultipartRequest` di protokol HTTP Dart.
- Sistem API MikroTik di Laravel sudah dibuat menggunakan Cache (oleh _Background Worker_). Aplikasi Flutter cukup melakukan _HTTP GET Request_ biasa, tidak perlu terkoneksi WebSockets secara langsung jika hanya menggunakan _polling_ 10 detik.
