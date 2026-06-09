# Dokumen Kebutuhan Produk (PRD)
## Erlangga Net Mobile - Sistem Manajemen ISP Terintegrasi

**Versi:** 2.0 (Edisi yang Ditingkatkan)  
**Status:** Draf untuk Implementasi  
**Teknologi:** Flutter (Dart)  
**Target OS:** Android  
**Backend:** Laravel 12 API (Sanctum Auth)

---

## 1. Ringkasan Proyek
Aplikasi mobile Erlangga Net adalah solusi manajemen operasional ISP (Internet Service Provider) yang dirancang untuk meningkatkan efisiensi tim lapangan (Teknisi), produktivitas tim pemasaran (Sales), dan visibilitas manajerial (Administrator). Aplikasi ini mengusung estetika **Modern Liquid Glass** yang bersih, beranimasi, dan responsif.

---

## 2. Peran & Tanggung Jawab Pengguna

### 2.1. Administrator (Mode Dewa)
*   **Tanggung Jawab:** Pengawasan seluruh ekosistem bisnis, delegasi tugas, manajemen infrastruktur, dan audit.
*   **Fitur Utama:** Dashboard global, manajemen pengguna (Akun), log audit, manajemen ODP, integrasi MikroTik (Pemantauan Real-time), laporan keuangan teknisi (Payroll), dan kontrol penuh data pelanggan.

### 2.2. Sales (Mesin Pertumbuhan)
*   **Tanggung Jawab:** Akuisisi pelanggan baru dan pemeliharaan hubungan pelanggan pasif/terisolir.
*   **Fitur Utama:** Input Pre-Order (PO), pelacakan status PO (Pending/Aktif/Unspek), pemantauan pelanggan terisolir (untuk penagihan/retensi), dan statistik performa pribadi.

### 2.3. Teknisi (Pahlawan Operasional)
*   **Tanggung Jawab:** Eksekusi teknis di lapangan (Aktivasi, Perbaikan, Pencabutan) dan pemeliharaan infrastruktur.
*   **Fitur Utama:** Daftar tugas delegasi (Aktivasi, Pemeliharaan, Dismantle), update data OLT, manajemen aset/stok barang, pelacakan lokasi (Location Tracking), dan transparansi pendapatan per-tugas.

---

## 3. Spesifikasi Desain UI/UX

### 3.1. Identitas Visual
*   **Tema:** **Clean Liquid Glass (Glassmorphism)**.
*   **Palet Warna:**
    *   Gradasi Utama: `LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [Color(0xFFE0F2FE), Color(0xFFBAE6FD)])` (Gradasi Biru Muda).
    *   Aksen: Sky Blue (Sky-500).
    *   Basis Glass: Putih dengan opasitas 70% & Backdrop Filter Blur (15px-20px).
*   **Efek:** Bayangan lembut (soft shadows), border glow tipis, dan transisi liquid.

### 3.2. Tata Letak & Komponen
*   **Grid Responsif:** Tampilan yang padat namun teratur (Ramai & Detail namun Terorganisir).
*   **Navbar Melayang (Floating):** Navbar dengan efek glassmorphism dan animasi pill-indicator.
*   **Bottom Sheets:** Digunakan untuk Filter, Detail Pelanggan, Form Cepat, dan Konfirmasi Aksi.
*   **Animasi:** 
    *   Transisi Hero untuk detail item.
    *   Animasi daftar bertahap (staggered list).
    *   State loading menggunakan efek shimmer atau gelembung liquid.
    *   Scrolling halus dengan fisika elastis.

---

## 4. Fitur Utama & Alur Aplikasi

### 4.1. Dashboard (Pusat Kendali)
*   **Widget Kaya Fitur:** Kartu statistik melayang, chart interaktif (aktivasi mingguan), daftar tugas hari ini, dan notifikasi aktivitas terbaru.
*   **Indikator Real-time:** Indikator status router (Online/Offline) dan monitor trafik mini (khusus Admin).

### 4.2. Alur Pre-Order & Aktivasi
1.  **Sales:** Input PO melalui form multi-tahap (Biodata -> Alamat/Koordinat -> Upload Foto KTP/Rumah).
2.  **Admin:** Notifikasi masuk -> Menugaskan (Assign) Teknisi.
3.  **Teknisi:** Menerima tugas -> Menuju lokasi -> Klik "Aktivasi".
4.  **Form Aktivasi:** Input PPPoE, Serial Number (SN) Modem, Jenis Modem. Upload Foto (Router, Lokasi, Speedtest).
5.  **Berhasil:** Data pindah ke daftar "Pelanggan Aktif".

### 4.3. Alur Pemeliharaan (Maintenance) & Dismantle
*   **Maintenance:** Teknisi menerima tiket -> Pilih Partner -> Input Solusi -> Jika ganti ONT, sistem memvalidasi SN lama vs baru secara otomatis.
*   **Dismantle:** Teknisi mencabut perangkat -> Upload foto bukti -> Pelanggan otomatis non-aktif (Soft Delete).

### 4.4. Kontrol OLT & Infrastruktur (Lanjutan)
*   **Dashboard OLT:** Scan port OLT untuk melihat ONU yang terkoneksi vs belum terkonfigurasi.
*   **Provisioning:** Tombol satu klik untuk cek ID ONU berikutnya yang tersedia dan eksekusi skrip konfigurasi langsung ke OLT.
*   **Peta ODP:** Visualisasi titik ODP di peta dengan informasi port yang tersedia.

### 4.5. Manajemen Aset & Inventaris
*   **Pelacakan Stok:** Melihat sisa stok kabel, modem, dan material lainnya.
*   **Transaksi:** Teknisi mencatat pengambilan barang (Keluar) atau admin menambah stok (Masuk).

---

## 5. Dokumentasi API Teknis (Pemetaan Lengkap)

### 5.1. Autentikasi
| Metode | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| POST | `/api/login` | Login pengguna, mengembalikan Bearer Token. |
| POST | `/api/logout` | Mencabut token aktif. |
| GET | `/api/me` | Mendapatkan detail profil pengguna saat ini. |

### 5.2. Dashboard & Statistik
| Metode | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| GET | `/api/dashboard/stats` | Data statistik global/berbasis peran dan data grafik. |

### 5.3. Manajemen Pre-Order
| Metode | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| GET | `/api/pre-orders` | Daftar semua PO dengan paginasi & filter. |
| POST | `/api/pre-orders` | Membuat PO baru (Multipart/Form-Data). |
| GET | `/api/pre-orders/{id}` | Informasi detail dari satu PO. |
| PUT | `/api/pre-orders/{id}` | Memperbarui data PO. |
| POST | `/api/pre-orders/{id}/unspek` | Menandai PO sebagai "Unspek" (Kegagalan teknis). |
| POST | `/api/pre-orders/{id}/approve-unspek` | Persetujuan admin untuk status unspek. |
| POST | `/api/pre-orders/{id}/reopen` | Membuka kembali PO unspek ke status pending. |
| DELETE| `/api/pre-orders/{id}` | Menghapus catatan PO. |

### 5.4. Manajemen Pelanggan
| Metode | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| GET | `/api/customers` | Daftar pelanggan aktif (Dapat dicari). |
| POST | `/api/customers` | **Aksi:** Aktivasi PO menjadi Pelanggan. |
| GET | `/api/customers/isolir` | Daftar pelanggan dengan IP 172.30.* (Suspend). |
| GET | `/api/customers/{id}` | Profil lengkap pelanggan & data teknis. |
| PUT | `/api/customers/{id}` | Update info teknis/foto pelanggan. |
| DELETE| `/api/customers/{id}` | Menonaktifkan pelanggan (Soft Delete). |
| POST | `/api/customers/{id}/restore` | Mengaktifkan kembali pelanggan yang dihapus. |

### 5.5. Pemeliharaan & Perbaikan (Maintenance)
| Metode | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| GET | `/api/maintenances` | Daftar tiket pemeliharaan. |
| POST | `/api/maintenances` | Admin membuat tugas pemeliharaan. |
| GET | `/api/maintenances/{id}` | Informasi detail tugas pemeliharaan. |
| POST | `/api/maintenances/{id}/complete` | Teknisi mengunggah laporan penyelesaian. |
| DELETE| `/api/maintenances/{id}` | Menghapus catatan pemeliharaan. |

### 5.6. Dismantle (Pencabutan)
| Metode | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| GET | `/api/dismantles` | Daftar tugas dismantle. |
| POST | `/api/dismantles` | Admin membuat tugas dismantle. |
| GET | `/api/dismantles/{id}` | Informasi detail tugas dismantle. |
| POST | `/api/dismantles/{id}/complete` | Teknisi mengunggah laporan penyelesaian. |
| DELETE| `/api/dismantles/{id}` | Menghapus catatan dismantle. |

### 5.7. ODP & Infrastruktur
| Metode | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| GET | `/api/odps` | Daftar ODP dengan koordinat & statistik port. |
| POST | `/api/odps` | Membuat lokasi ODP baru. |
| PUT | `/api/odps/{id}` | Update info ODP. |
| DELETE| `/api/odps/{id}` | Menghapus ODP. |

### 5.8. MikroTik & Monitoring
| Metode | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| GET | `/api/mikrotik/traffic` | Trafik antarmuka RX/TX real-time. |
| GET | `/api/mikrotik/status` | Status konektivitas router. |
| GET | `/api/mikrotik/pppoe-active`| Daftar pengguna aktif di MikroTik. |
| GET | `/api/mikrotik/pppoe/data` | Daftar aktif PPPoE detail & dapat dicari. |

### 5.9. Manajemen OLT (ZTE C320/C300)
| Metode | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| GET | `/api/olt/dashboard-data` | Hasil scan port (ONU terkonfigurasi/Uncfg). |
| GET | `/api/olt/onu-details` | Metrik ONU spesifik (Power, Trafik, IP). |
| POST | `/api/olt/check-next-id` | Deteksi otomatis ID ONU berikutnya yang tersedia. |
| POST | `/api/olt/execute` | Menjalankan skrip konfigurasi via proxy Telnet. |

### 5.10. Aset & Inventaris
| Metode | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| GET | `/api/assets` | Daftar inventaris & tingkat stok. |
| POST | `/api/assets` | Membuat tipe aset baru. |
| GET | `/api/assets/transactions` | Riwayat mutasi stok. |
| POST | `/api/assets/transactions` | Mencatat stok Masuk/Keluar. |
| GET | `/api/assets/{id}` | Detail aset & riwayat. |
| PUT | `/api/assets/{id}` | Update deskripsi/info aset. |
| DELETE| `/api/assets/{id}` | Menghapus aset (jika tidak ada transaksi). |

### 5.11. Administrasi (Khusus Admin)
| Metode | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| GET | `/api/accounts` | Daftar manajemen pengguna. |
| POST | `/api/accounts` | Membuat akun pengguna baru. |
| GET | `/api/accounts/{id}` | Detail satu pengguna. |
| PUT | `/api/accounts/{id}` | Update info pengguna/kata sandi. |
| DELETE| `/api/accounts/{id}` | Menghapus akun pengguna. |
| POST | `/api/accounts/{id}/2fa/reset` | Reset Google 2FA untuk pengguna. |
| GET | `/api/activity-logs` | Log audit sistem. |
| GET | `/api/finance` | Laporan perhitungan pendapatan teknisi. |

### 5.12. Pelacakan Lokasi (Location Tracking)
| Metode | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| GET | `/api/locations/all` | Posisi live dari semua teknisi lapangan. |
| GET | `/api/locations/{user}` | Riwayat pergerakan teknisi spesifik. |
| POST | `/api/location/update` | (Mobile) Update koordinat GPS saat ini. |

---

## 6. Peta Jalan Pengembangan (Fase)
1.  **Fase 1: Fondasi & Auth** (Setup Flutter, UI Theme Engine, Login/Profil).
2.  **Fase 2: Modul Sales** (Formulir Pre-Order, Integrasi Lokasi/Peta).
3.  **Fase 3: Alur Kerja Teknisi** (Aktivasi, Maintenance, Dismantle dengan Unggah Foto).
4.  **Fase 4: Admin Lanjutan** (Kontrol OLT, Pemantauan MikroTik, Inventaris).
5.  **Fase 5: Penyempurnaan** (Animasi, Offline caching, Background tracking).

---
**Disusun oleh:** Gemini CLI Agent  
**Tanggal:** 08 Juni 2026  
**Kerahasiaan:** Hanya untuk Internal - Proyek Erlangga Net
