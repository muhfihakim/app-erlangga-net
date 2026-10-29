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

            #transactionTable td {
                padding: 10px 14px;
                font-size: 13px;
            }

            #transactionTable th {
                padding: 11px 14px;
                font-size: 12px;
                font-weight: 700;
                color: #64748b;
                letter-spacing: 0.05em;
                text-transform: uppercase !important;
            }
        </style>
    @endsection

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Riwayat Mutasi Barang</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Log lengkap arus keluar dan masuk inventaris gudang.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('assets.index') }}" class="btn-cancel !bg-white !border-slate-200 !text-slate-600 !h-9 !px-4 text-xs font-semibold !flex !items-center !justify-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Stok
            </a>
            <button type="button" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold !flex !items-center !justify-center gap-2"
                onclick="openCustomModal('addMutationModal')">
                <i class="fa-solid fa-right-left"></i> Catat Mutasi
            </button>
            @if (request('start_date') && request('end_date'))
                <a href="{{ route('assets.export', request()->query()) }}" class="btn-primary-admin !bg-rose-600 !h-9 !px-4 text-xs font-semibold !flex !items-center !justify-center gap-2">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            @endif
        </div>
    </div>

    <!-- Filters Section -->
    <div class="table-toolbar">
        <form action="{{ route('assets.transactions') }}" method="GET" class="w-full">
            <div class="filter-group">
                <div style="flex: 1; min-width: 160px;">
                    <label class="form-label-custom">Tipe Transaksi</label>
                    <select name="type" class="form-input-custom">
                        <option value="">Semua Jenis</option>
                        <option value="Masuk" {{ request('type') === 'Masuk' ? 'selected' : '' }}>Barang Masuk (+)</option>
                        <option value="Keluar" {{ request('type') === 'Keluar' ? 'selected' : '' }}>Barang Keluar (-)</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label class="form-label-custom">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-input-custom" value="{{ request('start_date') }}">
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label class="form-label-custom">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-input-custom" value="{{ request('end_date') }}">
                </div>
                <div style="flex: 1.5; min-width: 200px;">
                    <label class="form-label-custom">Cari Transaksi</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input custom-search" id="custom-search" placeholder="Cari barang, user..."
                            autocomplete="off" />
                    </div>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary-admin !h-[38px] !px-4 text-xs font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('assets.transactions') }}" class="topbar-icon-btn !w-[38px] !h-[38px] !rounded-lg !bg-white" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table id="transactionTable" class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th style="text-align: center; width: 100px;">Tanggal</th>
                        <th style="text-align: left; min-width: 180px;">Nama Barang</th>
                        <th style="text-align: center; width: 110px;">Tipe</th>
                        <th style="text-align: center; width: 100px;">Jumlah</th>
                        <th style="text-align: left; min-width: 180px;">Penerima / Pengambil</th>
                        <th style="text-align: left; min-width: 200px;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trans)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                {{ $loop->iteration }}
                            </td>
                            <td style="text-align: center; color: #64748b; font-size: 12px; font-mono;">
                                {{ \Carbon\Carbon::parse($trans->date)->format('d/m/Y') }}
                            </td>
                            <td style="text-align: left; font-weight: 700; color: #0f172a;">
                                {{ $trans->asset ? $trans->asset->name : 'Barang Dihapus' }}
                                @if ($trans->asset)
                                    <span class="text-xs text-slate-400 font-normal">({{ $trans->asset->unit }})</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if ($trans->type === 'Masuk')
                                    <span class="badge" style="background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; font-size:10px; font-weight:700; padding:2px 8px; border-radius:6px;">
                                        <i class="fa-solid fa-arrow-down mr-1"></i> MASUK
                                    </span>
                                @else
                                    <span class="badge" style="background:#fef2f2; color:#be123c; border:1px solid #fecaca; font-size:10px; font-weight:700; padding:2px 8px; border-radius:6px;">
                                        <i class="fa-solid fa-arrow-up mr-1"></i> KELUAR
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="font-mono font-bold text-sm {{ $trans->type === 'Masuk' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $trans->type === 'Masuk' ? '+' : '-' }}{{ number_format($trans->quantity) }}
                                </span>
                            </td>
                            <td style="text-align: left;">
                                @if ($trans->user)
                                    <div class="flex items-center gap-2">
                                        <div style="width: 24px; height: 24px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; color: #64748b;">
                                            {{ strtoupper(substr($trans->user->nama_lengkap, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span style="font-weight: 600; color: #334155; font-size: 12px;">{{ $trans->user->nama_lengkap }}</span>
                                            <span style="font-size: 10px; color: #94a3b8; display: block;">{{ $trans->user->jabatan }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span style="color: #94a3b8; font-style: italic; font-size: 12px;">{{ $trans->external_person_name ?: '-' }}</span>
                                @endif
                            </td>
                            <td style="text-align: left; color: #64748b; font-size: 12px;">
                                {{ $trans->description ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 2.5rem 0; color: #94a3b8; font-weight: 600; text-align: center;">Belum ada data transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Mutasi --}}
    <div class="modal-overlay" id="addMutationModal">
        <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl">
            <form action="{{ route('assets.transactions.store') }}" method="POST" novalidate>
                @csrf
                <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm border border-emerald-200 shrink-0">
                            <i class="fa-solid fa-right-left"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 m-0">Catat Mutasi Barang</h3>
                            <p class="text-xs text-slate-500 m-0 mt-0.5">Catat arus barang masuk atau keluar gudang.</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('addMutationModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body p-5 space-y-4 bg-white">
                    <div class="form-group !mb-0">
                        <label class="form-label text-slate-700 font-medium text-xs block mb-1">Pilih Barang <span class="text-rose-500">*</span></label>
                        <select name="asset_id" class="form-input-custom w-full bg-white text-slate-900 border-slate-300" required>
                            <option value="">-- Pilih Barang --</option>
                            @foreach ($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->name }} (Sisa: {{ number_format($asset->stock) }} {{ $asset->unit }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs block mb-1">Jenis Mutasi <span class="text-rose-500">*</span></label>
                            <select name="type" id="mutation_type"
                                class="form-input-custom w-full bg-white text-slate-900 border-slate-300" required
                                onchange="toggleMutationFields()">
                                <option value="Masuk">Barang Masuk (Stok +)</option>
                                <option value="Keluar">Barang Keluar (Stok -)</option>
                            </select>
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs block mb-1">Tanggal <span class="text-rose-500">*</span></label>
                            <input type="date" name="date"
                                class="form-input bg-white text-slate-900 border-slate-300"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs block mb-1">Jumlah <span class="text-rose-500">*</span></label>
                            <input type="number" name="quantity"
                                class="form-input bg-white text-slate-900 border-slate-300" min="1"
                                placeholder="0" required>
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs block mb-1" id="label_mutation_person">Penerima / Admin</label>
                            <select name="user_id"
                                class="form-input-custom w-full bg-white text-slate-900 border-slate-300"
                                style="width: 100%;">
                                <option value="">-- Pilih User --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->nama_lengkap }} ({{ $user->jabatan }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label text-slate-700 font-medium text-xs block mb-1">Nama Pihak Luar (Opsional)</label>
                        <input type="text" name="external_person_name"
                            class="form-input bg-white text-slate-900 border-slate-300"
                            placeholder="Nama pengirim/penerima luar (jika non-staf)...">
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label text-slate-700 font-medium text-xs block mb-1">Tujuan / Keterangan <span class="text-rose-500">*</span></label>
                        <textarea name="description" class="form-textarea bg-white text-slate-900 border-slate-300" rows="2"
                            placeholder="Contoh: Pengadaan, Pasang Baru, Maintenance, dll" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2 rounded-b-2xl">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                        onclick="closeCustomModal('addMutationModal')">Batal</button>
                    <button type="submit" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold">Simpan Mutasi</button>
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

            function toggleMutationFields() {
                const type = $('#mutation_type').val();
                if (type === 'Masuk') {
                    $('#label_mutation_person').text('Penerima / Admin');
                } else {
                    $('#label_mutation_person').text('Pengambil / Teknisi');
                }
            }

            $(document).ready(function() {
                const table = $('#transactionTable').DataTable({
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
                });

                $('#custom-search').on('keyup', function() {
                    table.search(this.value).draw();
                });

                $('.modal-overlay').on('click', function(e) {
                    if (e.target === this) closeCustomModal(this.id);
                });
            });
        </script>
    @endsection
</x-layouts.app>
