<x-layouts.app>
    @section('Css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            /* Hakatech DataTables Customization */
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

            #admin-maintenance-table td,
            #pending-tasks-table td,
            #customer-search-table td {
                padding: 10px 14px;
                font-size: 13px;
            }

            /* Custom Modal Styles for Activation/Maintenance */
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

    @php $is_admin = Auth::user()->isAdmin(); @endphp

    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Pemeliharaan</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola tugas perbaikan dan pemeliharaan jaringan
                pelanggan.</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if ($is_admin)
                <button type="button" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                    onclick="openCustomModal('addMaintenanceModal')">
                    <i class="fa-solid fa-plus-square"></i> Tambah Pemeliharaan
                </button>
            @endif
        </div>
    </div>

    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;"><i
                    class="fa-solid fa-screwdriver-wrench"></i></div>
            <div>
                <div class="stat-val">{{ $totalThisMonth }}</div>
                <div class="stat-lbl">Bulan Ini</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-calendar-week"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalThisWeek }}</div>
                <div class="stat-lbl">Minggu Ini</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;"><i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalCompletedThisMonth }}</div>
                <div class="stat-lbl">Selesai (Bulan Ini)</div>
            </div>
        </div>
    </div>

    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input custom-search" placeholder="Cari pelanggan atau kendala..."
                autocomplete="off" />
        </div>
    </div>

    @if ($is_admin)
        <div class="table-card">
            <div class="table-responsive">
                <table id="admin-maintenance-table" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 45px;">#</th>
                            <th style="text-align: left; min-width: 220px;">Pelanggan</th>
                            <th style="text-align: center; width: 100px;">Status</th>
                            <th style="text-align: left; width: 160px;">Teknisi</th>
                            <th style="text-align: center; width: 130px;">Waktu Selesai</th>
                            <th style="text-align: center; width: 120px;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody style="border-top: none;">
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="table-card">
            <div class="table-responsive">
                <table id="pending-tasks-table" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 45px;">#</th>
                            <th style="text-align: left; min-width: 220px;">Pelanggan</th>
                            <th style="text-align: left; min-width: 200px;">Kendala</th>
                            <th style="text-align: center; width: 150px;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody style="border-top: none;">
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Modal Area --}}
    <div class="modal-overlay" id="searchCustomerModal" style="z-index: 10001;">
        <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Cari & Pilih Pelanggan</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Pilih pelanggan yang akan dibuatkan tugas pemeliharaan.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('searchCustomerModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-5 bg-white">
                <div class="search-wrap mb-4 !max-w-full">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="search" class="search-input" id="customer-modal-search"
                        placeholder="Ketik nama, ID pelanggan, atau alamat..." autocomplete="off" />
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table id="customer-search-table" class="admin-table w-full">
                        <thead>
                            <tr>
                                <th style="width: 45px; text-align: center;">#</th>
                                <th style="text-align: left;">Pelanggan</th>
                                <th style="width: 120px; text-align: center;">ID Pelanggan</th>
                                <th style="text-align: left;">Alamat</th>
                                <th style="width: 80px; text-align: center;">Opsi</th>
                            </tr>
                        </thead>
                        <tbody style="border-top: none;">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex justify-end">
                <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('searchCustomerModal')">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="addMaintenanceModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <form action="{{ route('maintenances.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 m-0">Buat Tugas Pemeliharaan</h3>
                            <p class="text-xs text-slate-500 m-0 mt-0.5">Tambahkan penugasan perbaikan atau kendala jaringan pelanggan.</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('addMaintenanceModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body p-5 space-y-4 bg-white">
                    <div class="form-group">
                        <label class="form-label text-slate-700 font-medium block mb-1 text-xs">Pelanggan Terpilih <span class="text-rose-500">*</span></label>
                        <div class="flex gap-2">
                            <input type="hidden" name="customer_id" id="selected_customer_id" required>
                            <input type="text" id="selected_customer_name"
                                class="form-input-custom !bg-slate-50 cursor-not-allowed flex-1 !font-normal text-slate-800 text-xs"
                                placeholder="Belum ada pelanggan terpilih..." readonly required>
                            <button type="button" class="btn-primary-admin !shadow-none !px-4 !h-[38px] text-xs font-semibold"
                                onclick="openCustomModal('searchCustomerModal')">Pilih Pelanggan</button>
                        </div>
                    </div>
                    <div id="customer_preview" class="hidden">
                        <div class="bg-slate-50 rounded-xl border border-slate-200 p-3.5 space-y-1">
                            <p id="pppoe_val" class="text-xs font-mono font-semibold text-slate-800 m-0"></p>
                            <p id="alamat_val" class="text-xs text-slate-600 m-0 leading-relaxed"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label text-slate-700 font-medium block mb-1 text-xs">Foto Kendala (Opsional)</label>
                        <input type="file" name="foto_kendala_awal" class="form-input !p-1.5 !h-auto text-xs !font-normal" accept="image/*">
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label text-slate-700 font-medium block mb-1 text-xs">Deskripsi Kendala <span class="text-rose-500">*</span></label>
                        <textarea name="keterangan_kendala" class="form-textarea !font-normal text-xs text-slate-800" required rows="3"
                            placeholder="Jelaskan detail kendala yang dialami pelanggan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                        onclick="closeCustomModal('addMaintenanceModal')">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !h-9 !px-5 text-xs font-semibold">
                        Kirim Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Maintenance Detail Modal -->
    <div class="modal-overlay" id="maintenanceDetailModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Detail Tugas Pemeliharaan</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Informasi kendala dan data pelanggan yang dilaporkan.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('maintenanceDetailModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-5 bg-white space-y-4 max-h-[75vh] overflow-y-auto">
                <div class="border border-slate-200 rounded-xl p-4 bg-white space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div>
                                <span class="text-[11px] font-medium text-slate-500 uppercase block mb-1">Nama Pelanggan</span>
                                <p id="det_nama" class="text-sm font-bold text-slate-900 m-0"></p>
                            </div>
                            <div>
                                <span class="text-[11px] font-medium text-slate-500 uppercase block mb-1">ID Pelanggan</span>
                                <p id="det_id_pel" class="text-xs font-mono font-semibold text-slate-700 m-0"></p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <span class="text-[11px] font-medium text-slate-500 uppercase block mb-1">No. WhatsApp</span>
                                <div class="flex items-center gap-2">
                                    <p id="det_wa" class="text-xs font-mono font-semibold text-slate-800 m-0"></p>
                                    <a id="det_wa_link" href="#" target="_blank"
                                        class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2 py-0.5 rounded transition">
                                        <i class="fa-brands fa-whatsapp"></i> Chat
                                    </a>
                                </div>
                            </div>
                            <div>
                                <span class="text-[11px] font-medium text-slate-500 uppercase block mb-1">Titik Koordinat</span>
                                <a id="det_tikor_link" href="#" target="_blank"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 hover:text-sky-600 bg-white hover:bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-lg transition shadow-sm">
                                    <i class="fa-solid fa-location-arrow text-[10px] text-sky-500"></i> Buka Maps
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/60 space-y-3">
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Alamat Pemasangan</span>
                        <p id="det_alamat" class="text-xs text-slate-700 leading-relaxed m-0 bg-white p-2.5 rounded-lg border border-slate-200"></p>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Deskripsi Kendala</span>
                        <div class="bg-white border border-slate-200 p-3 rounded-lg text-xs font-normal text-slate-800 leading-relaxed break-words"
                            id="det_kendala"></div>
                    </div>
                </div>

                <div id="det_foto_container" class="hidden border border-slate-200 rounded-xl p-4 bg-white space-y-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Foto Kendala Awal</span>
                    <div class="aspect-video w-full max-h-56 rounded-lg overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center">
                        <img id="det_foto" src="" class="w-full h-full object-contain cursor-pointer hover:scale-105 transition duration-200" onclick="window.open(this.src, '_blank')">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex justify-end">
                <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                    onclick="closeCustomModal('maintenanceDetailModal')">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Maintenance Result Modal -->
    <div class="modal-overlay" id="maintenanceResultModal">
        <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm border border-emerald-200 shrink-0">
                        <i class="fa-solid fa-file-circle-check"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Hasil Perbaikan Pemeliharaan</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Dokumentasi dan catatan solusi teknisi yang telah selesai.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('maintenanceResultModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-tabs bg-white border-b border-slate-200 px-6">
                <button type="button" class="modal-tab active" data-tab="tab-res-data">
                    <i class="fa-regular fa-file-lines text-xs"></i> Data Teknis
                </button>
                <button type="button" class="modal-tab" data-tab="tab-res-media">
                    <i class="fa-regular fa-image text-xs"></i> Dokumentasi Foto
                </button>
            </div>
            <div class="modal-body p-5 bg-white space-y-4 max-h-[75vh] overflow-y-auto">
                <div class="tab-panel active" id="tab-res-data">
                    <div class="border border-slate-200 rounded-xl p-4 bg-white space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 uppercase block mb-1">Nama Pelanggan</span>
                                    <p id="res_nama" class="text-sm font-bold text-slate-900 m-0"></p>
                                </div>
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 uppercase block mb-1">Teknisi Eksekutor</span>
                                    <p id="res_eksekutor" class="text-xs font-semibold text-slate-700 m-0"></p>
                                </div>
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 uppercase block mb-1">Rekan Tim</span>
                                    <p id="res_partner" class="text-xs text-slate-600 m-0 font-medium"></p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 uppercase block mb-1">Waktu Selesai</span>
                                    <p id="res_waktu" class="text-xs font-mono font-semibold text-slate-700 m-0"></p>
                                </div>
                                <div>
                                    <span class="text-[11px] font-medium text-slate-500 uppercase block mb-1">Kategori Kendala</span>
                                    <span id="res_kategori" class="badge text-[10px] font-semibold py-0.5 px-2 bg-slate-100 text-slate-700 border border-slate-200"></span>
                                </div>
                                <div id="res_ont_info" style="display:none;" class="bg-amber-50/70 border border-amber-200 p-2.5 rounded-lg space-y-0.5">
                                    <span class="text-[10px] font-bold text-amber-800 uppercase block">Informasi Pergantian ONT</span>
                                    <p class="text-xs font-medium text-slate-700 m-0">Lama: <span id="res_sn_lama" class="font-mono font-semibold"></span></p>
                                    <p class="text-xs font-medium text-slate-700 m-0">Baru: <span id="res_sn_baru" class="font-mono font-semibold text-emerald-700"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/60 space-y-2 mt-4">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Tindakan Solusi</span>
                        <div class="bg-white border border-slate-200 p-3 rounded-lg text-xs font-normal text-slate-800 leading-relaxed break-words"
                            id="res_solusi"></div>
                    </div>
                </div>
                <div class="tab-panel" id="tab-res-media">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-semibold text-slate-500 block text-center uppercase">Sebelum</span>
                            <div class="aspect-[4/3] rounded-lg overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center">
                                <img id="res_before" src="" class="w-full h-full object-cover cursor-pointer hover:scale-105 transition duration-200" onclick="window.open(this.src, '_blank')">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-semibold text-slate-500 block text-center uppercase">Sesudah</span>
                            <div class="aspect-[4/3] rounded-lg overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center">
                                <img id="res_after" src="" class="w-full h-full object-cover cursor-pointer hover:scale-105 transition duration-200" onclick="window.open(this.src, '_blank')">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-semibold text-slate-500 block text-center uppercase">Speedtest</span>
                            <div class="aspect-[4/3] rounded-lg overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center">
                                <img id="res_speed" src="" class="w-full h-full object-cover cursor-pointer hover:scale-105 transition duration-200" onclick="window.open(this.src, '_blank')">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-semibold text-slate-500 block text-center uppercase">Selfie Tim</span>
                            <div class="aspect-[4/3] rounded-lg overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center">
                                <img id="res_selfie" src="" class="w-full h-full object-cover cursor-pointer hover:scale-105 transition duration-200" onclick="window.open(this.src, '_blank')">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex justify-end">
                <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                    onclick="closeCustomModal('maintenanceResultModal')">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Selesai (Teknisi) --}}
    <div class="modal-overlay" id="completeModal">
        <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm border border-emerald-200 shrink-0">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Laporan Penyelesaian Pemeliharaan</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Pelanggan: <span id="nama_p" class="font-bold text-slate-800"></span></p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('completeModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-tabs bg-white border-b border-slate-200 px-6">
                <button type="button" class="modal-tab active" data-tab="tab-m-data">
                    <i class="fa-regular fa-clipboard text-xs"></i> Penugasan
                </button>
                <button type="button" class="modal-tab" data-tab="tab-m-solusi">
                    <i class="fa-regular fa-lightbulb text-xs"></i> Solusi Teknis
                </button>
                <button type="button" class="modal-tab" data-tab="tab-m-media">
                    <i class="fa-regular fa-image text-xs"></i> Dokumentasi
                </button>
            </div>
            <form id="completeForm" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-body bg-white p-5 space-y-4 max-h-[75vh] overflow-y-auto">
                    <div class="tab-panel active" id="tab-m-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-4">
                                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                                    <span class="text-[10px] font-bold text-slate-500 block uppercase tracking-wider mb-1">Status Laporan</span>
                                    <p class="text-xs font-semibold text-slate-700 m-0">Menunggu penyelesaian teknisi di lapangan.</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-slate-700 font-medium block mb-1 text-xs">Nama Pelanggan</label>
                                    <input type="text" class="form-input-custom !bg-slate-50 text-slate-800 text-xs !font-normal" id="m_nama_lengkap" readonly>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="form-group">
                                    <label class="form-label text-slate-700 font-medium block mb-1 text-xs">Partner / Teknisi Tambahan</label>
                                    <div class="partner-card-scroll">
                                        <div class="grid grid-cols-1 gap-1">
                                            @forelse ($teknisis as $t)
                                                @if ($t->id != auth()->id())
                                                    <div class="partner-item">
                                                        <input
                                                            class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary"
                                                            type="checkbox" name="partner_ids[]"
                                                            value="{{ $t->id }}"
                                                            id="m_partner_{{ $t->id }}">
                                                        <label
                                                            class="text-xs font-medium text-slate-700 cursor-pointer w-full"
                                                            for="m_partner_{{ $t->id }}">
                                                            {{ $t->nama_lengkap }}
                                                        </label>
                                                    </div>
                                                @endif
                                            @empty
                                                <div class="text-center py-4 text-slate-400 italic text-xs">Tidak ada teknisi lain.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-panel" id="tab-m-solusi">
                        <div class="space-y-4">
                            <div class="form-group">
                                <label class="form-label text-slate-700 font-medium block mb-1 text-xs">Kategori Masalah <span class="text-rose-500">*</span></label>
                                <select name="kategori_kendala" id="m_kategori_kendala"
                                    class="form-input-custom w-full bg-white text-slate-900 border-slate-300 text-xs !font-normal" required
                                    onchange="toggleOntFields()">
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="ONT">Pergantian ONT / Modem</option>
                                    <option value="Kabel Fiber Optic">Perbaikan Kabel Fiber Optic</option>
                                    <option value="Lainnya">Masalah Lainnya</option>
                                </select>
                            </div>
                            <div id="ont_fields" style="display: none;"
                                class="bg-amber-50/70 p-3.5 rounded-xl border border-amber-200 space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="form-label text-amber-800 font-semibold uppercase text-[10px] block mb-1">SN ONT Sebelumnya</label>
                                        <input type="text" id="m_sn_lama"
                                            class="form-input-custom bg-amber-100/50 border-amber-200 text-amber-900 font-mono text-xs"
                                            readonly>
                                    </div>
                                    <div>
                                        <label class="form-label text-amber-800 font-semibold uppercase text-[10px] block mb-1">SN ONT Pengganti <span class="text-rose-500">*</span></label>
                                        <input type="text" name="sn_baru" id="m_sn_baru"
                                            class="form-input-custom border-amber-300 font-mono text-xs !font-normal text-slate-800"
                                            placeholder="Masukkan SN Baru...">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-700 font-medium block mb-1 text-xs">Solusi / Tindakan Perbaikan <span class="text-rose-500">*</span></label>
                                <textarea name="solusi_kendala" class="form-textarea !font-normal text-xs text-slate-800" required rows="4"
                                    placeholder="Jelaskan secara detail tindakan perbaikan yang dilakukan..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="tab-panel" id="tab-m-media">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="form-group">
                                <label class="form-label text-slate-700 font-medium block mb-1 text-xs text-center">Foto Sebelum <span class="text-rose-500">*</span></label>
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-center">
                                    <input type="file" name="foto_sebelum" class="form-input !p-1 !h-auto text-[11px] !font-normal"
                                        required accept="image/*">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-700 font-medium block mb-1 text-xs text-center">Foto Sesudah <span class="text-rose-500">*</span></label>
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-center">
                                    <input type="file" name="foto_sesudah" class="form-input !p-1 !h-auto text-[11px] !font-normal"
                                        required accept="image/*">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-700 font-medium block mb-1 text-xs text-center">Speedtest <span class="text-rose-500">*</span></label>
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-center">
                                    <input type="file" name="foto_speedtest" class="form-input !p-1 !h-auto text-[11px] !font-normal"
                                        required accept="image/*">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-700 font-medium block mb-1 text-xs text-center">Selfie Tim <span class="text-rose-500">*</span></label>
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-center">
                                    <input type="file" name="foto_selfie" class="form-input !p-1 !h-auto text-[11px] !font-normal" required
                                        accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                        onclick="closeCustomModal('completeModal')">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !h-9 !px-5 text-xs font-semibold">
                        Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            let customerSearchTable = null;
            function initCustomerSearchTable() {
                if (!customerSearchTable && $('#customer-search-table').length) {
                    customerSearchTable = $('#customer-search-table').DataTable({
                        "pageLength": 5,
                        "lengthChange": false,
                        "ordering": false,
                        "info": false,
                        "responsive": true,
                        "dom": 'tpir',
                        processing: true,
                        serverSide: true,
                        ajax: '{{ route('maintenances.index') }}?type=customers',
                        columns: [
                            { data: 'no', name: 'no', searchable: false, orderable: false, className: 'text-center align-middle', render: function(d,t,r,m){ return `<div style="color: #94a3b8; font-weight: 600; font-size: 12px;">${m.row + m.settings._iDisplayStart + 1}</div>`; } },
                            { data: 'pelanggan', name: 'nama_lengkap', className: 'text-left align-middle' },
                            { data: 'id_pelanggan', name: 'id_pelanggan', className: 'text-center align-middle' },
                            { data: 'alamat_lengkap', name: 'alamat_lengkap', className: 'text-left align-middle' },
                            { data: 'opsi', name: 'opsi', searchable: false, orderable: false, className: 'text-center align-middle' }
                        ],
                        language: {
                            "zeroRecords": "Pelanggan tidak ditemukan",
                            "paginate": {
                                "previous": '<i class="fa-solid fa-chevron-left"></i>',
                                "next": '<i class="fa-solid fa-chevron-right"></i>'
                            },
                            processing: "<i class='fa-solid fa-circle-notch fa-spin text-sky-500 text-2xl'></i>"
                        }
                    });
                }
            }

            function openCustomModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('open');
                    document.body.style.overflow = 'hidden';
                    if (modalId === 'searchCustomerModal') {
                        initCustomerSearchTable();
                    }
                }
            }

            function closeCustomModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('open');
                    document.body.style.overflow = '';
                }
            }

            function selectCustomer(id, name) {
                $('#selected_customer_id').val(id);
                $('#selected_customer_name').val(name);
                fetch(`/maintenances/customer/${id}`).then(r => r.json()).then(d => {
                    $('#pppoe_val').text(d.pppoe || '-');
                    $('#alamat_val').text(d.alamat || '-');
                    $('#customer_preview').slideDown(200);
                });
                closeCustomModal('searchCustomerModal');
            }

            function openMaintenanceDetailModal(triggerEl) {
                const $btn = $(triggerEl);
                $('#det_nama').text($btn.data('nama'));
                $('#det_id_pel').text($btn.data('id-pel'));
                $('#det_pppoe').text($btn.data('pppoe'));
                $('#det_wa').text($btn.data('wa'));
                $('#det_alamat').text($btn.data('alamat'));
                $('#det_kendala').text($btn.data('kendala'));
                $('#det_tikor').text($btn.data('tikor'));
                const wa = String($btn.data('wa') || '');
                const waFormatted = wa.startsWith('0') ? '62' + wa.substring(1) : wa;
                $('#det_wa_link').attr('href', `https://wa.me/${waFormatted}`);
                const tikor = $btn.data('tikor');
                if (tikor && tikor !== 'N/A') {
                    $('#det_tikor_link').attr('href', `https://www.google.com/maps/search/?api=1&query=${tikor}`).show();
                } else {
                    $('#det_tikor_link').hide();
                }
                const foto = $btn.data('foto');
                if (foto && foto !== '') {
                    $('#det_foto').attr('src', foto);
                    $('#det_foto_container').removeClass('hidden');
                } else {
                    $('#det_foto_container').addClass('hidden');
                }
                openCustomModal('maintenanceDetailModal');
            }

            function openMaintenanceResultModal(triggerEl) {
                const $btn = $(triggerEl);
                $('#res_nama').text($btn.data('nama'));
                $('#res_eksekutor').text($btn.data('eksekutor'));
                $('#res_partner').text($btn.data('partner'));
                $('#res_waktu').text($btn.data('waktu'));
                $('#res_solusi').text($btn.data('solusi'));
                const kategori = $btn.data('kategori');
                $('#res_kategori').text(kategori || 'N/A');
                if (kategori === 'ONT') {
                    $('#res_ont_info').show();
                    $('#res_sn_lama').text($btn.data('sn-lama') || '-');
                    $('#res_sn_baru').text($btn.data('sn-baru') || '-');
                } else {
                    $('#res_ont_info').hide();
                }
                $('#res_before').attr('src', $btn.data('foto-before'));
                $('#res_after').attr('src', $btn.data('foto-after'));
                $('#res_speed').attr('src', $btn.data('foto-speed'));
                $('#res_selfie').attr('src', $btn.data('foto-selfie'));
                openCustomModal('maintenanceResultModal');
            }

            function openCompleteModal(triggerEl) {
                const $btn = $(triggerEl);
                const nama = $btn.data('nama');
                const sn = $btn.data('sn');
                $('#nama_p').text(nama);
                $('#m_nama_lengkap').val(nama);
                $('#m_sn_lama').val(sn || '-');
                $('#completeForm').attr('action', `/maintenances/${$btn.data('id')}/complete`);
                $('#completeForm')[0].reset();
                $('#ont_fields').hide();
                $('#m_sn_baru').removeAttr('required');
                $('#m_nama_lengkap').val(nama);
                $('#m_sn_lama').val(sn || '-');
                $('#completeModal .modal-tab').first().click();
                openCustomModal('completeModal');
            }

            function toggleOntFields() {
                const kategori = $('#m_kategori_kendala').val();
                if (kategori === 'ONT') {
                    $('#ont_fields').slideDown();
                    $('#m_sn_baru').attr('required', 'required');
                } else {
                    $('#ont_fields').slideUp();
                    $('#m_sn_baru').removeAttr('required');
                }
            }
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
                const adminTable = $('#admin-maintenance-table').length ? $('#admin-maintenance-table').DataTable({
                    ...dtOptions,
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('maintenances.index') }}?type=admin',
                    columns: [
                        { data: 'no', name: 'no', searchable: false, orderable: false, className: 'text-center align-middle', render: function(d,t,r,m){ return `<div style="color: #94a3b8; font-weight: 600; font-size: 12px;">${m.row + m.settings._iDisplayStart + 1}</div>`; } },
                        { data: 'pelanggan', name: 'customer.nama_lengkap', className: 'text-left align-middle' },
                        { data: 'status', name: 'status', className: 'text-center align-middle' },
                        { data: 'teknisi', name: 'teknisiEksekutor.nama_lengkap', className: 'text-left align-middle' },
                        { data: 'completed_at', name: 'completed_at', className: 'text-center align-middle' },
                        { data: 'opsi', name: 'opsi', searchable: false, orderable: false, className: 'text-center align-middle' }
                    ],
                    language: { ...dtOptions.language, processing: "<i class='fa-solid fa-circle-notch fa-spin text-sky-500 text-2xl'></i>" }
                }) : null;

                const pendingTable = $('#pending-tasks-table').length ? $('#pending-tasks-table').DataTable({
                    ...dtOptions,
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('maintenances.index') }}?type=pending',
                    columns: [
                        { data: 'no', name: 'no', searchable: false, orderable: false, className: 'text-center align-middle', render: function(d,t,r,m){ return `<div style="color: #94a3b8; font-weight: 600; font-size: 12px;">${m.row + m.settings._iDisplayStart + 1}</div>`; } },
                        { data: 'pelanggan', name: 'customer.nama_lengkap', className: 'text-left align-middle' },
                        { data: 'keterangan_kendala', name: 'keterangan_kendala', className: 'text-left align-middle' },
                        { data: 'opsi', name: 'opsi', searchable: false, orderable: false, className: 'text-center align-middle' }
                    ],
                    language: { ...dtOptions.language, processing: "<i class='fa-solid fa-circle-notch fa-spin text-sky-500 text-2xl'></i>" }
                }) : null;
                let searchTimer = null;
                $('.custom-search').on('keyup', function() {
                    clearTimeout(searchTimer);
                    const val = this.value;
                    searchTimer = setTimeout(function() {
                        if (adminTable) adminTable.search(val).draw();
                        if (pendingTable) pendingTable.search(val).draw();
                    }, 300);
                });

                let custSearchTimer = null;
                $('#customer-modal-search').on('keyup', function() {
                    clearTimeout(custSearchTimer);
                    const val = this.value;
                    custSearchTimer = setTimeout(function() {
                        if (customerSearchTable) customerSearchTable.search(val).draw();
                    }, 300);
                });
                $(document).on('click', '.modal-tab', function() {
                    const tabId = $(this).data('tab');
                    const $modalBox = $(this).closest('.modal-box');
                    $modalBox.find('.modal-tab').removeClass('active');
                    $(this).addClass('active');
                    $modalBox.find('.tab-panel').removeClass('active');
                    $('#' + tabId).addClass('active');
                });
                $('.modal-overlay').on('click', function(e) {
                    if (e.target === this) closeCustomModal(this.id);
                });
            });
        </script>
    @endsection
</x-layouts.app>
