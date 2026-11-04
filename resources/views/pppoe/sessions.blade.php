<x-layouts.app>
    @section('Css')
        <style>
            #session-table thead th, #user-history-table thead th {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 12px;
            }

            #session-table tbody td, #user-history-table tbody td {
                font-size: 12px;
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
        </style>
    @endsection

    <!-- Header Section -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Monitoring Sesi PPPoE
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">
                Status online/offline real-time akun PPPoE dan riwayat koneksi sesi dari FreeRADIUS.
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

    <!-- Stats Cards Grid (Kompak 3 Kartu) -->
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

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="session-table" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 45px;">#</th>
                        <th style="text-align: left;">Pelanggan</th>
                        <th style="text-align: left; width: 180px;">Username</th>
                        <th style="text-align: left; width: 140px;">Paket</th>
                        <th style="text-align: center; width: 95px;">Status Sesi</th>
                        <th style="text-align: center; width: 120px;">IP Address</th>
                        <th style="text-align: center; width: 130px;">MAC (ONT)</th>
                        <th style="text-align: center; width: 110px;">Uptime</th>
                        <th style="text-align: center; width: 110px;">Traffic (DL/UL)</th>
                        <th style="text-align: center; width: 95px;">Opsi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Modal Riwayat Sesi Per User (FreeRADIUS radacct) -->
    <div class="modal-overlay" id="userHistoryModal">
        <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-200 shrink-0">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Riwayat Sesi PPPoE</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5" id="historyModalSubtitle">Memuat riwayat sesi...</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('userHistoryModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                <!-- User Profile Box -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Nama Pelanggan</span>
                        <span class="font-bold text-slate-800 text-sm" id="histCustomerName">-</span>
                        <span class="text-slate-500 ml-2" id="histCustomerPhone"></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Username</span>
                            <span class="font-mono font-semibold text-slate-800" id="histUsername">-</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Paket</span>
                            <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700; font-size:11px; padding:2px 8px; border-radius:6px;" id="histCustomerPaket">-</span>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="historyLoading" class="py-10 text-center text-slate-400">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-sky-500 mb-2"></i>
                    <p class="text-xs font-semibold text-slate-600">Mengambil data riwayat sesi dari FreeRADIUS...</p>
                </div>

                <!-- History Table Container -->
                <div id="historyContent" class="table-responsive hidden border border-slate-200 rounded-xl overflow-hidden">
                    <table id="user-history-table" class="admin-table w-full">
                        <thead>
                            <tr class="bg-slate-50">
                                <th style="text-align: center; width: 45px;">#</th>
                                <th style="text-align: center; width: 90px;">Status</th>
                                <th style="text-align: center; width: 130px;">Waktu Mulai</th>
                                <th style="text-align: center; width: 130px;">Waktu Berhenti</th>
                                <th style="text-align: center; width: 85px;">Durasi</th>
                                <th style="text-align: center; width: 110px;">Traffic (DL/UL)</th>
                                <th style="text-align: center; width: 140px;">Alasan Putus</th>
                                <th style="text-align: center; width: 115px;">IP & MAC</th>
                                <th style="text-align: center; width: 50px;">Detail</th>
                            </tr>
                        </thead>
                        <tbody id="userHistoryTbody">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>

                <div id="historyEmpty" class="py-10 text-center text-slate-400 hidden">
                    <i class="fa-solid fa-inbox text-2xl mb-2 text-slate-300"></i>
                    <p class="text-xs text-slate-500 m-0">Belum ada riwayat sesi tercatat untuk akun PPPoE ini.</p>
                </div>
            </div>

            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
                <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5" onclick="closeCustomModal('userHistoryModal')">
                    <i class="fa-solid fa-xmark"></i> Tutup
                </button>
                <span class="text-xs text-slate-500" id="histTotalCountText"></span>
            </div>
        </div>
    </div>

    <!-- Modal Detail Parameter Sesi Spesifik (RADIUS) -->
    <div class="modal-overlay" id="sessionDetailModal">
        <div class="modal-box max-w-xl bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-200 shrink-0">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Detail Parameter Sesi</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5" id="modalSessionSubtitle">Memuat parameter telemetri...</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('sessionDetailModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                <div id="modalLoading" class="py-10 text-center text-slate-400">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-sky-500 mb-2"></i>
                    <p class="text-xs font-semibold text-slate-600">Mengambil data sesi...</p>
                </div>

                <div id="modalContent" class="space-y-4 hidden">
                    <!-- Telemetry Parameters -->
                    <div class="border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                Parameter Accounting RADIUS
                            </span>
                            <span id="modalSessionStatusBadge"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-xs">
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
                                <span class="text-slate-400 block text-[11px]">NAS Port</span>
                                <span class="text-slate-700 font-medium" id="modalNasPort">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Called Station ID</span>
                                <span class="text-slate-700 font-medium" id="modalCalledStation">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps & Traffic -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="border border-slate-200 rounded-xl p-3.5 space-y-1.5 text-xs">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Waktu Sesi</span>
                            <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                                <span class="text-slate-500">Mulai:</span>
                                <span class="font-semibold text-slate-800" id="modalStartTime">-</span>
                            </div>
                            <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                                <span class="text-slate-500">Update:</span>
                                <span class="font-semibold text-slate-800" id="modalUpdateTime">-</span>
                            </div>
                            <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                                <span class="text-slate-500">Selesai:</span>
                                <span class="font-semibold text-slate-800" id="modalStopTime">-</span>
                            </div>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-slate-500 font-semibold">Durasi:</span>
                                <span class="font-bold text-sky-700 font-mono" id="modalDuration">-</span>
                            </div>
                        </div>

                        <div class="border border-slate-200 rounded-xl p-3.5 space-y-1.5 text-xs">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Volume Data</span>
                            <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                                <span class="text-slate-500 text-emerald-700 font-semibold"><i class="fa-solid fa-arrow-down text-xs"></i> Download:</span>
                                <span class="font-bold font-mono text-emerald-700" id="modalDownload">-</span>
                            </div>
                            <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                                <span class="text-slate-500 text-sky-700 font-semibold"><i class="fa-solid fa-arrow-up text-xs"></i> Upload:</span>
                                <span class="font-bold font-mono text-sky-700" id="modalUpload">-</span>
                            </div>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-slate-500 font-semibold">Alasan Putus:</span>
                                <span class="font-bold text-slate-700" id="modalCauseTitle">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
                <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5" onclick="closeCustomModal('sessionDetailModal')">
                    <i class="fa-solid fa-xmark"></i> Tutup
                </button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            let sessionTable;

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
                            data: 'mac_address',
                            name: 'mac_address',
                            orderable: false,
                            searchable: false,
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
                    closeCustomModal('userHistoryModal');
                    closeCustomModal('sessionDetailModal');
                }
            });

            ['userHistoryModal', 'sessionDetailModal'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('click', function(e) {
                        if (e.target === this) {
                            closeCustomModal(id);
                        }
                    });
                }
            });

            // Tampilkan riwayat sesi khusus 1 PPPoE account
            function showUserHistory(username, customerName) {
                openCustomModal('userHistoryModal');
                const loading = document.getElementById('historyLoading');
                const content = document.getElementById('historyContent');
                const empty = document.getElementById('historyEmpty');
                const tbody = document.getElementById('userHistoryTbody');

                loading.classList.remove('hidden');
                content.classList.add('hidden');
                empty.classList.add('hidden');
                tbody.innerHTML = '';

                document.getElementById('historyModalSubtitle').innerText = `${username} • ${customerName || 'PPPoE'}`;
                document.getElementById('histUsername').innerText = username;
                document.getElementById('histCustomerName').innerText = customerName || 'Akun Mandiri';
                document.getElementById('histCustomerPhone').innerText = '';
                document.getElementById('histCustomerPaket').innerText = '-';
                document.getElementById('histTotalCountText').innerText = '';

                fetch(`/pppoe-sessions/user-history?username=${encodeURIComponent(username)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Gagal memuat riwayat sesi');
                    return res.json();
                })
                .then(data => {
                    if (!data.success) throw new Error(data.message || 'Respon tidak valid');

                    const c = data.customer;
                    if (c) {
                        document.getElementById('histCustomerName').innerText = c.nama_lengkap || 'Akun Mandiri';
                        document.getElementById('histCustomerPhone').innerText = c.no_whatsapp ? `(${c.no_whatsapp})` : '';
                        document.getElementById('histCustomerPaket').innerText = c.paket || '-';
                    }

                    const sessions = data.sessions || [];
                    document.getElementById('histTotalCountText').innerText = `Menampilkan ${sessions.length} riwayat sesi terakhir`;

                    if (sessions.length === 0) {
                        loading.classList.add('hidden');
                        empty.classList.remove('hidden');
                        return;
                    }

                    let rowsHtml = '';
                    sessions.forEach((s, idx) => {
                        const statusBadge = s.is_online
                            ? '<span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-size:10.5px; padding:2px 7px; border-radius:5px; font-weight:700;"><i class="fa-solid fa-circle text-[7px] mr-1 text-emerald-500"></i> ONLINE</span>'
                            : '<span class="badge" style="background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; font-size:10.5px; padding:2px 7px; border-radius:5px; font-weight:600;">OFFLINE</span>';

                        const cause = s.terminate_cause || '-';
                        let causeBadge = `<span class="badge" style="background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; font-size:10px; padding:2px 6px; border-radius:4px;">${cause}</span>`;
                        if (cause === 'User-Request') {
                            causeBadge = '<span class="badge" style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-size:10px; padding:2px 6px; border-radius:4px; font-weight:600;"><i class="fa-solid fa-power-off text-[8px] mr-1"></i> User-Request</span>';
                        } else if (cause === 'Lost-Carrier') {
                            causeBadge = '<span class="badge" style="background:#fef2f2; color:#be123c; border:1px solid #fecdd3; font-size:10px; padding:2px 6px; border-radius:4px; font-weight:600;"><i class="fa-solid fa-triangle-exclamation text-[8px] mr-1"></i> Lost-Carrier</span>';
                        } else if (cause === 'NAS-Reboot') {
                            causeBadge = '<span class="badge" style="background:#fffbeb; color:#b45309; border:1px solid #fde68a; font-size:10px; padding:2px 6px; border-radius:4px; font-weight:600;"><i class="fa-solid fa-rotate text-[8px] mr-1"></i> NAS-Reboot</span>';
                        } else if (s.is_online) {
                            causeBadge = '<span class="badge" style="background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; font-size:10px; padding:2px 6px; border-radius:4px; font-weight:600;">Aktif</span>';
                        }

                        rowsHtml += `
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-family: monospace;">${idx + 1}</td>
                                <td style="text-align: center;">${statusBadge}</td>
                                <td style="text-align: center; font-size: 11.5px; font-weight: 500;">${s.start}</td>
                                <td style="text-align: center; font-size: 11.5px; font-weight: 500;">${s.stop}</td>
                                <td style="text-align: center; font-family: monospace; font-size: 11px;">${s.duration}</td>
                                <td style="text-align: center; font-family: monospace; font-size: 10.5px;">
                                    <div style="color: #166534;"><i class="fa-solid fa-arrow-down text-[8px]"></i> ${s.download}</div>
                                    <div style="color: #0369a1;"><i class="fa-solid fa-arrow-up text-[8px]"></i> ${s.upload}</div>
                                </td>
                                <td style="text-align: center;">${causeBadge}</td>
                                <td style="text-align: center; font-family: monospace; font-size: 10.5px;">
                                    <div style="font-weight: 600; color: #334155;">${s.framedipaddress}</div>
                                    <div style="color: #94a3b8;">${s.callingstationid}</div>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="topbar-icon-btn !w-7 !h-7 !rounded-lg" onclick="showSessionDetail(${s.radacctid})" title="Detail Telemetri">
                                        <i class="fa-solid fa-circle-info text-slate-500"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    tbody.innerHTML = rowsHtml;
                    loading.classList.add('hidden');
                    content.classList.remove('hidden');
                })
                .catch(err => {
                    loading.innerHTML = `
                        <div class="text-rose-500 font-semibold mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Gagal Memuat Riwayat Sesi</div>
                        <p class="text-xs text-slate-500">${err.message}</p>
                    `;
                });
            }

            // Tampilkan parameter spesifik 1 session id
            function showSessionDetail(radacctid) {
                openCustomModal('sessionDetailModal');
                const loading = document.getElementById('modalLoading');
                const content = document.getElementById('modalContent');
                loading.classList.remove('hidden');
                content.classList.add('hidden');

                fetch(`/pppoe-sessions/${radacctid}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Gagal memuat detail');
                    return res.json();
                })
                .then(data => {
                    if (!data.success) throw new Error('Respon tidak valid');

                    const s = data.session;
                    document.getElementById('modalSessionSubtitle').innerText = `ID Sesi #${s.radacctid} • ${s.username}`;

                    document.getElementById('modalSessionId').innerText = s.acctsessionid || '-';
                    document.getElementById('modalFramedIp').innerText = s.framedipaddress || '-';
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

                    const statusBadge = document.getElementById('modalSessionStatusBadge');
                    if (s.is_online) {
                        statusBadge.innerHTML = '<span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-size:11px; padding:2px 8px; border-radius:6px; font-weight:700;"><i class="fa-solid fa-circle text-[7px] mr-1 text-emerald-500"></i> ONLINE</span>';
                    } else {
                        statusBadge.innerHTML = '<span class="badge" style="background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; font-size:11px; padding:2px 8px; border-radius:6px; font-weight:600;">OFFLINE</span>';
                    }

                    document.getElementById('modalCauseTitle').innerText = s.acctterminatecause || (s.is_online ? 'Sedang Aktif' : '-');

                    loading.classList.add('hidden');
                    content.classList.remove('hidden');
                })
                .catch(err => {
                    loading.innerHTML = `
                        <div class="text-rose-500 font-semibold mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Gagal Memuat Detail Sesi</div>
                        <p class="text-xs text-slate-500">${err.message}</p>
                    `;
                });
            }
        </script>
    @endsection
</x-layouts.app>
