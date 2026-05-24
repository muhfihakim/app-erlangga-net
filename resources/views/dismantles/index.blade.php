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
            <h1 style="font-size:22px;font-weight:900;margin:0;">Pencabutan (Dismantle)</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola pencabutan perangkat dan non-aktivasi pelanggan.
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            @if ($is_admin)
                <button type="button" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                    onclick="openCustomModal('addDismantleModal')">
                    <i class="fa-solid fa-plus-square"></i> Tambah Dismantle
                </button>
            @endif
            <a href="{{ route('dashboards.index') }}" class="btn-primary-admin !flex !items-center !justify-center gap-2"
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
                <i class="fa-solid fa-house-circle-xmark"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalThisMonth }}</div>
                <div class="stat-lbl">Dismantle Bulan Ini</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="fa-solid fa-calendar-week"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalThisWeek }}</div>
                <div class="stat-lbl">Dismantle Minggu Ini</div>
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
                <div class="stat-val">{{ $allDismantles->where('status', 'Pending')->count() }}</div>
                <div class="stat-lbl">Total Pending</div>
            </div>
        </div>
    </div>

    @if ($is_admin)
        <!-- Toolbar -->
        <div class="table-toolbar">
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="search" class="search-input" id="custom-search"
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
                <table id="admin-dismantle-table" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">#</th>
                            <th style="text-align: center; width: 250px;">Pelanggan</th>
                            <th style="text-align: center; width: 120px;">Status</th>
                            <th style="text-align: center; width: 200px;">Teknisi</th>
                            <th style="text-align: center; width: 150px;">Waktu Selesai</th>
                            <th style="text-align: center; width: 180px;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allDismantles as $d)
                            @php $isTrashed = $d->customer && $d->customer->trashed(); @endphp
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                    {{ $loop->iteration }}</td>
                                <td style="text-align: left;">
                                    <div style="font-weight: 700; color: #0f172a;">{{ $d->customer->nama_lengkap ?? 'N/A' }}
                                        @if ($isTrashed)
                                            <span class="badge badge-archived"
                                                style="font-size: 9px; margin-left: 4px;">NON-AKTIF</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                        ID: {{ $d->customer->id_pelanggan ?? '-' }} | PPPoE:
                                        {{ $d->customer->pppoe_username ?? '-' }}
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    @if ($d->status === 'Selesai')
                                        <span class="badge badge-published">SELESAI</span>
                                    @else
                                        <span class="badge"
                                            style="background: #fef9c3; color: #854d0e; padding: 4px 10px; border-radius: 6px; font-size: 10px;">PENDING</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <div style="font-weight: 600; color: #475569; font-size: 12px;">
                                        {{ $d->teknisiEksekutor->nama_lengkap ?? ($d->teknisiDelegasi->nama_lengkap ?? 'Tugas Umum') }}
                                    </div>
                                    @if ($d->customer && $d->customer->sales)
                                        <div style="font-size: 10px; color: #94a3b8;">Sales:
                                            {{ $d->customer->sales->nama_lengkap }}</div>
                                    @endif
                                </td>
                                <td style="text-align: center; color: #64748b; font-size: 12px; font-weight: 600;">
                                    {{ $d->completed_at ? $d->completed_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td style="text-align: center;">
                                    <div
                                        style="display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap;">
                                        {{-- Detail Tugas --}}
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            title="Detail Tugas" onclick="openDismantleDetailModal(this)"
                                            data-nama="{{ $d->customer->nama_lengkap ?? 'N/A' }}"
                                            data-id-pel="{{ $d->customer->id_pelanggan ?? '-' }}"
                                            data-pppoe="{{ $d->customer->pppoe_username ?? '-' }}"
                                            data-wa="{{ $d->customer->no_whatsapp ?? '-' }}"
                                            data-tikor="{{ $d->customer->preOrder->titik_koordinat ?? 'N/A' }}"
                                            data-alamat="{{ $d->customer->alamat_lengkap ?? '-' }}"
                                            data-paket="{{ $d->customer->paket_internet ?? '-' }}"
                                            data-sales="{{ $d->customer->sales->nama_lengkap ?? 'N/A' }}"
                                            data-keterangan="{{ $d->keterangan_admin ?: '-' }}"
                                            data-foto="{{ $d->foto_pencabutan ? asset('storage/' . $d->foto_pencabutan) : '' }}">
                                            <i class="fa-solid fa-circle-info text-primary"></i>
                                        </button>

                                        @if ($d->status === 'Selesai')
                                            @if (!$isTrashed)
                                                <button type="button" class="btn-primary-admin !py-1.5 !px-3 !text-[11px] !bg-rose-600"
                                                    onclick="confirmDeleteCustomer({{ $d->customer_id }}, '{{ $d->customer->nama_lengkap }}')">
                                                    <i class="fa-solid fa-user-slash mr-1"></i> Hapus Pelanggan
                                                </button>
                                                <form id="del-cust-{{ $d->customer_id }}"
                                                    action="{{ route('dismantles.deleteCustomer', $d->customer_id) }}"
                                                    method="POST" style="display: none;">@csrf @method('DELETE')</form>
                                            @else
                                                <span class="badge badge-archived !px-3 !py-2">TERHAPUS</span>
                                            @endif
                                        @else
                                            <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                                title="Batalkan Tugas"
                                                onclick="confirmCancelDismantle({{ $d->id }}, '{{ $d->customer->nama_lengkap }}')">
                                                <i class="fa-solid fa-trash text-rose-600"></i>
                                            </button>
                                            <form id="cancel-task-{{ $d->id }}"
                                                action="{{ route('dismantles.destroy', $d->id) }}" method="POST"
                                                style="display: none;">@csrf @method('DELETE')</form>
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
        {{-- TAMPILAN TEKNISI --}}
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
                                <th style="text-align: center; width: 150px;">Delegasi Pada</th>
                                <th style="text-align: center; width: 150px;">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($delegatedTasks as $d)
                                <tr>
                                    <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                        {{ $loop->iteration }}</td>
                                    <td style="text-align: left;">
                                        <div style="font-weight: 700; color: #0f172a;">
                                            {{ $d->customer->nama_lengkap }}</div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                            ID: {{ $d->customer->id_pelanggan }} | WA:
                                            {{ $d->customer->no_whatsapp }}
                                        </div>
                                    </td>
                                    <td style="text-align: center; font-weight: 600; color: #64748b;">
                                        {{ $d->delegated_at ? $d->delegated_at->format('d/m H:i') : '-' }}
                                    </td>
                                    <td style="text-align: center;">
                                        <div
                                            style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                            <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                                title="Detail" onclick="openDismantleDetailModal(this)"
                                                data-nama="{{ $d->customer->nama_lengkap }}"
                                                data-id-pel="{{ $d->customer->id_pelanggan }}"
                                                data-pppoe="{{ $d->customer->pppoe_username }}"
                                                data-wa="{{ $d->customer->no_whatsapp }}"
                                                data-tikor="{{ $d->customer->preOrder->titik_koordinat ?? 'N/A' }}"
                                                data-alamat="{{ $d->customer->alamat_lengkap }}"
                                                data-paket="{{ $d->customer->paket_internet }}"
                                                data-sales="{{ $d->customer->sales->nama_lengkap ?? 'N/A' }}"
                                                data-keterangan="{{ $d->keterangan_admin ?: '-' }}"
                                                data-foto="">
                                                <i class="fa-solid fa-circle-info text-primary"></i>
                                            </button>
                                            <button type="button" class="btn-primary-admin !py-1.5 !px-3 !text-[11px]"
                                                onclick="openCompleteDismantleModal(this)" data-id="{{ $d->id }}"
                                                data-nama="{{ $d->customer->nama_lengkap }}">
                                                Selesai
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
                                <th style="text-align: center; width: 200px;">Keterangan</th>
                                <th style="text-align: center; width: 150px;">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($generalTasks as $d)
                                <tr>
                                    <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                        {{ $loop->iteration }}</td>
                                    <td style="text-align: left;">
                                        <div style="font-weight: 700; color: #0f172a;">
                                            {{ $d->customer->nama_lengkap }}</div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                            ID: {{ $d->customer->id_pelanggan }} | PPPoE:
                                            {{ $d->customer->pppoe_username }}
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span
                                            style="font-size: 11px; font-weight: 600; color: #475569;">{{ Str::limit($d->keterangan_admin ?: '-', 30) }}</span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div
                                            style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                            <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                                title="Detail" onclick="openDismantleDetailModal(this)"
                                                data-nama="{{ $d->customer->nama_lengkap }}"
                                                data-id-pel="{{ $d->customer->id_pelanggan }}"
                                                data-pppoe="{{ $d->customer->pppoe_username }}"
                                                data-wa="{{ $d->customer->no_whatsapp }}"
                                                data-tikor="{{ $d->customer->preOrder->titik_koordinat ?? 'N/A' }}"
                                                data-alamat="{{ $d->customer->alamat_lengkap }}"
                                                data-paket="{{ $d->customer->paket_internet }}"
                                                data-sales="{{ $d->customer->sales->nama_lengkap ?? 'N/A' }}"
                                                data-keterangan="{{ $d->keterangan_admin ?: '-' }}"
                                                data-foto="">
                                                <i class="fa-solid fa-circle-info text-primary"></i>
                                            </button>
                                            <button type="button"
                                                class="btn-primary-admin !py-1.5 !px-3 !text-[11px] !bg-emerald-600"
                                                onclick="openCompleteDismantleModal(this)" data-id="{{ $d->id }}"
                                                data-nama="{{ $d->customer->nama_lengkap }}">
                                                Ambil
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
                                    <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}
                                    </td>
                                    <td style="font-weight: 700; color: #0f172a;">{{ $c->nama_lengkap }}</td>
                                    <td style="font-weight: 600; color: #64748b;">{{ $c->id_pelanggan }}</td>
                                    <td style="font-size: 11px; color: #64748b;">{{ Str::limit($c->alamat_lengkap, 50) }}
                                    </td>
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
                    Batal
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Tambah Dismantle --}}
    <div class="modal-overlay" id="addDismantleModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <form action="{{ route('dismantles.store') }}" method="POST">
                @csrf
                <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                    <div class="modal-title">
                        <i class="fa-solid fa-plus-square text-primary"></i>
                        <span class="text-slate-900 font-bold text-[17px]">Buat Penugasan Dismantle</span>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('addDismantleModal')">
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
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <span
                                        class="text-[10px] font-black text-sky-400 block uppercase tracking-wider">WhatsApp</span>
                                    <p id="v_wa" class="text-sm font-bold text-sky-700 m-0 mt-1"></p>
                                </div>
                                <div>
                                    <span
                                        class="text-[10px] font-black text-sky-400 block uppercase tracking-wider">Paket</span>
                                    <p id="v_paket" class="text-sm font-bold text-sky-700 m-0 mt-1"></p>
                                </div>
                                <div>
                                    <span
                                        class="text-[10px] font-black text-sky-400 block uppercase tracking-wider">Sales</span>
                                    <p id="v_sales" class="text-sm font-bold text-sky-700 m-0 mt-1"></p>
                                </div>
                                <div>
                                    <span
                                        class="text-[10px] font-black text-sky-400 block uppercase tracking-wider">Koordinat</span>
                                    <p id="v_tikor" class="text-sm font-bold text-sky-700 m-0 mt-1"></p>
                                </div>
                            </div>
                            <hr class="border-sky-100/50 m-0">
                            <div>
                                <span class="text-[10px] font-black text-sky-400 block uppercase tracking-wider">Alamat
                                    Pemasangan</span>
                                <p id="v_alamat" class="text-sm font-medium text-slate-600 m-0 mt-1 leading-relaxed">
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label !text-slate-800 !font-bold">
                            <i class="fa-solid fa-user-gear text-slate-400 mr-1"></i> Tugaskan Ke
                        </label>
                        <select name="id_teknisi_delegasi" class="form-select">
                            <option value="">-- Tugas Umum (Bisa diambil siapa saja) --</option>
                            @foreach ($teknisis as $t)
                                <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400 italic mt-1.5">*Opsional, bisa dikosongkan</p>
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label !text-slate-800 !font-bold">
                            <i class="fa-solid fa-comment-dots text-slate-400 mr-1"></i> Alasan / Keterangan <span
                                class="text-rose-500">*</span>
                        </label>
                        <textarea name="keterangan_admin" class="form-textarea" required rows="3"
                            placeholder="Contoh: Berhenti berlangganan / Pindah rumah / Masalah pembayaran"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        onclick="closeCustomModal('addDismantleModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-save !flex !items-center !justify-center gap-2 !bg-emerald-600">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Tugas Dismantle
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Detail Dismantle --}}
    <div class="modal-overlay" id="detailDismantleModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-circle-info text-primary"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Info Lengkap Dismantle</span>
                </div>
                <button class="modal-close" onclick="closeCustomModal('detailDismantleModal')">
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
                            <span class="text-[11px] font-bold text-slate-400 block uppercase">ID Pelanggan</span>
                            <p id="det_id_pel" class="text-sm font-bold text-slate-700 mt-0.5"></p>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 block uppercase">Paket Internet</span>
                            <p id="det_paket" class="text-sm font-bold text-sky-600 mt-0.5"></p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 block uppercase">Sales Terkait</span>
                            <p id="det_sales" class="text-sm font-bold text-slate-700 mt-0.5"></p>
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
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 block uppercase">Titik Koordinat</span>
                            <div class="flex items-center gap-2 mt-1">
                                <a id="det_tikor_link" href="#" target="_blank"
                                    class="bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded text-[10px] font-black no-underline">
                                    <i class="fa-solid fa-map-location-dot"></i> LIHAT MAPS
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="border-slate-100">
                <div class="space-y-3">
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Alamat Pemasangan</span>
                    <p id="det_alamat" class="text-sm font-medium text-slate-600 leading-relaxed m-0"></p>
                </div>
                <div class="space-y-3">
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Keterangan / Alasan</span>
                    <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl">
                        <p id="det_keterangan" class="text-sm font-bold text-amber-700 m-0 italic"></p>
                    </div>
                </div>
                <div id="det_foto_container" class="space-y-3 hidden">
                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Bukti Pencabutan Perangkat</span>
                    <div class="aspect-video w-full rounded-2xl overflow-hidden border-2 border-slate-100 shadow-sm bg-slate-50 flex items-center justify-center cursor-pointer"
                        onclick="window.open($('#det_foto').attr('src'), '_blank')">
                        <img id="det_foto" src="" class="w-full h-full object-contain">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button type="button" class="btn-cancel w-full !py-3"
                    onclick="closeCustomModal('detailDismantleModal')">
                    Tutup Detail
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Selesai (Teknisi) --}}
    <div class="modal-overlay" id="completeDismantleModal">
        <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl">
            <form id="completeForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                    <div class="modal-title">
                        <i class="fa-solid fa-camera text-emerald-500"></i>
                        <span class="text-slate-900 font-bold text-[17px]">Konfirmasi Pencabutan: <span
                                id="n_pel"></span></span>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('completeDismantleModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body p-8 space-y-6">
                    <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100 text-emerald-700">
                        <p class="text-xs font-bold leading-relaxed m-0">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i> Pastikan semua perangkat (ONT/Router)
                            sudah diambil kembali dari lokasi pelanggan sebelum menekan tombol submit.
                        </p>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label !text-slate-800 !font-bold">
                            <i class="fa-solid fa-image text-slate-400 mr-1"></i> Foto Bukti Perangkat Dicabut <span
                                class="text-rose-500">*</span>
                        </label>
                        <input type="file" name="foto_pencabutan" class="form-input" required accept="image/*">
                        <p class="text-[10px] text-slate-400 italic mt-1.5">*Wajib mengunggah foto sebagai bukti laporan.
                        </p>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="submit" class="btn-primary-admin w-full !py-3.5 !bg-emerald-600">
                        <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Kirim Laporan Selesai
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

            function selectCustomer(id, name) {
                $('#selected_customer_id').val(id);
                $('#selected_customer_name').val(name);

                if (id) {
                    fetch(`/dismantles/customer/${id}`)
                        .then(r => r.json())
                        .then(d => {
                            $('#v_wa').text(d.wa || '-');
                            $('#v_paket').text(d.paket || '-');
                            $('#v_sales').text(d.sales || '-');
                            $('#v_tikor').text(d.tikor || '-');
                            $('#v_alamat').text(d.alamat || '-');
                            $('#customer_preview').slideDown(200);
                        });
                }
                closeCustomModal('searchCustomerModal');
            }

            function openDismantleDetailModal(triggerEl) {
                const $btn = $(triggerEl);
                $('#det_nama').text($btn.data('nama'));
                $('#det_id_pel').text($btn.data('id-pel'));
                $('#det_paket').text($btn.data('paket'));
                $('#det_wa').text($btn.data('wa'));
                $('#det_sales').text($btn.data('sales'));
                $('#det_alamat').text($btn.data('alamat'));
                $('#det_keterangan').text($btn.data('keterangan'));

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
                if (foto) {
                    $('#det_foto').attr('src', foto);
                    $('#det_foto_container').removeClass('hidden');
                } else {
                    $('#det_foto_container').addClass('hidden');
                }

                openCustomModal('detailDismantleModal');
            }

            function openCompleteDismantleModal(triggerEl) {
                const $btn = $(triggerEl);
                $('#n_pel').text($btn.data('nama'));
                $('#completeForm').attr('action', `/dismantles/${$btn.data('id')}/complete`);
                openCustomModal('completeDismantleModal');
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

                const adminTable = $('#admin-dismantle-table').DataTable(dtOptions);
                $('#delegated-table, #general-table').DataTable(dtOptions);

                const customerSearchTable = $('#customer-search-table').DataTable({
                    ...dtOptions,
                    "pageLength": 5
                });

                // Custom Search
                $('#custom-search').on('keyup', function() {
                    adminTable.search(this.value).draw();
                });
                $('#customer-modal-search').on('keyup', function() {
                    customerSearchTable.search(this.value).draw();
                });

                // Global SweetAlert Messages
                @if (session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '{{ session('success') }}',
                        timer: 3000,
                        showConfirmButton: false,
                        position: 'top-end',
                        toast: true
                    });
                @endif

                // Close modal on overlay click
                $('.modal-overlay').on('click', function(e) {
                    if (e.target === this) closeCustomModal(this.id);
                });
            });

            function confirmDeleteCustomer(id, name) {
                Swal.fire({
                    title: 'Hapus Pelanggan?',
                    text: `Proses dismantle selesai. Hapus data "${name}" secara permanen?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((r) => {
                    if (r.isConfirmed) document.getElementById('del-cust-' + id).submit();
                });
            }

            function confirmCancelDismantle(id, name) {
                Swal.fire({
                    title: 'Batalkan Tugas?',
                    text: `Tugas dismantle untuk "${name}" akan dihapus dari antrean.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Batal'
                }).then((r) => {
                    if (r.isConfirmed) document.getElementById('cancel-task-' + id).submit();
                });
            }
        </script>
    @endsection
</x-layouts.app>
