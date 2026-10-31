<x-layouts.app>
    @section('Css')
        <style>
            /* Hakatech DataTables Customization */
            .dataTables_wrapper {
                padding: 0;
            }

            .dataTables_filter {
                display: none;
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
                background: #0ea5e9;
                border-color: #0ea5e9;
                color: #fff !important;
            }

            /* Full Capitalization for Table */
            #customer-table thead th,
            #customer-table tbody td,
            #generalTasksTable thead th,
            #generalTasksTable tbody td {
                text-transform: uppercase !important;
                font-weight: 600;
            }

            #customer-table tbody td,
            #generalTasksTable tbody td {
                font-size: 12px;
            }

            /* Scanner container styling */
            #reader-container {
                display: none;
                margin-bottom: 20px;
                border: 2px solid #0ea5e9;
                border-radius: 14px;
                overflow: hidden;
                box-shadow: 0 4px 12px rgba(14, 165, 233, 0.15);
            }

            /* Custom Modal Styles for Activation */
            .partner-card-scroll {
                max-height: 200px;
                overflow-y: auto;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 12px;
            }

            .partner-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 8px;
                border-radius: 8px;
                transition: background 0.2s;
            }

            .partner-item:hover {
                background: #f1f5f9;
            }
        </style>
    @endsection

    @php $is_admin = auth()->user()->isAdmin(); @endphp

    <!-- Page header -->
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Tugas Aktivasi Baru</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Daftar penugasan aktivasi pelanggan yang siap
                dikerjakan.</p>
        </div>
        <div style="display:flex; gap:10px;">
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalThisMonth }}</div>
                <div class="stat-lbl">Bulan Ini</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="fa-solid fa-calendar-week"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalThisWeek }}</div>
                <div class="stat-lbl">Minggu Ini</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-hourglass-start"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalPending }}</div>
                <div class="stat-lbl">Menunggu Aktivasi</div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input custom-search" placeholder="Cari pelanggan, paket, atau alamat..."
                autocomplete="off" />
        </div>
    </div>

    @if ($is_admin)
        <div class="table-card">
            <div class="table-responsive">
                <table id="customer-table" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">#</th>
                            <th style="text-align: center; width: 100px;">Tgl PO</th>
                            <th style="text-align: center; width: 150px;">Sales</th>
                            <th style="text-align: center; width: 200px;">Nama Pelanggan</th>
                            <th style="text-align: center; width: 150px;">Paket</th>
                            <th style="text-align: center; width: 140px;">Status PPPoE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allPreOrders as $po)
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}
                                </td>
                                <td style="text-align: center; color: #64748b; font-size: 12px; font-weight: 600;">
                                    {{ \Carbon\Carbon::parse($po->created_at)->format('d-m-Y') }}
                                </td>
                                <td style="text-align: center; font-weight: 600;">{{ $po->nama_sales }}</td>
                                <td style="text-align: left; font-weight: 700; color: #0f172a;">
                                    {{ $po->nama_lengkap }}
                                    <div style="font-size: 11px; color: #64748b; font-weight: 500;">{{ $po->alamat_lengkap }}</div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge"
                                        style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 10px;">
                                        {{ $po->paket ? $po->paket->nama_paket : '-' }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    @if ($po->pppoe)
                                        <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 10px; font-weight: 700;">
                                            <i class="fa-solid fa-check mr-1"></i> PPPoE Siap
                                        </span>
                                    @else
                                        <span class="badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 10px; font-weight: 700;">
                                            <i class="fa-solid fa-clock mr-1"></i> Menunggu Billing
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="table-card">
            <div class="table-responsive">
                <table id="generalTasksTable" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">#</th>
                            <th style="text-align: center; width: 100px;">Tgl PO</th>
                            <th style="text-align: center; width: 150px;">Sales</th>
                            <th style="text-align: center;">Nama Pelanggan</th>
                            <th style="text-align: center;">Paket</th>
                            <th style="text-align: center; width: 150px;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($generalTasks as $po)
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                    {{ $loop->iteration }}</td>
                                <td style="text-align: center; color: #64748b; font-size: 12px; font-weight: 600;">
                                    {{ \Carbon\Carbon::parse($po->created_at)->format('d-m-Y') }}
                                </td>
                                <td style="text-align: center; font-weight: 600;">{{ $po->nama_sales }}</td>
                                <td style="text-align: left;">
                                    <div style="font-weight: 700; color: #0f172a;">{{ $po->nama_lengkap }}</div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                        {{ $po->alamat_lengkap }}
                                    </div>
                                    @if ($po->pppoe)
                                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5 font-mono text-[11px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded w-fit font-bold" title="Akun PPPoE telah disiapkan oleh Admin">
                                            <i class="fa-solid fa-network-wired text-[10px]"></i>
                                            <span>{{ $po->pppoe->username }}</span>
                                            <span class="text-emerald-300">|</span>
                                            <span class="text-slate-600 font-normal">Pass: {{ $po->pppoe->password }}</span>
                                            @if(!empty($po->pppoe->ip_address))
                                                <span class="text-emerald-300">|</span>
                                                <span class="text-purple-700 font-normal">IP: {{ $po->pppoe->ip_address }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="mt-1.5 flex items-center gap-1 text-[10px] text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded w-fit font-semibold">
                                            <i class="fa-solid fa-clock text-[9px]"></i> Menunggu Akun PPPoE dari Admin
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge"
                                        style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 10px;">
                                        {{ $po->paket ? $po->paket->nama_paket : '-' }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            title="Detail" onclick="openActivationDetailModal(this)"
                                            data-nama="{{ $po->nama_lengkap }}"
                                            data-email="{{ $po->alamat_email ?? '-' }}"
                                            data-wa="{{ $po->no_whatsapp }}" data-alamat="{{ $po->alamat_lengkap }}"
                                            data-paket="{{ $po->paket ? $po->paket->nama_paket : '-' }}"
                                            data-koordinat="{{ $po->titik_koordinat }}"
                                            data-sales="{{ $po->nama_sales }}"
                                            data-pppoe-username="{{ $po->pppoe ? $po->pppoe->username : '' }}"
                                            data-pppoe-password="{{ $po->pppoe ? $po->pppoe->password : '' }}"
                                            data-pppoe-ip="{{ $po->pppoe ? ($po->pppoe->ip_address ?? '') : '' }}"
                                            data-tgl="{{ \Carbon\Carbon::parse($po->created_at)->format('d/m/Y') }}">
                                            <i class="fa-solid fa-circle-info text-primary"></i>
                                        </button>
                                        @if ($po->is_unspek)
                                            @if ($po->is_unspek_approved)
                                                <span class="badge" style="background:#fef2f2;color:#be123c;">UNSPEK</span>
                                            @else
                                                <span class="badge" style="background:#fef3c7;color:#b45309;">UNSPEK (PENDING)</span>
                                            @endif
                                        @else
                                            <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                                title="Unspek" onclick="openUnspekModal('{{ $po->id }}', '{{ $po->nama_lengkap }}')">
                                                <i class="fa-solid fa-ban text-amber-500"></i>
                                            </button>
                                            <button type="button"
                                                class="btn-primary-admin !h-9 !px-3 !text-[11px] activate-btn !flex !items-center !justify-center gap-1"
                                                onclick="openActivationModal(this)" data-po-id="{{ $po->id }}"
                                                data-po-sales-id="{{ $po->id_sales }}"
                                                data-po-nama="{{ $po->nama_lengkap }}"
                                                data-po-paket="{{ $po->paket ? $po->paket->nama_paket : '-' }}"
                                                data-po-pppoe-username="{{ $po->pppoe ? $po->pppoe->username : '' }}"
                                                data-po-pppoe-password="{{ $po->pppoe ? $po->pppoe->password : '' }}"
                                                data-po-pppoe-ip="{{ $po->pppoe ? ($po->pppoe->ip_address ?? '') : '' }}">
                                                Ambil & Aktivasi
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- MODAL AREA --}}
    <!-- Modern Activation Modal -->
    <div class="modal-overlay" id="activationModal">
        <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span class="text-slate-900 font-bold">Formulir Aktivasi Pelanggan</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('activationModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-tabs bg-white border-b border-slate-200">
                <button class="modal-tab active" data-tab="tab-po">
                    <i class="fa-solid fa-file-invoice"></i> Data PO
                </button>
                <button class="modal-tab" data-tab="tab-teknis">
                    <i class="fa-solid fa-gears"></i> Teknis & Billing
                </button>
                <button class="modal-tab" data-tab="tab-media">
                    <i class="fa-solid fa-camera"></i> Dokumentasi
                </button>
            </div>

            <form id="activationForm" method="POST" action="{{ route('customers.store') }}"
                enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" id="modal_pre_order_id" name="pre_order_id" required>
                <input type="hidden" id="modal_id_sales" name="id_sales" required>
                <input type="hidden" name="id_teknisi" value="{{ auth()->id() }}">

                <div class="modal-body bg-white p-6">
                    <!-- Tab Data PO & Partner -->
                    <div class="tab-panel active" id="tab-po">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="form-group">
                                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Nama
                                        Pelanggan (PO)</label>
                                    <input type="text" class="form-input bg-slate-50 border-slate-200"
                                        id="po_nama_lengkap" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Paket
                                        Internet (PO)</label>
                                    <input type="text" class="form-input bg-slate-50 border-slate-200"
                                        id="po_paket" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Keterangan
                                        Tambahan</label>
                                    <textarea class="form-textarea" name="keterangan" rows="3" placeholder="Catatan selama instalasi..."></textarea>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Partner /
                                    Teknisi Tambahan</label>
                                <div class="partner-card-scroll">
                                    <div class="grid grid-cols-1 gap-1">
                                        @forelse ($teknisiUsers as $t)
                                            @if ($t->id != auth()->id())
                                                <div class="partner-item">
                                                    <input
                                                        class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary"
                                                        type="checkbox" name="partner_ids[]"
                                                        value="{{ $t->id }}"
                                                        id="partner_{{ $t->id }}">
                                                    <label
                                                        class="text-sm font-semibold text-slate-700 cursor-pointer w-full"
                                                        for="partner_{{ $t->id }}">
                                                        {{ $t->nama_lengkap }}
                                                    </label>
                                                </div>
                                            @endif
                                        @empty
                                            <div class="text-center py-4 text-slate-400 italic text-sm">Tidak ada
                                                teknisi lain.</div>
                                        @endforelse
                                    </div>
                                </div>
                                <input type="hidden" name="partner" id="partner_names">
                            </div>
                        </div>
                    </div>

                    <!-- Tab Teknis & Billing -->
                    <div class="tab-panel" id="tab-teknis">
                        <!-- PPPoE Sync Banner -->
                        <div id="pppoe_sync_banner" class="mb-5 p-3.5 rounded-xl border flex items-start gap-3 transition-all duration-200">
                            <div id="pppoe_sync_icon" class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-sm font-bold">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 id="pppoe_sync_title" class="text-xs font-bold uppercase tracking-wider m-0">Sinkronisasi PPPoE</h4>
                                    <button type="button" id="btn_toggle_lock_pppoe" class="hidden text-[11px] font-semibold text-primary hover:underline items-center gap-1 cursor-pointer">
                                        <i class="fa-solid fa-lock-open text-[10px]"></i> Buka Kunci
                                    </button>
                                </div>
                                <p id="pppoe_sync_desc" class="text-xs mt-0.5 leading-relaxed m-0 text-slate-600"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="form-group">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="form-label text-slate-800 font-bold uppercase text-[11px] m-0">ID
                                            Pelanggan (Billing) <span class="text-rose-500">*</span></label>
                                        <span class="text-[10px] text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded font-bold border border-emerald-200">
                                            <i class="fa-solid fa-arrows-rotate mr-1"></i> Otomatis
                                        </span>
                                    </div>
                                    <input type="text" class="form-input font-mono font-bold text-slate-900" name="id_pelanggan" id="id_pelanggan"
                                        value="{{ $nextIdPelanggan }}" placeholder="Contoh: {{ $nextIdPelanggan }}" required>
                                    <small class="text-[11px] text-slate-400">Otomatis melanjutkan nomor ID terakhir.</small>
                                </div>
                                <div class="form-group">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="form-label text-slate-800 font-bold uppercase text-[11px] m-0">Akun
                                            PPPoE (Username) <span class="text-rose-500">*</span></label>
                                        <span id="pppoe_username_status">
                                            <span class="text-[10px] text-sky-600 bg-sky-50 px-2 py-0.5 rounded font-bold border border-sky-200">
                                                <i class="fa-solid fa-arrows-rotate mr-1"></i> Otomatis
                                            </span>
                                        </span>
                                    </div>
                                    <input type="text" class="form-input font-mono font-bold text-slate-900 transition-colors" name="pppoe_username"
                                        id="pppoe_username" placeholder="Nama@erlangga.net" required>
                                    <small id="pppoe_username_note" class="text-[11px] text-slate-400 block mt-1">Otomatis dari nama pelanggan (dapat diedit).</small>
                                </div>
                                <div class="form-group">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="form-label text-slate-800 font-bold uppercase text-[11px] m-0">Sandi
                                            PPPoE <span class="text-rose-500">*</span></label>
                                        <span id="pppoe_password_status">
                                            <span class="text-[10px] text-slate-600 bg-slate-100 px-2 py-0.5 rounded font-bold">
                                                Default: 123
                                            </span>
                                        </span>
                                    </div>
                                    <input type="text" class="form-input font-mono font-bold text-slate-900 transition-colors" name="pppoe_password"
                                        id="pppoe_password" value="123" placeholder="123" required>
                                    <small id="pppoe_password_note" class="text-[11px] text-slate-400 block mt-1">Password dial router & password login mobile app.</small>
                                </div>
                                <div class="form-group" id="pppoe_ip_container" style="display: none;">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="form-label text-slate-800 font-bold uppercase text-[11px] m-0">IP Statis (RADIUS)</label>
                                        <span class="text-[10px] text-purple-700 bg-purple-50 px-2 py-0.5 rounded font-bold border border-purple-200">
                                            Disiapkan Admin
                                        </span>
                                    </div>
                                    <input type="text" class="form-input font-mono font-bold text-slate-700 bg-slate-100 cursor-not-allowed" id="pppoe_ip_address" readonly>
                                    <small class="text-[11px] text-slate-400 block mt-1">Alamat IP statis ini didaftarkan di Radreply RADIUS.</small>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Metode
                                        Pembayaran <span class="text-rose-500">*</span></label>
                                    <div class="flex gap-4 mt-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="pembayaran" value="Cash" required
                                                class="w-4 h-4 text-primary">
                                            <span class="text-sm font-bold text-slate-700">Cash</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="pembayaran" value="Aplikasi" required
                                                class="w-4 h-4 text-primary">
                                            <span class="text-sm font-bold text-slate-700">Aplikasi</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="form-group">
                                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Jenis
                                        Modem <span class="text-rose-500">*</span></label>
                                    <div class="flex gap-4 mt-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="jenis_modem" value="EPON" required
                                                class="w-4 h-4 text-primary">
                                            <span class="text-sm font-bold text-slate-700">EPON</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="jenis_modem" value="GPON" required
                                                class="w-4 h-4 text-primary">
                                            <span class="text-sm font-bold text-slate-700">GPON</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">SN
                                        Perangkat ONT <span class="text-rose-500">*</span></label>
                                    <div class="flex gap-2">
                                        <input type="text" class="form-input" name="sn_perangkat"
                                            id="sn_perangkat" placeholder="Serial Number..." required>
                                        <button type="button"
                                            class="btn-primary-admin !shadow-none !w-12 !flex !items-center !justify-center"
                                            id="start-scan">
                                            <i class="fa-solid fa-qrcode"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="reader-container" style="display:none;" class="scanner-container">
                                    <div id="reader" style="width: 100%;"></div>
                                    <button type="button"
                                        class="btn-cancel !py-2 !px-4 !text-[11px] !w-fit mt-3 !flex !items-center !justify-center gap-2 mx-auto"
                                        id="stop-scan">
                                        <i class="fa-solid fa-camera-rotate"></i> Tutup Kamera
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Dokumentasi -->
                    <div class="tab-panel" id="tab-media">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Foto Petugas
                                    <span class="text-rose-500">*</span></label>
                                <div
                                    class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-4 text-center">
                                    <i class="fa-solid fa-user-gear text-2xl text-slate-300 mb-2"></i>
                                    <input type="file" class="form-input !text-[11px]" name="foto_petugas"
                                        accept="image/*" required>
                                    <p class="text-[9px] text-slate-400 mt-2 italic">Foto wajah teknisi di lokasi.</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Perangkat
                                    Terpasang <span class="text-rose-500">*</span></label>
                                <div
                                    class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-4 text-center">
                                    <i class="fa-solid fa-box text-2xl text-slate-300 mb-2"></i>
                                    <input type="file" class="form-input !text-[11px]" name="foto_terpasang"
                                        accept="image/*" required>
                                    <p class="text-[9px] text-slate-400 mt-2 italic">Foto modem & kabel terpasang.</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Hasil
                                    Speedtest <span class="text-rose-500">*</span></label>
                                <div
                                    class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-4 text-center">
                                    <i class="fa-solid fa-gauge-high text-2xl text-slate-300 mb-2"></i>
                                    <input type="file" class="form-input !text-[11px]" name="foto_speedtest"
                                        accept="image/*" required>
                                    <p class="text-[9px] text-slate-400 mt-2 italic">Screenshot hasil uji kecepatan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center"
                        onclick="closeCustomModal('activationModal')">
                        Batal
                    </button>
                    <button type="submit" class="btn-save !flex !items-center !justify-center !bg-primary">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Activation Detail Modal -->
    <div class="modal-overlay" id="detailActivationModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-circle-info text-primary"></i>
                    <span class="text-slate-900 font-bold">Detail Tugas Aktivasi</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('detailActivationModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-8 space-y-6 bg-white">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <span class="text-[10px] font-black text-slate-400 uppercase block mb-1">Pelanggan</span>
                        <p id="det_nama" class="text-lg font-black text-slate-900 m-0"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-black text-slate-400 uppercase block mb-1">Paket Internet</span>
                        <p id="det_paket" class="text-base font-bold text-indigo-600 m-0"></p>
                    </div>
                </div>
                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
                    <span class="text-[10px] font-black text-slate-400 uppercase block mb-2">Alamat Pemasangan</span>
                    <p id="det_alamat" class="text-sm font-medium text-slate-700 leading-relaxed m-0"></p>
                </div>
                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200" id="det_pppoe_box">
                    <span class="text-[10px] font-black text-slate-400 uppercase block mb-2">Akun PPPoE (Dibuat oleh Admin)</span>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-slate-500 block mb-0.5">Username:</span>
                            <span id="det_pppoe_username" class="font-mono font-bold text-slate-900 text-sm"></span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 block mb-0.5">Password:</span>
                            <span id="det_pppoe_password" class="font-mono font-bold text-slate-900 text-sm"></span>
                        </div>
                        <div id="det_pppoe_ip_box" class="col-span-2 pt-2 border-t border-slate-200 flex items-center justify-between text-xs" style="display:none;">
                            <span class="text-slate-500">IP Statis:</span>
                            <span id="det_pppoe_ip" class="font-mono font-bold text-purple-700"></span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <a id="det_wa_link" href="#" target="_blank"
                        class="btn-primary-admin !text-white !shadow-none !flex !items-center !justify-center gap-2 h-12 rounded-xl">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                        <span class="font-bold">Hubungi via WA</span>
                    </a>
                    <a id="det_tikor_link" href="#" target="_blank"
                        class="btn-primary-admin !text-white !shadow-none !flex !items-center !justify-center gap-2 h-12 rounded-xl">
                        <i class="fa-solid fa-map-location-dot text-lg"></i>
                        <span class="font-bold">Petunjuk Arah</span>
                    </a>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button type="button" class="btn-cancel w-full" onclick="closeCustomModal('detailActivationModal')">
                    Tutup Detail
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Unspek --}}
    <div class="modal-overlay" id="unspekModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm border border-amber-100 shrink-0">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Tangguhkan ke Waiting List</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Alihkan calon pelanggan ke Prospek Tunggu (Waiting List Ekspansi).</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('unspekModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="unspekForm" method="POST">
                @csrf
                <div class="modal-body p-5 space-y-3.5 bg-white">
                    <div class="bg-amber-50 p-3.5 rounded-xl border border-amber-200">
                        <span class="text-[10px] font-bold text-amber-600 block uppercase">Pelanggan</span>
                        <p id="unspek_po_name" class="text-sm font-bold text-amber-900 m-0"></p>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label text-slate-700 font-medium block mb-1 text-xs">
                            Kendala Lapangan / Alasan Penangguhan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="keterangan_unspek" class="form-textarea !font-normal text-xs text-slate-800" required rows="3"
                            placeholder="Contoh: Tiang penuh, Jarak terlalu jauh (>500m), Butuh penambahan ODP baru..."></textarea>
                        <small class="text-[11px] text-slate-400 block mt-1">Calon pelanggan akan dialihkan ke daftar prospek tunggu untuk perencanaan ekspansi ODP.</small>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                        onclick="closeCustomModal('unspekModal')">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !bg-amber-600 hover:!bg-amber-700 !text-white !h-9 !px-5 text-xs font-semibold">
                        Tangguhkan ke Waiting List
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="https://unpkg.com/html5-qrcode"></script>

        <script>
            $(document).ready(function() {
                // Shared DataTable Options
                const dtOptions = {
                    "pageLength": 10,
                    "lengthChange": false,
                    "ordering": false,
                    "info": false,
                    "responsive": true,
                    "dom": 'tpir',
                    "language": {
                        "zeroRecords": "Data tidak ditemukan",
                        "paginate": {
                            "previous": '<i class="fa-solid fa-chevron-left"></i>',
                            "next": '<i class="fa-solid fa-chevron-right"></i>'
                        }
                    }
                };

                const adminTable = $('#customer-table').DataTable(dtOptions);
                const generalTable = $('#generalTasksTable').DataTable(dtOptions);

                // Custom Search (Live Search)
                $('.custom-search').on('keyup', function() {
                    const val = this.value;
                    if (adminTable) adminTable.search(val).draw();
                    if (generalTable) generalTable.search(val).draw();
                });

                // Tab switching
                $('.modal-tab').on('click', function() {
                    const tabId = $(this).data('tab');
                    const $modalBox = $(this).closest('.modal-box');
                    $modalBox.find('.modal-tab').removeClass('active');
                    $(this).addClass('active');
                    $modalBox.find('.tab-panel').removeClass('active');
                    $('#' + tabId).addClass('active');
                });

                // Close modal on overlay click
                $('.modal-overlay').on('click', function(e) {
                    if (e.target === this) closeCustomModal(this.id);
                });
            });

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

            let pppoeIsLocked = false;

            function openActivationDetailModal(triggerEl) {
                const $btn = $(triggerEl);
                $('#det_nama').text($btn.data('nama'));
                $('#det_email').text($btn.data('email'));
                $('#det_wa').text($btn.data('wa'));
                $('#det_alamat').text($btn.data('alamat'));
                $('#det_paket').text($btn.data('paket'));
                $('#det_sales').text($btn.data('sales'));
                $('#det_tgl').text($btn.data('tgl'));

                const pppoeUser = $btn.data('pppoe-username');
                const pppoePass = $btn.data('pppoe-password');
                const pppoeIp = $btn.data('pppoe-ip');

                if (pppoeUser) {
                    $('#det_pppoe_username').text(pppoeUser);
                    $('#det_pppoe_password').text(pppoePass || '-');
                    if (pppoeIp) {
                        $('#det_pppoe_ip').text(pppoeIp);
                        $('#det_pppoe_ip_box').show();
                    } else {
                        $('#det_pppoe_ip_box').hide();
                    }
                    $('#det_pppoe_box').show();
                } else {
                    $('#det_pppoe_box').hide();
                }

                const wa = String($btn.data('wa') || '');
                const waFormatted = wa.startsWith('0') ? '62' + wa.substring(1) : wa;
                $('#det_wa_link').attr('href', `https://wa.me/${waFormatted}`);

                const tikor = $btn.data('koordinat');
                if (tikor && tikor !== 'N/A') {
                    $('#det_tikor_link').attr('href', `https://www.google.com/maps/search/?api=1&query=${tikor}`).show();
                } else {
                    $('#det_tikor_link').hide();
                }

                openCustomModal('detailActivationModal');
            }

            function setLockPppoeState(isLocked) {
                pppoeIsLocked = isLocked;
                const $user = $('#pppoe_username');
                const $pass = $('#pppoe_password');
                const $btnLock = $('#btn_toggle_lock_pppoe');

                if (isLocked) {
                    $user.prop('readonly', true)
                        .addClass('bg-slate-100 text-slate-700 cursor-not-allowed')
                        .removeClass('bg-white text-slate-900');
                    $pass.prop('readonly', true)
                        .addClass('bg-slate-100 text-slate-700 cursor-not-allowed')
                        .removeClass('bg-white text-slate-900');
                    $('#pppoe_username_status').html('<span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-bold border border-emerald-200"><i class="fa-solid fa-lock mr-1"></i> Disiapkan Admin</span>');
                    $('#pppoe_username_note').text('Disinkronkan dari Pre-Order. Terkunci untuk menjaga integritas RADIUS.');
                    $('#pppoe_password_status').html('<span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-bold border border-emerald-200">Disiapkan Admin</span>');
                    $('#pppoe_password_note').text('Kata sandi yang disiapkan Administrator.');
                    $btnLock.html('<i class="fa-solid fa-lock-open text-[10px]"></i> Buka Kunci');
                } else {
                    $user.prop('readonly', false)
                        .removeClass('bg-slate-100 text-slate-700 cursor-not-allowed')
                        .addClass('bg-white text-slate-900');
                    $pass.prop('readonly', false)
                        .removeClass('bg-slate-100 text-slate-700 cursor-not-allowed')
                        .addClass('bg-white text-slate-900');
                    $btnLock.html('<i class="fa-solid fa-lock text-[10px]"></i> Kunci Kembali');
                }
            }

            $('#btn_toggle_lock_pppoe').on('click', function() {
                if (pppoeIsLocked) {
                    // Unlock
                    setLockPppoeState(false);
                    $('#pppoe_sync_banner')
                        .removeClass('bg-emerald-50 border-emerald-200 text-emerald-950')
                        .addClass('bg-amber-50 border-amber-200 text-amber-950');
                    $('#pppoe_sync_icon')
                        .removeClass('bg-emerald-100 text-emerald-700')
                        .addClass('bg-amber-100 text-amber-700')
                        .html('<i class="fa-solid fa-triangle-exclamation"></i>');
                    $('#pppoe_sync_title').text('Akun PPPoE Dibuka Kunci (Mode Manual)');
                    $('#pppoe_sync_desc').text('Peringatan: Mengubah username atau kata sandi akan memperbarui akun RADIUS yang sudah disiapkan Admin.');
                    $('#pppoe_username_status').html('<span class="text-[10px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded font-bold border border-amber-200"><i class="fa-solid fa-pen mr-1"></i> Mode Manual</span>');
                    $('#pppoe_password_status').html('<span class="text-[10px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded font-bold border border-amber-200">Mode Manual</span>');
                    $('#pppoe_username').focus();
                } else {
                    // Relock
                    setLockPppoeState(true);
                    $('#pppoe_sync_banner')
                        .removeClass('bg-amber-50 border-amber-200 text-amber-950')
                        .addClass('bg-emerald-50 border-emerald-200 text-emerald-950');
                    $('#pppoe_sync_icon')
                        .removeClass('bg-amber-100 text-amber-700')
                        .addClass('bg-emerald-100 text-emerald-700')
                        .html('<i class="fa-solid fa-shield-halved"></i>');
                    $('#pppoe_sync_title').text('Akun PPPoE Disiapkan Admin');
                    $('#pppoe_sync_desc').text('Kredensial username dan kata sandi disinkronkan langsung dari Pre-Order. Dikunci secara default agar selaras dengan RADIUS.');
                }
            });

            function openActivationModal(triggerEl) {
                const $btn = $(triggerEl);
                const poNama = String($btn.data('po-nama') || '').trim();
                const pppoeUser = String($btn.data('po-pppoe-username') || '').trim();
                const pppoePass = String($btn.data('po-pppoe-password') || '').trim();
                const pppoeIp = String($btn.data('po-pppoe-ip') || '').trim();

                $('#modal_pre_order_id').val($btn.data('po-id'));
                $('#modal_id_sales').val($btn.data('po-sales-id'));
                $('#po_nama_lengkap').val(poNama);
                $('#po_paket').val($btn.data('po-paket'));

                // 1. Auto-fill ID Pelanggan
                $('#id_pelanggan').val('{{ $nextIdPelanggan }}');

                // Reset Tab ke tab pertama
                $('.modal-tab[data-tab="tab-po"]').trigger('click');

                // 2. IP Statis Container
                if (pppoeIp) {
                    $('#pppoe_ip_address').val(pppoeIp);
                    $('#pppoe_ip_container').show();
                } else {
                    $('#pppoe_ip_address').val('');
                    $('#pppoe_ip_container').hide();
                }

                // 3. Akun PPPoE Sync & Lock State
                if (pppoeUser) {
                    $('#pppoe_username').val(pppoeUser);
                    $('#pppoe_password').val(pppoePass || '123');

                    // Kunci input secara default
                    setLockPppoeState(true);

                    // Setup Banner Akun dari Admin
                    $('#pppoe_sync_banner')
                        .removeClass('bg-sky-50 border-sky-200 text-sky-950 bg-amber-50 border-amber-200 text-amber-950')
                        .addClass('bg-emerald-50 border-emerald-200 text-emerald-950');
                    $('#pppoe_sync_icon')
                        .removeClass('bg-sky-100 text-sky-700 bg-amber-100 text-amber-700')
                        .addClass('bg-emerald-100 text-emerald-700')
                        .html('<i class="fa-solid fa-shield-halved"></i>');
                    $('#pppoe_sync_title').text('Akun PPPoE Disiapkan Admin');
                    $('#pppoe_sync_desc').text('Kredensial username dan kata sandi disinkronkan langsung dari Pre-Order. Dikunci secara default agar selaras dengan RADIUS.');
                    
                    $('#btn_toggle_lock_pppoe').removeClass('hidden').addClass('flex');
                } else {
                    // Fallback jika admin belum membuatkan akun
                    if (poNama) {
                        const words = poNama.split(/\s+/);
                        let base = words[0].toLowerCase().replace(/[^a-z0-9]/g, '');
                        if (base.length > 0) {
                            base = base.charAt(0).toUpperCase() + base.slice(1);
                        }
                        if (words.length > 1 && words[1]) {
                            base += words[1].toLowerCase().replace(/[^a-z0-9]/g, '').substring(0, 3);
                        }
                        $('#pppoe_username').val(base + '@erlangga.net');
                    }
                    $('#pppoe_password').val('123');

                    // Unlock input
                    setLockPppoeState(false);

                    // Setup Banner Akun Otomatis
                    $('#pppoe_sync_banner')
                        .removeClass('bg-emerald-50 border-emerald-200 text-emerald-950 bg-amber-50 border-amber-200 text-amber-950')
                        .addClass('bg-sky-50 border-sky-200 text-sky-950');
                    $('#pppoe_sync_icon')
                        .removeClass('bg-emerald-100 text-emerald-700 bg-amber-100 text-amber-700')
                        .addClass('bg-sky-100 text-sky-700')
                        .html('<i class="fa-solid fa-wand-magic-sparkles"></i>');
                    $('#pppoe_sync_title').text('Akun PPPoE Otomatis');
                    $('#pppoe_sync_desc').text('Admin belum membuatkan akun PPPoE untuk Pre-Order ini. Sistem membuatkan username otomatis dari nama pelanggan dengan password default 123.');

                    $('#btn_toggle_lock_pppoe').addClass('hidden').removeClass('flex');

                    $('#pppoe_username_status').html('<span class="text-[10px] text-sky-600 bg-sky-50 px-2 py-0.5 rounded font-bold border border-sky-200"><i class="fa-solid fa-arrows-rotate mr-1"></i> Otomatis</span>');
                    $('#pppoe_username_note').text('Otomatis dari nama pelanggan (dapat disesuaikan).');
                    $('#pppoe_password_status').html('<span class="text-[10px] text-slate-600 bg-slate-100 px-2 py-0.5 rounded font-bold border border-slate-200">Default: 123</span>');
                    $('#pppoe_password_note').text('Password default dial router & login mobile app.');
                }

                openCustomModal('activationModal');
            }

            // --- QR Scanner Implementation ---
            let html5QrCode = null;

            function startScanner() {
                $('#reader-container').show();
                html5QrCode = new Html5Qrcode("reader");
                const config = {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    }
                };

                html5QrCode.start({
                    facingMode: "environment"
                }, config, (decodedText) => {
                    $('#sn_perangkat').val(decodedText);
                    stopScanner();
                    showToast('Serial Number berhasil dipindai!', 'success');
                }).catch((err) => {
                    console.error("Gagal start scanner", err);
                    showToast('Gagal mengakses kamera.', 'error');
                });
            }

            function stopScanner() {
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        $('#reader-container').hide();
                    }).catch((err) => {
                        console.error("Gagal stop scanner", err);
                    });
                }
            }

            $('#start-scan').on('click', startScanner);
            $('#stop-scan').on('click', stopScanner);

            function openUnspekModal(poId, poNama) {
                $('#unspekForm').attr('action', `/pre-orders/${poId}/unspek`);
                $('#unspek_po_name').text(poNama);
                openCustomModal('unspekModal');
            }
        </script>
    @endsection
</x-layouts.app>
