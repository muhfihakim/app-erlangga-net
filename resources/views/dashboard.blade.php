<x-layouts.app>
    @section('Css')
        <style>
            /* Toolbar Styling */
            .table-toolbar {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1rem 1.25rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
                margin-bottom: 1rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }

            .search-wrap {
                position: relative;
                flex: 1;
                min-width: 200px;
                max-width: 360px;
            }

            .search-icon {
                position: absolute;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                color: #94a3b8;
                font-size: 13px;
                pointer-events: none;
            }

            .search-input {
                width: 100%;
                padding: 9px 12px 9px 36px;
                border: 2px solid #e2e8f0;
                border-radius: 10px;
                font-size: 13px;
                font-family: 'Inter', sans-serif;
                color: #374151;
                outline: none;
                transition: all .25s;
                background: #f8fafc;
            }

            .search-input:focus {
                border-color: #0ea5e9;
                background: #fff;
            }

            .search-input::placeholder {
                color: #94a3b8;
            }
        </style>
    @endsection

    <!-- Page header -->
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Dashboard</h1>
            <p style="font-size:13px;color: #64748b;margin:3px 0 0;">Selamat datang kembali,
                <strong>{{ Auth::user()->nama_lengkap }}</strong>.
            </p>
        </div>
    </div>

    @if ($user->jabatan !== 'Teknisi')
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $totalPoThisMonth }}</div>
                    <div class="stat-lbl">Total PO ({{ $currentPeriodLabel ?? 'Bulan Ini' }})</div>
                    <div class="stat-trend {{ $percentageChange >= 0 ? 'stat-up' : 'stat-down' }}">
                        <i
                            class="fa-solid {{ $percentageChange >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                        {{ round(abs($percentageChange), 1) }}% dibanding periode lalu
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $totalPoPreviousMonth }}</div>
                    <div class="stat-lbl">PO Periode Lalu ({{ $previousPeriodLabel ?? 'Bulan Lalu' }})</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $totalAktivasiThisMonth }}</div>
                    <div class="stat-lbl">Total Aktivasi (Periode Berjalan)</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#fef2f2;color:#dc2626;">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $totalPendingThisMonth }}</div>
                    <div class="stat-lbl">Total Pending (Periode Berjalan)</div>
                </div>
            </div>
        </div>
    @endif

    @if (Auth::user()->jabatan === 'Teknisi')
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                    <i class="fa-solid fa-calendar-week"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $aktivasiMingguIni ?? 0 }}</div>
                    <div class="stat-lbl">Aktivasi Minggu Ini</div>
                    <p style="font-size: 10px; color: #94a3b8; margin-top: 4px; font-weight: 600;">SABTU - JUMAT</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $aktivasiBulanIni ?? 0 }}</div>
                    <div class="stat-lbl">Aktivasi Bulan Ini</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#f0f9ff;color:#0ea5e9;">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $personalMaintenanceSelesaiBulanIni ?? 0 }}</div>
                    <div class="stat-lbl">Maintenance Selesai</div>
                    <p style="font-size: 10px; color: #94a3b8; margin-top: 4px; font-weight: 600;">BULAN INI</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef2f2;color:#dc2626;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $totalExpired ?? 0 }}</div>
                    <div class="stat-lbl">Total Expired</div>
                </div>
            </div>
        </div>
    @endif

    @if ($user->jabatan === 'Administrator')
        <!-- Performa Aktivasi -->
        <div style="margin-top: 2rem;">
            <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 1rem;">
                <i class="fa-solid fa-rocket text-success mr-2"></i> Performa Aktivasi
            </h3>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                <!-- Activation Trend -->
                <div class="table-card p-6" style="margin-bottom: 0;">
                    <h4
                        style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-chart-line text-success"></i> Tren Aktivasi Mingguan (Sabtu - Jumat)
                    </h4>
                    <div style="height: 300px; position: relative;">
                        <canvas id="activationTrendChart"></canvas>
                    </div>
                </div>

                <!-- Top Activation Technicians -->
                <div class="table-card p-6" style="margin-bottom: 0;">
                    <h4
                        style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-trophy text-amber-500"></i> Top Teknisi & Partner Aktivasi (Bulan Ini)
                    </h4>
                    <div style="height: 300px; position: relative;">
                        @if (count($aDonutValues) > 0)
                            <canvas id="activationTopTechChart"></canvas>
                        @else
                            <div class="text-center py-12">
                                <i class="fa-solid fa-chart-pie text-slate-200 text-5xl mb-3"></i>
                                <p class="text-slate-400 font-bold text-xs uppercase">Belum ada data</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Performa Sales -->
        <div style="margin-top: 2rem;">
            <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 1rem;">
                <i class="fa-solid fa-users-viewfinder text-indigo-500 mr-2"></i> Performa Sales
            </h3>

            <div class="table-card p-6">
                <h4
                    style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-award text-amber-500"></i> Top Sales: Input Pre-Order (Bulan Ini)
                </h4>
                <div style="height: 350px; position: relative;">
                    @if (count($sChartLabels) > 0)
                        <canvas id="topSalesChart"></canvas>
                    @else
                        <div class="text-center py-12">
                            <i class="fa-solid fa-users text-slate-200 text-5xl mb-3"></i>
                            <p class="text-slate-400 font-bold text-xs uppercase">Belum ada data</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Maintenance & Dismantle Insights -->
        <div style="margin-top: 2rem;">
            <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 1rem;">
                <i class="fa-solid fa-chart-pie text-primary mr-2"></i> Analisis & Performa Operasional
            </h3>

            <!-- Maintenance Stats -->
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                <!-- Maintenance Trend -->
                <div class="table-card p-6" style="margin-bottom: 0;">
                    <h4
                        style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-chart-line text-primary"></i> Tren Maintenance (Bulan Ini)
                    </h4>
                    <div style="height: 300px; position: relative;">
                        <canvas id="maintenanceChart"></canvas>
                    </div>
                </div>

                <!-- Top Maintenance Technicians -->
                <div class="table-card p-6" style="margin-bottom: 0;">
                    <h4
                        style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-trophy text-amber-500"></i> Top Teknisi Maintenance (Bulan Ini)
                    </h4>
                    <div
                        style="height: 300px; position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        @if (count($mDonutValues) > 0)
                            <canvas id="mTeknisiChart"></canvas>
                        @else
                            <div class="text-center py-12">
                                <i class="fa-solid fa-chart-pie text-slate-200 text-5xl mb-3"></i>
                                <p class="text-slate-400 font-bold text-xs uppercase">Belum ada data</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Dismantle Stats -->
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                <!-- Dismantle Trend -->
                <div class="table-card p-6" style="margin-bottom: 0;">
                    <h4
                        style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-chart-line text-sky-500"></i> Tren Dismantle (Bulan Ini)
                    </h4>
                    <div style="height: 300px; position: relative;">
                        <canvas id="dismantleChart"></canvas>
                    </div>
                </div>

                <!-- Top Dismantle Technicians -->
                <div class="table-card p-6" style="margin-bottom: 0;">
                    <h4
                        style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-trophy text-amber-500"></i> Top Teknisi Dismantle (Bulan Ini)
                    </h4>
                    <div
                        style="height: 300px; position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        @if (count($dDonutValues) > 0)
                            <canvas id="dTeknisiChart"></canvas>
                        @else
                            <div class="text-center py-12">
                                <i class="fa-solid fa-chart-pie text-slate-200 text-5xl mb-3"></i>
                                <p class="text-slate-400 font-bold text-xs uppercase">Belum ada data</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($user->jabatan === 'Administrator')
        <div style="margin-top: 2rem;">
            <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 1rem;">
                <i class="fa-solid fa-person-walking text-indigo-500 mr-2"></i> Aktivitas Terbaru
            </h3>

            <!-- Custom Toolbar -->
            <div class="table-toolbar">
                <div class="search-wrap">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="search" class="search-input" id="custom-search"
                        placeholder="Cari aktivitas, user, atau deskripsi..." autocomplete="off" />
                </div>
                <div style="display:flex;gap:8px;">
                    <div
                        style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        Log Sistem
                    </div>
                </div>
            </div>

            <div class="table-card">
                <div class="table-responsive">
                    <table id="activity-log-table" class="admin-table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 50px;">#</th>
                                <th style="text-align: center; width: 180px;">Waktu</th>
                                <th style="text-align: center; width: 220px;">User</th>
                                <th style="text-align: center; width: 160px;">Aktivitas</th>
                                <th style="text-align: center;">Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                        {{ $loop->iteration }}</td>
                                    <td style="text-align: center; color: #64748b; font-size: 12px; font-weight: 600;">
                                        {{ $log->logged_at->format('d-m-Y H:i:s') }}
                                    </td>
                                    <td style="font-weight: 600;">
                                        <div
                                            style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                                            <div
                                                style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9, #7c3aed); display: flex; align-items: center; justify-content: center; font-size: 11px; color: #fff; font-weight: 800; flex-shrink: 0;">
                                                {{ strtoupper(substr($log->user->nama_lengkap ?? 'U', 0, 1)) }}
                                            </div>
                                            <span class="truncate">{{ $log->user->nama_lengkap ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align: center">
                                        <span class="badge"
                                            style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 10px;">
                                            {{ $log->activity_type }}
                                        </span>
                                    </td>
                                    <td style="text-align: center; font-size: 13px;">{{ $log->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 4rem 2rem;">
                                        <i class="fa-solid fa-inbox"
                                            style="font-size: 3rem; color: #e2e8f0; margin-bottom: 1rem; display: block;"></i>
                                        <p style="font-weight: 600; color: #94a3b8; margin: 0;">Belum ada aktivitas
                                            yang tercatat.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if (Auth::user()->jabatan === 'Teknisi')
        <div style="margin-top: 2rem;">
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-list-check text-primary"></i> Tugas Delegasi Khusus Anda
            </h3>
            <p style="font-size: 13px; color: #64748b; margin-bottom: 1.5rem;">Daftar Pre-Order yang ditugaskan kepada
                Anda dan belum diaktivasi.</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                @forelse ($delegatedTasks as $task)
                    <div class="stat-card"
                        style="flex-direction: column; align-items: stretch; gap: 0; padding: 0; overflow: hidden;">
                        <div
                            style="padding: 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: flex-start;">
                            <div style="min-width: 0;">
                                <h4 style="font-size: 14px; font-weight: 700; margin: 0; text-transform: uppercase;">
                                    {{ Str::limit($task->nama_lengkap, 25) }}</h4>
                                <span
                                    class="badge {{ $task->bg_color === 'bg-danger' ? 'badge-archived' : 'badge-published' }}"
                                    style="margin-top: 0.5rem; font-size: 9px;">
                                    {{ $task->status_display }}
                                </span>
                            </div>
                            <div
                                style="width: 36px; height: 36px; border-radius: 10px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b;">
                                <i class="fa-solid fa-user-gear"></i>
                            </div>
                        </div>
                        <div style="padding: 1.25rem; font-size: 13px;">
                            <div style="display: flex; gap: 8px; margin-bottom: 0.75rem;">
                                <i class="fa-solid fa-wifi" style="width: 16px; color: #94a3b8;"></i>
                                <span>Paket: <strong>{{ $task->paket_internet }}</strong></span>
                            </div>
                            <div style="display: flex; gap: 8px; margin-bottom: 0.75rem;">
                                <i class="fa-solid fa-map-location-dot" style="width: 16px; color: #94a3b8;"></i>
                                <span>{{ Str::limit($task->alamat_lengkap, 35) }}</span>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <i class="fa-brands fa-whatsapp" style="width: 16px; color: #10b981;"></i>
                                <span style="font-family: monospace;">{{ $task->no_whatsapp }}</span>
                            </div>
                        </div>
                        <div
                            style="padding: 1rem 1.25rem; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-size: 10px; color: #94a3b8;">
                                Deadline: <strong style="color: #ef4444;">{{ $task->deadline_date ?? 'N/A' }}</strong>
                            </div>
                            <a href="{{ route('customers.index') }}"
                                class="btn-primary-admin !h-9 !px-3 !text-[11px] activate-btn !flex !items-center !justify-center gap-1"
                                style="padding: 6px 12px; font-size: 11px;">
                                Detail Tugas <i class="fa-solid fa-arrow-right ml-1" style="font-size: 10px;"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div
                        style="grid-column: 1 / -1; padding: 3rem; background: #fff; border-radius: 14px; text-align: center; border: 2px dashed #e2e8f0;">
                        <i class="fa-solid fa-circle-check"
                            style="font-size: 3rem; color: #dcfce7; margin-bottom: 1rem; display: block;"></i>
                        <p style="font-weight: 600; color: #94a3b8;">Tidak ada tugas delegasi khusus saat ini.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

        <script>
            // Register DataLabels Plugin
            Chart.register(ChartDataLabels);

            $(document).ready(function() {
                const table = $('#activity-log-table').DataTable({
                    "pageLength": 10,
                    "lengthChange": false,
                    "ordering": false,
                    "info": false,
                    "responsive": true,
                    "dom": 'tpir', // Hide default search box
                    "language": {
                        "zeroRecords": "Data tidak ditemukan",
                        "paginate": {
                            "previous": '<i class="fa-solid fa-chevron-left"></i>',
                            "next": '<i class="fa-solid fa-chevron-right"></i>'
                        }
                    }
                });

                // Link custom search input to DataTables
                $('#custom-search').on('keyup', function() {
                    table.search(this.value).draw();
                });

                @if ($user->jabatan === 'Administrator')
                    // Initialize Activation Trend Chart
                    const aCtx = document.getElementById('activationTrendChart').getContext('2d');
                    new Chart(aCtx, {
                        type: 'line',
                        data: {
                            labels: {!! json_encode($aChartLabels) !!},
                            datasets: [{
                                label: 'Total Aktivasi',
                                data: {!! json_encode($aChartValues) !!},
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                borderColor: '#22c55e',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#22c55e',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                datalabels: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: '#f1f5f9'
                                    },
                                    ticks: {
                                        stepSize: 1,
                                        color: '#94a3b8',
                                        font: {
                                            size: 11,
                                            weight: '600'
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        color: '#94a3b8',
                                        font: {
                                            size: 10,
                                            weight: '600'
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Initialize Top Activation Teknisi & Partner Horizontal Bar Chart
                    @if (count($aDonutValues) > 0)
                        const aDonutCtx = document.getElementById('activationTopTechChart').getContext('2d');
                        new Chart(aDonutCtx, {
                            type: 'bar',
                            data: {
                                labels: {!! json_encode($aDonutLabels) !!},
                                datasets: [{
                                    label: 'Total Aktivasi',
                                    data: {!! json_encode($aDonutValues) !!},
                                    backgroundColor: 'rgba(16, 185, 129, 0.15)',
                                    borderColor: '#10b981',
                                    borderWidth: 2,
                                    borderRadius: 6,
                                    barThickness: 20
                                }]
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'right',
                                        offset: 4,
                                        color: '#64748b',
                                        font: {
                                            weight: 'bold',
                                            size: 12
                                        },
                                        formatter: (value) => value
                                    }
                                },
                                scales: {
                                    x: {
                                        beginAtZero: true,
                                        grid: {
                                            color: '#f1f5f9'
                                        },
                                        ticks: {
                                            stepSize: 1,
                                            color: '#94a3b8',
                                            font: {
                                                size: 11,
                                                weight: '600'
                                            }
                                        }
                                    },
                                    y: {
                                        grid: {
                                            display: false
                                        },
                                        ticks: {
                                            color: '#475569',
                                            font: {
                                                size: 11,
                                                weight: '700'
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    @endif

                    // Initialize Top Sales Stacked Horizontal Bar Chart
                    @if (count($sChartLabels) > 0)
                        const sCtx = document.getElementById('topSalesChart').getContext('2d');
                        new Chart(sCtx, {
                            type: 'bar',
                            data: {
                                labels: {!! json_encode($sChartLabels) !!},
                                datasets: [{
                                        label: 'Sudah Aktivasi',
                                        data: {!! json_encode($sChartActivated) !!},
                                        backgroundColor: '#10b981',
                                        borderRadius: 4,
                                        barThickness: 25
                                    },
                                    {
                                        label: 'Masih Pending',
                                        data: {!! json_encode($sChartPending) !!},
                                        backgroundColor: '#f59e0b',
                                        borderRadius: 4,
                                        barThickness: 25
                                    }
                                ]
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            usePointStyle: true,
                                            padding: 20,
                                            font: {
                                                size: 12,
                                                weight: '600'
                                            }
                                        }
                                    },
                                    datalabels: {
                                        color: '#fff',
                                        font: {
                                            weight: 'bold',
                                            size: 11
                                        },
                                        formatter: (value) => value > 0 ? value : '',
                                        display: true
                                    }
                                },
                                scales: {
                                    x: {
                                        stacked: true,
                                        beginAtZero: true,
                                        grid: {
                                            color: '#f1f5f9'
                                        },
                                        ticks: {
                                            stepSize: 1,
                                            color: '#94a3b8',
                                            font: {
                                                size: 11,
                                                weight: '600'
                                            }
                                        }
                                    },
                                    y: {
                                        stacked: true,
                                        grid: {
                                            display: false
                                        },
                                        ticks: {
                                            color: '#475569',
                                            font: {
                                                size: 12,
                                                weight: '700'
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    @endif

                    // Initialize Maintenance Trend Chart
                    const mCtx = document.getElementById('maintenanceChart').getContext('2d');
                    new Chart(mCtx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($mChartLabels) !!},
                            datasets: [{
                                label: 'Total Maintenance',
                                data: {!! json_encode($mChartValues) !!},
                                backgroundColor: 'rgba(14, 165, 233, 0.15)',
                                borderColor: '#0ea5e9',
                                borderWidth: 1.5,
                                borderRadius: 4,
                                barThickness: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                datalabels: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: '#f1f5f9'
                                    },
                                    ticks: {
                                        stepSize: 1,
                                        color: '#94a3b8',
                                        font: {
                                            size: 10,
                                            weight: '600'
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        color: '#94a3b8',
                                        font: {
                                            size: 9,
                                            weight: '600'
                                        },
                                        maxRotation: 45,
                                        minRotation: 45,
                                        autoSkip: true,
                                        maxTicksLimit: 15
                                    }
                                }
                            }
                        }
                    });

                    // Initialize Top Maintenance Teknisi Pie Chart
                    @if (count($mDonutValues) > 0)
                        const mDonutCtx = document.getElementById('mTeknisiChart').getContext('2d');
                        new Chart(mDonutCtx, {
                            type: 'pie',
                            data: {
                                labels: {!! json_encode($mDonutLabels) !!},
                                datasets: [{
                                    data: {!! json_encode($mDonutValues) !!},
                                    backgroundColor: ['#0ea5e9', '#6366f1', '#8b5cf6', '#ec4899',
                                        '#f43f5e', '#f59e0b'
                                    ],
                                    borderWidth: 2,
                                    borderColor: '#fff',
                                    hoverOffset: 10
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            usePointStyle: true,
                                            padding: 20,
                                            font: {
                                                size: 11,
                                                weight: '600'
                                            },
                                            color: '#64748b'
                                        }
                                    },
                                    datalabels: {
                                        color: '#fff',
                                        font: {
                                            weight: 'bold',
                                            size: 13
                                        },
                                        formatter: (value) => value,
                                        display: true
                                    }
                                }
                            }
                        });
                    @endif

                    // Initialize Dismantle Trend Chart
                    const dCtx = document.getElementById('dismantleChart').getContext('2d');
                    new Chart(dCtx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($dChartLabels) !!},
                            datasets: [{
                                label: 'Total Dismantle',
                                data: {!! json_encode($dChartValues) !!},
                                backgroundColor: 'rgba(14, 165, 233, 0.15)',
                                borderColor: '#0ea5e9',
                                borderWidth: 1.5,
                                borderRadius: 4,
                                barThickness: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                datalabels: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: '#f1f5f9'
                                    },
                                    ticks: {
                                        stepSize: 1,
                                        color: '#94a3b8',
                                        font: {
                                            size: 10,
                                            weight: '600'
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        color: '#94a3b8',
                                        font: {
                                            size: 9,
                                            weight: '600'
                                        },
                                        maxRotation: 45,
                                        minRotation: 45,
                                        autoSkip: true,
                                        maxTicksLimit: 15
                                    }
                                }
                            }
                        }
                    });

                    // Initialize Top Dismantle Teknisi Pie Chart
                    @if (count($dDonutValues) > 0)
                        const dDonutCtx = document.getElementById('dTeknisiChart').getContext('2d');
                        new Chart(dDonutCtx, {
                            type: 'pie',
                            data: {
                                labels: {!! json_encode($dDonutLabels) !!},
                                datasets: [{
                                    data: {!! json_encode($dDonutValues) !!},
                                    backgroundColor: ['#0ea5e9', '#6366f1', '#8b5cf6', '#ec4899',
                                        '#f43f5e', '#f59e0b'
                                    ],
                                    borderWidth: 2,
                                    borderColor: '#fff',
                                    hoverOffset: 10
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            usePointStyle: true,
                                            padding: 20,
                                            font: {
                                                size: 11,
                                                weight: '600'
                                            },
                                            color: '#64748b'
                                        }
                                    },
                                    datalabels: {
                                        color: '#fff',
                                        font: {
                                            weight: 'bold',
                                            size: 13
                                        },
                                        formatter: (value) => value,
                                        display: true
                                    }
                                }
                            }
                        });
                    @endif
                @endif
            });
        </script>
    @endsection
</x-layouts.app>
