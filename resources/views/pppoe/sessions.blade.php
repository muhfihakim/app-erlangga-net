<x-layouts.app>
    @section('Css')
        <style>
            #session-table thead th, #modalHistoryTable thead th {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 12px;
            }

            #session-table tbody td, #modalHistoryTable tbody td {
                font-size: 12px;
                white-space: nowrap;
            }

            .pppoe-username-val {
                text-transform: none !important;
            }

            .dataTables_wrapper {
                position: relative;
                min-height: 200px;
                padding: 0;
            }

            .dataTables_wrapper .dataTables_processing {
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                background: rgba(255, 255, 255, 0.95) !important;
                padding: 15px 25px !important;
                border-radius: 12px !important;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important;
                z-index: 50 !important;
                margin: 0 !important;
                border: 1px solid #e2e8f0 !important;
                width: auto !important;
                height: auto !important;
                text-align: center;
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
                text-decoration: none !important;
            }

            .paginate_button:hover:not(.disabled) {
                background: #f8fafc;
                border-color: #cbd5e1;
                color: #0f172a !important;
            }

            .paginate_button.current {
                background: #0ea5e9 !important;
                border-color: #0ea5e9 !important;
                color: #fff !important;
            }

            .paginate_button.disabled {
                opacity: 0.4;
                cursor: not-allowed;
            }

            .modal-scrollable-table {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
                width: 100%;
                display: block;
            }
        </style>
    @endsection

    <!-- Header Section -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Monitoring Sesi PPPoE
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">
                Status online/offline real-time dan riwayat koneksi sesi akun PPPoE.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="reloadTable()" class="btn-cancel !h-8.5 !px-3.5 text-xs font-semibold flex items-center gap-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-xs">
                <i class="fa-solid fa-arrows-rotate text-slate-500" id="refreshIcon"></i> Refresh
            </button>
            <a href="{{ route('pppoe.index') }}" class="btn-cancel !h-8.5 !px-3.5 text-xs font-semibold flex items-center gap-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-xs">
                <i class="fa-solid fa-network-wired text-emerald-600"></i> PPPoE Aktif
            </a>
            @if (auth()->user() && auth()->user()->isAdmin())
                <a href="{{ route('pppoe_accounts.index') }}" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !h-8.5 !px-3.5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-users-gear"></i> Daftar Akun PPPoE
                </a>
            @endif
        </div>
    </div>

    <!-- Stats Cards Grid (3 Kartu Kompak) -->
    <div class="stats-grid mb-5 grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0e7ff;color:#4338ca;">
                <i class="fa-solid fa-users-gear"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($stats['total_pppoe'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Total Akun PPPoE</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-wifi"></i>
            </div>
            <div>
                <div class="stat-val text-emerald-600">{{ number_format($stats['online'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Sesi Online Aktif</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <div class="stat-val text-sky-700">{{ number_format($stats['active24h'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Aktivitas 24 Jam</div>
            </div>
        </div>
    </div>

    <!-- Table Toolbar -->
    <div class="table-toolbar">
        <div class="filter-group">
            <div style="flex: 1; min-width: 160px;">
                <label class="form-label-custom">Status Sesi</label>
                <select class="form-input-custom !h-8.5 !text-xs" id="filter-status">
                    <option value="">Semua Status</option>
                    <option value="online" {{ ($filterStatus ?? '') === 'online' ? 'selected' : '' }}>Online (Aktif)</option>
                    <option value="offline" {{ ($filterStatus ?? '') === 'offline' ? 'selected' : '' }}>Offline</option>
                </select>
            </div>
            <div style="flex: 1.5; min-width: 200px;">
                <label class="form-label-custom">Paket Layanan</label>
                <select class="form-input-custom !h-8.5 !text-xs" id="filter-paket">
                    <option value="">Semua Paket</option>
                    @foreach ($pakets as $p)
                        <option value="{{ $p->id }}" {{ ($filterPaket ?? '') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama_paket }} (Rp {{ number_format($p->harga, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 2; min-width: 240px;">
                <label class="form-label-custom">Pencarian</label>
                <div class="search-wrap !max-w-full">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="search" class="search-input custom-search !h-8.5 !text-xs" id="custom-search"
                        placeholder="Cari username, nama, no WA, IP..." autocomplete="off" />
                </div>
            </div>
            <button type="button" onclick="resetFilters()" class="btn-cancel !h-8.5 !px-3.5 text-xs font-semibold flex items-center gap-1.5 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 shadow-xs" title="Reset Filter">
                <i class="fa-solid fa-rotate-left"></i> Reset
            </button>
        </div>
    </div>

    <!-- Table Card (Tanpa Kolom MAC ONT di Halaman Utama) -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="session-table" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: left;">Pelanggan</th>
                        <th style="text-align: left; width: 190px;">Username</th>
                        <th style="text-align: left; width: 150px;">Paket</th>
                        <th style="text-align: center; width: 100px;">Status Sesi</th>
                        <th style="text-align: center; width: 130px;">IP Address</th>
                        <th style="text-align: center; width: 120px;">Uptime</th>
                        <th style="text-align: center; width: 120px;">Traffic (DL/UL)</th>
                        <th style="text-align: center; width: 95px;">Opsi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Modal Detail Lengkap & Riwayat Sesi -->
    <div class="modal-overlay" id="accountDetailModal">
        <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-200 shrink-0">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Detail Sesi & Riwayat PPPoE</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5" id="modalAccountSubtitle">Memuat parameter sesi dan riwayat...</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('accountDetailModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                <!-- Loading State -->
                <div id="modalDetailLoading" class="py-12 text-center text-slate-400">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-sky-500 mb-2"></i>
                    <p class="text-xs font-semibold text-slate-600">Mengambil data sesi dan riwayat koneksi...</p>
                </div>

                <!-- Content Container -->
                <div id="modalDetailContent" class="space-y-5 hidden">
                    <!-- 1. Customer Information Box -->
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                Profil Pelanggan
                            </span>
                            <span id="modalCustomerPaket" class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700; font-size:11px; padding:2px 8px; border-radius:6px;">
                                -
                            </span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Nama Pelanggan</span>
                                <span class="font-bold text-slate-800 text-sm" id="modalCustomerName">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">ID Pelanggan</span>
                                <span class="font-mono font-semibold text-slate-800" id="modalCustomerId">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">No WhatsApp</span>
                                <span class="font-medium text-slate-800" id="modalCustomerPhone">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Username PPPoE</span>
                                <span class="font-mono font-semibold text-slate-800" id="modalPppoeUsername">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Session Telemetry (Detail Sesi Terkini) -->
                    <div class="border border-slate-200 rounded-xl p-4" id="sessionTelemetryCard">
                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                Telemetri Sesi Terkini
                            </span>
                            <span id="modalSessionStatusBadge"></span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Acct-Session-Id</span>
                                <span class="font-mono text-slate-700 font-semibold" id="modalSessionId">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Framed IP (Client)</span>
                                <span class="font-mono text-sky-700 font-bold" id="modalFramedIp">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">MAC Address (ONT)</span>
                                <span class="font-mono text-slate-700 font-semibold" id="modalMac">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">NAS IP Address</span>
                                <span class="font-mono text-slate-700 font-semibold" id="modalNasIp">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">NAS Port & Tipe</span>
                                <span class="text-slate-700 font-medium" id="modalNasPort">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Called Station ID</span>
                                <span class="text-slate-700 font-medium" id="modalCalledStation">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Timestamps & Traffic Volume -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="sessionMetricsCard">
                        <div class="border border-slate-200 rounded-xl p-3.5 space-y-1.5 text-xs">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Waktu Sesi Terkini</span>
                            <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                                <span class="text-slate-500">Mulai:</span>
                                <span class="font-semibold text-slate-800" id="modalStartTime">-</span>
                            </div>
                            <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                                <span class="text-slate-500">Update Terakhir:</span>
                                <span class="font-semibold text-slate-800" id="modalUpdateTime">-</span>
                            </div>
                            <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                                <span class="text-slate-500">Selesai:</span>
                                <span class="font-semibold text-slate-800" id="modalStopTime">-</span>
                            </div>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-slate-500 font-semibold">Durasi Sesi:</span>
                                <span class="font-bold text-sky-700 font-mono" id="modalDuration">-</span>
                            </div>
                        </div>

                        <div class="border border-slate-200 rounded-xl p-3.5 space-y-1.5 text-xs">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Volume Data Sesi Terkini</span>
                            <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                                <span class="text-slate-500 text-emerald-700 font-semibold"><i class="fa-solid fa-arrow-down text-xs"></i> Download (DL):</span>
                                <span class="font-bold font-mono text-emerald-700" id="modalDownload">-</span>
                            </div>
                            <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                                <span class="text-slate-500 text-sky-700 font-semibold"><i class="fa-solid fa-arrow-up text-xs"></i> Upload (UL):</span>
                                <span class="font-bold font-mono text-sky-700" id="modalUpload">-</span>
                            </div>
                            <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                                <span class="text-slate-500 font-semibold"><i class="fa-solid fa-right-left text-xs text-indigo-600"></i> Total DL/UL:</span>
                                <span class="font-bold font-mono text-indigo-700" id="modalTotalTraffic">-</span>
                            </div>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-slate-500 font-semibold">Alasan Putus:</span>
                                <span class="font-bold text-slate-700" id="modalCauseTitle">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Disconnect Explanation Notice -->
                    <div class="border border-slate-200 bg-slate-50 rounded-xl p-3 text-xs" id="modalCauseBox">
                        <div class="font-semibold text-slate-700 mb-0.5" id="modalCauseHeading">Keterangan Terminasi Sesi</div>
                        <p class="text-slate-600 m-0" id="modalCauseDesc">-</p>
                    </div>

                    <!-- 5. Bagian Riwayat Sesi PPPoE (Scrollable Horizontal) -->
                    <div class="space-y-2.5 pt-2 border-t border-slate-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-md bg-sky-50 text-sky-600 flex items-center justify-center text-xs border border-sky-100">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider m-0">
                                    Riwayat Sesi PPPoE
                                </h4>
                            </div>
                            <span class="text-xs text-slate-500" id="histCountLabel"></span>
                        </div>

                        <!-- History Table Container dengan Dukungan Geser Horizontal -->
                        <div class="modal-scrollable-table border border-slate-200 rounded-xl" id="historyTableContainer">
                            <table class="admin-table w-full" id="modalHistoryTable" style="min-width: 820px;">
                                <thead>
                                    <tr class="bg-slate-50">
                                        <th style="text-align: center; width: 45px;">#</th>
                                        <th style="text-align: center; width: 140px;">Waktu Mulai</th>
                                        <th style="text-align: center; width: 140px;">Waktu Berhenti</th>
                                        <th style="text-align: center; width: 95px;">Durasi Sesi</th>
                                        <th style="text-align: center; width: 110px;">Download (DL)</th>
                                        <th style="text-align: center; width: 110px;">Upload (UL)</th>
                                        <th style="text-align: center; width: 120px;">Total DL/UL</th>
                                        <th style="text-align: center; width: 130px;">IP Address</th>
                                    </tr>
                                </thead>
                                <tbody id="modalHistoryTbody">
                                    <!-- Populated via JS -->
                                </tbody>
                            </table>
                        </div>

                        <div id="modalHistoryEmpty" class="py-8 text-center text-slate-400 hidden bg-slate-50 border border-slate-200 rounded-xl">
                            <i class="fa-solid fa-inbox text-xl mb-1.5 text-slate-300"></i>
                            <p class="text-xs text-slate-500 m-0">Belum ada riwayat sesi koneksi tercatat untuk akun PPPoE ini.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
                <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5" onclick="closeCustomModal('accountDetailModal')">
                    <i class="fa-solid fa-xmark"></i> Tutup
                </button>
                <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5 border border-slate-300 bg-white hover:bg-slate-50" onclick="copyDetailSummary()">
                    <i class="fa-regular fa-copy text-slate-500"></i> Salin Ringkasan
                </button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            let sessionTable;
            let currentLoadedData = null;

            $(document).ready(function() {
                sessionTable = $('#session-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    lengthChange: false,
                    ordering: false,
                    info: false,
                    responsive: true,
                    dom: 'tpir',
                    ajax: {
                        url: "{{ route('pppoe_sessions.index') }}",
                        data: function(d) {
                            d.status = $('#filter-status').val();
                            d.paket_id = $('#filter-paket').val();
                        }
                    },
                    columns: [
                        {
                            data: 'no',
                            name: 'no',
                            orderable: false,
                            searchable: false,
                            className: 'text-center font-mono text-xs text-slate-400',
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: 'pelanggan',
                            name: 'pelanggan',
                            orderable: false,
                            searchable: true
                        },
                        {
                            data: 'username_col',
                            name: 'username',
                            orderable: false,
                            searchable: true
                        },
                        {
                            data: 'paket',
                            name: 'paket_id',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'status_badge',
                            name: 'status_badge',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'ip_address',
                            name: 'ip_address',
                            orderable: false,
                            searchable: true,
                            className: 'text-center'
                        },
                        {
                            data: 'uptime',
                            name: 'uptime',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'traffic',
                            name: 'traffic',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'opsi',
                            name: 'opsi',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        }
                    ],
                    language: {
                        processing: "<i class='fa-solid fa-circle-notch fa-spin text-sky-500 text-2xl'></i>",
                        zeroRecords: "Data tidak ditemukan",
                        paginate: {
                            previous: '<i class="fa-solid fa-chevron-left"></i>',
                            next: '<i class="fa-solid fa-chevron-right"></i>'
                        }
                    }
                });

                // Debounced Live Search
                let searchTimeout;
                $('#custom-search').on('keyup input', function() {
                    const val = this.value;
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        sessionTable.search(val).draw();
                    }, 350);
                });

                // Filter on select change
                $('#filter-status, #filter-paket').on('change', function() {
                    sessionTable.draw();
                });
            });

            function reloadTable() {
                const icon = document.getElementById('refreshIcon');
                if (icon) icon.classList.add('fa-spin');
                if (sessionTable) {
                    sessionTable.ajax.reload(function() {
                        if (icon) icon.classList.remove('fa-spin');
                    }, false);
                }
            }

            function resetFilters() {
                $('#filter-status').val('');
                $('#filter-paket').val('');
                $('#custom-search').val('');
                if (sessionTable) {
                    sessionTable.search('').draw();
                }
            }

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

            // Close on overlay click or Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeCustomModal('accountDetailModal');
                }
            });

            const accountModalEl = document.getElementById('accountDetailModal');
            if (accountModalEl) {
                accountModalEl.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeCustomModal('accountDetailModal');
                    }
                });
            }

            // Buka Modal Detail Lengkap & Riwayat Sesi
            function showAccountDetail(username, customerName) {
                openCustomModal('accountDetailModal');
                const loading = document.getElementById('modalDetailLoading');
                const content = document.getElementById('modalDetailContent');
                loading.classList.remove('hidden');
                content.classList.add('hidden');
                currentLoadedData = null;

                document.getElementById('modalAccountSubtitle').innerText = `${username} • ${customerName || 'PPPoE'}`;
                document.getElementById('modalPppoeUsername').innerText = username;
                document.getElementById('modalCustomerName').innerText = customerName || 'Akun Mandiri';
                document.getElementById('modalCustomerPhone').innerText = '-';
                document.getElementById('modalCustomerId').innerText = '-';
                document.getElementById('modalCustomerPaket').innerText = '-';

                fetch(`/pppoe-sessions/user-detail?username=${encodeURIComponent(username)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Gagal memuat detail akun');
                    return res.json();
                })
                .then(data => {
                    if (!data.success) throw new Error(data.message || 'Respon tidak valid');
                    currentLoadedData = data;

                    const c = data.customer;
                    const p = data.pppoe;
                    const s = data.latest_session;
                    const history = data.history || [];

                    // Profil Pelanggan
                    if (c) {
                        document.getElementById('modalCustomerName').innerText = c.nama_lengkap || 'Akun Mandiri';
                        document.getElementById('modalCustomerPhone').innerText = c.no_whatsapp || '-';
                        document.getElementById('modalCustomerId').innerText = c.id_pelanggan || '-';
                        document.getElementById('modalCustomerPaket').innerText = c.paket || '-';
                    }

                    // Telemetri Sesi Terkini
                    if (s) {
                        document.getElementById('modalSessionId').innerText = s.acctsessionid || '-';
                        document.getElementById('modalFramedIp').innerText = s.framedipaddress || (p ? p.ip_address : '-');
                        document.getElementById('modalMac').innerText = s.callingstationid || '-';
                        document.getElementById('modalNasIp').innerText = s.nasipaddress || '-';
                        document.getElementById('modalNasPort').innerText = `${s.nasportid || '-'} (${s.nasporttype || 'Ethernet'})`;
                        document.getElementById('modalCalledStation').innerText = s.calledstationid || '-';

                        document.getElementById('modalStartTime').innerText = s.start_formatted || '-';
                        document.getElementById('modalUpdateTime').innerText = s.update_formatted || '-';
                        document.getElementById('modalStopTime').innerText = s.stop_formatted || '-';
                        document.getElementById('modalDuration').innerText = s.formatted_duration || '0 detik';

                        document.getElementById('modalDownload').innerText = s.download_formatted || '0 B';
                        document.getElementById('modalUpload').innerText = s.upload_formatted || '0 B';
                        document.getElementById('modalTotalTraffic').innerText = s.total_formatted || '0 B';

                        const statusBadge = document.getElementById('modalSessionStatusBadge');
                        if (s.is_online) {
                            statusBadge.innerHTML = '<span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-size:11px; padding:2px 8px; border-radius:6px; font-weight:700;"><i class="fa-solid fa-circle text-[7px] mr-1 text-emerald-500"></i> ONLINE</span>';
                        } else {
                            statusBadge.innerHTML = '<span class="badge" style="background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; font-size:11px; padding:2px 8px; border-radius:6px; font-weight:600;">OFFLINE</span>';
                        }

                        document.getElementById('modalCauseTitle').innerText = s.acctterminatecause || (s.is_online ? 'Sedang Aktif' : '-');

                        const causeHeading = document.getElementById('modalCauseHeading');
                        const causeDesc = document.getElementById('modalCauseDesc');

                        if (s.is_online) {
                            causeHeading.innerText = 'Sesi Sedang Berlangsung (Online)';
                            causeDesc.innerText = 'Akun PPPoE saat ini sedang terhubung aktif ke internet.';
                        } else {
                            const cause = s.acctterminatecause || 'Tidak Tercatat';
                            if (cause === 'User-Request') {
                                causeHeading.innerText = 'User-Request (Koneksi Diputus Normal)';
                                causeDesc.innerText = 'Pelanggan mematikan router/modem atau merestart perangkat ONT secara mandiri.';
                            } else if (cause === 'Lost-Carrier') {
                                causeHeading.innerText = 'Lost-Carrier (Sinyal Terputus / LOS)';
                                causeDesc.innerText = 'Indikasi LOS merah: kabel fiber optik terputus, redaman drop, atau modem mati mendadak tanpa sinyal disconnect.';
                            } else if (cause === 'NAS-Reboot') {
                                causeHeading.innerText = 'NAS-Reboot (Router MikroTik Mulai Ulang)';
                                causeDesc.innerText = 'Router server MikroTik / BRAS reboot sehingga seluruh sesi dial ulang.';
                            } else if (cause === 'NAS-Request') {
                                causeHeading.innerText = 'NAS-Request (Diputus oleh Router)';
                                causeDesc.innerText = 'Sesi diputus oleh MikroTik (kick sesi, pergantian isolir, atau limit waktu).';
                            } else {
                                causeHeading.innerText = `Penyebab Terminasi: ${cause}`;
                                causeDesc.innerText = `Sesi terakhir selesai dengan kode status: ${cause}.`;
                            }
                        }
                    } else {
                        document.getElementById('modalSessionStatusBadge').innerHTML = '<span class="badge" style="background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; font-size:11px; padding:2px 8px; border-radius:6px; font-weight:600;">OFFLINE</span>';
                        document.getElementById('modalFramedIp').innerText = p ? (p.ip_address || '-') : '-';
                        document.getElementById('modalSessionId').innerText = '-';
                        document.getElementById('modalMac').innerText = '-';
                        document.getElementById('modalNasIp').innerText = '-';
                        document.getElementById('modalNasPort').innerText = '-';
                        document.getElementById('modalCalledStation').innerText = '-';
                        document.getElementById('modalStartTime').innerText = '-';
                        document.getElementById('modalUpdateTime').innerText = '-';
                        document.getElementById('modalStopTime').innerText = '-';
                        document.getElementById('modalDuration').innerText = '-';
                        document.getElementById('modalDownload').innerText = '-';
                        document.getElementById('modalUpload').innerText = '-';
                        document.getElementById('modalTotalTraffic').innerText = '-';
                        document.getElementById('modalCauseTitle').innerText = 'Belum Ada Sesi';
                        document.getElementById('modalCauseHeading').innerText = 'Tidak Ada Sesi Aktif';
                        document.getElementById('modalCauseDesc').innerText = 'Belum ada log sesi koneksi tercatat untuk akun ini.';
                    }

                    // 5. Riwayat Sesi (Di Bawah Detail)
                    const histCount = history.length;
                    document.getElementById('histCountLabel').innerText = histCount > 0 ? `${histCount} sesi terakhir` : '';

                    const tbody = document.getElementById('modalHistoryTbody');
                    const emptyBox = document.getElementById('modalHistoryEmpty');
                    const tableContainer = document.getElementById('historyTableContainer');

                    if (histCount === 0) {
                        tableContainer.classList.add('hidden');
                        emptyBox.classList.remove('hidden');
                    } else {
                        emptyBox.classList.add('hidden');
                        tableContainer.classList.remove('hidden');

                        let rows = '';
                        history.forEach((h, idx) => {
                            rows += `
                                <tr>
                                    <td style="text-align: center; color: #94a3b8; font-family: monospace;">${idx + 1}</td>
                                    <td style="text-align: center; font-size: 11.5px; font-weight: 500;">${h.start}</td>
                                    <td style="text-align: center; font-size: 11.5px; font-weight: 500;">${h.stop}</td>
                                    <td style="text-align: center; font-family: monospace; font-size: 11px;">${h.duration}</td>
                                    <td style="text-align: center; font-family: monospace; font-size: 11px; color: #166534;">
                                        <i class="fa-solid fa-arrow-down text-[8px] mr-1"></i>${h.download}
                                    </td>
                                    <td style="text-align: center; font-family: monospace; font-size: 11px; color: #0369a1;">
                                        <i class="fa-solid fa-arrow-up text-[8px] mr-1"></i>${h.upload}
                                    </td>
                                    <td style="text-align: center; font-family: monospace; font-size: 11px; font-weight: 700; color: #4338ca;">
                                        ${h.total_traffic}
                                    </td>
                                    <td style="text-align: center; font-family: monospace; font-size: 11px; font-weight: 600; color: #334155;">
                                        ${h.ip}
                                    </td>
                                </tr>
                            `;
                        });
                        tbody.innerHTML = rows;
                    }

                    loading.classList.add('hidden');
                    content.classList.remove('hidden');
                })
                .catch(err => {
                    loading.innerHTML = `
                        <div class="text-rose-500 font-semibold mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Gagal Memuat Data</div>
                        <p class="text-xs text-slate-500">${err.message}</p>
                    `;
                });
            }

            function copyDetailSummary() {
                if (!currentLoadedData) {
                    alert('Data belum selesai dimuat.');
                    return;
                }
                const s = currentLoadedData.latest_session;
                const c = currentLoadedData.customer;
                const u = currentLoadedData.username;
                const text = `[DETAIL SESI PPPoE]\n` +
                    `Username: ${u}\n` +
                    `Pelanggan: ${c ? c.nama_lengkap : 'Mandiri'} (${c ? c.no_whatsapp : '-'})\n` +
                    `IP Address: ${s ? s.framedipaddress : '-'}\n` +
                    `MAC ONT: ${s ? s.callingstationid : '-'}\n` +
                    `Status: ${s ? (s.is_online ? 'ONLINE' : 'OFFLINE') : '-'}\n` +
                    `Mulai: ${s ? s.start_formatted : '-'}\n` +
                    `Durasi: ${s ? s.formatted_duration : '-'}\n` +
                    `Traffic: DL ${s ? s.download_formatted : '-'} / UL ${s ? s.upload_formatted : '-'} (Total: ${s ? s.total_formatted : '-'})\n` +
                    `Penyebab Putus: ${s ? s.acctterminatecause : '-'}`;

                navigator.clipboard.writeText(text).then(() => {
                    alert('Ringkasan sesi berhasil disalin ke clipboard!');
                }).catch(() => {
                    alert('Gagal menyalin ke clipboard.');
                });
            }
        </script>
    @endsection
</x-layouts.app>
