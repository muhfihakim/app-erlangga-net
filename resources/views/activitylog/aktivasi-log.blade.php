<x-layouts.app>
    @section('Css')
        <style>
            /* Full Capitalization for Table */
            #aktivasi-table thead th,
            #aktivasi-table tbody td {
                text-transform: uppercase !important;
                font-weight: 600;
            }

            #aktivasi-table tbody td {
                font-size: 12px;
            }
        </style>
    @endsection

    <!-- Page header -->
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Laporan Aktivasi</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Data pemasangan baru dan aktivasi pelanggan.</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if ($isFiltered)
                <a href="{{ route('activitylog.aktivasi.export', ['start_date' => $start, 'end_date' => $end, 'teknisi_id' => $teknisiId]) }}"
                    class="btn-primary-admin !flex !items-center !justify-center gap-2 !bg-rose-600">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            @endif
        </div>
    </div>

    <!-- Stats cards -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['total_filtered'] }}</div>
                <div class="stat-lbl">Hasil Filter</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['this_month'] }}</div>
                <div class="stat-lbl">Aktivasi Bulan Ini</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['today'] }}</div>
                <div class="stat-lbl">Aktivasi Hari Ini</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9;color:#475569;">
                <i class="fa-solid fa-database"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['total_all'] }}</div>
                <div class="stat-lbl">Total Seluruh Data</div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('activitylog.aktivasi') }}" class="w-full">
            <div class="filter-group">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label-custom">Teknisi</label>
                    <select name="teknisi_id" class="form-input-custom w-full">
                        <option value="">-- Semua Teknisi --</option>
                        @foreach ($teknisis as $t)
                            <option value="{{ $t->id }}" {{ $teknisiId == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="width: 180px;">
                    <label class="form-label-custom">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-input-custom w-full" value="{{ $start }}">
                </div>
                <div style="width: 180px;">
                    <label class="form-label-custom">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-input-custom w-full" value="{{ $end }}">
                </div>
                <button type="submit" class="btn-primary-admin !h-10 !px-6 !text-[13px] !w-fit flex items-center gap-2">
                    <i class="fa-solid fa-filter"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="aktivasi-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: center; width: 160px;">Tgl. Aktivasi</th>
                        <th style="text-align: center; width: 220px;">Informasi Pelanggan</th>
                        <th style="text-align: center; width: 120px;">Paket</th>
                        <th style="text-align: center; width: 200px;">Teknisi Utama</th>
                        <th style="text-align: center; width: 180px;">Partner</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $c)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                            <td style="text-align: center; font-size: 12px; font-weight: 700; color: #0f172a;">
                                {{ optional($c->created_at)->format('d/m/Y') }}
                                <div style="font-size: 10px; color: #94a3b8; font-weight: 600;">{{ optional($c->created_at)->format('H:i') }} WIB</div>
                            </td>
                            <td style="text-align: left;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $c->nama_lengkap }}</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                    <i class="fa-solid fa-hashtag mr-1"></i> ID: {{ $c->id_pelanggan }}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: #f0f9ff; color: #0ea5e9; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800;">
                                    {{ $c->paket_internet }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="font-weight: 700; color: #475569; font-size: 12px;">{{ $c->teknisiUtama->nama_lengkap ?? 'N/A' }}</div>
                                <div style="font-size: 10px; color: #94a3b8; font-weight: 600;">TEKNISI UTAMA</div>
                            </td>
                            <td style="text-align: center; font-size: 11px; color: #64748b; font-weight: 600;">
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
                $('#aktivasi-table').DataTable({
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
            });
        </script>
    @endsection
</x-layouts.app>
