<x-layouts.app>
    @section('Css')
        <style>
            #aktivasi-table td,
            .admin-table td {
                padding: 10px 14px;
                font-size: 13px;
                vertical-align: middle;
            }

            #aktivasi-table th,
            .admin-table th {
                padding: 11px 14px;
                font-size: 12px;
                font-weight: 700;
                color: #64748b;
                letter-spacing: 0.05em;
                text-transform: uppercase !important;
                vertical-align: middle;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
                gap: 0.75rem;
                margin-bottom: 1.25rem;
            }

            .stat-card {
                background: #fff;
                border-radius: 12px;
                padding: 0.75rem 1rem;
                border: 1px solid #e2e8f0;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
                display: flex;
                align-items: center;
                gap: 12px;
                min-height: 72px;
                transition: all 0.2s;
            }

            .stat-card:hover {
                border-color: #cbd5e1;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                min-width: 40px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
                flex-shrink: 0;
            }

            .stat-val {
                font-size: 17px;
                font-weight: 800;
                color: #0f172a;
                line-height: 1.25;
                display: flex;
                align-items: baseline;
                gap: 5px;
                flex-wrap: wrap;
            }

            .stat-lbl {
                font-size: 12px;
                font-weight: 600;
                color: #64748b;
                margin-top: 2px;
            }

            .dataTables_wrapper {
                position: relative;
                min-height: 200px;
                padding: 0;
            }

            .dataTables_filter {
                display: none;
            }

            .dataTables_paginate {
                display: flex;
                justify-content: flex-end;
                padding: 1.25rem 1.5rem;
                gap: 4px;
                border-top: 1px solid #f1f5f9;
            }

            .paginate_button {
                width: 34px;
                height: 34px;
                border-radius: 8px;
                border: 1.5px solid #e2e8f0;
                background: #fff;
                color: #64748b !important;
                font-size: 13px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
            }

            .paginate_button.current {
                background: #0ea5e9 !important;
                border-color: #0ea5e9 !important;
                color: #fff !important;
            }
        </style>
    @endsection

    <!-- Page Header -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Laporan Aktivasi (Sales)
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Data pemasangan baru dan kontribusi penjualan sales.</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if ($isFiltered)
                <a href="{{ route('activitylog.aktivasi_sales.export', ['start_date' => $start, 'end_date' => $end, 'sales_id' => $salesId]) }}"
                    class="btn-primary-admin !h-9 !px-4 text-xs font-semibold !flex !items-center !justify-center gap-1.5 !bg-rose-600 hover:!bg-rose-700 shadow-sm" target="_blank">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            @endif
        </div>
    </div>

    <!-- Overview Stats Cards -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-sky-700">{{ number_format($stats['total_filtered'], 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Sesuai Filter</div>
                <div class="stat-lbl truncate">Hasil Filter</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-emerald-700">{{ number_format($stats['this_month'], 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</div>
                <div class="stat-lbl truncate">Aktivasi Bulan Ini</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-amber-600">{{ number_format($stats['today'], 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}</div>
                <div class="stat-lbl truncate">Aktivasi Hari Ini</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9;color:#475569;">
                <i class="fa-solid fa-database"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-slate-700">{{ number_format($stats['total_all'], 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Akumulasi Total</div>
                <div class="stat-lbl truncate">Total Seluruh Data</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('activitylog.aktivasi_sales') }}" class="w-full">
            <div class="filter-group">
                <!-- Filter Sales -->
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label-custom">Sales</label>
                    <select name="sales_id" class="form-input-custom !h-[38px] text-xs w-full">
                        <option value="">-- Semua Sales --</option>
                        @foreach ($allSales as $s)
                            <option value="{{ $s->id }}" {{ $salesId == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Dari Tanggal -->
                <div style="width: 150px;">
                    <label class="form-label-custom">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-input-custom !h-[38px] text-xs w-full"
                        value="{{ $start }}">
                </div>

                <!-- Sampai Tanggal -->
                <div style="width: 150px;">
                    <label class="form-label-custom">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-input-custom !h-[38px] text-xs w-full"
                        value="{{ $end }}">
                </div>

                <!-- Live Search -->
                <div style="flex: 1.5; min-width: 200px;">
                    <label class="form-label-custom">Pencarian</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input !h-[38px] text-xs" id="custom-search"
                            placeholder="Cari data sales..." autocomplete="off" />
                    </div>
                </div>

                <!-- Action Filter -->
                <div class="flex items-end">
                    <button type="submit"
                        class="btn-primary-admin !h-[38px] !px-4 text-xs font-semibold flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card mt-4 mb-6">
        <div class="table-responsive">
            <table id="aktivasi-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th style="width: 140px; text-align: center;">Tgl. Aktivasi</th>
                        <th style="text-align: left;">Informasi Pelanggan</th>
                        <th style="width: 140px; text-align: center;">Paket</th>
                        <th style="width: 200px; text-align: left;">Sales</th>
                        <th style="width: 200px; text-align: left;">Teknisi Utama</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $c)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                            <td style="text-align: center;">
                                <div class="font-semibold text-slate-800">{{ optional($c->created_at)->format('d/m/Y') }}</div>
                                <div class="text-[11px] text-slate-400 font-medium">{{ optional($c->created_at)->format('H:i') }} WIB</div>
                            </td>
                            <td style="text-align: left;">
                                <div class="font-bold text-slate-900 text-[13px]">{{ $c->nama_lengkap }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">ID: {{ $c->id_pelanggan }}</div>
                            </td>
                            <td style="text-align: center;">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                    {{ $c->paket ? $c->paket->nama_paket : '-' }}
                                </span>
                            </td>
                            <td style="text-align: left;">
                                <div class="font-bold text-slate-800 text-[13px]">{{ $c->sales->nama_lengkap ?? 'N/A' }}</div>
                                <div class="text-[11px] text-slate-400 font-medium">Sales</div>
                            </td>
                            <td style="text-align: left;">
                                <div class="font-bold text-slate-800 text-[13px]">{{ $c->teknisiUtama->nama_lengkap ?? 'N/A' }}</div>
                                <div class="text-[11px] text-slate-400 font-medium">Teknisi Utama</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                const aktivasiTable = $('#aktivasi-table').DataTable({
                    "pageLength": 15,
                    "lengthChange": false,
                    "ordering": false,
                    "info": false,
                    "responsive": true,
                    "dom": 'tpir',
                    "language": {
                        "zeroRecords": "Data tidak ditemukan",
                        "paginate": {
                            "previous": '<i class="fa-solid fa-chevron-left"></i>',
                            "next": '<i class="fa-solid fa-chevron-right"></i>'
                        }
                    }
                });

                $('#custom-search').on('keyup', function() {
                    aktivasiTable.search(this.value).draw();
                });
            });
        </script>
    @endsection
</x-layouts.app>
