<x-layouts.app>
    @section('Css')
        <style>
            #session-table thead th {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 12px;
            }

            #session-table tbody td {
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
                font-size: 12px;
                font-weight: 600;
                color: #0284c7;
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
                Riwayat Sesi PPPoE
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">
                Log accounting RADIUS, durasi koneksi online/offline, dan riwayat terminasi sesi pelanggan.
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

    <!-- Stats Cards Grid -->
    <div class="stats-grid mb-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0e7ff;color:#4338ca;">
                <i class="fa-solid fa-database"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($stats['total'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Total Riwayat Sesi</div>
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
            <div class="stat-icon" style="background:#fee2e2;color:#be123c;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <div class="stat-val text-slate-800">{{ number_format($stats['offline'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Sesi Selesai (Offline)</div>
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
            <div style="flex: 1; min-width: 150px;">
                <label class="form-label-custom">Status Sesi</label>
                <select class="form-input-custom !h-8.5 !text-xs" id="filter-status">
                    <option value="">Semua Status</option>
                    <option value="online" {{ ($filterStatus ?? '') === 'online' ? 'selected' : '' }}>Online (Aktif)</option>
                    <option value="offline" {{ ($filterStatus ?? '') === 'offline' ? 'selected' : '' }}>Offline (Selesai)</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 160px;">
                <label class="form-label-custom">Rentang Waktu</label>
                <select class="form-input-custom !h-8.5 !text-xs" id="filter-date">
                    <option value="">Semua Waktu</option>
                    <option value="today">Hari Ini</option>
                    <option value="7d">7 Hari Terakhir</option>
                    <option value="30d">30 Hari Terakhir</option>
                </select>
            </div>
            <div style="flex: 1.5; min-width: 200px;">
                <label class="form-label-custom">Alasan Putus (Cause)</label>
                <select class="form-input-custom !h-8.5 !text-xs" id="filter-cause">
                    <option value="">Semua Alasan</option>
                    @foreach ($causes as $c)
                        <option value="{{ $c }}" {{ ($filterCause ?? '') === $c ? 'selected' : '' }}>
                            {{ $c }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 2; min-width: 220px;">
                <label class="form-label-custom">Pencarian</label>
                <div class="search-wrap !max-w-full">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="search" class="search-input custom-search !h-8.5 !text-xs" id="custom-search"
                        placeholder="Cari username, IP, MAC..." autocomplete="off" />
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
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: left;">Pelanggan</th>
                        <th style="text-align: left; width: 170px;">Username</th>
                        <th style="text-align: center; width: 95px;">Status</th>
                        <th style="text-align: center; width: 120px;">IP Address</th>
                        <th style="text-align: center; width: 130px;">MAC (ONT)</th>
                        <th style="text-align: center; width: 110px;">Mulai</th>
                        <th style="text-align: center; width: 110px;">Berhenti</th>
                        <th style="text-align: center; width: 85px;">Durasi</th>
                        <th style="text-align: center; width: 105px;">Traffic (DL/UL)</th>
                        <th style="text-align: center; width: 135px;">Alasan Putus</th>
                        <th style="text-align: center; width: 55px;">Opsi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Modal Detail Sesi PPPoE (RADIUS) -->
    <div class="modal-overlay" id="sessionDetailModal">
        <div class="modal-box max-w-xl bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-200 shrink-0">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Detail Sesi RADIUS</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5" id="modalSessionSubtitle">Memuat detail sesi...</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('sessionDetailModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                <!-- Loading State -->
                <div id="modalLoading" class="py-10 text-center text-slate-400">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-sky-600 mb-2"></i>
                    <p class="text-xs font-semibold text-slate-600">Mengambil data sesi dari FreeRADIUS...</p>
                </div>

                <!-- Content State -->
                <div id="modalContent" class="space-y-4 hidden">
                    <!-- Customer Information Box -->
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                Profil Pelanggan
                            </span>
                            <span id="modalCustomerPaket" class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700; font-size:11px; padding:2px 8px; border-radius:6px;">
                                -
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Nama Lengkap</span>
                                <span class="font-bold text-slate-800" id="modalCustomerName">-</span>
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

                    <!-- Telemetry Parameters -->
                    <div class="border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                Telemetri Koneksi (radacct)
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
                                <span class="text-slate-500 font-semibold">Penyebab:</span>
                                <span class="font-bold text-slate-700" id="modalCauseTitle">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Disconnect Explanation Box -->
                    <div class="border border-slate-200 bg-slate-50 rounded-xl p-3 text-xs" id="modalCauseBox">
                        <div class="font-semibold text-slate-700 mb-0.5" id="modalCauseHeading">Keterangan Terminasi Sesi</div>
                        <p class="text-slate-600 m-0" id="modalCauseDesc">-</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
                <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5" onclick="closeCustomModal('sessionDetailModal')">
                    <i class="fa-solid fa-xmark"></i> Tutup
                </button>
                <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5 border border-slate-300 bg-white hover:bg-slate-50" onclick="copySessionDetails()">
                    <i class="fa-regular fa-copy text-slate-500"></i> Salin Ringkasan
                </button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            let sessionTable;
            let currentLoadedSession = null;

            $(document).ready(function() {
                sessionTable = $('#session-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    order: [[0, 'desc']],
                    ajax: {
                        url: "{{ route('pppoe_sessions.index') }}",
                        data: function(d) {
                            d.status = $('#filter-status').val();
                            d.date_range = $('#filter-date').val();
                            d.cause = $('#filter-cause').val();
                        }
                    },
                    columns: [
                        {
                            data: 'radacctid',
                            name: 'radacctid',
                            orderable: true,
                            searchable: false,
                            className: 'text-center font-mono text-xs text-slate-400'
                        },
                        {
                            data: 'pelanggan',
                            name: 'username',
                            orderable: true,
                            searchable: true
                        },
                        {
                            data: 'username_col',
                            name: 'username',
                            orderable: true,
                            searchable: true
                        },
                        {
                            data: 'status_badge',
                            name: 'acctstoptime',
                            orderable: true,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'ip_address',
                            name: 'framedipaddress',
                            orderable: true,
                            searchable: true,
                            className: 'text-center'
                        },
                        {
                            data: 'mac_address',
                            name: 'callingstationid',
                            orderable: true,
                            searchable: true,
                            className: 'text-center'
                        },
                        {
                            data: 'waktu_mulai',
                            name: 'acctstarttime',
                            orderable: true,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'waktu_selesai',
                            name: 'acctstoptime',
                            orderable: true,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'durasi',
                            name: 'acctsessiontime',
                            orderable: true,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'traffic',
                            name: 'acctoutputoctets',
                            orderable: true,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'alasan_putus',
                            name: 'acctterminatecause',
                            orderable: true,
                            searchable: true,
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
                        processing: '<div class="flex items-center gap-2"><i class="fa-solid fa-circle-notch fa-spin text-sky-600"></i> Memuat data...</div>',
                        lengthMenu: "Tampilkan _MENU_ data",
                        zeroRecords: "Data tidak ditemukan",
                        info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                        infoEmpty: "Menampilkan 0 data",
                        infoFiltered: "(disaring dari _MAX_ total)",
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
                $('#filter-status, #filter-date, #filter-cause').on('change', function() {
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
                $('#filter-date').val('');
                $('#filter-cause').val('');
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
                    closeCustomModal('sessionDetailModal');
                }
            });

            const modalOverlay = document.getElementById('sessionDetailModal');
            if (modalOverlay) {
                modalOverlay.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeCustomModal('sessionDetailModal');
                    }
                });
            }

            function showSessionDetail(radacctid) {
                openCustomModal('sessionDetailModal');
                const loading = document.getElementById('modalLoading');
                const content = document.getElementById('modalContent');
                loading.classList.remove('hidden');
                content.classList.add('hidden');
                currentLoadedSession = null;

                fetch(`/pppoe-sessions/${radacctid}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Gagal memuat detail sesi');
                    return res.json();
                })
                .then(data => {
                    if (!data.success) throw new Error('Respon tidak valid');
                    currentLoadedSession = data;

                    const s = data.session;
                    const c = data.customer;
                    const p = data.pppoe;

                    document.getElementById('modalSessionSubtitle').innerText = `ID #${s.radacctid} • ${s.username}`;

                    // Customer section
                    if (c) {
                        document.getElementById('modalCustomerName').innerText = c.nama_lengkap || '-';
                        document.getElementById('modalCustomerId').innerText = c.id_pelanggan || '-';
                        document.getElementById('modalCustomerPhone').innerText = c.no_whatsapp || '-';
                        document.getElementById('modalCustomerPaket').innerText = c.paket || 'Paket Default';
                    } else {
                        document.getElementById('modalCustomerName').innerText = 'Akun Mandiri / Profil Belum Ditautkan';
                        document.getElementById('modalCustomerId').innerText = '-';
                        document.getElementById('modalCustomerPhone').innerText = '-';
                        document.getElementById('modalCustomerPaket').innerText = p && p.paket ? p.paket : 'Tanpa Paket';
                    }
                    document.getElementById('modalPppoeUsername').innerText = s.username;

                    // Telemetry
                    document.getElementById('modalSessionId').innerText = s.acctsessionid || '-';
                    document.getElementById('modalFramedIp').innerText = s.framedipaddress || '-';
                    document.getElementById('modalMac').innerText = s.callingstationid || '-';
                    document.getElementById('modalNasIp').innerText = s.nasipaddress || '-';
                    document.getElementById('modalNasPort').innerText = `${s.nasportid || '-'} (${s.nasporttype || 'Ethernet'})`;
                    document.getElementById('modalCalledStation').innerText = s.calledstationid || '-';

                    // Timestamps & Duration
                    document.getElementById('modalStartTime').innerText = s.start_formatted || '-';
                    document.getElementById('modalUpdateTime').innerText = s.update_formatted || '-';
                    document.getElementById('modalStopTime').innerText = s.stop_formatted || '-';
                    document.getElementById('modalDuration').innerText = s.formatted_duration || '0 detik';

                    // Traffic
                    document.getElementById('modalDownload').innerText = s.download_formatted || '0 B';
                    document.getElementById('modalUpload').innerText = s.upload_formatted || '0 B';

                    // Status Badge
                    const statusBadge = document.getElementById('modalSessionStatusBadge');
                    if (s.is_online) {
                        statusBadge.innerHTML = '<span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-size:11px; padding:2px 8px; border-radius:6px; font-weight:700;"><i class="fa-solid fa-circle text-[7px] mr-1 text-emerald-500"></i> ONLINE</span>';
                    } else {
                        statusBadge.innerHTML = '<span class="badge" style="background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; font-size:11px; padding:2px 8px; border-radius:6px; font-weight:600;">OFFLINE</span>';
                    }

                    // Cause box
                    const causeHeading = document.getElementById('modalCauseHeading');
                    const causeDesc = document.getElementById('modalCauseDesc');
                    const causeTitle = document.getElementById('modalCauseTitle');

                    if (s.is_online) {
                        causeTitle.innerText = 'Online Aktif';
                        causeHeading.innerText = 'Sesi Sedang Berlangsung';
                        causeDesc.innerText = 'Akun PPPoE saat ini sedang terhubung aktif ke internet.';
                    } else {
                        const cause = s.acctterminatecause || 'Tidak Tercatat';
                        causeTitle.innerText = cause;
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
                            causeDesc.innerText = `Sesi selesai dengan kode status RADIUS: ${cause}.`;
                        }
                    }

                    loading.classList.add('hidden');
                    content.classList.remove('hidden');
                })
                .catch(err => {
                    loading.innerHTML = `
                        <div class="text-rose-500 font-semibold mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Gagal Memuat Data Sesi</div>
                        <p class="text-xs text-slate-500">${err.message}</p>
                    `;
                });
            }

            function copySessionDetails() {
                if (!currentLoadedSession || !currentLoadedSession.session) {
                    alert('Data sesi belum dimuat.');
                    return;
                }
                const s = currentLoadedSession.session;
                const c = currentLoadedSession.customer;
                const text = `[DETAIL SESI RADIUS PPPoE]\n` +
                    `ID Sesi: ${s.radacctid}\n` +
                    `Username: ${s.username}\n` +
                    `Pelanggan: ${c ? c.nama_lengkap : 'Mandiri'} (${c ? c.no_whatsapp : '-'})\n` +
                    `IP Address: ${s.framedipaddress || '-'}\n` +
                    `MAC ONT: ${s.callingstationid || '-'}\n` +
                    `Mulai: ${s.start_formatted || '-'}\n` +
                    `Selesai: ${s.stop_formatted || '-'}\n` +
                    `Durasi: ${s.formatted_duration || '-'}\n` +
                    `Traffic: DL ${s.download_formatted} / UL ${s.upload_formatted}\n` +
                    `Penyebab Putus: ${s.acctterminatecause || (s.is_online ? 'Sedang Aktif' : '-')}`;

                navigator.clipboard.writeText(text).then(() => {
                    alert('Ringkasan sesi berhasil disalin ke clipboard!');
                }).catch(() => {
                    alert('Gagal menyalin ke clipboard.');
                });
            }
        </script>
    @endsection
</x-layouts.app>
