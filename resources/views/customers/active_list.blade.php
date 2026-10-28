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

            #customer-table td, #isolir-table td {
                padding: 10px 14px;
                font-size: 13px;
            }

            /* Map Customizations */
            #customer-map {
                height: 250px;
                width: 100%;
                border-radius: 14px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                z-index: 1;
            }

            .map-container {
                position: relative;
                margin-bottom: 1.25rem;
            }

            .map-legend {
                position: absolute;
                bottom: 20px;
                left: 20px;
                background: white;
                padding: 12px 16px;
                border-radius: 12px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                z-index: 10;
            }

            .legend-item {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 11px;
                font-weight: 700;
                color: #475569;
                margin-bottom: 4px;
            }

            .legend-dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
            }
        </style>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endsection

    @php $is_admin = Auth::user()->isAdmin(); @endphp

    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Data Pelanggan</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola data pelanggan yang sudah aktif dan monitoring
                isolir.</p>
        </div>
    </div>

    <style>
        @media (min-width: 1280px) {
            .stats-grid-6 {
                grid-template-columns: repeat(6, minmax(0, 1fr));
            }

            .stats-grid-6 .stat-card {
                padding: 1rem;
                gap: 10px;
            }

            .stats-grid-6 .stat-icon {
                width: 38px;
                height: 38px;
                font-size: 16px;
            }

            .stats-grid-6 .stat-val {
                font-size: 20px;
            }

            .stats-grid-6 .stat-lbl {
                font-size: 10.5px;
            }
        }

        .dataTables_wrapper {
            position: relative;
            min-height: 200px;
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
    </style>

    <div class="stats-grid mb-6 {{ $is_admin ? 'stats-grid-6' : '' }}">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;"><i class="fa-solid fa-users"></i></div>
            <div>
                <div class="stat-val">{{ $totalActive }}</div>
                <div class="stat-lbl">Aktif</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;"><i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <div class="stat-val">{{ $newThisMonth }}</div>
                <div class="stat-lbl">Bulan Ini</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9;color:#475569;"><i class="fa-solid fa-user-slash"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalArchived }}</div>
                <div class="stat-lbl">Non-Aktif</div>
            </div>
        </div>

        @if ($is_admin)
            <div class="stat-card">
                <div class="stat-icon" style="background:#d1fae5;color:#059669;"><i class="fa-solid fa-wifi"></i></div>
                <div>
                    <div class="stat-val">{{ $totalOnlineAll }}</div>
                    <div class="stat-lbl">Online</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fee2e2;color:#b91c1c;"><i class="fa-solid fa-plane-slash"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $totalOfflineAll }}</div>
                    <div class="stat-lbl">Offline</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i
                        class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                    <div class="stat-val">{{ $totalIsolirAll }}</div>
                    <div class="stat-lbl">Isolir</div>
                </div>
            </div>
        @endif
    </div>


    <!-- Map Section -->
    <div class="map-container">
        <div id="customer-map"></div>

        <div class="map-legend">
            <div class="legend-item">
                <div class="legend-dot" style="background: #10b981; border: 2px solid #fff;"></div>
                Online
            </div>
            <div class="legend-item">
                <div class="legend-dot" style="background: #ef4444; border: 2px solid #fff;"></div>
                Offline
            </div>
        </div>
    </div>

    <div class="table-toolbar" style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
        <div style="display: flex; gap: 10px; align-items: center; flex: 1; min-width: 280px; flex-wrap: wrap;">
            <div class="search-wrap" style="flex: 1; min-width: 220px;">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="search" class="search-input custom-search" id="custom-search"
                    placeholder="Cari ID, Nama, atau Paket..." autocomplete="off" />
            </div>
            <div style="min-width: 170px;">
                <select id="filter-status" class="form-input-custom !h-10 !text-xs !bg-white" style="border-radius: 10px;">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif (Berlangganan)</option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                    <option value="isolir">Terisolir</option>
                    <option value="non_aktif">Non-Aktif (Arsip)</option>
                </select>
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <div
                style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                {{ $is_admin ? 'Data Keseluruhan' : 'Data Saya' }}
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="customer-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 45px;">#</th>
                        <th style="text-align: center; width: 105px;">ID Pelanggan</th>
                        <th style="text-align: left; min-width: 240px;">Nama Pelanggan</th>
                        <th style="text-align: center; width: 130px;">Paket</th>
                        <th style="text-align: left; width: 140px;">Teknisi</th>
                        <th style="text-align: center; width: 95px;">Tgl Aktivasi</th>
                        <th style="text-align: center; width: 180px;">Status / Opsi</th>
                    </tr>
                </thead>
                <tbody style="border-top: none;">
                    <!-- Data is loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    @if (!Auth::user()->isAdmin())
        @if (isset($pelangganIsolir) && $pelangganIsolir->count() > 0)
            <div
                style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;margin-top:2rem;">
                <div>
                    <h1 style="font-size:18px;font-weight:900;margin:0;color:#dc2626;">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i>Pelanggan Terisolir
                    </h1>
                    <p style="font-size:12px;color:#64748b;margin:3px 0 0;">Daftar pelanggan yang saat ini sedang dalam
                        masa isolir/suspend.</p>
                </div>

                <!-- Filter & Export Isolir -->
                <form action="{{ route('customers.export_isolir') }}" method="GET"
                    style="display:flex; gap:8px; align-items:center;">
                    @if (Auth::user()->isAdmin())
                        <select name="sales_id" class="form-control"
                            style="font-size:12px; padding:6px 12px; border-radius:8px; border:1px solid #e2e8f0;">
                            <option value="">-- Semua Sales --</option>
                            @php
                                $salesList = $pelangganIsolir->pluck('sales')->filter()->unique('id');
                            @endphp
                            @foreach ($salesList as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    @endif
                    <button type="submit" class="btn-primary-admin !h-8 !px-4 !text-xs !bg-slate-800 hover:!bg-slate-900 !text-white flex items-center gap-1.5">
                        <i class="fa-solid fa-file-pdf text-[11px]"></i> Export PDF
                    </button>
                </form>
            </div>

            <div class="table-card mb-8 border border-slate-200 shadow-sm">
                <div class="table-responsive">
                    <table id="isolir-table" class="admin-table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 45px;">#</th>
                                <th style="text-align: left; min-width: 220px;">Nama Pelanggan</th>
                                <th style="text-align: center; width: 140px;">Username PPPoE</th>
                                <th style="text-align: left; width: 140px;">Sales</th>
                                <th style="text-align: center; width: 130px;">Paket</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pelangganIsolir as $isolir)
                                <tr>
                                    <td style="text-align: center; color: #94a3b8; font-weight: 600; font-size: 12px;">
                                        {{ $loop->iteration }}</td>
                                    <td>
                                        <div class="text-left font-bold text-slate-900 text-[13px] leading-snug">{{ $isolir->nama_lengkap }}</div>
                                        @if ($isolir->no_whatsapp)
                                            <div class="text-left text-[11px] font-mono text-slate-400 mt-0.5">
                                                <a href="https://wa.me/{{ $isolir->no_whatsapp }}" target="_blank" class="text-slate-500 hover:text-emerald-600 inline-flex items-center gap-1">
                                                    <i class="fa-brands fa-whatsapp text-emerald-600 text-[11px]"></i> {{ $isolir->no_whatsapp }}
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td style="text-align: center; font-family: monospace; color: #334155; font-size: 12px; font-weight: 600;">
                                        {{ $isolir->pppoe_username }}
                                    </td>
                                    <td style="text-align: left; font-size: 12px; font-medium; color: #334155;">
                                        <i class="fa-regular fa-user text-[11px] text-slate-400 mr-1.5"></i>{{ $isolir->sales->nama_lengkap ?? '-' }}
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge" style="background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; font-weight: 600;">
                                            {{ $isolir->paket ? $isolir->paket->nama_paket : '-' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    {{-- Removed redundant Edit Customer Modal since we're integrating it --}}

    {{-- Custom Tabbed Detail Modal --}}
    <style>
        #customerDetailModal .edit-only {
            display: none !important;
        }

        #customerDetailModal.is-editing .edit-only {
            display: block !important;
        }

        #customerDetailModal.is-editing select.edit-only {
            display: block !important;
        }

        #customerDetailModal.is-editing input.edit-only {
            display: block !important;
        }

        #customerDetailModal.is-editing textarea.edit-only {
            display: block !important;
        }

        #customerDetailModal.is-editing .view-only {
            display: none !important;
        }
    </style>
    <div class="modal-overlay" id="customerDetailModal">
        <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl">
            @if ($is_admin)
                <form id="detailFormInline" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
            @endif
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                        <i class="fa-solid fa-address-card"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Detail Informasi Pelanggan</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Informasi biodata, parameter teknis jaringan, dan dokumentasi instalasi.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('customerDetailModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-tabs bg-white border-b border-slate-200 px-6">
                <button type="button" class="modal-tab active" data-tab="tab-biodata">
                    <i class="fa-regular fa-user text-xs"></i> Biodata
                </button>
                <button type="button" class="modal-tab" data-tab="tab-teknis">
                    <i class="fa-regular fa-hard-drive text-xs"></i> Data Teknis
                </button>
                <button type="button" class="modal-tab" data-tab="tab-dokumentasi">
                    <i class="fa-regular fa-image text-xs"></i> Dokumentasi
                </button>
            </div>

            <div class="modal-body p-5 bg-white space-y-4 max-h-[75vh] overflow-y-auto">
                <!-- Tab Biodata -->
                <div class="tab-panel active" id="tab-biodata">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Biodata Inti -->
                        <div class="border border-slate-200 rounded-xl p-4 bg-white space-y-3">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                                <i class="fa-regular fa-user text-xs text-slate-400"></i>
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Biodata Pelanggan</span>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">Nomor Identitas (NIK)</span>
                                    <p id="detail_nik_view" class="view-only text-sm font-semibold font-mono text-slate-800 m-0"></p>
                                    @if ($is_admin)
                                        <input type="text" name="no_identitas" id="detail_nik_input" class="edit-only form-input-custom !font-normal text-slate-800">
                                    @endif
                                </div>
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">Nama Lengkap</span>
                                    <p id="detail_nama_view" class="view-only text-sm font-bold text-slate-900 m-0"></p>
                                    @if ($is_admin)
                                        <input type="text" name="nama_lengkap" id="detail_nama_input" class="edit-only form-input-custom !font-normal text-slate-800" required>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">Alamat Email</span>
                                    <p id="detail_email_view" class="view-only text-sm font-medium text-slate-700 m-0"></p>
                                    @if ($is_admin)
                                        <input type="email" name="alamat_email" id="detail_email_input" class="edit-only form-input-custom !font-normal text-slate-800">
                                    @endif
                                </div>
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">No. WhatsApp</span>
                                    <div class="view-only flex items-center gap-2">
                                        <span id="detail_wa_view" class="text-sm font-semibold font-mono text-slate-800"></span>
                                        <a id="detail_wa_link" href="#" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2 py-0.5 rounded transition">
                                            <i class="fa-brands fa-whatsapp"></i> Chat
                                        </a>
                                    </div>
                                    @if ($is_admin)
                                        <input type="text" name="no_whatsapp" id="detail_wa_input" class="edit-only form-input-custom !font-normal text-slate-800" required>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Sales & Lokasi -->
                        <div class="space-y-4">
                            <div class="border border-slate-200 rounded-xl p-4 bg-white space-y-3">
                                <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                                    <i class="fa-regular fa-id-badge text-xs text-slate-400"></i>
                                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Informasi Sales & PO</span>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">Petugas Sales</span>
                                        <p id="detail_sales_view" class="view-only text-xs font-semibold text-slate-800 m-0"></p>
                                        @if ($is_admin)
                                            <select name="id_sales" id="detail_sales_input" class="edit-only form-input-custom !font-normal text-slate-800">
                                                <option value="">-- Pilih Sales --</option>
                                                @foreach ($salesUsers as $sales)
                                                    <option value="{{ $sales->id }}">{{ $sales->nama_lengkap }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">Tgl. Pre-Order</span>
                                        <p id="detail_tgl_po_view" class="view-only text-xs font-semibold text-slate-800 m-0"></p>
                                        @if ($is_admin)
                                            <input type="date" name="tgl_po" id="detail_tgl_po_input" class="edit-only form-input-custom !font-normal text-slate-800">
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">Titik Koordinat</span>
                                    <div class="view-only flex items-center justify-between gap-2">
                                        <span id="detail_tikor_view" class="text-xs font-mono font-medium text-slate-700"></span>
                                        <a id="detail_tikor_link" href="#" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 hover:text-sky-600 bg-white hover:bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-lg transition shadow-sm">
                                            <i class="fa-solid fa-location-arrow text-[10px] text-sky-500"></i> Buka Maps
                                        </a>
                                    </div>
                                    @if ($is_admin)
                                        <input type="text" name="titik_koordinat" id="detail_tikor_input" class="edit-only form-input-custom !font-normal text-slate-800 font-mono text-xs">
                                    @endif
                                </div>
                            </div>

                            <!-- Lokasi Pasang -->
                            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/60 space-y-2">
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Alamat Pemasangan</span>
                                <p id="detail_alamat_lengkap_view" class="view-only text-xs text-slate-700 leading-relaxed m-0 bg-white p-2.5 rounded-lg border border-slate-200"></p>
                                @if ($is_admin)
                                    <textarea name="alamat_lengkap" id="detail_alamat_lengkap_input" class="edit-only form-textarea !font-normal text-slate-800 text-xs" rows="3" required></textarea>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Teknis -->
                <div class="tab-panel" id="tab-teknis">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Akun & Layanan -->
                        <div class="border border-slate-200 rounded-xl p-4 bg-white space-y-3">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                                <i class="fa-regular fa-hard-drive text-xs text-slate-400"></i>
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Akun & Layanan</span>
                            </div>
                            <div>
                                <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">ID Pelanggan</span>
                                <p id="detail_customer_id_view" class="view-only text-sm font-bold font-mono text-sky-700 m-0"></p>
                                @if ($is_admin)
                                    <input type="text" name="id_pelanggan" id="detail_customer_id_input" class="edit-only form-input-custom !font-normal text-slate-800 font-mono" required>
                                @endif
                            </div>
                            <div>
                                <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">Paket Internet</span>
                                <p id="detail_paket_view" class="view-only text-sm font-semibold text-slate-800 m-0"></p>
                                @if ($is_admin)
                                    <select name="paket_id" id="detail_paket_input" class="edit-only form-input-custom !font-normal text-slate-800" required>
                                        @foreach(\App\Models\Paket::all() as $paket)
                                            <option value="{{ $paket->id }}">{{ $paket->nama_paket }} - Rp {{ number_format($paket->harga, 0, ',', '.') }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">PPPoE User</span>
                                    <p id="detail_pppoe_user_view" class="view-only text-xs font-mono font-semibold text-slate-800 m-0"></p>
                                    @if ($is_admin)
                                        <input type="text" name="pppoe_username" id="detail_pppoe_user_input" class="edit-only form-input-custom !font-normal font-mono text-xs text-slate-800">
                                    @endif
                                </div>
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">PPPoE Pass</span>
                                    <p id="detail_pppoe_pass_view" class="view-only text-xs font-mono font-semibold text-slate-800 m-0"></p>
                                    @if ($is_admin)
                                        <input type="text" name="pppoe_password" id="detail_pppoe_pass_input" class="edit-only form-input-custom !font-normal font-mono text-xs text-slate-800">
                                    @endif
                                </div>
                            </div>
                            <div class="view-only pt-2 border-t border-slate-100 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-medium text-slate-500 uppercase">Status PPPoE</span>
                                    <div id="detail_isolir_badge"></div>
                                </div>
                                <div id="detail_isolir_info" class="hidden text-xs p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 space-y-1">
                                    <div><strong>Alasan:</strong> <span id="detail_isolir_reason"></span></div>
                                    <div class="text-[10px] text-rose-600"><strong>Tanggal:</strong> <span id="detail_isolir_at"></span></div>
                                </div>
                                @if ($is_admin)
                                    <div class="pt-1">
                                        <button type="button" id="btnDetailIsolirAction" class="w-full text-xs font-semibold py-2 px-3 rounded-lg flex items-center justify-center gap-2 transition shadow-sm"></button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Perangkat & Billing -->
                        <div class="border border-slate-200 rounded-xl p-4 bg-white space-y-3">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                                <i class="fa-solid fa-server text-xs text-slate-400"></i>
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Perangkat & Billing</span>
                            </div>
                            <div>
                                <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">Jenis Modem</span>
                                <p id="detail_jenis_modem_view" class="view-only text-xs font-semibold text-slate-800 m-0"></p>
                                @if ($is_admin)
                                    <input type="text" name="jenis_modem" id="detail_jenis_modem_input" class="edit-only form-input-custom !font-normal text-slate-800">
                                @endif
                            </div>
                            <div>
                                <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">SN Perangkat</span>
                                <p id="detail_sn_view" class="view-only text-xs font-mono font-semibold text-slate-800 m-0"></p>
                                @if ($is_admin)
                                    <input type="text" name="sn_perangkat" id="detail_sn_input" class="edit-only form-input-custom !font-normal font-mono text-slate-800">
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">Pembayaran</span>
                                    <p id="detail_pembayaran_view" class="view-only text-xs font-semibold text-slate-800 m-0"></p>
                                    @if ($is_admin)
                                        <select name="pembayaran" id="detail_pembayaran_input" class="edit-only form-input-custom !font-normal text-slate-800" required>
                                            <option value="Transfer">Transfer</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Aplikasi">Aplikasi</option>
                                        </select>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1">Tgl Aktivasi</span>
                                    <p id="detail_tgl_aktivasi_view" class="view-only text-xs font-semibold text-slate-800 m-0"></p>
                                    @if ($is_admin)
                                        <input type="date" name="tgl_aktivasi" id="detail_tgl_aktivasi_input" class="edit-only form-input-custom !font-normal text-slate-800">
                                    @endif
                                </div>
                            </div>
                            <div class="view-only pt-2 border-t border-slate-100">
                                <span class="text-[11px] font-medium text-slate-500 block uppercase mb-1.5">Kontrol ONT & Wi-Fi (ACS)</span>
                                <button type="button" class="btn-primary-admin !h-9 !px-4 !text-xs !bg-slate-800 hover:!bg-slate-900 !text-white flex items-center gap-1.5 shadow-sm !w-full justify-center"
                                    onclick="openAcsModalFromDetail()">
                                    Status Live Modem & Wi-Fi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Dokumentasi -->
                <div class="tab-panel" id="tab-dokumentasi">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                        <div class="lg:col-span-8 border border-slate-200 rounded-xl p-4 bg-white space-y-3">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                                <i class="fa-regular fa-image text-xs text-slate-400"></i>
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Foto Hasil Instalasi</span>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="space-y-1.5">
                                    <span class="text-[10px] font-semibold text-slate-500 block text-center uppercase">Petugas</span>
                                    <div class="aspect-[3/4] w-full rounded-lg overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center">
                                        <img id="img_petugas" src="" class="w-full h-full object-cover hidden cursor-pointer hover:scale-105 transition duration-200" onclick="window.open(this.src, '_blank')">
                                        <i class="fa-solid fa-user-gear text-slate-300 text-2xl" id="placeholder_petugas"></i>
                                    </div>
                                    @if ($is_admin)
                                        <input type="file" name="foto_petugas" class="edit-only form-input !p-1 !h-auto text-[11px] !font-normal">
                                    @endif
                                </div>
                                <div class="space-y-1.5">
                                    <span class="text-[10px] font-semibold text-slate-500 block text-center uppercase">Perangkat</span>
                                    <div class="aspect-[3/4] w-full rounded-lg overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center">
                                        <img id="img_terpasang" src="" class="w-full h-full object-cover hidden cursor-pointer hover:scale-105 transition duration-200" onclick="window.open(this.src, '_blank')">
                                        <i class="fa-solid fa-microchip text-slate-300 text-2xl" id="placeholder_terpasang"></i>
                                    </div>
                                    @if ($is_admin)
                                        <input type="file" name="foto_terpasang" class="edit-only form-input !p-1 !h-auto text-[11px] !font-normal">
                                    @endif
                                </div>
                                <div class="space-y-1.5">
                                    <span class="text-[10px] font-semibold text-slate-500 block text-center uppercase">Speedtest</span>
                                    <div class="aspect-[3/4] w-full rounded-lg overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center">
                                        <img id="img_speedtest" src="" class="w-full h-full object-cover hidden cursor-pointer hover:scale-105 transition duration-200" onclick="window.open(this.src, '_blank')">
                                        <i class="fa-solid fa-gauge-high text-slate-300 text-2xl" id="placeholder_speedtest"></i>
                                    </div>
                                    @if ($is_admin)
                                        <input type="file" name="foto_speedtest" class="edit-only form-input !p-1 !h-auto text-[11px] !font-normal">
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-4 border border-slate-200 rounded-xl p-4 bg-slate-50/60 space-y-2 flex flex-col justify-between">
                            <div>
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-2">Catatan Teknisi</span>
                                <div class="bg-white p-3 rounded-lg border border-slate-200 min-h-[100px]">
                                    <p id="detail_keterangan_view" class="view-only text-xs font-normal text-slate-700 italic leading-relaxed m-0 break-words"></p>
                                    @if ($is_admin)
                                        <textarea name="keterangan" id="detail_keterangan_input" class="edit-only form-textarea !font-normal text-slate-800 text-xs !italic" rows="4"></textarea>
                                    @endif
                                </div>
                            </div>
                            <p class="text-[10px] text-slate-400 text-center m-0 pt-2 border-t border-slate-200">
                                Klik gambar untuk melihat ukuran penuh
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
                <button type="button" class="view-only btn-cancel !h-9 !px-4 text-xs font-semibold"
                    onclick="closeCustomModal('customerDetailModal')">
                    Tutup
                </button>
                @if ($is_admin)
                    <button type="button" id="btnToggleEdit"
                        class="view-only btn-primary-admin !h-9 !px-5 text-xs font-semibold"
                        onclick="toggleEditMode()">
                        Edit Data
                    </button>
                    <button type="button" class="edit-only btn-cancel !h-9 !px-4 text-xs font-semibold"
                        onclick="toggleEditMode()">
                        Batal Edit
                    </button>
                    <button type="submit"
                        class="edit-only btn-primary-admin !h-9 !px-5 text-xs font-semibold">
                        Simpan
                    </button>
                @endif
            </div>
            @if ($is_admin)
                </form>
            @endif
        </div>
    </div>

    <!-- Modal Detail ACS Live (GenieACS TR-069) -->
    <div class="modal-overlay" id="customerAcsModal">
        <div class="modal-box max-w-3xl bg-white border border-slate-300 shadow-2xl overflow-hidden rounded-2xl">
            <div class="modal-header border-b border-slate-200 bg-white p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg shadow-sm border border-sky-100">
                        <i class="fa-solid fa-satellite-dish"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Status Modem & Wi-Fi (ACS TR-069)</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">
                            Pelanggan: <span id="acs_modal_nama" class="font-bold text-slate-800">-</span>
                            (<span id="acs_modal_id_pelanggan" class="font-mono text-sky-600 font-bold">-</span>)
                        </p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('customerAcsModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body p-6 max-h-[75vh] overflow-y-auto space-y-6">
                <!-- Loading State -->
                <div id="acs_loading_state" class="py-12 text-center space-y-3">
                    <i class="fa-solid fa-circle-notch fa-spin text-4xl text-sky-500"></i>
                    <p class="text-sm font-bold text-slate-700 m-0">Membaca Data Live dari GenieACS & Modem ONT...</p>
                    <p class="text-xs text-slate-400 m-0">Mengambil status sinyal optik, konfigurasi Wi-Fi, dan perangkat terhubung.</p>
                </div>

                <!-- Error State -->
                <div id="acs_error_state" class="hidden py-8 px-6 bg-amber-50/60 border border-amber-200 rounded-2xl text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto text-xl">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 m-0" id="acs_error_title">Perangkat ONT Belum Terhubung di ACS</h4>
                    <p class="text-xs text-slate-600 max-w-md mx-auto m-0" id="acs_error_msg">
                        Perangkat ONT pelanggan ini belum terdaftar atau sedang offline di GenieACS.
                    </p>
                    <div class="bg-white p-3 rounded-xl border border-amber-200/70 inline-block text-left text-xs font-mono text-slate-600 space-y-1">
                        <div>SN Perangkat : <span id="acs_err_sn" class="font-bold text-slate-800">-</span></div>
                        <div>Username PPPoE : <span id="acs_err_pppoe" class="font-bold text-slate-800">-</span></div>
                    </div>
                    <p class="text-[11px] text-slate-400 italic m-0 pt-2">
                        *Pastikan modem ONT menyala dan parameter TR-069 pada modem sudah mengarah ke server GenieACS.
                    </p>
                </div>

                <!-- Content State -->
                <div id="acs_content_state" class="hidden space-y-6">
                    <!-- Top 4 Cards Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <!-- Status ONT -->
                        <div class="p-3.5 rounded-xl border bg-slate-50 border-slate-200/80">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Status ONT</div>
                            <div class="mt-1" id="acs_status_badge">
                                <!-- Badge Online/Offline -->
                            </div>
                            <div class="text-[10px] text-slate-500 mt-1.5 truncate" id="acs_last_inform">-</div>
                        </div>

                        <!-- Redaman Optik (RX Power) -->
                        <div class="p-3.5 rounded-xl border" id="acs_rx_card">
                            <div class="flex items-center justify-between text-[10px] font-bold text-slate-500 uppercase">
                                <span>Redaman RX</span>
                                <i class="fa-solid fa-wave-square"></i>
                            </div>
                            <div class="text-lg font-black font-mono mt-1" id="acs_rx_power">-</div>
                            <div class="text-[10px] font-semibold mt-1" id="acs_rx_quality">-</div>
                        </div>

                        <!-- Suhu ONT -->
                        <div class="p-3.5 rounded-xl border" id="acs_temp_card">
                            <div class="flex items-center justify-between text-[10px] font-bold text-slate-500 uppercase">
                                <span>Suhu ONT</span>
                                <i class="fa-solid fa-temperature-half"></i>
                            </div>
                            <div class="text-lg font-black font-mono mt-1" id="acs_temperature">-</div>
                            <div class="text-[10px] font-semibold mt-1" id="acs_temp_quality">-</div>
                        </div>

                        <!-- Spesifikasi Modem -->
                        <div class="p-3.5 rounded-xl border bg-slate-50 border-slate-200/80">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Perangkat & IP</div>
                            <div class="text-xs font-bold text-slate-800 mt-1 truncate" id="acs_model_vendor">-</div>
                            <div class="text-[10px] font-mono text-slate-600 mt-0.5 truncate" id="acs_ip_wan">-</div>
                        </div>
                    </div>

                    <!-- Wi-Fi Information Card -->
                    <div class="border border-sky-200 bg-sky-50/40 rounded-2xl p-5 space-y-4">
                        <div class="flex items-center justify-between border-b border-sky-100 pb-3">
                            <div class="flex items-center gap-2 text-sm font-bold text-slate-900">
                                <i class="fa-solid fa-wifi text-sky-600"></i>
                                <span>Konfigurasi Wi-Fi Pelanggan</span>
                            </div>
                            <span class="badge bg-sky-100 text-sky-700 text-[10px] font-bold border border-sky-200">TR-069 Live</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Wi-Fi 2.4 GHz -->
                            <div class="bg-white p-4 rounded-xl border border-sky-100 shadow-sm space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                        <i class="fa-solid fa-signal text-sky-500 text-[11px]"></i> Frekuensi 2.4 GHz
                                    </span>
                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200" id="acs_wlan1_status">Aktif</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Nama Wi-Fi (SSID)</span>
                                    <div class="text-sm font-mono font-black text-slate-900 mt-0.5 select-all" id="acs_wlan1_ssid">-</div>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Kata Sandi Wi-Fi</span>
                                    <div class="flex items-center justify-between bg-slate-50 p-2 rounded-lg border border-slate-200 mt-0.5">
                                        <span class="text-sm font-mono font-black text-slate-800 tracking-wider" id="acs_wlan1_pass">••••••••</span>
                                        <div class="flex items-center gap-1">
                                            <button type="button" class="topbar-icon-btn !w-7 !h-7 !rounded-lg text-slate-500 hover:text-slate-700" onclick="toggleAcsPassword('acs_wlan1_pass')" title="Lihat Sandi">
                                                <i class="fa-solid fa-eye text-xs"></i>
                                            </button>
                                            <button type="button" class="topbar-icon-btn !w-7 !h-7 !rounded-lg text-sky-600 hover:text-sky-700" onclick="copyTextFromElement('acs_wlan1_pass', this)" title="Salin Sandi">
                                                <i class="fa-solid fa-copy text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Wi-Fi 5 GHz (Dual Band) -->
                            <div class="bg-white p-4 rounded-xl border border-sky-100 shadow-sm space-y-3" id="acs_wlan5_container">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                        <i class="fa-solid fa-bolt text-indigo-500 text-[11px]"></i> Frekuensi 5 GHz (Dual-Band)
                                    </span>
                                    <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200" id="acs_wlan5_status">Aktif</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Nama Wi-Fi (SSID)</span>
                                    <div class="text-sm font-mono font-black text-slate-900 mt-0.5 select-all" id="acs_wlan5_ssid">-</div>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Kata Sandi Wi-Fi</span>
                                    <div class="flex items-center justify-between bg-slate-50 p-2 rounded-lg border border-slate-200 mt-0.5">
                                        <span class="text-sm font-mono font-black text-slate-800 tracking-wider" id="acs_wlan5_pass">••••••••</span>
                                        <div class="flex items-center gap-1">
                                            <button type="button" class="topbar-icon-btn !w-7 !h-7 !rounded-lg text-slate-500 hover:text-slate-700" onclick="toggleAcsPassword('acs_wlan5_pass')" title="Lihat Sandi">
                                                <i class="fa-solid fa-eye text-xs"></i>
                                            </button>
                                            <button type="button" class="topbar-icon-btn !w-7 !h-7 !rounded-lg text-sky-600 hover:text-sky-700" onclick="copyTextFromElement('acs_wlan5_pass', this)" title="Salin Sandi">
                                                <i class="fa-solid fa-copy text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Connected Devices (Hosts) Card -->
                    <div class="border border-slate-200 rounded-2xl p-5 bg-white space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2 text-sm font-bold text-slate-900">
                                <i class="fa-solid fa-laptop-code text-indigo-600"></i>
                                <span>Perangkat Terhubung (Connected Devices)</span>
                            </div>
                            <span class="badge bg-indigo-50 text-indigo-700 text-[11px] font-bold border border-indigo-200" id="acs_hosts_badge">0 Perangkat Terhubung</span>
                        </div>

                        <div class="table-responsive max-h-56 overflow-y-auto">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="border-b border-slate-200 text-slate-400 uppercase font-bold text-[10px]">
                                        <th class="py-2 text-left">Nama Perangkat / Host</th>
                                        <th class="py-2 text-left">Alamat IP</th>
                                        <th class="py-2 text-left">MAC Address</th>
                                        <th class="py-2 text-center">Tipe</th>
                                        <th class="py-2 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="acs_hosts_table_body" class="divide-y divide-slate-100 font-medium text-slate-700">
                                    <!-- Filled by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-3">
                <div>
                    <a id="acs_btn_open_genieacs" href="#" target="_blank"
                        class="btn-primary-admin !h-9 !px-4 !bg-slate-800 hover:!bg-slate-900 !text-white text-xs font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Kelola di Menu GenieACS
                    </a>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="refreshCurrentAcsModal()">
                        Muat Ulang
                    </button>
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('customerAcsModal')">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Isolir Customer -->
    <div class="modal-overlay modal-centered" id="isolirCustomerModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm border border-rose-100 shrink-0">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Isolir Layanan Pelanggan</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Alihkan paket ke profil isolir FreeRADIUS & MikroTik.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('isolirCustomerModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="isolirCustomerForm" method="POST" action="">
                @csrf
                <div class="p-6 space-y-4">
                    <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 space-y-1">
                        <div class="font-bold flex items-center gap-1.5 text-amber-900">
                            <i class="fa-solid fa-triangle-exclamation"></i> Konfirmasi Isolir:
                        </div>
                        <p class="m-0 leading-relaxed">
                            Pelanggan <strong id="isolirCustomerName" class="text-slate-900 font-bold"></strong> (PPPoE: <code id="isolirCustomerUsername" class="font-mono text-rose-700 font-bold"></code>) akan dipindahkan ke group <strong>ISOLIR</strong> dan koneksi aktifnya akan diputus otomatis agar langsung dial ulang dengan limit isolir.
                        </p>
                    </div>

                    <div>
                        <label class="form-label text-slate-700 font-medium block mb-1 text-xs">Alasan Isolir</label>
                        <textarea name="reason" id="isolirCustomerReason" class="form-textarea !font-normal w-full text-xs text-slate-800 border-slate-300" rows="3" placeholder="Contoh: Menunggak pembayaran tagihan" required>Isolir manual oleh Admin</textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex justify-between gap-2">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('isolirCustomerModal')">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !text-white !h-9 !px-5 text-xs font-semibold">
                        Isolir Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Buka Isolir Customer -->
    <div class="modal-overlay modal-centered" id="bukaIsolirCustomerModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm border border-emerald-100 shrink-0">
                        <i class="fa-solid fa-unlock-keyhole"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Buka Isolir Pelanggan</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Kembalikan kecepatan dan akses internet normal.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('bukaIsolirCustomerModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="bukaIsolirCustomerForm" method="POST" action="">
                @csrf
                <div class="p-6 space-y-4">
                    <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 space-y-1">
                        <div class="font-bold flex items-center gap-1.5 text-emerald-900">
                            <i class="fa-solid fa-circle-check"></i> Konfirmasi Pembukaan:
                        </div>
                        <p class="m-0 leading-relaxed">
                            Layanan internet untuk <strong id="bukaIsolirCustomerName" class="text-slate-900 font-bold"></strong> (PPPoE: <code id="bukaIsolirCustomerUsername" class="font-mono text-emerald-800 font-bold"></code>) akan dipulihkan ke paket aslinya dan sesi PPPoE di-kick agar langsung mendapatkan kecepatan penuh.
                        </p>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex justify-between gap-2">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('bukaIsolirCustomerModal')">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !bg-emerald-600 hover:!bg-emerald-700 !text-white !h-9 !px-5 text-xs font-semibold">
                        Buka Isolir Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($is_admin)
        <!-- Modal Non-aktifkan Pelanggan (Pencabutan / Arsip) -->
        <div class="modal-overlay modal-centered" id="deactivateCustomerModal">
            <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
                <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm border border-amber-100 shrink-0">
                            <i class="fa-solid fa-user-slash"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 m-0">Non-aktifkan Pelanggan</h3>
                            <p class="text-xs text-slate-500 m-0 mt-0.5">Pencabutan layanan & arsipkan data pelanggan.</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('deactivateCustomerModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <form id="deactivateCustomerForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="p-6 space-y-4">
                        <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 space-y-1">
                            <div class="font-bold flex items-center gap-1.5 text-amber-900">
                                <i class="fa-solid fa-triangle-exclamation"></i> Konfirmasi Penonaktifan:
                            </div>
                            <p class="m-0 leading-relaxed">
                                Apakah Anda yakin ingin menonaktifkan pelanggan <strong id="deactivateCustomerName" class="text-slate-900 font-bold"></strong>?
                            </p>
                        </div>
                        <div class="text-xs text-slate-600 space-y-1.5 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                            <div class="font-semibold text-slate-800 mb-1">Keterangan arsip & jaringan:</div>
                            <div class="flex items-start gap-2">
                                <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5"></i>
                                <span>Data pelanggan <strong>tetap tersimpan di arsip</strong> dan dapat diaktifkan kembali sewaktu-waktu.</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i class="fa-solid fa-circle-xmark text-rose-500 mt-0.5"></i>
                                <span>Kredensial login FreeRADIUS dinonaktifkan dan sesi router di-kick otomatis.</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex justify-between gap-2">
                        <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('deactivateCustomerModal')">
                            Batal
                        </button>
                        <button type="submit" class="btn-primary-admin !bg-amber-600 hover:!bg-amber-700 !text-white !h-9 !px-5 text-xs font-semibold">
                            Non-aktifkan (Arsipkan)
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Aktifkan Kembali Pelanggan (Restore) -->
        <div class="modal-overlay modal-centered" id="restoreCustomerModal">
            <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
                <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm border border-emerald-100 shrink-0">
                            <i class="fa-solid fa-rotate-left"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 m-0">Aktifkan Kembali Pelanggan</h3>
                            <p class="text-xs text-slate-500 m-0 mt-0.5">Pulihkan pelanggan dari arsip ke status aktif.</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('restoreCustomerModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <form id="restoreCustomerForm" method="POST" action="">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 space-y-1">
                            <div class="font-bold flex items-center gap-1.5 text-emerald-900">
                                <i class="fa-solid fa-circle-check"></i> Konfirmasi Pemulihan:
                            </div>
                            <p class="m-0 leading-relaxed">
                                Aktifkan kembali langganan untuk <strong id="restoreCustomerName" class="text-slate-900 font-bold"></strong>?
                            </p>
                        </div>
                        <p class="text-xs text-slate-600 m-0 leading-relaxed">
                            Akun PPPoE dan kredensial FreeRADIUS akan otomatis dipulihkan ke paket aslinya agar modem dapat terhubung kembali.
                        </p>
                    </div>
                    <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex justify-between gap-2">
                        <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('restoreCustomerModal')">
                            Batal
                        </button>
                        <button type="submit" class="btn-primary-admin !bg-emerald-600 hover:!bg-emerald-700 !text-white !h-9 !px-5 text-xs font-semibold">
                            Aktifkan Kembali
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Hapus Permanen Pelanggan (Force Delete) -->
        <div class="modal-overlay modal-centered" id="forceDeleteCustomerModal">
            <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
                <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm border border-rose-100 shrink-0">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 m-0">Hapus Permanen Pelanggan</h3>
                            <p class="text-xs text-rose-600 font-semibold m-0 mt-0.5">Tindakan ini tidak dapat dibatalkan!</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('forceDeleteCustomerModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <form id="forceDeleteCustomerForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="p-6 space-y-4">
                        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 space-y-1">
                            <div class="font-bold flex items-center gap-1.5 text-rose-900">
                                <i class="fa-solid fa-trash-can"></i> Peringatan Penghapusan Permanen:
                            </div>
                            <p class="m-0 leading-relaxed">
                                Data pelanggan <strong id="forceDeleteCustomerName" class="text-slate-900 font-bold"></strong> beserta akun PPPoE dan file dokumentasi akan <strong>dihapus selamanya dari database</strong>.
                            </p>
                        </div>
                        <p class="text-xs text-slate-500 m-0">
                            Gunakan fitur ini hanya jika data benar-benar tidak diperlukan lagi atau terjadi duplikasi data.
                        </p>
                    </div>
                    <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex justify-between gap-2">
                        <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('forceDeleteCustomerModal')">
                            Batal
                        </button>
                        <button type="submit" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !text-white !h-9 !px-5 text-xs font-semibold">
                            Ya, Hapus Permanen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <script>
            // Focus Map
            function focusOnCustomer(tikor, customerId) {
                const coords = tikor.split(',').map(c => parseFloat(c.trim()));
                if (coords.length === 2) {
                    document.getElementById('customer-map').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    if (window.customerMap) {
                        window.customerMap.setView(coords, 18);
                        if (window.customerMarkers) {
                            window.customerMarkers.eachLayer(function(marker) {
                                const mCoords = marker.getLatLng();
                                if (Math.abs(mCoords.lat - coords[0]) < 0.0001 && Math.abs(mCoords.lng - coords[1]) <
                                    0.0001) {
                                    marker.openPopup();
                                }
                            });
                        }
                    }
                }
            }
            // Custom Modal Functions
            function openCustomModal(modalId, triggerEl = null) {
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

            let currentCustomerTrigger = null;

            function setValOrText(id, val) {
                const el = document.getElementById(id);
                if (!el) return;
                if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT') {
                    $(el).val(val);
                } else {
                    $(el).text(val);
                }
            }

            function toggleEditMode() {
                const modal = document.getElementById('customerDetailModal');
                if (modal) {
                    modal.classList.toggle('is-editing');
                }
            }

            function openCustomerDetailModal(triggerEl) {
                currentCustomerTrigger = triggerEl;
                const $btn = $(triggerEl);

                document.getElementById('customerDetailModal').classList.remove('is-editing');

                if ($('#detailFormInline').length) {
                    $('#detailFormInline').attr('action', '/customers/' + $btn.data('id'));
                }

                setValOrText('detail_nama_view', $btn.data('nama-lengkap'));
                setValOrText('detail_nama_input', $btn.data('nama-lengkap'));

                setValOrText('detail_nik_view', $btn.data('po-nik'));
                setValOrText('detail_nik_input', $btn.data('po-nik') === 'N/A' ? '' : $btn.data('po-nik'));

                setValOrText('detail_email_view', $btn.data('po-email'));
                setValOrText('detail_email_input', $btn.data('po-email') === 'N/A' ? '' : $btn.data('po-email'));

                const wa = $btn.data('po-wa');
                setValOrText('detail_wa_view', wa);
                setValOrText('detail_wa_input', wa);
                if (wa) {
                    $('#detail_wa_link').attr('href', `https://wa.me/${wa}`).show();
                } else {
                    $('#detail_wa_link').hide();
                }

                setValOrText('detail_sales_view', $btn.data('po-sales'));
                setValOrText('detail_sales_input', $btn.data('id-sales'));

                setValOrText('detail_tgl_po_view', $btn.data('po-tgl-po'));
                if ($btn.data('tgl-po-raw')) setValOrText('detail_tgl_po_input', $btn.data('tgl-po-raw'));

                setValOrText('detail_alamat_lengkap_view', $btn.data('po-alamat'));
                setValOrText('detail_alamat_lengkap_input', $btn.data('po-alamat'));

                const tikor = $btn.data('po-tikor');
                setValOrText('detail_tikor_view', tikor);
                setValOrText('detail_tikor_input', tikor === 'N/A' ? '' : tikor);
                if (tikor && tikor !== 'N/A') {
                    $('#detail_tikor_link').attr('href', `https://www.google.com/maps/dir/?api=1&destination=${tikor}`).show();
                } else {
                    $('#detail_tikor_link').hide();
                }

                setValOrText('detail_customer_id_view', $btn.data('customer-id'));
                setValOrText('detail_customer_id_input', $btn.data('customer-id'));

                setValOrText('detail_paket_view', $btn.data('po-paket'));
                setValOrText('detail_paket_input', $btn.data('paket-id'));

                setValOrText('detail_pppoe_user_view', $btn.data('pppoe-user'));
                setValOrText('detail_pppoe_user_input', $btn.data('pppoe-user'));

                setValOrText('detail_pppoe_pass_view', $btn.data('pppoe-pass'));
                setValOrText('detail_pppoe_pass_input', $btn.data('pppoe-pass'));

                setValOrText('detail_jenis_modem_view', $btn.data('jenis-modem'));
                setValOrText('detail_jenis_modem_input', $btn.data('jenis-modem'));

                setValOrText('detail_sn_view', $btn.data('sn-perangkat'));
                setValOrText('detail_sn_input', $btn.data('sn-perangkat'));

                setValOrText('detail_pembayaran_view', $btn.data('pembayaran'));
                setValOrText('detail_pembayaran_input', $btn.data('pembayaran'));

                setValOrText('detail_tgl_aktivasi_view', $btn.data('tgl-aktivasi'));
                if ($btn.data('tgl-aktivasi-raw')) setValOrText('detail_tgl_aktivasi_input', $btn.data('tgl-aktivasi-raw'));

                const handleImage = (imgId, placeholderId, src) => {
                    const $img = $(`#${imgId}`);
                    const $placeholder = $(`#${placeholderId}`);
                    if (src && src !== '' && !src.includes('null')) {
                        $img.attr('src', src).show();
                        $placeholder.hide();
                    } else {
                        $img.hide();
                        $placeholder.show();
                    }
                };

                handleImage('img_petugas', 'placeholder_petugas', $btn.data('foto-petugas'));
                handleImage('img_terpasang', 'placeholder_terpasang', $btn.data('foto-terpasang'));
                handleImage('img_speedtest', 'placeholder_speedtest', $btn.data('foto-speedtest'));

                setValOrText('detail_keterangan_view', $btn.data('keterangan'));
                setValOrText('detail_keterangan_input', $btn.data('keterangan'));

                // Handle Isolir status in Detail Modal
                const isIsolir = String($btn.data('is-isolir')) === '1';
                const isolirReason = $btn.data('isolir-reason') || '';
                const isolirAt = $btn.data('isolir-at') || '';
                const pppoeUser = $btn.data('pppoe-user') || '';
                const customerName = $btn.data('nama-lengkap') || '';
                const isolirUrl = $btn.data('isolir-url') || '';
                const bukaIsolirUrl = $btn.data('buka-isolir-url') || '';

                if (isIsolir) {
                    $('#detail_isolir_badge').html('<span class="badge" style="background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;font-size:10px;font-weight:800;padding:2px 8px;border-radius:6px;"><i class="fa-solid fa-ban mr-1"></i> TERISOLIR</span>');
                    $('#detail_isolir_reason').text(isolirReason || 'Isolir manual oleh Admin');
                    $('#detail_isolir_at').text(isolirAt || '-');
                    $('#detail_isolir_info').removeClass('hidden');

                    $('#btnDetailIsolirAction')
                        .removeClass('!bg-amber-500 hover:!bg-amber-600')
                        .addClass('!bg-emerald-600 hover:!bg-emerald-700 text-white')
                        .html('<i class="fa-solid fa-lock-open mr-1"></i> Buka Isolir Pelanggan Ini')
                        .off('click')
                        .on('click', function() {
                            openBukaIsolirCustomerModal(bukaIsolirUrl, customerName, pppoeUser);
                        })
                        .show();
                } else {
                    $('#detail_isolir_badge').html('<span class="badge" style="background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;font-size:10px;font-weight:800;padding:2px 8px;border-radius:6px;"><i class="fa-solid fa-circle-check mr-1"></i> NORMAL / AKTIF</span>');
                    $('#detail_isolir_info').addClass('hidden');

                    if (pppoeUser) {
                        $('#btnDetailIsolirAction')
                            .removeClass('!bg-emerald-600 hover:!bg-emerald-700')
                            .addClass('!bg-amber-500 hover:!bg-amber-600 text-white')
                            .html('<i class="fa-solid fa-ban mr-1"></i> Isolir Pelanggan Ini')
                            .off('click')
                            .on('click', function() {
                                openIsolirCustomerModal(isolirUrl, customerName, pppoeUser);
                            })
                            .show();
                    } else {
                        $('#btnDetailIsolirAction').hide();
                    }
                }

                $('#customerDetailModal .modal-tab').removeClass('active');
                $('#customerDetailModal .tab-panel').removeClass('active');
                $('#customerDetailModal .modal-tab[data-tab="tab-biodata"]').addClass('active');
                $('#tab-biodata').addClass('active');

                openCustomModal('customerDetailModal');
            }

            function openIsolirCustomerModal(url, name, username) {
                document.getElementById('isolirCustomerForm').action = url;
                document.getElementById('isolirCustomerName').innerText = name;
                document.getElementById('isolirCustomerUsername').innerText = username;
                document.getElementById('isolirCustomerReason').value = 'Isolir manual oleh Admin';
                openCustomModal('isolirCustomerModal');
            }

            function openBukaIsolirCustomerModal(url, name, username) {
                document.getElementById('bukaIsolirCustomerForm').action = url;
                document.getElementById('bukaIsolirCustomerName').innerText = name;
                document.getElementById('bukaIsolirCustomerUsername').innerText = username;
                openCustomModal('bukaIsolirCustomerModal');
            }

            function openDeactivateCustomerModal(url, name, username) {
                const form = document.getElementById('deactivateCustomerForm');
                const nameEl = document.getElementById('deactivateCustomerName');
                if (form && nameEl) {
                    form.action = url;
                    nameEl.innerText = name;
                    openCustomModal('deactivateCustomerModal');
                }
            }

            function openRestoreCustomerModal(url, name) {
                const form = document.getElementById('restoreCustomerForm');
                const nameEl = document.getElementById('restoreCustomerName');
                if (form && nameEl) {
                    form.action = url;
                    nameEl.innerText = name;
                    openCustomModal('restoreCustomerModal');
                }
            }

            function openForceDeleteCustomerModal(url, name) {
                const form = document.getElementById('forceDeleteCustomerForm');
                const nameEl = document.getElementById('forceDeleteCustomerName');
                if (form && nameEl) {
                    form.action = url;
                    nameEl.innerText = name;
                    openCustomModal('forceDeleteCustomerModal');
                }
            }

            $(document).ready(function() {
                // DataTables Isolir (Hanya jika tabel ada di DOM / Non-Admin)
                if ($('#isolir-table').length) {
                    $('#isolir-table').DataTable({
                        "pageLength": 5,
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
                    });
                }

                // DataTables Utama (Server Side)
                const table = $('#customer-table').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    ajax: {
                        url: '{{ route('customers.active_list') }}',
                        data: function(d) {
                            d.status_filter = $('#filter-status').val();
                        }
                    },
                    columns: [
                        {
                            data: null,
                            name: 'id',
                            searchable: false,
                            orderable: false,
                            className: 'text-center align-middle',
                            render: function(data, type, row, meta) {
                                return `<div style="color: #94a3b8; font-weight: 600; font-size: 12px;">${meta.row + meta.settings._iDisplayStart + 1}</div>`;
                            }
                        },
                        {
                            data: 'id_formatted',
                            name: 'id_pelanggan',
                            className: 'text-center align-middle'
                        },
                        {
                            data: 'nama_lengkap_formatted',
                            name: 'nama_lengkap',
                            orderable: true,
                            searchable: true,
                            className: 'text-left align-middle'
                        },
                        {
                            data: 'paket_formatted',
                            name: 'paket_id',
                            orderable: false,
                            searchable: false,
                            className: 'text-center align-middle'
                        },
                        {
                            data: 'teknisi_formatted',
                            name: 'teknisiUtama.nama_lengkap',
                            orderable: false,
                            searchable: true,
                            className: 'text-left align-middle'
                        },
                        {
                            data: 'tgl_formatted',
                            name: 'created_at',
                            orderable: true,
                            searchable: false,
                            className: 'text-center align-middle'
                        },
                        {
                            data: 'status_opsi',
                            name: 'status_opsi',
                            orderable: false,
                            searchable: false,
                            className: 'text-center align-middle'
                        }
                    ],
                    dom: 'tpir',
                    pageLength: 10,
                    lengthChange: false,
                    info: false,
                    ordering: false,
                    responsive: true,
                    language: {
                        processing: "<i class='fa-solid fa-circle-notch fa-spin text-sky-500 text-2xl'></i>",
                        infoEmpty: "Data tidak ditemukan",
                        emptyTable: "Belum ada data pelanggan",
                        zeroRecords: "Pencarian tidak menemukan hasil",
                        paginate: {
                            previous: '<i class="fa-solid fa-chevron-left"></i>',
                            next: '<i class="fa-solid fa-chevron-right"></i>'
                        }
                    }
                });

                // Custom Search & Filter
                $('#custom-search').on('keyup', function() {
                    table.search(this.value).draw();
                });

                $('#filter-status').on('change', function() {
                    table.ajax.reload();
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

                // Close on overlay click
                $('.modal-overlay').on('click', function(e) {
                    if (e.target === this) closeCustomModal(this.id);
                });

                // Init Map
                const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                });

                const gmapsSatellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: '© Google Maps'
                });

                const map = L.map('customer-map', {
                    center: [-6.56272, 107.75107], // Default view
                    zoom: 14,
                    layers: [osm]
                });

                const baseMaps = {
                    "Standard (OSM)": osm,
                    "Realistic (Google Satellite)": gmapsSatellite
                };

                L.control.layers(baseMaps).addTo(map);
                window.customerMap = map;

                const customers = @json($mapData ?? []);
                const markers = L.layerGroup().addTo(map);
                window.customerMarkers = markers;

                function formatBytes(bytes, decimals = 2) {
                    if (!bytes || bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const dm = decimals < 0 ? 0 : decimals;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
                }

                customers.forEach(c => {
                    if (c.koordinat) {
                        const coords = c.koordinat.split(',').map(v => parseFloat(v.trim()));
                        if (coords.length === 2) {
                            const color = c.is_online ? '#10b981' :
                                '#ef4444'; // Emerald for online, Red for offline
                            const marker = L.circleMarker(coords, {
                                radius: 8,
                                fillColor: color,
                                color: "#fff",
                                weight: 2,
                                opacity: 1,
                                fillOpacity: 0.9
                            }).addTo(markers);

                            let trafficInfo = '';
                            if (c.is_online) {
                                const rx = formatBytes(c.bytes_in);
                                const tx = formatBytes(c.bytes_out);
                                trafficInfo = `
                                    <div style="display:flex; justify-content:space-between; margin-top:5px; font-size:10px;">
                                        <span><i class="fa-solid fa-arrow-down" style="color:#0ea5e9;"></i> RX: ${rx}</span>
                                        <span><i class="fa-solid fa-arrow-up" style="color:#f59e0b;"></i> TX: ${tx}</span>
                                    </div>
                                `;
                            }

                            const popupContent = `
                                <div style="min-width: 200px; font-family: 'Inter', sans-serif;">
                                    <div style="font-weight: 800; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 6px;">
                                        ${c.nama}
                                    </div>
                                    <div style="font-size: 11px; margin-bottom: 4px; display:flex; justify-content:space-between;">
                                        <span style="color: #94a3b8; font-weight: 700;">USERNAME:</span>
                                        <span style="font-weight: 800; color: #0ea5e9;">${c.username}</span>
                                    </div>
                                    <div style="font-size: 11px; margin-bottom: 4px; display:flex; justify-content:space-between;">
                                        <span style="color: #94a3b8; font-weight: 700;">STATUS:</span>
                                        <span style="font-weight: 800; color: ${color};">${c.is_online ? 'ONLINE' : 'OFFLINE'}</span>
                                    </div>
                                    <div style="font-size: 11px; margin-bottom: 4px; display:flex; justify-content:space-between;">
                                        <span style="color: #94a3b8; font-weight: 700;">UPTIME:</span>
                                        <span style="font-weight: 700; color: #475569;">${c.uptime}</span>
                                    </div>
                                    <div style="font-size: 11px; margin-bottom: 4px; display:flex; justify-content:space-between;">
                                        <span style="color: #94a3b8; font-weight: 700;">IP:</span>
                                        <span style="font-weight: 700; color: #475569;">${c.ip_address}</span>
                                    </div>
                                    ${trafficInfo}
                                </div>
                            `;
                            marker.bindPopup(popupContent);
                        }
                    }
                });

                if (customers.length > 0) {
                    const allCoords = customers
                        .filter(c => c.koordinat)
                        .map(c => c.koordinat.split(',').map(v => parseFloat(v.trim())));

                    if (allCoords.length > 0) {
                        const bounds = L.latLngBounds(allCoords);
                        map.fitBounds(bounds, {
                            padding: [50, 50]
                        });
                    }
                }
            });

            // --- GenieACS Real-Time Modal Functions ---
            let currentAcsCustomer = { id: null, nama: '', idPelanggan: '' };
            let rawWifiPasswords = { acs_wlan1_pass: '', acs_wlan5_pass: '' };

            function openAcsModalFromDetail() {
                if (!currentCustomerTrigger) return;
                const $btn = $(currentCustomerTrigger);
                openCustomerAcsModal($btn.data('id'), $btn.data('nama-lengkap'), $btn.data('customer-id'));
            }

            function openCustomerAcsModal(id, nama, idPelanggan) {
                currentAcsCustomer = { id: id, nama: nama, idPelanggan: idPelanggan };
                $('#acs_modal_nama').text(nama || '-');
                $('#acs_modal_id_pelanggan').text(idPelanggan || '-');

                $('#acs_loading_state').show();
                $('#acs_error_state').hide();
                $('#acs_content_state').hide();
                $('#acs_btn_open_genieacs').hide();

                openCustomModal('customerAcsModal');
                fetchAcsData(id);
            }

            function refreshCurrentAcsModal() {
                if (currentAcsCustomer.id) {
                    $('#acs_loading_state').show();
                    $('#acs_error_state').hide();
                    $('#acs_content_state').hide();
                    fetchAcsData(currentAcsCustomer.id);
                }
            }

            function fetchAcsData(customerId) {
                $.ajax({
                    url: '/customers/' + customerId + '/acs-status',
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        $('#acs_loading_state').hide();
                        if (res.success && res.device) {
                            renderAcsContent(res);
                        } else {
                            renderAcsError(res);
                        }
                    },
                    error: function(xhr) {
                        $('#acs_loading_state').hide();
                        $('#acs_error_state').show();
                        $('#acs_content_state').hide();
                        $('#acs_error_title').text('Gagal Terhubung ke Server ACS');
                        $('#acs_error_msg').text('Server ACS tidak dapat dihubungi saat ini. Pastikan GenieACS aktif.');
                        $('#acs_err_sn').text('-');
                        $('#acs_err_pppoe').text('-');
                    }
                });
            }

            function renderAcsError(res) {
                $('#acs_error_state').show();
                $('#acs_content_state').hide();
                $('#acs_btn_open_genieacs').hide();
                $('#acs_error_title').text(res.message || 'Perangkat ONT Belum Terhubung di ACS');
                $('#acs_error_msg').text('Perangkat ONT pelanggan ini belum terdaftar atau sedang offline di GenieACS.');
                const c = res.customer || {};
                $('#acs_err_sn').text(c.sn_perangkat || 'Tidak ada');
                $('#acs_err_pppoe').text(c.pppoe_username || 'Tidak ada');
            }

            function renderAcsContent(res) {
                const d = res.device;
                $('#acs_content_state').show();
                $('#acs_error_state').hide();

                // Button Link to GenieACS
                if (d.genieacs_url) {
                    $('#acs_btn_open_genieacs').attr('href', d.genieacs_url).show();
                }

                // 1. Status
                if (d.is_online) {
                    $('#acs_status_badge').html('<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-100 text-emerald-700"><span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>ONLINE</span>');
                } else {
                    $('#acs_status_badge').html('<span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-200 text-slate-600">OFFLINE</span>');
                }
                $('#acs_last_inform').text('Inform: ' + (d.last_inform || '-'));

                // 2. Redaman RX
                const rxStr = d.rx_power ? (d.rx_power + ' dBm') : '-';
                const rxVal = parseFloat(String(d.rx_power || '0').replace(/[^0-9.-]/g, '')) || 0;
                $('#acs_rx_power').text(rxStr);
                const $rxCard = $('#acs_rx_card');
                $rxCard.removeClass('bg-emerald-50 border-emerald-200 text-emerald-700 bg-amber-50 border-amber-200 text-amber-700 bg-rose-50 border-rose-200 text-rose-700 bg-slate-50 border-slate-200 text-slate-700');
                if (d.rx_power) {
                    if (rxVal < -27) {
                        $rxCard.addClass('bg-rose-50 border-rose-200 text-rose-700');
                        $('#acs_rx_quality').text('Redaman Kritis (< -27 dBm)').attr('class', 'text-[10px] font-bold text-rose-600');
                    } else if (rxVal < -24) {
                        $rxCard.addClass('bg-amber-50 border-amber-200 text-amber-700');
                        $('#acs_rx_quality').text('Waspada (< -24 dBm)').attr('class', 'text-[10px] font-bold text-amber-600');
                    } else {
                        $rxCard.addClass('bg-emerald-50 border-emerald-200 text-emerald-700');
                        $('#acs_rx_quality').text('Sinyal Bagus / Normal').attr('class', 'text-[10px] font-bold text-emerald-600');
                    }
                } else {
                    $rxCard.addClass('bg-slate-50 border-slate-200 text-slate-700');
                    $('#acs_rx_quality').text('Tidak ada data').attr('class', 'text-[10px] font-semibold text-slate-400');
                }

                // 3. Suhu
                const tempStr = d.temperature ? (d.temperature + ' °C') : '-';
                const tempVal = parseFloat(String(d.temperature || '0').replace(/[^0-9.-]/g, '')) || 0;
                $('#acs_temperature').text(tempStr);
                const $tempCard = $('#acs_temp_card');
                $tempCard.removeClass('bg-rose-50 border-rose-200 text-rose-700 bg-slate-50 border-slate-200 text-slate-700');
                if (tempVal > 65) {
                    $tempCard.addClass('bg-rose-50 border-rose-200 text-rose-700');
                    $('#acs_temp_quality').text('Suhu Panas (> 65°C)').attr('class', 'text-[10px] font-bold text-rose-600');
                } else {
                    $tempCard.addClass('bg-slate-50 border-slate-200 text-slate-700');
                    $('#acs_temp_quality').text('Suhu Normal (< 60°C)').attr('class', 'text-[10px] font-semibold text-slate-500');
                }

                // 4. Model & IP
                $('#acs_model_vendor').text((d.manufacturer || '') + ' ' + (d.model || '-'));
                $('#acs_ip_wan').text('IP: ' + (d.ip_address || '-'));

                // 5. Wi-Fi 2.4 GHz
                const w1 = res.wlan1 || {};
                $('#acs_wlan1_ssid').text(w1.ssid || 'Belum diatur');
                rawWifiPasswords['acs_wlan1_pass'] = w1.password || '';
                $('#acs_wlan1_pass').text(w1.password ? '••••••••' : '(Tanpa Password)');
                $('#acs_wlan1_status').text(w1.status || 'Aktif');

                // 6. Wi-Fi 5 GHz
                const w5 = res.wlan5;
                if (w5 && w5.ssid) {
                    $('#acs_wlan5_container').show();
                    $('#acs_wlan5_ssid').text(w5.ssid);
                    rawWifiPasswords['acs_wlan5_pass'] = w5.password || '';
                    $('#acs_wlan5_pass').text(w5.password ? '••••••••' : '(Tanpa Password)');
                    $('#acs_wlan5_status').text(w5.status || 'Aktif');
                } else {
                    $('#acs_wlan5_container').hide();
                }

                // 7. Connected Hosts
                const hosts = res.hosts || [];
                const activeCount = res.total_connected || 0;
                $('#acs_hosts_badge').text(activeCount + ' Perangkat Terhubung');

                let html = '';
                if (hosts.length === 0) {
                    html = '<tr><td colspan="5" class="text-center py-6 text-slate-400">Tidak ada perangkat yang terdeteksi terhubung saat ini.</td></tr>';
                } else {
                    hosts.forEach(function(h) {
                        const isAct = h.active;
                        const badgeStatus = isAct
                            ? '<span class="badge bg-emerald-50 text-emerald-700 font-bold text-[10px] px-2 py-0.5 rounded">Aktif</span>'
                            : '<span class="badge bg-slate-100 text-slate-400 text-[10px] px-2 py-0.5 rounded">Offline</span>';
                        html += `<tr>
                            <td class="py-2.5 font-bold text-slate-800">${h.hostname || 'Tanpa Nama'}</td>
                            <td class="py-2.5 font-mono text-slate-600">${h.ip || '-'}</td>
                            <td class="py-2.5 font-mono text-slate-500">${h.mac || '-'}</td>
                            <td class="py-2.5 text-center"><span class="badge bg-sky-50 text-sky-700 text-[10px] font-bold">${h.interface || 'Wi-Fi'}</span></td>
                            <td class="py-2.5 text-center">${badgeStatus}</td>
                        </tr>`;
                    });
                }
                $('#acs_hosts_table_body').html(html);
            }

            function toggleAcsPassword(elemId) {
                const realPass = rawWifiPasswords[elemId];
                if (!realPass) return;
                const $el = $('#' + elemId);
                if ($el.text() === '••••••••') {
                    $el.text(realPass);
                } else {
                    $el.text('••••••••');
                }
            }

            function copyTextFromElement(elemId, btnEl) {
                const realPass = rawWifiPasswords[elemId];
                if (!realPass) return;
                navigator.clipboard.writeText(realPass).then(function() {
                    const $i = $(btnEl).find('i');
                    $i.removeClass('fa-copy').addClass('fa-check text-emerald-600');
                    setTimeout(function() {
                        $i.removeClass('fa-check text-emerald-600').addClass('fa-copy');
                    }, 2000);
                });
            }
        </script>
    @endsection
</x-layouts.app>
