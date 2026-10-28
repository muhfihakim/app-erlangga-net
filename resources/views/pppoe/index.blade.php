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

    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Manajemen PPPoE
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola data akun PPPoE pelanggan, IP Address, router,
                dan status billing.</p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('pppoe_accounts.sync_radius') }}" method="POST"
                onsubmit="return confirm('Mulai sinkronisasi data dari server RADIUS?');">
                @csrf
                <button type="submit"
                    class="btn-primary-admin !bg-indigo-600 hover:!bg-indigo-700 !h-10 flex items-center gap-2">
                    <i class="fa-solid fa-rotate"></i> Sync RADIUS
                </button>
            </form>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="stats-grid mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0e7ff;color:#4338ca;">
                <i class="fa-solid fa-users-gear"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($totalPppoeCount, 0, ',', '.') }}</div>
                <div class="stat-lbl">Total Akun PPPoE</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($totalPaidCount, 0, ',', '.') }}</div>
                <div class="stat-lbl">Status Terbayar</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#be123c;">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($totalUnpaidCount, 0, ',', '.') }}</div>
                <div class="stat-lbl">Status Belum Bayar</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecdd3;">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <div class="stat-val text-rose-600">{{ number_format($totalIsolirCount ?? 0, 0, ',', '.') }}</div>
                <div class="stat-lbl">Status Terisolir</div>
            </div>
        </div>
    </div>

    <!-- Table Toolbar -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('pppoe_accounts.index') }}" class="w-full" id="filterForm">
            <div class="filter-group">
                <div style="flex: 1; min-width: 160px;">
                    <label class="form-label-custom">Status Akun</label>
                    <select name="status_invoice" class="form-input-custom" id="filter-status">
                        <option value="">Semua Status</option>
                        <option value="terbayar" {{ $filterStatus === 'terbayar' ? 'selected' : '' }}>Terbayar</option>
                        <option value="belum_bayar" {{ $filterStatus === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="isolir" {{ $filterStatus === 'isolir' ? 'selected' : '' }}>Terisolir</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label-custom">Paket Layanan</label>
                    <select name="paket_id" class="form-input-custom" id="filter-paket">
                        <option value="">Semua Paket</option>
                        @foreach ($pakets as $p)
                            <option value="{{ $p->id }}" {{ $filterPaket == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_paket }} (Rp {{ number_format($p->harga, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label class="form-label-custom">Tipe Bayar</label>
                    <select name="tipe_pembayaran" class="form-input-custom" id="filter-tipe">
                        <option value="">Semua Tipe</option>
                        <option value="prepaid" {{ $filterTipe === 'prepaid' ? 'selected' : '' }}>Prepaid</option>
                        <option value="postpaid" {{ $filterTipe === 'postpaid' ? 'selected' : '' }}>Postpaid</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 160px;">
                    <label class="form-label-custom">Router / NAS</label>
                    <select name="router" class="form-input-custom" id="filter-router">
                        <option value="">Semua Router</option>
                        <option value="All" {{ $filterRouter === 'All' ? 'selected' : '' }}>All (Semua Router)
                        </option>
                        @foreach ($routers as $r)
                            <option value="{{ $r->shortname }}"
                                {{ $filterRouter === $r->shortname ? 'selected' : '' }}>
                                {{ $r->shortname }} ({{ $r->nasname }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="flex: 1.5; min-width: 220px;">
                    <label class="form-label-custom">Live Search</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input custom-search" id="custom-search"
                            placeholder="Cari username, nama, IP..." autocomplete="off"
                            onkeydown="return event.key != 'Enter';" />
                    </div>
                </div>
                <button type="submit" class="btn-primary-admin !h-10 !px-6 flex items-center gap-2">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="pppoe-table" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: left;">Pelanggan</th>
                        <th style="text-align: left; width: 180px;">Username</th>
                        <th style="text-align: left; width: 190px;">Profile/Paket</th>
                        <th style="text-align: center; width: 100px;">Router</th>
                        <th style="text-align: center; width: 120px;">IP Address</th>
                        <th style="text-align: center; width: 110px;">Tipe Bayar</th>
                        <th style="text-align: center; width: 130px;">Status Invoice</th>
                        <th style="text-align: center; width: 90px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal-overlay" id="editPppoeModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-pen-to-square text-amber-500"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Edit Akun PPPoE</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('editPppoeModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-tabs bg-white border-b border-slate-200">
                <button class="modal-tab active" data-tab="edit-pppoe-tab-akun">
                    <i class="fa-solid fa-user-shield"></i> Akun PPPoE
                </button>
                <button class="modal-tab" data-tab="edit-pppoe-tab-pembayaran">
                    <i class="fa-solid fa-money-bill"></i> Pembayaran
                </button>
            </div>

            <form id="editPppoeForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body bg-white">
                    <!-- Tab Akun PPPoE -->
                    <div class="tab-panel active" id="edit-pppoe-tab-akun">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">Username <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                                    id="edit_username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">Password <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                                    id="edit_password" name="password" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Profile / Paket <span
                                    class="text-rose-500">*</span></label>
                            <select class="form-input-custom w-full bg-white text-slate-900 border-slate-300"
                                id="edit_paket_id" name="paket_id" required>
                                @foreach ($pakets as $paket)
                                    <option value="{{ $paket->id }}">{{ $paket->nama_paket }} - Rp
                                        {{ number_format($paket->harga, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">Router / NAS</label>
                                <select class="form-input-custom w-full bg-white text-slate-900 border-slate-300"
                                    id="edit_router" name="router">
                                    <option value="All">Semua Router (ALL)</option>
                                    @foreach ($routers as $r)
                                        <option value="{{ $r->shortname }}">{{ $r->shortname }}
                                            ({{ $r->nasname }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">IP Address</label>
                                <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                                    id="edit_ip_address" name="ip_address">
                            </div>
                        </div>
                    </div>

                    <!-- Tab Pembayaran -->
                    <div class="tab-panel" id="edit-pppoe-tab-pembayaran">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">Tipe Pembayaran</label>
                                <select class="form-input-custom w-full bg-white text-slate-900 border-slate-300"
                                    id="edit_tipe_pembayaran" name="tipe_pembayaran">
                                    <option value="postpaid">Postpaid</option>
                                    <option value="prepaid">Prepaid</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">Tanggal Aktif</label>
                                <input type="date" class="form-input bg-white text-slate-900 border-slate-300"
                                    id="edit_tanggal_aktif" name="tanggal_aktif">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Periode Billing</label>
                            <select class="form-input-custom w-full bg-white text-slate-900 border-slate-300"
                                id="edit_periode_billing" name="periode_billing">
                                <option value="fixed">Fixed</option>
                                <option value="renewal">Renewal</option>
                            </select>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">Pajak (Rp)</label>
                                <input type="number" class="form-input bg-white text-slate-900 border-slate-300"
                                    id="edit_pajak" name="pajak">
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">Diskon (Rp)</label>
                                <input type="number" class="form-input bg-white text-slate-900 border-slate-300"
                                    id="edit_diskon" name="diskon">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Status Invoice</label>
                            <select class="form-input-custom w-full bg-white text-slate-900 border-slate-300"
                                id="edit_status_invoice" name="status_invoice">
                                <option value="belum_bayar">Belum Bayar</option>
                                <option value="terbayar">Terbayar</option>
                            </select>
                        </div>
                        <div class="form-group mt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="edit_generate_invoice" name="generate_invoice"
                                    class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                <span class="text-sm font-medium text-slate-700">Generate Invoice</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        onclick="closeCustomModal('editPppoeModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit"
                        class="btn-save !flex !items-center !justify-center gap-2 !bg-amber-500 hover:!bg-amber-600">
                        <i class="fa-solid fa-rotate"></i> Update PPPoE
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Isolir PPPoE -->
    <div class="modal-overlay" id="isolirPppoeModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm border border-rose-100 shrink-0">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Isolir Akun PPPoE</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Putus akses & alihkan ke profil isolir.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('isolirPppoeModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="isolirPppoeForm" method="POST" action="">
                @csrf
                <div class="p-5 space-y-3.5">
                    <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 space-y-1">
                        <div class="font-bold flex items-center gap-1.5 text-amber-900">
                            <i class="fa-solid fa-triangle-exclamation"></i> Perhatian:
                        </div>
                        <p class="m-0 leading-relaxed">
                            Akun <strong id="isolirPppoeUsername" class="font-mono text-rose-700 font-bold"></strong> akan dialihkan ke group FreeRADIUS <strong>ISOLIR</strong> (kecepatan 128k/128k, IP 172.30.x.x) dan sesi aktif di MikroTik akan di-kick agar dial ulang secara otomatis.
                        </p>
                    </div>

                    <div>
                        <label class="form-label text-slate-700 font-medium block mb-1 text-xs">Alasan Isolir</label>
                        <textarea name="reason" id="isolirPppoeReason" class="form-textarea !font-normal w-full text-xs text-slate-800 border-slate-300" rows="3" placeholder="Contoh: Menunggak tagihan bulan ini / Pelanggaran penggunaan" required>Isolir manual oleh Admin</textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('isolirPppoeModal')">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !text-white !h-9 !px-5 text-xs font-semibold">
                        Isolir Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Buka Isolir PPPoE -->
    <div class="modal-overlay" id="bukaIsolirPppoeModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm border border-emerald-100 shrink-0">
                        <i class="fa-solid fa-unlock-keyhole"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Buka Isolir PPPoE</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Kembalikan kecepatan dan akses normal.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('bukaIsolirPppoeModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="bukaIsolirPppoeForm" method="POST" action="">
                @csrf
                <div class="p-5 space-y-3.5">
                    <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 space-y-1">
                        <div class="font-bold flex items-center gap-1.5 text-emerald-900">
                            <i class="fa-solid fa-circle-check"></i> Konfirmasi Pembukaan:
                        </div>
                        <p class="m-0 leading-relaxed">
                            Akun <strong id="bukaIsolirPppoeUsername" class="font-mono text-emerald-800 font-bold"></strong> akan dikembalikan ke profil paket aslinya di FreeRADIUS dan sesi aktif di MikroTik akan di-kick agar dial ulang dengan kecepatan normal.
                        </p>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('bukaIsolirPppoeModal')">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !bg-emerald-600 hover:!bg-emerald-700 !text-white !h-9 !px-5 text-xs font-semibold">
                        Buka Isolir Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            let table;

            $(document).ready(function() {
                table = $('#pppoe-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('pppoe_accounts.index') }}",
                        data: function(d) {
                            d.status_invoice = $('#filter-status').val();
                            d.paket_id = $('#filter-paket').val();
                            d.tipe_pembayaran = $('#filter-tipe').val();
                            d.router = $('#filter-router').val();
                        }
                    },
                    columns: [{
                            data: 'no',
                            name: 'no',
                            searchable: false,
                            orderable: false,
                            className: 'text-center text-slate-400 font-bold',
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: 'pelanggan',
                            name: 'username'
                        },
                        {
                            data: 'username_col',
                            name: 'username'
                        },
                        {
                            data: 'paket',
                            name: 'paket_id'
                        },
                        {
                            data: 'router',
                            name: 'router'
                        },
                        {
                            data: 'ip_address',
                            name: 'ip_address'
                        },
                        {
                            data: 'tipe_pembayaran_badge',
                            name: 'tipe_pembayaran'
                        },
                        {
                            data: 'status_invoice_badge',
                            name: 'status_invoice',
                            searchable: false,
                            orderable: false
                        },
                        {
                            data: 'opsi',
                            name: 'opsi',
                            searchable: false,
                            orderable: false
                        }
                    ],
                    pageLength: 10,
                    lengthChange: false,
                    ordering: false,
                    info: false,
                    responsive: true,
                    dom: 'tpir',
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
                        table.search(val).draw();
                    }, 350);
                });

                // Filter on select change
                $('#filter-status, #filter-paket, #filter-tipe, #filter-router').on('change', function() {
                    table.draw();
                });

                $('#filterForm').on('submit', function(e) {
                    e.preventDefault();
                    table.draw();
                });
            });

            function openEditModal(id, username, password, paket_id, router, ip_address, tipe, tgl, periode, pajak, diskon,
                status, gen) {
                document.getElementById('editPppoeForm').action = `/pppoe-accounts/${id}`;
                document.getElementById('edit_username').value = username;
                document.getElementById('edit_password').value = password;
                document.getElementById('edit_paket_id').value = paket_id;
                document.getElementById('edit_router').value = (router || 'All');
                document.getElementById('edit_ip_address').value = ip_address;
                document.getElementById('edit_tipe_pembayaran').value = tipe;
                document.getElementById('edit_tanggal_aktif').value = tgl;
                document.getElementById('edit_periode_billing').value = periode;
                document.getElementById('edit_pajak').value = pajak;
                document.getElementById('edit_diskon').value = diskon;
                document.getElementById('edit_status_invoice').value = status;
                document.getElementById('edit_generate_invoice').checked = (gen == '1');

                $('#editPppoeModal .modal-tab').first().trigger('click');
                const modal = document.getElementById('editPppoeModal');
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            $(document).on('click', '.modal-tab', function() {
                const tabId = $(this).data('tab');
                const $modalBox = $(this).closest('.modal-box');
                $modalBox.find('.modal-tab').removeClass('active');
                $(this).addClass('active');
                $modalBox.find('.tab-panel').removeClass('active');
                $('#' + tabId).addClass('active');
            });

            function openIsolirPppoeModal(url, username) {
                document.getElementById('isolirPppoeForm').action = url;
                document.getElementById('isolirPppoeUsername').innerText = username;
                document.getElementById('isolirPppoeReason').value = 'Isolir manual oleh Admin';
                const modal = document.getElementById('isolirPppoeModal');
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function openBukaIsolirPppoeModal(url, username) {
                document.getElementById('bukaIsolirPppoeForm').action = url;
                document.getElementById('bukaIsolirPppoeUsername').innerText = username;
                const modal = document.getElementById('bukaIsolirPppoeModal');
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        </script>
    @endsection
</x-layouts.app>
