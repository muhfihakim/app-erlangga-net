<x-layouts.app>
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title flex items-center gap-2">
                Laporan Aktivasi
            </h1>
            <p class="page-subtitle">Data pemasangan baru dan aktivasi pelanggan.</p>
        </div>
        <div class="flex items-center gap-2">
            @if ($isFiltered)
                <a href="{{ route('activitylog.aktivasi.export', ['start_date' => $start, 'end_date' => $end, 'teknisi_id' => $teknisiId]) }}"
                    class="btn-primary-admin !h-8.5 !px-4 text-xs font-semibold !flex !items-center !justify-center gap-1.5 !bg-rose-600 hover:!bg-rose-700 shadow-sm" target="_blank">
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
        <form method="GET" action="{{ route('activitylog.aktivasi') }}" class="w-full">
            <div class="filter-group">
                <!-- Filter Teknisi -->
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label-custom">Teknisi</label>
                    <select name="teknisi_id" class="form-input-custom !h-[38px] text-xs w-full">
                        <option value="">-- Semua Teknisi --</option>
                        @foreach ($teknisis as $t)
                            <option value="{{ $t->id }}" {{ $teknisiId == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_lengkap }}
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
                            placeholder="Cari data aktivasi..." autocomplete="off" />
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
                        <th style="width: 130px; text-align: center;">Tgl. Aktivasi</th>
                        <th style="text-align: left;">Informasi Pelanggan</th>
                        <th style="width: 170px; text-align: left;">Paket Layanan</th>
                        <th style="width: 180px; text-align: left;">Teknisi Utama</th>
                        <th style="width: 180px; text-align: left;">Partner</th>
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
                            <td style="text-align: left;">
                                <div class="font-bold text-slate-800 text-[13px]">{{ $c->paket ? $c->paket->nama_paket : '-' }}</div>
                                @if ($c->paket && $c->paket->harga)
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">Rp {{ number_format($c->paket->harga, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td style="text-align: left;">
                                <div class="font-bold text-slate-800 text-[13px]">{{ $c->teknisiUtama->nama_lengkap ?? 'N/A' }}</div>
                                <div class="text-[11px] text-slate-400 font-medium">Teknisi Utama</div>
                            </td>
                            <td style="text-align: left; color: #475569; font-size: 13px;">
                                {{ $c->partner ?: '-' }}
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
