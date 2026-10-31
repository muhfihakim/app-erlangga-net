<x-layouts.app>
    @section('Css')
        <style>
            #kasbon-table td,
            .admin-table td {
                padding: 10px 14px;
                font-size: 13px;
                vertical-align: middle;
            }

            #kasbon-table th,
            .admin-table th {
                padding: 11px 14px;
                font-size: 12px;
                font-weight: 700;
                color: #64748b;
                letter-spacing: 0.05em;
                text-transform: uppercase !important;
                vertical-align: middle;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 0.75rem;
                margin-bottom: 1.25rem;
            }

            .stat-card {
                background: #fff;
                border-radius: 12px;
                padding: 0.75rem 1rem;
                border: 1px solid #e2e8f0;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
                display: flex;
                align-items: center;
                gap: 12px;
                min-height: 72px;
                transition: all 0.2s;
            }

            .stat-card:hover {
                border-color: #cbd5e1;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                min-width: 40px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
                flex-shrink: 0;
            }

            .stat-val {
                font-size: 17px;
                font-weight: 800;
                color: #0f172a;
                line-height: 1.25;
                display: flex;
                align-items: baseline;
                gap: 5px;
                flex-wrap: wrap;
            }

            .stat-lbl {
                font-size: 12px;
                font-weight: 600;
                color: #64748b;
                margin-top: 2px;
            }

            .dataTables_wrapper {
                position: relative;
                min-height: 200px;
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
                background: #0ea5e9 !important;
                border-color: #0ea5e9 !important;
                color: #fff !important;
            }
        </style>
    @endsection

    <!-- Page Header -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Kasbon Teknisi
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Pengelolaan dan pencatatan kasbon operasional teknisi.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="button" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold !flex !items-center !justify-center gap-1.5 shadow-sm"
                onclick="openCustomModal('kasbonModal')">
                Tambah
            </button>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#be123c;">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-rose-600">Rp {{ number_format($totalKasbon, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Siklus Aktif</div>
                <div class="stat-lbl truncate">Kasbon Periode Ini</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#ffedd5;color:#ea580c;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-orange-600">Rp {{ number_format($totalKasbonPrevious ?? 0, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Siklus Sebelumnya</div>
                <div class="stat-lbl truncate">Kasbon Periode Sebelumnya</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fce7f3;color:#db2777;">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-pink-600">Rp {{ number_format($totalKasbonThisMonth ?? 0, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</div>
                <div class="stat-lbl truncate">Total Kasbon Bulan Ini</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('kasbons.index') }}" class="w-full">
            <div class="filter-group">
                <!-- Filter Teknisi -->
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label-custom">Teknisi</label>
                    <select name="teknisi_id" class="form-input-custom !h-[38px] text-xs w-full">
                        <option value="">-- Semua Teknisi --</option>
                        @foreach ($teknisis as $t)
                            <option value="{{ $t->id }}" {{ $teknisiId == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Periode Mulai -->
                <div style="width: 150px;">
                    <label class="form-label-custom">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-input-custom !h-[38px] text-xs w-full"
                        value="{{ $start }}">
                </div>

                <!-- Periode Selesai -->
                <div style="width: 150px;">
                    <label class="form-label-custom">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-input-custom !h-[38px] text-xs w-full"
                        value="{{ $end }}">
                </div>

                <!-- Live Search -->
                <div style="flex: 1.5; min-width: 200px;">
                    <label class="form-label-custom">Pencarian</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input !h-[38px] text-xs" id="custom-search"
                            placeholder="Cari kasbon..." autocomplete="off" />
                    </div>
                </div>

                <!-- Action Filter -->
                <div class="flex items-end">
                    <button type="submit"
                        class="btn-primary-admin !h-[38px] !px-4 text-xs font-semibold flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Kasbon Card & Table -->
    <div class="table-card mt-4 mb-6">
        <div class="table-responsive">
            <table id="kasbon-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th style="width: 120px; text-align: center;">Tanggal</th>
                        <th style="width: 220px; text-align: left;">Teknisi</th>
                        <th style="text-align: left;">Keterangan</th>
                        <th style="width: 170px; text-align: right; padding-right: 24px;">Nominal</th>
                        <th style="width: 100px; text-align: center;">Bukti</th>
                        <th style="width: 70px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kasbons as $k)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                            <td style="text-align: center; color: #64748b; font-weight: 600;">
                                {{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') }}
                            </td>
                            <td style="text-align: left;">
                                <div class="font-bold text-slate-800 text-[13px]">{{ $k->teknisi->nama_lengkap ?? 'Unknown' }}</div>
                                <div class="text-[11px] text-slate-400 font-medium">Teknisi</div>
                            </td>
                            <td style="text-align: left; color: #475569;">
                                {{ $k->keterangan ?: '-' }}
                            </td>
                            <td style="text-align: right; padding-right: 24px;">
                                <span class="font-bold text-rose-600 text-[13px]">
                                    Rp {{ number_format($k->nominal, 0, ',', '.') }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if ($k->bukti)
                                    <a href="{{ Storage::url($k->bukti) }}" target="_blank"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-sky-50 text-sky-600 hover:bg-sky-100 border border-sky-200 transition-colors">
                                        <i class="fa-solid fa-paperclip text-[10px]"></i> Bukti
                                    </a>
                                @else
                                    <span class="text-slate-300 text-xs font-medium">-</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!bg-rose-50"
                                    title="Hapus"
                                    onclick="confirmGlobalDelete('{{ route('kasbons.destroy', $k->id) }}', 'Kasbon {{ addslashes($k->teknisi->nama_lengkap ?? '') }} (Rp {{ number_format($k->nominal, 0, ',', '.') }})')">
                                    <i class="fa-solid fa-trash text-rose-500 text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Kasbon -->
    <div class="modal-overlay" id="kasbonModal">
        <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Tambah Kasbon Teknisi</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Catat pinjaman atau kasbon operasional teknisi.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('kasbonModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form action="{{ route('kasbons.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body bg-white p-6">
                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Teknisi <span class="text-rose-500">*</span></label>
                        <select name="teknisi_id" class="form-input-custom !font-normal w-full bg-white text-slate-800 border-slate-300 text-xs" required>
                            <option value="">-- Pilih Teknisi --</option>
                            @foreach ($teknisis as $t)
                                <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-grid-2 mb-3.5">
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Nominal (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" name="nominal" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" required
                                placeholder="Contoh: 50000" min="0" step="1000">
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Tanggal <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" required
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Bukti Pembayaran / Nota (Opsional)</label>
                        <input type="file" name="bukti" class="form-input !font-normal bg-white text-slate-700 border-slate-300 text-xs py-1.5"
                            accept="image/jpeg,image/png,application/pdf" capture="environment">
                        <span class="text-[11px] text-slate-400 mt-1 block">Format: JPG, PNG, atau PDF (Maks. 5MB)</span>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-textarea !font-normal bg-white text-slate-800 border-slate-300 text-xs" rows="2"
                            placeholder="Catatan atau alasan kasbon..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2.5">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                        onclick="closeCustomModal('kasbonModal')">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !h-9 !px-5 text-xs font-semibold">
                        Simpan
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
