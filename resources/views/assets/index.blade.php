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

            #assetTable td {
                padding: 10px 14px;
                font-size: 13px;
            }

            #assetTable th {
                padding: 11px 14px;
                font-size: 12px;
                font-weight: 700;
                color: #64748b;
                letter-spacing: 0.05em;
                text-transform: uppercase !important;
            }

            .badge-type {
                padding: 2px 8px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 600;
                background: #f1f5f9;
                color: #475569;
                border: 1px solid #e2e8f0;
            }
        </style>
    @endsection

    @php $isAdmin = auth()->user()->isAdmin(); @endphp

    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Pengelolaan Aset</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Monitor stok barang, material, dan alat kerja secara real-time.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('assets.transactions') }}"
                class="btn-primary-admin !bg-slate-800 !h-9 !px-4 text-xs font-semibold !flex !items-center !justify-center gap-2">
                <i class="fa-solid fa-history"></i> Riwayat
            </a>
            @if ($isAdmin)
                <button type="button" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold !flex !items-center !justify-center gap-2"
                    onclick="openCustomModal('addAssetModal')">
                    <i class="fa-solid fa-plus-square"></i> Tambah Barang
                </button>
            @endif
        </div>
    </div>

    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;"><i class="fa-solid fa-boxes-stacked"></i>
            </div>
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
            <div class="stat-icon" style="background:#fff1f2;color:#e11d48;"><i
                    class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
                <div class="stat-val">{{ $lowStockCount }}</div>
                <div class="stat-lbl">Stok Menipis</div>
            </div>
        </div>
    </div>

    <div class="table-toolbar">
        <div class="search-wrap !max-w-md">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input custom-search" placeholder="Cari nama barang atau tipe..."
                autocomplete="off" />
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table id="assetTable" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: left;">Nama Barang</th>
                        <th style="text-align: center; width: 140px;">Tipe</th>
                        <th style="text-align: center; width: 100px;">Stok</th>
                        <th style="text-align: center; width: 100px;">Satuan</th>
                        <th style="text-align: center; width: 130px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assets as $asset)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}
                            </td>
                            <td style="text-align: left;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 13px;">{{ $asset->name }}</div>
                                @if ($asset->description)
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">{{ $asset->description }}</div>
                                @endif
                            </td>
                            <td style="text-align: center;"><span class="badge-type">{{ $asset->type }}</span></td>
                            <td style="text-align: center;">
                                <span class="font-mono font-bold text-sm {{ $asset->stock <= 5 ? 'text-rose-600' : 'text-slate-900' }}">
                                    {{ number_format($asset->stock) }}
                                </span>
                            </td>
                            <td style="text-align: center; color: #64748b; font-size: 12px; font-weight: 500;">{{ $asset->unit }}</td>
                            <td style="text-align: center;">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button"
                                        onclick="openTransactionModal({{ $asset->id }}, '{{ addslashes($asset->name) }}')"
                                        class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-emerald-300 hover:!bg-emerald-50"
                                        title="Catat Mutasi Stok">
                                        <i class="fa-solid fa-right-left text-emerald-600"></i>
                                    </button>
                                    @if ($isAdmin)
                                        <button type="button" onclick="openEditAssetModal(this)"
                                            data-id="{{ $asset->id }}" data-name="{{ $asset->name }}"
                                            data-type="{{ $asset->type }}" data-unit="{{ $asset->unit }}"
                                            data-description="{{ $asset->description }}"
                                            class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-sky-300 hover:!bg-sky-50" title="Edit Barang">
                                            <i class="fa-solid fa-pen-to-square text-slate-600"></i>
                                        </button>
                                        <button type="button"
                                            onclick="confirmGlobalDelete('{{ route('assets.destroy', $asset->id) }}', '{{ addslashes($asset->name) }}')"
                                            class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-rose-300 hover:!bg-rose-50" title="Hapus Barang">
                                            <i class="fa-solid fa-trash text-rose-500"></i>
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
    @if ($isAdmin)
        <div class="modal-overlay" id="addAssetModal">
            <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl">
                <form action="{{ route('assets.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 m-0">Tambah Jenis Barang</h3>
                                <p class="text-xs text-slate-500 m-0 mt-0.5">Daftarkan item barang atau perangkat baru ke inventaris.</p>
                            </div>
                        </div>
                        <button type="button" class="modal-close" onclick="closeCustomModal('addAssetModal')">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body p-6 bg-white space-y-4">
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs">Nama Barang <span class="text-rose-500">*</span></label>
                            <input type="text" name="name"
                                class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs"
                                placeholder="Contoh: ONT Huawei HG8245H" required>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Tipe Barang <span class="text-rose-500">*</span></label>
                                <select name="type"
                                    class="form-input-custom !font-normal w-full bg-white text-slate-800 border-slate-300 text-xs" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="ONT">ONT (Network Terminal)</option>
                                    <option value="Kabel">Kabel Fiber Optic</option>
                                    <option value="Passive">Passive Splitter</option>
                                    <option value="Tools">Alat Kerja</option>
                                    <option value="Lainnya">Lain-lain</option>
                                </select>
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Satuan <span class="text-rose-500">*</span></label>
                                <input type="text" name="unit"
                                    class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs"
                                    placeholder="Pcs, Meter, Roll, dll" required>
                            </div>
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs">Keterangan Tambahan</label>
                            <textarea name="description" class="form-textarea !font-normal bg-white text-slate-800 border-slate-300 text-xs" rows="3"
                                placeholder="Informasi tambahan spesifikasi atau penempatan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2 rounded-b-2xl">
                        <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                            onclick="closeCustomModal('addAssetModal')">Batal</button>
                        <button type="submit" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold">Simpan Barang</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-overlay" id="editAssetModal">
            <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl">
                <form id="editAssetForm" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 m-0">Edit Jenis Barang</h3>
                                <p class="text-xs text-slate-500 m-0 mt-0.5">Perbarui rincian item barang pada inventaris.</p>
                            </div>
                        </div>
                        <button type="button" class="modal-close" onclick="closeCustomModal('editAssetModal')">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body p-6 bg-white space-y-4">
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs">Nama Barang <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" id="edit_name"
                                class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" required>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Tipe Barang <span class="text-rose-500">*</span></label>
                                <select name="type" id="edit_type"
                                    class="form-input-custom !font-normal w-full bg-white text-slate-800 border-slate-300 text-xs" required>
                                    <option value="ONT">ONT (Network Terminal)</option>
                                    <option value="Kabel">Kabel Fiber Optic</option>
                                    <option value="Passive">Passive Splitter</option>
                                    <option value="Tools">Alat Kerja</option>
                                    <option value="Lainnya">Lain-lain</option>
                                </select>
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Satuan <span class="text-rose-500">*</span></label>
                                <input type="text" name="unit" id="edit_unit"
                                    class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" required>
                            </div>
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs">Keterangan</label>
                            <textarea name="description" id="edit_description"
                                class="form-textarea !font-normal bg-white text-slate-800 border-slate-300 text-xs" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2 rounded-b-2xl">
                        <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                            onclick="closeCustomModal('editAssetModal')">Batal</button>
                        <button type="submit" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold">Perbarui Barang</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="modal-overlay" id="transactionModal">
        <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl">
            <form action="{{ route('assets.transactions.store') }}" method="POST" novalidate>
                @csrf
                <input type="hidden" name="asset_id" id="trans_asset_id">
                <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm border border-emerald-200 shrink-0">
                            <i class="fa-solid fa-right-left"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 m-0">Catat Mutasi Barang</h3>
                            <p class="text-xs text-slate-500 m-0 mt-0.5">Barang: <span id="trans_asset_name" class="font-bold text-emerald-700"></span></p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('transactionModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body p-6 bg-white space-y-4">
                    <div class="form-grid-2">
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs">Jenis Transaksi <span class="text-rose-500">*</span></label>
                            <select name="type" id="trans_type"
                                class="form-input-custom !font-normal w-full bg-white text-slate-800 border-slate-300 text-xs" required
                                onchange="toggleTransactionFields()">
                                <option value="Masuk">Barang Masuk (Stok +)</option>
                                <option value="Keluar">Barang Keluar (Stok -)</option>
                            </select>
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs">Tanggal <span class="text-rose-500">*</span></label>
                            <input type="date" name="date"
                                class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs">Jumlah <span class="text-rose-500">*</span></label>
                            <input type="number" name="quantity"
                                class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" min="1"
                                placeholder="0" required>
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs" id="label_person">Penerima / Admin</label>
                            <select name="user_id"
                                class="form-input-custom !font-normal w-full bg-white text-slate-800 border-slate-300 text-xs"
                                style="width: 100%;">
                                <option value="">-- Pilih User --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->nama_lengkap }} ({{ $user->jabatan }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label text-slate-700 font-medium text-xs">Nama Pihak Luar (Opsional)</label>
                        <input type="text" name="external_person_name"
                            class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs"
                            placeholder="Nama pengirim/penerima luar (jika non-staf)...">
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label text-slate-700 font-medium text-xs">Tujuan / Keterangan <span class="text-rose-500">*</span></label>
                        <textarea name="description" class="form-textarea !font-normal bg-white text-slate-800 border-slate-300 text-xs" rows="2"
                            placeholder="Contoh: Pengadaan, Pasang Baru, Maintenance, dll" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2 rounded-b-2xl">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                        onclick="closeCustomModal('transactionModal')">Batal</button>
                    <button type="submit" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
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
            $(document).ready(function() {
                const table = $('#assetTable').DataTable({
                    "pageLength": 15,
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
                $('.custom-search').on('keyup', function() {
                    table.search(this.value).draw();
                });
                $('.modal-overlay').on('click', function(e) {
                    if (e.target === this) closeCustomModal(this.id);
                });
            });

            function openEditAssetModal(btn) {
                const $btn = $(btn);
                $('#editAssetForm').attr('action', `/assets/${$btn.data('id')}`);
                $('#edit_name').val($btn.data('name'));
                $('#edit_type').val($btn.data('type'));
                $('#edit_unit').val($btn.data('unit'));
                $('#edit_description').val($btn.data('description'));
                openCustomModal('editAssetModal');
            }

            function openTransactionModal(id, name) {
                $('#trans_asset_id').val(id);
                $('#trans_asset_name').text(name);
                toggleTransactionFields();
                openCustomModal('transactionModal');
            }

            function toggleTransactionFields() {
                const type = $('#trans_type').val();
                if (type === 'Masuk') {
                    $('#label_person').text('Penerima / Admin');
                } else {
                    $('#label_person').text('Pengambil / Teknisi');
                }
            }
        </script>
    @endsection
</x-layouts.app>
