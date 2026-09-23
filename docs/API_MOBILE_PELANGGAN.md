# 📱 Dokumentasi API Mobile Pelanggan (Android & iOS)
**Aplikasi Manajemen Pelanggan - Erlangga Net**

Dokumentasi ini ditujukan untuk tim pengembang Mobile App (Flutter / React Native / Swift / Kotlin) dan pengujian integrasi via Postman.

---

## 📌 Ringkasan Konfigurasi API

- **Base URL Pengembangan (Lokal)**: `http://127.0.0.1:8000/api`
- **Base URL Server Produksi**: `https://app.erlangga.net/api` *(atau domain hosting aktif)*
- **Header Standar (Semua Request)**:
  ```http
  Accept: application/json
  Content-Type: application/json
  ```
- **Mekanisme Autentikasi**:
  - Pelanggan login menggunakan **`id_pelanggan`** dan **`pppoe_password`**.
  - Server mengembalikan **Bearer Token (Laravel Sanctum)**.
  - Untuk setiap endpoint yang terproteksi, sertakan header:
    ```http
    Authorization: Bearer <token_dari_login>
    ```

---

## 📑 Daftar Endpoint

| No | Modul | Method | Endpoint | Akses | Deskripsi |
|---|---|:---:|---|:---:|---|
| **1** | **Autentikasi** | `POST` | `/login` | Public | Login pelanggan (ID Pelanggan & Sandi PPPoE) |
| | | `GET` | `/me` | Protected | Cek sesi & data profil pelanggan yang sedang aktif |
| | | `POST` | `/logout` | Protected | Logout & revoke access token |
| **2** | **Pembayaran** | `GET` | `/payments/status` | Protected | Cek status layanan (Aktif/Isolir), jatuh tempo & tagihan berjalan |
| | | `GET` | `/payments/invoices` | Protected | Riwayat faktur tagihan (`?status=unpaid` / `paid`) |
| | | `GET` | `/payments/invoices/{id}` | Protected | Detail rincian faktur tertentu |
| | | `POST` | `/payments/pay` | Protected | Generate URL Checkout Payment Gateway Xendit |
| | | `POST` | `/payments/invoices/{id}/check-status` | Protected | Cek & sinkronisasi real-time status pembayaran |
| **3** | **Akun & Penggunaan** | `GET` | `/account/info` | Protected | Detail lengkap profil pelanggan, paket langganan & modem ONT |
| | | `GET` | `/account/usage` | Protected | Status koneksi real-time, uptime, dan kuota/traffic bulanan |
| **4** | **Kontrol Perangkat (ACS)** | `GET` | `/device/wifi` | Protected | Informasi SSID, Sandi Wi-Fi, dan status ONT via TR-069 |
| | | `GET` | `/device/hosts` | Protected | Daftar perangkat yang sedang terhubung ke Wi-Fi / LAN |
| | | `POST` | `/device/wifi` | Protected | Ubah Nama (SSID) dan Password Wi-Fi via TR-069 |
| | | `POST` | `/device/reboot` | Protected | Restart / Reboot perangkat ONT jarak jauh via TR-069 |

---

## 1️⃣ Modul Autentikasi

### 1.1 Login Pelanggan
Digunakan untuk login pertama kali pada mobile app.

- **Method**: `POST`
- **Endpoint**: `/login`
- **Akses**: Public
- **Request Body (JSON)**:
  ```json
  {
    "id_pelanggan": "23114957687",
    "password": "123",
    "device_name": "iPhone 15 Pro"
  }
  ```
  *(Catatan: Kolom `id_pelanggan` juga menerima `username` PPPoE).*

- **Response `200 OK` (Sukses)**:
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
        "no_whatsapp": "6282191285271",
        "alamat_lengkap": "Blok Tegal Kalapa RT. 14 RW 10 Dangdeur Subang",
        "jenis_modem": "EPON",
        "sn_perangkat": null
      },
      "paket": {
        "id": 1,
        "nama_paket": "Paket Bronze 112K",
        "kecepatan": "10 Mbps",
        "harga": 112000,
        "total_harga": 112000
      },
      "layanan": {
        "pppoe_username": "Nurultgl@erlangga.net",
        "status_invoice": "terbayar",
        "tanggal_aktif": "2026-09-20",
        "unpaid_invoices_count": 0,
        "has_unpaid_bill": false
      }
    }
  }
  ```

- **Response `401 Unauthorized` (Gagal)**:
  ```json
  {
    "success": false,
    "message": "ID Pelanggan atau Sandi salah.",
    "errors": {
      "id_pelanggan": ["Akun pelanggan tidak ditemukan."]
    }
  }
  ```

---

### 1.2 Profil Saya (`/me`)
Memeriksa apakah token masih valid dan mengambil informasi dasar pelanggan.

- **Method**: `GET`
- **Endpoint**: `/me`
- **Header**: `Authorization: Bearer <token>`
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "id_pelanggan": "23114957687",
      "nama_lengkap": "Nurul Adhira",
      "no_whatsapp": "6282191285271",
      "alamat_lengkap": "Blok Tegal Kalapa RT. 14 RW 10 Dangdeur Subang",
      "paket": {
        "nama_paket": "Paket Bronze 112K",
        "kecepatan": "10 Mbps",
        "harga": 112000,
        "total_harga": 112000
      },
      "pppoe_username": "Nurultgl@erlangga.net",
      "status_layanan": "Aktif",
      "tanggal_aktif": "2026-09-20"
    }
  }
  ```

---

### 1.3 Logout
Menghapus session token pada database.

- **Method**: `POST`
- **Endpoint**: `/logout`
- **Header**: `Authorization: Bearer <token>`
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "message": "Logout berhasil."
  }
  ```

---

## 2️⃣ Modul Pembayaran & Payment Gateway

### 2.1 Status Pembayaran & Tagihan Aktif
Menampilkan kartu ringkasan status billing di halaman utama mobile app.

- **Method**: `GET`
- **Endpoint**: `/payments/status`
- **Header**: `Authorization: Bearer <token>`
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": {
      "status_layanan": "aktif",
      "status_invoice": "terbayar",
      "tanggal_jatuh_tempo": "2026-09-20",
      "sisa_hari": 11,
      "is_overdue": false,
      "total_tunggakan": 0,
      "unpaid_count": 0,
      "tagihan_aktif": null
    }
  }
  ```

*(Jika ada tagihan belum lunas, properti `tagihan_aktif` akan berisi objek invoice dan `status_layanan` bernilai `menunggu_pembayaran` atau `isolir`).*

---

### 2.2 Riwayat Faktur / Invoices
Menampilkan daftar riwayat tagihan bulanan pelanggan.

- **Method**: `GET`
- **Endpoint**: `/payments/invoices`
- **Query Parameter (Opsional)**: `?status=unpaid` atau `?status=paid`
- **Header**: `Authorization: Bearer <token>`
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "invoice_number": "INV-20260901-0001",
        "amount": 112000,
        "tax": 0,
        "discount": 0,
        "total": 112000,
        "status": "paid",
        "due_date": "2026-09-08",
        "payment_method": "Xendit - BCA_VA",
        "xendit_invoice_url": "https://checkout.xendit.co/web/...",
        "paid_at": "2026-09-05 10:14:20",
        "created_at": "2026-09-01 00:00:00"
      }
    ]
  }
  ```

---

### 2.3 Detail Faktur Tertentu
- **Method**: `GET`
- **Endpoint**: `/payments/invoices/{id}`
- **Header**: `Authorization: Bearer <token>`
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "invoice_number": "INV-20260901-0001",
      "paket_nama": "Paket Bronze 112K",
      "amount": 112000,
      "tax": 0,
      "discount": 0,
      "total": 112000,
      "status": "paid",
      "due_date": "2026-09-08",
      "payment_method": "Xendit - BCA_VA",
      "xendit_invoice_url": "https://checkout.xendit.co/web/...",
      "created_at": "2026-09-01 00:00:00",
      "transactions": [
        {
          "id": 1,
          "payment_method": "Xendit - BCA_VA",
          "amount_paid": 112000,
          "payment_date": "2026-09-05 10:14:20",
          "status": "success",
          "xendit_payment_id": "pm_12345"
        }
      ]
    }
  }
  ```

---

### 2.4 Melakukan Pembayaran (Xendit Payment Gateway)
Mengambil atau membuat link checkout pembayaran online (Virtual Account, QRIS, Alfamart/Indomaret, OVO, Dana, GoPay, Kartu Debit/Kredit).

- **Method**: `POST`
- **Endpoint**: `/payments/pay`
- **Header**: `Authorization: Bearer <token>`
- **Request Body (JSON - Opsional)**:
  ```json
  {
    "invoice_id": 1
  }
  ```
  *(Jika `invoice_id` dikosongkan, sistem secara otomatis memilih tagihan aktif yang paling awal jatuh tempo).*

- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "message": "Checkout payment gateway berhasil dibuat.",
    "invoice_id": 1,
    "invoice_number": "INV-20260901-0001",
    "total": 112000,
    "payment_url": "https://checkout.xendit.co/web/66de46781290abcdef"
  }
  ```
> **Implementasi Mobile App:** Buka nilai `payment_url` tersebut menggunakan **WebView** di dalam aplikasi atau melalui Browser sistem.

---

### 2.5 Cek / Sinkronisasi Status Pembayaran
Digunakan ketika pelanggan selesai melakukan pembayaran di WebView Xendit dan kembali ke halaman aplikasi.

- **Method**: `POST`
- **Endpoint**: `/payments/invoices/{id}/check-status`
- **Header**: `Authorization: Bearer <token>`
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "status": "paid",
    "payment_method": "Xendit - QRIS",
    "message": "Pembayaran lunas terverifikasi."
  }
  ```

---

## 3️⃣ Modul Informasi Akun & Penggunaan

### 3.1 Informasi Akun & Paket Lengkap
Menampilkan data profil pelanggan, paket internet aktif, spesifikasi modem, dan informasi teknis.

- **Method**: `GET`
- **Endpoint**: `/account/info`
- **Header**: `Authorization: Bearer <token>`
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": {
      "profil": {
        "id": 1,
        "id_pelanggan": "23114957687",
        "nama_lengkap": "Nurul Adhira",
        "no_whatsapp": "6282191285271",
        "alamat_lengkap": "Blok Tegal Kalapa RT. 14 RW 10 Dangdeur Subang",
        "email": "nurul@bbs.id"
      },
      "paket": {
        "id": 1,
        "nama_paket": "Paket Bronze 112K",
        "kecepatan": "10 Mbps",
        "harga": 112000,
        "pajak": 0,
        "diskon": 0,
        "total_harga": 112000,
        "deskripsi": "Akses internet unlimited 24 jam tanpa FUP"
      },
      "teknis": {
        "pppoe_username": "Nurultgl@erlangga.net",
        "jenis_modem": "EPON",
        "sn_perangkat": null,
        "ip_address": "182.10.0.3",
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

### 3.2 Informasi Penggunaan (Traffic Kuota & Koneksi)
Menampilkan data pemakaian traffic internet (FreeRADIUS / MikroTik), status koneksi saat ini (Online/Offline), uptime perangkat, dan riwayat sesi.

- **Method**: `GET`
- **Endpoint**: `/account/usage`
- **Header**: `Authorization: Bearer <token>`
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": {
      "koneksi": {
        "status": "online",
        "ip_address": "182.10.0.3",
        "mac_address": "1C:3D:2F:80:05:47",
        "online_since": "2026-09-06 18:29:12",
        "uptime": "3 Hari 15 Jam 20 Menit",
        "uptime_seconds": 314400
      },
      "penggunaan_kuota": {
        "periode": "September 2026",
        "total_download": "44.95 GB",
        "total_upload": "3.59 GB",
        "total_traffic": "48.54 GB",
        "download_bytes": 48264556084,
        "upload_bytes": 3854175653,
        "total_bytes": 52118731737,
        "total_sesi": 1,
        "tipe_kuota": "Unlimited (Tanpa FUP)"
      },
      "paket": {
        "nama_paket": "Paket Bronze 112K",
        "kecepatan": "10 Mbps"
      },
      "riwayat_sesi": [
        {
          "ip_address": "182.10.0.3",
          "waktu_konek": "06/09/2026 18:29",
          "waktu_putus": "Aktif Sekarang",
          "durasi": "3 Hari 15 Jam 20 Menit",
          "download": "44.95 GB",
          "upload": "3.59 GB",
          "total": "48.54 GB",
          "status": "online"
        }
      ]
    }
  }
  ```

---

## 4️⃣ Modul Kontrol Perangkat & Wi-Fi (ACS / TR-069)

### 4.1 Informasi Wi-Fi & Status ONT
Mengambil konfigurasi Wi-Fi (SSID, Sandi), sinyal optik (RX Power), suhu, dan status online modem secara langsung dari ONT via GenieACS.

- **Method**: `GET`
- **Endpoint**: `/device/wifi`
- **Header**: `Authorization: Bearer <token>`
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": {
      "device": {
        "device_id": "202BC1-F609-ZTEG12345678",
        "serial_number": "ZTEG12345678",
        "model": "F609",
        "manufacturer": "ZTE",
        "is_online": true,
        "rx_power": "-21.4 dBm",
        "temperature": "45.0 °C",
        "pon_mode": "GPON",
        "last_inform": "09 Sep 2026 14:20:00"
      },
      "wlan_2_4g": {
        "ssid": "ERLANGGA_NET_NURUL",
        "password": "wifi_password123",
        "channel": "Auto",
        "status": "Up",
        "enable": true
      },
      "wlan_5g": null
    }
  }
  ```

---

### 4.2 Daftar Perangkat Terhubung (Connected Devices)
Melihat daftar gadget/perangkat (HP, Laptop, Smart TV, dll.) yang sedang terhubung ke router Wi-Fi atau port LAN ONT pelanggan.

- **Method**: `GET`
- **Endpoint**: `/device/hosts`
- **Header**: `Authorization: Bearer <token>`
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": {
      "total_connected": 3,
      "total_recorded": 5,
      "hosts": [
        {
          "index": "1",
          "hostname": "iPhone-15-Pro",
          "ip": "192.168.1.10",
          "mac": "AA:BB:CC:11:22:33",
          "interface": "Wi-Fi",
          "active": true
        },
        {
          "index": "2",
          "hostname": "Samsung-Smart-TV",
          "ip": "192.168.1.15",
          "mac": "AA:BB:CC:44:55:66",
          "interface": "Wi-Fi",
          "active": true
        },
        {
          "index": "3",
          "hostname": "PC-Workstation",
          "ip": "192.168.1.20",
          "mac": "AA:BB:CC:77:88:99",
          "interface": "Ethernet",
          "active": true
        }
      ]
    }
  }
  ```

---

### 4.3 Mengganti Nama (SSID) & Sandi Wi-Fi
Mengirimkan tugas `setParameterValues` TR-069 ke modem ONT pelanggan untuk mengubah nama Wi-Fi atau kata sandi secara instan.

- **Method**: `POST`
- **Endpoint**: `/device/wifi`
- **Header**: `Authorization: Bearer <token>`
- **Request Body (JSON)**:
  ```json
  {
    "ssid": "ERLANGGA_WIFI_BARU",
    "password": "password_baru123",
    "wlan_index": 1
  }
  ```
  *(Catatan: `wlan_index` bernilai `1` untuk frekuensi 2.4GHz dan `5` untuk 5GHz. Default: `1`).*

- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "message": "Perubahan nama dan sandi Wi-Fi berhasil dikirim ke perangkat ONT!",
    "data": {
      "ssid": "ERLANGGA_WIFI_BARU",
      "wlan_index": 1
    }
  }
  ```

---

### 4.4 Reboot / Restart Perangkat ONT
Mengirimkan perintah `reboot` TR-069 ke modem ONT pelanggan jika internet lambat atau ingin melakukan restart mandiri dari aplikasi.

- **Method**: `POST`
- **Endpoint**: `/device/reboot`
- **Header**: `Authorization: Bearer <token>`
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "message": "Perintah reboot berhasil dikirim. Perangkat ONT akan restart dalam beberapa detik."
  }
  ```

---

## 5️⃣ Mekanisme Penghubungan (Link) ACS & Pelanggan

Sistem **secara otomatis menghubungkan (auto-link)** akun pelanggan dengan perangkat modem ONT di GenieACS melalui 2 lapisan identitas unik:

```mermaid
flowchart LR
    Customer["Akun Pelanggan (Login Mobile)"]
    ACS["GenieACS (TR-069 Engine)"]
    ONT["Perangkat Modem ONT"]

    Customer -->|"1. Cocokkan Serial Number (sn_perangkat)"| ACS
    Customer -.->|"2. Fallback: Cocokkan PPPoE (pppoe_username)"| ACS
    ACS <-->|"TR-069 Protocol"| ONT
```

1. **Prioritas 1: Serial Number ONT (`sn_perangkat`)**
   - Dicocokkan dengan parameter TR-069 `DeviceID.SerialNumber` / `DeviceID.ID`.
2. **Prioritas 2: Akun Dial PPPoE (`pppoe_username`)**
   - Jika SN belum terisi atau berbeda, sistem secara cerdas mencocokkan `Customer.pppoe_username` dengan parameter WAN PPP Dialer ONT (`VirtualParameters.pppoeUsername` atau `WANPPPConnection.Username`).
3. **Hasil**: Pelanggan yang login **hanya bisa mengakses dan mengontrol modem milik mereka sendiri**, tanpa perlu konfigurasi ID manual di aplikasi.

---

## 6️⃣ Format Response Standar Error

### 401 Unauthorized (Token Kadaluarsa / Belum Login)
```json
{
  "message": "Unauthenticated."
}
```

### 422 Unprocessable Entity (Validasi Input Gagal)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "ssid": [
      "The ssid field is required."
    ]
  }
}
```

### 404 Not Found (Modem / Data Tidak Ditemukan)
```json
{
  "success": false,
  "message": "Perangkat ONT belum terdaftar di ACS atau belum terhubung."
}
```
