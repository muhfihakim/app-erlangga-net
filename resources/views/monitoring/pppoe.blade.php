<x-layouts.app>
    @section('Css')
        <style>
            /* Toolbar Styles */
            .table-toolbar {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1rem 1.25rem;
                display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:1rem;
            }
            .search-wrap { position:relative; flex:1; min-width:200px; max-width:360px; }
            .search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:13px; }
            .search-input { width:100%; padding:9px 12px 9px 36px; border:2px solid #e2e8f0; border-radius:10px; font-size:13px; outline:none; transition:all .25s; background:#f8fafc; }
            .search-input:focus { border-color:#0ea5e9; background:#fff; }
            #pppoe-table thead th, #pppoe-table tbody td { text-transform:uppercase !important; font-weight:600; }
            #pppoe-table tbody td { font-size:12px; vertical-align:middle !important; }
            .badge-pppoe { background:#e0f2fe; color:#0369a1; padding:4px 8px; border-radius:6px; font-size:10px; font-weight:800; }
        </style>
    @endsection

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">PPPoE Active Connections</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Monitoring pengguna PPPoE yang sedang aktif secara real-time.</p>
        </div>
        <div>
            <button type="button" onclick="pppoeTable.ajax.reload(null, false)" class="btn-primary-admin !flex !items-center !justify-center gap-2">
                <i class="fa-solid fa-sync" id="refresh-icon"></i> Refresh Data
            </button>
        </div>
    </div>

    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" id="custom-search" class="search-input" placeholder="Cari nama atau IP address..." autocomplete="off" />
        </div>
        <div style="display:flex;gap:8px;">
            <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; background:#f8fafc; padding:6px 12px; border-radius:8px; border:1px solid #e2e8f0;">
                Total Aktif: <span id="total-active" class="text-primary">0</span>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table id="pppoe-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align:center; width:50px;">#</th>
                        <th>User PPPoE</th>
                        <th style="text-align:center;">Uptime</th>
                        <th style="text-align:center;">Remote Address</th>
                        <th style="text-align:center;">Caller ID</th>
                        <th style="text-align:center;">Service</th>
                        <th style="text-align:center; width: 80px;">Opsi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="pppoeDetailModal">
        <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title"><i class="fa-solid fa-circle-info text-primary"></i> <span class="text-slate-900 font-bold">Detail Koneksi PPPoE</span></div>
                <button type="button" class="modal-close" onclick="closeCustomModal('pppoeDetailModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body p-6" id="modal-content"></div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button type="button" class="btn-cancel" onclick="closeCustomModal('pppoeDetailModal')">Tutup</button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            let pppoeTable;

            $(document).ready(function() {
                pppoeTable = $('#pppoe-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('pppoe.data') }}",
                    pageLength: 10,
                    lengthChange: false,
                    ordering: false,
                    info: false,
                    dom: 'tpir',
                    columns: [
                        { data: null, render: (d,t,r,meta) => `<div style="text-align:center; font-weight:700; color:#94a3b8;">${meta.row + meta.settings._iDisplayStart + 1}</div>` },
                        { data: 'name', render: (d) => `<div style="font-weight:700; color:#0f172a;">${d}</div>` },
                        { data: 'uptime', render: (d) => `<div style="text-align:center; font-family:monospace; color:#64748b;">${formatUptimeToHMS(d)}</div>` },
                        { data: 'address', render: (d) => `<div style="text-align:center; font-family:monospace; color:#0ea5e9; font-weight:700;">${d}</div>` },
                        { data: 'caller-id', render: (d) => `<div style="text-align:center; color:#64748b; font-size:11px;">${d || '-'}</div>` },
                        { data: 'service', render: (d) => `<div style="text-align:center;"><span class="badge-pppoe">${d}</span></div>` },
                        { data: null, render: (d,t,r) => `<div style="text-align:center;">
                            <button type="button" class="topbar-icon-btn !w-9 !h-9" onclick="showDetail('${r.name}', '${r.address}', '${r.uptime}', '${r['caller-id']}', '${r.service}', '${r['bytes-in'] || 0}', '${r['bytes-out'] || 0}')">
                                <i class="fa-solid fa-eye text-primary"></i>
                            </button></div>`
                        }
                    ],
                    drawCallback: function() {
                        $('#total-active').text(this.api().page.info().recordsTotal);
                    },
                    language: { zeroRecords: "Data tidak ditemukan", paginate: { previous: '<i class="fa-solid fa-chevron-left"></i>', next: '<i class="fa-solid fa-chevron-right"></i>' } }
                });

                $('#custom-search').on('keyup', function() { pppoeTable.search(this.value).draw(); });
                
                // Refresh list automatically every 10 seconds
                setInterval(() => pppoeTable.ajax.reload(null, false), 10000);
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
                let w=0, d=0, h=0, m=0, s=0;
                const mw=u.match(/(\d+)w/), md=u.match(/(\d+)d/), mh=u.match(/(\d+)h/), mm=u.match(/(\d+)m/), ms=u.match(/(\d+)s/);
                if(mw)w=parseInt(mw[1]); if(md)d=parseInt(md[1]); if(mh)h=parseInt(mh[1]); if(mm)m=parseInt(mm[1]); if(ms)s=parseInt(ms[1]);
                let totalH = (w*168)+(d*24)+h;
                return String(totalH).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
            }

            function showDetail(name, addr, uptime, caller, svc, bin, bout) {
                $('#modal-content').html(`
                    <div class="space-y-4">
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">User Name</span><span class="text-sm font-bold text-slate-900">${name}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Remote Address</span><span class="text-sm font-bold text-primary font-mono">${addr}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Uptime</span><span class="text-sm font-bold text-slate-700">${formatUptimeToHMS(uptime)}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Total Upload (TX)</span><span class="text-sm font-bold text-orange-600">${formatBytes(bin)}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">Total Download (RX)</span><span class="text-sm font-bold text-sky-600">${formatBytes(bout)}</span></div>
                        <div class="flex justify-between"><span class="text-[11px] font-bold text-slate-400 uppercase">Caller ID (MAC)</span><span class="text-sm font-bold text-slate-700 font-mono">${caller || '-'}</span></div>
                    </div>
                `);
                openCustomModal('pppoeDetailModal');
            }
        </script>
    @endsection
</x-layouts.app>
