# 📱 PRD — Erlangga Net Mobile App (Android Flutter/Dart)

> **Versi:** 1.0.0
> **Tanggal:** Juni 2026
> **Platform:** Android (Flutter / Dart)
> **Backend API:** Laravel Sanctum (`/api/*` — sama dengan web app)
> **Base URL API:** `https://app.erlangga.net/api`

---

## 1. Ringkasan Eksekutif

Mobile app **Erlangga Net** adalah versi Android dari sistem manajemen operasional ISP (Internet Service Provider) yang telah ada di web. Aplikasi ini mereplikasi seluruh fungsionalitas web app secara identik dan menargetkan pengguna lapangan (Teknisi & Sales) serta manajemen (Administrator) agar dapat mengakses dan mengelola data secara real-time dari perangkat mobile mereka.

### Tujuan Bisnis
- Mempercepat alur kerja lapangan (aktivasi, maintenance, dismantle)
- Memungkinkan update data secara real-time tanpa harus ke kantor
- Meningkatkan efisiensi tim dengan notifikasi tugas secara langsung
- Memberikan visibilitas dashboard kepada manajemen di mana saja

---

## 2. Pengguna & Role

Aplikasi memiliki **3 role utama** berdasarkan field `jabatan` pada model User:

| Role | Nilai `jabatan` | Deskripsi |
|---|---|---|
| **Administrator** | `Administrator` atau `Web Developer` | Akses penuh ke semua modul. Dapat membuat/mengelola akun, melihat semua data, laporan, monitoring. |
| **Sales** | `Sales` | Akses terbatas: Pre-Order (milik sendiri), Pelanggan Aktif (milik sendiri), ODP (read-only), Dashboard personal. |
| **Teknisi** | `Teknisi` | Akses: Maintenance task (pending), Dismantle task (pending), Aktivasi pelanggan, Keuangan personal, ODP, OLT monitoring, Supporting Activity, Dashboard personal. |

### Deteksi Admin
```dart
bool isAdmin = user.jabatan == 'Administrator' || user.jabatan == 'Web Developer';
```

---

## 3. Arsitektur Teknis

### 3.1 Tech Stack
- **Framework:** Flutter (Dart)
- **State Management:** Riverpod atau BLoC
- **HTTP Client:** Dio (dengan interceptor untuk token Sanctum)
- **Storage Lokal:** flutter_secure_storage (untuk token), Hive/SharedPreferences (preferensi UI)
- **Image Handling:** image_picker + cached_network_image
- **Maps:** flutter_map + OpenStreetMap / google_maps_flutter
- **Charts:** fl_chart
- **Navigation:** GoRouter
- **Notifications:** firebase_messaging (FCM)

### 3.2 Autentikasi
Menggunakan **Laravel Sanctum Token-Based Authentication**:

```
POST /api/login
Body: { email, password, device_name: "android-flutter" }
Response: { token: "...", user: {...} }
```

Token disimpan di `flutter_secure_storage` dan dikirim sebagai `Authorization: Bearer {token}` pada setiap request.

### 3.3 File Upload
Semua foto dikirim sebagai `multipart/form-data`. Ukuran maksimal per file: **20 MB**.

---

## 4. Desain & UI/UX Guidelines

### 4.1 Color Palette (Identik dengan Web App)

| Token | Warna | Hex |
|---|---|---|
| Primary | Sky Blue | `#0ea5e9` |
| Primary Dark | Sky Dark | `#0284c7` |
| Primary Darker | Sky Darker | `#0369a1` |
| Primary Light | Sky Light | `#38bdf8` |
| Primary Lighter | Sky Lighter | `#e0f2fe` |
| Surface Dark | Slate 900 | `#0f172a` |
| Surface Secondary | Slate 800 | `#1e293b` |
| Surface Active | Navy | `#1e3a5f` |
| Text Primary | Slate 900 | `#0f172a` |
| Text Secondary | Slate 400 | `#94a3b8` |
| Background | Slate 100 | `#f1f5f9` |
| Success | Emerald | `#10b981` |
| Warning | Amber | `#f59e0b` |
| Danger | Red | `#ef4444` |
| Info | Blue | `#3b82f6` |

### 4.2 Tipografi
- **Font Utama:** `Inter` (dari Google Fonts)
- **Font Weights:** 400, 500, 600, 700, 800

### 4.3 Navigasi — Floating Bottom Navbar

Gunakan **Bottom Navigation Bar** berbentuk **floating** (rounded corners, shadow, glassmorphism):

```
┌─────────────────────────────────────────────┐
│          [Konten Halaman]                   │
│    ┌───────────────────────────────┐        │
│    │  🏠   📋   ⚙️   ☰ More      │  float  │
│    └───────────────────────────────┘        │
└─────────────────────────────────────────────┘
```

- Icon **"☰ More"** membuka **Bottom Sheet** berisi semua menu tambahan
- border-radius: 24px, background `rgba(15, 23, 42, 0.92)` + blur
- Active tab: warna `#38bdf8` + titik indikator
- Animasi transisi: slide fade antar tab

### 4.4 Bottom Sheet
Digunakan untuk:
- More Menu (semua menu tambahan)
- Form konfirmasi aksi (hapus, unspek, dll)
- Filter & sort data
- Preview / pilih foto
- Detail ringkas sebelum navigasi

### 4.5 Card & List Style
- Background: white dengan shadow ringan (elevation: 2)
- Border radius: 12px, padding: 16px
- Status badge: chip kecil berwarna

### 4.6 Animasi & Micro-interactions
- Page transition: slide dari kanan
- Loading: Shimmer skeleton
- Pull to refresh: custom indicator primary
- FAB: scale animation
- List: fade+slide staggered animation

---

## 5. Struktur Navigasi

### Administrator
```
Bottom Navbar: [Dashboard] [Pre-Order] [Pelanggan] [☰ More]
More Menu:
  - Maintenance         - Dismantle
  - Keuangan Teknisi    - Manajemen ODP
  - OLT Monitoring      - MikroTik Monitoring
  - PPPoE Active        - Manajemen Aset
  - Manajemen Akun      - Activity Log
  - Supporting Activity - Laporan (sub-menu)
  - Tracking Lokasi     - Profil Saya
```

### Sales
```
Bottom Navbar: [Dashboard] [Pre-Order] [Pelanggan] [☰ More]
More Menu: - ODP (Read Only) - Profil Saya
```

### Teknisi
```
Bottom Navbar: [Dashboard] [Maintenance] [Dismantle] [☰ More]
More Menu:
  - Aktivasi Pelanggan  - ODP
  - OLT Monitoring      - Keuangan Saya
  - Supporting Activity - Pre-Order (Read Only)
  - Profil Saya
```

---

## 6. Modul & Halaman Detail

---

### 6.1 Halaman Login

**Desain:**
- Background gradient dark (`#0f172a` → `#1e3a5f`)
- Logo app di tengah atas
- Card login glassmorphism
- Input floating label, toggle password visibility
- Tombol gradient primary + loading state

**API:**
```
POST /api/login
Body: { email, password, device_name: "android-flutter" }
Response 200: { token, user: { id, nama_lengkap, email, jabatan, ... } }
Response 422: { message, errors: { email: [...] } }
```

---

### 6.2 Dashboard

**API:** `GET /api/dashboard/stats`
**Akses:** Semua role (tampilan berbeda per role)

#### Dashboard Administrator
Stat Cards (Periode Billing 29-28):
- Total Pre-Order bulan ini
- Total Aktivasi bulan ini
- Total Pending PO
- Total Maintenance Selesai bulan ini

Charts (fl_chart):
- Line Chart: Tren aktivasi mingguan (5 minggu, Sabtu-Jumat)
- Donut Chart: Top 5 Teknisi Aktivasi
- Bar Chart Grouped: Top 5 Sales (Aktivasi vs Pending)
- Line Chart: Tren Maintenance harian
- Line Chart: Tren Dismantle harian
- Donut Chart: Top 5 Teknisi Maintenance
- Donut Chart: Top 5 Teknisi Dismantle
- Activity Log terbaru (10 item)

#### Dashboard Sales
- Total Pelanggan Saya (all time)
- Total Isolir Saya (dari MikroTik cache)
- Total PO Saya bulan ini

#### Dashboard Teknisi
- Maintenance Selesai Saya bulan ini
- Aktivasi Minggu Ini (Sabtu-Jumat)
- Breakdown: Sendiri / Berpasangan / Bertiga

---

### 6.3 Pre-Order

**API:**
```
GET    /api/pre-orders?status=pending&id_sales=5&page=1
POST   /api/pre-orders
GET    /api/pre-orders/{id}
PUT    /api/pre-orders/{id}
DELETE /api/pre-orders/{id}
POST   /api/pre-orders/{id}/unspek
POST   /api/pre-orders/{id}/approve-unspek
POST   /api/pre-orders/{id}/reopen
```

**Filter Chips:** Semua | Pending | Aktif | Berhenti | Unspek

**List Item:** Nama, Paket, Sales, Tanggal, Status Badge

**Role Akses:**
| Aksi | Admin | Sales | Teknisi |
|---|---|---|---|
| Lihat semua PO | ✅ | ❌ (sendiri) | ✅ Read-only |
| Tambah PO | ✅ | ✅ | ❌ |
| Edit PO | ✅ | ✅ (sendiri, pending) | ❌ |
| Hapus PO | ✅ | ✅ (sendiri, pending) | ❌ |
| Tandai Unspek | ✅ | ✅ | ❌ |
| Approve Unspek | ✅ | ❌ | ❌ |
| Reopen PO | ✅ | ❌ | ❌ |
| Aktivasi | ✅ | ❌ | ✅ (status Pending) |

**Form Tambah/Edit PO:**
| Field | Tipe | Wajib |
|---|---|---|
| Paket Internet | TextField | ✅ |
| Nama Lengkap | TextField | ✅ |
| No. Identitas (KTP/NIK) | TextField | ✅ (unik) |
| No. WhatsApp | TextField | ✅ |
| Alamat Email | TextField | ❌ |
| Alamat Lengkap | TextArea | ✅ |
| Titik Koordinat | TextField + 📍 Peta | ✅ |
| Foto KTP | ImagePicker | ❌ |
| Foto Rumah | ImagePicker | ❌ |

---

### 6.4 Pelanggan Aktif

**API:**
```
GET    /api/customers?page=1
GET    /api/customers/isolir
GET    /api/customers/{id}
POST   /api/customers           (multipart/form-data — Aktivasi)
PUT    /api/customers/{id}
DELETE /api/customers/{id}
POST   /api/customers/{id}/restore
```

**Stat Bar:** Total Aktif | Baru Bulan Ini | Baru Minggu Ini | Total Arsip

**Status Online/Offline:** dari MikroTik PPPoE cache (dot hijau/merah)

**Form Aktivasi:**
| Field | Tipe | Wajib |
|---|---|---|
| Pre-Order | Auto-select dari PO | ✅ |
| Teknisi Utama | Dropdown | ✅ |
| Teknisi Rekan | Multi-select chips | ❌ |
| ID Pelanggan | TextField | ✅ (unik) |
| PPPoE Username | TextField | ✅ (unik) |
| PPPoE Password | TextField | ✅ |
| Jenis Modem | Dropdown: EPON/GPON | ✅ |
| SN Perangkat | TextField | ✅ |
| Metode Pembayaran | Dropdown: Cash/Aplikasi | ✅ |
| Keterangan | TextArea | ❌ |
| Foto Petugas | ImagePicker (kamera/galeri) | ✅ |
| Foto Terpasang | ImagePicker | ✅ |
| Foto Speedtest | ImagePicker | ✅ |

**Role Akses:**
| Aksi | Admin | Sales | Teknisi |
|---|---|---|---|
| Lihat semua | ✅ | ✅ (sendiri) | ✅ |
| Aktivasi | ✅ | ❌ | ✅ |
| Edit data | ✅ | ❌ | ✅ |
| Nonaktifkan | ✅ | ❌ | ❌ |
| Restore | ✅ | ❌ | ❌ |

---

### 6.5 Maintenance

**API:**
```
GET    /api/maintenances?page=1
GET    /api/maintenances/{id}
POST   /api/maintenances              (Admin: buat tugas)
POST   /api/maintenances/{id}/complete (Teknisi: selesaikan + foto)
DELETE /api/maintenances/{id}
```

**Role Akses:**
| Aksi | Admin | Sales | Teknisi |
|---|---|---|---|
| Lihat | ✅ (semua) | ❌ | ✅ (Pending only) |
| Buat tugas | ✅ | ❌ | ❌ |
| Selesaikan | ❌ | ❌ | ✅ |
| Hapus | ✅ | ❌ | ❌ |

**Form Buat Maintenance (Admin):**
| Field | Tipe | Wajib |
|---|---|---|
| Pelanggan | Search/Select | ✅ |
| Keterangan Kendala | TextArea | ✅ |
| Foto Kendala Awal | ImagePicker | ❌ |

**Form Selesaikan Maintenance (Teknisi):**
| Field | Tipe | Wajib |
|---|---|---|
| Kategori Kendala | Dropdown: ONT/Kabel Fiber Optic/Lainnya | ✅ |
| SN Baru (jika ONT) | TextField (conditional) | Kondisional |
| Solusi Kendala | TextArea | ✅ |
| Teknisi Rekan | Multi-select | ❌ |
| Foto Sebelum Perbaikan | ImagePicker | ✅ |
| Foto Setelah Perbaikan | ImagePicker | ✅ |
| Foto Speedtest | ImagePicker | ✅ |
| Foto Selfie Teknisi | ImagePicker | ✅ |

---

### 6.6 Dismantle (Pencabutan)

**API:**
```
GET    /api/dismantles?page=1
GET    /api/dismantles/{id}
POST   /api/dismantles              (Admin: buat tugas)
POST   /api/dismantles/{id}/complete (Teknisi: selesaikan)
DELETE /api/dismantles/{id}
```

**Role Akses:** Sama seperti Maintenance

**Form Buat Dismantle (Admin):**
| Field | Tipe | Wajib |
|---|---|---|
| Pelanggan | Search/Select | ✅ |
| Keterangan Admin | TextArea | ❌ |

**Form Selesaikan Dismantle (Teknisi):**
| Field | Tipe | Wajib |
|---|---|---|
| Keterangan | TextArea | ✅ |
| Foto Perangkat (dicabut) | ImagePicker | ✅ |
| Foto Rumah (kondisi) | ImagePicker | ✅ |

> Setelah dismantle selesai, pelanggan otomatis di-soft delete.

---

### 6.7 Manajemen ODP

**API:**
```
GET    /api/odps
POST   /api/odps
PUT    /api/odps/{id}
DELETE /api/odps/{id}
```

**Stat Bar:** Total ODP | GPON | EPON

**Role Akses:**
| Aksi | Admin | Sales | Teknisi |
|---|---|---|---|
| Lihat | ✅ | ✅ Read-only | ✅ |
| Tambah/Edit/Hapus | ✅ | ❌ | ✅ |

**Form Tambah/Edit ODP:**
| Field | Tipe | Wajib |
|---|---|---|
| Jenis ODP | Dropdown: GPON/EPON | ✅ |
| Lokasi ODP | TextField | ✅ |
| Titik Koordinat | TextField + 📍 Peta | ✅ |
| Keterangan | TextArea | ❌ |
| Foto ODP | ImagePicker | ❌ |

---

### 6.8 OLT Monitoring

**API:**
```
GET  /api/olt/dashboard-data
GET  /api/olt/onu-details
POST /api/olt/check-next-id  (Admin only)
POST /api/olt/execute        (Admin only)
```

**Akses:** Administrator & Teknisi (Sales: forbidden)

**Tampilan:**
- Dashboard port utilization, jumlah ONU aktif
- Tabel ONU Details: ID, status, SN, port
- Admin: form check-next-id dan execute script

---

### 6.9 MikroTik Monitoring

**API:**
```
GET /api/mikrotik/traffic
GET /api/mikrotik/status
```

**Akses:** Administrator only

**Tampilan:**
- Status MikroTik (uptime, CPU, memory)
- Grafik traffic real-time (auto-refresh 5 detik)
- Interface statistics

---

### 6.10 PPPoE Active

**API:**
```
GET /api/mikrotik/pppoe-active
GET /api/mikrotik/pppoe/data
```

**Akses:** Administrator only

**Filter:** Semua | Online | Isolir (IP prefix 172.30.x.x)

---

### 6.11 Keuangan Teknisi

**API:**
```
GET /api/finance?teknisi_id=5&start_date=2026-05-01&end_date=2026-05-31
```

**Akses:** Administrator (semua teknisi), Teknisi (diri sendiri)

**Filter (Admin):** Pilih Teknisi + Rentang Tanggal

**Tampilan:**
- Total Pendapatan (summary card)
- Fee dasar per aktivasi
- List aktivasi: nama pelanggan, tanggal, jumlah teknisi, fee didapat

---

### 6.12 Manajemen Aset

**API:**
```
GET    /api/assets
GET    /api/assets/{id}
POST   /api/assets
PUT    /api/assets/{id}
DELETE /api/assets/{id}
GET    /api/assets/transactions
POST   /api/assets/transactions
```

**Akses:** Administrator only (lihat & kelola), Teknisi (lihat transaksi sendiri)

**Form Tambah/Edit Aset:**
| Field | Tipe | Wajib |
|---|---|---|
| Nama Aset | TextField | ✅ |
| Tipe | TextField | ✅ |
| Satuan (Unit) | TextField | ✅ |
| Deskripsi | TextArea | ❌ |

**Form Transaksi Aset:**
| Field | Tipe | Wajib |
|---|---|---|
| Aset | Dropdown | ✅ |
| Tipe Transaksi | Dropdown: Masuk/Keluar | ✅ |
| Jumlah | NumberField | ✅ |
| Tanggal | DatePicker | ✅ |
| Penerima/Pengirim (User) | Dropdown | ❌ |
| Nama Eksternal | TextField | ❌ |
| Keterangan | TextArea | ❌ |

---

### 6.13 Manajemen Akun

**API:**
```
GET    /api/accounts
GET    /api/accounts/{id}
POST   /api/accounts
PUT    /api/accounts/{id}
DELETE /api/accounts/{id}
POST   /api/accounts/{id}/2fa/reset
```

**Akses:** Administrator only

**Stat Bar:** Total User | Admin | Sales | Teknisi

**Form Tambah/Edit Akun:**
| Field | Tipe | Wajib |
|---|---|---|
| Nama Lengkap | TextField | ✅ |
| Email | TextField | ✅ (unik) |
| Password | PasswordField | ✅ (tambah), ❌ (edit) |
| NIK | TextField | ❌ |
| Jabatan | Dropdown: Administrator/Sales/Teknisi | ✅ |
| Jenis Kelamin | Dropdown | ❌ |
| Tanggal Lahir | DatePicker | ❌ |
| Alamat | TextArea | ❌ |

---

### 6.14 Activity Log

**API:** `GET /api/activity-logs`
**Akses:** Administrator only

**Tampilan:** Timeline dengan ikon per tipe aktivitas, nama user, deskripsi, waktu relatif

---

### 6.15 Laporan

**Akses:** Administrator only

**Sub-menu:**
- Laporan Pre-Order
- Laporan Aktivasi
- Laporan Maintenance (status=Selesai)
- Laporan Dismantle (status=Selesai)

**Fitur:** Filter rentang tanggal, tampil tabel, export (share PDF/Excel)

---

### 6.16 Supporting Activity (Perbantuan BBSNET)

**Akses:** Administrator & Teknisi (Sales: forbidden)

**Tipe Aktivitas:** `PO` | `Pemeliharaan` | `Buat ODP` | `Aktivasi Jalur`

**Form Tambah/Edit:**
| Field | Tipe | Wajib |
|---|---|---|
| Tipe Aktivitas | Dropdown | ✅ |
| Tanggal Aktivitas | DatePicker | ✅ |
| Teknisi Utama | Dropdown | ✅ |
| Teknisi Rekan | Multi-select | ❌ |
| Deskripsi | TextArea | ❌ |
| Foto | ImagePicker | ❌ (max 2MB) |

> **NOTE:** Perlu tambah endpoint di `api.php`:
> `GET/POST/PUT/DELETE /api/supporting`

---

### 6.17 Tracking Lokasi

**API:**
```
GET  /api/locations/all
GET  /api/locations/{user}
POST /api/location/update
```

**Tampilan (Admin):** Peta penuh, marker tiap teknisi, tap marker → Bottom Sheet info

**Auto-update lokasi:** Background geolocation + kirim koordinat berkala

---

### 6.18 Profil Pengguna

**API:** `GET /api/me`

**Tampilan:** Avatar inisial, info user, jabatan badge, tombol Logout

**Logout:**
```
POST /api/logout → hapus token → redirect Login
```

---

## 7. API Endpoints Lengkap

| Method | Endpoint | Deskripsi | Role |
|---|---|---|---|
| POST | `/login` | Login | Public |
| GET | `/me` | Info user | All |
| POST | `/logout` | Logout | All |
| GET | `/dashboard/stats` | Stats dashboard | All |
| GET | `/pre-orders` | Daftar PO | All |
| POST | `/pre-orders` | Tambah PO | Sales, Admin |
| GET | `/pre-orders/{id}` | Detail PO | All |
| PUT | `/pre-orders/{id}` | Update PO | Sales, Admin |
| DELETE | `/pre-orders/{id}` | Hapus PO | Sales, Admin |
| POST | `/pre-orders/{id}/unspek` | Tandai unspek | Sales, Admin |
| POST | `/pre-orders/{id}/approve-unspek` | Setujui unspek | Admin |
| POST | `/pre-orders/{id}/reopen` | Reopen PO | Admin |
| GET | `/customers` | Daftar pelanggan | All |
| POST | `/customers` | Aktivasi | Teknisi, Admin |
| GET | `/customers/isolir` | Daftar isolir | All |
| GET | `/customers/{id}` | Detail pelanggan | All |
| PUT | `/customers/{id}` | Update | Teknisi, Admin |
| DELETE | `/customers/{id}` | Nonaktifkan | Admin |
| POST | `/customers/{id}/restore` | Restore | Admin |
| GET | `/maintenances` | Daftar | All |
| POST | `/maintenances` | Buat tugas | Admin |
| GET | `/maintenances/{id}` | Detail | All |
| POST | `/maintenances/{id}/complete` | Selesaikan | Teknisi |
| DELETE | `/maintenances/{id}` | Hapus | Admin |
| GET | `/dismantles` | Daftar | All |
| POST | `/dismantles` | Buat tugas | Admin |
| GET | `/dismantles/{id}` | Detail | All |
| POST | `/dismantles/{id}/complete` | Selesaikan | Teknisi |
| DELETE | `/dismantles/{id}` | Hapus | Admin |
| GET | `/odps` | Daftar ODP | All |
| POST | `/odps` | Tambah ODP | Admin, Teknisi |
| PUT | `/odps/{id}` | Update ODP | Admin, Teknisi |
| DELETE | `/odps/{id}` | Hapus ODP | Admin, Teknisi |
| GET | `/assets` | Daftar aset | Admin |
| POST | `/assets` | Tambah aset | Admin |
| GET | `/assets/{id}` | Detail aset | Admin |
| PUT | `/assets/{id}` | Update aset | Admin |
| DELETE | `/assets/{id}` | Hapus aset | Admin |
| GET | `/assets/transactions` | Riwayat transaksi | Admin, Teknisi |
| POST | `/assets/transactions` | Catat transaksi | Admin, Teknisi |
| GET | `/accounts` | Daftar akun | Admin |
| POST | `/accounts` | Buat akun | Admin |
| GET | `/accounts/{id}` | Detail akun | Admin |
| PUT | `/accounts/{id}` | Update akun | Admin |
| DELETE | `/accounts/{id}` | Hapus akun | Admin |
| POST | `/accounts/{id}/2fa/reset` | Reset 2FA | Admin |
| GET | `/activity-logs` | Log aktivitas | Admin |
| GET | `/finance` | Data keuangan | Admin, Teknisi |
| GET | `/mikrotik/traffic` | Traffic | Admin |
| GET | `/mikrotik/status` | Status | Admin |
| GET | `/mikrotik/pppoe-active` | PPPoE aktif | Admin |
| GET | `/mikrotik/pppoe/data` | Data PPPoE | Admin |
| GET | `/olt/dashboard-data` | Dashboard OLT | Admin, Teknisi |
| GET | `/olt/onu-details` | Detail ONU | Admin, Teknisi |
| POST | `/olt/check-next-id` | Cek next ONU ID | Admin |
| POST | `/olt/execute` | Eksekusi script | Admin |
| GET | `/locations/all` | Semua lokasi | Admin |
| GET | `/locations/{user}` | Lokasi user | Admin |
| POST | `/location/update` | Update lokasi | All |

---

## 8. Matriks Permission Lengkap

| Modul | Administrator | Sales | Teknisi |
|---|---|---|---|
| Dashboard | ✅ Full | ✅ Terbatas | ✅ Terbatas |
| Pre-Order (semua) | ✅ CRUD | ✅ CRUD (sendiri) | 👁️ Read-only |
| Pre-Order (approve unspek) | ✅ | ❌ | ❌ |
| Pelanggan (semua) | ✅ CRUD | 👁️ Read (sendiri) | ✅ Create+Read+Edit |
| Pelanggan (hapus/restore) | ✅ | ❌ | ❌ |
| Maintenance (buat) | ✅ | ❌ | ❌ |
| Maintenance (selesaikan) | ❌ | ❌ | ✅ |
| Maintenance (lihat semua) | ✅ | ❌ | ✅ (Pending only) |
| Dismantle (buat) | ✅ | ❌ | ❌ |
| Dismantle (selesaikan) | ❌ | ❌ | ✅ |
| Dismantle (lihat semua) | ✅ | ❌ | ✅ (Pending only) |
| ODP (CRUD) | ✅ | 👁️ Read-only | ✅ CRUD |
| OLT Monitoring | ✅ | ❌ | ✅ |
| OLT Execute Script | ✅ | ❌ | ❌ |
| MikroTik Monitoring | ✅ | ❌ | ❌ |
| PPPoE | ✅ | ❌ | ❌ |
| Keuangan (semua teknisi) | ✅ | ❌ | 👁️ (sendiri) |
| Manajemen Aset | ✅ | ❌ | ❌ |
| Transaksi Aset | ✅ | ❌ | ✅ (sendiri) |
| Manajemen Akun | ✅ | ❌ | ❌ |
| Activity Log | ✅ | ❌ | ❌ |
| Laporan | ✅ | ❌ | ❌ |
| Supporting Activity | ✅ | ❌ | ✅ |
| Tracking Lokasi (lihat) | ✅ | ❌ | ❌ |
| Tracking Lokasi (update) | ✅ | ✅ | ✅ |

---

## 9. Alur Kerja Utama

### 9.1 Alur Aktivasi Pelanggan Baru
```
Sales: Buat Pre-Order → upload foto KTP & rumah → tandai koordinat peta
  ↓
Admin: Review PO → (opsional: Unspek jika lokasi tidak terjangkau)
  ↓
Teknisi: Buka PO Pending → Form Aktivasi → upload 3 foto → Submit
  ↓
Sistem: Customer dibuat, PO status = Aktif, Activity Log tercatat
```

### 9.2 Alur Maintenance
```
Admin: Buat Tugas Maintenance (pilih pelanggan, isi kendala, opsional foto awal)
  ↓
Teknisi: List Maintenance Pending → Pilih tugas → Ke lapangan
  ↓
Teknisi: Isi form selesai (kategori, solusi, 4 foto wajib) → Submit
  ↓
Sistem: Status = Selesai, SN diupdate (jika ONT), Activity Log tercatat
```

### 9.3 Alur Dismantle
```
Admin: Buat Tugas Dismantle (pilih pelanggan, isi keterangan)
  ↓
Teknisi: List Dismantle Pending → Ke lapangan → Cabut perangkat
  ↓
Teknisi: Isi form selesai (keterangan, 2 foto wajib) → Submit
  ↓
Sistem: Status = Selesai, Pelanggan soft-deleted, Activity Log tercatat
```

---

## 10. Komponen UI Reusable

```dart
- AppCard              // card standar shadow + border-radius
- StatusBadge          // chip status warna dinamis
- AppButton            // primary/secondary/danger + loading state
- AppTextField         // floating label + validasi
- AppImagePicker       // picker foto preview + hapus
- AppPhotoViewer       // full-screen viewer + zoom
- AppStatCard          // stat card dashboard
- AppSearchBar         // search + debounce
- AppFilterChips       // horizontal scrollable filter
- AppListTile          // standardized list item
- AppBottomSheet       // reusable wrapper
- AppConfirmSheet      // konfirmasi aksi
- AppMapPicker         // peta pilih koordinat
- AppLoadingOverlay    // loading blur overlay
- AppShimmer           // shimmer skeleton
- FloatingNavbar       // floating bottom navigation
- MoreMenuSheet        // bottom sheet semua menu
- AppPaginatedList     // infinite scroll + pagination
- AppEmptyState        // tampilan data kosong
- AppErrorState        // tampilan error/offline
```

---

## 11. Struktur Proyek Flutter

```
lib/
├── main.dart
├── app.dart                    # MaterialApp + GoRouter setup
├── core/
│   ├── constants/
│   │   ├── api_constants.dart  # Base URL, endpoints
│   │   └── app_colors.dart     # Color palette
│   ├── theme/
│   │   └── app_theme.dart
│   ├── utils/
│   │   ├── auth_interceptor.dart
│   │   ├── error_handler.dart
│   │   └── date_formatter.dart
│   └── widgets/                # Reusable widgets
├── data/
│   ├── datasources/remote/     # API calls
│   ├── datasources/local/      # Hive/SecureStorage
│   ├── models/                 # Dart model classes
│   └── repositories/
├── domain/repositories/        # Abstract interfaces
├── presentation/
│   ├── auth/
│   ├── dashboard/
│   ├── pre_orders/
│   ├── customers/
│   ├── maintenances/
│   ├── dismantles/
│   ├── odp/
│   ├── olt/
│   ├── monitoring/
│   ├── pppoe/
│   ├── finance/
│   ├── assets/
│   ├── accounts/
│   ├── activity_log/
│   ├── laporan/
│   ├── supporting/
│   ├── tracking/
│   └── profile/
└── providers/                  # Riverpod providers
```

---

## 12. Dependensi Flutter (pubspec.yaml)

```yaml
dependencies:
  flutter_riverpod: ^2.x.x
  go_router: ^14.x.x
  dio: ^5.x.x
  flutter_secure_storage: ^9.x.x
  hive_flutter: ^1.x.x
  google_fonts: ^6.x.x
  shimmer: ^3.x.x
  cached_network_image: ^3.x.x
  photo_view: ^0.15.x
  flutter_map: ^7.x.x
  latlong2: ^0.9.x
  fl_chart: ^0.68.x
  image_picker: ^1.x.x
  firebase_messaging: ^15.x.x
  flutter_local_notifications: ^17.x.x
  geolocator: ^13.x.x
  intl: ^0.19.x
  timeago: ^3.x.x
  share_plus: ^10.x.x
  path_provider: ^2.x.x
```

---

## 13. Checklist Halaman

| # | Halaman | Status |
|---|---|---|
| 1 | Login Page | ⬜ |
| 2 | Dashboard Admin | ⬜ |
| 3 | Dashboard Sales | ⬜ |
| 4 | Dashboard Teknisi | ⬜ |
| 5 | Daftar Pre-Order | ⬜ |
| 6 | Form Tambah/Edit PO | ⬜ |
| 7 | Detail Pre-Order | ⬜ |
| 8 | Daftar Pelanggan | ⬜ |
| 9 | Form Aktivasi | ⬜ |
| 10 | Detail Pelanggan | ⬜ |
| 11 | Daftar Maintenance | ⬜ |
| 12 | Form Buat Maintenance (Admin) | ⬜ |
| 13 | Form Selesaikan Maintenance (Teknisi) | ⬜ |
| 14 | Detail Maintenance | ⬜ |
| 15 | Daftar Dismantle | ⬜ |
| 16 | Form Buat Dismantle (Admin) | ⬜ |
| 17 | Form Selesaikan Dismantle (Teknisi) | ⬜ |
| 18 | Detail Dismantle | ⬜ |
| 19 | Daftar ODP | ⬜ |
| 20 | Form Tambah/Edit ODP | ⬜ |
| 21 | OLT Monitoring | ⬜ |
| 22 | MikroTik Monitoring | ⬜ |
| 23 | PPPoE Active | ⬜ |
| 24 | Keuangan Teknisi | ⬜ |
| 25 | Daftar Aset | ⬜ |
| 26 | Form Tambah/Edit Aset | ⬜ |
| 27 | Transaksi Aset | ⬜ |
| 28 | Manajemen Akun | ⬜ |
| 29 | Form Tambah/Edit Akun | ⬜ |
| 30 | Activity Log | ⬜ |
| 31 | Laporan Pre-Order | ⬜ |
| 32 | Laporan Aktivasi | ⬜ |
| 33 | Laporan Maintenance | ⬜ |
| 34 | Laporan Dismantle | ⬜ |
| 35 | Supporting Activity | ⬜ |
| 36 | Tracking Lokasi | ⬜ |
| 37 | Profil Pengguna | ⬜ |
| 38 | More Menu (Bottom Sheet) | ⬜ |
| 39 | Map Picker (Koordinat) | ⬜ |

---

## 14. Non-Functional Requirements

| Aspek | Requirement |
|---|---|
| Performance | App launch < 3 detik; list render < 1 detik |
| Responsiveness | Semua ukuran layar Android (4.5" - 7") |
| Offline | Tidak crash offline; tampilkan data cache & error jelas |
| Security | Token di Keystore (flutter_secure_storage); SSL pinning di production |
| File Size | APK < 50 MB |
| Android Version | Minimum Android 6.0 (API 23) |
| Accessibility | Label semantik pada semua tombol & ikon |
| Localisation | Bahasa Indonesia (default) |

---

## 15. Catatan Penting

> **PENTING — Supporting Activity:** Belum ada API endpoint di `api.php`. Perlu ditambahkan backend sebelum fitur ini diimplementasikan.

> **CATATAN — Laporan Export:** Web menggunakan Excel/PDF download. Untuk mobile, gunakan share_plus atau buka browser in-app.

> **TIP — Real-time MikroTik & OLT:** Gunakan timer polling 5-10 detik dan cancel timer saat halaman di-dispose untuk hemat baterai.

> **PERHATIAN — Ukuran Foto:** Max 20 MB per file. Pertimbangkan kompresi gambar di client sebelum upload untuk hemat bandwidth.

---

*Dokumen ini dibuat berdasarkan analisis kode sumber web app Erlangga Net (Laravel 11, Sanctum) — Juni 2026.*
