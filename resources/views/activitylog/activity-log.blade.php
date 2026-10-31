<x-layouts.app>
    @section('Css')
        <style>
            /* Hakatech DataTables Customization */
            .dataTables_wrapper { padding: 0; }
            .dataTables_filter { display: none; }
            .dataTables_paginate {
                display: flex; justify-content: flex-end; padding: 1rem 1.25rem; gap: 4px; border-top: 1px solid #f1f5f9;
            }
            .paginate_button {
                width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid #e2e8f0;
                background: #fff; color: #64748b !important; font-size: 12px; font-weight: 600;
                display: inline-flex; align-items: center; justify-content: center; cursor: pointer;
            }
            .paginate_button.current { background: #0ea5e9; border-color: #0ea5e9; color: #fff !important; }

            .dataTables_wrapper {
                position: relative;
                min-height: 200px;
            }
            .dataTables_wrapper .dataTables_processing {
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                background: rgba(255, 255, 255, 0.95) !important;
                padding: 12px 20px !important;
                border-radius: 12px !important;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
                z-index: 50 !important;
                margin: 0 !important;
                border: 1px solid #e2e8f0 !important;
                width: auto !important;
                height: auto !important;
                text-align: center;
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
        <div style="display:flex; gap:8px;">
            <button type="button" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold"
                onclick="openCustomModal('exportLogModal')">
                Export PDF
            </button>
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
                        <th style="text-align: center; width: 140px;">Waktu</th>
                        <th style="text-align: left; width: 220px;">Pelaku</th>
                        <th style="text-align: center; width: 140px;">Aktivitas</th>
                        <th style="text-align: left;">Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
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
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('activitylog.index') }}',
                    columns: [
                        { data: 'logged_at', name: 'logged_at', className: 'text-center' },
                        { data: 'user', name: 'user.nama_lengkap', className: 'text-left' },
                        { data: 'activity_type', name: 'activity_type', className: 'text-center' },
                        { data: 'description', name: 'description', className: 'text-left' }
                    ],
                    order: [[0, 'desc']], // Default order by logged_at desc
                    "pageLength": 15,
                    "lengthChange": false,
                    "ordering": false,
                    "info": false,
                    "responsive": true,
                    "dom": 'tpir',
                    "language": {
                        "processing": "<i class='fa-solid fa-circle-notch fa-spin text-sky-500 text-2xl'></i>",
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