<x-layouts.app>
    @section('Css')
        <style>
            .stat-card-asset {
                background: white;
                padding: 1.5rem;
                border-radius: 20px;
                border: 1px solid #e2e8f0;
                display: flex;
                align-items: center;
                gap: 1rem;
                transition: all 0.3s ease;
            }
            .stat-card-asset:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            }
            .stat-icon-asset {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
            }
            .asset-table-wrap {
                background: white;
                border-radius: 20px;
                border: 1px solid #e2e8f0;
                overflow: hidden;
            }
            .badge-type {
                padding: 4px 10px;
                border-radius: 8px;
                font-size: 10px;
                font-weight: 800;
                text-transform: uppercase;
            }
        </style>
    @endsection

    @php
        $isAdmin = auth()->user()->jabatan === 'Administrator';
    @endphp

    <!-- Header -->
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Pengelolaan Aset & Inventaris</h1>
            <p class="text-sm text-slate-500">Monitor stok barang, material, dan alat kerja secara real-time.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('assets.transactions') }}" class="btn-primary-admin !bg-slate-800 !flex items-center gap-2">
                <i class="fa-solid fa-history"></i> Riwayat Transaksi
            </a>
            @if($isAdmin)
                <button onclick="openCustomModal('addAssetModal')" class="btn-primary-admin !flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i> Tambah Jenis Barang
                </button>
            @endif
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="stat-card-asset">
            <div class="stat-icon-asset bg-blue-50 text-blue-600">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ $totalItems }}</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jenis Barang</div>
            </div>
        </div>
        <div class="stat-card-asset">
            <div class="stat-icon-asset bg-emerald-50 text-emerald-600">
                <i class="fa-solid fa-warehouse"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($totalStock) }}</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Stok Global</div>
            </div>
        </div>
        <div class="stat-card-asset">
            <div class="stat-icon-asset bg-rose-50 text-rose-600">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ $lowStockCount }}</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Stok Hampir Habis</div>
            </div>
        </div>
    </div>

    <!-- Toolbar & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 mb-4 flex flex-wrap items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="assetSearch" class="form-input !pl-12" placeholder="Cari nama barang atau tipe...">
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">
                Inventory List
            </span>
        </div>
    </div>

    <!-- Table -->
    <div class="asset-table-wrap">
        <table id="assetTable" class="admin-table w-full">
            <thead>
                <tr>
                    <th class="text-center w-16">#</th>
                    <th>Nama Barang</th>
                    <th>Tipe</th>
                    <th class="text-center">Stok</th>
                    <th>Satuan</th>
                    <th class="text-center w-32">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assets as $asset)
                    <tr>
                        <td class="text-center font-bold text-slate-400">{{ $loop->iteration }}</td>
                        <td class="font-bold text-slate-800 uppercase">{{ $asset->name }}</td>
                        <td>
                            <span class="badge-type bg-slate-100 text-slate-600 border border-slate-200">
                                {{ $asset->type }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="text-sm font-black {{ $asset->stock <= 5 ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ number_format($asset->stock) }}
                            </span>
                        </td>
                        <td class="text-xs font-bold text-slate-500 uppercase">{{ $asset->unit }}</td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="openTransactionModal({{ $asset->id }}, '{{ $asset->name }}')" 
                                    class="topbar-icon-btn !w-9 !h-9 !rounded-xl !bg-emerald-50" title="Input Transaksi">
                                    <i class="fa-solid fa-right-left text-emerald-600"></i>
                                </button>
                                @if($isAdmin)
                                    <button onclick="openEditAssetModal(this)" 
                                        data-id="{{ $asset->id }}"
                                        data-name="{{ $asset->name }}"
                                        data-type="{{ $asset->type }}"
                                        data-unit="{{ $asset->unit }}"
                                        data-description="{{ $asset->description }}"
                                        class="topbar-icon-btn !w-9 !h-9 !rounded-xl" title="Edit">
                                        <i class="fa-solid fa-pen-to-square text-primary"></i>
                                    </button>
                                    <button onclick="confirmGlobalDelete('{{ route('assets.destroy', $asset->id) }}', '{{ $asset->name }}')"
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

    <!-- Modals -->
    @if($isAdmin)
        {{-- Modal Tambah Asset --}}
        <div class="modal-overlay" id="addAssetModal">
            <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
                <form action="{{ route('assets.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                        <div class="modal-title">
                            <i class="fa-solid fa-plus-circle text-primary"></i>
                            <span class="text-slate-900 font-bold">Tambah Jenis Barang Baru</span>
                        </div>
                        <button type="button" class="modal-close" onclick="closeCustomModal('addAssetModal')">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body p-8 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label class="form-label font-bold uppercase text-[11px]">Nama Barang <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" class="form-input" placeholder="Contoh: ONT Huawei HG8245H" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label font-bold uppercase text-[11px]">Tipe Barang <span class="text-rose-500">*</span></label>
                                <select name="type" class="form-select" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="ONT">ONT (Optical Network Terminal)</option>
                                    <option value="Kabel">Kabel Fiber Optic</option>
                                    <option value="Passive">Passive Splitter / Box</option>
                                    <option value="Tools">Alat Kerja (Tang, Splicer, dll)</option>
                                    <option value="Lainnya">Lain-lain</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label class="form-label font-bold uppercase text-[11px]">Satuan <span class="text-rose-500">*</span></label>
                                <input type="text" name="unit" class="form-input" placeholder="Pcs, Meter, Roll, dll" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label font-bold uppercase text-[11px]">Keterangan</label>
                            <textarea name="description" class="form-textarea" rows="3" placeholder="Informasi tambahan mengenai barang ini..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                        <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" onclick="closeCustomModal('addAssetModal')">
                            <i class="fa-solid fa-xmark"></i> Batal
                        </button>
                        <button type="submit" class="btn-save !flex !items-center !justify-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i> Simpan Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Edit Asset --}}
        <div class="modal-overlay" id="editAssetModal">
            <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
                <form id="editAssetForm" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                        <div class="modal-title">
                            <i class="fa-solid fa-pen-to-square text-primary"></i>
                            <span class="text-slate-900 font-bold">Edit Jenis Barang</span>
                        </div>
                        <button type="button" class="modal-close" onclick="closeCustomModal('editAssetModal')">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body p-8 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label class="form-label font-bold uppercase text-[11px]">Nama Barang <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" id="edit_name" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label font-bold uppercase text-[11px]">Tipe Barang <span class="text-rose-500">*</span></label>
                                <select name="type" id="edit_type" class="form-select" required>
                                    <option value="ONT">ONT (Optical Network Terminal)</option>
                                    <option value="Kabel">Kabel Fiber Optic</option>
                                    <option value="Passive">Passive Splitter / Box</option>
                                    <option value="Tools">Alat Kerja (Tang, Splicer, dll)</option>
                                    <option value="Lainnya">Lain-lain</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label class="form-label font-bold uppercase text-[11px]">Satuan <span class="text-rose-500">*</span></label>
                                <input type="text" name="unit" id="edit_unit" class="form-input" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label font-bold uppercase text-[11px]">Keterangan</label>
                            <textarea name="description" id="edit_description" class="form-textarea" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                        <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" onclick="closeCustomModal('editAssetModal')">
                            <i class="fa-solid fa-xmark"></i> Batal
                        </button>
                        <button type="submit" class="btn-save !flex !items-center !justify-center gap-2">
                            <i class="fa-solid fa-check"></i> Perbarui Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Transaksi (Semua Role) --}}
    <div class="modal-overlay" id="transactionModal">
        <div class="modal-box max-w-xl bg-white border border-slate-300 shadow-2xl">
            <form action="{{ route('assets.transactions.store') }}" method="POST" novalidate>
                @csrf
                <input type="hidden" name="asset_id" id="trans_asset_id">
                <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                    <div class="modal-title">
                        <i class="fa-solid fa-right-left text-emerald-600"></i>
                        <span class="text-slate-900 font-bold">Transaksi Aset: <span id="trans_asset_name" class="text-emerald-600"></span></span>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('transactionModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body p-8 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="form-label font-bold uppercase text-[11px]">Jenis Transaksi <span class="text-rose-500">*</span></label>
                            <select name="type" id="trans_type" class="form-select" required onchange="toggleTransactionFields()">
                                <option value="Masuk">Barang Masuk (Stok Bertambah)</option>
                                <option value="Keluar">Barang Keluar (Stok Berkurang)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label font-bold uppercase text-[11px]">Tanggal <span class="text-rose-500">*</span></label>
                            <input type="date" name="date" class="form-input" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="form-label font-bold uppercase text-[11px]">Jumlah <span class="text-rose-500">*</span></label>
                            <input type="number" name="quantity" class="form-input" min="1" placeholder="0" required>
                        </div>
                        <div class="form-group" id="field_person_dropdown">
                            <label class="form-label font-bold uppercase text-[11px]" id="label_person">Penanggung Jawab / User</label>
                            <select name="user_id" class="form-select select2-user">
                                <option value="">-- Pilih User (Optional) --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->nama_lengkap }} ({{ $user->jabatan }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label font-bold uppercase text-[11px]">Nama Pihak Luar (Manual)</label>
                        <input type="text" name="external_person_name" class="form-input" placeholder="Isi jika orang tersebut tidak terdaftar di sistem">
                    </div>

                    <div class="form-group">
                        <label class="form-label font-bold uppercase text-[11px]">Keterangan / Tujuan <span class="text-rose-500">*</span></label>
                        <textarea name="description" class="form-textarea" rows="2" placeholder="Contoh: Pengadaan rutin, Pemasangan pelanggan A, dll" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" onclick="closeCustomModal('transactionModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-save !bg-emerald-600 !flex !items-center !justify-center gap-2">
                        <i class="fa-solid fa-save"></i> Catat Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                const table = $('#assetTable').DataTable({
                    "pageLength": 15,
                    "lengthChange": false,
                    "ordering": false,
                    "info": false,
                    "responsive": true,
                    "dom": 'tpir',
                    "language": {
                        "zeroRecords": "Data barang tidak ditemukan",
                        "paginate": {
                            "previous": '<i class="fa-solid fa-chevron-left"></i>',
                            "next": '<i class="fa-solid fa-chevron-right"></i>'
                        }
                    }
                });

                $('#assetSearch').on('keyup', function() {
                    table.search(this.value).draw();
                });

                $('.select2-user').select2({
                    width: '100%',
                    dropdownParent: $('#transactionModal')
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
                    $('#label_person').text('Penerima / Admin Gudang');
                } else {
                    $('#label_person').text('Pengambil / Teknisi');
                }
            }
        </script>
    @endsection
</x-layouts.app>
