<x-layouts.app>
    @section('Css')
        <style>
            #pppoe-table thead th,
            #pppoe-table tbody td {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 12px;
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
            }

            .paginate_button.current {
                background: #0ea5e9 !important;
                border-color: #0ea5e9 !important;
                color: #fff !important;
            }
        </style>
    @endsection

    <!-- Page Header -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                PPPoE Aktif (Realtime)
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">
                Monitoring pengguna PPPoE yang sedang terhubung secara live langsung dari router MikroTik.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="btn-refresh-live" class="btn-primary-admin !h-8.5 !px-3.5 text-xs font-semibold shadow-xs">Refresh</button>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="stats-grid mb-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0284c7;">
                <i class="fa-solid fa-network-wired"></i>
            </div>
            <div>
                <div class="stat-val" id="stat-total-active">{{ number_format($totalActive, 0, ',', '.') }}</div>
                <div class="stat-lbl">Total Sesi Aktif</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-val" id="stat-total-normal">{{ number_format($totalNormal, 0, ',', '.') }}</div>
                <div class="stat-lbl">Layanan Aktif (Normal)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#be123c;">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <div class="stat-val" id="stat-total-isolir">{{ number_format($totalIsolir, 0, ',', '.') }}</div>
                <div class="stat-lbl">Pelanggan Terisolir</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe;color:#6d28d9;">
                <i class="fa-solid fa-server"></i>
            </div>
            <div>
                <div class="stat-val truncate" style="font-size:15px;" title="{{ $routerName }}">{{ $routerName }}</div>
                <div class="stat-lbl font-mono text-[11px]">{{ $routerHost }}</div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input !h-8.5 !text-xs" id="custom-search"
                placeholder="Live Search user, sales, IP, atau MAC..." autocomplete="off" />
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:nowrap;">
            <!-- Filter Status -->
            <div style="min-width: 160px; max-width: 190px;">
                <select id="filter-status" class="form-input-custom !h-8.5 !text-xs !bg-white !border-slate-300 !w-full cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="normal">Normal (Aktif)</option>
                    <option value="isolir">Terisolir (172.30.x.x)</option>
                </select>
            </div>

            <!-- Per Page Length -->
            <div style="min-width: 110px; max-width: 130px;">
                <select id="filter-length" class="form-input-custom !h-8.5 !text-xs !bg-white !border-slate-300 !w-full cursor-pointer">
                    <option value="10">10 Baris</option>
                    <option value="25">25 Baris</option>
                    <option value="50">50 Baris</option>
                    <option value="100">100 Baris</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="pppoe-table" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="width: 220px;">User PPPoE</th>
                        <th style="width: 150px;">Sales</th>
                        <th style="text-align: center; width: 120px;">Uptime</th>
                        <th style="text-align: center; width: 150px;">Remote Address</th>
                        <th style="text-align: center; width: 170px;">Caller ID (MAC)</th>
                        <th style="text-align: center; width: 70px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Populated dynamically via server-side DataTables -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal-overlay" id="pppoeDetailModal">
        <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-circle-info text-primary"></i>
                    <span class="text-slate-900 font-bold">Detail Koneksi PPPoE</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('pppoeDetailModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-6" id="modal-content">
                <!-- Content injected by JS -->
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold w-full" onclick="closeCustomModal('pppoeDetailModal')">Tutup</button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            let forceRefresh = false;
            let pppoeTable = null;

            $(document).ready(function() {
                pppoeTable = $('#pppoe-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('pppoe.data') }}',
                        data: function(d) {
                            d.status = $('#filter-status').val();
                            if (forceRefresh) {
                                d.refresh = 1;
                            }
                        },
                        dataSrc: function(json) {
                            forceRefresh = false;
                            if (json.totalActive !== undefined) {
                                $('#stat-total-active').text(new Intl.NumberFormat('id-ID').format(json.totalActive));
                                $('#stat-total-normal').text(new Intl.NumberFormat('id-ID').format(json.totalNormal));
                                $('#stat-total-isolir').text(new Intl.NumberFormat('id-ID').format(json.totalIsolir));
                            }
                            return json.data || [];
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                        { data: 'user_html', name: 'name' },
                        { data: 'sales_html', name: 'sales_name' },
                        { data: 'uptime_html', name: 'uptime', className: 'text-center' },
                        { data: 'address_html', name: 'address', className: 'text-center' },
                        { data: 'caller_html', name: 'caller_id', className: 'text-center' },
                        { data: 'opsi', name: 'opsi', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    pageLength: 10,
                    lengthChange: false,
                    ordering: true,
                    order: [[1, 'asc']],
                    info: false,
                    responsive: true,
                    dom: 'tpir',
                    language: {
                        processing: "<i class='fa-solid fa-circle-notch fa-spin text-sky-500 text-2xl'></i>",
                        zeroRecords: "Data PPPoE tidak ditemukan",
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
                        pppoeTable.search(val).draw();
                    }, 300);
                });

                // Filter Status Change
                $('#filter-status').on('change', function() {
                    pppoeTable.draw();
                });

                // Per Page Length Change
                $('#filter-length').on('change', function() {
                    pppoeTable.page.len(parseInt(this.value)).draw();
                });

                // Live Refresh Button
                $('#btn-refresh-live').on('click', function() {
                    const $btn = $(this);
                    const $icon = $('#refresh-icon');
                    $icon.addClass('fa-spin');
                    $btn.prop('disabled', true).css('opacity', '0.7');
                    forceRefresh = true;

                    pppoeTable.ajax.reload(function() {
                        $icon.removeClass('fa-spin');
                        $btn.prop('disabled', false).css('opacity', '1');
                        if (typeof showToast === 'function') {
                            showToast('Data PPPoE aktif berhasil diperbarui langsung dari router.', 'success');
                        }
                    });
                });
            });

            function formatBytes(bytes) {
                let b = parseInt(bytes || 0);
                if (b >= 1073741824) return (b / 1073741824).toFixed(2) + ' GB';
                if (b >= 1048576) return (b / 1048576).toFixed(2) + ' MB';
                if (b >= 1024) return (b / 1024).toFixed(2) + ' KB';
                return b + ' B';
            }

            function formatUptimeToHMS(u) {
                if (!u) return '00:00:00';
                let w = 0, d = 0, h = 0, m = 0, s = 0;
                const mw = u.match(/(\d+)w/),
                    md = u.match(/(\d+)d/),
                    mh = u.match(/(\d+)h/),
                    mm = u.match(/(\d+)m/),
                    ms = u.match(/(\d+)s/);
                if (mw) w = parseInt(mw[1]);
                if (md) d = parseInt(md[1]);
                if (mh) h = parseInt(mh[1]);
                if (mm) m = parseInt(mm[1]);
                if (ms) s = parseInt(ms[1]);
                let totalH = (w * 168) + (d * 24) + h;
                return String(totalH).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            }

            function showDetail(item) {
                if (typeof item === 'string') {
                    try { item = JSON.parse(item); } catch (e) {}
                }
                const name = item.name || '-';
                const sales = item.sales || '-';
                const addr = item.address || '-';
                const uptime = item.uptime || '';
                const caller = item.caller || '-';
                const bin = item.bytes_in || 0;
                const bout = item.bytes_out || 0;
                const isIsolir = item.is_isolir || false;

                $('#modal-content').html(`
                    <div class="space-y-4">
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">User PPPoE</span><span class="text-sm font-bold text-slate-900">${name}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Status</span><span class="text-xs font-bold ${isIsolir ? 'text-rose-600' : 'text-emerald-600'}">${isIsolir ? 'TERISOLIR (172.30.x.x)' : 'AKTIF NORMAL'}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Sales</span><span class="text-sm font-bold text-slate-900">${sales}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Remote Address</span><span class="text-sm font-bold ${isIsolir ? 'text-rose-600' : 'text-primary'} font-mono">${addr}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Uptime</span><span class="text-sm font-bold text-slate-700 font-mono">${formatUptimeToHMS(uptime)}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Total Upload (TX)</span><span class="text-sm font-bold text-orange-600 font-mono">${formatBytes(bin)}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Total Download (RX)</span><span class="text-sm font-bold text-sky-600 font-mono">${formatBytes(bout)}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Caller ID (MAC)</span><span class="text-sm font-bold text-slate-700 font-mono">${caller || '-'}</span></div>
                        
                        <div class="mt-6">
                            <button onclick="createRemoteTunnel('${addr}')" class="btn-primary-admin w-full !h-8.5 text-xs font-semibold shadow-xs">Remote Akses ONT/ONU</button>
                        </div>
                    </div>
                `);
                openCustomModal('pppoeDetailModal');
            }

            function createRemoteTunnel(ip) {
                const btn = $(event.currentTarget);
                const originalHtml = btn.html();
                btn.html('<i class="fa-solid fa-spinner fa-spin"></i> Menyiapkan tunnel...').prop('disabled', true).css('opacity', '0.7');
                
                // Buka tab baru SEBELUM proses AJAX
                const newTab = window.open('about:blank', '_blank');
                if (newTab) {
                    const loadingHtml = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Menyiapkan Remote Akses</title>
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <script src="https://cdn.tailwindcss.com"><\/script>
                        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
                        <style>
                            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
                            body { font-family: 'Inter', sans-serif; background: #f8fafc; }
                            .pulse-ring { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
                            @keyframes pulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: .7; transform: scale(1.05); } }
                            @keyframes slide { 0% { transform: translateX(-100%); } 100% { transform: translateX(200%); } }
                        </style>
                    </head>
                    <body class="flex items-center justify-center min-h-screen m-0">
                        <div class="bg-white p-10 rounded-3xl shadow-xl flex flex-col items-center text-center max-w-md w-full mx-4 border border-slate-100">
                            <div class="w-24 h-24 mb-6 rounded-full bg-sky-50 flex items-center justify-center pulse-ring">
                                <i class="fa-solid fa-satellite-dish text-4xl text-sky-500"></i>
                            </div>
                            <h2 class="text-2xl font-extrabold text-slate-800 mb-2">Membangun Tunnel Aman</h2>
                            <p class="text-slate-500 text-sm mb-8" id="status-text">Sedang menegosiasikan enkripsi dan rute dengan server Cloudflare. Proses ini membutuhkan waktu beberapa detik...</p>
                            <div class="w-full bg-slate-100 rounded-full h-2 mb-2 overflow-hidden relative">
                                <div class="bg-sky-500 h-2 rounded-full absolute top-0 left-0 w-1/2" style="animation: slide 1.5s ease-in-out infinite alternate;"></div>
                            </div>
                        </div>
                    </body>
                    </html>
                    `;
                    newTab.document.write(loadingHtml);
                    newTab.document.close();
                }
                
                $.ajax({
                    url: '{{ route('pppoe.remote_tunnel') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ip: ip,
                        port: 80
                    },
                    success: function(res) {
                        btn.html(originalHtml).prop('disabled', false).css('opacity', '1');
                        if (res.success) {
                            showToast(res.message, 'success');
                            if (newTab) {
                                newTab.document.getElementById('status-text').innerHTML = 'Tunnel aktif! Mengarahkan lalu lintas dalam <span id="countdown" class="font-bold text-sky-600 text-base">4</span> detik untuk memastikan kestabilan jalur...';
                                let count = 4;
                                const interval = setInterval(() => {
                                    count--;
                                    const countEl = newTab.document.getElementById('countdown');
                                    if(countEl) countEl.innerText = count;
                                    
                                    if(count <= 0) {
                                        clearInterval(interval);
                                        newTab.location.href = res.url;
                                    }
                                }, 1000);
                            } else {
                                window.open(res.url, '_blank');
                            }
                        } else {
                            showToast(res.message, 'error');
                            if (newTab) newTab.close();
                        }
                    },
                    error: function(err) {
                        btn.html(originalHtml).prop('disabled', false).css('opacity', '1');
                        if (newTab) newTab.close();
                        let msg = 'Terjadi kesalahan sistem.';
                        if (err.responseJSON && err.responseJSON.message) {
                            msg = err.responseJSON.message;
                        }
                        showToast(msg, 'error');
                    }
                });
            }
        </script>
    @endsection
</x-layouts.app>
