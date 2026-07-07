<x-layouts.app>
    @section('Css')
        <style>
            .info-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1.25rem 1.5rem;
                display: flex;
                align-items: center;
                gap: 2rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
                margin-bottom: 1rem;
                flex-wrap: wrap;
            }
            .info-item { flex: 1 1 calc(16.666% - 1rem); min-width: 120px; }
            .info-item .lbl {
                font-size: 11px;
                font-weight: 800;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: .05em;
                margin-bottom: 2px;
            }
            .info-item .val {
                font-size: 15px;
                font-weight: 700;
                color: #0f172a;
            }
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 1rem;
                margin-bottom: 1rem;
            }
            .stat-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1rem 1.25rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }
            .stat-card .lbl {
                font-size: 11px;
                font-weight: 800;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: .05em;
                margin-bottom: 4px;
            }
            .stat-card .val {
                font-size: 20px;
                font-weight: 900;
            }
            .stat-card .val.rx { color: #0ea5e9; }
            .stat-card .val.tx { color: #f97316; }
            .chart-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1.5rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }
            .badge-online {
                background: #dcfce7;
                color: #15803d;
                padding: 4px 12px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 800;
                display: inline-block;
            }
            .badge-offline {
                background: #fef2f2;
                color: #dc2626;
                padding: 4px 12px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 800;
                display: inline-block;
            }
            .badge-checking {
                background: #fef3c7;
                color: #d97706;
                padding: 4px 12px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 800;
                display: inline-block;
            }
            @media (max-width: 768px) {
                .info-card { gap: 1rem; padding: 1rem; }
                .info-item { flex: 1 1 calc(50% - 1rem); min-width: 100px; }
                .info-item .val { font-size: 13px; }
                .stats-grid { grid-template-columns: repeat(2, 1fr); }
            }
        </style>
    @endsection

    <!-- Page header -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Monitoring Bandwidth</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Real-time traffic MikroTik interface.</p>
        </div>
        <div style="display:flex; gap:10px;">
        </div>
    </div>

    <!-- Info card -->
    <div class="info-card">
        <div class="info-item">
            <div class="lbl">Interface</div>
            <div class="val">
                <select id="interface-select" onchange="changeInterface()" style="padding: 4px 8px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; font-weight: 700; color: #0f172a; outline: none; width: 100%; cursor: pointer;">
                    @foreach($interfaces as $iface)
                        <option value="{{ $iface }}" {{ $iface == config('mikrotik.interface') ? 'selected' : '' }}>{{ $iface }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="info-item">
            <div class="lbl">Status</div>
            <div id="status-badge"><span class="badge-checking">Mengecek...</span></div>
        </div>
        <div class="info-item">
            <div class="lbl">Total RX</div>
            <div class="val" id="info-total-rx" style="font-size:13px;font-weight:700;color:#0ea5e9;">--</div>
        </div>
        <div class="info-item">
            <div class="lbl">Total TX</div>
            <div class="val" id="info-total-tx" style="font-size:13px;font-weight:700;color:#f97316;">--</div>
        </div>
    </div>

    <!-- Stats row -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="lbl">RX Saat Ini</div>
            <div class="val rx" id="stat-rx" data-value="0">0 bps</div>
        </div>
        <div class="stat-card">
            <div class="lbl">TX Saat Ini</div>
            <div class="val tx" id="stat-tx" data-value="0">0 bps</div>
        </div>
        <div class="stat-card">
            <div class="lbl">Peak RX</div>
            <div class="val rx" id="stat-peak-rx" data-value="0">0 bps</div>
        </div>
        <div class="stat-card">
            <div class="lbl">Peak TX</div>
            <div class="val tx" id="stat-peak-tx" data-value="0">0 bps</div>
        </div>
    </div>

    <!-- Chart -->
    <div class="chart-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:8px;">
            <div style="font-size:14px;font-weight:800;color:#334155;">Traffic Chart (60 detik terakhir)</div>
            <div style="display:flex;gap:12px;font-size:12px;font-weight:700;">
                <span style="display:inline-flex;align-items:center;gap:6px;">
                    <span style="width:12px;height:3px;background:#0ea5e9;border-radius:2px;display:inline-block;"></span> RX (Download)
                </span>
                <span style="display:inline-flex;align-items:center;gap:6px;">
                    <span style="width:12px;height:3px;background:#f97316;border-radius:2px;display:inline-block;"></span> TX (Upload)
                </span>
            </div>
        </div>
        <canvas id="trafficChart" style="max-height:280px;"></canvas>
        <div style="text-align:center;font-size:12px;color:#94a3b8;margin-top:8px;">
            Terakhir diperbarui: <span id="last-updated">--:--:--</span>
        </div>
    </div>

    <script src="{{ asset('dist/js/chart.umd.min.js') }}"></script>
    <script>
        const MAX_POINTS = 60;
        const labels = [];
        const rxData = [];
        const txData = [];

        for (let i = 0; i < MAX_POINTS; i++) {
            labels.push('');
            rxData.push(null);
            txData.push(null);
        }

        const ctx = document.getElementById('trafficChart').getContext('2d');
        const trafficChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'RX (Download)',
                        data: rxData,
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14,165,233,.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 0,
                    },
                    {
                        label: 'TX (Upload)',
                        data: txData,
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249,115,22,.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
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
                                if (value >= 1000000000) return (value / 1000000000).toFixed(1) + ' Gbps';
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + ' Mbps';
                                if (value >= 1000) return (value / 1000).toFixed(1) + ' Kbps';
                                return value + ' bps';
                            },
                            font: { size: 11 }
                        },
                        grid: { color: '#f1f5f9' },
                        beginAtZero: true,
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatBits(context.raw);
                            }
                        }
                    }
                },
                animation: { duration: 0 }
            }
        });

        function formatBits(bits) {
            if (bits == null) return 'N/A';
            if (bits >= 1000000000) return (bits / 1000000000).toFixed(2) + ' Gbps';
            if (bits >= 1000000) return (bits / 1000000).toFixed(2) + ' Mbps';
            if (bits >= 1000) return (bits / 1000).toFixed(2) + ' Kbps';
            return bits + ' bps';
        }

        function formatBytesLarge(bytes) {
            if (bytes == null || bytes === 0) return '0 B';
            if (bytes >= 1099511627776) return (bytes / 1099511627776).toFixed(3) + ' TB';
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
            if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
            return bytes + ' B';
        }

        function formatBitsShort(bits) {
            if (bits == null || bits === 0) return '0 bps';
            if (bits >= 1000000000) return (bits / 1000000000).toFixed(2) + ' Gbps';
            if (bits >= 1000000) return (bits / 1000000).toFixed(2) + ' Mbps';
            if (bits >= 1000) return (bits / 1000).toFixed(2) + ' Kbps';
            return bits + ' bps';
        }

        function zeroPad(n) { return n.toString().padStart(2, '0'); }

        function getTimeStr() {
            const d = new Date();
            return zeroPad(d.getHours()) + ':' + zeroPad(d.getMinutes()) + ':' + zeroPad(d.getSeconds());
        }

        let peakRx = 0;
        let peakTx = 0;

        function updateStatus() {
            fetch('/api/mikrotik/status')
                .then(r => r.json())
                .then(resp => {
                    const badge = document.getElementById('status-badge');
                    if (resp.online) {
                        badge.innerHTML = '<span class="badge-online">ONLINE</span>';
                    } else {
                        badge.innerHTML = '<span class="badge-offline">OFFLINE</span>';
                    }
                })
                .catch(() => {
                    document.getElementById('status-badge').innerHTML = '<span class="badge-offline">OFFLINE</span>';
                });
        }

        let currentInterface = document.getElementById('interface-select') ? document.getElementById('interface-select').value : '';

        function changeInterface() {
            currentInterface = document.getElementById('interface-select').value;
            // Reset chart and stats for new interface
            for (let i = 0; i < MAX_POINTS; i++) {
                rxData[i] = 0;
                txData[i] = 0;
            }
            peakRx = 0;
            peakTx = 0;
            document.getElementById('stat-peak-rx').textContent = '0 bps';
            document.getElementById('stat-peak-tx').textContent = '0 bps';
            trafficChart.update('none');
            updateChart(); // fetch immediately
        }

        function updateChart() {
            const url = currentInterface ? `/api/mikrotik/traffic?interface=${encodeURIComponent(currentInterface)}` : '/api/mikrotik/traffic';
            fetch(url)
                .then(res => res.json())
                .then(response => {
                    const timeStr = getTimeStr();

                    if (response.success && response.data) {
                        const rxRate = response.data.rx_rate || 0;
                        const txRate = response.data.tx_rate || 0;

                        labels.shift();
                        labels.push(timeStr);
                        rxData.shift();
                        rxData.push(rxRate);
                        txData.shift();
                        txData.push(txRate);

                        trafficChart.update('none');

                        document.getElementById('stat-rx').textContent = formatBitsShort(rxRate);
                        document.getElementById('stat-tx').textContent = formatBitsShort(txRate);

                        if (rxRate > peakRx) {
                            peakRx = rxRate;
                            document.getElementById('stat-peak-rx').textContent = formatBitsShort(peakRx);
                        }
                        if (txRate > peakTx) {
                            peakTx = txRate;
                            document.getElementById('stat-peak-tx').textContent = formatBitsShort(peakTx);
                        }

                        // Total & uptime
                        if (response.data.total_rx !== undefined) {
                            document.getElementById('info-total-rx').textContent = formatBytesLarge(response.data.total_rx);
                        }
                        if (response.data.total_tx !== undefined) {
                            document.getElementById('info-total-tx').textContent = formatBytesLarge(response.data.total_tx);
                        }

                        document.getElementById('last-updated').textContent = timeStr;
                    } else {
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
                    const timeStr = getTimeStr();
                    labels.shift();
                    labels.push(timeStr);
                    rxData.shift();
                    rxData.push(0);
                    txData.shift();
                    txData.push(0);
                    trafficChart.update('none');
                });
        }

        // Initial status check
        updateStatus();

        // Poll every 1 second
        setInterval(updateChart, 1000);
        setInterval(updateStatus, 30000); // check status every 30s

        // Initial call
        updateChart();
    </script>
</x-layouts.app>