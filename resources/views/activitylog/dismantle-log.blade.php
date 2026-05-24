<x-layouts.app>
    @section('Css')
        <style>
            /* Toolbar & Search */
            .table-toolbar {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1.25rem;
                margin-bottom: 1rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }

            .filter-group {
                display: flex;
                align-items: flex-end;
                gap: 12px;
                flex-wrap: wrap;
            }

            .form-label-custom {
                font-size: 11px;
                font-weight: 800;
                color: #64748b;
                text-transform: uppercase;
                margin-bottom: 6px;
                display: block;
                letter-spacing: 0.025em;
            }

            .form-input-custom {
                padding: 8px 12px;
                border: 2px solid #e2e8f0;
                border-radius: 10px;
                font-size: 13px;
                font-weight: 600;
                color: #334155;
                background: #f8fafc;
                outline: none;
                height: 40px;
            }
        </style>
    @endsection

    <!-- Page header -->
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Laporan Dismantle</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Riwayat pencabutan perangkat dan pemutusan layanan.</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if ($start && $end)
                <a href="{{ route('activitylog.dismantle.export', ['start_date' => $start, 'end_date' => $end, 'teknisi_id' => $teknisiId]) }}"
                    class="btn-primary-admin !flex !items-center !justify-center gap-2 !bg-rose-600">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            @endif
            <a href="{{ route('dashboards.index') }}" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                style="background: #f1f5f9; color: #475569; box-shadow: none; border: 1px solid #e2e8f0;"
                onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
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
            <div class="stat-icon" style="background:#fef2f2;color:#dc2626;">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['this_month'] }}</div>
                <div class="stat-lbl">Dismantle Bulan Ini</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['today'] }}</div>
                <div class="stat-lbl">Dismantle Hari Ini</div>
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
        <form method="GET" action="{{ route('activitylog.dismantle') }}" class="w-full">
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
            <table id="dismantleTable" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: center; width: 160px;">Tgl. Selesai</th>
                        <th style="text-align: center; width: 220px;">Informasi Pelanggan</th>
                        <th style="text-align: center; width: 180px;">Teknisi Eksekutor</th>
                        <th style="text-align: center; width: 120px;">Dokumentasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dismantles as $d)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                            <td style="text-align: center; font-size: 12px; font-weight: 700; color: #0f172a;">
                                {{ $d->completed_at->format('d/m/Y') }}
                                <div style="font-size: 10px; color: #94a3b8; font-weight: 600;">{{ $d->completed_at->format('H:i') }} WIB</div>
                            </td>
                            <td style="text-align: left;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $d->customer->nama_lengkap ?? 'Data Terhapus' }}</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                    <i class="fa-solid fa-hashtag mr-1"></i> ID: {{ $d->customer->id_pelanggan ?? '-' }}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <div style="font-weight: 700; color: #475569; font-size: 12px;">{{ $d->teknisiEksekutor->nama_lengkap ?? 'N/A' }}</div>
                                <div style="font-size: 10px; color: #94a3b8; font-weight: 600;">EKSEKUTOR</div>
                            </td>
                            <td style="text-align: center;">
                                @if($d->foto_pencabutan)
                                    <a href="{{ asset('storage/' . $d->foto_pencabutan) }}" target="_blank" 
                                       class="topbar-icon-btn !w-9 !h-9 !rounded-xl mx-auto flex items-center justify-center" title="Lihat Foto">
                                        <i class="fa-solid fa-image text-primary"></i>
                                    </a>
                                @else
                                    <span class="text-slate-300 text-[10px] font-bold">NO IMAGE</span>
                                @endif
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
                $('#dismantleTable').DataTable({
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
