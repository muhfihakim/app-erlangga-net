# 📱 Dokumentasi API Mobile Pelanggan (Android & iOS)
**Sistem Billing & Otomasi ISP — Erlangga Net**

Dokumentasi ini disiapkan khusus untuk Mobile Developer (Flutter / React Native / Native Android & iOS) untuk integrasi dan pengujian API pelanggan.

---

## 🌐 Konfigurasi Lingkungan (Environment)

| Parameter | Nilai / Konfigurasi |
|---|---|
| **Base URL Staging** | `https://stage.erlangga.net/api` |
| **Base URL Produksi** | `https://app.erlangga.net/api` |
| **Format Data** | `application/json` |
| **Autentikasi** | Bearer Token (Laravel Sanctum) |

### Standar Request Header
Untuk seluruh request API, wajib menyertakan header berikut:
```http
Accept: application/json
Content-Type: application/json
```
Untuk endpoint yang **memerlukan autentikasi (Protected)**, tambahkan header:
```http
Authorization: Bearer <TOKEN_HASIL_LOGIN>
```

---

## 📑 Ringkasan Daftar Endpoint API

### A. Screen-Oriented API (1 Screen = 1 API Endpoint — Rekomendasi Utama Mobile App)
| No | Layar Mobile (Flutter Tab) | Method | Endpoint URL | Akses | Widget Terkait & Deskripsi |
|:---:|---|:---:|---|:---:|---|
| **1** | **Tab 1: Home Screen** | `GET` | `/api/dashboard`<br>`/api/screens/home` | Protected | `StatusSection`, `InfoPaketSection`, `TagihanSection`<br>Status akun (Aktif/Terisolir), status internet (Online/Offline), paket aktif, & ringkasan tagihan |
| **2** | **Tab 2: Riwayat Screen** | `GET` | `/api/riwayat`<br>`/api/screens/history` | Protected | `BillingCard`, `BillingDetailScreen`<br>Daftar riwayat faktur tagihan lengkap |
| **3** | **Tab 3: Notifikasi Screen** | `GET` | `/api/notifications`<br>`/api/screens/notifications` | Protected | `NotificationCard`, `NotificationDetailScreen`<br>Feed notifikasi tagihan, payment, & info |
| | **Action Notifikasi** | `POST` | `/api/notifications/{id}/read` | Protected | Tandai 1 notifikasi tertentu telah dibaca |
| | | `POST` | `/api/notifications/read-all` | Protected | Tandai seluruh notifikasi telah dibaca |
| **4** | **Tab 4: Profil Screen** | `GET` | `/api/profile`<br>`/api/screens/profile` | Protected | `ProfileAccordionCard`, `ProfileDetailRow`<br>Detail identitas pelanggan, teknis & kontak CS |
| **5** | **Direct Checkout / Bayar** | `POST` | `/api/pay` | Protected | `TagihanSection`, `BillingDetailScreen`<br>Generate URL pembayaran Xendit |

### B. Modular API (Fine-Grained Endpoints)
| No | Modul | Method | Full URL (Staging) | Akses | Deskripsi |
|:---:|---|:---:|---|:---:|---|
| **1** | **Autentikasi & Push Token** | `POST` | `https://stage.erlangga.net/api/login` | Public | Login pelanggan via ID Pelanggan / Username & Sandi (bisa sertakan `token_fcm`) |
| | | `GET` | `https://stage.erlangga.net/api/me` | Protected | Cek profil & masa aktif sesi login saat ini |
| | | `POST` | `https://stage.erlangga.net/api/logout` | Protected | Logout & hapus token akses |
| | | `POST` | `https://stage.erlangga.net/api/fcm-token` | Protected | Daftarkan / perbarui token FCM perangkat Android & iOS |
| **2** | **Pembayaran & Tagihan** | `GET` | `https://stage.erlangga.net/api/payments/status` | Protected | Ringkasan billing, status isolir, jatuh tempo & total tunggakan |
| | | `GET` | `https://stage.erlangga.net/api/payments/invoices` | Protected | Riwayat & daftar tagihan (`?status=unpaid` / `paid`) |
| | | `GET` | `https://stage.erlangga.net/api/payments/invoices/{id}` | Protected | Detail item rincian faktur tagihan |
| | | `POST` | `https://stage.erlangga.net/api/payments/pay` | Protected | Generate URL Checkout Pembayaran Xendit (VA, QRIS, dll) |
| | | `POST` | `https://stage.erlangga.net/api/payments/invoices/{id}/check-status` | Protected | Cek status pembayaran real-time setelah checkout |
| **3** | **Akun & Pemakaian** | `GET` | `https://stage.erlangga.net/api/account/info` | Protected | Detail akun pelanggan, paket langganan, IP & info teknis |
| | | `GET` | `https://stage.erlangga.net/api/account/usage` | Protected | Status online/offline, uptime, traffic & kuota pemakaian |
| **4** | **Kontrol ONT (TR-069)** | `GET` | `https://stage.erlangga.net/api/device/wifi` | Protected | Status modem ONT, sinyal optik (dBm), SSID & Sandi Wi-Fi |
| | | `GET` | `https://stage.erlangga.net/api/device/hosts` | Protected | Daftar HP / Laptop yang sedang terhubung ke Wi-Fi |
| | | `POST` | `https://stage.erlangga.net/api/device/wifi` | Protected | Ganti SSID dan Password Wi-Fi langsung ke ONT |
| | | `POST` | `https://stage.erlangga.net/api/device/reboot` | Protected | Perintah restart / reboot modem ONT jarak jauh |
| **5** | **Notifikasi & FCM** | `GET` | `https://stage.erlangga.net/api/notifications` | Protected | Feed riwayat notifikasi tagihan, bayar, & pemeliharaan |
| | | `POST` | `https://stage.erlangga.net/api/notifications/{id}/read` | Protected | Tandai 1 notifikasi tertentu sebagai telah dibaca |
| | | `POST` | `https://stage.erlangga.net/api/notifications/read-all` | Protected | Tandai semua notifikasi pelanggan sebagai telah dibaca |

---

## 1️⃣ Modul Autentikasi

### 1.1 Login Pelanggan
Digunakan saat pelanggan membuka aplikasi dan memasukkan kredensial login.

- **Method**: `POST`
- **URL**: `https://stage.erlangga.net/api/login`
- **Headers**:
  ```http
  Accept: application/json
  Content-Type: application/json
  ```
- **Request Body (JSON)**:
  ```json
  {
    "id_pelanggan": "23114957687",
    "password": "password_pppoe_pelanggan",
    "device_name": "Flutter Android - Xiaomi 13",
    "token_fcm": "dZxyz...token_fcm_dari_hp_opsional..."
  }
  ```
  *(Catatan: Field `id_pelanggan` juga dapat diisi dengan username PPPoE pelanggan. Parameter `token_fcm` atau `fcm_token` bersifat opsional dan jika dikirim akan langsung menautkan token FCM perangkat ke akun pelanggan).*

- **Response `200 OK` (Berhasil)**:
  ```json
  {
    "success": true,
    "message": "Login berhasil.",
    "token": "86|ebNoPomYpZclQbfHhXWuG5pfzPKkuhgIDA7qMbNOd3239cb9",
    "data": {
      "customer": {
        "id": 1,
        "id_pelanggan": "23114957687",
        "nama_lengkap": "Nurul Adhira",
        "no_whatsapp": "082191285271",
        "alamat_lengkap": "Blok Tegal Kalapa RT. 14 RW 10 Dangdeur Subang",
        "jenis_modem": "EPON",
        "sn_perangkat": "ZTEGC1234567"
      },
      "paket": {
        "id": 1,
        "nama_paket": "Paket Home 20 Mbps",
        "kecepatan": "20 Mbps",
        "harga": 150000,
        "total_harga": 166500
      },
      "layanan": {
        "pppoe_username": "nurul_tegal",
        "status_invoice": "terbayar",
        "tanggal_aktif": "2026-09-20",
        "unpaid_invoices_count": 0,
        "has_unpaid_bill": false
      }
    }
  }
  ```

- **Response `200 OK` (Sandi / Akun Salah atau Validasi Gagal)**:
  > *Catatan: Untuk menghindari loop logout / auto-redirect interceptor HTTP di Flutter, endpoint login mengembalikan HTTP 200 OK dengan flag `success: false`.*
  ```json
  {
    "success": false,
    "message": "ID Pelanggan atau Sandi salah.",
    "errors": {
      "id_pelanggan": [
        "Akun pelanggan tidak ditemukan."
      ]
    }
  }
  ```

- **Response `200 OK` (Validasi Kosong / Gagal)**:
  ```json
  {
    "success": false,
    "message": "ID Pelanggan wajib diisi.",
    "errors": {
      "id_pelanggan": [
        "ID Pelanggan atau Username wajib diisi."
      ]
    }
  }
  ```

---

### 1.2 Profil Sesi Aktif (`/me`)
Memeriksa apakah token pelanggan masih valid dan mengambil informasi dasar profil.

- **Method**: `GET`
- **URL**: `https://stage.erlangga.net/api/me`
- **Headers**:
  ```http
  Accept: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Response `200 OK` (Berhasil)**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "id_pelanggan": "23114957687",
      "nama_lengkap": "Nurul Adhira",
      "no_whatsapp": "082191285271",
      "alamat_lengkap": "Blok Tegal Kalapa RT. 14 RW 10 Dangdeur Subang",
      "paket": {
        "nama_paket": "Paket Home 20 Mbps",
        "kecepatan": "20 Mbps",
        "harga": 150000,
        "total_harga": 166500
      },
      "pppoe_username": "nurul_tegal",
      "status_layanan": "Aktif",
      "tanggal_aktif": "2026-09-20"
    }
  }
  ```

- **Response `401 Unauthorized` (Token Kadaluarsa / Hilang)**:
  ```json
  {
    "message": "Unauthenticated."
  }
  ```

---

### 1.3 Logout Pelanggan
Menghapus token sesi aktif di database saat pelanggan menekan tombol Logout.

- **Method**: `POST`
- **URL**: `https://stage.erlangga.net/api/logout`
- **Headers**:
  ```http
  Accept: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "message": "Logout berhasil."
  }
  ```

---

### 1.4 Registrasi & Update Token FCM Pelanggan (`/fcm-token`)
Digunakan oleh aplikasi mobile (Android / iOS) untuk mendaftarkan atau memperbarui token Firebase Cloud Messaging (FCM) perangkat ke server. Endpoint ini dipanggil ketika aplikasi mobile mendapatkan device token dari Firebase SDK (misal saat inisialisasi aplikasi atau saat token FCM di-refresh).

- **Method**: `POST`
- **URL Staging**: `https://stage.erlangga.net/api/fcm-token` *(Alias: `https://stage.erlangga.net/api/update-fcm-token`)*
- **Headers**:
  ```http
  Accept: application/json
  Content-Type: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Request Body (JSON)**:
  ```json
  {
    "token_fcm": "f5kL9_xYz1234567890abcdefghijklmnopqrstuvwxyz..."
  }
  ```
  *(Catatan: Anda juga bisa menggunakan key `"fcm_token"`, keduanya diterima oleh server).*

- **Response `200 OK` (Berhasil Disimpan)**:
  ```json
  {
    "success": true,
    "message": "Token FCM berhasil diperbarui.",
    "fcm_token": "f5kL9_xYz1234567890abcdefghijklmnopqrstuvwxyz..."
  }
  ```

- **Menghapus Token FCM (Misal saat User Opt-Out / Logout Bersih)**:
  Kirim body kosong atau string kosong:
  ```json
  {
    "token_fcm": ""
  }
  ```
  Response `200 OK`:
  ```json
  {
    "success": true,
    "message": "Token FCM berhasil dihapus.",
    "fcm_token": null
  }
  ```

- **Response `401 Unauthorized` (Sesi Kadaluarsa)**:
  ```json
  {
    "message": "Unauthenticated."
  }
  ```

---

## 2️⃣ Modul Pembayaran & Payment Gateway

### 💡 Panduan Siklus Status Tagihan & Link Pembayaran (Mobile Best Practice)
Untuk memastikan UX aplikasi mobile mulus tanpa kendala link kedaluwarsa atau error di halaman payment gateway:
- **Nilai `status_invoice` (Level Akun/Ringkasan):**
  - `"belum_bayar"`: Pelanggan memiliki minimal 1 faktur aktif yang belum dilunasi.
  - `"terbayar"`: Pelanggan tidak memiliki tunggakan tagihan.
  - `"non_billing"`: Pelanggan dibebaskan dari penagihan (misal: koneksi internal/kantor).
- **Nilai `status` & `status_invoice` (Level Item Faktur di Riwayat):**
  - `"unpaid"` (`status_badge: "Belum Bayar"`): Faktur belum dibayar dan belum melewati jatuh tempo.
  - `"expired"` (`status_badge: "Kedaluwarsa"`, `is_expired: true`): Faktur belum dibayar dan telah melewati tanggal jatuh tempo (`due_date`).
  - `"paid"` (`status_badge: "Lunas"`): Faktur telah lunas terbayar.
- **Perilaku `invoice_url` / `xendit_invoice_url`:**
  - Jika faktur masih aktif (`unpaid`) dan sudah pernah dibuatkan link, `invoice_url` berisi URL checkout Xendit aktif.
  - Jika faktur belum pernah dibuatkan link, sudah kedaluwarsa (`is_expired: true`), atau sudah lunas (`paid`), `invoice_url` **selalu berupa string kosong `""` (bukan `null`)** agar aman dari error type-casting pada Flutter/Dart (`type 'Null' is not a subtype of type 'String'`).
  - Saat pelanggan menekan tombol **"Bayar Sekarang"** pada faktur yang `invoice_url`-nya kosong (`""`), aplikasi cukup memanggil `POST /api/pay` atau `POST /api/payments/pay`. Backend secara otomatis akan menerbitkan link checkout Xendit baru dengan masa aktif 24 jam.

---

### 2.1 Ringkasan Billing & Tagihan Aktif
Menampilkan widget kartu status tagihan di dashboard utama aplikasi. Memberikan informasi apakah status koneksi aktif, isolir, atau menunggu pembayaran.

- **Method**: `GET`
- **URL**: `https://stage.erlangga.net/api/payments/status`
- **Headers**:
  ```http
  Accept: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```

- **Response `200 OK` (Kondisi Sudah Lunas / Aktif)**:
  ```json
  {
    "success": true,
    "data": {
      "status_layanan": "aktif",
      "status_invoice": "terbayar",
      "tanggal_jatuh_tempo": "2026-11-20",
      "sisa_hari": 18,
      "is_overdue": false,
      "total_tunggakan": 0,
      "unpaid_count": 0,
      "tagihan_aktif": null
    }
  }
  ```

- **Response `200 OK` (Kondisi Ada Tagihan Belum Dibayar)**:
  ```json
  {
    "success": true,
    "data": {
      "status_layanan": "menunggu_pembayaran",
      "status_invoice": "belum_bayar",
      "tanggal_jatuh_tempo": "2026-11-05",
      "sisa_hari": 3,
      "is_overdue": false,
      "total_tunggakan": 166500,
      "unpaid_count": 1,
      "tagihan_aktif": {
        "id": 142,
        "invoice_number": "INV-202611-0001",
        "amount": 150000,
        "tax": 16500,
        "discount": 0,
        "total": 166500,
        "status": "unpaid",
        "due_date": "2026-11-05",
        "xendit_invoice_url": "https://checkout.xendit.co/web/6725ec389140ab12"
      }
    }
  }
  ```
  *(Catatan: Jika `is_overdue` bernilai `true` dan `unpaid_count > 0`, maka `status_layanan` bernilai `isolir`).*

---

### 2.2 Riwayat & Daftar Faktur Tagihan
Menampilkan seluruh riwayat tagihan bulanan pelanggan.

- **Method**: `GET`
- **URL**: `https://stage.erlangga.net/api/payments/invoices`
- **Query Parameter (Opsional)**:
  - `?status=unpaid` *(Hanya tagihan tertunggak)*
  - `?status=paid` *(Hanya tagihan lunas)*
  - Contoh: `https://stage.erlangga.net/api/payments/invoices?status=paid`
- **Headers**:
  ```http
  Accept: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 142,
        "invoice_number": "INV-202611-0001",
        "amount": 150000,
        "tax": 16500,
        "discount": 0,
        "total": 166500,
        "status": "unpaid",
        "due_date": "2026-11-05",
        "payment_method": null,
        "xendit_invoice_url": "https://checkout.xendit.co/web/6725ec389140ab12",
        "paid_at": null,
        "created_at": "2026-11-01 00:00:00"
      },
      {
        "id": 115,
        "invoice_number": "INV-202610-0012",
        "amount": 150000,
        "tax": 16500,
        "discount": 0,
        "total": 166500,
        "status": "paid",
        "due_date": "2026-10-05",
        "payment_method": "Xendit - BCA_VA",
        "xendit_invoice_url": "https://checkout.xendit.co/web/66de46781290abcdef",
        "paid_at": "2026-10-03 14:22:10",
        "created_at": "2026-10-01 00:00:00"
      }
    ]
  }
  ```

---

### 2.3 Detail Rincian Faktur Tertentu
Mengambil rincian faktur tertentu beserta riwayat transaksinya.

- **Method**: `GET`
- **URL**: `https://stage.erlangga.net/api/payments/invoices/142`
- **Headers**:
  ```http
  Accept: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": {
      "id": 142,
      "invoice_number": "INV-202611-0001",
      "paket_nama": "Paket Home 20 Mbps",
      "amount": 150000,
      "tax": 16500,
      "discount": 0,
      "total": 166500,
      "status": "unpaid",
      "due_date": "2026-11-05",
      "payment_method": null,
      "xendit_invoice_url": "https://checkout.xendit.co/web/6725ec389140ab12",
      "created_at": "2026-11-01 00:00:00",
      "transactions": []
    }
  }
  ```

---

### 2.4 Melakukan Pembayaran (Bayar via Xendit Payment Gateway)
Mengambil link checkout pembayaran aktif atau membuat sesi invoice baru di Payment Gateway Xendit (Mendukung BCA/BRI/Mandiri/BNI Virtual Account, QRIS, Alfamart/Indomaret, GoPay, OVO, ShopeePay, Kartu Kredit).

- **Method**: `POST`
- **URL**: `https://stage.erlangga.net/api/payments/pay`
- **Headers**:
  ```http
  Accept: application/json
  Content-Type: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Request Body (JSON - Opsional)**:
  ```json
  {
    "invoice_id": 142
  }
  ```
  *(Catatan: Jika `invoice_id` dikosongkan `{}` sistem secara otomatis memilih tagihan unpaid paling awal yang harus dibayar).*

- **Response `200 OK` (Berhasil)**:
  ```json
  {
    "success": true,
    "message": "Checkout payment gateway berhasil dibuat.",
    "invoice_id": 142,
    "invoice_number": "INV-202611-0001",
    "total": 166500,
    "payment_url": "https://checkout.xendit.co/web/6725ec389140ab12"
  }
  ```
  > **Panduan Integrasi WebView Mobile (Flutter / Android / iOS):**
  > Backend telah mengonfigurasi callback redirect URL otomatis pada checkout Xendit:
  > - **URL Sukses:** `https://stage.erlangga.net/payment/success?status=success&invoice_id={id}`
  >   *(Memenuhi kondisi interceptor: `lowerUrl.contains('/success')` dan `lowerUrl.contains('status=success')`)*
  > - **URL Gagal / Dibatalkan:** `https://stage.erlangga.net/payment/failure?status=failed&invoice_id={id}`
  >   *(Memenuhi kondisi interceptor: `lowerUrl.contains('/failure')` dan `lowerUrl.contains('status=failed')`)*
  > 
  > Begitu pelanggan selesai membayar di WebView, Xendit otomatis me-redirect ke URL sukses di atas sehingga WebView dapat langsung dideteksi oleh aplikasi, ditutup otomatis, dan memicu `_handleSuccessProcess()` secara instan.

- **Response `400 Bad Request` (Sudah Lunas)**:
  ```json
  {
    "success": false,
    "message": "Tagihan ini sudah lunas terbayar."
  }
  ```

---

### 2.5 Cek / Sinkronisasi Real-Time Status Pembayaran
Dipanggil setelah pelanggan selesai membayar di WebView / In-App Browser untuk memverifikasi apakah status sudah berubah menjadi lunas secara instan.

- **Method**: `POST` *(atau `GET`)*
- **URL**: `https://stage.erlangga.net/api/payments/invoices/142/check-status`
- **Headers**:
  ```http
  Accept: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Response `200 OK` (Sudah Terbayar)**:
  ```json
  {
    "success": true,
    "status": "paid",
    "payment_method": "Xendit - BCA VA",
    "message": "Pembayaran lunas terverifikasi."
  }
  ```
- **Response `200 OK` (Masih Menunggu)**:
  ```json
  {
    "success": true,
    "status": "pending",
    "message": "Status pembayaran saat ini: PENDING"
  }
  ```

---

## 3️⃣ Modul Informasi Akun & Penggunaan (Account & Usage)

### 3.1 Informasi Profil Akun & Paket Langganan
Mengambil data detail pelanggan, paket internet aktif, IP address, router gateway, dan rincian teknis.

- **Method**: `GET`
- **URL**: `https://stage.erlangga.net/api/account/info`
- **Headers**:
  ```http
  Accept: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": {
      "profil": {
        "id": 1,
        "id_pelanggan": "23114957687",
        "nama_lengkap": "Nurul Adhira",
        "no_whatsapp": "082191285271",
        "alamat_lengkap": "Blok Tegal Kalapa RT. 14 RW 10 Dangdeur Subang",
        "email": "nuruladhira@gmail.com"
      },
      "paket": {
        "id": 1,
        "nama_paket": "Paket Home 20 Mbps",
        "kecepatan": "20 Mbps",
        "harga": 150000,
        "pajak": 16500,
        "diskon": 0,
        "total_harga": 166500,
        "deskripsi": "Akses internet unlimited 24 jam tanpa FUP"
      },
      "teknis": {
        "pppoe_username": "nurul_tegal",
        "jenis_modem": "EPON",
        "sn_perangkat": "ZTEGC1234567",
        "ip_address": "10.10.20.45",
        "router": "MikroTik Gateway"
      },
      "layanan": {
        "status_layanan": "Aktif",
        "status_invoice": "terbayar",
        "tanggal_aktif": "2026-09-20",
        "tipe_pembayaran": "prepaid",
        "periode_billing": "fixed"
      }
    }
  }
  ```

---

### 3.2 Informasi Penggunaan (Traffic Kuota & Status Koneksi)
Mengambil data pemakaian traffic download/upload bulan berjalan (dari FreeRADIUS `radacct`), status koneksi saat ini (online/offline), uptime perangkat, dan riwayat sesi koneksi.

- **Method**: `GET`
- **URL**: `https://stage.erlangga.net/api/account/usage`
- **Headers**:
  ```http
  Accept: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": {
      "koneksi": {
        "status": "online",
        "ip_address": "10.10.20.45",
        "mac_address": "48:8D:36:12:34:56",
        "online_since": "2026-11-01 06:15:30",
        "uptime": "1 Hari 5 Jam 33 Menit",
        "uptime_seconds": 106380
      },
      "penggunaan_kuota": {
        "periode": "November 2026",
        "total_download": "42.50 GB",
        "total_upload": "8.12 GB",
        "total_traffic": "50.62 GB",
        "download_bytes": 45634027520,
        "upload_bytes": 8718909440,
        "total_bytes": 54352936960,
        "total_sesi": 4,
        "tipe_kuota": "Unlimited (Tanpa FUP)"
      },
      "paket": {
        "nama_paket": "Paket Home 20 Mbps",
        "kecepatan": "20 Mbps"
      },
      "riwayat_sesi": [
        {
          "ip_address": "10.10.20.45",
          "waktu_konek": "01/11/2026 06:15",
          "waktu_putus": "Aktif Sekarang",
          "durasi": "1 Hari 5 Jam 33 Menit",
          "download": "12.40 GB",
          "upload": "2.10 GB",
          "total": "14.50 GB",
          "status": "online"
        },
        {
          "ip_address": "10.10.20.45",
          "waktu_konek": "28/10/2026 10:00",
          "waktu_putus": "01/11/2026 06:14",
          "durasi": "3 Hari 20 Jam 14 Menit",
          "download": "30.10 GB",
          "upload": "6.02 GB",
          "total": "36.12 GB",
          "status": "selesai"
        }
      ]
    }
  }
  ```

---

## 4️⃣ Modul Kontrol Perangkat Modem & Wi-Fi (TR-069 ACS)

Sistem secara otomatis mengaitkan akun pelanggan dengan perangkat ONT di GenieACS melalui Serial Number (`sn_perangkat`) atau username dial PPPoE.

### 4.1 Status ONT, Sinyal Optik & Wi-Fi
Mengambil informasi status modem, sinyal optik (RX Power), suhu, status radio Wi-Fi 2.4GHz & 5GHz, SSID, serta password Wi-Fi saat ini.

- **Method**: `GET`
- **URL**: `https://stage.erlangga.net/api/device/wifi`
- **Headers**:
  ```http
  Accept: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Response `200 OK` (Berhasil)**:
  ```json
  {
    "success": true,
    "data": {
      "device": {
        "device_id": "202BC1-ZTEGC1234567",
        "serial_number": "ZTEGC1234567",
        "model": "F609",
        "manufacturer": "ZTE",
        "is_online": true,
        "rx_power": "-19.45 dBm",
        "temperature": "42.0 °C",
        "pon_mode": "GPON/EPON",
        "last_inform": "2026-11-02 11:30:00"
      },
      "wlan_2_4g": {
        "ssid": "ERLANGGA-NET-NURUL",
        "password": "sandi_wifi_rahasia",
        "channel": "6",
        "status": "Up",
        "enable": true
      },
      "wlan_5g": {
        "ssid": "ERLANGGA-NET-NURUL-5G",
        "password": "sandi_wifi_rahasia",
        "channel": "149",
        "status": "Up",
        "enable": true
      }
    }
  }
  ```

- **Response `404 Not Found` (Modem Belum Terhubung ke ACS)**:
  ```json
  {
    "success": false,
    "message": "Perangkat ONT belum terdaftar di ACS atau belum terhubung.",
    "data": {
      "device": {
        "serial_number": "ZTEGC1234567",
        "jenis_modem": "EPON",
        "is_online": false
      },
      "wlan1": null,
      "wlan5": null
    }
  }
  ```

---

### 4.2 Daftar Gadget Terhubung (Connected Hosts)
Melihat daftar gadget (HP, laptop, Smart TV, dll.) yang sedang terhubung ke router ONT pelanggan.

- **Method**: `GET`
- **URL**: `https://stage.erlangga.net/api/device/hosts`
- **Headers**:
  ```http
  Accept: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": {
      "total_connected": 3,
      "total_recorded": 5,
      "hosts": [
        {
          "host_name": "Galaxy-S23-Ultra",
          "ip_address": "192.168.1.102",
          "mac_address": "8C:AA:B5:11:22:33",
          "interface_type": "802.11 (Wi-Fi 5GHz)",
          "active": true
        },
        {
          "host_name": "iPhone-14-Pro",
          "ip_address": "192.168.1.105",
          "mac_address": "B4:43:0D:44:55:66",
          "interface_type": "802.11 (Wi-Fi 2.4GHz)",
          "active": true
        },
        {
          "host_name": "Smart-TV-LivingRoom",
          "ip_address": "192.168.1.110",
          "mac_address": "00:E0:4C:77:88:99",
          "interface_type": "Ethernet (LAN 1)",
          "active": true
        }
      ]
    }
  }
  ```

---

### 4.3 Mengganti Nama SSID & Password Wi-Fi
Mengirimkan perintah perubahan konfigurasi SSID atau password Wi-Fi secara instan ke modem ONT via protokol TR-069.

- **Method**: `POST`
- **URL**: `https://stage.erlangga.net/api/device/wifi`
- **Headers**:
  ```http
  Accept: application/json
  Content-Type: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Request Body (JSON)**:
  ```json
  {
    "ssid": "ERLANGGA-NURUL-BARU",
    "password": "sandiBaruMinimal8Karakter",
    "wlan_index": 1
  }
  ```
  *(Catatan: `wlan_index` bernilai `1` untuk frekuensi 2.4GHz [default], atau `5` untuk frekuensi 5GHz).*

- **Ketentuan Validasi**:
  - `ssid`: Wajib diisi, string, 1 - 32 karakter.
  - `password`: Opsional / string, 8 - 63 karakter.
  - `wlan_index`: Opsional, integer `1` atau `5`.

- **Response `200 OK` (Berhasil)**:
  ```json
  {
    "success": true,
    "message": "Perubahan nama dan sandi Wi-Fi berhasil dikirim ke perangkat ONT!",
    "data": {
      "ssid": "ERLANGGA-NURUL-BARU",
      "wlan_index": 1
    }
  }
  ```

- **Response `422 Unprocessable Entity` (Sandi Terlalu Pendek)**:
  ```json
  {
    "message": "The password field must be at least 8 characters.",
    "errors": {
      "password": [
        "The password field must be at least 8 characters."
      ]
    }
  }
  ```

---

### 4.4 Reboot / Restart Modem ONT
Mengirimkan perintah `reboot` jarak jauh ke modem ONT via TR-069 ketika pelanggan mengalami gangguan internet atau ingin me-restart modem tanpa mencabut adaptor.

- **Method**: `POST`
- **URL**: `https://stage.erlangga.net/api/device/reboot`
- **Headers**:
  ```http
  Accept: application/json
  Content-Type: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Request Body**: Kosong `{}`
- **Response `200 OK` (Berhasil)**:
  ```json
  {
    "success": true,
    "message": "Perintah reboot berhasil dikirim. Perangkat ONT akan restart dalam beberapa detik."
  }
  ```

- **Response `404 Not Found` (Modem Sedang Offline)**:
  ```json
  {
    "success": false,
    "message": "Perangkat ONT tidak ditemukan di ACS atau sedang offline."
  }
  ```

---

## 5️⃣ Modul Notifikasi & Riwayat Pemberitahuan

### 5.1 Riwayat Feed Notifikasi Pelanggan
Mengambil daftar riwayat notifikasi pelanggan (tagihan baru terbit, konfirmasi pembayaran invoice, pengumuman pemeliharaan jaringan, dan sambutan selamat datang). Endpoint ini digunakan langsung untuk Tab Notifikasi di Flutter.

- **Method**: `GET`
- **URL**: `https://stage.erlangga.net/api/notifications` *(Alias: `https://stage.erlangga.net/api/screens/notifications`)*
- **Headers**:
  ```http
  Accept: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Response `200 OK` (Berhasil)**:
  ```json
  {
    "success": true,
    "message": "Daftar notifikasi berhasil dimuat.",
    "unread_count": 1,
    "data": [
      {
        "id": 12,
        "type": "invoice_unpaid",
        "title": "Tagihan November 2026 Siap Dibayar",
        "message": "Tagihan internet periode November 2026 sebesar Rp 166.500 telah terbit. Jatuh tempo pada 20 November 2026.",
        "body": "Tagihan internet periode November 2026 sebesar Rp 166.500 telah terbit. Jatuh tempo pada 20 November 2026.",
        "data": {
          "invoice_id": 105,
          "action_url": "/billing/105"
        },
        "is_read": false,
        "created_at": "2026-11-01 08:00:00",
        "action_url": "/billing/105",
        "invoice_id": 105
      },
      {
        "id": 1,
        "type": "general",
        "title": "Selamat Datang di BBS Mobile, Nurul Adhira!",
        "message": "Kelola tagihan, cek status perangkat ONT, dan nikmati koneksi internet super cepat tanpa hambatan bersama Erlangga Network.",
        "body": "Kelola tagihan, cek status perangkat ONT, dan nikmati koneksi internet super cepat tanpa hambatan bersama Erlangga Network.",
        "data": {
          "action_url": "/dashboard"
        },
        "is_read": true,
        "created_at": "2026-09-20 10:00:00",
        "action_url": "/dashboard",
        "invoice_id": null
      }
    ]
  }
  ```

---

### 5.2 Tandai Notifikasi Tertentu Telah Dibaca
Memperbarui status `is_read = true` saat item notifikasi tertentu dibuka atau ditekan oleh pelanggan di aplikasi.

- **Method**: `POST`
- **URL**: `https://stage.erlangga.net/api/notifications/{id}/read`
- **Headers**:
  ```http
  Accept: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Request Body**: Kosong `{}`
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "message": "Notifikasi ditandai telah dibaca.",
    "id": 12
  }
  ```

---

### 5.3 Tandai Seluruh Notifikasi Telah Dibaca
Menandai seluruh notifikasi pelanggan yang belum dibaca menjadi sudah dibaca.

- **Method**: `POST`
- **URL**: `https://stage.erlangga.net/api/notifications/read-all`
- **Headers**:
  ```http
  Accept: application/json
  Authorization: Bearer <TOKEN_PELANGGAN>
  ```
- **Request Body**: Kosong `{}`
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "message": "Seluruh notifikasi berhasil ditandai telah dibaca."
  }
  ```

---

## 6️⃣ Contoh Perintah Pengujian (cURL)

Berikut cURL template cepat untuk pengujian di terminal atau diimpor ke Postman / Bruno / Insomnia:

### Step 1: Login
```bash
curl -X POST "https://stage.erlangga.net/api/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "id_pelanggan": "23114957687",
    "password": "123",
    "device_name": "Test Terminal",
    "token_fcm": "fcm_token_sample_123"
  }'
```

### Step 2: Daftarkan / Perbarui Token FCM
```bash
curl -X POST "https://stage.erlangga.net/api/fcm-token" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <GANTI_DENGAN_TOKEN>" \
  -d '{
    "token_fcm": "f5kL9_xYz1234567890abcdefghijklmnopqrstuvwxyz"
  }'
```

### Step 3: Cek Feed Notifikasi Pelanggan
```bash
curl -X GET "https://stage.erlangga.net/api/notifications" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <GANTI_DENGAN_TOKEN>"
```

### Step 4: Cek Status Billing & Tagihan
```bash
curl -X GET "https://stage.erlangga.net/api/payments/status" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <GANTI_DENGAN_TOKEN>"
```

### Step 5: Dapatkan URL Pembayaran Xendit
```bash
curl -X POST "https://stage.erlangga.net/api/payments/pay" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <GANTI_DENGAN_TOKEN>" \
  -d '{}'
```

### Step 6: Cek Informasi Wi-Fi & Sinyal Optik
```bash
curl -X GET "https://stage.erlangga.net/api/device/wifi" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <GANTI_DENGAN_TOKEN>"
```

### Step 7: Ganti Password Wi-Fi
```bash
curl -X POST "https://stage.erlangga.net/api/device/wifi" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <GANTI_DENGAN_TOKEN>" \
  -d '{
    "ssid": "ERLANGGA-TEST",
    "password": "passwordBaru123",
    "wlan_index": 1
  }'
```
