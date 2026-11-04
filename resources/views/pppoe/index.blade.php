<x-layouts.app>
    @section('Css')
        <style>
            #pppoe-table thead th {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 12px;
            }

            #pppoe-table tbody td {
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
            <button type="button" onclick="openCreateModal()" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !h-8.5 !px-3.5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Tambah Akun PPPoE
            </button>
            <form action="{{ route('pppoe_accounts.sync_radius') }}" method="POST"
                onsubmit="return confirm('Mulai sinkronisasi data dari server RADIUS?');">
                @csrf
                <button type="submit" class="btn-primary-admin !bg-indigo-600 hover:!bg-indigo-700 !h-8.5 !px-3.5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-rotate"></i> Sinkronkan RADIUS
                </button>
            </form>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="stats-grid mb-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
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
            <div class="stat-icon" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <div class="stat-val text-slate-700">{{ number_format($totalNonBillingCount ?? 0, 0, ',', '.') }}</div>
                <div class="stat-lbl">Non-Billing / Khusus</div>
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
                    <select name="status_invoice" class="form-input-custom !h-8.5 !text-xs" id="filter-status">
                        <option value="">Semua Status</option>
                        <option value="terbayar" {{ $filterStatus === 'terbayar' ? 'selected' : '' }}>Terbayar</option>
                        <option value="belum_bayar" {{ $filterStatus === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="non_billing" {{ $filterStatus === 'non_billing' ? 'selected' : '' }}>Non-Billing (Tanpa Tagihan)</option>
                        <option value="isolir" {{ $filterStatus === 'isolir' ? 'selected' : '' }}>Terisolir</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label-custom">Paket Layanan</label>
                    <select name="paket_id" class="form-input-custom !h-8.5 !text-xs" id="filter-paket">
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
                    <select name="tipe_pembayaran" class="form-input-custom !h-8.5 !text-xs" id="filter-tipe">
                        <option value="">Semua Tipe</option>
                        <option value="prepaid" {{ $filterTipe === 'prepaid' ? 'selected' : '' }}>Prepaid</option>
                        <option value="postpaid" {{ $filterTipe === 'postpaid' ? 'selected' : '' }}>Postpaid</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 160px;">
                    <label class="form-label-custom">Router / NAS</label>
                    <select name="router" class="form-input-custom !h-8.5 !text-xs" id="filter-router">
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
                    <label class="form-label-custom">Pencarian</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input custom-search !h-8.5 !text-xs" id="custom-search"
                            placeholder="Cari username, nama, IP, router..." autocomplete="off"
                            onkeydown="return event.key != 'Enter';" />
                    </div>
                </div>
                <button type="submit" class="btn-primary-admin !h-8.5 !px-4 text-xs font-semibold shadow-xs flex items-center gap-1.5">
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
                        <th style="text-align: center; width: 160px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal (Standalone / Direct PPPoE Creation) -->
    <div class="modal-overlay" id="createPppoeModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-200 shrink-0">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Tambah Akun PPPoE</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Buat akun PPPoE langsung atau hubungkan dengan calon pelanggan dari Pre-Order.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('createPppoeModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-tabs bg-white border-b border-slate-200 px-6">
                <button type="button" class="modal-tab !py-2.5 !text-xs active" data-tab="create-pppoe-tab-akun">
                    <i class="fa-regular fa-user text-xs"></i> Akun PPPoE
                </button>
                <button type="button" class="modal-tab !py-2.5 !text-xs" data-tab="create-pppoe-tab-pembayaran">
                    <i class="fa-regular fa-credit-card text-xs"></i> Billing & Pembayaran
                </button>
            </div>

            <form id="createPppoeForm" action="{{ route('pppoe_accounts.store') }}" method="POST">
                @csrf

                <div class="modal-body p-5 bg-white space-y-4">
                    <!-- Tab Akun PPPoE -->
                    <div class="tab-panel active" id="create-pppoe-tab-akun">
                        <!-- Pre-Order Linkage (Optional) -->
                        <div class="form-group mb-3.5">
                            <label class="form-label text-slate-700 font-medium text-xs">Tautkan ke Pre-Order / Calon Pelanggan (Opsional)</label>
                            <select class="form-input-custom !font-normal !h-8.5 !text-xs w-full bg-white text-slate-800 border-slate-300" id="create_pre_order_id" name="pre_order_id">
                                <option value="">-- Tanpa Pre-Order (Akun Mandiri / Khusus) --</option>
                                @if(isset($unlinkedPreOrders) && count($unlinkedPreOrders) > 0)
                                    @foreach ($unlinkedPreOrders as $upo)
                                        <option value="{{ $upo->id }}" data-nama="{{ $upo->nama_lengkap }}" data-wa="{{ $upo->no_whatsapp }}" data-paket="{{ $upo->paket_id }}">
                                            {{ $upo->nama_lengkap }} ({{ $upo->no_whatsapp ?: 'No WA -' }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <span class="text-[11px] text-slate-400 mt-1 block">Pilih calon pelanggan dari Pre-Order jika ada, atau biarkan kosong untuk membuat akun PPPoE mandiri.</span>
                        </div>

                        <!-- Keterangan / Nama Pemilik -->
                        <div class="form-group mb-3.5">
                            <label class="form-label text-slate-700 font-medium text-xs">Keterangan / Nama Pemilik (Opsional)</label>
                            <input type="text" class="form-input !h-8.5 !text-xs bg-white text-slate-900 border-slate-300" id="create_keterangan" name="keterangan" placeholder="Contoh: Fasilitas Masjid Jamie, WiFi Balai RW, Akun Testing NOC">
                        </div>

                        <div class="form-grid-2 mb-3.5">
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Username PPPoE <span class="text-rose-500">*</span></label>
                                <input type="text" class="form-input !h-8.5 !text-xs bg-white text-slate-900 border-slate-300 font-mono" id="create_username" name="username" placeholder="user@erlangga.net" required>
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Password PPPoE <span class="text-rose-500">*</span></label>
                                <input type="text" class="form-input !h-8.5 !text-xs bg-white text-slate-900 border-slate-300 font-mono" id="create_password" name="password" value="123" required>
                            </div>
                        </div>

                        <div class="form-group mb-3.5">
                            <label class="form-label text-slate-700 font-medium text-xs">Profile / Paket Layanan <span class="text-rose-500">*</span></label>
                            <select class="form-input-custom !font-normal !h-8.5 !text-xs w-full bg-white text-slate-800 border-slate-300" id="create_paket_id" name="paket_id" required>
                                <option value="">-- Pilih Paket Layanan --</option>
                                @foreach ($pakets as $paket)
                                    <option value="{{ $paket->id }}">{{ $paket->nama_paket }} - Rp {{ number_format($paket->harga, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Router / NAS</label>
                                <select class="form-input-custom !font-normal !h-8.5 !text-xs w-full bg-white text-slate-800 border-slate-300" id="create_router" name="router">
                                    <option value="All">Semua Router (ALL)</option>
                                    @foreach ($routers as $r)
                                        <option value="{{ $r->shortname }}">{{ $r->shortname }} ({{ $r->nasname }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group !mb-0">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="form-label text-slate-700 font-medium text-xs !mb-0">IP Address (Opsional)</label>
                                    <button type="button" onclick="autoFillIp('create')" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1 cursor-pointer" title="Generate IP Address Selanjutnya">
                                        <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i> Auto IP
                                    </button>
                                </div>
                                <input type="text" class="form-input !h-8.5 !text-xs bg-white text-slate-900 border-slate-300 font-mono" id="create_ip_address" name="ip_address" placeholder="Contoh: 182.10.0.10">
                                <span class="text-[10.5px] text-slate-400 mt-1 block">Otomatis terisi host .2 s/d .253 sesuai paket, tetap dapat diedit manual.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Pembayaran -->
                    <div class="tab-panel" id="create-pppoe-tab-pembayaran">
                        <!-- Toggle Status Penagihan -->
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 mb-3.5 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Status Penagihan & Pembayaran (Billing)</span>
                                <span class="text-[11px] text-slate-500 block">Aktifkan untuk memproses tagihan, faktur bulanan, dan status bayar.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer m-0">
                                <input type="checkbox" id="create_is_billing_active" name="is_billing_active" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>

                        <!-- Notice jika Non-Billing -->
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center space-y-1 hidden" id="create_non_billing_notice">
                            <i class="fa-solid fa-shield-halved text-slate-400 text-xl mb-1"></i>
                            <div class="text-xs font-bold text-slate-700">Akun Bebas Tagihan (Non-Billing)</div>
                            <p class="text-[11px] text-slate-500 m-0 leading-relaxed max-w-sm mx-auto">
                                Akun PPPoE ini dibuat murni untuk koneksi jaringan tanpa penagihan faktur bulanan, tagihan kas, ataupun auto-isolir jatuh tempo.
                            </p>
                        </div>

                        <!-- Fields jika Billing Aktif -->
                        <div id="create_billing_fields" class="space-y-3.5">
                            <div class="form-grid-2">
                                <div class="form-group !mb-0">
                                    <label class="form-label text-slate-700 font-medium text-xs">Tipe Pembayaran</label>
                                    <select class="form-input-custom !font-normal !h-8.5 !text-xs w-full bg-white text-slate-800 border-slate-300" id="create_tipe_pembayaran" name="tipe_pembayaran">
                                        <option value="prepaid">Prepaid (Pra Bayar)</option>
                                        <option value="postpaid">Postpaid (Pasca Bayar)</option>
                                    </select>
                                </div>
                                <div class="form-group !mb-0">
                                    <label class="form-label text-slate-700 font-medium text-xs">Tanggal Aktif</label>
                                    <input type="date" class="form-input !h-8.5 !text-xs bg-white text-slate-900 border-slate-300" id="create_tanggal_aktif" name="tanggal_aktif" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="form-grid-2">
                                <div class="form-group !mb-0">
                                    <label class="form-label text-slate-700 font-medium text-xs">Periode Billing</label>
                                    <select class="form-input-custom !font-normal !h-8.5 !text-xs w-full bg-white text-slate-800 border-slate-300" id="create_periode_billing" name="periode_billing">
                                        <option value="fixed">Fixed (Setiap Awal Bulan)</option>
                                        <option value="renewal">Renewal (Sesuai Tgl Aktif)</option>
                                    </select>
                                </div>
                                <div class="form-group !mb-0">
                                    <label class="form-label text-slate-700 font-medium text-xs">Status Invoice</label>
                                    <select class="form-input-custom !font-normal !h-8.5 !text-xs w-full bg-white text-slate-800 border-slate-300" id="create_status_invoice" name="status_invoice">
                                        <option value="belum_bayar">Belum Bayar (Unpaid)</option>
                                        <option value="terbayar">Terbayar (Paid)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-grid-2">
                                <div class="form-group !mb-0">
                                    <label class="form-label text-slate-700 font-medium text-xs">Pajak (Rp)</label>
                                    <input type="number" class="form-input !h-8.5 !text-xs bg-white text-slate-900 border-slate-300 font-mono" id="create_pajak" name="pajak" value="0">
                                </div>
                                <div class="form-group !mb-0">
                                    <label class="form-label text-slate-700 font-medium text-xs">Diskon (Rp)</label>
                                    <input type="number" class="form-input !h-8.5 !text-xs bg-white text-slate-900 border-slate-300 font-mono" id="create_diskon" name="diskon" value="0">
                                </div>
                            </div>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 mt-2">
                                <label class="flex items-center gap-2.5 cursor-pointer m-0">
                                    <input type="checkbox" id="create_generate_invoice" name="generate_invoice" value="1" checked
                                        class="w-4 h-4 text-sky-600 border-slate-300 rounded focus:ring-sky-500">
                                    <div>
                                        <span class="text-xs font-semibold text-slate-800 block">Generate Invoice Otomatis</span>
                                        <span class="text-[11px] text-slate-500 block">Buat tagihan faktur pertama langsung saat akun PPPoE disimpan.</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
                    <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5" onclick="closeCustomModal('createPppoeModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !h-8.5 !px-5 text-xs font-semibold !bg-sky-600 hover:!bg-sky-700 shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan PPPoE
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal-overlay" id="editPppoeModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm border border-amber-200 shrink-0">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Edit Akun PPPoE</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Konfigurasi akun koneksi internet dan skema billing pelanggan.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('editPppoeModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-tabs bg-white border-b border-slate-200 px-6">
                <button type="button" class="modal-tab !py-2.5 !text-xs active" data-tab="edit-pppoe-tab-akun">
                    <i class="fa-regular fa-user text-xs"></i> Akun PPPoE
                </button>
                <button type="button" class="modal-tab !py-2.5 !text-xs" data-tab="edit-pppoe-tab-pembayaran">
                    <i class="fa-regular fa-credit-card text-xs"></i> Billing & Pembayaran
                </button>
            </div>

            <form id="editPppoeForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body p-5 bg-white space-y-4">
                    <!-- Tab Akun PPPoE -->
                    <div class="tab-panel active" id="edit-pppoe-tab-akun">
                        <!-- Keterangan / Catatan -->
                        <div class="form-group mb-3.5">
                            <label class="form-label text-slate-700 font-medium text-xs">Keterangan / Nama Pemilik (Opsional)</label>
                            <input type="text" class="form-input !h-8.5 !text-xs bg-white text-slate-900 border-slate-300" id="edit_keterangan" name="keterangan" placeholder="Contoh: Fasilitas Masjid Jamie, WiFi Balai RW, Testing NOC">
                        </div>

                        <div class="form-grid-2 mb-3.5">
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Username <span class="text-rose-500">*</span></label>
                                <input type="text" class="form-input !h-8.5 !text-xs bg-white text-slate-900 border-slate-300 font-mono" id="edit_username" name="username" required>
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Password <span class="text-rose-500">*</span></label>
                                <input type="text" class="form-input !h-8.5 !text-xs bg-white text-slate-900 border-slate-300 font-mono" id="edit_password" name="password" required>
                            </div>
                        </div>
                        <div class="form-group mb-3.5">
                            <label class="form-label text-slate-700 font-medium text-xs">Profile / Paket <span class="text-rose-500">*</span></label>
                            <select class="form-input-custom !font-normal !h-8.5 !text-xs w-full bg-white text-slate-800 border-slate-300" id="edit_paket_id" name="paket_id" required>
                                @foreach ($pakets as $paket)
                                    <option value="{{ $paket->id }}">{{ $paket->nama_paket }} - Rp {{ number_format($paket->harga, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Router / NAS</label>
                                <select class="form-input-custom !font-normal !h-8.5 !text-xs w-full bg-white text-slate-800 border-slate-300" id="edit_router" name="router">
                                    <option value="All">Semua Router (ALL)</option>
                                    @foreach ($routers as $r)
                                        <option value="{{ $r->shortname }}">{{ $r->shortname }} ({{ $r->nasname }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group !mb-0">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="form-label text-slate-700 font-medium text-xs !mb-0">IP Address (Opsional)</label>
                                    <button type="button" onclick="autoFillIp('edit')" class="text-[11px] font-semibold text-amber-600 hover:text-amber-700 flex items-center gap-1 cursor-pointer" title="Generate IP Address Selanjutnya">
                                        <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i> Auto IP
                                    </button>
                                </div>
                                <input type="text" class="form-input !h-8.5 !text-xs bg-white text-slate-900 border-slate-300 font-mono" id="edit_ip_address" name="ip_address" placeholder="Contoh: 182.10.0.10">
                                <span class="text-[10.5px] text-slate-400 mt-1 block">Klik Auto IP untuk mengambil IP berikutnya jika ingin mengganti alamat.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Pembayaran -->
                    <div class="tab-panel" id="edit-pppoe-tab-pembayaran">
                        <!-- Toggle Status Penagihan -->
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 mb-3.5 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Status Penagihan & Pembayaran (Billing)</span>
                                <span class="text-[11px] text-slate-500 block">Aktifkan untuk memproses tagihan, faktur bulanan, dan status bayar.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer m-0">
                                <input type="checkbox" id="edit_is_billing_active" name="is_billing_active" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>

                        <!-- Notice jika Non-Billing -->
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center space-y-1 hidden" id="edit_non_billing_notice">
                            <i class="fa-solid fa-shield-halved text-slate-400 text-xl mb-1"></i>
                            <div class="text-xs font-bold text-slate-700">Akun Bebas Tagihan (Non-Billing)</div>
                            <p class="text-[11px] text-slate-500 m-0 leading-relaxed max-w-sm mx-auto">
                                Akun PPPoE ini beroperasi secara mandiri tanpa penagihan faktur bulanan, tagihan kas, ataupun auto-isolir jatuh tempo.
                            </p>
                        </div>

                        <!-- Fields jika Billing Aktif -->
                        <div id="edit_billing_fields" class="space-y-3.5">
                            <div class="form-grid-2">
                                <div class="form-group !mb-0">
                                    <label class="form-label text-slate-700 font-medium text-xs">Tipe Pembayaran</label>
                                    <select class="form-input-custom !font-normal !h-8.5 !text-xs w-full bg-white text-slate-800 border-slate-300" id="edit_tipe_pembayaran" name="tipe_pembayaran">
                                        <option value="postpaid">Postpaid (Pasca Bayar)</option>
                                        <option value="prepaid">Prepaid (Pra Bayar)</option>
                                    </select>
                                </div>
                                <div class="form-group !mb-0">
                                    <label class="form-label text-slate-700 font-medium text-xs">Tanggal Aktif</label>
                                    <input type="date" class="form-input !h-8.5 !text-xs bg-white text-slate-900 border-slate-300" id="edit_tanggal_aktif" name="tanggal_aktif">
                                </div>
                            </div>
                            <div class="form-grid-2">
                                <div class="form-group !mb-0">
                                    <label class="form-label text-slate-700 font-medium text-xs">Periode Billing</label>
                                    <select class="form-input-custom !font-normal !h-8.5 !text-xs w-full bg-white text-slate-800 border-slate-300" id="edit_periode_billing" name="periode_billing">
                                        <option value="fixed">Fixed (Setiap Awal Bulan)</option>
                                        <option value="renewal">Renewal (Sesuai Tgl Aktif)</option>
                                    </select>
                                </div>
                                <div class="form-group !mb-0">
                                    <label class="form-label text-slate-700 font-medium text-xs">Status Invoice</label>
                                    <select class="form-input-custom !font-normal !h-8.5 !text-xs w-full bg-white text-slate-800 border-slate-300" id="edit_status_invoice" name="status_invoice">
                                        <option value="belum_bayar">Belum Bayar (Unpaid)</option>
                                        <option value="terbayar">Terbayar (Paid)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-grid-2">
                                <div class="form-group !mb-0">
                                    <label class="form-label text-slate-700 font-medium text-xs">Pajak (Rp)</label>
                                    <input type="number" class="form-input !h-8.5 !text-xs bg-white text-slate-900 border-slate-300 font-mono" id="edit_pajak" name="pajak">
                                </div>
                                <div class="form-group !mb-0">
                                    <label class="form-label text-slate-700 font-medium text-xs">Diskon (Rp)</label>
                                    <input type="number" class="form-input !h-8.5 !text-xs bg-white text-slate-900 border-slate-300 font-mono" id="edit_diskon" name="diskon">
                                </div>
                            </div>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 mt-2">
                                <label class="flex items-center gap-2.5 cursor-pointer m-0">
                                    <input type="checkbox" id="edit_generate_invoice" name="generate_invoice" value="1"
                                        class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500">
                                    <div>
                                        <span class="text-xs font-semibold text-slate-800 block">Generate Invoice Otomatis</span>
                                        <span class="text-[11px] text-slate-500 block">Buat tagihan faktur pertama langsung saat akun PPPoE disimpan.</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
                    <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5" onclick="closeCustomModal('editPppoeModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !h-8.5 !px-5 text-xs font-semibold !bg-amber-500 hover:!bg-amber-600 shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan
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
                            Akun <strong id="isolirPppoeUsername" class="font-mono text-rose-700 font-bold"></strong> akan dialihkan ke group RADIUS <strong>ISOLIR</strong> (kecepatan 128k/128k, IP 172.30.x.x) dan sesi aktif di MikroTik akan di-kick agar dial ulang secara otomatis.
                        </p>
                    </div>

                    <div>
                        <label class="form-label text-slate-700 font-medium block mb-1 text-xs">Alasan Isolir</label>
                        <textarea name="reason" id="isolirPppoeReason" class="form-textarea !font-normal w-full text-xs text-slate-800 border-slate-300" rows="3" placeholder="Contoh: Menunggak tagihan bulan ini / Pelanggaran penggunaan" required>Isolir manual oleh Admin</textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2">
                    <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5" onclick="closeCustomModal('isolirPppoeModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !text-white !h-8.5 !px-5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-ban"></i> Isolir Sekarang
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
                            Akun <strong id="bukaIsolirPppoeUsername" class="font-mono text-emerald-800 font-bold"></strong> akan dikembalikan ke profil paket aslinya di RADIUS dan sesi aktif di MikroTik akan di-kick agar dial ulang dengan kecepatan normal.
                        </p>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2">
                    <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5" onclick="closeCustomModal('bukaIsolirPppoeModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !bg-emerald-600 hover:!bg-emerald-700 !text-white !h-8.5 !px-5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-unlock-keyhole"></i> Buka Isolir
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
                            orderable: false,
                            className: 'text-center whitespace-nowrap'
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

            function updateBillingVisibility(toggleElem, fieldsElem, noticeElem) {
                if (toggleElem.checked) {
                    fieldsElem.classList.remove('hidden');
                    noticeElem.classList.add('hidden');
                } else {
                    fieldsElem.classList.add('hidden');
                    noticeElem.classList.remove('hidden');
                }
            }

            $('#create_is_billing_active').on('change', function() {
                updateBillingVisibility(
                    this,
                    document.getElementById('create_billing_fields'),
                    document.getElementById('create_non_billing_notice')
                );
            });

            $('#edit_is_billing_active').on('change', function() {
                updateBillingVisibility(
                    this,
                    document.getElementById('edit_billing_fields'),
                    document.getElementById('edit_non_billing_notice')
                );
            });

            const nextIpsMap = @json($nextIps ?? []);

            function autoFillIp(target, force = true) {
                const paketId = $('#' + target + '_paket_id').val();
                const $ipInput = $('#' + target + '_ip_address');

                if (!paketId) return;

                if (!force && $ipInput.val().trim() !== '') {
                    return;
                }

                if (nextIpsMap[paketId]) {
                    $ipInput.val(nextIpsMap[paketId]);
                }

                // AJAX fetch untuk memastikan paling mutakhir
                $.get("{{ route('pppoe_accounts.next_ip') }}", { paket_id: paketId }, function(res) {
                    if (res && res.success && res.ip) {
                        nextIpsMap[paketId] = res.ip;
                        $ipInput.val(res.ip);
                    }
                });
            }

            $('#create_paket_id').on('change', function() {
                autoFillIp('create', true);
            });

            $('#create_pre_order_id').on('change', function() {
                const selected = $(this).find(':selected');
                const nama = selected.data('nama');
                const paketId = selected.data('paket');

                if (nama) {
                    if (!$('#create_keterangan').val()) {
                        $('#create_keterangan').val(nama);
                    }
                    if (!$('#create_username').val()) {
                        const cleanUser = nama.toLowerCase().replace(/[^a-z0-9]/g, '') + '@erlangga.net';
                        $('#create_username').val(cleanUser);
                    }
                }
                if (paketId && $('#create_paket_id').val() !== String(paketId)) {
                    $('#create_paket_id').val(paketId).trigger('change');
                }
            });

            function openCreateModal() {
                document.getElementById('createPppoeForm').reset();
                document.getElementById('create_password').value = '123';
                document.getElementById('create_is_billing_active').checked = true;
                updateBillingVisibility(
                    document.getElementById('create_is_billing_active'),
                    document.getElementById('create_billing_fields'),
                    document.getElementById('create_non_billing_notice')
                );

                const firstPaket = $('#create_paket_id').val();
                if (firstPaket) {
                    autoFillIp('create', true);
                }

                $('#createPppoeModal .modal-tab').first().trigger('click');
                const modal = document.getElementById('createPppoeModal');
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function openEditModal(id, username, password, paket_id, router, ip_address, tipe, tgl, periode, pajak, diskon,
                status, gen, keterangan) {
                document.getElementById('editPppoeForm').action = `/pppoe-accounts/${id}`;
                document.getElementById('edit_username').value = username;
                document.getElementById('edit_password').value = password;
                document.getElementById('edit_paket_id').value = paket_id;
                document.getElementById('edit_router').value = (router || 'All');
                document.getElementById('edit_ip_address').value = ip_address || '';
                document.getElementById('edit_keterangan').value = keterangan || '';
                document.getElementById('edit_tipe_pembayaran').value = tipe || 'prepaid';
                document.getElementById('edit_tanggal_aktif').value = tgl || '';
                document.getElementById('edit_periode_billing').value = periode || 'fixed';
                document.getElementById('edit_pajak').value = pajak || 0;
                document.getElementById('edit_diskon').value = diskon || 0;
                document.getElementById('edit_status_invoice').value = (status === 'non_billing' ? 'belum_bayar' : (status || 'belum_bayar'));
                document.getElementById('edit_generate_invoice').checked = (gen == '1');

                const isBilling = (gen == '1' || (status !== 'non_billing' && gen !== '0'));
                const billingToggle = document.getElementById('edit_is_billing_active');
                billingToggle.checked = isBilling;
                updateBillingVisibility(
                    billingToggle,
                    document.getElementById('edit_billing_fields'),
                    document.getElementById('edit_non_billing_notice')
                );

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
