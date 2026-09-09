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

            /* Full Capitalization for Table */
            #kasbon-table thead th,
            #kasbon-table tbody td {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 12px;
            }
        </style>
    @endsection

    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Kasbon Teknisi</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Pengelolaan data kasbon teknisi.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="button" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                onclick="openCustomModal('kasbonModal')">
                <i class="fa-solid fa-hand-holding-dollar"></i> Tambah Kasbon
            </button>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#ef4444;">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div>
                <div class="stat-val" style="color: #ef4444;">Rp {{ number_format($totalKasbon, 0, ',', '.') }}</div>
                <div class="stat-lbl">Total Kasbon Periode Ini</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#ffedd5;color:#f97316;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <div class="stat-val" style="color: #f97316;">Rp
                    {{ number_format($totalKasbonPrevious ?? 0, 0, ',', '.') }}</div>
                <div class="stat-lbl">Kasbon Periode Sebelumnya</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fce7f3;color:#ec4899;">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div>
                <div class="stat-val" style="color: #ec4899;">Rp
                    {{ number_format($totalKasbonThisMonth ?? 0, 0, ',', '.') }}</div>
                <div class="stat-lbl">Kasbon Bulan Ini</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Form -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('kasbons.index') }}" class="w-full">
            <div class="filter-group">
                <!-- Filter Teknisi -->
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label-custom">Teknisi</label>
                    <select name="teknisi_id" class="form-input-custom w-full">
                        <option value="">-- Semua --</option>
                        @foreach ($teknisis as $t)
                            <option value="{{ $t->id }}" {{ $teknisiId == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Periode -->
                <div style="width: 160px;">
                    <label class="form-label-custom">Mulai</label>
                    <input type="date" name="start_date" class="form-input-custom w-full"
                        value="{{ $start }}">
                </div>
                <div style="width: 160px;">
                    <label class="form-label-custom">Selesai</label>
                    <input type="date" name="end_date" class="form-input-custom w-full" value="{{ $end }}">
                </div>

                <!-- Live Search -->
                <div style="flex: 1.5; min-width: 200px;">
                    <label class="form-label-custom">Pencarian</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input" id="custom-search"
                            style="padding: 9px 12px 9px 36px; height: 40px; border-radius: 10px; font-weight: 600;"
                            placeholder="Cari kasbon..." autocomplete="off" />
                    </div>
                </div>

                <button type="submit"
                    class="btn-primary-admin !h-10 !px-6 !text-[13px] !w-fit flex items-center gap-2">
                    <i class="fa-solid fa-filter"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Kasbon Card -->
    <div class="table-card mt-6 mb-6">
        <div class="table-responsive">
            <table id="kasbon-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: center; width: 120px;">Tanggal</th>
                        <th style="text-align: center; width: 200px;">Teknisi</th>
                        <th style="text-align: center;">Keterangan</th>
                        <th style="text-align: center; width: 150px;">Nominal</th>
                        <th style="text-align: center; width: 100px;">Bukti</th>
                        <th style="text-align: center; width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kasbons as $k)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}
                            </td>
                            <td style="text-align: center; color: #64748b;">
                                {{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') }}</td>
                            <td style="text-align: center; font-weight: 700; color: #0f172a;">
                                {{ $k->teknisi->nama_lengkap ?? 'Unknown' }}</td>
                            <td style="text-align: left; color: #475569;">{{ $k->keterangan ?? '-' }}</td>
                            <td style="text-align: center; font-weight: 800; color: #ef4444;">Rp
                                {{ number_format($k->nominal, 0, ',', '.') }}</td>
                            <td style="text-align: center;">
                                @if ($k->bukti)
                                    <a href="{{ Storage::url($k->bukti) }}" target="_blank" class="badge"
                                        style="background:#f0f9ff;color:#0ea5e9;">Lihat</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                    onclick="confirmGlobalDelete('{{ route('kasbons.destroy', $k->id) }}', 'Kasbon {{ $k->teknisi->nama_lengkap ?? '' }}')">
                                    <i class="fa-solid fa-trash text-red-500"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Kasbon -->
    <div class="modal-overlay" id="kasbonModal">
        <div class="modal-box bg-white border border-slate-300 shadow-2xl max-w-lg">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-hand-holding-dollar text-emerald-500"></i> Tambah Kasbon
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('kasbonModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form action="{{ route('kasbons.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body bg-slate-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="form-group md:col-span-2">
                            <label class="form-label-custom">Teknisi <span class="text-red-500">*</span></label>
                            <select name="teknisi_id" class="form-input-custom w-full" required>
                                <option value="">-- Pilih Teknisi --</option>
                                @foreach ($teknisis as $t)
                                    <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label-custom">Nominal (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="nominal" class="form-input-custom w-full" required
                                placeholder="Contoh: 50000" min="0">
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label-custom">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal" class="form-input-custom w-full" required
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label-custom">Bukti (Opsional)</label>
                            <input type="file" name="bukti" class="form-input-custom w-full"
                                accept="image/jpeg,image/png,application/pdf" capture="environment">
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label-custom">Keterangan (Opsional)</label>
                            <textarea name="keterangan" class="form-input-custom w-full" rows="2" placeholder="Catatan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-white rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        onclick="closeCustomModal('kasbonModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-save !flex !items-center !justify-center gap-2">
                        <i class="fa-solid fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                const kasbonTable = $('#kasbon-table').DataTable({
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
                    kasbonTable.search(this.value).draw();
                });
            });
        </script>
    @endsection
</x-layouts.app>
