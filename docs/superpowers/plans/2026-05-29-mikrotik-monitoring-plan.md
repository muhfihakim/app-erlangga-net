# MikroTik Bandwidth Monitoring — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Real-time bandwidth monitoring page at `/monitoring` — 1 MikroTik device, 1 interface, polling every 1 second via Chart.js.

**Architecture:** Client-side JS polling → Laravel API route → MikroTikService → MikroTik REST API. No database, no queue worker.

**Tech Stack:** Laravel 12, Chart.js via CDN, Tailwind v4, `zilleali/mikrotik-laravel`.

---

## File Map

| Action | File |
|--------|------|
| Create | `app/Services/MikroTikService.php` |
| Create | `app/Http/Controllers/MonitoringController.php` |
| Modify | `routes/web.php` — add `/monitoring` route |
| Modify | `routes/api.php` — add `GET /api/mikrotik/traffic` |
| Modify | `resources/views/components/layouts/sidebar.blade.php` — add Monitoring menu |
| Create | `resources/views/monitoring/index.blade.php` |

---

## Implementation Steps

### Task 1: Install `zilleali/mikrotik-laravel`

- [ ] **Step 1: Install via Composer**

```bash
cd /Users/bbs/Documents/Project/app-erlangga-net
composer require zilleali/mikrotik-laravel
```

---

### Task 2: Setup `.env` variables

- [ ] **Step 1: Add MikroTik env vars to `.env`**

```env
MIKROTIK_HOST=192.168.10.1
MIKROTIK_PORT=8728
MIKROTIK_USER=administrator@hakim.id
MIKROTIK_PASSWORD=admin
MIKROTIK_INTERFACE=ether1-ISP
```

- [ ] **Step 2: Publish config (if any)**

```bash
php artisan vendor:publish --tag=mikrotik 2>/dev/null || echo "no publish tag"
```

---

### Task 3: Create `MikroTikService.php`

- [ ] **Step 1: Create service file**

File: `app/Services/MikroTikService.php`

```php
<?php

namespace App\Services;

use Exception;

class MikroTikService
{
    private string $host;
    private int $port;
    private string $user;
    private string $password;
    private string $interface;

    public function __construct()
    {
        $this->host = config('mikrotik.host');
        $this->port = (int) config('mikrotik.port', 8728);
        $this->user = config('mikrotik.user');
        $this->password = config('mikrotik.password');
        $this->interface = config('mikrotik.interface');
    }

    public function getTraffic(): array
    {
        try {
            $api = new \RouterosAPI();
            $api->port = $this->port;

            if (!$api->connect($this->host, $this->user, $this->password)) {
                throw new Exception('Cannot connect to MikroTik');
            }

            // Use monitor-traffic to get live stats
            $response = $api->comm('/interface/monitor-traffic', [
                'interface' => $this->interface,
                'once' => true,
            ]);

            $api->disconnect();

            if (empty($response[0])) {
                throw new Exception('No traffic data returned');
            }

            $data = $response[0];

            return [
                'success' => true,
                'data' => [
                    'interface' => $this->interface,
                    'rx_bytes' => (int) ($data['rx-bits-per-second'] ?? 0),
                    'tx_bytes' => (int) ($data['tx-bits-per-second'] ?? 0),
                    'rx_rate' => round(((int) ($data['rx-bits-per-second'] ?? 0)) / 8, 2),
                    'tx_rate' => round(((int) ($data['tx-bits-per-second'] ?? 0)) / 8, 2),
                    'timestamp' => now()->toIso8601String(),
                ],
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getStatus(): array
    {
        try {
            $api = new \RouterosAPI();
            $api->port = $this->port;

            if (!$api->connect($this->host, $this->user, $this->password)) {
                return ['online' => false, 'interface' => $this->interface, 'host' => $this->host];
            }

            $api->disconnect();

            return ['online' => true, 'interface' => $this->interface, 'host' => $this->host];
        } catch (Exception $e) {
            return ['online' => false, 'interface' => $this->interface, 'host' => $this->host];
        }
    }
}
```

- [ ] **Step 2: Add config file**

File: `config/mikrotik.php`

```php
<?php

return [
    'host' => env('MIKROTIK_HOST', '192.168.10.1'),
    'port' => env('MIKROTIK_PORT', 8728),
    'user' => env('MIKROTIK_USER', 'admin'),
    'password' => env('MIKROTIK_PASSWORD', ''),
    'interface' => env('MIKROTIK_INTERFACE', 'ether1'),
];
```

- [ ] **Step 3: Commit**

```bash
git add app/Services/MikroTikService.php config/mikrotik.php
git commit -m "feat(monitoring): add MikroTikService and config"
```

---

### Task 4: Create `MonitoringController.php`

- [ ] **Step 1: Create controller**

File: `app/Http/Controllers/MonitoringController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Services\MikroTikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function __construct(
        private MikroTikService $mikroTikService
    ) {}

    public function index()
    {
        return view('monitoring.index');
    }

    public function traffic(): JsonResponse
    {
        $result = $this->mikroTikService->getTraffic();

        return response()->json($result);
    }

    public function status(): JsonResponse
    {
        $result = $this->mikroTikService->getStatus();

        return response()->json($result);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/MonitoringController.php
git commit -m "feat(monitoring): add MonitoringController"
```

---

### Task 5: Add routes

- [ ] **Step 1: Add to `routes/web.php`**

Add inside the `auth.custom` group:

```php
Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
```

- [ ] **Step 2: Add to `routes/api.php`**

Inside the `auth:sanctum` group:

```php
Route::get('/mikrotik/traffic', [MonitoringController::class, 'traffic']);
Route::get('/mikrotik/status', [MonitoringController::class, 'status']);
```

- [ ] **Step 3: Commit**

```bash
git add routes/web.php routes/api.php
git commit -m "feat(monitoring): add /monitoring route and API endpoints"
```

---

### Task 6: Update sidebar

- [ ] **Step 1: Add menu item in sidebar**

File: `resources/views/components/layouts/sidebar.blade.php`

Add under "Infrastruktur" section, after the ODP link:

```php
<a class="sidebar-link {{ Request::is('monitoring') ? 'active' : '' }}"
    href="{{ route('monitoring.index') }}">
    <i class="fa-solid fa-chart-line"></i>
    <span>Monitoring</span>
</a>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/layouts/sidebar.blade.php
git commit -m "feat(monitoring): add sidebar menu item"
```

---

### Task 7: Create monitoring view

- [ ] **Step 1: Create directory and view**

File: `resources/views/monitoring/index.blade.php`

```blade
<x-layouts.app>
    @section('Css')
        <style>
            .table-toolbar {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1rem 1.25rem;
                margin-bottom: 1rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }
            .info-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1.25rem 1.5rem;
                display: flex;
                align-items: center;
                gap: 1rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }
            .stats-row {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 1rem;
                margin-bottom: 1rem;
            }
            .stat-box {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1rem 1.25rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }
            .stat-box .lbl { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
            .stat-box .val { font-size: 20px; font-weight: 900; color: #0f172a; }
            .stat-box .val.rx { color: #0ea5e9; }
            .stat-box .val.tx { color: #f97316; }
            .chart-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1.5rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }
            .badge-online { background:#dcfce7; color:#15803d; padding:4px 12px; border-radius:6px; font-size:11px; font-weight:800; }
            .badge-offline { background:#fef2f2; color:#dc2626; padding:4px 12px; border-radius:6px; font-size:11px; font-weight:800; }
        </style>
    @endsection

    <!-- Page header -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Monitoring Bandwidth</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Real-time traffic MikroTik interface.</p>
        </div>
        <div id="connection-badge"></div>
    </div>

    <!-- Info card -->
    <div class="info-card" style="margin-bottom:1rem;">
        <div style="flex:1;">
            <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Interface</div>
            <div id="info-interface" style="font-size:15px;font-weight:700;color:#0f172a;">{{ config('mikrotik.interface') }}</div>
        </div>
        <div style="flex:1;">
            <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">IP Address</div>
            <div id="info-host" style="font-size:15px;font-weight:700;color:#0f172a;">{{ config('mikrotik.host') }}</div>
        </div>
        <div style="flex:1;">
            <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Status</div>
            <div id="status-badge" class="badge-offline">Mengecek...</div>
        </div>
        <div>
            <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Polling</div>
            <div style="font-size:15px;font-weight:700;color:#0f172a;">1 detik</div>
        </div>
    </div>

    <!-- Stats row -->
    <div class="stats-row">
        <div class="stat-box">
            <div class="lbl">RX Saat Ini</div>
            <div id="stat-rx" class="val rx" data-value="0">0 B/s</div>
        </div>
        <div class="stat-box">
            <div class="lbl">TX Saat Ini</div>
            <div id="stat-tx" class="val tx" data-value="0">0 B/s</div>
        </div>
        <div class="stat-box">
            <div class="lbl">Peak RX</div>
            <div id="stat-peak-rx" class="val rx" data-value="0">0 B/s</div>
        </div>
        <div class="stat-box">
            <div class="lbl">Peak TX</div>
            <div id="stat-peak-tx" class="val tx" data-value="0">0 B/s</div>
        </div>
    </div>

    <!-- Chart -->
    <div class="chart-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <div style="font-size:14px;font-weight:800;color:#334155;">Traffic Chart (60 detik terakhir)</div>
            <div style="display:flex;gap:12px;font-size:12px;font-weight:700;">
                <span style="display:inline-flex;align-items:center;gap:6px;"><span style="width:12px;height:3px;background:#0ea5e9;border-radius:2px;display:inline-block;"></span> RX</span>
                <span style="display:inline-flex;align-items:center;gap:6px;"><span style="width:12px;height:3px;background:#f97316;border-radius:2px;display:inline-block;"></span> TX</span>
            </div>
        </div>
        <canvas id="trafficChart" height="200"></canvas>
        <div style="text-align:center;font-size:12px;color:#94a3b8;margin-top:8px;">
            Terakhir diperbarui: <span id="last-updated">--</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const MAX_POINTS = 60;
        const labels = [];
        const rxData = [];
        const txData = [];

        // Initialize labels with 60 empty slots
        for (let i = 0; i < MAX_POINTS; i++) {
            labels.push('');
            rxData.push(0);
            txData.push(0);
        }

        const ctx = document.getElementById('trafficChart').getContext('2d');
        const trafficChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'RX',
                        data: rxData,
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14,165,233,.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: .3,
                        pointRadius: 0,
                    },
                    {
                        label: 'TX',
                        data: txData,
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249,115,22,.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: .3,
                        pointRadius: 0,
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    x: { display: false },
                    y: {
                        ticks: {
                            callback: function(value) {
                                if (value >= 1048576) return (value / 1048576).toFixed(1) + ' MB/s';
                                if (value >= 1024) return (value / 1024).toFixed(1) + ' KB/s';
                                return value + ' B/s';
                            },
                            font: { size: 11 }
                        },
                        grid: { color: '#f1f5f9' }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatBytes(context.raw);
                            }
                        }
                    }
                },
                animation: { duration: 0 }
            }
        });

        function formatBytes(bytes) {
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB/s';
            if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB/s';
            return bytes + ' B/s';
        }

        function formatBytesShort(bytes) {
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB/s';
            if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB/s';
            return bytes + ' B/s';
        }

        let peakRx = 0;
        let peakTx = 0;

        function updateChart() {
            fetch('/api/mikrotik/traffic')
                .then(res => res.json())
                .then(response => {
                    const now = new Date();
                    const timeStr = now.getHours().toString().padStart(2, '0') + ':' +
                        now.getMinutes().toString().padStart(2, '0') + ':' +
                        now.getSeconds().toString().padStart(2, '0');

                    if (response.success) {
                        const rxRate = response.data.rx_rate;
                        const txRate = response.data.tx_rate;

                        // Shift and push
                        labels.shift();
                        labels.push(timeStr);
                        rxData.shift();
                        rxData.push(rxRate);
                        txData.shift();
                        txData.push(txRate);

                        trafficChart.update('none');

                        // Update stats
                        document.getElementById('stat-rx').textContent = formatBytesShort(rxRate);
                        document.getElementById('stat-tx').textContent = formatBytesShort(txRate);

                        if (rxRate > peakRx) {
                            peakRx = rxRate;
                            document.getElementById('stat-peak-rx').textContent = formatBytesShort(peakRx);
                        }
                        if (txRate > peakTx) {
                            peakTx = txRate;
                            document.getElementById('stat-peak-tx').textContent = formatBytesShort(peakTx);
                        }

                        document.getElementById('last-updated').textContent = timeStr;
                    } else {
                        // Offline - push zeros
                        labels.shift();
                        labels.push(timeStr);
                        rxData.shift();
                        rxData.push(0);
                        txData.shift();
                        txData.push(0);
                        trafficChart.update('none');
                    }
                })
                .catch(() => {
                    // Network error - push zeros
                    const now = new Date();
                    const timeStr = now.getHours().toString().padStart(2, '0') + ':' +
                        now.getMinutes().toString().padStart(2, '0') + ':' +
                        now.getSeconds().toString().padStart(2, '0');
                    labels.shift();
                    labels.push(timeStr);
                    rxData.shift();
                    rxData.push(0);
                    txData.shift();
                    txData.push(0);
                    trafficChart.update('none');
                });
        }

        // Poll every 1 second
        setInterval(updateChart, 1000);
        updateChart(); // Initial call
    </script>
</x-layouts.app>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/monitoring/index.blade.php
git commit -m "feat(monitoring): add monitoring page with real-time Chart.js"
```

---

## Post-Implementation Checklist

- [ ] `php artisan config:clear` — clear config cache
- [ ] Visit `/monitoring` in browser — verify chart loads
- [ ] Open browser DevTools → Network tab — verify `/api/mikrotik/traffic` called every 1s
- [ ] Verify chart updates with live data (RX/TX lines move)
- [ ] Check MikroTik API credentials — ensure correct host/port/user/password in `.env`