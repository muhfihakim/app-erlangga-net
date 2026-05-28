<x-layouts.app>
    @section('Css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
                transition: border-color 0.3s ease;
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
                transition: all .2s;
                text-decoration: none !important;
            }

            .paginate_button:hover:not(.disabled):not(.current) {
                border-color: #0ea5e9;
                color: #0ea5e9 !important;
                background: #f0f9ff;
            }

            .paginate_button.current {
                background: #0ea5e9;
                border-color: #0ea5e9;
                color: #fff !important;
            }

            .paginate_button.disabled {
                opacity: 0.35;
                cursor: not-allowed;
            }

            table.dataTable.no-footer {
                border-bottom: none;
            }

            /* Toolbar & Search */
            .table-toolbar {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1rem 1.25rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
                margin-bottom: 1rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }

            .search-wrap {
                position: relative;
                flex: 1;
                min-width: 200px;
                max-width: 360px;
            }

            .search-icon {
                position: absolute;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                color: #94a3b8;
                font-size: 13px;
                pointer-events: none;
            }

            .search-input {
                width: 100%;
                padding: 9px 12px 9px 36px;
                border: 2px solid #e2e8f0;
                border-radius: 10px;
                font-size: 13px;
                font-family: 'Inter', sans-serif;
                color: #374151;
                outline: none;
                transition: all .25s;
                background: #f8fafc;
            }

            .search-input:focus {
                border-color: #0ea5e9;
                background: #fff;
            }

            /* Select2 Custom Styling to match modern UI */
            .select2-container--bootstrap4 .select2-selection--single {
                height: 42px !important;
                border: 2px solid #cbd5e1 !important;
                border-radius: 10px !important;
                background-color: #fff !important;
                transition: all .2s ease;
                display: flex;
                align-items: center;
            }

            .select2-container--bootstrap4.select2-container--focus .select2-selection--single {
                border-color: #0ea5e9 !important;
                box-shadow: 0 0 0 3px rgba(14, 165, 233, .12) !important;
            }

            .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
                color: #0f172a !important;
                font-size: 13px !important;
                font-weight: 500 !important;
                padding-left: 12px !important;
                line-height: 38px !important;
            }

            .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
                height: 38px !important;
                top: 2px !important;
                right: 8px !important;
            }

            .select2-dropdown {
                border: 2px solid #e2e8f0 !important;
                border-radius: 12px !important;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
                overflow: hidden !important;
                z-index: 9999 !important;
            }

            .select2-results__option {
                padding: 8px 12px !important;
                font-size: 13px !important;
            }

            .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
                background-color: #0ea5e9 !important;
            }
        </style>
    @endsection

    @php $is_admin = Auth::user()->jabatan === 'Administrator'; @endphp

    <!-- Page header -->
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Pemeliharaan (Maintenance)</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola tugas perbaikan dan pemeliharaan jaringan
                pelanggan.</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if ($is_admin)
                <button type="button" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                    onclick="openCustomModal('addMaintenanceModal')">
                    <i class="fa-solid fa-plus-square"></i> Tambah Maintenance
                </button>
            @endif
            <a href="{{ route('dashboards.index') }}"
                class="btn-primary-admin !flex !items-center !justify-center gap-2"
                style="background: #f1f5f9; color: #475569; box-shadow: none; border: 1px solid #e2e8f0;"
                onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Stats cards -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalThisMonth }}</div>
                <div class="stat-lbl">Maintenance Bulan Ini</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="fa-solid fa-calendar-week"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalThisWeek }}</div>
                <div class="stat-lbl">Maintenance Minggu Ini</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalCompletedThisMonth }}</div>
                <div class="stat-lbl">Total Selesai (Bulan Ini)</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef2f2;color:#dc2626;">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <div class="stat-val">{{ $allMaintenances->where('status', 'Pending')->count() }}</div>
                <div class="stat-lbl">Total Pending</div>
            </div>
        </div>
    </div>

    @if ($is_admin)
        <!-- Toolbar -->
        <div class="table-toolbar">
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="search" class="search-input custom-search"
                    placeholder="Cari pelanggan, teknisi, atau status..." autocomplete="off" />
            </div>
            <div style="display:flex;gap:8px;">
                <div
                    style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    Data Keseluruhan
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-responsive">
                <table id="admin-maintenance-table" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">#</th>
                            <th style="text-align: center; width: 250px;">Pelanggan</th>
                            <th style="text-align: center; width: 120px;">Status</th>
                            <th style="text-align: center; width: 200px;">Teknisi</th>
                            <th style="text-align: center; width: 150px;">Waktu Selesai</th>
                            <th style="text-align: center; width: 150px;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allMaintenances as $m)
                            @php
                                $deadline = $m->delegated_at
                                    ? \Carbon\Carbon::parse($m->delegated_at)->addDays(2)
                                    : null;
                                $isLocked = $m->status === 'Pending' && $deadline && now()->greaterThan($deadline);
                                $isTrashed = $m->customer && $m->customer->trashed();
                            @endphp
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                    {{ $loop->iteration }}</td>
                                <td style="text-align: left;">
                                    <div style="font-weight: 700; color: #0f172a;">
                                        {{ $m->customer->nama_lengkap ?? 'N/A' }}
                                        @if ($isTrashed)
                                            <span class="badge badge-archived"
                                                style="font-size: 9px; margin-left: 4px;">NON-AKTIF</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                        ID: {{ $m->customer->id_pelanggan ?? '-' }} | PPPoE:
                                        {{ $m->customer->pppoe_username ?? '-' }}
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    @if ($m->status === 'Selesai')
                                        <span class="badge badge-published">SELESAI</span>
                                    @elseif($isLocked)
                                        <span class="badge badge-archived">LOCKED</span>
                                    @else
                                        <span class="badge"
                                            style="background: #fef9c3; color: #854d0e; padding: 4px 10px; border-radius: 6px; font-size: 10px;">PENDING</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <div style="font-weight: 600; color: #475569; font-size: 12px;">
                                        {{ $m->teknisiDelegasi->nama_lengkap ?? 'Tugas Umum' }}
                                    </div>
                                    @if ($m->teknisiEksekutor)
                                        <div style="font-size: 10px; color: #94a3b8;">Eksekutor:
                                            {{ $m->teknisiEksekutor->nama_lengkap }}</div>
                                    @endif
                                    @if ($m->customer && $m->customer->partner)
                                        <div style="font-size: 10px; color: #94a3b8;">Partner:
                                            {{ $m->customer->partner }}
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align: center; color: #64748b; font-size: 12px; font-weight: 600;">
                                    {{ $m->completed_at ? \Carbon\Carbon::parse($m->completed_at)->format('d-m-Y H:i') : '-' }}
                                </td>
                                <td style="text-align: center;">
                                    <div
                                        style="display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap;">
                                        {{-- Detail Tugas --}}
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            title="Detail Tugas" onclick="openMaintenanceDetailModal(this)"
                                            data-nama="{{ $m->customer->nama_lengkap ?? 'N/A' }}"
                                            data-id-pel="{{ $m->customer->id_pelanggan ?? '-' }}"
                                            data-pppoe="{{ $m->customer->pppoe_username ?? '-' }}"
                                            data-wa="{{ $m->customer->no_whatsapp ?? '-' }}"
                                            data-tikor="{{ $m->customer->preOrder->titik_koordinat ?? 'N/A' }}"
                                            data-alamat="{{ $m->customer->alamat_lengkap ?? '-' }}"
                                            data-kendala="{{ $m->keterangan_kendala }}"
                                            data-foto="{{ $m->foto_kendala_awal ? asset('storage/' . $m->foto_kendala_awal) : '' }}">
                                            <i class="fa-solid fa-circle-info text-primary"></i>
                                        </button>

                                        @if ($m->status === 'Selesai')
                                            {{-- Hasil Pengerjaan --}}
                                            <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                                title="Hasil Pengerjaan" onclick="openMaintenanceResultModal(this)"
                                                data-nama="{{ $m->customer->nama_lengkap ?? 'N/A' }}"
                                                data-eksekutor="{{ $m->teknisiEksekutor->nama_lengkap ?? 'N/A' }}"
                                                data-partner="{{ $m->customer->partner ?: '-' }}"
                                                data-solusi="{{ $m->solusi_kendala }}"
                                                data-waktu="{{ \Carbon\Carbon::parse($m->completed_at)->format('d-m-Y H:i') }}"
                                                data-foto-before="{{ asset('storage/' . $m->foto_sebelum_perbaikan) }}"
                                                data-foto-after="{{ asset('storage/' . $m->foto_setelah_perbaikan) }}"
                                                data-foto-speed="{{ asset('storage/' . $m->foto_speedtest) }}"
                                                data-foto-selfie="{{ asset('storage/' . $m->foto_selfie_teknisi) }}">
                                                <i class="fa-solid fa-file-circle-check text-emerald-500"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                class="topbar-icon-btn !w-9 !h-9 !rounded-xl {{ $isLocked ? 'text-rose-500' : 'text-sky-500' }}"
                                                title="{{ $isLocked ? 'Locked / Reassign' : 'Tugaskan' }}"
                                                onclick="openCustomModal('reassignModal', this)"
                                                data-id="{{ $m->id }}"
                                                data-nama="{{ $m->customer->nama_lengkap ?? 'N/A' }}">
                                                <i class="fa-solid {{ $isLocked ? 'fa-lock' : 'fa-user-gear' }}"></i>
                                            </button>
                                        @endif

                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            title="Hapus"
                                            onclick="confirmGlobalDelete('{{ route('maintenances.destroy', $m->id) }}', '{{ $m->customer->nama_lengkap ?? 'N/A' }}')">
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
        {{-- TAMPILAN TEKNISI --}}
        <!-- Toolbar -->
        <div class="table-toolbar">
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="search" class="search-input custom-search"
                    placeholder="Cari nama pelanggan, kendala, atau alamat..." autocomplete="off" />
            </div>
            <div style="display:flex;gap:8px;">
                <div
                    style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    Tugas Aktif
                </div>
            </div>
        </div>

        <div style="margin-bottom: 2rem;">
            <div
                style="display: flex; align-items: center; gap: 8px; margin-bottom: 1rem; border-left: 4px solid #0ea5e9; padding-left: 12px;">
                <h2 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">1. Tugas Delegasi Khusus
                    (Prioritas)</h2>
            </div>
            <div class="table-card">
                <div class="table-responsive">
                    <table id="delegated-table" class="admin-table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 50px;">#</th>
                                <th style="text-align: center; width: 250px;">Pelanggan</th>
                                <th style="text-align: center; width: 150px;">Deadline</th>
                                <th style="text-align: center; width: 150px;">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($delegatedTasks as $m)
                                @php $isLocked = now()->greaterThan(\Carbon\Carbon::parse($m->delegated_at)->addDays(2)); @endphp
                                <tr>
                                    <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                        {{ $loop->iteration }}</td>
                                    <td style="text-align: left;">
                                        <div style="font-weight: 700; color: #0f172a;">
                                            {{ $m->customer->nama_lengkap }}</div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                            ID: {{ $m->customer->id_pelanggan }} | WA:
                                            {{ $m->customer->no_whatsapp }}
                                        </div>
                                    </td>
                                    <td style="text-align: center; font-weight: 700; color: #f43f5e;">
                                        {{ \Carbon\Carbon::parse($m->delegated_at)->addDays(2)->format('d/m H:i') }}
                                    </td>
                                    <td style="text-align: center;">
                                        <div
                                            style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                            <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                                title="Detail" onclick="openMaintenanceDetailModal(this)"
                                                data-nama="{{ $m->customer->nama_lengkap }}"
                                                data-id-pel="{{ $m->customer->id_pelanggan }}"
                                                data-pppoe="{{ $m->customer->pppoe_username }}"
                                                data-wa="{{ $m->customer->no_whatsapp }}"
                                                data-tikor="{{ $m->customer->preOrder->titik_koordinat ?? 'N/A' }}"
                                                data-alamat="{{ $m->customer->alamat_lengkap }}"
                                                data-kendala="{{ $m->keterangan_kendala }}"
                                                data-foto="{{ $m->foto_kendala_awal ? asset('storage/' . $m->foto_kendala_awal) : '' }}">
                                                <i class="fa-solid fa-circle-info text-primary"></i>
                                            </button>
                                            @if ($isLocked)
                                                <span class="badge badge-archived !px-3 !py-2">LOCKED</span>
                                            @else
                                                <button type="button"
                                                    class="btn-primary-admin !h-9 !px-3 !text-[11px] activate-btn !flex !items-center !justify-center gap-1"
                                                    onclick="openCompleteModal(this)" data-id="{{ $m->id }}"
                                                    data-nama="{{ $m->customer->nama_lengkap }}">
                                                    <i class="fa-solid fa-circle-check"></i> Selesaikan
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
        </div>

        <div>
            <div
                style="display: flex; align-items: center; gap: 8px; margin-bottom: 1rem; border-left: 4px solid #64748b; padding-left: 12px;">
                <h2 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">2. Tugas Umum</h2>
            </div>
            <div class="table-card">
                <div class="table-responsive">
                    <table id="general-table" class="admin-table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 50px;">#</th>
                                <th style="text-align: center; width: 250px;">Pelanggan</th>
                                <th style="text-align: center; width: 150px;">Kendala</th>
                                <th style="text-align: center; width: 150px;">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($generalTasks as $m)
                                <tr>
                                    <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                        {{ $loop->iteration }}</td>
                                    <td style="text-align: left;">
                                        <div style="font-weight: 700; color: #0f172a;">
                                            {{ $m->customer->nama_lengkap }}</div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                            ID: {{ $m->customer->id_pelanggan }} | PPPoE:
                                            {{ $m->customer->pppoe_username }}
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span
                                            style="font-size: 11px; font-weight: 600; color: #475569;">{{ Str::limit($m->keterangan_kendala, 30) }}</span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div
                                            style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                            <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                                title="Detail" onclick="openMaintenanceDetailModal(this)"
                                                data-nama="{{ $m->customer->nama_lengkap }}"
                                                data-id-pel="{{ $m->customer->id_pelanggan }}"
                                                data-pppoe="{{ $m->customer->pppoe_username }}"
                                                data-wa="{{ $m->customer->no_whatsapp }}"
                                                data-tikor="{{ $m->customer->preOrder->titik_koordinat ?? 'N/A' }}"
                                                data-alamat="{{ $m->customer->alamat_lengkap }}"
                                                data-kendala="{{ $m->keterangan_kendala }}"
                                                data-foto="{{ $m->foto_kendala_awal ? asset('storage/' . $m->foto_kendala_awal) : '' }}">
                                                <i class="fa-solid fa-circle-info text-primary"></i>
                                            </button>
                                            <button type="button"
                                                class="btn-primary-admin !py-1.5 !px-3 !text-[11px] !bg-emerald-600 !flex !items-center !justify-center gap-1"
                                                onclick="openCompleteModal(this)" data-id="{{ $m->id }}"
                                                data-nama="{{ $m->customer->nama_lengkap }}">
                                                <i class="fa-solid fa-hand-holding-hand"></i> Ambil
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL AREA --}}

    {{-- Modal Detail Maintenance --}}
    <div class="modal-overlay" id="maintenanceDetailModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-circle-info text-primary"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Detail Pemeliharaan</span>
                </div>
                <button class="modal-close" onclick="closeCustomModal('maintenanceDetailModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 block uppercase">Nama Pelanggan</span>
                            <p id="det_nama" class="text-base font-black text-slate-900 mt-0.5"></p>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 block uppercase">ID Pelanggan /
                                PPPoE</span>
                            <p class="text-sm font-bold text-slate-700 mt-0.5">
                                <span id="det_id_pel"></span> / <code id="det_pppoe"
                                    class="bg-slate-100 px-1.5 py-0.5 rounded text-sky-600"></code>
                            </p>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 block uppercase">No. WhatsApp</span>
                            <div class="flex items-center gap-2 mt-1">
                                <p id="det_wa" class="text-sm font-bold text-emerald-600 m-0"></p>
                                <a id="det_wa_link" href="#" target="_blank"
                                    class="bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded text-[10px] font-black no-underline">
                                    <i class="fa-brands fa-whatsapp"></i> CHAT
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 block uppercase">Titik Koordinat</span>
                            <div class="flex items-center gap-2 mt-1">
                                <p id="det_tikor" class="text-sm font-bold text-indigo-600 m-0"></p>
                                <a id="det_tikor_link" href="#" target="_blank"
                                    class="bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded text-[10px] font-black no-underline">
                                    <i class="fa-solid fa-map-location-dot"></i> MAPS
                                </a>
                            </div>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 block uppercase">Alamat Lengkap</span>
                            <p id="det_alamat" class="text-sm font-medium text-slate-600 leading-relaxed mt-0.5"></p>
                        </div>
                    </div>
                </div>
                <hr class="border-slate-100">
                <div class="space-y-3">
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Keterangan Kendala</span>
                    <div class="bg-rose-50 border border-rose-100 p-4 rounded-xl">
                        <p id="det_kendala" class="text-sm font-bold text-rose-700 m-0 leading-relaxed"></p>
                    </div>
                </div>
                <div id="det_foto_container" class="space-y-3 hidden">
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Foto Kendala Awal</span>
                    <div class="aspect-video w-full rounded-2xl overflow-hidden border-2 border-slate-100 shadow-sm bg-slate-50 flex items-center justify-center cursor-pointer"
                        onclick="window.open($('#det_foto').attr('src'), '_blank')">
                        <img id="det_foto" src="" class="w-full h-full object-contain">
                    </div>
                </div>
            </div>
            <div class="modal-footer !bg-white border-t border-slate-200 rounded-b-2xl">
                <button type="button"
                    class="btn-cancel w-full !py-3.5 !text-sm !rounded-xl !bg-slate-100 !text-slate-600 hover:!bg-slate-200 !flex !items-center !justify-center gap-2 transition-colors"
                    onclick="closeCustomModal('maintenanceDetailModal')">
                    <i class="fa-solid fa-xmark"></i> Tutup Detail
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Hasil Maintenance --}}
    <div class="modal-overlay" id="maintenanceResultModal">
        <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-file-circle-check text-emerald-500"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Hasil Pengerjaan Pemeliharaan</span>
                </div>
                <button class="modal-close" onclick="closeCustomModal('maintenanceResultModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-8">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="space-y-4">
                            <h6
                                class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-l-4 border-emerald-500 pl-3">
                                Informasi Tim</h6>
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-4">
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Pelanggan</span>
                                    <p id="res_nama" class="text-sm font-black text-slate-900"></p>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Teknisi
                                        Eksekutor</span>
                                    <p id="res_eksekutor" class="text-sm font-bold text-slate-700"></p>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Partner /
                                        Rekan</span>
                                    <p id="res_partner" class="text-sm font-bold text-slate-700"></p>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Waktu
                                        Selesai</span>
                                    <p id="res_waktu" class="text-sm font-bold text-slate-700"></p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <h6
                                class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-l-4 border-sky-500 pl-3">
                                Solusi Perbaikan</h6>
                            <div class="bg-sky-50 p-4 rounded-2xl border border-sky-100">
                                <p id="res_solusi" class="text-sm font-bold text-sky-700 leading-relaxed italic m-0">
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="lg:col-span-3 space-y-4">
                        <h6
                            class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-l-4 border-amber-500 pl-3">
                            Dokumentasi Lapangan</h6>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5 text-center">
                                <span class="text-[9px] font-black text-slate-400 uppercase">Sebelum</span>
                                <div class="aspect-square rounded-xl overflow-hidden border-2 border-white shadow-sm bg-slate-100 cursor-pointer"
                                    onclick="window.open($('#res_before').attr('src'), '_blank')">
                                    <img id="res_before" src="" class="w-full h-full object-cover">
                                </div>
                            </div>
                            <div class="space-y-1.5 text-center">
                                <span class="text-[9px] font-black text-slate-400 uppercase">Sesudah</span>
                                <div class="aspect-square rounded-xl overflow-hidden border-2 border-white shadow-sm bg-slate-100 cursor-pointer"
                                    onclick="window.open($('#res_after').attr('src'), '_blank')">
                                    <img id="res_after" src="" class="w-full h-full object-cover">
                                </div>
                            </div>
                            <div class="space-y-1.5 text-center">
                                <span class="text-[9px] font-black text-slate-400 uppercase">Speedtest</span>
                                <div class="aspect-square rounded-xl overflow-hidden border-2 border-white shadow-sm bg-slate-100 cursor-pointer"
                                    onclick="window.open($('#res_speed').attr('src'), '_blank')">
                                    <img id="res_speed" src="" class="w-full h-full object-cover">
                                </div>
                            </div>
                            <div class="space-y-1.5 text-center">
                                <span class="text-[9px] font-black text-slate-400 uppercase">Selfie Teknisi</span>
                                <div class="aspect-square rounded-xl overflow-hidden border-2 border-white shadow-sm bg-slate-100 cursor-pointer"
                                    onclick="window.open($('#res_selfie').attr('src'), '_blank')">
                                    <img id="res_selfie" src="" class="w-full h-full object-cover">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button type="button" class="btn-cancel w-full !py-3.5"
                    onclick="closeCustomModal('maintenanceResultModal')">
                    Tutup Laporan
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Cari Pelanggan --}}
    <div class="modal-overlay" id="searchCustomerModal" style="z-index: 10001;">
        <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-users text-primary"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Cari & Pilih Pelanggan</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('searchCustomerModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-6">
                <div class="search-wrap mb-4 !max-w-full">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="search" class="search-input" id="customer-modal-search"
                        placeholder="Ketik nama, ID pelanggan, atau alamat..." autocomplete="off" />
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table id="customer-search-table" class="admin-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Pelanggan</th>
                                <th>ID Pelanggan</th>
                                <th>Alamat</th>
                                <th style="width: 80px; text-align: center;">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customers as $c)
                                <tr>
                                    <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                        {{ $loop->iteration }}</td>
                                    <td style="font-weight: 700; color: #0f172a;">{{ $c->nama_lengkap }}</td>
                                    <td style="font-weight: 600; color: #64748b;">{{ $c->id_pelanggan }}</td>
                                    <td style="font-size: 11px; color: #64748b;">
                                        {{ Str::limit($c->alamat_lengkap, 50) }}</td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-primary-admin !py-1.5 !px-3 !text-[11px]"
                                            onclick="selectCustomer('{{ $c->id }}', '{{ $c->nama_lengkap }} ({{ $c->id_pelanggan }})')">
                                            Pilih
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button type="button" class="btn-cancel w-full" onclick="closeCustomModal('searchCustomerModal')">
                    <i class="fa-solid fa-xmark"></i> Batal
                </button>
            </div>
        </div>
    </div>
    <div class="modal-overlay" id="addMaintenanceModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <form action="{{ route('maintenances.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                    <div class="modal-title">
                        <i class="fa-solid fa-plus-square text-primary"></i>
                        <span class="text-slate-900 font-bold text-[17px]">Buat Tugas Pemeliharaan</span>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('addMaintenanceModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body p-8 space-y-6 bg-white">
                    <div class="form-group">
                        <label class="form-label !text-slate-800 !font-bold">
                            <i class="fa-solid fa-user-tag text-primary mr-1"></i> Pelanggan Terpilih <span
                                class="text-rose-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <input type="hidden" name="customer_id" id="selected_customer_id" required>
                                <input type="text" id="selected_customer_name"
                                    class="form-input !bg-slate-50 cursor-not-allowed"
                                    placeholder="Belum ada pelanggan terpilih..." readonly required>
                            </div>
                            <button type="button"
                                class="btn-primary-admin !shadow-none !px-4 !h-[42px] !flex !items-center !justify-center gap-2"
                                onclick="openCustomModal('searchCustomerModal')">
                                <i class="fa-solid fa-magnifying-glass"></i> Cari Pelanggan
                            </button>
                        </div>
                    </div>

                    <div id="customer_preview" class="hidden">
                        <div class="bg-sky-50 rounded-2xl border border-sky-100 p-5 space-y-4">
                            <div>
                                <span class="text-[10px] font-black text-sky-400 block uppercase tracking-wider">PPPoE
                                    Username</span>
                                <p id="pppoe_val" class="text-sm font-bold text-sky-700 m-0 mt-1"></p>
                            </div>
                            <hr class="border-sky-100/50 m-0">
                            <div>
                                <span class="text-[10px] font-black text-sky-400 block uppercase tracking-wider">Alamat
                                    Pemasangan</span>
                                <p id="alamat_val"
                                    class="text-sm font-medium text-slate-600 m-0 mt-1 leading-relaxed">
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label !text-slate-800 !font-bold">
                                <i class="fa-solid fa-user-gear text-slate-400 mr-1"></i> Tugaskan Ke
                            </label>
                            <select name="id_teknisi_delegasi" class="form-select">
                                <option value="">-- Tugas Umum --</option>
                                @foreach ($teknisis as $t)
                                    <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-400 italic mt-1.5">*Opsional, bisa dikosongkan</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label !text-slate-800 !font-bold">
                                <i class="fa-solid fa-camera text-slate-400 mr-1"></i> Foto Kendala
                            </label>
                            <input type="file" name="foto_kendala_awal" class="form-input" accept="image/*">
                            <p class="text-[10px] text-slate-400 italic mt-1.5">*Opsional, jika ada foto awal</p>
                        </div>
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label !text-slate-800 !font-bold">
                            <i class="fa-solid fa-comment-dots text-slate-400 mr-1"></i> Deskripsi Kendala <span
                                class="text-rose-500">*</span>
                        </label>
                        <textarea name="keterangan_kendala" class="form-textarea" required rows="3"
                            placeholder="Jelaskan detail kendala (Contoh: Los Merah, Router Mati, dsb)"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        onclick="closeCustomModal('addMaintenanceModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-save !flex !items-center !justify-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Tugas Pemeliharaan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Reassign (Admin) --}}
    <div class="modal-overlay" id="reassignModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <form id="reassignForm" method="POST">
                @csrf
                <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                    <div class="modal-title">
                        <i class="fa-solid fa-user-gear text-sky-500"></i>
                        <span class="text-slate-900 font-bold text-[17px]">Tugaskan Ulang</span>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('reassignModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body p-6 space-y-4">
                    <div class="bg-sky-50 p-4 rounded-xl border border-sky-100 mb-4">
                        <span class="text-[10px] font-black text-sky-400 block uppercase">Pelanggan</span>
                        <p id="re_nama" class="text-base font-black text-sky-700 m-0"></p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-slate-500 uppercase">Pilih Teknisi Baru <span
                                class="text-rose-500">*</span></label>
                        <select name="id_teknisi_delegasi" class="form-control" required
                            style="border: 2px solid #e2e8f0; border-radius: 10px; font-size: 13px; padding: 10px; height: auto;">
                            <option value="">-- Pilih Teknisi --</option>
                            @foreach ($teknisis as $t)
                                <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        onclick="closeCustomModal('reassignModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-save !flex !items-center !justify-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Update Penugasan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Selesai (Teknisi) --}}
    <div class="modal-overlay" id="completeModal">
        <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span class="text-slate-900 font-bold">Laporan Penyelesaian: <span id="nama_p"></span></span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('completeModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-tabs bg-white border-b border-slate-200">
                <button class="modal-tab active" data-tab="tab-m-data">
                    <i class="fa-solid fa-file-invoice"></i> Data Penugasan
                </button>
                <button class="modal-tab" data-tab="tab-m-solusi">
                    <i class="fa-solid fa-gears"></i> Solusi Teknis
                </button>
                <button class="modal-tab" data-tab="tab-m-media">
                    <i class="fa-solid fa-camera"></i> Dokumentasi
                </button>
            </div>

            <form id="completeForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body bg-white p-6">
                    <!-- Tab Data Penugasan -->
                    <div class="tab-panel active" id="tab-m-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="bg-sky-50 p-5 rounded-2xl border border-sky-100">
                                    <span
                                        class="text-[10px] font-black text-sky-400 block uppercase tracking-wider mb-1">Status
                                        Laporan</span>
                                    <p class="text-sm font-bold text-sky-700 m-0">Menunggu penyelesaian teknisi di
                                        lapangan.</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Nama
                                        Pelanggan</label>
                                    <input type="text" class="form-input bg-slate-50 border-slate-200"
                                        id="m_nama_lengkap" readonly>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="form-group">
                                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Pilih
                                        Partner / Rekan Kerja</label>
                                    <div class="partner-card-scroll"
                                        style="max-height: 200px; overflow-y: auto; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px;">
                                        <div class="grid grid-cols-1 gap-1">
                                            @foreach ($teknisis as $t)
                                                @if ($t->id != auth()->id())
                                                    <div class="partner-item"
                                                        style="display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 8px; transition: background 0.2s;">
                                                        <input
                                                            class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary"
                                                            type="checkbox" name="partner_ids[]"
                                                            value="{{ $t->id }}"
                                                            id="m_partner_{{ $t->id }}">
                                                        <label
                                                            class="text-sm font-semibold text-slate-700 cursor-pointer w-full m-0"
                                                            for="m_partner_{{ $t->id }}">
                                                            {{ $t->nama_lengkap }}
                                                        </label>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-slate-400 italic mt-1.5">*Opsional, bisa dikosongkan
                                        jika bekerja sendiri.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Solusi Teknis -->
                    <div class="tab-panel" id="tab-m-solusi">
                        <div class="space-y-4">
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Solusi /
                                    Tindakan Perbaikan <span class="text-rose-500">*</span></label>
                                <textarea name="solusi_kendala" class="form-textarea" required rows="5"
                                    placeholder="Jelaskan secara detail tindakan yang telah dilakukan untuk memperbaiki kendala pelanggan..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Dokumentasi -->
                    <div class="tab-panel" id="tab-m-media">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Foto Sebelum
                                    <span class="text-rose-500">*</span></label>
                                <div
                                    class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl p-3 text-center">
                                    <input type="file" name="foto_sebelum" class="form-input !text-[10px]"
                                        required accept="image/*">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Foto Sesudah
                                    <span class="text-rose-500">*</span></label>
                                <div
                                    class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl p-3 text-center">
                                    <input type="file" name="foto_sesudah" class="form-input !text-[10px]"
                                        required accept="image/*">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Speedtest
                                    <span class="text-rose-500">*</span></label>
                                <div
                                    class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl p-3 text-center">
                                    <input type="file" name="foto_speedtest" class="form-input !text-[10px]"
                                        required accept="image/*">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Selfie Tim
                                    <span class="text-rose-500">*</span></label>
                                <div
                                    class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl p-3 text-center">
                                    <input type="file" name="foto_selfie" class="form-input !text-[10px]" required
                                        accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        onclick="closeCustomModal('completeModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-save !flex !items-center !justify-center gap-2 !bg-emerald-600">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Kirim Laporan Selesai
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
            // Modal Utilities
            function openCustomModal(modalId, triggerEl = null) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    if (modalId === 'reassignModal' && triggerEl) {
                        const $btn = $(triggerEl);
                        $('#re_nama').text($btn.data('nama'));
                        $('#reassignForm').attr('action', `/maintenances/${$btn.data('id')}/reassign`);
                    }
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

            // Detail & Result Modal Logic
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

                $('#res_before').attr('src', $btn.data('foto-before'));
                $('#res_after').attr('src', $btn.data('foto-after'));
                $('#res_speed').attr('src', $btn.data('foto-speed'));
                $('#res_selfie').attr('src', $btn.data('foto-selfie'));

                openCustomModal('maintenanceResultModal');
            }

            function openCompleteModal(triggerEl) {
                const $btn = $(triggerEl);
                const nama = $btn.data('nama');

                // Update UI elements
                $('#nama_p').text(nama);
                $('#m_nama_lengkap').val(nama);

                // Set form action
                $('#completeForm').attr('action', `/maintenances/${$btn.data('id')}/complete`);

                // Reset form and tabs
                $('#completeForm')[0].reset();

                // Re-fill name after reset since reset() clears the input
                $('#m_nama_lengkap').val(nama);

                // Reset tabs to first
                $('#completeModal .modal-tab').first().click();

                openCustomModal('completeModal');
            }

            function selectCustomer(id, name) {
                $('#selected_customer_id').val(id);
                $('#selected_customer_name').val(name);

                // Trigger preview logic
                if (id) {
                    fetch(`/maintenances/customer/${id}`)
                        .then(r => r.json())
                        .then(d => {
                            $('#pppoe_val').text(d.pppoe || '-');
                            $('#alamat_val').text(d.alamat || '-');
                            $('#customer_preview').slideDown(200);
                        });
                }

                closeCustomModal('searchCustomerModal');
            }

            $(document).ready(function() {
                // Shared DataTable Options
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

                const adminTable = $('#admin-maintenance-table').DataTable(dtOptions);
                const delegatedTable = $('#delegated-table').DataTable(dtOptions);
                const generalTable = $('#general-table').DataTable(dtOptions);

                // Customer Search Modal DataTable
                const customerSearchTable = $('#customer-search-table').DataTable({
                    ...dtOptions,
                    "pageLength": 5
                });

                // Custom Search for tables (Live Search)
                $('.custom-search').on('keyup', function() {
                    const val = this.value;
                    if (adminTable) adminTable.search(val).draw();
                    if (delegatedTable) delegatedTable.search(val).draw();
                    if (generalTable) generalTable.search(val).draw();
                });

                $('#customer-modal-search').on('keyup', function() {
                    customerSearchTable.search(this.value).draw();
                });

                // Tab Switching Logic
                $(document).on('click', '.modal-tab', function() {
                    const tabId = $(this).data('tab');
                    const $modalBox = $(this).closest('.modal-box');

                    // Update Tabs
                    $modalBox.find('.modal-tab').removeClass('active');
                    $(this).addClass('active');

                    // Update Panels
                    $modalBox.find('.tab-panel').removeClass('active');
                    $('#' + tabId).addClass('active');
                });

                // Select2 Setup
                $('.select2').each(function() {
                    const $p = $(this).closest('.modal-box');
                    $(this).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownParent: $p.length ? $p : null
                    });
                });

                // Admin Add Modal Customer Change
                $('#customer_select').on('change', function() {
                    const id = $(this).val();
                    if (id) {
                        fetch(`/maintenances/customer/${id}`)
                            .then(r => r.json())
                            .then(d => {
                                $('#pppoe_val').text(d.pppoe || '-');
                                $('#alamat_val').text(d.alamat || '-');
                                $('#customer_preview').slideDown(200);
                            });
                    } else {
                        $('#customer_preview').slideUp(200);
                    }
                });

                // Close modal on overlay click
                $('.modal-overlay').on('click', function(e) {
                    if (e.target === this) closeCustomModal(this.id);
                });
            });
        </script>
    @endsection
</x-layouts.app>
