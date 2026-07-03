<x-layouts.app>
    @section('Css')
        <style>
            /* Table styling matching /pre-orders to prevent overflow */
            #pppoe-table thead th,
            #pppoe-table tbody td {
                text-transform: uppercase !important;
                font-weight: 600;
            }

            #pppoe-table tbody td {
                font-size: 12px;
            }

            .badge-pppoe {
                background: #e0f2fe;
                color: #0369a1;
                padding: 4px 8px;
                border-radius: 6px;
                font-size: 10px;
                font-weight: 800;
            }
        </style>
    @endsection

    <!-- Page header -->
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">PPPoE Aktif</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Monitoring pengguna PPPoE yang sedang aktif secara
                real-time.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="button" onclick="location.reload()"
                class="btn-primary-admin !flex !items-center !justify-center gap-2">
                <i class="fa-solid fa-sync" id="refresh-icon"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input" id="custom-search"
                placeholder="Cari nama, sales, atau IP address..." autocomplete="off" />
        </div>
        <div style="display:flex;gap:8px;">
            <div
                style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                Total Aktif: <span class="text-primary">{{ count($pppoeActive) }}</span>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="pppoe-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="width: 200px;">User PPPoE</th>
                        <th style="width: 150px;">Sales</th>
                        <th style="text-align: center; width: 120px;">Uptime</th>
                        <th style="text-align: center; width: 150px;">Remote Address</th>
                        <th style="text-align: center; width: 180px;">Caller ID</th>
                        <th style="text-align: center; width: 80px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pppoeActive as $item)
                        @php
                            $cid = btoa_safe($item['name'] ?? 'unknown');
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;">
                                    {{ $item['name'] ?? '-' }}
                                    @if ($item['is_isolir'])
                                        <span
                                            style="background:#fef2f2; color:#dc2626; padding:2px 6px; border-radius:4px; font-size:9px; font-weight:800; margin-left:4px;">ISOLIR</span>
                                    @endif
                                </div>
                            </td>
                            <td style="font-weight: 600; color: #475569; font-size: 11px;">
                                {{ $item['sales_name'] ?? '-' }}</td>
                            <td style="text-align: center; font-family: monospace; color: #64748b;">
                                {{ format_uptime_hms($item['uptime'] ?? '') }}
                            </td>
                            <td
                                style="text-align: center; font-family: monospace; font-weight: 700; color: {{ $item['is_isolir'] ? '#dc2626' : '#0ea5e9' }};">
                                {{ $item['address'] ?? '-' }}
                            </td>
                            <td style="text-align: center; color: #64748b; font-size: 11px;">
                                {{ $item['caller-id'] ?? '-' }}</td>
                            <td style="text-align: center;">
                                <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl" title="Detail"
                                    onclick="showDetail('{{ $item['name'] ?? '' }}', '{{ $item['sales_name'] ?? '' }}', '{{ $item['address'] ?? '' }}', '{{ $item['uptime'] ?? '' }}', '{{ $item['caller-id'] ?? '' }}', '{{ $item['service'] ?? '' }}', '{{ $item['bytes-in'] ?? 0 }}', '{{ $item['bytes-out'] ?? 0 }}')">
                                    <i class="fa-solid fa-eye text-primary"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
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
                <button type="button" class="btn-cancel w-full" onclick="closeCustomModal('pppoeDetailModal')">
                    <i class="fa-solid fa-xmark"></i> Tutup Detail
                </button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                const pppoeTable = $('#pppoe-table').DataTable({
                    "pageLength": 10,
                    "lengthChange": false,
                    "ordering": true,
                    "info": false,
                    "responsive": true,
                    "dom": 'tpir',
                    "language": {
                        "zeroRecords": "Data PPPoE tidak ditemukan",
                        "paginate": {
                            "previous": '<i class="fa-solid fa-chevron-left"></i>',
                            "next": '<i class="fa-solid fa-chevron-right"></i>'
                        }
                    }
                });

                $('#custom-search').on('keyup', function() {
                    pppoeTable.search(this.value).draw();
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
                let w = 0,
                    d = 0,
                    h = 0,
                    m = 0,
                    s = 0;
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

            function showDetail(name, sales, addr, uptime, caller, svc, bin, bout) {
                $('#modal-content').html(`
                    <div class="space-y-4">
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">User Name</span><span class="text-sm font-bold text-slate-900">${name}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Sales</span><span class="text-sm font-bold text-slate-900">${sales}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Remote Address</span><span class="text-sm font-bold text-primary font-mono">${addr}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Uptime</span><span class="text-sm font-bold text-slate-700">${formatUptimeToHMS(uptime)}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Total Upload (TX)</span><span class="text-sm font-bold text-orange-600">${formatBytes(bin)}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Total Download (RX)</span><span class="text-sm font-bold text-sky-600">${formatBytes(bout)}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Caller ID (MAC)</span><span class="text-sm font-bold text-slate-700 font-mono">${caller || '-'}</span></div>
                        
                        <div class="mt-6">
                            <button onclick="createRemoteTunnel('${addr}')" class="btn-primary-admin w-full flex items-center justify-center gap-2" style="padding: 12px; border-radius: 12px; font-weight: 700; width: 100%;">
                                <i class="fa-solid fa-satellite-dish"></i> Remote Akses ONT/ONU
                            </button>
                        </div>
                    </div>
                `);
                openCustomModal('pppoeDetailModal');
            }

            function createRemoteTunnel(ip) {
                const btn = $(event.currentTarget);
                const originalHtml = btn.html();
                btn.html('<i class="fa-solid fa-spinner fa-spin"></i> Menyiapkan tunnel...').prop('disabled', true).css('opacity', '0.7');
                
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
                            window.open(res.url, '_blank');
                        } else {
                            showToast(res.message, 'error');
                        }
                    },
                    error: function(err) {
                        btn.html(originalHtml).prop('disabled', false).css('opacity', '1');
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
