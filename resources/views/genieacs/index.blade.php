<x-layouts.app>
    @section('Css')
        <style>
            #genieacs-table thead th,
            #genieacs-table tbody td {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 12px;
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
                text-decoration: none;
            }

            .paginate_button:hover:not(.disabled):not(.current) {
                border-color: #0ea5e9;
                color: #0ea5e9 !important;
                background: #f0f9ff;
            }

            .paginate_button.current,
            .paginate_button.active {
                background: #0ea5e9 !important;
                border-color: #0ea5e9 !important;
                color: #fff !important;
            }

            .paginate_button.disabled {
                opacity: 0.35;
                cursor: not-allowed;
                pointer-events: none;
            }
        </style>
    @endsection

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Manajemen ONT (GenieACS TR-069)
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">
                Monitoring status ONT, suhu, redaman optik, serta kontrol nama dan kata sandi Wi-Fi pelanggan via TR-069.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('settings.index', ['tab' => 'genieacs']) }}"
                class="btn-primary-admin !bg-slate-700 hover:!bg-slate-800 !h-10 flex items-center gap-2">
                <i class="fa-solid fa-sliders"></i> Pengaturan GenieACS
            </a>
            <button onclick="window.location.reload()"
                class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !h-10 flex items-center gap-2">
                <i class="fa-solid fa-rotate"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0e7ff;color:#4338ca;">
                <i class="fa-solid fa-satellite-dish"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($totalCount, 0, ',', '.') }}</div>
                <div class="stat-lbl">Total ONT Terdaftar</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($onlineCount, 0, ',', '.') }}</div>
                <div class="stat-lbl">Status Online (Aktif)</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#be123c;">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($offlineCount, 0, ',', '.') }}</div>
                <div class="stat-lbl">Status Offline / Standby</div>
            </div>
        </div>
    </div>

    <!-- Table Toolbar / Live Search & Filter -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('genieacs.index') }}" class="w-full" id="filterForm">
            <div class="filter-group">
                <div style="flex: 1; min-width: 170px;">
                    <label class="form-label-custom">Status Koneksi</label>
                    <select name="status" class="form-input-custom" id="filter-status">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="online" {{ $statusFilter === 'online' ? 'selected' : '' }}>Online Saja</option>
                        <option value="offline" {{ $statusFilter === 'offline' ? 'selected' : '' }}>Offline Saja</option>
                    </select>
                </div>
                <div style="flex: 2; min-width: 250px;">
                    <label class="form-label-custom">Live Search</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" name="search" class="search-input custom-search" id="custom-search"
                            value="{{ $search }}"
                            placeholder="Cari serial number, username PPPoE, SSID, IP..." autocomplete="off" />
                    </div>
                </div>
                <button type="submit" class="btn-primary-admin !h-10 !px-6 flex items-center gap-2">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if ($search || $statusFilter !== 'all')
                    <a href="{{ route('genieacs.index') }}" class="btn-cancel !h-10 !px-4 flex items-center gap-1.5" title="Reset Filter">
                        <i class="fa-solid fa-xmark"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="genieacs-table" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 80px;">Status</th>
                        <th>Perangkat (ONT)</th>
                        <th>Pelanggan (PPPoE)</th>
                        <th>Alamat IP</th>
                        <th>Nama Wi-Fi (SSID)</th>
                        <th style="text-align: center;">Redaman RX</th>
                        <th style="text-align: center;">Suhu ONT</th>
                        <th>Inform Terakhir</th>
                        <th style="text-align: center; width: 120px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($devices as $d)
                        <tr>
                            <!-- Status -->
                            <td style="text-align: center;">
                                @if (!empty($d['is_online']))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                        </span>
                                        ONLINE
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500">
                                        OFFLINE
                                    </span>
                                @endif
                            </td>

                            <!-- Perangkat ONT -->
                            <td>
                                <div class="font-bold text-slate-900 font-mono text-xs">{{ $d['serial_number'] }}</div>
                                <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                    <span class="font-semibold text-sky-600">{{ $d['manufacturer'] }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $d['model'] }}</span>
                                    <span class="badge !text-[9px] !py-0 !px-1.5 bg-slate-100 text-slate-600 font-mono">{{ $d['pon_mode'] }}</span>
                                </div>
                            </td>

                            <!-- Pelanggan PPPoE -->
                            <td>
                                @if (!empty($d['pppoe_username']) && $d['pppoe_username'] !== '-')
                                    <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-user text-xs text-slate-400"></i>
                                        <span>{{ $d['pppoe_username'] }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-xs">-</span>
                                @endif
                            </td>

                            <!-- IP Address -->
                            <td class="font-mono text-xs text-slate-600">
                                {{ $d['ip_address'] ?? '-' }}
                            </td>

                            <!-- SSID Wi-Fi -->
                            <td>
                                @if (!empty($d['ssid']) && $d['ssid'] !== '-')
                                    <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-wifi text-xs text-sky-500"></i>
                                        <span>{{ $d['ssid'] }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-xs">-</span>
                                @endif
                            </td>

                            <!-- Redaman Optik RX Power -->
                            <td style="text-align: center;">
                                @if (!empty($d['rx_power']))
                                    @php
                                        $cleanRx = trim(str_ireplace('dBm', '', $d['rx_power']));
                                        $numVal = floatval($cleanRx);
                                        $isWarning = $numVal < -27 || $numVal > -10;
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-mono font-bold {{ $isWarning ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200' }}">
                                        <i class="fa-solid fa-wave-square text-[10px]"></i>
                                        <span>{{ $cleanRx }} dBm</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>

                            <!-- Suhu ONT -->
                            <td style="text-align: center;">
                                @if (!empty($d['temperature']))
                                    @php
                                        $tempVal = floatval(str_replace(['°C', ' '], '', $d['temperature']));
                                        $isHot = $tempVal > 65;
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-mono font-bold {{ $isHot ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-slate-100 text-slate-700' }}">
                                        <i class="fa-solid fa-temperature-half text-[11px] {{ $isHot ? 'text-rose-500' : 'text-slate-400' }}"></i>
                                        {{ $d['temperature'] }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>

                            <!-- Last Inform -->
                            <td class="font-mono text-[11px] text-slate-500">
                                {{ $d['last_inform'] }}
                            </td>

                            <!-- Action Buttons -->
                            <td style="text-align: center;">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('genieacs.show', $d['id']) }}"
                                        class="btn-sm-action !p-2 rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-600 hover:text-white transition-colors"
                                        title="Detail & Kelola ONT">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                    <button type="button"
                                        class="btn-quick-wifi btn-sm-action !p-2 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-colors"
                                        data-device-id="{{ $d['id'] }}"
                                        data-serial="{{ $d['serial_number'] }}"
                                        data-ssid="{{ $d['ssid'] !== '-' ? $d['ssid'] : '' }}"
                                        title="Ubah Nama & Password Wi-Fi">
                                        <i class="fa-solid fa-key text-xs"></i>
                                    </button>
                                    <button type="button"
                                        class="btn-quick-reboot btn-sm-action !p-2 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors"
                                        data-device-id="{{ $d['id'] }}"
                                        data-serial="{{ $d['serial_number'] }}"
                                        title="Reboot ONT">
                                        <i class="fa-solid fa-power-off text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="dataTables_empty">
                                Tidak ada data perangkat ONT ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Seragamkan Pagination (DataTables Style) -->
        @if ($devices->hasPages())
            <div class="flex items-center justify-between p-4 border-t border-slate-100 flex-wrap gap-3">
                <span class="text-xs text-slate-500 font-semibold">
                    Menampilkan {{ $devices->firstItem() ?? 0 }} - {{ $devices->lastItem() ?? 0 }} dari {{ number_format($devices->total(), 0, ',', '.') }} ONT
                </span>
                <div class="dataTables_paginate !p-0 !border-0">
                    {{-- Previous Page Link --}}
                    @if ($devices->onFirstPage())
                        <span class="paginate_button disabled"><i class="fa-solid fa-chevron-left"></i></span>
                    @else
                        <a href="{{ $devices->previousPageUrl() }}" class="paginate_button"><i class="fa-solid fa-chevron-left"></i></a>
                    @endif

                    {{-- Pagination Elements --}}
                    @php
                        $start = max(1, $devices->currentPage() - 2);
                        $end = min($devices->lastPage(), $devices->currentPage() + 2);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $devices->url(1) }}" class="paginate_button">1</a>
                        @if($start > 2)
                            <span class="paginate_button disabled">...</span>
                        @endif
                    @endif

                    @for ($i = $start; $i <= $end; $i++)
                        @if ($i == $devices->currentPage())
                            <span class="paginate_button current">{{ $i }}</span>
                        @else
                            <a href="{{ $devices->url($i) }}" class="paginate_button">{{ $i }}</a>
                        @endif
                    @endfor

                    @if($end < $devices->lastPage())
                        @if($end < $devices->lastPage() - 1)
                            <span class="paginate_button disabled">...</span>
                        @endif
                        <a href="{{ $devices->url($devices->lastPage()) }}" class="paginate_button">{{ $devices->lastPage() }}</a>
                    @endif

                    {{-- Next Page Link --}}
                    @if ($devices->hasMorePages())
                        <a href="{{ $devices->nextPageUrl() }}" class="paginate_button"><i class="fa-solid fa-chevron-right"></i></a>
                    @else
                        <span class="paginate_button disabled"><i class="fa-solid fa-chevron-right"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Modal: Ubah Wi-Fi -->
    <div class="modal-overlay" id="quickWifiModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title flex items-center gap-2">
                    <i class="fa-solid fa-wifi text-sky-600"></i>
                    <span class="text-slate-900 font-bold text-base">Ubah Wi-Fi ONT</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('quickWifiModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="quickWifiForm" onsubmit="submitQuickWifi(event)">
                @csrf
                <input type="hidden" id="quick_wifi_device_id" name="device_id">
                <div class="modal-body p-6 space-y-4">
                    <div class="bg-sky-50 border border-sky-100 rounded-xl p-3 text-xs text-sky-800">
                        <i class="fa-solid fa-circle-info mr-1 text-sky-600"></i>
                        Perubahan SSID dan password akan dikirim langsung ke ONT <strong id="quick_wifi_serial" class="font-mono text-slate-800"></strong> via TR-069.
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Nama Wi-Fi (SSID) <span class="text-rose-500">*</span></label>
                        <input type="text" name="ssid" id="quick_wifi_ssid" class="form-input font-medium"
                            maxlength="32" placeholder="Nama Wi-Fi baru" required>
                        <small class="text-slate-400 text-[11px]">Maksimal 32 karakter.</small>
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Password Wi-Fi Baru</label>
                        <div class="relative">
                            <input type="password" name="password" id="quick_wifi_password"
                                class="form-input !pr-10 font-mono"
                                minlength="8" maxlength="63" placeholder="Minimal 8 karakter (kosongkan jika tidak diubah)">
                            <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                onclick="togglePasswordVisibility('quick_wifi_password', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-slate-400 text-[11px]">Panjang 8 - 63 karakter WPA2-PSK.</small>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl p-4 flex justify-end gap-2">
                    <button type="button" class="btn-cancel" onclick="closeCustomModal('quickWifiModal')">Batal</button>
                    <button type="submit" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 flex items-center gap-2" id="btn-submit-quick-wifi">
                        <i class="fa-solid fa-paper-plane"></i> Terapkan ke ONT
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Reboot ONT -->
    <div class="modal-overlay" id="rebootModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title flex items-center gap-2">
                    <i class="fa-solid fa-power-off text-rose-600"></i>
                    <span class="text-slate-900 font-bold text-base">Reboot ONT Pelanggan</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('rebootModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-6 text-center">
                <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fa-solid fa-rotate-right"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-800 mb-1">Restart Perangkat ONT?</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Perintah reboot akan dikirim ke ONT <span class="font-mono font-bold text-slate-800" id="reboot-serial-span"></span>. Koneksi internet pelanggan akan terputus sementara selama 1-2 menit.
                </p>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl p-4 flex justify-end gap-2">
                <button type="button" class="btn-cancel" onclick="closeCustomModal('rebootModal')">Batal</button>
                <button type="button" onclick="executeReboot()" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 flex items-center gap-2" id="btn-submit-reboot">
                    <i class="fa-solid fa-power-off"></i> Ya, Reboot Sekarang
                </button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script>
            let currentRebootDeviceId = null;

            // Delegated click listener to open Wi-Fi Modal
            $(document).on('click', '.btn-quick-wifi', function(e) {
                e.preventDefault();
                const deviceId = $(this).data('device-id');
                const serial = $(this).data('serial');
                const ssid = $(this).data('ssid') || '';

                $('#quick_wifi_device_id').val(deviceId);
                $('#quick_wifi_serial').text(serial);
                $('#quick_wifi_ssid').val(ssid);
                $('#quick_wifi_password').val('');

                openCustomModal('quickWifiModal');
            });

            // Delegated click listener to open Reboot Modal
            $(document).on('click', '.btn-quick-reboot', function(e) {
                e.preventDefault();
                currentRebootDeviceId = $(this).data('device-id');
                const serial = $(this).data('serial');
                $('#reboot-serial-span').text(serial);

                openCustomModal('rebootModal');
            });

            // Toggle Password Visibility
            function togglePasswordVisibility(fieldId, btn) {
                const field = document.getElementById(fieldId);
                const icon = btn.querySelector('i');
                if (field.type === 'password') {
                    field.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    field.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }

            // Submit Wi-Fi Form
            function submitQuickWifi(e) {
                e.preventDefault();
                const deviceId = document.getElementById('quick_wifi_device_id').value;
                const ssid = document.getElementById('quick_wifi_ssid').value;
                const password = document.getElementById('quick_wifi_password').value;
                const btn = document.getElementById('btn-submit-quick-wifi');
                const origText = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengirim ke ONT...';

                fetch(`/genieacs/${encodeURIComponent(deviceId)}/wifi`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ssid, password })
                })
                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                .then(res => {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                    if (res.status === 200 && res.body.success) {
                        showToast(res.body.message, 'success');
                        closeCustomModal('quickWifiModal');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showToast(res.body.message || 'Gagal mengubah Wi-Fi.', 'error');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                    showToast('Network error saat mengirim ke GenieACS.', 'error');
                });
            }

            // Execute Reboot
            function executeReboot() {
                if (!currentRebootDeviceId) return;

                const btn = document.getElementById('btn-submit-reboot');
                const origText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengirim...';

                fetch(`/genieacs/${encodeURIComponent(currentRebootDeviceId)}/reboot`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                .then(res => {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                    if (res.status === 200 && res.body.success) {
                        showToast(res.body.message, 'success');
                        closeCustomModal('rebootModal');
                    } else {
                        showToast(res.body.message || 'Gagal mengirim perintah reboot.', 'error');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                    showToast('Network error saat menghubungi GenieACS.', 'error');
                });
            }

            // Live Search with Debounce
            let searchTimer;
            $('#custom-search').on('keyup input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    $('#filterForm').submit();
                }, 600);
            });

            $('#filter-status').on('change', function() {
                $('#filterForm').submit();
            });
        </script>
    @endsection
</x-layouts.app>
