<x-layouts.app>
    @section('Css')
        <style>
            /* Toolbar & Search */
            .table-toolbar {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1rem 1.25rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
                margin-bottom: 1rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }

            .search-wrap {
                position: relative;
                flex: 1;
                min-width: 200px;
                max-width: 360px;
            }

            .search-icon {
                position: absolute;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                color: #94a3b8;
                font-size: 13px;
                pointer-events: none;
            }

            .search-input {
                width: 100%;
                padding: 9px 12px 9px 36px;
                border: 2px solid #e2e8f0;
                border-radius: 10px;
                font-size: 13px;
                font-family: 'Inter', sans-serif;
                color: #374151;
                outline: none;
                transition: all .25s;
                background: #f8fafc;
            }

            .search-input:focus {
                border-color: #0ea5e9;
                background: #fff;
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

            .scanner-container {
                border: 2px dashed #e2e8f0;
                border-radius: 12px;
                padding: 15px;
                background: #f8fafc;
                text-align: center;
                margin-top: 10px;
            }

            /* Full Capitalization for Table */
            #customer-table thead th,
            #customer-table tbody td {
                text-transform: uppercase !important;
                font-weight: 600;
            }

            #customer-table tbody td {
                font-size: 12px;
            }
        </style>
    @endsection

    <!-- Page header -->
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Aktivasi Pelanggan</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola aktivasi dan instalasi perangkat pelanggan
                baru.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('dashboards.index') }}" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                style="background: #f1f5f9; color: #475569; box-shadow: none; border: 1px solid #e2e8f0;"
                onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
        </div>
    </div>

    @if (Auth::user()->jabatan === 'Administrator')
        <!-- Stats cards for Admin -->
        <div class="stats-grid mb-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                    <i class="fa-solid fa-hourglass-start"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $allPreOrders->count() }}</div>
                    <div class="stat-lbl">Menunggu Aktivasi</div>
                </div>
            </div>
        </div>

        <!-- Admin Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table id="example1" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">#</th>
                            <th style="text-align: center; width: 100px;">Tgl PO</th>
                            <th style="text-align: center;">Sales</th>
                            <th style="text-align: center;">Nama Pelanggan</th>
                            <th style="text-align: center;">Paket</th>
                            <th style="text-align: center;">Ditugaskan Ke</th>
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
                                <td style="text-align: center;">
                                    @if ($po->id_teknisi_delegasi)
                                        <div style="font-weight: 700; color: #0ea5e9;">{{ $po->delegated_to }}</div>
                                        <div style="font-size: 10px; color: #94a3b8;">
                                            {{ \Carbon\Carbon::parse($po->delegated_at)->format('d/m H:i') }}</div>
                                    @else
                                        <span class="badge"
                                            style="background: #f1f5f9; color: #94a3b8; font-size: 10px;">Belum
                                            Ditunjuk</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- Stats cards for Technician -->
        <div class="stats-grid mb-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef2f2;color:#dc2626;">
                    <i class="fa-solid fa-fire"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $delegatedTasks->count() }}</div>
                    <div class="stat-lbl">Tugas Prioritas</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#f1f5f9;color:#475569;">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $generalTasks->count() }}</div>
                    <div class="stat-lbl">Tugas Umum</div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="table-toolbar">
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="search" class="search-input custom-search"
                    placeholder="Cari nama pelanggan, paket, atau alamat..." autocomplete="off" />
            </div>
            <div style="display:flex;gap:8px;">
                <div
                    style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    Tugas Aktif
                </div>
            </div>
        </div>

        <!-- Technician: Delegated Tasks (Priority) -->
        <h3
            style="font-size: 16px; font-weight: 800; margin-bottom: 1rem; color: #0f172a; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-star text-amber-500"></i> 1. Tugas Delegasi Khusus (Prioritas)
        </h3>
        <div class="table-card mb-8">
            <div class="table-responsive">
                <table id="delegatedTable" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">#</th>
                            <th style="text-align: center; width: 100px;">Tgl PO</th>
                            <th style="text-align: center;">Nama Pelanggan</th>
                            <th style="text-align: center;">Paket</th>
                            <th style="text-align: center; width: 100px;">Status</th>
                            <th style="text-align: center; width: 190px;">Deadline</th>
                            <th style="text-align: center; width: 150px;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($delegatedTasks as $po)
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                    {{ $loop->iteration }}</td>
                                <td style="text-align: center; color: #64748b; font-size: 12px; font-weight: 600;">
                                    {{ \Carbon\Carbon::parse($po->created_at)->format('d-m-Y') }}
                                </td>
                                <td style="text-align: center; font-weight: 700; color: #0f172a;">
                                    {{ $po->nama_lengkap }}</td>
                                <td style="text-align: center;">
                                    <span class="badge"
                                        style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 10px;">
                                        {{ $po->paket_internet }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    @if ($po->is_locked)
                                        <span class="badge badge-archived !bg-rose-600 !text-white">EXPIRED</span>
                                    @elseif ($po->deadline_status == 'SEGERA')
                                        <span class="badge !bg-amber-500 !text-white">SEGERA</span>
                                    @else
                                        <span class="badge badge-published">AKTIF</span>
                                    @endif
                                </td>
                                <td
                                    style="text-align: center; font-size: 11px; font-weight: 700; color: {{ $po->is_locked ? '#e11d48' : '#64748b' }};">
                                    {{ $po->deadline_date ?? '-' }}
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                        <a href="https://wa.me/{{ $po->no_whatsapp }}" target="_blank"
                                            class="topbar-icon-btn !w-9 !h-9 !rounded-xl" title="WhatsApp">
                                            <i class="fa-brands fa-whatsapp text-emerald-500"></i>
                                        </a>
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $po->titik_koordinat }}"
                                            target="_blank" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            title="Lokasi">
                                            <i class="fa-solid fa-map-location-dot text-indigo-500"></i>
                                        </a>
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            title="Tangguhkan / Unspek" onclick="openUnspekModal('{{ $po->id }}', '{{ $po->nama_lengkap }}')">
                                            <i class="fa-solid fa-ban text-amber-500"></i>
                                        </button>
                                        @if ($po->is_locked)
                                            <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                                disabled style="opacity: 0.5;">
                                                <i class="fa-solid fa-lock text-rose-600"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                class="btn-primary-admin !h-9 !px-3 !text-[11px] activate-btn !flex !items-center !justify-center gap-1"
                                                onclick="openActivationModal(this)" data-po-id="{{ $po->id }}"
                                                data-po-sales-id="{{ $po->id_sales }}"
                                                data-po-nama="{{ $po->nama_lengkap }}"
                                                data-po-paket="{{ $po->paket_internet }}">
                                                <i class="fa-solid fa-bolt"></i> Aktivasi
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

        <!-- Technician: General Tasks -->
        <h3
            style="font-size: 16px; font-weight: 800; margin-bottom: 1rem; color: #0f172a; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-users-gear text-slate-500"></i> 2. Tugas Umum (Pool)
        </h3>
        <div class="table-card mb-8">
            <div class="table-responsive">
                <table id="generalTable" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">#</th>
                            <th style="text-align: center; width: 100px;">Tgl PO</th>
                            <th style="text-align: center;">Sales</th>
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
                                <td style="text-align: center; font-weight: 700; color: #0f172a;">
                                    {{ $po->nama_lengkap }}</td>
                                <td style="text-align: center;">
                                    <span class="badge"
                                        style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 10px;">
                                        {{ $po->paket_internet }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div
                                        style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                        <a href="https://wa.me/{{ $po->no_whatsapp }}" target="_blank"
                                            class="topbar-icon-btn !w-9 !h-9 !rounded-xl" title="WhatsApp">
                                            <i class="fa-brands fa-whatsapp text-emerald-500"></i>
                                        </a>
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $po->titik_koordinat }}"
                                            target="_blank" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            title="Lokasi">
                                            <i class="fa-solid fa-map-location-dot text-indigo-500"></i>
                                        </a>
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            title="Tangguhkan / Unspek" onclick="openUnspekModal('{{ $po->id }}', '{{ $po->nama_lengkap }}')">
                                            <i class="fa-solid fa-ban text-amber-500"></i>
                                        </button>
                                        <button type="button"
                                            class="btn-primary-admin !h-9 !px-3 !text-[11px] activate-btn !flex !items-center !justify-center gap-1"
                                            onclick="openActivationModal(this)" data-po-id="{{ $po->id }}"
                                            data-po-sales-id="{{ $po->id_sales }}"
                                            data-po-nama="{{ $po->nama_lengkap }}"
                                            data-po-paket="{{ $po->paket_internet }}">
                                            <i class="fa-solid fa-bolt"></i> Aktivasi
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Technician: Unspek Tasks -->
        <h3
            style="font-size: 16px; font-weight: 800; margin-bottom: 1rem; color: #0f172a; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-ban text-rose-500"></i> 3. Penangguhan / Unspek
        </h3>
        <div class="table-card mb-8">
            <div class="table-responsive">
                <table id="unspekTable" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">#</th>
                            <th style="text-align: center; width: 100px;">Tgl PO</th>
                            <th style="text-align: center;">Nama Pelanggan</th>
                            <th style="text-align: center;">Keterangan</th>
                            <th style="text-align: center; width: 150px;">Status Unspek</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($unspekTasks as $po)
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                    {{ $loop->iteration }}</td>
                                <td style="text-align: center; color: #64748b; font-size: 12px; font-weight: 600;">
                                    {{ \Carbon\Carbon::parse($po->created_at)->format('d-m-Y') }}
                                </td>
                                <td style="text-align: center; font-weight: 700; color: #0f172a;">
                                    {{ $po->nama_lengkap }}</td>
                                <td style="text-align: center; font-size: 11px; color: #64748b; font-style: italic;">
                                    {{ $po->keterangan_unspek }}
                                </td>
                                <td style="text-align: center;">
                                    @if($po->is_unspek_approved)
                                        <span class="badge badge-archived !px-3 !py-2" title="Disetujui oleh Sales">DISETUJUI</span>
                                    @else
                                        <span class="badge" style="background:#fefce8;color:#a16207;border:1px solid #fef08a;" title="Menunggu Konfirmasi Sales">MENUNGGU</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

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
                enctype="multipart/form-data">
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

                <div class="modal-footer border-t border-slate-200 bg-white rounded-b-2xl">
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
                            <i class="fa-solid fa-comment-dots text-slate-400 mr-1"></i> Alasan Unspek / Penangguhan <span class="text-rose-500">*</span>
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
            // DataTables Initialization
            $(document).ready(function() {
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

                const example1 = $('#example1').DataTable(dtOptions);
                const delegatedTable = $('#delegatedTable').DataTable(dtOptions);
                const generalTable = $('#generalTable').DataTable(dtOptions);

                // Custom Search for tables (Live Search)
                $('.custom-search').on('keyup', function() {
                    const val = this.value;
                    if (example1) example1.search(val).draw();
                    if (delegatedTable) delegatedTable.search(val).draw();
                    if (generalTable) generalTable.search(val).draw();
                });

                // Tab Switching
                $(document).on('click', '.modal-tab', function() {
                    const tabId = $(this).data('tab');
                    const $modalBox = $(this).closest('.modal-box');
                    $modalBox.find('.modal-tab').removeClass('active');
                    $(this).addClass('active');
                    $modalBox.find('.tab-panel').removeClass('active');
                    $('#' + tabId).addClass('active');
                });
            });

            // Activation Modal Handling
            function openActivationModal(btn) {
                const $btn = $(btn);
                $('#modal_pre_order_id').val($btn.data('po-id'));
                $('#modal_id_sales').val($btn.data('po-sales-id'));
                $('#po_nama_lengkap').val($btn.data('po-nama'));
                $('#po_paket').val($btn.data('po-paket'));

                // Reset form
                $('#activationForm')[0].reset();
                $('#modal_pre_order_id').val($btn.data('po-id'));
                $('#modal_id_sales').val($btn.data('po-sales-id'));
                $('#po_nama_lengkap').val($btn.data('po-nama'));
                $('#po_paket').val($btn.data('po-paket'));

                // Reset tabs to first
                $('#activationModal .modal-tab').first().click();

                openCustomModal('activationModal');
            }

            function openUnspekModal(poId, poNama) {
                $('#unspekForm').attr('action', `/pre-orders/${poId}/unspek`);
                $('#unspek_po_name').text(poNama);
                openCustomModal('unspekModal');
            }

            // QR Scanner Logic
            const html5QrCode = new Html5Qrcode("reader");
            const config = {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 150
                }
            };

            $('#start-scan').on('click', function() {
                $('#reader-container').show();
                html5QrCode.start({
                        facingMode: "environment"
                    },
                    config,
                    (decodedText) => {
                        $('#sn_perangkat').val(decodedText);
                        stopScanner();
                        showToast('Scan SN berhasil: ' + decodedText, 'success');
                    },
                    (errorMessage) => {}
                ).catch((err) => {
                    showToast('Gagal akses kamera: ' + err, 'error');
                });
            });

            $('#stop-scan').on('click', stopScanner);

            function stopScanner() {
                html5QrCode.stop().then(() => {
                    $('#reader-container').hide();
                }).catch((err) => {
                    console.error("Gagal stop scanner", err);
                });
            }
        </script>
    @endsection
</x-layouts.app>
