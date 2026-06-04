<x-layouts.app>
    @section('Css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            /* Hakatech DataTables Customization */
            .dataTables_wrapper { padding: 0; }
            .dataTables_filter { display: none; }
            .dataTables_paginate {
                display: flex; justify-content: flex-end; padding: 1.25rem 1.5rem; gap: 4px;
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
            .search-icon {
                position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
                color: #94a3b8; font-size: 13px; pointer-events: none;
            }
            .search-input {
                width: 100%; padding: 9px 12px 9px 36px; border: 2px solid #e2e8f0;
                border-radius: 10px; font-size: 13px; outline: none; background: #f8fafc;
                height: 42px;
            }

            #assetTable thead th, #assetTable tbody td { text-transform: uppercase !important; font-weight: 600; font-size: 12px; }

            .badge-type {
                padding: 3px 9px; border-radius: 20px; font-size: 10px; font-weight: 700;
                background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;
            }

            /* Select2 Matching OLT & Hakatech Form Style */
            .select2-container--default .select2-selection--single {
                height: 42px !important; 
                border: 2px solid #cbd5e1 !important; 
                border-radius: 10px !important;
                display: flex !important; 
                align-items: center !important;
                background-color: #fff !important;
                transition: all 0.2s;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 42px !important; 
                padding-left: 12px !important; 
                font-size: 13px !important; 
                font-weight: 600 !important;
                color: #334155 !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow { 
                height: 40px !important; 
                right: 8px !important;
            }
            .select2-container--default.select2-container--focus .select2-selection--single {
                border-color: #0ea5e9 !important;
                box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1) !important;
            }
            .select2-dropdown { 
                border: 2px solid #0ea5e9 !important; 
                border-radius: 10px !important; 
                overflow: hidden !important; 
                box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1) !important; 
            }

            /* Fix Form Layout */
            .form-group-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1.5rem;
            }
            @media (max-width: 640px) {
                .form-group-row { grid-template-columns: 1fr; gap: 1rem; }
            }
        </style>
    @endsection

    @php $isAdmin = auth()->user()->jabatan === 'Administrator'; @endphp

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Pengelolaan Aset</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Monitor stok barang, material, dan alat kerja secara real-time.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('assets.transactions') }}" class="btn-primary-admin !bg-slate-800 !flex !items-center !justify-center gap-2">
                <i class="fa-solid fa-history"></i> Riwayat
            </a>
            @if ($isAdmin)
                <button type="button" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                    onclick="openCustomModal('addAssetModal')">
                    <i class="fa-solid fa-plus-square"></i> Tambah Barang
                </button>
            @endif
        </div>
    </div>

    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;"><i class="fa-solid fa-boxes-stacked"></i></div>
            <div>
                <div class="stat-val">{{ $totalItems }}</div>
                <div class="stat-lbl">Jenis Barang</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;"><i class="fa-solid fa-warehouse"></i></div>
            <div>
                <div class="stat-val">{{ number_format($totalStock) }}</div>
                <div class="stat-lbl">Total Stok</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff1f2;color:#e11d48;"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
                <div class="stat-val">{{ $lowStockCount }}</div>
                <div class="stat-lbl">Stok Menipis</div>
            </div>
        </div>
    </div>

    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input custom-search" placeholder="Cari nama barang atau tipe..." autocomplete="off" />
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table id="assetTable" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th>Nama Barang</th>
                        <th style="text-align: center;">Tipe</th>
                        <th style="text-align: center;">Stok</th>
                        <th style="text-align: center;">Satuan</th>
                        <th style="text-align: center; width: 150px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $asset)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                            <td style="text-align: left; font-weight: 700; color: #0f172a;">{{ $asset->name }}</td>
                            <td style="text-align: center;"><span class="badge-type">{{ $asset->type }}</span></td>
                            <td style="text-align: center;">
                                <span style="font-weight: 800; font-size: 14px; {{ $asset->stock <= 5 ? 'color: #e11d48;' : 'color: #0f172a;' }}">
                                    {{ number_format($asset->stock) }}
                                </span>
                            </td>
                            <td style="text-align: center; color: #64748b;">{{ $asset->unit }}</td>
                            <td style="text-align: center;">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="openTransactionModal({{ $asset->id }}, '{{ $asset->name }}')" 
                                        class="topbar-icon-btn !w-9 !h-9 !rounded-xl !bg-emerald-50" title="Input Transaksi">
                                        <i class="fa-solid fa-right-left text-emerald-600"></i>
                                    </button>
                                    @if($isAdmin)
                                        <button type="button" onclick="openEditAssetModal(this)" 
                                            data-id="{{ $asset->id }}" data-name="{{ $asset->name }}" data-type="{{ $asset->type }}"
                                            data-unit="{{ $asset->unit }}" data-description="{{ $asset->description }}"
                                            class="topbar-icon-btn !w-9 !h-9 !rounded-xl" title="Edit">
                                            <i class="fa-solid fa-pen-to-square text-primary"></i>
                                        </button>
                                        <button type="button" onclick="confirmGlobalDelete('{{ route('assets.destroy', $asset->id) }}', '{{ $asset->name }}')"
                                            class="topbar-icon-btn !w-9 !h-9 !rounded-xl" title="Hapus">
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

    {{-- Modals --}}
    @if($isAdmin)
        <div class="modal-overlay" id="addAssetModal">
            <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
                <form action="{{ route('assets.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                        <div class="modal-title"><i class="fa-solid fa-plus-square text-primary"></i> <span class="text-slate-900 font-bold text-[17px]">Tambah Jenis Barang</span></div>
                        <button type="button" class="modal-close" onclick="closeCustomModal('addAssetModal')"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="modal-body p-8 space-y-6 bg-white">
                        <div class="form-group-row">
                            <div class="form-group">
                                <label class="form-label !text-slate-800 !font-bold">Nama Barang <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" class="form-input" placeholder="Contoh: ONT Huawei HG8245H" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label !text-slate-800 !font-bold">Tipe Barang <span class="text-rose-500">*</span></label>
                                <select name="type" class="form-select select2-basic" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="ONT">ONT (Network Terminal)</option>
                                    <option value="Kabel">Kabel Fiber Optic</option>
                                    <option value="Passive">Passive Splitter</option>
                                    <option value="Tools">Alat Kerja</option>
                                    <option value="Lainnya">Lain-lain</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group-row">
                            <div class="form-group">
                                <label class="form-label !text-slate-800 !font-bold">Satuan <span class="text-rose-500">*</span></label>
                                <input type="text" name="unit" class="form-input" placeholder="Pcs, Meter, Roll, dll" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label !text-slate-800 !font-bold">Keterangan Tambahan</label>
                                <input type="text" name="description" class="form-input" placeholder="Opsional...">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                        <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" onclick="closeCustomModal('addAssetModal')"><i class="fa-solid fa-xmark"></i> Batal</button>
                        <button type="submit" class="btn-save !flex !items-center !justify-center gap-2"><i class="fa-solid fa-paper-plane"></i> Simpan Barang</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-overlay" id="editAssetModal">
            <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
                <form id="editAssetForm" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                        <div class="modal-title"><i class="fa-solid fa-pen-to-square text-primary"></i> <span class="text-slate-900 font-bold text-[17px]">Edit Jenis Barang</span></div>
                        <button type="button" class="modal-close" onclick="closeCustomModal('editAssetModal')"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="modal-body p-8 space-y-6 bg-white">
                        <div class="form-group-row">
                            <div class="form-group">
                                <label class="form-label !text-slate-800 !font-bold">Nama Barang <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" id="edit_name" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label !text-slate-800 !font-bold">Tipe Barang <span class="text-rose-500">*</span></label>
                                <select name="type" id="edit_type" class="form-select select2-basic" required>
                                    <option value="ONT">ONT (Network Terminal)</option>
                                    <option value="Kabel">Kabel Fiber Optic</option>
                                    <option value="Passive">Passive Splitter</option>
                                    <option value="Tools">Alat Kerja</option>
                                    <option value="Lainnya">Lain-lain</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group-row">
                            <div class="form-group">
                                <label class="form-label !text-slate-800 !font-bold">Satuan <span class="text-rose-500">*</span></label>
                                <input type="text" name="unit" id="edit_unit" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label !text-slate-800 !font-bold">Keterangan</label>
                                <input type="text" name="description" id="edit_description" class="form-input">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                        <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" onclick="closeCustomModal('editAssetModal')"><i class="fa-solid fa-xmark"></i> Batal</button>
                        <button type="submit" class="btn-save !flex !items-center !justify-center gap-2"><i class="fa-solid fa-check"></i> Perbarui Barang</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="modal-overlay" id="transactionModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <form action="{{ route('assets.transactions.store') }}" method="POST" novalidate>
                @csrf
                <input type="hidden" name="asset_id" id="trans_asset_id">
                <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                    <div class="modal-title"><i class="fa-solid fa-right-left text-emerald-600"></i> <span class="text-slate-900 font-bold text-[17px]">Transaksi: <span id="trans_asset_name" class="text-emerald-600"></span></span></div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('transactionModal')"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body p-8 space-y-6 bg-white">
                    <div class="form-group-row">
                        <div class="form-group">
                            <label class="form-label !text-slate-800 !font-bold">Jenis Transaksi <span class="text-rose-500">*</span></label>
                            <select name="type" id="trans_type" class="form-select select2-basic" required onchange="toggleTransactionFields()">
                                <option value="Masuk">Barang Masuk (Stok +)</option>
                                <option value="Keluar">Barang Keluar (Stok -)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label !text-slate-800 !font-bold">Tanggal <span class="text-rose-500">*</span></label>
                            <input type="date" name="date" class="form-input" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="form-group-row">
                        <div class="form-group">
                            <label class="form-label !text-slate-800 !font-bold">Jumlah <span class="text-rose-500">*</span></label>
                            <input type="number" name="quantity" class="form-input" min="1" placeholder="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label !text-slate-800 !font-bold" id="label_person">Penerima / Admin</label>
                            <select name="user_id" class="form-select select2-user">
                                <option value="">-- Pilih User --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->nama_lengkap }} ({{ $user->jabatan }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group-row">
                        <div class="form-group">
                            <label class="form-label !text-slate-800 !font-bold">Nama Pihak Luar (Manual)</label>
                            <input type="text" name="external_person_name" class="form-input" placeholder="Nama pengirim/penerima luar...">
                        </div>
                        <div class="form-group">
                            <label class="form-label !text-slate-800 !font-bold">Tujuan / Keterangan <span class="text-rose-500">*</span></label>
                            <input type="text" name="description" class="form-input" placeholder="Contoh: Pengadaan, Pasang Baru, dll" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" onclick="closeCustomModal('transactionModal')"><i class="fa-solid fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn-save !bg-emerald-600 !flex !items-center !justify-center gap-2"><i class="fa-solid fa-check-circle"></i> Catat Transaksi</button>
                </div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            function openCustomModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) { modal.classList.add('open'); document.body.style.overflow = 'hidden'; }
            }
            function closeCustomModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) { modal.classList.remove('open'); document.body.style.overflow = ''; }
            }
            $(document).ready(function() {
                const table = $('#assetTable').DataTable({
                    "pageLength": 15, "lengthChange": false, "ordering": false, "info": false, "responsive": true, "dom": 'tpir',
                    "language": { "zeroRecords": "Data tidak ditemukan", "paginate": { "previous": '<i class="fa-solid fa-chevron-left"></i>', "next": '<i class="fa-solid fa-chevron-right"></i>' } }
                });
                $('.custom-search').on('keyup', function() { table.search(this.value).draw(); });
                
                $('.select2-user').select2({ width: '100%', dropdownParent: $('#transactionModal') });
                $('.select2-basic').select2({ width: '100%', minimumResultsForSearch: Infinity });

                $('.modal-overlay').on('click', function(e) { if (e.target === this) closeCustomModal(this.id); });
            });
            function openEditAssetModal(btn) {
                const $btn = $(btn);
                $('#editAssetForm').attr('action', `/assets/${$btn.data('id')}`);
                $('#edit_name').val($btn.data('name')); $('#edit_type').val($btn.data('type')).trigger('change');
                $('#edit_unit').val($btn.data('unit')); $('#edit_description').val($btn.data('description'));
                openCustomModal('editAssetModal');
            }
            function openTransactionModal(id, name) {
                $('#trans_asset_id').val(id); $('#trans_asset_name').text(name);
                toggleTransactionFields(); openCustomModal('transactionModal');
            }
            function toggleTransactionFields() {
                const type = $('#trans_type').val();
                if (type === 'Masuk') { $('#label_person').text('Penerima / Admin'); } else { $('#label_person').text('Pengambil / Teknisi'); }
            }
        </script>
    @endsection
</x-layouts.app>
