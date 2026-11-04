<x-layouts.app>
    @section('Css')
        <style>
            #session-table thead th {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 11.5px;
                letter-spacing: 0.03em;
            }

            #session-table tbody td {
                font-size: 12px;
                vertical-align: middle;
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
                padding: 12px 24px !important;
                border-radius: 10px !important;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
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
                padding: 1rem 1.25rem;
                gap: 4px;
                border-top: 1px solid #f1f5f9;
            }

            .paginate_button {
                width: 32px;
                height: 32px;
                border-radius: 7px;
                border: 1px solid #e2e8f0;
                background: #fff;
                color: #64748b !important;
                font-size: 12px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.15s ease;
                text-decoration: none !important;
            }

            .paginate_button:hover:not(.disabled) {
                background: #f8fafc;
                border-color: #cbd5e1;
                color: #0f172a !important;
            }

            .paginate_button.current {
                background: #0284c7 !important;
                border-color: #0284c7 !important;
                color: #fff !important;
            }

            .paginate_button.disabled {
                opacity: 0.4;
                cursor: not-allowed;
            }

            .stat-pulse-dot {
                position: relative;
            }
            .stat-pulse-dot::after {
                content: '';
                position: absolute;
                inset: -3px;
                border-radius: 9999px;
                background-color: currentColor;
                opacity: 0.35;
                animation: pulseRing 2s cubic-bezier(0, 0, 0.2, 1) infinite;
            }
            @keyframes pulseRing {
                0% { transform: scale(0.9); opacity: 0.5; }
                70% { transform: scale(2.2); opacity: 0; }
                100% { transform: scale(2.2); opacity: 0; }
            }
        </style>
    @endsection

    <div class="space-y-5">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Riwayat & Sesi PPPoE</h1>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                        <i class="fa-solid fa-server text-[10px]"></i> RADIUS Accounting
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Log pencatatan sesi koneksi, durasi online, statistik pemakaian kuota/traffic, serta riwayat penyebab putus (<span class="font-mono text-slate-700">terminate cause</span>) dari FreeRADIUS.
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" onclick="reloadTable()" class="btn-cancel !h-8.5 !px-3.5 text-xs font-semibold flex items-center gap-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-xs">
                    <i class="fa-solid fa-arrows-rotate text-slate-500" id="refreshIcon"></i> Refresh
                </button>
                <a href="{{ route('pppoe.index') }}" class="btn-cancel !h-8.5 !px-3.5 text-xs font-semibold flex items-center gap-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-xs">
                    <i class="fa-solid fa-network-wired text-emerald-600"></i> PPPoE Aktif (MikroTik)
                </a>
                @if (auth()->user() && auth()->user()->isAdmin())
                    <a href="{{ route('pppoe_accounts.index') }}" class="btn-primary-admin !h-8.5 !px-3.5 text-xs font-semibold !bg-sky-600 hover:!bg-sky-700 shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-users-gear"></i> Kelola Akun PPPoE
                    </a>
                @endif
            </div>
        </div>

        <!-- 4 Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- 1. Total Sesi -->
            <div class="bg-white border border-slate-200 rounded-xl p-3.5 sm:p-4 shadow-2xs hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Riwayat Sesi</span>
                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-database"></i>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ number_format($stats['total'], 0, ',', '.') }}</span>
                    <span class="text-[11px] font-medium text-slate-400">log</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-500">Tersimpan di database FreeRADIUS</div>
            </div>

            <!-- 2. Sesi Online Aktif -->
            <div class="bg-white border border-slate-200 rounded-xl p-3.5 sm:p-4 shadow-2xs hover:border-emerald-300 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-emerald-700 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 stat-pulse-dot text-emerald-500"></span>
                        Online Saat Ini
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-wifi"></i>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-xl sm:text-2xl font-bold text-emerald-700 tracking-tight">{{ number_format($stats['online'], 0, ',', '.') }}</span>
                    <span class="text-[11px] font-semibold text-emerald-600">sesi</span>
                </div>
                <div class="mt-1 text-[11px] text-emerald-600 font-medium">Sedang aktif dial terhubung</div>
            </div>

            <!-- 3. Sesi Offline / Selesai -->
            <div class="bg-white border border-slate-200 rounded-xl p-3.5 sm:p-4 shadow-2xs hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Sesi Offline / Selesai</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">{{ number_format($stats['offline'], 0, ',', '.') }}</span>
                    <span class="text-[11px] font-medium text-slate-400">sesi</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-500">Koneksi sebelumnya yang telah terputus</div>
            </div>

            <!-- 4. Aktivitas 24 Jam -->
            <div class="bg-white border border-slate-200 rounded-xl p-3.5 sm:p-4 shadow-2xs hover:border-sky-300 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-sky-700 uppercase tracking-wider">Aktivitas 24 Jam</span>
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-xl sm:text-2xl font-bold text-sky-700 tracking-tight">{{ number_format($stats['active24h'], 0, ',', '.') }}</span>
                    <span class="text-[11px] font-semibold text-sky-600">pembaruan</span>
                </div>
                <div class="mt-1 text-[11px] text-sky-600 font-medium">Interim update & sesi harian</div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white border border-slate-200 rounded-xl p-3.5 sm:p-4 shadow-2xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <!-- Status Filter -->
                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-semibold text-slate-600 uppercase tracking-wider mb-1">Status Sesi</label>
                    <select id="filter-status" class="w-full text-xs font-medium rounded-lg border-slate-300 bg-white text-slate-800 py-2 px-3 focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                        <option value="">Semua Status Sesi</option>
                        <option value="online" {{ $statusFilter === 'online' ? 'selected' : '' }}>Hanya Online (Sedang Aktif)</option>
                        <option value="offline" {{ $statusFilter === 'offline' ? 'selected' : '' }}>Hanya Offline (Selesai/Putus)</option>
                    </select>
                </div>

                <!-- Rentang Waktu -->
                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-semibold text-slate-600 uppercase tracking-wider mb-1">Rentang Waktu</label>
                    <select id="filter-date" class="w-full text-xs font-medium rounded-lg border-slate-300 bg-white text-slate-800 py-2 px-3 focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                        <option value="">Semua Waktu Riwayat</option>
                        <option value="today">Hari Ini</option>
                        <option value="7d">7 Hari Terakhir</option>
                        <option value="30d">30 Hari Terakhir</option>
                    </select>
                </div>

                <!-- Disconnect Cause -->
                <div class="lg:col-span-4">
                    <label class="block text-[11px] font-semibold text-slate-600 uppercase tracking-wider mb-1">Alasan Putus (Terminate Cause)</label>
                    <select id="filter-cause" class="w-full text-xs font-medium rounded-lg border-slate-300 bg-white text-slate-800 py-2 px-3 focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                        <option value="">Semua Penyebab Putus</option>
                        @foreach ($causes as $c)
                            <option value="{{ $c }}" {{ $causeFilter === $c ? 'selected' : '' }}>
                                {{ $c }} 
                                @if ($c === 'User-Request') - (Modem Off / User Restart)
                                @elseif ($c === 'Lost-Carrier') - (Kabel Putus / LOS Merah)
                                @elseif ($c === 'NAS-Reboot') - (MikroTik Router Reboot)
                                @elseif ($c === 'NAS-Request') - (Kick by NAS / Timeout)
                                @elseif ($c === 'Admin-Reset' || $c === 'Admin-Reboot') - (Reset Admin)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="button" onclick="resetFilters()" class="w-full h-9 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-filter-circle-xmark text-slate-400"></i> Reset Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
            <div class="overflow-x-auto">
                <table id="session-table" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600">
                            <th class="py-3 px-3 w-10 text-center">#</th>
                            <th class="py-3 px-3 min-w-[190px]">Pelanggan / Username</th>
                            <th class="py-3 px-3 text-center w-28">Status</th>
                            <th class="py-3 px-3 text-center min-w-[140px]">IP & MAC</th>
                            <th class="py-3 px-3 text-center min-w-[125px]">Waktu Mulai</th>
                            <th class="py-3 px-3 text-center min-w-[125px]">Waktu Selesai</th>
                            <th class="py-3 px-3 text-center min-w-[95px]">Durasi</th>
                            <th class="py-3 px-3 text-center min-w-[110px]">Traffic (DL / UL)</th>
                            <th class="py-3 px-3 text-center min-w-[170px]">Alasan Putus</th>
                            <th class="py-3 px-3 text-center w-14">Opsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        <!-- DataTables Populated -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Detail Sesi PPPoE (RADIUS) -->
    <div class="modal-overlay" id="sessionDetailModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-200 shrink-0">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Detail Sesi Accounting RADIUS</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5" id="modalSessionSubtitle">Memuat parameter telemetri sesi...</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('sessionDetailModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 max-h-[78vh] overflow-y-auto">
                <!-- Loading State -->
                <div id="modalLoading" class="py-12 text-center text-slate-400">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-sky-500 mb-2"></i>
                    <p class="text-xs font-semibold text-slate-600">Mengambil data sesi dari FreeRADIUS...</p>
                </div>

                <!-- Content State -->
                <div id="modalContent" class="space-y-4 hidden">
                    <!-- Customer Information Box -->
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-user text-slate-400"></i> Profil Pelanggan
                            </span>
                            <span id="modalCustomerPaket" class="px-2 py-0.5 rounded text-[11px] font-bold bg-sky-100 text-sky-700 border border-sky-200">
                                -
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
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

                    <!-- Accounting Telemetry Grid -->
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-slate-100/75 px-4 py-2 border-b border-slate-200 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                Parameter Accounting (radacct)
                            </span>
                            <span id="modalSessionStatusBadge" class="text-xs font-bold"></span>
                        </div>
                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-xs">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Acct-Session-Id</span>
                                <span class="font-mono text-slate-700 font-semibold" id="modalSessionId">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Acct-Unique-Id</span>
                                <span class="font-mono text-[11px] text-slate-600 truncate block" id="modalUniqueId" title="">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Framed IP Address (Client)</span>
                                <span class="font-mono text-sky-700 font-bold" id="modalFramedIp">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Calling-Station-Id (MAC Modem)</span>
                                <span class="font-mono text-slate-700 font-semibold" id="modalMac">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">NAS IP Address</span>
                                <span class="font-mono text-slate-700 font-semibold" id="modalNasIp">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">NAS Port & Type</span>
                                <span class="text-slate-700 font-medium" id="modalNasPort">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Called-Station-Id</span>
                                <span class="text-slate-700 font-medium" id="modalCalledStation">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Protokol Layanan</span>
                                <span class="text-slate-700 font-medium" id="modalProtocol">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps & Traffic Volume -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="border border-slate-200 rounded-xl p-3.5 space-y-2 text-xs">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Waktu Sesi</span>
                            <div class="flex justify-between items-center py-1 border-b border-slate-100">
                                <span class="text-slate-500">Mulai (Start):</span>
                                <span class="font-semibold text-slate-800" id="modalStartTime">-</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-100">
                                <span class="text-slate-500">Update Terakhir:</span>
                                <span class="font-semibold text-slate-800" id="modalUpdateTime">-</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-100">
                                <span class="text-slate-500">Selesai (Stop):</span>
                                <span class="font-semibold text-slate-800" id="modalStopTime">-</span>
                            </div>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-slate-500 font-semibold">Total Durasi:</span>
                                <span class="font-bold text-sky-700 font-mono" id="modalDuration">-</span>
                            </div>
                        </div>

                        <div class="border border-slate-200 rounded-xl p-3.5 space-y-2 text-xs">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Volume Traffic Sesi</span>
                            <div class="flex justify-between items-center py-1 border-b border-slate-100">
                                <span class="text-slate-500 flex items-center gap-1.5 text-emerald-600 font-semibold">
                                    <i class="fa-solid fa-arrow-down text-xs"></i> Download (Output):
                                </span>
                                <span class="font-bold font-mono text-emerald-700 text-sm" id="modalDownload">-</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-100">
                                <span class="text-slate-500 flex items-center gap-1.5 text-sky-600 font-semibold">
                                    <i class="fa-solid fa-arrow-up text-xs"></i> Upload (Input):
                                </span>
                                <span class="font-bold font-mono text-sky-700 text-sm" id="modalUpload">-</span>
                            </div>
                            <div class="mt-2 text-[11px] text-slate-400 italic">
                                *Dihitung akumulatif dari awal koneksi sampai sesi terputus.
                            </div>
                        </div>
                    </div>

                    <!-- Disconnect Reason Explanation -->
                    <div class="border rounded-xl p-3.5" id="modalCauseBox">
                        <div class="flex items-center gap-2 mb-1">
                            <i id="modalCauseIcon" class="fa-solid fa-circle-info text-slate-500"></i>
                            <span class="text-xs font-bold text-slate-800" id="modalCauseTitle">Penyebab Berakhir</span>
                        </div>
                        <p class="text-xs text-slate-600 m-0" id="modalCauseDesc">-</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
                <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5" onclick="closeCustomModal('sessionDetailModal')">
                    <i class="fa-solid fa-xmark"></i> Tutup
                </button>
                <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5 border border-slate-300 bg-white hover:bg-slate-50" onclick="copySessionDetails()">
                    <i class="fa-regular fa-copy text-slate-500"></i> Salin Ringkasan Sesi
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
                            data: 'status_badge',
                            name: 'acctstoptime',
                            orderable: true,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'ip_mac',
                            name: 'framedipaddress',
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
                        processing: '<div class="flex items-center gap-2"><i class="fa-solid fa-circle-notch fa-spin text-sky-600"></i> Memuat sesi dari FreeRADIUS...</div>',
                        lengthMenu: '_MENU_ data per halaman',
                        zeroRecords: 'Tidak ada riwayat sesi yang sesuai kriteria.',
                        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ riwayat sesi',
                        infoEmpty: 'Menampilkan 0 sesi',
                        infoFiltered: '(disaring dari _MAX_ total)',
                        search: '',
                        searchPlaceholder: 'Cari username, IP, MAC...',
                        paginate: {
                            previous: '<i class="fa-solid fa-chevron-left text-xs"></i>',
                            next: '<i class="fa-solid fa-chevron-right text-xs"></i>'
                        }
                    },
                    dom: "<'p-3.5 sm:p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3'<'flex items-center gap-2'l><'flex items-center gap-2'f>>" +
                         "<'table-responsive'tr>" +
                         "<'p-3.5 sm:p-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3'<'text-xs text-slate-500'i><'flex justify-end'p>>"
                });

                // Re-draw table when filters change
                $('#filter-status, #filter-date, #filter-cause').on('change', function() {
                    sessionTable.ajax.reload();
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
                if (sessionTable) {
                    sessionTable.search('').ajax.reload();
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

                    document.getElementById('modalSessionSubtitle').innerText = `ID Sesi #${s.radacctid} • ${s.username}`;

                    // Customer section
                    if (c) {
                        document.getElementById('modalCustomerName').innerText = c.nama_lengkap || '-';
                        document.getElementById('modalCustomerId').innerText = c.id_pelanggan || '-';
                        document.getElementById('modalCustomerPhone').innerText = c.no_whatsapp || '-';
                        document.getElementById('modalCustomerPaket').innerText = c.paket || 'Paket Default';
                    } else {
                        document.getElementById('modalCustomerName').innerText = 'Profil belum ditautkan (Akun Mandiri)';
                        document.getElementById('modalCustomerId').innerText = '-';
                        document.getElementById('modalCustomerPhone').innerText = '-';
                        document.getElementById('modalCustomerPaket').innerText = p && p.paket ? p.paket : 'Tanpa Paket';
                    }
                    document.getElementById('modalPppoeUsername').innerText = s.username;

                    // Telemetry
                    document.getElementById('modalSessionId').innerText = s.acctsessionid || '-';
                    const uniqueEl = document.getElementById('modalUniqueId');
                    uniqueEl.innerText = s.acctuniqueid || '-';
                    uniqueEl.title = s.acctuniqueid || '';
                    document.getElementById('modalFramedIp').innerText = s.framedipaddress || '-';
                    document.getElementById('modalMac').innerText = s.callingstationid || '-';
                    document.getElementById('modalNasIp').innerText = s.nasipaddress || '-';
                    document.getElementById('modalNasPort').innerText = `${s.nasportid || '-'} (${s.nasporttype || 'Ethernet'})`;
                    document.getElementById('modalCalledStation').innerText = s.calledstationid || '-';
                    document.getElementById('modalProtocol').innerText = `${s.servicetype || 'Framed-User'} / ${s.framedprotocol || 'PPP'}`;

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
                        statusBadge.className = 'px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
                        statusBadge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block mr-1"></span> ONLINE';
                    } else {
                        statusBadge.className = 'px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200';
                        statusBadge.innerHTML = 'OFFLINE';
                    }

                    // Cause box
                    const causeBox = document.getElementById('modalCauseBox');
                    const causeTitle = document.getElementById('modalCauseTitle');
                    const causeDesc = document.getElementById('modalCauseDesc');
                    const causeIcon = document.getElementById('modalCauseIcon');

                    if (s.is_online) {
                        causeBox.className = 'border border-emerald-200 bg-emerald-50/60 rounded-xl p-3.5';
                        causeTitle.className = 'text-xs font-bold text-emerald-900';
                        causeTitle.innerText = 'Sesi Sedang Berlangsung (Online)';
                        causeDesc.className = 'text-xs text-emerald-700 m-0';
                        causeDesc.innerText = 'Akun PPPoE saat ini sedang terhubung dan aktif melakukan transmisi data ke internet.';
                        causeIcon.className = 'fa-solid fa-bolt text-emerald-600';
                    } else {
                        const cause = s.acctterminatecause || 'Tidak Tercatat';
                        if (cause === 'User-Request') {
                            causeBox.className = 'border border-blue-200 bg-blue-50/60 rounded-xl p-3.5';
                            causeTitle.className = 'text-xs font-bold text-blue-900';
                            causeTitle.innerText = 'User-Request (Koneksi Diputus Normal)';
                            causeDesc.className = 'text-xs text-blue-700 m-0';
                            causeDesc.innerText = 'Pelanggan mematikan router/modem atau merestart perangkat ONT secara mandiri.';
                            causeIcon.className = 'fa-solid fa-power-off text-blue-600';
                        } else if (cause === 'Lost-Carrier') {
                            causeBox.className = 'border border-rose-200 bg-rose-50/60 rounded-xl p-3.5';
                            causeTitle.className = 'text-xs font-bold text-rose-900';
                            causeTitle.innerText = 'Lost-Carrier (Sinyal Terputus / LOS)';
                            causeDesc.className = 'text-xs text-rose-700 m-0';
                            causeDesc.innerText = 'Indikasi red drop / LOS merah: kabel fiber optik terputus, redaman tinggi, atau router mendadak mati listrik tanpa graceful disconnect.';
                            causeIcon.className = 'fa-solid fa-triangle-exclamation text-rose-600';
                        } else if (cause === 'NAS-Reboot') {
                            causeBox.className = 'border border-amber-200 bg-amber-50/60 rounded-xl p-3.5';
                            causeTitle.className = 'text-xs font-bold text-amber-900';
                            causeTitle.innerText = 'NAS-Reboot (Router MikroTik Mulai Ulang)';
                            causeDesc.className = 'text-xs text-amber-700 m-0';
                            causeDesc.innerText = 'Router server MikroTik / BRAS mengalami reboot sehingga seluruh koneksi PPPoE dial ulang.';
                            causeIcon.className = 'fa-solid fa-rotate-right text-amber-600';
                        } else if (cause === 'NAS-Request') {
                            causeBox.className = 'border border-slate-200 bg-slate-100 rounded-xl p-3.5';
                            causeTitle.className = 'text-xs font-bold text-slate-900';
                            causeTitle.innerText = 'NAS-Request (Diputus oleh Router / Admin)';
                            causeDesc.className = 'text-xs text-slate-600 m-0';
                            causeDesc.innerText = 'Sesi diputus atas perintah MikroTik, seperti proses kick sesi, pergantian profil isolir, atau session timeout.';
                            causeIcon.className = 'fa-solid fa-shield-halved text-slate-600';
                        } else {
                            causeBox.className = 'border border-slate-200 bg-slate-50 rounded-xl p-3.5';
                            causeTitle.className = 'text-xs font-bold text-slate-800';
                            causeTitle.innerText = `Penyebab Berakhir: ${cause}`;
                            causeDesc.className = 'text-xs text-slate-600 m-0';
                            causeDesc.innerText = `Sesi tercatat selesai dengan status kode RADIUS: ${cause}.`;
                            causeIcon.className = 'fa-solid fa-circle-info text-slate-500';
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
