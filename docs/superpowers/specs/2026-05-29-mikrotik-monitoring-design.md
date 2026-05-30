# Monitoring Bandwidth MikroTik — Design Spec

**Date:** 2026-05-29
**Status:** Approved

---

## Overview

Fitur monitoring bandwidth/traffic MikroTik secara real-time via chart. Target: 1 MikroTik device (x86), 1 interface, client-side polling setiap 1 detik.

---

## Architecture

```
MikroTik API (API-REST)
    → Laravel Route [GET /api/mikrotik/traffic]
        → MikroTikService (pakai library Zilleali/mikrotik-laravel)
            → return { rx_bytes, tx_bytes, timestamp }
    → Blade View (Chart.js, polling JS setiap 1 detik)
```

**File yang dibuat:**

| File                                                   | Purpose                              |
| ------------------------------------------------------ | ------------------------------------ |
| `app/Services/MikroTikService.php`                     | Service layer, wrap library Zilleali |
| `routes/api.php`                                       | API endpoint `/api/mikrotik/traffic` |
| `routes/web.php`                                       | Route `/monitoring`                  |
| `resources/views/monitoring/index.blade.php`           | Halaman chart                        |
| `resources/views/components/layouts/sidebar.blade.php` | Tambah menu sidebar                  |
| `.env`                                                 | Credentials MikroTik                 |

**Library:** `Zilleali/mikrotik-laravel`

---

## Environment Variables

```env
MIKROTIK_HOST=192.168.10.1
MIKROTIK_PORT=8728
MIKROTIK_USER=administrator@hakim.id
MIKROTIK_PASSWORD=admin
MIKROTIK_INTERFACE=ether1-ISP
```

---

## API Endpoint

**Route:** `GET /api/mikrotik/traffic`

**Response:**

```json
{
    "success": true,
    "data": {
        "interface": "ether1-ISP",
        "rx_bytes": 1234567,
        "tx_bytes": 987654,
        "rx_rate": 1024.5,
        "tx_rate": 512.3,
        "timestamp": "2026-05-29T10:00:00Z"
    }
}
```

**Error response (MikroTik offline):**

```json
{
    "success": false,
    "error": "Connection refused"
}
```

**Logic:**

1. Connect ke MikroTik via `RouterosAPI` (library Zilleali)
2. Fetch interface stats via `/interface/monitor-traffic`
3. Return byte counts + calculated rate

---

## UI Layout

**URL:** `/monitoring`
**Menu label:** "Monitoring MikroTik"
**Middleware:** `auth` (sama dengan halaman lain)

```
┌─ Judul: "Monitoring Bandwidth"
├─ Info card: Interface (ether1) | IP (192.168.88.1) | Status (Online/Offline badge)
├─ Stats row: Current RX (MB/s) | Current TX (MB/s) | Peak RX | Peak TX
├─ Chart area: Line chart RX vs TX (rolling 60 detik)
└─ Footer: Last updated: HH:MM:SS
```

**Chart specs:**

- Library: Chart.js via CDN
- Type: Line chart
- 2 datasets: RX (biru), TX (oranye)
- X-axis: waktu rolling (60 detik window)
- Y-axis: bytes/sec, auto scale, format KB/MB
- Polling: `setInterval()` setiap 1000ms (1 detik)
- Max data points: 60 (shift array ketika overflow)

**Error state:**

- MikroTik offline → badge "Offline" merah
- Chart berhenti, stats show "N/A"
- Retry on next poll

---

## Tech Stack

- Laravel 12
- Chart.js via CDN
- Tailwind CSS v4 (already in project)
- Library: `zilleali/mikrotik-laravel`

---

## Implementation Steps

1. Install `zilleali/mikrotik-laravel`
2. Setup `.env` variables
3. Buat `MikroTikService.php`
4. Buat API route `GET /api/mikrotik/traffic`
5. Buat web route `/monitoring`
6. Buat `resources/views/monitoring/index.blade.php`
7. Update sidebar component (tambah menu)
