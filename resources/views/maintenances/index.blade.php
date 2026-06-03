<x-layouts.app>
    @section('Css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            /* Hakatech DataTables Customization */
            .dataTables_wrapper { padding: 0; }
            .dataTables_filter { display: none; }
            .dataTables_paginate {
                display: flex;
                justify-content: flex-end;
                padding: 1.25rem 1.5rem;
                gap: 4px;
                border-top: 1px solid #f1f5f9;
            }
            .paginate_button {
                width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid #e2e8f0;
                background: #fff; color: #64748b !important; font-size: 13px; font-weight: 600;
                display: inline-flex; align-items: center; justify-content: center; cursor: pointer;
            }
            .paginate_button.current { background: #0ea5e9; border-color: #0ea5e9; color: #fff !important; }
            
            .table-toolbar {
                background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
                padding: 1rem 1.25rem; display: flex; align-items: center;
                justify-content: space-between; gap: 12px; flex-wrap: wrap;
                margin-bottom: 1rem; box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }
            .search-wrap { position: relative; flex: 1; min-width: 200px; max-width: 360px; }
            .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px; }
            .search-input {
                width: 100%; padding: 9px 12px 9px 36px; border: 2px solid #e2e8f0;
                border-radius: 10px; font-size: 13px; outline: none; background: #f8fafc;
            }
            #admin-maintenance-table thead th, #admin-maintenance-table tbody td,
            #pending-tasks-table thead th, #pending-tasks-table tbody td {
                text-transform: uppercase !important; font-weight: 600; font-size: 12px;
            }
        </style>
    @endsection

    @php $is_admin = Auth::user()->jabatan === 'Administrator'; @endphp

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Pemeliharaan (Maintenance)</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola tugas perbaikan dan pemeliharaan jaringan pelanggan.</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if ($is_admin)
                <button type="button" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                    onclick="openCustomModal('addMaintenanceModal')">
                    <i class="fa-solid fa-plus-square"></i> Tambah Maintenance
                </button>
            @endif
            <a href="{{ route('dashboards.index') }}" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;"><i class="fa-solid fa-screwdriver-wrench"></i></div>
            <div>
                <div class="stat-val">{{ $totalThisMonth }}</div>
                <div class="stat-lbl">Bulan Ini</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-calendar-week"></i></div>
            <div>
                <div class="stat-val">{{ $totalThisWeek }}</div>
                <div class="stat-lbl">Minggu Ini</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <div class="stat-val">{{ $totalCompletedThisMonth }}</div>
                <div class="stat-lbl">Selesai (Bulan Ini)</div>
            </div>
        </div>
    </div>

    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input custom-search" placeholder="Cari pelanggan atau kendala..." autocomplete="off" />
        </div>
    </div>

    @if ($is_admin)
        <div class="table-card">
            <div class="table-responsive">
                <table id="admin-maintenance-table" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">#</th>
                            <th style="text-align: center;">Pelanggan</th>
                            <th style="text-align: center; width: 120px;">Status</th>
                            <th style="text-align: center;">Teknisi</th>
                            <th style="text-align: center; width: 150px;">Waktu Selesai</th>
                            <th style="text-align: center; width: 120px;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allMaintenances as $m)
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                                <td style="text-align: left;">
                                    <div style="font-weight: 700; color: #0f172a;">{{ $m->customer->nama_lengkap ?? 'N/A' }}</div>
                                    <div style="font-size: 11px; color: #64748b;">ID: {{ $m->customer->id_pelanggan ?? '-' }}</div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge {{ $m->status === 'Selesai' ? 'badge-published' : 'badge-pending' }}" 
                                          style="{{ $m->status !== 'Selesai' ? 'background:#fef9c3;color:#854d0e;' : '' }}">
                                        {{ strtoupper($m->status) }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    @if ($m->teknisiEksekutor)
                                        <div style="font-weight: 600; color: #475569;">{{ $m->teknisiEksekutor->nama_lengkap }}</div>
                                        @if ($m->partner) <div style="font-size: 10px; color: #94a3b8;">Team: {{ $m->partner }}</div> @endif
                                    @else
                                        <span class="text-slate-400 italic">BELUM DIAMBIL</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">{{ $m->completed_at ? \Carbon\Carbon::parse($m->completed_at)->format('d-m-Y H:i') : '-' }}</td>
                                <td style="text-align: center;">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl" onclick="openMaintenanceDetailModal(this)"
                                            data-nama="{{ $m->customer->nama_lengkap ?? 'N/A' }}" data-id-pel="{{ $m->customer->id_pelanggan ?? '-' }}"
                                            data-pppoe="{{ $m->customer->pppoe_username ?? '-' }}" data-wa="{{ $m->customer->no_whatsapp ?? '-' }}"
                                            data-tikor="{{ $m->customer->preOrder->titik_koordinat ?? 'N/A' }}" data-alamat="{{ $m->customer->alamat_lengkap ?? '-' }}"
                                            data-kendala="{{ $m->keterangan_kendala }}" data-foto="{{ $m->foto_kendala_awal ? asset('storage/' . $m->foto_kendala_awal) : '' }}">
                                            <i class="fa-solid fa-circle-info text-primary"></i>
                                        </button>
                                        @if ($m->status === 'Selesai')
                                            <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl" onclick="openMaintenanceResultModal(this)"
                                                data-nama="{{ $m->customer->nama_lengkap ?? 'N/A' }}" data-eksekutor="{{ $m->teknisiEksekutor->nama_lengkap ?? 'N/A' }}"
                                                data-partner="{{ $m->partner ?: '-' }}" data-kategori="{{ $m->kategori_kendala }}"
                                                data-sn-baru="{{ $m->sn_baru }}" data-sn-lama="{{ $m->sn_lama }}" data-solusi="{{ $m->solusi_kendala }}"
                                                data-waktu="{{ \Carbon\Carbon::parse($m->completed_at)->format('d-m-Y H:i') }}"
                                                data-foto-before="{{ asset('storage/' . $m->foto_sebelum_perbaikan) }}" data-foto-after="{{ asset('storage/' . $m->foto_setelah_perbaikan) }}"
                                                data-foto-speed="{{ asset('storage/' . $m->foto_speedtest) }}" data-foto-selfie="{{ asset('storage/' . $m->foto_selfie_teknisi) }}">
                                                <i class="fa-solid fa-file-circle-check text-emerald-500"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl" onclick="confirmGlobalDelete('{{ route('maintenances.destroy', $m->id) }}', '{{ $m->customer->nama_lengkap ?? 'N/A' }}')">
                                            <i class="fa-solid fa-trash text-rose-600"></i>
                                        </button>
                                    </div>
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
                <table id="pending-tasks-table" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">#</th>
                            <th style="text-align: center;">Pelanggan</th>
                            <th style="text-align: center;">Kendala</th>
                            <th style="text-align: center; width: 150px;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingTasks as $m)
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                                <td style="text-align: left;">
                                    <div style="font-weight: 700; color: #0f172a;">{{ $m->customer->nama_lengkap }}</div>
                                    <div style="font-size: 11px; color: #64748b;">ID: {{ $m->customer->id_pelanggan }}</div>
                                </td>
                                <td style="text-align: center;">{{ Str::limit($m->keterangan_kendala, 40) }}</td>
                                <td style="text-align: center;">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl" onclick="openMaintenanceDetailModal(this)"
                                            data-nama="{{ $m->customer->nama_lengkap }}" data-id-pel="{{ $m->customer->id_pelanggan }}"
                                            data-pppoe="{{ $m->customer->pppoe_username }}" data-wa="{{ $m->customer->no_whatsapp }}"
                                            data-tikor="{{ $m->customer->preOrder->titik_koordinat ?? 'N/A' }}" data-alamat="{{ $m->customer->alamat_lengkap }}"
                                            data-kendala="{{ $m->keterangan_kendala }}" data-foto="{{ $m->foto_kendala_awal ? asset('storage/' . $m->foto_kendala_awal) : '' }}">
                                            <i class="fa-solid fa-circle-info text-primary"></i>
                                        </button>
                                        <button type="button" class="btn-primary-admin !h-9 !px-3 !text-[11px]" onclick="openCompleteModal(this)" 
                                            data-id="{{ $m->id }}" data-nama="{{ $m->customer->nama_lengkap }}" data-sn="{{ $m->customer->sn_perangkat }}">
                                            <i class="fa-solid fa-hand-holding-hand"></i> Ambil & Selesaikan
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

    {{-- Modal Area --}}
    <div class="modal-overlay" id="searchCustomerModal" style="z-index: 10001;">
        <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title"><i class="fa-solid fa-users text-primary"></i> <span class="text-slate-900 font-bold text-[17px]">Cari & Pilih Pelanggan</span></div>
                <button type="button" class="modal-close" onclick="closeCustomModal('searchCustomerModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body p-6">
                <div class="search-wrap mb-4 !max-w-full"><i class="fa-solid fa-magnifying-glass search-icon"></i><input type="search" class="search-input" id="customer-modal-search" placeholder="Ketik nama, ID pelanggan, atau alamat..." autocomplete="off" /></div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table id="customer-search-table" class="admin-table">
                        <thead><tr><th style="width: 50px;">#</th><th>Pelanggan</th><th>ID Pelanggan</th><th>Alamat</th><th style="width: 80px; text-align: center;">Opsi</th></tr></thead>
                        <tbody>
                            @foreach ($customers as $c)
                                <tr>
                                    <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                                    <td style="font-weight: 700; color: #0f172a;">{{ $c->nama_lengkap }}</td>
                                    <td style="font-weight: 600; color: #64748b;">{{ $c->id_pelanggan }}</td>
                                    <td style="font-size: 11px; color: #64748b;">{{ Str::limit($c->alamat_lengkap, 50) }}</td>
                                    <td style="text-align: center;"><button type="button" class="btn-primary-admin !py-1.5 !px-3 !text-[11px]" onclick="selectCustomer('{{ $c->id }}', '{{ $c->nama_lengkap }} ({{ $c->id_pelanggan }})')">Pilih</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl"><button type="button" class="btn-cancel w-full" onclick="closeCustomModal('searchCustomerModal')"><i class="fa-solid fa-xmark"></i> Batal</button></div>
        </div>
    </div>

    <div class="modal-overlay" id="addMaintenanceModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <form action="{{ route('maintenances.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                    <div class="modal-title"><i class="fa-solid fa-plus-square text-primary"></i> <span class="text-slate-900 font-bold text-[17px]">Buat Tugas Pemeliharaan</span></div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('addMaintenanceModal')"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body p-8 space-y-6 bg-white">
                    <div class="form-group">
                        <label class="form-label !text-slate-800 !font-bold"><i class="fa-solid fa-user-tag text-primary mr-1"></i> Pelanggan Terpilih <span class="text-rose-500">*</span></label>
                        <div class="flex gap-2">
                            <input type="hidden" name="customer_id" id="selected_customer_id" required>
                            <input type="text" id="selected_customer_name" class="form-input !bg-slate-50 cursor-not-allowed flex-1" placeholder="Belum ada pelanggan terpilih..." readonly required>
                            <button type="button" class="btn-primary-admin !shadow-none !px-4 !h-[42px]" onclick="openCustomModal('searchCustomerModal')"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </div>
                    <div id="customer_preview" class="hidden">
                        <div class="bg-sky-50 rounded-2xl border border-sky-100 p-5 space-y-2">
                            <p id="pppoe_val" class="text-sm font-bold text-sky-700 m-0"></p>
                            <p id="alamat_val" class="text-sm font-medium text-slate-600 m-0 leading-relaxed"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label !text-slate-800 !font-bold"><i class="fa-solid fa-camera text-slate-400 mr-1"></i> Foto Kendala (Opsional)</label>
                        <input type="file" name="foto_kendala_awal" class="form-input" accept="image/*">
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label !text-slate-800 !font-bold"><i class="fa-solid fa-comment-dots text-slate-400 mr-1"></i> Deskripsi Kendala <span class="text-rose-500">*</span></label>
                        <textarea name="keterangan_kendala" class="form-textarea" required rows="3" placeholder="Jelaskan detail kendala..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel" onclick="closeCustomModal('addMaintenanceModal')"><i class="fa-solid fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn-save !bg-indigo-600"><i class="fa-solid fa-paper-plane"></i> Kirim Tugas</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Maintenance Detail Modal -->
    <div class="modal-overlay" id="maintenanceDetailModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title"><i class="fa-solid fa-circle-info text-primary"></i> <span class="text-slate-900 font-bold">Detail Tugas Maintenance</span></div>
                <button class="modal-close" onclick="closeCustomModal('maintenanceDetailModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body p-8 bg-white space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div><span class="text-[10px] font-black text-slate-400 uppercase block mb-1">Pelanggan</span><p id="det_nama" class="text-lg font-black text-slate-900 m-0"></p></div>
                        <div><span class="text-[10px] font-black text-slate-400 uppercase block mb-1">ID Pelanggan</span><p id="det_id_pel" class="text-sm font-bold text-slate-600 m-0"></p></div>
                    </div>
                    <div class="space-y-4">
                        <div><span class="text-[10px] font-black text-slate-400 uppercase block mb-1">WhatsApp</span><div class="flex items-center gap-2"><p id="det_wa" class="text-sm font-bold text-emerald-600 m-0"></p><a id="det_wa_link" href="#" target="_blank" class="bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded text-[10px] font-black no-underline"><i class="fa-brands fa-whatsapp"></i> CHAT</a></div></div>
                        <div><span class="text-[10px] font-black text-slate-400 uppercase block mb-1">Titik Koordinat</span><a id="det_tikor_link" href="#" target="_blank" class="bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded text-[10px] font-black no-underline"><i class="fa-solid fa-location-crosshairs"></i> LIHAT MAPS</a></div>
                    </div>
                </div>
                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
                    <span class="text-[10px] font-black text-slate-400 uppercase block mb-2">Alamat & Kendala</span>
                    <p id="det_alamat" class="text-sm font-medium text-slate-700 leading-relaxed mb-3"></p>
                    <div class="bg-white border border-slate-200 p-4 rounded-xl font-bold text-slate-800 text-sm" id="det_kendala"></div>
                </div>
                <div id="det_foto_container" class="hidden">
                    <span class="text-[10px] font-black text-slate-400 uppercase block mb-2">Foto Kendala Awal</span>
                    <div class="aspect-video w-full rounded-2xl overflow-hidden border-2 border-slate-100 bg-slate-50 flex items-center justify-center"><img id="det_foto" src="" class="w-full h-full object-contain"></div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl"><button type="button" class="btn-cancel w-full" onclick="closeCustomModal('maintenanceDetailModal')"><i class="fa-solid fa-xmark"></i> Tutup Detail</button></div>
        </div>
    </div>

    <!-- Maintenance Result Modal -->
    <div class="modal-overlay" id="maintenanceResultModal">
        <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title"><i class="fa-solid fa-file-circle-check text-emerald-500"></i> <span class="text-slate-900 font-bold">Hasil Perbaikan Maintenance</span></div>
                <button class="modal-close" onclick="closeCustomModal('maintenanceResultModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body p-0 bg-white">
                <div class="modal-tabs bg-slate-50 border-b border-slate-200">
                    <button class="modal-tab active" data-tab="tab-res-data"><i class="fa-solid fa-id-card"></i> Data Teknis</button>
                    <button class="modal-tab" data-tab="tab-res-media"><i class="fa-solid fa-camera"></i> Dokumentasi Foto</button>
                </div>
                <div class="p-8">
                    <div class="tab-panel active" id="tab-res-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-5">
                                <div><span class="text-[11px] font-bold text-slate-400 uppercase block">Pelanggan</span><p id="res_nama" class="text-base font-black text-slate-900 m-0"></p></div>
                                <div><span class="text-[11px] font-bold text-slate-400 uppercase block">Teknisi Eksekutor</span><p id="res_eksekutor" class="text-sm font-bold text-slate-700 m-0"></p></div>
                                <div><span class="text-[11px] font-bold text-slate-400 uppercase block">Rekan Tim</span><p id="res_partner" class="text-sm font-medium text-slate-600 m-0"></p></div>
                            </div>
                            <div class="space-y-5">
                                <div><span class="text-[11px] font-bold text-slate-400 uppercase block">Waktu Selesai</span><p id="res_waktu" class="text-sm font-bold text-slate-700 m-0"></p></div>
                                <div><span class="text-[11px] font-bold text-slate-400 uppercase block">Kategori Kendala</span><span id="res_kategori" class="badge badge-published !text-[10px] mt-1"></span></div>
                                <div id="res_ont_info" style="display:none;"><span class="text-[11px] font-bold text-amber-500 uppercase block">Informasi ONT (Diganti)</span><p class="text-[11px] font-bold text-slate-600 m-0 mt-1">LAMA: <span id="res_sn_lama" class="font-mono"></span></p><p class="text-[11px] font-bold text-slate-600 m-0">BARU: <span id="res_sn_baru" class="font-mono text-emerald-600"></span></p></div>
                            </div>
                        </div>
                        <div class="mt-6"><span class="text-[11px] font-bold text-slate-400 uppercase block mb-2">Tindakan Solusi</span><div class="bg-emerald-50 border border-emerald-100 p-4 rounded-xl text-emerald-900 text-sm font-bold leading-relaxed" id="res_solusi"></div></div>
                    </div>
                    <div class="tab-panel" id="tab-res-media">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div><span class="text-[10px] font-bold text-slate-400 uppercase block mb-2">Sebelum</span><div class="aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center"><img id="res_before" src="" class="w-full h-full object-cover"></div></div>
                            <div><span class="text-[10px] font-bold text-slate-400 uppercase block mb-2">Sesudah</span><div class="aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center"><img id="res_after" src="" class="w-full h-full object-cover"></div></div>
                            <div><span class="text-[10px] font-bold text-slate-400 uppercase block mb-2">Speedtest</span><div class="aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center"><img id="res_speed" src="" class="w-full h-full object-cover"></div></div>
                            <div><span class="text-[10px] font-bold text-slate-400 uppercase block mb-2">Selfie Tim</span><div class="aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center"><img id="res_selfie" src="" class="w-full h-full object-cover"></div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl"><button type="button" class="btn-cancel w-full" onclick="closeCustomModal('maintenanceResultModal')"><i class="fa-solid fa-xmark"></i> Tutup Laporan</button></div>
        </div>
    </div>

    {{-- Modal Selesai (Teknisi) --}}
    <div class="modal-overlay" id="completeModal">
        <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title"><i class="fa-solid fa-circle-check text-emerald-500"></i> <span class="text-slate-900 font-bold">Laporan Penyelesaian: <span id="nama_p"></span></span></div>
                <button type="button" class="modal-close" onclick="closeCustomModal('completeModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-tabs bg-white border-b border-slate-200">
                <button class="modal-tab active" data-tab="tab-m-data"><i class="fa-solid fa-file-invoice"></i> Data Penugasan</button>
                <button class="modal-tab" data-tab="tab-m-solusi"><i class="fa-solid fa-gears"></i> Solusi Teknis</button>
                <button class="modal-tab" data-tab="tab-m-media"><i class="fa-solid fa-camera"></i> Dokumentasi</button>
            </div>
            <form id="completeForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body bg-white p-6">
                    <div class="tab-panel active" id="tab-m-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="bg-sky-50 p-5 rounded-2xl border border-sky-100"><span class="text-[10px] font-black text-sky-400 block uppercase tracking-wider mb-1">Status Laporan</span><p class="text-sm font-bold text-sky-700 m-0">Menunggu penyelesaian teknisi di lapangan.</p></div>
                                <div class="form-group"><label class="form-label text-slate-800 font-bold uppercase text-[11px]">Nama Pelanggan</label><input type="text" class="form-input bg-slate-50 border-slate-200" id="m_nama_lengkap" readonly></div>
                            </div>
                            <div class="space-y-4">
                                <div class="form-group"><label class="form-label text-slate-800 font-bold uppercase text-[11px]">Pilih Partner / Rekan Kerja</label><div class="partner-card-scroll"><div class="grid grid-cols-1 gap-1">@foreach ($teknisis as $t)@if ($t->id != auth()->id())<div class="partner-item"><input class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary" type="checkbox" name="partner_ids[]" value="{{ $t->id }}" id="m_partner_{{ $t->id }}"><label class="text-sm font-semibold text-slate-700 cursor-pointer w-full m-0" for="m_partner_{{ $t->id }}">{{ $t->nama_lengkap }}</label></div>@endif @endforeach</div></div><p class="text-[10px] text-slate-400 italic mt-1.5">*Opsional, bisa dikosongkan jika bekerja sendiri.</p></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-panel" id="tab-m-solusi">
                        <div class="space-y-4">
                            <div class="form-group"><label class="form-label text-slate-800 font-bold uppercase text-[11px]">Kategori Masalah <span class="text-rose-500">*</span></label><select name="kategori_kendala" id="m_kategori_kendala" class="form-input" required onchange="toggleOntFields()"><option value="">-- Pilih Kategori --</option><option value="ONT">PERGANTIAN ONT / MODEM</option><option value="Kabel Fiber Optic">PERBAIKAN KABEL FIBER OPTIC</option><option value="Lainnya">MASALAH LAINNYA</option></select></div>
                            <div id="ont_fields" style="display: none;" class="bg-amber-50 p-4 rounded-xl border border-amber-100 space-y-4"><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><div class="form-group"><label class="form-label text-amber-800 font-bold uppercase text-[10px]">SN ONT Sebelumnya</label><input type="text" id="m_sn_lama" class="form-input bg-amber-100/50 border-amber-200 text-amber-900 font-mono" readonly></div><div class="form-group"><label class="form-label text-amber-800 font-bold uppercase text-[10px]">SN ONT Pengganti <span class="text-rose-500">*</span></label><input type="text" name="sn_baru" id="m_sn_baru" class="form-input border-amber-300 font-mono" placeholder="Masukkan SN Baru..."></div></div></div>
                            <div class="form-group"><label class="form-label text-slate-800 font-bold uppercase text-[11px]">Solusi / Tindakan Perbaikan <span class="text-rose-500">*</span></label><textarea name="solusi_kendala" class="form-textarea" required rows="5" placeholder="Jelaskan secara detail tindakan..."></textarea></div>
                        </div>
                    </div>
                    <div class="tab-panel" id="tab-m-media">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="form-group"><label class="form-label text-slate-800 font-bold uppercase text-[11px]">Foto Sebelum <span class="text-rose-500">*</span></label><div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl p-3 text-center"><input type="file" name="foto_sebelum" class="form-input !text-[10px]" required accept="image/*"></div></div>
                            <div class="form-group"><label class="form-label text-slate-800 font-bold uppercase text-[11px]">Foto Sesudah <span class="text-rose-500">*</span></label><div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl p-3 text-center"><input type="file" name="foto_sesudah" class="form-input !text-[10px]" required accept="image/*"></div></div>
                            <div class="form-group"><label class="form-label text-slate-800 font-bold uppercase text-[11px]">Speedtest <span class="text-rose-500">*</span></label><div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl p-3 text-center"><input type="file" name="foto_speedtest" class="form-input !text-[10px]" required accept="image/*"></div></div>
                            <div class="form-group"><label class="form-label text-slate-800 font-bold uppercase text-[11px]">Selfie Tim <span class="text-rose-500">*</span></label><div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl p-3 text-center"><input type="file" name="foto_selfie" class="form-input !text-[10px]" required accept="image/*"></div></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl"><button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" onclick="closeCustomModal('completeModal')"><i class="fa-solid fa-xmark"></i> Batal</button><button type="submit" class="btn-save !flex !items-center !justify-center gap-2 !bg-emerald-600"><i class="fa-solid fa-cloud-arrow-up"></i> Kirim Laporan Selesai</button></div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function openCustomModal(modalId) { const modal = document.getElementById(modalId); if (modal) { modal.classList.add('open'); document.body.style.overflow = 'hidden'; } }
            function closeCustomModal(modalId) { const modal = document.getElementById(modalId); if (modal) { modal.classList.remove('open'); document.body.style.overflow = ''; } }
            function selectCustomer(id, name) { $('#selected_customer_id').val(id); $('#selected_customer_name').val(name); fetch(`/maintenances/customer/${id}`).then(r => r.json()).then(d => { $('#pppoe_val').text(d.pppoe || '-'); $('#alamat_val').text(d.alamat || '-'); $('#customer_preview').slideDown(200); }); closeCustomModal('searchCustomerModal'); }
            function openMaintenanceDetailModal(triggerEl) { const $btn = $(triggerEl); $('#det_nama').text($btn.data('nama')); $('#det_id_pel').text($btn.data('id-pel')); $('#det_pppoe').text($btn.data('pppoe')); $('#det_wa').text($btn.data('wa')); $('#det_alamat').text($btn.data('alamat')); $('#det_kendala').text($btn.data('kendala')); $('#det_tikor').text($btn.data('tikor')); const wa = String($btn.data('wa') || ''); const waFormatted = wa.startsWith('0') ? '62' + wa.substring(1) : wa; $('#det_wa_link').attr('href', `https://wa.me/${waFormatted}`); const tikor = $btn.data('tikor'); if (tikor && tikor !== 'N/A') { $('#det_tikor_link').attr('href', `https://www.google.com/maps/search/?api=1&query=${tikor}`).show(); } else { $('#det_tikor_link').hide(); } const foto = $btn.data('foto'); if (foto && foto !== '') { $('#det_foto').attr('src', foto); $('#det_foto_container').removeClass('hidden'); } else { $('#det_foto_container').addClass('hidden'); } openCustomModal('maintenanceDetailModal'); }
            function openMaintenanceResultModal(triggerEl) { const $btn = $(triggerEl); $('#res_nama').text($btn.data('nama')); $('#res_eksekutor').text($btn.data('eksekutor')); $('#res_partner').text($btn.data('partner')); $('#res_waktu').text($btn.data('waktu')); $('#res_solusi').text($btn.data('solusi')); const kategori = $btn.data('kategori'); $('#res_kategori').text(kategori || 'N/A'); if (kategori === 'ONT') { $('#res_ont_info').show(); $('#res_sn_lama').text($btn.data('sn-lama') || '-'); $('#res_sn_baru').text($btn.data('sn-baru') || '-'); } else { $('#res_ont_info').hide(); } $('#res_before').attr('src', $btn.data('foto-before')); $('#res_after').attr('src', $btn.data('foto-after')); $('#res_speed').attr('src', $btn.data('foto-speed')); $('#res_selfie').attr('src', $btn.data('foto-selfie')); openCustomModal('maintenanceResultModal'); }
            function openCompleteModal(triggerEl) { const $btn = $(triggerEl); const nama = $btn.data('nama'); const sn = $btn.data('sn'); $('#nama_p').text(nama); $('#m_nama_lengkap').val(nama); $('#m_sn_lama').val(sn || '-'); $('#completeForm').attr('action', `/maintenances/${$btn.data('id')}/complete`); $('#completeForm')[0].reset(); $('#ont_fields').hide(); $('#m_sn_baru').removeAttr('required'); $('#m_nama_lengkap').val(nama); $('#m_sn_lama').val(sn || '-'); $('#completeModal .modal-tab').first().click(); openCustomModal('completeModal'); }
            function toggleOntFields() { const kategori = $('#m_kategori_kendala').val(); if (kategori === 'ONT') { $('#ont_fields').slideDown(); $('#m_sn_baru').attr('required', 'required'); } else { $('#ont_fields').slideUp(); $('#m_sn_baru').removeAttr('required'); } }
            $(document).ready(function() {
                const dtOptions = { "pageLength": 10, "lengthChange": false, "ordering": false, "info": false, "responsive": true, "dom": 'tpir', "language": { "zeroRecords": "Data tidak ditemukan" } };
                const adminTable = $('#admin-maintenance-table').DataTable(dtOptions);
                const pendingTable = $('#pending-tasks-table').DataTable(dtOptions);
                const customerSearchTable = $('#customer-search-table').DataTable({ ...dtOptions, "pageLength": 5 });
                $('.custom-search').on('keyup', function() { const val = this.value; if (adminTable) adminTable.search(val).draw(); if (pendingTable) pendingTable.search(val).draw(); });
                $('#customer-modal-search').on('keyup', function() { customerSearchTable.search(this.value).draw(); });
                $(document).on('click', '.modal-tab', function() { const tabId = $(this).data('tab'); const $modalBox = $(this).closest('.modal-box'); $modalBox.find('.modal-tab').removeClass('active'); $(this).addClass('active'); $modalBox.find('.tab-panel').removeClass('active'); $('#' + tabId).addClass('active'); });
                $('.modal-overlay').on('click', function(e) { if (e.target === this) closeCustomModal(this.id); });
            });
        </script>
    @endsection
</x-layouts.app>
