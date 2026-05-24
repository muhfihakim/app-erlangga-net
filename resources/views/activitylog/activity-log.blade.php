<x-layouts.app>
    @section('Css')
        <style>
            /* Toolbar & Search */
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

            /* Custom Pagination Styling */
            .pagination-container {
                padding: 1.5rem;
                border-top: 1px solid #f1f5f9;
                background: #fff;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
        </style>
    @endsection

    <!-- Page header -->
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Log Aktivitas Sistem</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Riwayat tindakan pengguna dan perubahan sistem.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="button" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                onclick="openCustomModal('exportLogModal')">
                <i class="fa-solid fa-file-pdf"></i> Export PDF
            </button>
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
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($totalLogs) }}</div>
                <div class="stat-lbl">Total Riwayat</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div>
                <div class="stat-val">{{ $logsToday }}</div>
                <div class="stat-lbl">Riwayat Hari Ini</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="fa-solid fa-cart-arrow-down"></i>
            </div>
            <div>
                <div class="stat-val">{{ $logsPreOrder }}</div>
                <div class="stat-lbl">Log Pre-Order</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#f0f9ff;color:#0ea5e9;">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <div class="stat-val">{{ $logsAktivasi }}</div>
                <div class="stat-lbl">Log Aktivasi</div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input" id="custom-search"
                placeholder="Cari user, tipe aktivitas, atau deskripsi..." autocomplete="off" />
        </div>
        <div style="display:flex;gap:8px;">
            <div
                style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                Log Terbaru
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="activity-log-table" class="admin-table">
            <thead>
                <tr>
                    <th style="text-align: center; width: 180px;">Waktu</th>
                    <th style="text-align: center; width: 220px;">Pelaku</th>
                    <th style="text-align: center; width: 150px;">Aktivitas</th>
                    <th style="text-align: center; width: auto;">Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td style="text-align: center; font-size: 12px; color: #64748b; font-weight: 600;">
                            <div class="text-slate-900 font-bold">{{ $log->logged_at->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-wider">{{ $log->logged_at->format('H:i:s') }}</div>
                        </td>
                        <td style="text-align: left;">
                            <div style="font-weight: 700; color: #0f172a;">{{ $log->user->nama_lengkap ?? 'Sistem' }}</div>
                            <div style="font-size: 10px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                                {{ $log->user->jabatan ?? 'ROOT' }}
                            </div>
                        </td>
                        <td style="text-align: center;">
                            @php
                                $bg = '#f1f5f9'; $color = '#64748b';
                                if(str_contains($log->activity_type, 'create')) { $bg = '#f0fdf4'; $color = '#16a34a'; }
                                elseif(str_contains($log->activity_type, 'update')) { $bg = '#fffbeb'; $color = '#d97706'; }
                                elseif(str_contains($log->activity_type, 'delete')) { $bg = '#fef2f2'; $color = '#dc2626'; }
                                elseif(str_contains($log->activity_type, 'aktivasi')) { $bg = '#f0f9ff'; $color = '#0ea5e9'; }
                            @endphp
                            <span class="badge" style="background: {{ $bg }}; color: {{ $color }}; padding: 4px 10px; border-radius: 6px; font-size: 9px; font-weight: 800; text-transform: uppercase;">
                                {{ str_replace('_', ' ', $log->activity_type) }}
                            </span>
                        </td>
                        <td style="text-align: left; font-size: 12px; color: #475569; font-weight: 500; line-height: 1.5;">
                            {{ $log->description }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-12">
                            <i class="fa-solid fa-clock-rotate-left text-slate-200 text-5xl mb-3 block"></i>
                            <p class="text-slate-400 font-bold text-xs uppercase">Belum ada aktivitas yang tercatat</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </table>
            </div>
            </div>

            @include('partials.modal_activity-log')

            @section('Scripts')
            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

            <script>
            // Custom Modal Functions
            function openCustomModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
            }

            function closeCustomModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('open');
                document.body.style.overflow = '';
            }
            }

            $(document).ready(function() {
            const table = $('#activity-log-table').DataTable({
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
                    table.search(this.value).draw();
                });

                $('.modal-overlay').on('click', function(e) {
                    if (e.target === this) closeCustomModal(this.id);
                });
            });
        </script>
    @endsection
</x-layouts.app>
