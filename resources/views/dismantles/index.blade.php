<x-layouts.app>
    @section('Css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
            #admin-dismantle-table thead th, #admin-dismantle-table tbody td,
            #pending-tasks-table thead th, #pending-tasks-table tbody td {
                text-transform: uppercase !important; font-weight: 600; font-size: 12px;
            }
        </style>
    @endsection

    @php $is_admin = Auth::user()->jabatan === 'Administrator'; @endphp

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Pencabutan (Dismantle)</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola pencabutan perangkat dan non-aktivasi pelanggan.</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if ($is_admin)
                <button type="button" class="btn-primary-admin !flex !items-center !justify-center gap-2" onclick="openCustomModal('addDismantleModal')">
                    <i class="fa-solid fa-plus-square"></i> Tambah Dismantle
                </button>
            @endif
            <a href="{{ route('dashboards.index') }}" class="btn-primary-admin !flex !items-center !justify-center gap-2" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;"><i class="fa-solid fa-house-circle-xmark"></i></div>
            <div><div class="stat-val">{{ $totalThisMonth }}</div><div class="stat-lbl">Bulan Ini</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-calendar-week"></i></div>
            <div><div class="stat-val">{{ $totalThisWeek }}</div><div class="stat-lbl">Minggu Ini</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;"><i class="fa-solid fa-circle-check"></i></div>
            <div><div class="stat-val">{{ $totalCompletedThisMonth }}</div><div class="stat-lbl">Selesai (Bulan Ini)</div></div>
        </div>
    </div>

    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input custom-search" placeholder="Cari pelanggan atau keterangan..." autocomplete="off" />
        </div>
    </div>

    @if ($is_admin)
        <div class="table-card">
            <div class="table-responsive">
                <table id="admin-dismantle-table" class="admin-table">
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
                        @foreach ($allDismantles as $d)
                            @php $isTrashed = $d->customer && $d->customer->trashed(); @endphp
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                                <td style="text-align: left;">
                                    <div style="font-weight: 700; color: #0f172a;">{{ $d->customer->nama_lengkap ?? 'N/A' }} @if ($isTrashed) <span class="badge badge-archived" style="font-size: 9px; margin-left: 4px;">NON-AKTIF</span> @endif</div>
                                    <div style="font-size: 11px; color: #64748b;">ID: {{ $d->customer->id_pelanggan ?? '-' }}</div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge {{ $d->status === 'Selesai' ? 'badge-published' : 'badge-pending' }}" style="{{ $d->status !== 'Selesai' ? 'background:#fef9c3;color:#854d0e;' : '' }}">
                                        {{ strtoupper($d->status) }}
                                    </span>
                                </td>
                                <td style="text-align: center;">{{ $d->teknisiEksekutor->nama_lengkap ?? '-' }}</td>
                                <td style="text-align: center;">{{ $d->completed_at ? $d->completed_at->format('d-m-Y H:i') : '-' }}</td>
                                <td style="text-align: center;">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl" onclick="openDismantleDetailModal(this)"
                                            data-nama="{{ $d->customer->nama_lengkap ?? 'N/A' }}" data-id-pel="{{ $d->customer->id_pelanggan ?? '-' }}"
                                            data-pppoe="{{ $d->customer->pppoe_username ?? '-' }}" data-wa="{{ $d->customer->no_whatsapp ?? '-' }}"
                                            data-tikor="{{ $d->customer->preOrder->titik_koordinat ?? 'N/A' }}" data-alamat="{{ $d->customer->alamat_lengkap ?? '-' }}"
                                            data-keterangan="{{ $d->keterangan_admin ?: '-' }}" data-foto="{{ $d->foto_pencabutan ? asset('storage/' . $d->foto_pencabutan) : '' }}">
                                            <i class="fa-solid fa-circle-info text-primary"></i>
                                        </button>
                                        @if ($d->status === 'Selesai')
                                            @if (!$isTrashed)
                                                <button type="button" class="btn-primary-admin !h-9 !px-3 !text-[11px]" onclick="confirmGlobalDelete('{{ route('dismantles.deleteCustomer', $d->customer_id) }}', '{{ $d->customer->nama_lengkap }}')">
                                                    <i class="fa-solid fa-user-slash"></i> Hapus
                                                </button>
                                            @endif
                                        @else
                                            <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl" onclick="confirmGlobalDelete('{{ route('dismantles.destroy', $d->id) }}', '{{ $d->customer->nama_lengkap ?? 'N/A' }}')">
                                                <i class="fa-solid fa-trash text-rose-600"></i>
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
    @else
        <div class="table-card">
            <div class="table-responsive">
                <table id="pending-tasks-table" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">#</th>
                            <th style="text-align: center;">Pelanggan</th>
                            <th style="text-align: center;">Keterangan</th>
                            <th style="text-align: center; width: 150px;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingTasks as $d)
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                                <td style="text-align: left;">
                                    <div style="font-weight: 700; color: #0f172a;">{{ $d->customer->nama_lengkap }}</div>
                                    <div style="font-size: 11px; color: #64748b;">ID: {{ $d->customer->id_pelanggan }}</div>
                                </td>
                                <td style="text-align: center;">{{ Str::limit($d->keterangan_admin ?: '-', 40) }}</td>
                                <td style="text-align: center;">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl" onclick="openDismantleDetailModal(this)"
                                            data-nama="{{ $d->customer->nama_lengkap }}" data-id-pel="{{ $d->customer->id_pelanggan }}"
                                            data-pppoe="{{ $d->customer->pppoe_username }}" data-wa="{{ $d->customer->no_whatsapp }}"
                                            data-tikor="{{ $d->customer->preOrder->titik_koordinat ?? 'N/A' }}" data-alamat="{{ $d->customer->alamat_lengkap }}"
                                            data-keterangan="{{ $d->keterangan_admin ?: '-' }}" data-foto="">
                                            <i class="fa-solid fa-circle-info text-primary"></i>
                                        </button>
                                        <button type="button" class="btn-primary-admin !h-9 !px-3 !text-[11px]" onclick="openCompleteDismantleModal(this)" data-id="{{ $d->id }}" data-nama="{{ $d->customer->nama_lengkap }}">
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

    <div class="modal-overlay" id="addDismantleModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <form action="{{ route('dismantles.store') }}" method="POST">
                @csrf
                <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                    <div class="modal-title"><i class="fa-solid fa-plus-square text-primary"></i> <span class="text-slate-900 font-bold text-[17px]">Buat Penugasan Dismantle</span></div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('addDismantleModal')"><i class="fa-solid fa-xmark"></i></button>
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
                            <p id="v_wa" class="text-sm font-bold text-sky-700 m-0"></p>
                            <p id="v_alamat" class="text-sm font-medium text-slate-600 m-0 leading-relaxed"></p>
                        </div>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label !text-slate-800 !font-bold"><i class="fa-solid fa-comment-dots text-slate-400 mr-1"></i> Alasan / Keterangan <span class="text-rose-500">*</span></label>
                        <textarea name="keterangan_admin" class="form-textarea" required rows="3" placeholder="Jelaskan detail alasan dismantle..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel" onclick="closeCustomModal('addDismantleModal')"><i class="fa-solid fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn-save !bg-indigo-600"><i class="fa-solid fa-paper-plane"></i> Kirim Tugas</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Dismantle Detail Modal -->
    <div class="modal-overlay" id="detailDismantleModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title"><i class="fa-solid fa-circle-info text-primary"></i> <span class="text-slate-900 font-bold">Info Lengkap Dismantle</span></div>
                <button class="modal-close" onclick="closeCustomModal('detailDismantleModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div><span class="text-[11px] font-bold text-slate-400 uppercase block">Nama Pelanggan</span><p id="det_nama" class="text-base font-black text-slate-900 m-0"></p></div>
                        <div><span class="text-[11px] font-bold text-slate-400 uppercase block">ID Pelanggan</span><p id="det_id_pel" class="text-sm font-bold text-slate-700 mt-0.5"></p></div>
                    </div>
                    <div class="space-y-4">
                        <div><span class="text-[11px] font-bold text-slate-400 uppercase block">No. WhatsApp</span><div class="flex items-center gap-2 mt-1"><p id="det_wa" class="text-sm font-bold text-emerald-600 m-0"></p><a id="det_wa_link" href="#" target="_blank" class="bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded text-[10px] font-black no-underline"><i class="fa-brands fa-whatsapp"></i> CHAT</a></div></div>
                        <div><span class="text-[11px] font-bold text-slate-400 uppercase block">Titik Koordinat</span><a id="det_tikor_link" href="#" target="_blank" class="bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded text-[10px] font-black no-underline"><i class="fa-solid fa-location-crosshairs"></i> LIHAT MAPS</a></div>
                    </div>
                </div>
                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 block uppercase mb-2">Alamat & Keterangan</span>
                    <p id="det_alamat" class="text-sm font-medium text-slate-600 leading-relaxed mb-3"></p>
                    <div class="bg-white border border-slate-200 p-4 rounded-xl font-bold text-slate-800 text-sm italic" id="det_keterangan"></div>
                </div>
                <div id="det_foto_container" class="hidden">
                    <span class="text-[11px] font-bold text-slate-400 block uppercase mb-2">Foto Bukti Pencabutan</span>
                    <div class="aspect-video w-full rounded-2xl overflow-hidden border-2 border-slate-100 bg-slate-50 flex items-center justify-center"><img id="det_foto" src="" class="w-full h-full object-contain"></div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl"><button type="button" class="btn-cancel w-full" onclick="closeCustomModal('detailDismantleModal')"><i class="fa-solid fa-xmark"></i> Tutup Detail</button></div>
        </div>
    </div>

    {{-- Modal Selesai (Teknisi) --}}
    <div class="modal-overlay" id="completeDismantleModal">
        <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl">
            <form id="completeForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                    <div class="modal-title"><i class="fa-solid fa-camera text-emerald-500"></i> <span class="text-slate-900 font-bold text-[17px]">Konfirmasi Pencabutan: <span id="n_pel"></span></span></div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('completeDismantleModal')"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body p-8 space-y-6">
                    <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100 text-emerald-700">
                        <p class="text-xs font-bold leading-relaxed m-0"><i class="fa-solid fa-circle-exclamation mr-1"></i> Pastikan semua perangkat sudah diambil dari lokasi pelanggan sebelum menekan tombol submit.</p>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label !text-slate-800 !font-bold"><i class="fa-solid fa-image text-slate-400 mr-1"></i> Foto Bukti Perangkat Dicabut <span class="text-rose-500">*</span></label>
                        <input type="file" name="foto_pencabutan" class="form-input" required accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl"><button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" onclick="closeCustomModal('completeDismantleModal')"><i class="fa-solid fa-xmark"></i> Batal</button><button type="submit" class="btn-save !flex !items-center !justify-center gap-2 !bg-emerald-600"><i class="fa-solid fa-cloud-arrow-up"></i> Kirim Laporan Selesai</button></div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            function openCustomModal(modalId) { const modal = document.getElementById(modalId); if (modal) { modal.classList.add('open'); document.body.style.overflow = 'hidden'; } }
            function closeCustomModal(modalId) { const modal = document.getElementById(modalId); if (modal) { modal.classList.remove('open'); document.body.style.overflow = ''; } }
            function selectCustomer(id, name) { $('#selected_customer_id').val(id); $('#selected_customer_name').val(name); fetch(`/dismantles/customer/${id}`).then(r => r.json()).then(d => { $('#v_wa').text('WA: ' + d.wa); $('#v_alamat').text('Alamat: ' + d.alamat); $('#customer_preview').slideDown(200); }); closeCustomModal('searchCustomerModal'); }
            function openDismantleDetailModal(triggerEl) { const $btn = $(triggerEl); $('#det_nama').text($btn.data('nama')); $('#det_id_pel').text($btn.data('id-pel')); $('#det_wa').text($btn.data('wa')); $('#det_alamat').text($btn.data('alamat')); $('#det_keterangan').text($btn.data('keterangan')); const wa = String($btn.data('wa') || ''); const waFormatted = wa.startsWith('0') ? '62' + wa.substring(1) : wa; $('#det_wa_link').attr('href', `https://wa.me/${waFormatted}`); const tikor = $btn.data('tikor'); if (tikor && tikor !== 'N/A') { $('#det_tikor_link').attr('href', `https://www.google.com/maps/search/?api=1&query=${tikor}`).show(); } else { $('#det_tikor_link').hide(); } const foto = $btn.data('foto'); if (foto && foto !== '') { $('#det_foto').attr('src', foto); $('#det_foto_container').removeClass('hidden'); } else { $('#det_foto_container').addClass('hidden'); } openCustomModal('detailDismantleModal'); }
            function openCompleteDismantleModal(triggerEl) { const $btn = $(triggerEl); $('#n_pel').text($btn.data('nama')); $('#completeForm').attr('action', `/dismantles/${$btn.data('id')}/complete`); openCustomModal('completeDismantleModal'); }
            $(document).ready(function() {
                const dtOptions = { "pageLength": 10, "lengthChange": false, "ordering": false, "info": false, "responsive": true, "dom": 'tpir', "language": { "zeroRecords": "Data tidak ditemukan" } };
                const adminTable = $('#admin-dismantle-table').DataTable(dtOptions);
                const pendingTable = $('#pending-tasks-table').DataTable(dtOptions);
                const customerSearchTable = $('#customer-search-table').DataTable({ ...dtOptions, "pageLength": 5 });
                $('.custom-search').on('keyup', function() { const val = this.value; if (adminTable) adminTable.search(val).draw(); if (pendingTable) pendingTable.search(val).draw(); });
                $('#customer-modal-search').on('keyup', function() { customerSearchTable.search(this.value).draw(); });
                $('.modal-overlay').on('click', function(e) { if (e.target === this) closeCustomModal(this.id); });
            });
        </script>
    @endsection
</x-layouts.app>
