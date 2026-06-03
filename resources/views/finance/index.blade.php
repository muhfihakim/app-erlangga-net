<x-layouts.app>
    @section('Css')
        <style>
            /* Hakatech DataTables Customization */
            .dataTables_wrapper { padding: 0; }
            .dataTables_filter { display: none; }
            .dataTables_paginate {
                display: flex; justify-content: flex-end; padding: 1.25rem 1.5rem; gap: 4px; border-top: 1px solid #f1f5f9;
            }
            .paginate_button {
                width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid #e2e8f0;
                background: #fff; color: #64748b !important; font-size: 13px; font-weight: 600;
                display: inline-flex; align-items: center; justify-content: center; cursor: pointer;
            }
            .paginate_button.current { background: #0ea5e9; border-color: #0ea5e9; color: #fff !important; }

            /* Toolbar & Search */
            .table-toolbar {
                background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
                padding: 1rem 1.25rem; display: flex; align-items: center;
                justify-content: space-between; gap: 12px; flex-wrap: wrap;
                margin-bottom: 1rem; box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }
            .search-wrap { position: relative; flex: 1; min-width: 200px; max-width: 360px; }
            .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px; pointer-events: none; }
            .search-input {
                width: 100%; padding: 9px 12px 9px 36px; border: 2px solid #e2e8f0;
                border-radius: 10px; font-size: 13px; outline: none; background: #f8fafc;
            }
            .search-input:focus { border-color: #0ea5e9; background: #fff; }

            .filter-group { display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; }
            .form-label-custom { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 0.025em; }
            .form-input-custom { padding: 8px 12px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 13px; font-weight: 600; color: #334155; background: #f8fafc; outline: none; height: 40px; }

            /* Full Capitalization for Table */
            #finance-table thead th, #finance-table tbody td {
                text-transform: uppercase !important; font-weight: 600; font-size: 12px;
            }
        </style>
    @endsection

    <!-- Page header -->
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Pendapatan Teknisi</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Laporan estimasi pendapatan teknisi dari aktivasi
                pelanggan.</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if ($isFiltered)
                <a href="{{ route('finance.export', ['start_date' => $start, 'end_date' => $end, 'teknisi_id' => $teknisiId, 'type' => 'pdf']) }}"
                    class="btn-primary-admin !flex !items-center !justify-center gap-2 !bg-rose-600">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            @endif
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <div class="stat-val">{{ $customers->count() }}</div>
                <div class="stat-lbl">Total Aktivasi</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div>
                <div class="stat-val">Rp {{ number_format($baseFee, 0, ',', '.') }}</div>
                <div class="stat-lbl">Tarif Dasar / Aktivasi</div>
            </div>
        </div>

        <div class="stat-card" style="grid-column: span 2;">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <div class="stat-val" style="color: #d97706;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                </div>
                <div class="stat-lbl">Estimasi Pendapatan {{ $teknisiId ? 'Teknisi' : 'Keseluruhan' }}</div>
            </div>
        </div>
    </div>

    <!-- Team Size Stats -->
    <div class="stats-grid mb-6" style="grid-template-columns: repeat(4, 1fr);">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0f9ff;color:#0ea5e9;">
                <i class="fa-solid fa-user"></i>
            </div>
            <div>
                <div class="stat-val" style="font-size: 18px;">{{ $stats['solo'] }} Kali</div>
                <div class="stat-lbl">Aktivasi Sendiri</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5;color:#10b981;">
                <i class="fa-solid fa-user-group"></i>
            </div>
            <div>
                <div class="stat-val" style="font-size: 18px;">{{ $stats['pair'] }} Kali</div>
                <div class="stat-lbl">Aktivasi Berdua</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fefce8;color:#eab308;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="stat-val" style="font-size: 18px;">{{ $stats['triple'] }} Kali</div>
                <div class="stat-lbl">Aktivasi Bertiga</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff1f2;color:#f43f5e;">
                <i class="fa-solid fa-users-viewfinder"></i>
            </div>
            <div>
                <div class="stat-val" style="font-size: 18px;">{{ $stats['quad'] + $stats['more'] }} Kali</div>
                <div class="stat-lbl">Berempat atau Lebih</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Form -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('finance.index') }}" class="w-full">
            <div class="filter-group">
                <!-- Filter Teknisi -->
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label-custom">Teknisi</label>
                    <select name="teknisi_id" class="form-input-custom w-full">
                        <option value="">-- Semua --</option>
                        @foreach ($teknisis as $t)
                            <option value="{{ $t->id }}" {{ $teknisiId == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Periode -->
                <div style="width: 160px;">
                    <label class="form-label-custom">Mulai</label>
                    <input type="date" name="start_date" class="form-input-custom w-full"
                        value="{{ $start }}">
                </div>
                <div style="width: 160px;">
                    <label class="form-label-custom">Selesai</label>
                    <input type="date" name="end_date" class="form-input-custom w-full" value="{{ $end }}">
                </div>

                <!-- Live Search -->
                <div style="flex: 1.5; min-width: 200px;">
                    <label class="form-label-custom">Pencarian</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input" id="custom-search"
                            style="padding: 9px 12px 9px 36px; height: 40px; border-radius: 10px; font-weight: 600;"
                            placeholder="Cari pelanggan..." autocomplete="off" />
                    </div>
                </div>

                <!-- Button -->
                <button type="submit"
                    class="btn-primary-admin !h-10 !px-6 !text-[13px] !w-fit flex items-center gap-2">
                    <i class="fa-solid fa-filter"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="finance-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: center; width: 120px;">Tgl Aktivasi</th>
                        <th style="text-align: center; width: 200px;">Pelanggan</th>
                        <th style="text-align: center; width: 200px;">Tim Teknisi</th>
                        <th style="text-align: center; width: 100px;">Total Tim</th>
                        <th style="text-align: center; width: 150px;">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $c)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                {{ $loop->iteration }}
                            </td>
                            <td style="text-align: center; color: #64748b;">
                                {{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y') }}
                            </td>
                            <td style="text-align: left;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $c->nama_lengkap }}</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                    ID: {{ $c->id_pelanggan }}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <div style="font-weight: 600; color: #475569;">
                                    {{ $c->teknisiUtama->nama_lengkap ?? 'Unknown' }}
                                </div>
                                @php
                                    $partners = is_array($c->partner_ids)
                                        ? $c->partner_ids
                                        : json_decode($c->partner_ids, true);
                                    $partnerNames = [];
                                    if (is_array($partners) && count($partners) > 0) {
                                        foreach ($partners as $pid) {
                                            $partnerUser = \App\Models\User::find($pid);
                                            if ($partnerUser) {
                                                $partnerNames[] = $partnerUser->nama_lengkap;
                                            }
                                        }
                                    }
                                @endphp
                                @if (count($partnerNames) > 0)
                                    <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">
                                        Partner: {{ implode(', ', $partnerNames) }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center; font-weight: 700; color: #0ea5e9;">
                                {{ $c->total_techs }} Orang
                            </td>
                            <td style="text-align: center;">
                                @if ($c->calculated_fee > 0)
                                    <div style="font-weight: 800; color: #10b981;">
                                        Rp {{ number_format($c->calculated_fee, 0, ',', '.') }}
                                    </div>
                                    <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">
                                        (Total: Rp {{ number_format($baseFee, 0, ',', '.') }})
                                    </div>
                                @else
                                    <div style="font-weight: 800; color: #94a3b8;">-</div>
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
                const table = $('#finance-table').DataTable({
                    "pageLength": 10,
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
                    table.search(this.value).draw();
                });
            });
        </script>
    @endsection
</x-layouts.app>
