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
                                <td style="text-align: center; font-weight: 700; color: #0f172a;">
                                    {{ $po->nama_lengkap }}</td>
                                <td style="text-align: center;">
                                    <span class="badge"
                                        style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 10px;">
                                        {{ $po->paket_internet }}
                                    </span>
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
                                <td style="text-align: left;">
                                    <div style="font-weight: 700; color: #0f172a;">{{ $po->nama_lengkap }}</div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                        {{ $po->alamat_lengkap }}
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge"
                                        style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 10px;">
                                        {{ $po->paket_internet }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            title="Detail" onclick="openActivationDetailModal(this)"
                                            data-nama="{{ $po->nama_lengkap }}"
                                            data-email="{{ $po->alamat_email ?? '-' }}"
                                            data-wa="{{ $po->no_whatsapp }}" data-alamat="{{ $po->alamat_lengkap }}"
                                            data-paket="{{ $po->paket_internet }}"
                                            data-koordinat="{{ $po->titik_koordinat }}"
                                            data-sales="{{ $po->nama_sales }}"
                                            data-tgl="{{ \Carbon\Carbon::parse($po->created_at)->format('d/m/Y') }}">
                                            <i class="fa-solid fa-circle-info text-primary"></i>
                                        </button>
                                        <button type="button"
                                            class="btn-primary-admin !h-9 !px-3 !text-[11px] activate-btn !flex !items-center !justify-center gap-1"
                                            onclick="openActivationModal(this)" data-po-id="{{ $po->id }}"
                                            data-po-sales-id="{{ $po->id_sales }}"
                                            data-po-nama="{{ $po->nama_lengkap }}"
                                            data-po-paket="{{ $po->paket_internet }}">
                                            <i class="fa-solid fa-bolt"></i> Ambil & Aktivasi
                                        </button>
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="form-group">
                                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">ID
                                        Pelanggan (Billing) <span class="text-rose-500">*</span></label>
                                    <input type="text" class="form-input" name="id_pelanggan" id="id_pelanggan"
                                        placeholder="Contoh: 123456" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Akun PPPoE
                                        (Username) <span class="text-rose-500">*</span></label>
                                    <input type="text" class="form-input" name="pppoe_username"
                                        id="pppoe_username" placeholder="pppoe@erlangga.net" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Sandi
                                        PPPoE <span class="text-rose-500">*</span></label>
                                    <input type="text" class="form-input" name="pppoe_password"
                                        id="pppoe_password" placeholder="Sandi standar/custom" required>
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
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        onclick="closeCustomModal('activationModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-save !flex !items-center !justify-center gap-2 !bg-primary">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Simpan Aktivasi
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
                <div class="grid grid-cols-2 gap-4">
                    <a id="det_wa_link" href="#" target="_blank"
                        class="btn-primary-admin !bg-emerald-50 !text-emerald-700 !border-emerald-200 !shadow-none !flex !items-center !justify-center gap-2 h-12 rounded-xl">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                        <span class="font-bold">Hubungi via WA</span>
                    </a>
                    <a id="det_tikor_link" href="#" target="_blank"
                        class="btn-primary-admin !bg-indigo-50 !text-indigo-700 !border-indigo-200 !shadow-none !flex !items-center !justify-center gap-2 h-12 rounded-xl">
                        <i class="fa-solid fa-map-location-dot text-lg"></i>
                        <span class="font-bold">Petunjuk Arah</span>
                    </a>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button type="button" class="btn-cancel w-full" onclick="closeCustomModal('detailActivationModal')">
                    <i class="fa-solid fa-xmark"></i> Tutup Detail
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Unspek --}}
    <div class="modal-overlay" id="unspekModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-ban text-amber-500"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Unspek / Tangguhkan PO</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('unspekModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="unspekForm" method="POST">
                @csrf
                <div class="modal-body p-6 space-y-4 bg-white">
                    <div class="bg-amber-50 p-4 rounded-xl border border-amber-100 mb-4">
                        <span class="text-[10px] font-black text-amber-500 block uppercase">Pelanggan</span>
                        <p id="unspek_po_name" class="text-base font-black text-amber-700 m-0"></p>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label !text-slate-800 !font-bold">
                            <i class="fa-solid fa-comment-dots text-slate-400 mr-1"></i> Alasan Unspek / Penangguhan
                            <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="keterangan_unspek" class="form-textarea" required rows="3"
                            placeholder="Contoh: Tiang penuh, Jarak terlalu jauh, Pelanggan membatalkan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        onclick="closeCustomModal('unspekModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-save !flex !items-center !justify-center gap-2 !bg-amber-500">
                        <i class="fa-solid fa-paper-plane"></i> Simpan Unspek
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

            function openActivationDetailModal(triggerEl) {
                const $btn = $(triggerEl);
                $('#det_nama').text($btn.data('nama'));
                $('#det_email').text($btn.data('email'));
                $('#det_wa').text($btn.data('wa'));
                $('#det_alamat').text($btn.data('alamat'));
                $('#det_paket').text($btn.data('paket'));
                $('#det_sales').text($btn.data('sales'));
                $('#det_tgl').text($btn.data('tgl'));

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

            function openActivationModal(triggerEl) {
                const $btn = $(triggerEl);
                $('#modal_pre_order_id').val($btn.data('po-id'));
                $('#modal_id_sales').val($btn.data('po-sales-id'));
                $('#po_nama_lengkap').val($btn.data('po-nama'));
                $('#po_paket').val($btn.data('po-paket'));
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
