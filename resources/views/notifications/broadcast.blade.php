<x-layouts.app title="Broadcast & Pemberitahuan Pelanggan">
    @section('Css')
        <style>
            #broadcast-table thead th {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 12px;
            }

            #broadcast-table tbody td {
                font-size: 12px;
            }

            .badge-type {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 2.5px 8px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 600;
                letter-spacing: 0.01em;
            }

            .badge-maintenance {
                background: #fff7ed;
                color: #c2410c;
                border: 1px solid #ffedd5;
            }

            .badge-announcement {
                background: #f0f9ff;
                color: #0369a1;
                border: 1px solid #e0f2fe;
            }

            .badge-promo {
                background: #faf5ff;
                color: #7e22ce;
                border: 1px solid #f3e8ff;
            }

            .badge-billing {
                background: #ecfdf5;
                color: #047857;
                border: 1px solid #d1fae5;
            }

            .badge-general {
                background: #f8fafc;
                color: #475569;
                border: 1px solid #e2e8f0;
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
                text-decoration: none !important;
            }

            .paginate_button:hover:not(.disabled) {
                background: #f8fafc;
                border-color: #cbd5e1;
                color: #0f172a !important;
            }

            .paginate_button.current {
                background: #0ea5e9 !important;
                border-color: #0ea5e9 !important;
                color: #fff !important;
            }

            .paginate_button.disabled {
                opacity: 0.4;
                cursor: not-allowed;
            }

            .mobile-preview-box {
                background: #ffffff;
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
                border: 1px solid #e2e8f0;
                padding: 10px 12px;
                position: relative;
            }
        </style>
    @endsection

    <!-- Header Section -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Broadcast & Pemberitahuan Pelanggan
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">
                Kirim notifikasi push ke aplikasi mobile & in-app untuk pemeliharaan jaringan, pengumuman, promo, dan tagihan.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="reloadTable()" class="btn-cancel !h-8.5 !px-3.5 text-xs font-semibold flex items-center gap-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-xs">
                <i class="fa-solid fa-arrows-rotate text-slate-500" id="refreshIcon"></i> Refresh
            </button>
            <button type="button" onclick="openCustomModal('createBroadcastModal')" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !h-8.5 !px-3.5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                <i class="fa-solid fa-paper-plane"></i> Tambah Pemberitahuan
            </button>
        </div>
    </div>

    <!-- Stats Cards Grid (4 Kartu Kompak) -->
    <div class="stats-grid mb-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0e7ff;color:#4338ca;">
                <i class="fa-solid fa-bullhorn"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($stats['total_broadcasts'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Total Broadcast Dikirim</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-val text-emerald-600">{{ number_format($stats['total_sent'], 0, ',', '.') }}</div>
                <div class="stat-lbl">FCM Push Terkirim</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#b45309;">
                <i class="fa-solid fa-mobile-screen-button"></i>
            </div>
            <div>
                <div class="stat-val text-amber-700">{{ number_format($stats['fcm_registered'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Pelanggan Mobile (FCM)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="stat-val text-slate-700">{{ number_format($stats['total_customers'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Total Pelanggan Terdaftar</div>
            </div>
        </div>
    </div>

    <!-- Table Toolbar & Filters (Seragam) -->
    <div class="table-toolbar">
        <div class="filter-group">
            <div style="flex: 1; min-width: 170px;">
                <label class="form-label-custom">Kategori Pemberitahuan</label>
                <select id="filter-type" class="form-input-custom !h-8.5 !text-xs">
                    <option value="">Semua Kategori</option>
                    <option value="maintenance">Pemeliharaan (Maintenance)</option>
                    <option value="announcement">Pengumuman Resmi</option>
                    <option value="promo">Promo & Penawaran</option>
                    <option value="billing">Tagihan / Billing</option>
                    <option value="general">Informasi Umum</option>
                </select>
            </div>

            <div style="flex: 1; min-width: 170px;">
                <label class="form-label-custom">Target Penerima</label>
                <select id="filter-target" class="form-input-custom !h-8.5 !text-xs">
                    <option value="">Semua Target</option>
                    <option value="all">Semua Pelanggan</option>
                    <option value="package">Berdasarkan Paket</option>
                    <option value="customer">Pelanggan Tertentu</option>
                </select>
            </div>

            <div style="flex: 2; min-width: 230px;">
                <label class="form-label-custom">Pencarian</label>
                <div class="search-wrap !max-w-full">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="search" id="custom-search" class="search-input custom-search !h-8.5 !text-xs"
                        placeholder="Cari judul, isi pengumuman, atau pesan..." autocomplete="off" />
                </div>
            </div>

            <button type="button" onclick="resetFilters()" class="btn-cancel !h-8.5 !px-3.5 text-xs font-semibold flex items-center gap-1 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 shadow-xs" title="Reset Filter">
                <i class="fa-solid fa-arrow-rotate-left"></i> Reset
            </button>
        </div>
    </div>

    <!-- Table Card (DataTables Server-Side) -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="broadcast-table" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 45px;">#</th>
                        <th style="width: 135px;">Waktu Kirim</th>
                        <th style="width: 140px;">Kategori</th>
                        <th>Judul & Isi Pesan</th>
                        <th style="width: 170px;">Target Pelanggan</th>
                        <th style="text-align: center; width: 130px;">Status Push FCM</th>
                        <th style="width: 120px;">Dibuat Oleh</th>
                        <th style="text-align: center; width: 85px;">Opsi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: Tambah Pemberitahuan Baru (Seragam modal-overlay) -->
    <div class="modal-overlay" id="createBroadcastModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-200 shrink-0">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Kirim Pemberitahuan & Broadcast Baru</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Kirim push notifikasi real-time & simpan di feed mobile pelanggan.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('createBroadcastModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('broadcasts.store') }}" method="POST" id="formCreateBroadcast" onsubmit="return confirmSubmitBroadcast()">
                @csrf
                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <!-- Template Cepat -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold text-slate-700 flex items-center gap-1.5 uppercase tracking-wider">
                                <i class="fa-solid fa-bolt text-amber-500 text-[11px]"></i> Template Cepat (Isi Otomatis):
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" onclick="applyTemplate('maintenance')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white border border-slate-200 hover:border-amber-300 hover:bg-amber-50 text-slate-700 transition shadow-2xs">
                                <i class="fa-solid fa-screwdriver-wrench text-amber-600 mr-1"></i> Pemeliharaan Jaringan
                            </button>
                            <button type="button" onclick="applyTemplate('gangguan')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white border border-slate-200 hover:border-rose-300 hover:bg-rose-50 text-slate-700 transition shadow-2xs">
                                <i class="fa-solid fa-triangle-exclamation text-rose-600 mr-1"></i> Info Gangguan
                            </button>
                            <button type="button" onclick="applyTemplate('tagihan')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50 text-slate-700 transition shadow-2xs">
                                <i class="fa-solid fa-file-invoice-dollar text-emerald-600 mr-1"></i> Pengingat Tagihan
                            </button>
                            <button type="button" onclick="applyTemplate('promo')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white border border-slate-200 hover:border-purple-300 hover:bg-purple-50 text-slate-700 transition shadow-2xs">
                                <i class="fa-solid fa-gift text-purple-600 mr-1"></i> Promo Upgrade
                            </button>
                        </div>
                    </div>

                    <!-- Kategori & Target -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-semibold text-xs">Kategori Pemberitahuan <span class="text-rose-500">*</span></label>
                            <select name="type" id="input_type" class="form-input-custom !h-8.5 !text-xs w-full" required onchange="updatePreview()">
                                <option value="maintenance">Pemeliharaan (Maintenance)</option>
                                <option value="announcement">Pengumuman Resmi</option>
                                <option value="promo">Promo & Penawaran</option>
                                <option value="billing">Tagihan / Billing</option>
                                <option value="general">Informasi Umum</option>
                            </select>
                        </div>

                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-semibold text-xs">Target Penerima <span class="text-rose-500">*</span></label>
                            <select name="target_type" id="input_target_type" class="form-input-custom !h-8.5 !text-xs w-full" required onchange="toggleTargetDropdown()">
                                <option value="all">Semua Pelanggan (Broadcast)</option>
                                <option value="package">Berdasarkan Paket Layanan Tertentu</option>
                                <option value="customer">1 Pelanggan Tertentu</option>
                            </select>
                        </div>
                    </div>

                    <!-- Pilihan Target Dinamis -->
                    <div id="target_package_wrap" class="hidden">
                        <label class="form-label text-slate-700 font-semibold text-xs">Pilih Paket Layanan <span class="text-rose-500">*</span></label>
                        <select name="target_id" id="target_package_id" class="form-input-custom !h-8.5 !text-xs w-full" disabled>
                            <option value="">-- Pilih Paket Target --</option>
                            @foreach ($pakets as $p)
                                <option value="{{ $p->id }}">{{ $p->nama_paket }} ({{ $p->rate_limit ?? '-' }}) - Rp {{ number_format($p->harga, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="target_customer_wrap" class="hidden">
                        <label class="form-label text-slate-700 font-semibold text-xs">Pilih Pelanggan <span class="text-rose-500">*</span></label>
                        <select name="target_id" id="target_customer_id" class="form-input-custom !h-8.5 !text-xs w-full" disabled>
                            <option value="">-- Pilih Pelanggan Target --</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">
                                    {{ $c->id_pelanggan }} - {{ $c->nama_lengkap }} {{ !empty($c->fcm_token) ? ' [FCM Terhubung]' : ' [Belum Login Mobile]' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Judul Notifikasi -->
                    <div class="form-group !mb-0">
                        <label class="form-label text-slate-700 font-semibold text-xs">Judul Pemberitahuan <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" id="input_title" class="form-input !h-8.5 !text-xs" required
                            maxlength="150" placeholder="Contoh: Pemeliharaan Jaringan Terjadwal Area Timur" oninput="updatePreview()">
                    </div>

                    <!-- Isi Pesan -->
                    <div class="form-group !mb-0">
                        <div class="flex items-center justify-between mb-1">
                            <label class="form-label text-slate-700 font-semibold text-xs !mb-0">Isi Pesan Notifikasi <span class="text-rose-500">*</span></label>
                            <span class="text-[10.5px] text-slate-400" id="char_counter">0 / 2000 karakter</span>
                        </div>
                        <textarea name="body" id="input_body" rows="4" class="form-input !text-xs leading-relaxed" required
                            maxlength="2000" placeholder="Tuliskan detail pesan pengumuman secara jelas..." oninput="updatePreview()"></textarea>
                    </div>

                    <!-- Live Mobile Preview Card -->
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 block mb-1.5 uppercase tracking-wider">
                            <i class="fa-solid fa-mobile-screen mr-1 text-slate-400"></i> Preview Notifikasi di HP Pelanggan:
                        </span>
                        <div class="mobile-preview-box">
                            <div class="flex items-center justify-between text-[10.5px] text-slate-400 mb-1.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-4 h-4 rounded bg-sky-500 text-white flex items-center justify-center font-bold text-[9px]">E</span>
                                    <span class="font-bold text-slate-700">Erlangga Net</span>
                                    <span>•</span>
                                    <span>Baru saja</span>
                                </div>
                                <span class="text-[10px] font-semibold text-amber-600" id="preview_type_label">Pemeliharaan</span>
                            </div>
                            <div class="text-xs font-bold text-slate-800" id="preview_title">Judul Pemberitahuan</div>
                            <div class="text-[11px] text-slate-600 mt-1 leading-snug whitespace-pre-wrap" id="preview_body">Detail pesan pemberitahuan akan tampil di sini saat Anda mengetik...</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3.5 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeCustomModal('createBroadcastModal')" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitBroadcast" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !h-8.5 !px-4.5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Broadcast
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Detail Riwayat Pemberitahuan (Seragam modal-overlay) -->
    <div class="modal-overlay" id="detailBroadcastModal">
        <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-200 shrink-0">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Detail Riwayat Pemberitahuan</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Informasi konten dan statistik pengiriman notifikasi.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('detailBroadcastModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kategori & Target</div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span id="detail_type_badge" class="badge-type"></span>
                        <span id="detail_target" class="text-xs font-bold text-slate-700"></span>
                    </div>
                </div>

                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Judul</span>
                    <div id="detail_title" class="text-sm font-bold text-slate-900 leading-snug"></div>
                </div>

                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Isi Pesan Notifikasi</span>
                    <div id="detail_body" class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 leading-relaxed whitespace-pre-wrap font-sans"></div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100 text-xs">
                    <div>
                        <span class="text-[11px] text-slate-400 block mb-0.5">Waktu Pengiriman</span>
                        <span id="detail_time" class="font-semibold text-slate-800 font-mono text-[11.5px]"></span>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400 block mb-0.5">Status Pengiriman Push FCM</span>
                        <span id="detail_stats" class="font-bold text-emerald-600"></span>
                    </div>
                </div>

                <div class="pt-2 text-xs text-slate-500">
                    <span>Pengirim: </span><strong id="detail_creator" class="text-slate-800"></strong>
                </div>
            </div>

            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3.5 flex justify-end">
                <button type="button" onclick="closeCustomModal('detailBroadcastModal')" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL: Konfirmasi Hapus (Seragam modal-overlay) -->
    <div class="modal-overlay" id="deleteBroadcastModal">
        <div class="modal-box max-w-sm bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="p-6 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xl mx-auto">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 m-0">Hapus Riwayat Pemberitahuan?</h3>
                <p class="text-xs text-slate-500 m-0">Log pengiriman ini akan dihapus permanen dari sistem. Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-center gap-2">
                <button type="button" onclick="closeCustomModal('deleteBroadcastModal')" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold">
                    Batal
                </button>
                <button type="button" id="btnConfirmDelete" onclick="executeDeleteBroadcast()" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !h-8.5 !px-4.5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-trash-can"></i> Hapus
                </button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            let broadcastTable;
            let pendingDeleteId = null;

            $(document).ready(function() {
                broadcastTable = $('#broadcast-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    lengthChange: false,
                    ordering: false,
                    info: false,
                    responsive: true,
                    dom: 'tpir',
                    ajax: {
                        url: "{{ route('broadcasts.index') }}",
                        data: function(d) {
                            d.type = $('#filter-type').val();
                            d.target_type = $('#filter-target').val();
                        }
                    },
                    columns: [
                        {
                            data: 'no',
                            name: 'no',
                            orderable: false,
                            searchable: false,
                            className: 'text-center font-mono text-xs text-slate-400',
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: 'waktu',
                            name: 'created_at',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'kategori',
                            name: 'type',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'pesan',
                            name: 'title',
                            orderable: false,
                            searchable: true
                        },
                        {
                            data: 'target',
                            name: 'target_type',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'status_fcm',
                            name: 'total_sent',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'creator',
                            name: 'created_by',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'opsi',
                            name: 'opsi',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        }
                    ],
                    language: {
                        processing: "<i class='fa-solid fa-circle-notch fa-spin text-sky-500 text-2xl'></i>",
                        zeroRecords: "<div class='py-8 text-center text-slate-400'><i class='fa-solid fa-bullhorn text-3xl mb-2 text-slate-300 block'></i>Belum ada data pemberitahuan</div>",
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
                        broadcastTable.search(val).draw();
                    }, 350);
                });

                // Filter on select change
                $('#filter-type, #filter-target').on('change', function() {
                    broadcastTable.draw();
                });

                // Close modal on backdrop click
                $(document).on('click', '.modal-overlay', function(e) {
                    if (e.target === this) {
                        closeCustomModal(this.id);
                    }
                });
            });

            function reloadTable() {
                const icon = document.getElementById('refreshIcon');
                if (icon) icon.classList.add('fa-spin');
                if (broadcastTable) {
                    broadcastTable.ajax.reload(function() {
                        if (icon) icon.classList.remove('fa-spin');
                    }, false);
                }
            }

            function resetFilters() {
                $('#filter-type').val('');
                $('#filter-target').val('');
                $('#custom-search').val('');
                if (broadcastTable) {
                    broadcastTable.search('').draw();
                }
            }

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

            function toggleTargetDropdown() {
                const targetType = document.getElementById('input_target_type').value;
                const pkgWrap = document.getElementById('target_package_wrap');
                const pkgSelect = document.getElementById('target_package_id');
                const custWrap = document.getElementById('target_customer_wrap');
                const custSelect = document.getElementById('target_customer_id');

                if (targetType === 'package') {
                    pkgWrap.classList.remove('hidden');
                    pkgSelect.removeAttribute('disabled');
                    custWrap.classList.add('hidden');
                    custSelect.setAttribute('disabled', 'disabled');
                } else if (targetType === 'customer') {
                    custWrap.classList.remove('hidden');
                    custSelect.removeAttribute('disabled');
                    pkgWrap.classList.add('hidden');
                    pkgSelect.setAttribute('disabled', 'disabled');
                } else {
                    pkgWrap.classList.add('hidden');
                    pkgSelect.setAttribute('disabled', 'disabled');
                    custWrap.classList.add('hidden');
                    custSelect.setAttribute('disabled', 'disabled');
                }
            }

            function updatePreview() {
                const title = document.getElementById('input_title').value.trim() || 'Judul Pemberitahuan';
                const body = document.getElementById('input_body').value.trim() || 'Detail pesan pemberitahuan akan tampil di sini saat Anda mengetik...';
                const typeSelect = document.getElementById('input_type');
                const typeText = typeSelect.options[typeSelect.selectedIndex].text;

                document.getElementById('preview_title').textContent = title;
                document.getElementById('preview_body').textContent = body;
                document.getElementById('preview_type_label').textContent = typeText;

                const bodyLen = document.getElementById('input_body').value.length;
                document.getElementById('char_counter').textContent = `${bodyLen} / 2000 karakter`;
            }

            function applyTemplate(templateKey) {
                const typeSelect = document.getElementById('input_type');
                const titleInput = document.getElementById('input_title');
                const bodyInput = document.getElementById('input_body');

                if (templateKey === 'maintenance') {
                    typeSelect.value = 'maintenance';
                    titleInput.value = 'Pemberitahuan Pemeliharaan Jaringan';
                    bodyInput.value = 'Diberitahukan kepada seluruh pelanggan Erlangga Net, akan dilakukan pemeliharaan jaringan terjadwal pada dini hari nanti pukul 01:00 - 04:00 WIB untuk meningkatkan kehandalan koneksi. Mohon maaf atas ketidaknyamanannya.';
                } else if (templateKey === 'gangguan') {
                    typeSelect.value = 'announcement';
                    titleInput.value = 'Informasi Gangguan Jaringan Sementara';
                    bodyInput.value = 'Saat ini terjadi kendala teknis pada jalur fiber optik utama yang menyebabkan gangguan koneksi di beberapa area. Tim teknisi kami sudah berada di lokasi untuk penanganan cepat. Layanan akan normal kembali sesegera mungkin.';
                } else if (templateKey === 'tagihan') {
                    typeSelect.value = 'billing';
                    titleInput.value = 'Pengingat Pembayaran Tagihan Internet';
                    bodyInput.value = 'Tagihan internet bulanan Anda telah terbit. Segera lakukan pembayaran melalui aplikasi atau transfer sebelum batas tanggal jatuh tempo agar kenyamanan akses internet Anda tetap terjaga tanpa terputus.';
                } else if (templateKey === 'promo') {
                    typeSelect.value = 'promo';
                    titleInput.value = 'Promo Spesial Upgrade Kecepatan Internet!';
                    bodyInput.value = 'Nikmati penawaran spesial upgrade paket internet dengan kecepatan hingga 2x lipat tanpa biaya instalasi ulang. Hubungi layanan CS atau cek detail paket sekarang juga!';
                }

                updatePreview();
            }

            function confirmSubmitBroadcast() {
                const targetText = document.getElementById('input_target_type').options[document.getElementById('input_target_type').selectedIndex].text;
                return confirm(`Apakah Anda yakin ingin mengirim pemberitahuan ini ke ${targetText}? Notifikasi akan langsung terkirim ke perangkat HP pelanggan.`);
            }

            function showDetailModal(item) {
                document.getElementById('detail_title').textContent = item.title;
                document.getElementById('detail_body').textContent = item.body;
                
                const badgeClass = item.type === 'maintenance' ? 'badge-maintenance' :
                                   item.type === 'announcement' ? 'badge-announcement' :
                                   item.type === 'promo' ? 'badge-promo' :
                                   item.type === 'billing' ? 'badge-billing' : 'badge-general';
                const badgeEl = document.getElementById('detail_type_badge');
                badgeEl.className = 'badge-type ' + badgeClass;
                badgeEl.textContent = item.type_label || item.type;

                document.getElementById('detail_target').textContent = item.target_label || 'Semua Pelanggan';
                document.getElementById('detail_time').textContent = item.created_at;
                document.getElementById('detail_stats').textContent = `${item.total_sent} Terkirim (${item.total_targeted} Target)`;
                document.getElementById('detail_creator').textContent = item.creator || 'Sistem';

                openCustomModal('detailBroadcastModal');
            }

            function confirmDeleteBroadcast(id) {
                pendingDeleteId = id;
                openCustomModal('deleteBroadcastModal');
            }

            function executeDeleteBroadcast() {
                if (!pendingDeleteId) return;

                const btn = document.getElementById('btnConfirmDelete');
                const origHtml = btn.innerHTML;
                btn.innerHTML = "<i class='fa-solid fa-circle-notch fa-spin'></i> Menghapus...";
                btn.disabled = true;

                fetch(`/broadcasts/${pendingDeleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    btn.innerHTML = origHtml;
                    btn.disabled = false;
                    closeCustomModal('deleteBroadcastModal');

                    if (data.success) {
                        showToast(data.message, 'success');
                        reloadTable();
                    } else {
                        showToast(data.message || 'Gagal menghapus riwayat pemberitahuan.', 'error');
                    }
                })
                .catch(err => {
                    btn.innerHTML = origHtml;
                    btn.disabled = false;
                    closeCustomModal('deleteBroadcastModal');
                    showToast('Terjadi kesalahan jaringan.', 'error');
                });
            }
        </script>
    @endsection
</x-layouts.app>
