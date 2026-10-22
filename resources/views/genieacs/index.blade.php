<x-layouts.app>
    @section('Css')
        <style>
            /* Table formatting matching /customers/active-list */
            #genieacs-table thead th,
            #genieacs-table tbody td {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 12px;
            }

            /* Prevent RX and Temp columns from wrapping onto 2 lines */
            #genieacs-table th.col-rx,
            #genieacs-table td.col-rx {
                width: 145px !important;
                min-width: 145px !important;
                white-space: nowrap !important;
            }

            #genieacs-table th.col-temp,
            #genieacs-table td.col-temp {
                width: 130px !important;
                min-width: 130px !important;
                white-space: nowrap !important;
            }

            .dataTables_wrapper {
                position: relative;
                min-height: 200px;
                padding: 0;
            }

            .dataTables_filter {
                display: none;
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

            .dataTables_info {
                font-size: 12px;
                font-weight: 600;
                color: #64748b;
                padding: 1.25rem 1.5rem;
                float: left;
            }

            .dataTables_paginate {
                display: flex;
                justify-content: flex-end;
                align-items: center;
                padding: 1.25rem 1.5rem;
                gap: 4px;
                float: right;
                border-top: 1px solid #f1f5f9;
            }

            .dataTables_wrapper::after {
                content: "";
                display: table;
                clear: both;
            }

            @media (max-width: 640px) {
                .dataTables_info,
                .dataTables_paginate {
                    float: none;
                    justify-content: center;
                    text-align: center;
                    padding: 0.75rem 1rem;
                }
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
                transition: all 0.15s ease;
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

    <!-- Page Header -->
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
            <button type="button" id="btn-refresh-data"
                class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !h-10 flex items-center gap-2">
                <i class="fa-solid fa-arrows-rotate" id="refresh-icon"></i> Refresh Data
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
                <div class="stat-val" id="stat-total-val">{{ number_format($totalCount, 0, ',', '.') }}</div>
                <div class="stat-lbl">Total ONT Terdaftar</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-val" id="stat-online-val">{{ number_format($onlineCount, 0, ',', '.') }}</div>
                <div class="stat-lbl">Status Online (Aktif)</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#be123c;">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <div class="stat-val" id="stat-offline-val">{{ number_format($offlineCount, 0, ',', '.') }}</div>
                <div class="stat-lbl">Status Offline / Standby</div>
            </div>
        </div>
    </div>

    <!-- Table Toolbar / Live Search & Filter -->
    <div class="table-toolbar">
        <form class="w-full" id="filterForm" onsubmit="return false;">
            <div class="filter-group">
                <div style="flex: 1; min-width: 170px;">
                    <label class="form-label-custom">Status Koneksi</label>
                    <select name="status" class="form-input-custom" id="filter-status">
                        <option value="all">Semua Status</option>
                        <option value="online">Online Saja</option>
                        <option value="offline">Offline Saja</option>
                    </select>
                </div>
                <div style="flex: 2; min-width: 250px;">
                    <label class="form-label-custom">Live Search</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" name="search" class="search-input custom-search" id="custom-search"
                            placeholder="Cari serial number, username PPPoE, SSID, IP..." autocomplete="off"
                            onkeydown="return event.key != 'Enter';" />
                    </div>
                </div>
                <button type="button" id="btn-filter" class="btn-primary-admin !h-10 !px-6 flex items-center gap-2">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <button type="button" id="btn-reset-filter" class="btn-cancel !h-10 !px-4 flex items-center gap-1.5" title="Reset Filter">
                    <i class="fa-solid fa-xmark"></i> Reset
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="genieacs-table" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 90px; min-width: 90px; white-space: nowrap;">Status</th>
                        <th style="min-width: 170px; white-space: nowrap;">Perangkat (ONT)</th>
                        <th style="min-width: 150px; white-space: nowrap;">Pelanggan (PPPoE)</th>
                        <th style="min-width: 130px; white-space: nowrap;">Alamat IP</th>
                        <th style="min-width: 140px; white-space: nowrap;">Nama Wi-Fi (SSID)</th>
                        <th class="col-rx" style="text-align: center; width: 145px; min-width: 145px; white-space: nowrap !important;">Redaman RX</th>
                        <th class="col-temp" style="text-align: center; width: 130px; min-width: 130px; white-space: nowrap !important;">Suhu ONT</th>
                        <th style="text-align: center; width: 120px; min-width: 120px; white-space: nowrap;">Opsi</th>
                    </tr>
                </thead>
                <tbody style="border-top: none;">
                    <!-- Data dimuat secara asinkron (AJAX Server-Side) -->
                </tbody>
            </table>
        </div>
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
    <div class="modal-overlay modal-centered" id="rebootModal">
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
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            window._forceRefresh = false;
            let currentRebootDeviceId = null;

            $(document).ready(function() {
                // Inisialisasi DataTables Server Side
                const genieTable = $('#genieacs-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('genieacs.index') }}',
                        data: function(d) {
                            d.status = $('#filter-status').val();
                            d.refresh = window._forceRefresh ? 1 : 0;
                            window._forceRefresh = false;
                        }
                    },
                    columns: [
                        { data: 'status', name: 'status', orderable: false, searchable: false, className: 'text-center align-middle whitespace-nowrap' },
                        { data: 'device', name: 'device', orderable: false, searchable: true, className: 'align-middle' },
                        { data: 'pppoe', name: 'pppoe', orderable: false, searchable: true, className: 'align-middle' },
                        { data: 'ip', name: 'ip', orderable: false, searchable: true, className: 'align-middle font-mono' },
                        { data: 'wifi', name: 'wifi', orderable: false, searchable: true, className: 'align-middle' },
                        { data: 'rx_power', name: 'rx_power', orderable: false, searchable: false, className: 'text-center align-middle col-rx whitespace-nowrap', width: '145px' },
                        { data: 'temperature', name: 'temperature', orderable: false, searchable: false, className: 'text-center align-middle col-temp whitespace-nowrap', width: '130px' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle whitespace-nowrap' }
                    ],
                    dom: 'tpir',
                    pageLength: 25,
                    lengthChange: false,
                    info: true,
                    ordering: false,
                    responsive: true,
                    language: {
                        processing: "<i class='fa-solid fa-circle-notch fa-spin text-sky-500 text-2xl'></i>",
                        info: "Menampilkan _START_ - _END_ dari _TOTAL_ ONT",
                        infoEmpty: "Menampilkan 0 dari 0 ONT",
                        zeroRecords: "Tidak ada data ONT yang sesuai dengan filter/pencarian",
                        emptyTable: "Belum ada data ONT di GenieACS",
                        paginate: {
                            previous: '<i class="fa-solid fa-chevron-left"></i>',
                            next: '<i class="fa-solid fa-chevron-right"></i>'
                        }
                    }
                });

                // Sinkronisasi angka kartu statistik dari response AJAX
                genieTable.on('xhr', function(e, settings, json) {
                    if (json && json.stats) {
                        $('#stat-total-val').text(json.stats.total);
                        $('#stat-online-val').text(json.stats.online);
                        $('#stat-offline-val').text(json.stats.offline);
                    }
                });

                // Live Search dengan Debounce (350ms)
                let searchTimer;
                $('#custom-search').on('keyup input', function() {
                    clearTimeout(searchTimer);
                    const val = this.value;
                    searchTimer = setTimeout(function() {
                        genieTable.search(val).draw();
                    }, 350);
                });

                // Filter status koneksi
                $('#filter-status').on('change', function() {
                    genieTable.draw();
                });

                // Tombol Filter
                $('#btn-filter').on('click', function() {
                    genieTable.search($('#custom-search').val()).draw();
                });

                // Tombol Reset Filter
                $('#btn-reset-filter').on('click', function() {
                    $('#filter-status').val('all');
                    $('#custom-search').val('');
                    genieTable.search('').draw();
                });

                // Tombol Refresh Data (Hanya refresh tabel via AJAX)
                $('#btn-refresh-data').on('click', function() {
                    window._forceRefresh = true;
                    const $icon = $('#refresh-icon');
                    $icon.addClass('fa-spin');
                    genieTable.ajax.reload(function() {
                        $icon.removeClass('fa-spin');
                        showToast('Data ONT berhasil disegarkan dari GenieACS!', 'success');
                    }, false);
                });

                // Delegated listener untuk Modal Ubah Wi-Fi
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

                // Delegated listener untuk Modal Reboot
                $(document).on('click', '.btn-quick-reboot', function(e) {
                    e.preventDefault();
                    currentRebootDeviceId = $(this).data('device-id');
                    const serial = $(this).data('serial');
                    $('#reboot-serial-span').text(serial);

                    openCustomModal('rebootModal');
                });

                // Submit Form Ubah Wi-Fi via AJAX
                $('#quickWifiForm').on('submit', function(e) {
                    e.preventDefault();
                    const deviceId = $('#quick_wifi_device_id').val();
                    const ssid = $('#quick_wifi_ssid').val();
                    const password = $('#quick_wifi_password').val();
                    const btn = $('#btn-submit-quick-wifi');
                    const origHtml = btn.html();

                    btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengirim ke ONT...');

                    $.ajax({
                        url: `/genieacs/${encodeURIComponent(deviceId)}/wifi`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        contentType: 'application/json',
                        data: JSON.stringify({ ssid, password }),
                        success: function(res) {
                            btn.prop('disabled', false).html(origHtml);
                            closeCustomModal('quickWifiModal');
                            showToast(res.message || 'SSID & Password Wi-Fi berhasil diperbarui!', 'success');
                            genieTable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html(origHtml);
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengubah Wi-Fi.';
                            showToast(msg, 'error');
                        }
                    });
                });

                // Eksekusi Reboot ONT via AJAX
                $('#btn-submit-reboot').on('click', function() {
                    if (!currentRebootDeviceId) return;

                    const btn = $(this);
                    const origHtml = btn.html();
                    btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengirim...');

                    $.ajax({
                        url: `/genieacs/${encodeURIComponent(currentRebootDeviceId)}/reboot`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        contentType: 'application/json',
                        success: function(res) {
                            btn.prop('disabled', false).html(origHtml);
                            closeCustomModal('rebootModal');
                            showToast(res.message || 'Perintah Reboot berhasil dikirim ke ONT!', 'success');
                            genieTable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html(origHtml);
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengirim perintah reboot.';
                            showToast(msg, 'error');
                        }
                    });
                });
            });

            // Toggle Password Visibility
            function togglePasswordVisibility(fieldId, btn) {
                const field = document.getElementById(fieldId);
                const icon = btn.querySelector('i');
                if (!field || !icon) return;
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
        </script>
    @endsection
</x-layouts.app>
