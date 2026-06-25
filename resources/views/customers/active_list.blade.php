<x-layouts.app>
    @section('Css')
        <style>
            /* Hakatech DataTables Customization */
            .dataTables_wrapper { padding: 0; }
            .dataTables_filter { display: none; }
            .dataTables_paginate {
                display: flex; justify-content: flex-end; padding: 1.25rem 1.5rem; gap: 4px; border-top: 1px solid #f1f5f9;
            }
            .paginate_button {
                width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid #e2e8f0;
                background: #fff; color: #64748b !important; font-size: 13px; font-weight: 600;
                display: inline-flex; align-items: center; justify-content: center; cursor: pointer;
            }
            .paginate_button.current { background: #0ea5e9; border-color: #0ea5e9; color: #fff !important; }

            /* Full Capitalization for Table */
            #customer-table thead th, #customer-table tbody td {
                text-transform: uppercase !important; font-weight: 600; font-size: 12px;
            }

            /* Map Customizations */
            #customer-map {
                height: 400px;
                width: 100%;
                border-radius: 16px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                z-index: 1;
            }
            .map-container {
                position: relative;
                margin-bottom: 2rem;
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
                z-index: 1000;
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

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Data Pelanggan</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola data pelanggan yang sudah aktif dan monitoring isolir.</p>
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
                width: 38px; height: 38px; font-size: 16px;
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
            <div><div class="stat-val">{{ $totalActive }}</div><div class="stat-lbl">Aktif</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;"><i class="fa-solid fa-calendar-check"></i></div>
            <div><div class="stat-val">{{ $newThisMonth }}</div><div class="stat-lbl">Bulan Ini</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9;color:#475569;"><i class="fa-solid fa-user-slash"></i></div>
            <div><div class="stat-val">{{ $totalArchived }}</div><div class="stat-lbl">Non-Aktif</div></div>
        </div>
        
        @if ($is_admin)
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5;color:#059669;"><i class="fa-solid fa-wifi"></i></div>
            <div><div class="stat-val">{{ $totalOnlineAll }}</div><div class="stat-lbl">Online</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#b91c1c;"><i class="fa-solid fa-plane-slash"></i></div>
            <div><div class="stat-val">{{ $totalOfflineAll }}</div><div class="stat-lbl">Offline</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div><div class="stat-val">{{ $totalIsolirAll }}</div><div class="stat-lbl">Isolir</div></div>
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

    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input custom-search" id="custom-search" placeholder="Cari ID, Nama, atau Paket..." autocomplete="off" />
        </div>
        <div style="display:flex;gap:8px;">
            <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
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
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: center; width: 120px;">ID Pelanggan</th>
                        <th style="text-align: center; width: 250px;">Nama Pelanggan</th>
                        <th style="text-align: center; width: 150px;">No. WA</th>
                        <th style="text-align: center; width: 150px;">Teknisi</th>
                        <th style="text-align: center; width: 120px;">Tgl Aktivasi</th>
                        <th style="text-align: center; width: 120px;">Opsi</th>
                    </tr>
                </thead>
                <tbody style="border-top: none;">
                    <!-- Data is loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    @if (Auth::user()->jabatan === 'Sales' || Auth::user()->isAdmin())
        @if (isset($pelangganIsolir) && $pelangganIsolir->count() > 0)
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;margin-top:2rem;">
                <div>
                    <h1 style="font-size:18px;font-weight:900;margin:0;color:#dc2626;">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i>Pelanggan Terisolir
                    </h1>
                    <p style="font-size:12px;color:#64748b;margin:3px 0 0;">Daftar pelanggan yang saat ini sedang dalam masa isolir/suspend.</p>
                </div>
                
                <!-- Filter & Export Isolir -->
                <form action="{{ route('customers.export_isolir') }}" method="GET" style="display:flex; gap:8px; align-items:center;">
                    @if(Auth::user()->isAdmin())
                        <select name="sales_id" class="form-control" style="font-size:12px; padding:6px 12px; border-radius:8px; border:1px solid #e2e8f0;">
                            <option value="">-- Semua Sales --</option>
                            @php
                                // Unique sales from the isolir list
                                $salesList = $pelangganIsolir->pluck('sales')->filter()->unique('id');
                            @endphp
                            @foreach($salesList as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    @endif
                    <button type="submit" class="btn btn-primary" style="font-size:12px; padding:6px 16px; border-radius:8px; background:#dc2626; border-color:#dc2626; font-weight:700; cursor:pointer; color:#ffffff;">
                        <i class="fa-solid fa-file-pdf"></i> Export PDF
                    </button>
                </form>
            </div>

            <div class="table-card mb-8 border border-red-200 shadow-sm shadow-red-100">
                <div class="table-responsive">
                    <table id="isolir-table" class="admin-table">
                        <thead style="background:#fef2f2;">
                            <tr>
                                <th style="text-align: center; width: 50px; color:#991b1b;">#</th>
                                <th style="color:#991b1b;">Nama Pelanggan</th>
                                <th style="text-align: center; color:#991b1b;">Username PPPoE</th>
                                <th style="text-align: center; color:#991b1b;">Sales</th>
                                <th style="text-align: center; color:#991b1b;">No. WA</th>
                                <th style="text-align: center; color:#991b1b;">Paket</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pelangganIsolir as $isolir)
                                <tr>
                                    <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                        {{ $loop->iteration }}</td>
                                    <td style="font-weight: 700; color: #0f172a;">{{ $isolir->nama_lengkap }}</td>
                                    <td style="text-align: center; font-family:monospace; color:#0ea5e9; font-weight:700;">
                                        {{ $isolir->pppoe_username }}</td>
                                    <td style="text-align: center; font-weight: 600;">
                                        {{ $isolir->sales->nama_lengkap ?? '-' }}</td>
                                    <td style="text-align: center;">
                                        <a href="https://wa.me/{{ $isolir->no_whatsapp }}" target="_blank"
                                            style="color: #10b981; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-brands fa-whatsapp"></i> {{ $isolir->no_whatsapp }}
                                        </a>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge"
                                            style="background:#fef2f2; color:#dc2626; padding:4px 8px; border-radius:6px; font-size:10px; font-weight:800;">
                                            {{ $isolir->paket_internet }}
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

    {{-- Custom Tabbed Detail Modal --}}
    <div class="modal-overlay" id="customerDetailModal">
        <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-circle-info text-primary"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Detail Informasi Pelanggan</span>
                </div>
                <button class="modal-close" onclick="closeCustomModal('customerDetailModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-tabs bg-white border-b border-slate-200">
                <button class="modal-tab active" data-tab="tab-biodata">
                    <i class="fa-solid fa-id-card"></i> Biodata
                </button>
                <button class="modal-tab" data-tab="tab-teknis">
                    <i class="fa-solid fa-gears"></i> Data Teknis
                </button>
                <button class="modal-tab" data-tab="tab-dokumentasi">
                    <i class="fa-solid fa-camera-retro"></i> Dokumentasi
                </button>
            </div>

            <div class="modal-body !p-0 bg-white">
                <!-- Tab Biodata -->
                <div class="tab-panel active" id="tab-biodata">
                    <div class="grid grid-cols-1 lg:grid-cols-5">
                        <div class="lg:col-span-3 p-8 space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-4">
                                    <h6
                                        class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] border-l-4 border-primary pl-3">
                                        Biodata Inti</h6>
                                    <div class="space-y-4">
                                        <div>
                                            <span class="text-[11px] font-bold text-slate-400 block uppercase">Nomor
                                                Identitas (NIK)</span>
                                            <p id="detail_nik"
                                                class="text-xl font-black text-slate-900 leading-none mt-1"></p>
                                        </div>
                                        <div>
                                            <span class="text-[11px] font-bold text-slate-400 block uppercase">Alamat
                                                Email</span>
                                            <p id="detail_email"
                                                class="text-base font-black text-primary truncate mt-1"></p>
                                        </div>
                                        <div>
                                            <span class="text-[11px] font-bold text-slate-400 block uppercase">No. WhatsApp</span>
                                            <div class="flex items-center gap-2 mt-1">
                                                <p id="detail_wa" class="text-sm font-bold text-emerald-600 m-0"></p>
                                                <a id="detail_wa_link" href="#" target="_blank"
                                                    class="bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded text-[10px] font-black no-underline">
                                                    <i class="fa-brands fa-whatsapp"></i> CHAT
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <h6
                                        class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] border-l-4 border-indigo-500 pl-3">
                                        Informasi Sales</h6>
                                    <div class="space-y-4">
                                        <div>
                                            <span class="text-[11px] font-bold text-slate-400 block uppercase">Petugas
                                                Sales</span>
                                            <p id="detail_sales" class="text-sm font-bold text-slate-800 mt-1"></p>
                                        </div>
                                        <div>
                                            <span class="text-[11px] font-bold text-slate-400 block uppercase">Tgl.
                                                Pre-Order</span>
                                            <p id="detail_tgl_po" class="text-sm font-bold text-slate-800 mt-1"></p>
                                        </div>
                                        <div>
                                            <span class="text-[11px] font-bold text-slate-400 block uppercase">Titik Koordinat</span>
                                            <div class="flex items-center gap-2 mt-1">
                                                <p id="detail_tikor" class="text-sm font-bold text-indigo-600 m-0"></p>
                                                <a id="detail_tikor_link" href="#" target="_blank"
                                                    class="bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded text-[10px] font-black no-underline">
                                                    <i class="fa-solid fa-map-location-dot"></i> MAPS
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <h6
                                    class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] border-l-4 border-slate-400 pl-3">
                                    Lokasi Pasang</h6>
                                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                                    <p id="detail_alamat_lengkap"
                                        class="text-sm font-bold text-slate-700 leading-relaxed"></p>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-2 p-8 bg-slate-50 border-l border-slate-200">
                            <div class="bg-primary/5 p-4 rounded-2xl border border-primary/10">
                                <p class="text-[10px] text-slate-500 leading-relaxed font-medium italic text-center">
                                    Data ini bersumber dari formulir Pre-Order asli yang diinput oleh tim Sales.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Teknis -->
                <div class="tab-panel p-8" id="tab-teknis">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <h6
                                class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] border-l-4 border-sky-500 pl-3">
                                Akun & Layanan</h6>
                            <div class="bg-sky-50 p-5 rounded-2xl border border-sky-100 space-y-4">
                                <div>
                                    <span class="text-[10px] font-black text-sky-400 block uppercase">ID
                                        Pelanggan</span>
                                    <p id="detail_customer_id" class="text-xl font-black text-sky-700 mt-1"></p>
                                </div>
                                <div>
                                    <span class="text-[10px] font-black text-sky-400 block uppercase">Paket
                                        Internet</span>
                                    <p id="detail_paket" class="text-sm font-black text-sky-800 mt-1"></p>
                                </div>
                                <hr class="border-sky-200">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-[10px] font-black text-sky-400 block uppercase">PPPoE
                                            User</span>
                                        <p id="detail_pppoe_user" class="text-xs font-bold text-slate-700"></p>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-black text-sky-400 block uppercase">PPPoE
                                            Pass</span>
                                        <p id="detail_pppoe_pass" class="text-xs font-bold text-slate-700"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <h6
                                class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] border-l-4 border-amber-500 pl-3">
                                Perangkat & Billing</h6>
                            <div class="space-y-4">
                                <div>
                                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Jenis
                                        Modem</span>
                                    <p id="detail_jenis_modem" class="text-sm font-bold text-slate-800 mt-1"></p>
                                </div>
                                <div>
                                    <span class="text-[11px] font-bold text-slate-400 block uppercase">SN
                                        Perangkat</span>
                                    <p id="detail_sn" class="text-sm font-bold text-slate-800 mt-1"></p>
                                </div>
                                <div>
                                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Metode
                                        Pembayaran</span>
                                    <p id="detail_pembayaran" class="text-sm font-bold text-amber-600 mt-1"></p>
                                </div>
                                <div>
                                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Tanggal
                                        Aktivasi</span>
                                    <p id="detail_tgl_aktivasi" class="text-sm font-bold text-slate-800 mt-1"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Dokumentasi -->
                <div class="tab-panel overflow-x-hidden" id="tab-dokumentasi">
                    <div class="grid grid-cols-1 lg:grid-cols-5">
                        <div class="lg:col-span-3 p-5 sm:p-8 space-y-6">
                            <h6
                                class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] border-l-4 border-emerald-500 pl-3">
                                Hasil Instalasi</h6>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-4">
                                <div class="space-y-2">
                                    <span
                                        class="text-[10px] font-bold text-slate-500 block text-center uppercase">Petugas</span>
                                    <div
                                        class="aspect-[3/4] w-full rounded-2xl overflow-hidden border-2 border-white shadow-md bg-slate-100 flex items-center justify-center">
                                        <img id="img_petugas" src=""
                                            class="w-full h-full object-cover hidden cursor-pointer"
                                            onclick="window.open(this.src, '_blank')">
                                        <i class="fa-solid fa-user-gear text-slate-300 text-3xl"
                                            id="placeholder_petugas"></i>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <span
                                        class="text-[10px] font-bold text-slate-500 block text-center uppercase">Perangkat</span>
                                    <div
                                        class="aspect-[3/4] w-full rounded-2xl overflow-hidden border-2 border-white shadow-md bg-slate-100 flex items-center justify-center">
                                        <img id="img_terpasang" src=""
                                            class="w-full h-full object-cover hidden cursor-pointer"
                                            onclick="window.open(this.src, '_blank')">
                                        <i class="fa-solid fa-microchip text-slate-300 text-3xl"
                                            id="placeholder_terpasang"></i>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <span
                                        class="text-[10px] font-bold text-slate-500 block text-center uppercase">Speedtest</span>
                                    <div
                                        class="aspect-[3/4] w-full rounded-2xl overflow-hidden border-2 border-white shadow-md bg-slate-100 flex items-center justify-center">
                                        <img id="img_speedtest" src=""
                                            class="w-full h-full object-cover hidden cursor-pointer"
                                            onclick="window.open(this.src, '_blank')">
                                        <i class="fa-solid fa-gauge-high text-slate-300 text-3xl"
                                            id="placeholder_speedtest"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="lg:col-span-2 p-5 sm:p-8 bg-slate-50 border-t lg:border-t-0 lg:border-l border-slate-200">
                            <h6 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-3">Catatan
                                Teknisi</h6>
                            <div class="bg-white p-4 rounded-2xl border border-slate-200 min-h-[120px]">
                                <p id="detail_keterangan"
                                    class="text-sm font-medium text-slate-600 italic leading-relaxed break-words"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button type="button" class="btn-cancel w-full"
                    onclick="closeCustomModal('customerDetailModal')">
                    <i class="fa-solid fa-xmark"></i> Tutup Detail
                </button>
            </div>
        </div>
    </div>

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
                                if (Math.abs(mCoords.lat - coords[0]) < 0.0001 && Math.abs(mCoords.lng - coords[1]) < 0.0001) {
                                    marker.openPopup();
                                }
                            });
                        }
                    }
                }
            }
            // Custom Modal Functions
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

            function openCustomerDetailModal(triggerEl) {
                const $btn = $(triggerEl);

                // Populate Biodata
                $('#title_nama_pelanggan').text($btn.data('nama-lengkap'));
                $('#detail_nik').text($btn.data('po-nik'));
                $('#detail_email').text($btn.data('po-email'));
                const wa = $btn.data('po-wa');
                $('#detail_wa').text(wa);
                if (wa) {
                    $('#detail_wa_link').attr('href', `https://wa.me/${wa}`).show();
                } else {
                    $('#detail_wa_link').hide();
                }
                $('#detail_sales').text($btn.data('po-sales'));
                $('#detail_tgl_po').text($btn.data('po-tgl-po'));
                $('#detail_alamat_lengkap').text($btn.data('po-alamat'));

                const tikor = $btn.data('po-tikor');
                $('#detail_tikor').text(tikor);
                if (tikor && tikor !== 'N/A') {
                    $('#detail_tikor_link').attr('href', `https://www.google.com/maps/dir/?api=1&destination=${tikor}`).show();
                } else {
                    $('#detail_tikor_link').hide();
                }

                // Populate Teknis
                $('#detail_customer_id').text($btn.data('customer-id'));
                $('#detail_paket').text($btn.data('po-paket'));
                $('#detail_pppoe_user').text($btn.data('pppoe-user'));
                $('#detail_pppoe_pass').text($btn.data('pppoe-pass'));
                $('#detail_jenis_modem').text($btn.data('jenis-modem'));
                $('#detail_sn').text($btn.data('sn-perangkat'));
                $('#detail_pembayaran').text($btn.data('pembayaran'));
                $('#detail_tgl_aktivasi').text($btn.data('tgl-aktivasi'));

                // Populate Dokumentasi
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
                $('#detail_keterangan').text($btn.data('keterangan'));

                // Reset tabs
                $('#customerDetailModal .modal-tab').first().click();

                openCustomModal('customerDetailModal');
            }

            $(document).ready(function() {
                // DataTables Isolir
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

                // DataTables Utama (Server Side)
                const table = $('#customer-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route("customers.active_list") }}',
                    columns: [
                        { data: null, name: 'id', searchable: false, orderable: false, className: 'text-center align-middle', render: function (data, type, row, meta) {
                            return `<div style="color: #94a3b8; font-weight: 700;">${meta.row + meta.settings._iDisplayStart + 1}</div>`;
                        }},
                        { data: 'id_pelanggan', name: 'id_pelanggan', className: 'text-center align-middle', render: function(data) {
                            return `<div style="font-weight: 800; color: #0ea5e9;">${data}</div>`;
                        }},
                        { data: 'nama_lengkap_formatted', name: 'nama_lengkap', orderable: true, searchable: true, className: 'text-center align-middle' },
                        { data: 'wa_formatted', name: 'no_whatsapp', orderable: false, searchable: true, className: 'text-center align-middle' },
                        { data: 'teknisi_formatted', name: 'teknisiUtama.nama_lengkap', orderable: false, searchable: true, className: 'text-center align-middle' },
                        { data: 'tgl_formatted', name: 'created_at', orderable: true, searchable: false, className: 'text-center align-middle text-slate-500 font-semibold text-xs' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle' }
                    ],
                    dom: 'tpir',
                    pageLength: 10,
                    lengthChange: false,
                    info: false,
                    ordering: false,
                    responsive: true,
                    language: {
                        processing: "<i class='fa-solid fa-circle-notch fa-spin text-primary text-2xl'></i>",
                        infoEmpty: "Data tidak ditemukan",
                        emptyTable: "Belum ada data pelanggan",
                        zeroRecords: "Pencarian tidak menemukan hasil",
                        paginate: {
                            first: "<i class='fa-solid fa-angles-left'></i>",
                            last: "<i class='fa-solid fa-angles-right'></i>",
                            next: "<i class='fa-solid fa-angle-right'></i>",
                            previous: "<i class='fa-solid fa-angle-left'></i>"
                        }
                    }
                });

                // Custom Search
                $('#custom-search').on('keyup', function() {
                    table.search(this.value).draw();
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
                            const color = c.is_online ? '#10b981' : '#ef4444'; // Emerald for online, Red for offline
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
        </script>
    @endsection
</x-layouts.app>
