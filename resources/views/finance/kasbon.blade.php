<x-layouts.app>
    @section('styles')
        <style>
            #kasbon-table th {
                padding: 8px 10px !important;
                font-size: 11px !important;
                font-weight: 700 !important;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #64748b;
                background-color: #f8fafc;
                border-bottom: 1px solid #e2e8f0;
                vertical-align: middle !important;
                white-space: nowrap;
            }

            #kasbon-table td {
                padding: 7px 10px !important;
                font-size: 12px;
                vertical-align: middle !important;
                white-space: nowrap;
            }

            .badge-status-pending {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 2px 7px;
                border-radius: 9999px;
                font-size: 10px;
                font-weight: 700;
                background-color: #fef3c7;
                color: #b45309;
                border: 1px solid #fde68a;
            }

            .badge-status-approved {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 2px 7px;
                border-radius: 9999px;
                font-size: 10px;
                font-weight: 700;
                background-color: #dcfce7;
                color: #15803d;
                border: 1px solid #bbf7d0;
            }

            .badge-status-rejected {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 2px 7px;
                border-radius: 9999px;
                font-size: 10px;
                font-weight: 700;
                background-color: #fee2e2;
                color: #b91c1c;
                border: 1px solid #fecaca;
            }

            .badge-role-teknisi {
                display: inline-flex;
                align-items: center;
                gap: 3px;
                padding: 1px 6px;
                border-radius: 4px;
                font-size: 10px;
                font-weight: 700;
                background-color: #f0f9ff;
                color: #0369a1;
                border: 1px solid #bae6fd;
            }

            .badge-role-sales {
                display: inline-flex;
                align-items: center;
                gap: 3px;
                padding: 1px 6px;
                border-radius: 4px;
                font-size: 10px;
                font-weight: 700;
                background-color: #fdf2f8;
                color: #be185d;
                border: 1px solid #fbcfe8;
            }

            .badge-perf-tech {
                display: inline-flex;
                align-items: center;
                gap: 3px;
                padding: 1px 6px;
                border-radius: 4px;
                font-size: 10px;
                font-weight: 600;
                background-color: #f0fdf4;
                color: #166534;
                border: 1px solid #dcfce7;
            }

            .badge-perf-sales {
                display: inline-flex;
                align-items: center;
                gap: 3px;
                padding: 1px 6px;
                border-radius: 4px;
                font-size: 10px;
                font-weight: 600;
                background-color: #fffbeb;
                color: #92400e;
                border: 1px solid #fef3c7;
            }

            .dataTables_paginate {
                display: flex !important;
                align-items: center;
                justify-content: flex-end;
                gap: 4px;
                padding: 10px 14px !important;
            }

            .paginate_button {
                border: 1px solid #e2e8f0 !important;
                background: #fff !important;
                border-radius: 6px !important;
                color: #64748b !important;
                font-size: 11px !important;
                font-weight: 600 !important;
                min-width: 28px;
                height: 28px;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                padding: 0 5px !important;
                cursor: pointer;
                transition: all 0.15s ease;
            }

            .paginate_button.current {
                background: #0ea5e9 !important;
                border-color: #0ea5e9 !important;
                color: #fff !important;
            }
        </style>
    @endsection

    <!-- Page Header (Compact) -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:20px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Kasbon
            </h1>
            <p style="font-size:12px;color:#64748b;margin:2px 0 0;">
                @if ($isAdmin)
                    Pengelolaan, persetujuan (ACC), dan pencatatan kasbon operasional teknisi dan sales.
                @elseif ($isTeknisi)
                    Riwayat kasbon dan pengajuan kasbon operasional teknisi (Siklus Sabtu - Jumat).
                @elseif ($isSales)
                    Riwayat kasbon dan pengajuan kasbon operasional sales (Siklus Bulanan).
                @endif
            </p>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="button" class="btn-primary-admin !h-8 !px-3.5 text-xs font-semibold !flex !items-center !justify-center gap-1.5 shadow-xs"
                onclick="openCustomModal('kasbonModal')">
                @if ($isAdmin)
                    <i class="fa-solid fa-plus"></i> Catat Kasbon
                @else
                    <i class="fa-solid fa-paper-plane"></i> Ajukan Kasbon
                @endif
            </button>
        </div>
    </div>

    <!-- Overview Stats Cards (Compact) -->
    <div class="stats-grid mb-4">
        @if ($isAdmin)
            {{-- STATS ADMIN --}}
            <div class="stat-card !p-3.5">
                <div class="stat-icon !w-8 !h-8 !text-sm" style="background:#dcfce7;color:#15803d;">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div class="min-w-0">
                    <div class="stat-val !text-lg text-emerald-600">Rp {{ number_format($stats['total_approved_nominal'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-[10px] text-slate-500 font-medium truncate mt-0.5">Kasbon Disetujui</div>
                    <div class="stat-lbl !text-[11px] truncate">Periode Terpilih</div>
                </div>
            </div>

            <div class="stat-card !p-3.5">
                <div class="stat-icon !w-8 !h-8 !text-sm" style="background:#fef3c7;color:#b45309;">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="min-w-0">
                    <div class="stat-val !text-lg text-amber-600">{{ $stats['total_pending_count'] ?? 0 }} Pengajuan</div>
                    <div class="text-[10px] text-slate-500 font-medium truncate mt-0.5">
                        Rp {{ number_format($stats['total_pending_nominal'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="stat-lbl !text-[11px] truncate">Menunggu ACC Admin</div>
                </div>
            </div>

            <div class="stat-card !p-3.5">
                <div class="stat-icon !w-8 !h-8 !text-sm" style="background:#e0f2fe;color:#0369a1;">
                    <i class="fa-solid fa-wrench"></i>
                </div>
                <div class="min-w-0">
                    <div class="stat-val !text-lg text-sky-600">Rp {{ number_format($stats['teknisi_kasbon_minggu_ini'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-[10px] text-slate-500 font-medium truncate mt-0.5">
                        {{ $startOfWeekSat->format('d M') }} - {{ $endOfWeekFri->format('d M Y') }}
                    </div>
                    <div class="stat-lbl !text-[11px] truncate">Kasbon Teknisi (Sabtu - Jumat)</div>
                </div>
            </div>

            <div class="stat-card !p-3.5">
                <div class="stat-icon !w-8 !h-8 !text-sm" style="background:#fce7f3;color:#be185d;">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <div class="min-w-0">
                    <div class="stat-val !text-lg text-pink-600">Rp {{ number_format($stats['sales_kasbon_bulan_ini'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-[10px] text-slate-500 font-medium truncate mt-0.5">
                        {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                    </div>
                    <div class="stat-lbl !text-[11px] truncate">Kasbon Sales (Bulan Ini)</div>
                </div>
            </div>

        @elseif ($isTeknisi)
            {{-- STATS KHUSUS TEKNISI --}}
            <div class="stat-card !p-3.5">
                <div class="stat-icon !w-8 !h-8 !text-sm" style="background:#fee2e2;color:#be123c;">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div class="min-w-0">
                    <div class="stat-val !text-lg text-rose-600">Rp {{ number_format($stats['teknisi_kasbon_minggu_ini'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-[10px] text-slate-500 font-medium truncate mt-0.5">
                        {{ $startOfWeekSat->format('d M') }} - {{ $endOfWeekFri->format('d M Y') }}
                    </div>
                    <div class="stat-lbl !text-[11px] truncate">Kasbon Minggu Ini (Disetujui)</div>
                </div>
            </div>

            <div class="stat-card !p-3.5">
                <div class="stat-icon !w-8 !h-8 !text-sm" style="background:#dcfce7;color:#15803d;">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="min-w-0">
                    <div class="stat-val !text-lg text-emerald-600">Rp {{ number_format($stats['teknisi_pendapatan_minggu_ini'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-[10px] text-slate-500 font-medium truncate mt-0.5">Fee Aktivasi Siklus Ini</div>
                    <div class="stat-lbl !text-[11px] truncate">Pendapatan Minggu Ini</div>
                </div>
            </div>

            <div class="stat-card !p-3.5">
                <div class="stat-icon !w-8 !h-8 !text-sm" style="background:#f0f9ff;color:#0284c7;">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div class="min-w-0">
                    <div class="stat-val !text-lg text-sky-600">Rp {{ number_format($stats['teknisi_sisa_estimasi'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-[10px] text-slate-500 font-medium truncate mt-0.5">Pendapatan - Kasbon</div>
                    <div class="stat-lbl !text-[11px] truncate">Estimasi Penerimaan</div>
                </div>
            </div>

            <div class="stat-card !p-3.5">
                <div class="stat-icon !w-8 !h-8 !text-sm" style="background:#fef3c7;color:#b45309;">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div class="min-w-0">
                    <div class="stat-val !text-lg text-amber-600">{{ $stats['my_pending_count'] ?? 0 }} Pengajuan</div>
                    <div class="text-[10px] text-slate-500 font-medium truncate mt-0.5">
                        Rp {{ number_format($stats['my_pending_nominal'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="stat-lbl !text-[11px] truncate">Menunggu ACC Admin</div>
                </div>
            </div>

        @elseif ($isSales)
            {{-- STATS KHUSUS SALES --}}
            <div class="stat-card !p-3.5">
                <div class="stat-icon !w-8 !h-8 !text-sm" style="background:#fce7f3;color:#be185d;">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div class="min-w-0">
                    <div class="stat-val !text-lg text-pink-600">Rp {{ number_format($stats['sales_kasbon_bulan_ini'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-[10px] text-slate-500 font-medium truncate mt-0.5">
                        {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                    </div>
                    <div class="stat-lbl !text-[11px] truncate">Kasbon Bulan Ini (Disetujui)</div>
                </div>
            </div>

            <div class="stat-card !p-3.5">
                <div class="stat-icon !w-8 !h-8 !text-sm" style="background:#dcfce7;color:#15803d;">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div class="min-w-0">
                    <div class="stat-val !text-lg text-emerald-600">{{ $stats['sales_aktivasi_bulan_ini'] ?? 0 }} Pelanggan</div>
                    <div class="text-[10px] text-slate-500 font-medium truncate mt-0.5">Closed PSB / Aktivasi</div>
                    <div class="stat-lbl !text-[11px] truncate">Aktivasi Bulan Ini</div>
                </div>
            </div>

            <div class="stat-card !p-3.5">
                <div class="stat-icon !w-8 !h-8 !text-sm" style="background:#fef3c7;color:#b45309;">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div class="min-w-0">
                    <div class="stat-val !text-lg text-amber-600">{{ $stats['my_pending_count'] ?? 0 }} Pengajuan</div>
                    <div class="text-[10px] text-slate-500 font-medium truncate mt-0.5">
                        Rp {{ number_format($stats['my_pending_nominal'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="stat-lbl !text-[11px] truncate">Menunggu ACC Admin</div>
                </div>
            </div>
        @endif
    </div>

    <!-- Filter & Search Toolbar (Compact) -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('kasbons.index') }}" class="w-full">
            <div class="filter-group flex-wrap">
                @if ($isAdmin)
                    <!-- Filter Karyawan / Pemohon -->
                    <div style="flex: 1.5; min-width: 180px;">
                        <label class="form-label-custom">Pemohon Kasbon</label>
                        <select name="user_id" class="form-input-custom !h-[36px] text-xs w-full">
                            <option value="">-- Semua Pemohon --</option>
                            <optgroup label="Teknisi">
                                @foreach ($teknisis as $t)
                                    <option value="{{ $t->id }}" {{ $userId == $t->id ? 'selected' : '' }}>
                                        {{ $t->nama_lengkap }} (Teknisi)
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Sales">
                                @foreach ($allSales as $s)
                                    <option value="{{ $s->id }}" {{ $userId == $s->id ? 'selected' : '' }}>
                                        {{ $s->nama_lengkap }} (Sales)
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <!-- Filter Role -->
                    <div style="width: 120px;">
                        <label class="form-label-custom">Role</label>
                        <select name="role" class="form-input-custom !h-[36px] text-xs w-full">
                            <option value="">Semua Role</option>
                            <option value="Teknisi" {{ $roleFilter === 'Teknisi' ? 'selected' : '' }}>Teknisi</option>
                            <option value="Sales" {{ $roleFilter === 'Sales' ? 'selected' : '' }}>Sales</option>
                        </select>
                    </div>
                @endif

                <!-- Filter Status -->
                <div style="width: 135px;">
                    <label class="form-label-custom">Status ACC</label>
                    <select name="status" class="form-input-custom !h-[36px] text-xs w-full">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Menunggu ACC</option>
                        <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <!-- Periode Mulai -->
                <div style="width: 135px;">
                    <label class="form-label-custom">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-input-custom !h-[36px] text-xs w-full"
                        value="{{ $start }}">
                </div>

                <!-- Periode Selesai -->
                <div style="width: 135px;">
                    <label class="form-label-custom">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-input-custom !h-[36px] text-xs w-full"
                        value="{{ $end }}">
                </div>

                <!-- Live Search -->
                <div style="flex: 1.5; min-width: 170px;">
                    <label class="form-label-custom">Pencarian</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input !h-[36px] text-xs" id="custom-search"
                            placeholder="Cari kasbon..." autocomplete="off" />
                    </div>
                </div>

                <!-- Action Filter -->
                <div class="flex items-end gap-1.5">
                    <button type="submit"
                        class="btn-primary-admin !h-[36px] !px-3.5 text-xs font-semibold flex items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                    @if ($isFiltered || $statusFilter || ($isAdmin && ($userId || $roleFilter)))
                        <a href="{{ route('kasbons.index') }}"
                            class="topbar-icon-btn !w-[36px] !h-[36px] !rounded-lg hover:!bg-slate-100 flex items-center justify-center text-slate-500"
                            title="Reset Filter">
                            <i class="fa-solid fa-rotate-left text-xs text-slate-500"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Kasbon Card & Compact Table -->
    <div class="table-card mt-3 mb-6">
        <div class="table-responsive">
            <table id="kasbon-table" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">#</th>
                        <th style="width: 105px; text-align: center;">Tanggal</th>
                        <th style="min-width: 230px; text-align: left;">Pemohon & Performa</th>
                        <th style="width: 140px; text-align: right; padding-right: 18px;">Nominal</th>
                        <th style="width: 130px; text-align: center;">Status ACC</th>
                        <th style="text-align: left;">Keterangan</th>
                        <th style="width: 55px; text-align: center;">Bukti</th>
                        <th style="width: 110px; text-align: center;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kasbons as $k)
                        @php
                            $applicant = $k->user;
                            $role = $applicant ? $applicant->jabatan : 'Teknisi';
                            $nominalFormatted = number_format($k->nominal, 0, ',', '.');
                            $tanggalFormatted = $k->tanggal ? $k->tanggal->format('Y-m-d') : date('Y-m-d');
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                            <td style="text-align: center; color: #64748b; font-weight: 600;">
                                {{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') }}
                            </td>
                            <td style="text-align: left;">
                                <div class="font-bold text-slate-800 text-[12px] leading-tight">{{ $applicant->nama_lengkap ?? 'Unknown' }}</div>
                                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                    @if ($role === 'Teknisi')
                                        <span class="badge-role-teknisi">
                                            <i class="fa-solid fa-wrench"></i> Teknisi
                                        </span>
                                        <span class="badge-perf-tech" title="Total Pendapatan Aktivasi Siklus Minggu Ini (Sabtu - Jumat)">
                                            <i class="fa-solid fa-coins"></i> Pendapatan Mgg Ini: Rp {{ number_format($k->performa_teknisi_pendapatan ?? 0, 0, ',', '.') }}
                                        </span>
                                    @elseif ($role === 'Sales')
                                        <span class="badge-role-sales">
                                            <i class="fa-solid fa-briefcase"></i> Sales
                                        </span>
                                        <span class="badge-perf-sales" title="Total Aktivasi Pelanggan Bulan Ini">
                                            <i class="fa-solid fa-user-check"></i> {{ $k->performa_sales_aktivasi ?? 0 }} Aktivasi (Bulan Ini)
                                        </span>
                                    @else
                                        <span class="text-[11px] text-slate-400 font-medium">{{ $role }}</span>
                                    @endif
                                </div>
                            </td>
                            <td style="text-align: right; padding-right: 18px;">
                                <span class="font-bold text-rose-600 text-[13px]">
                                    Rp {{ $nominalFormatted }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if ($k->status === 'pending')
                                    <span class="badge-status-pending">
                                        <i class="fa-solid fa-clock"></i> Menunggu ACC
                                    </span>
                                @elseif ($k->status === 'approved')
                                    <span class="badge-status-approved" title="Disetujui oleh: {{ $k->approver->nama_lengkap ?? 'Admin' }}">
                                        <i class="fa-solid fa-check"></i> Disetujui
                                    </span>
                                @elseif ($k->status === 'rejected')
                                    <span class="badge-status-rejected" title="{{ $k->catatan_admin ?? 'Ditolak' }}">
                                        <i class="fa-solid fa-xmark"></i> Ditolak
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: left; color: #475569; max-width: 250px; white-space: normal;">
                                <div class="text-[11px] leading-relaxed">{{ $k->keterangan ?: '-' }}</div>
                                @if ($k->catatan_admin)
                                    <div class="text-[10px] text-slate-400 italic mt-0.5">
                                        <i class="fa-solid fa-comment-dots mr-1"></i>{{ $k->catatan_admin }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if ($k->bukti)
                                    <a href="{{ Storage::url($k->bukti) }}" target="_blank"
                                        class="topbar-icon-btn !w-7 !h-7 !rounded-lg inline-flex items-center justify-center"
                                        title="Lihat Lampiran Bukti / Nota">
                                        <i class="fa-solid fa-paperclip text-slate-500 hover:text-sky-600 text-xs"></i>
                                    </a>
                                @else
                                    <span class="text-slate-300 text-xs font-medium">-</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div class="flex items-center justify-center gap-1">
                                    @if ($isAdmin)
                                        {{-- AKSI ADMIN --}}
                                        @if ($k->status === 'pending')
                                            <!-- Tombol ACC (Setujui) -->
                                            <button type="button"
                                                class="topbar-icon-btn !w-7 !h-7 !rounded-lg"
                                                title="Setujui (ACC) Kasbon"
                                                onclick="openAccModal({{ $k->id }}, '{{ addslashes($applicant->nama_lengkap ?? '') }}', '{{ $nominalFormatted }}', '{{ $role }}', '{{ $role === 'Teknisi' ? 'Pendapatan: Rp ' . number_format($k->performa_teknisi_pendapatan ?? 0, 0, ',', '.') : 'Aktivasi: ' . ($k->performa_sales_aktivasi ?? 0) . ' Pelanggan' }}')">
                                                <i class="fa-solid fa-check text-slate-500 hover:text-emerald-600 text-xs"></i>
                                            </button>

                                            <!-- Tombol Tolak -->
                                            <button type="button"
                                                class="topbar-icon-btn !w-7 !h-7 !rounded-lg"
                                                title="Tolak Pengajuan Kasbon"
                                                onclick="openRejectModal({{ $k->id }}, '{{ addslashes($applicant->nama_lengkap ?? '') }}', '{{ $nominalFormatted }}')">
                                                <i class="fa-solid fa-xmark text-slate-500 hover:text-amber-600 text-xs"></i>
                                            </button>
                                        @endif

                                        <!-- Tombol Ubah / Edit Kasbon (Admin) -->
                                        <button type="button"
                                            class="topbar-icon-btn !w-7 !h-7 !rounded-lg"
                                            title="Ubah Kasbon"
                                            onclick="openEditKasbonModal({{ $k->id }}, {{ $k->nominal }}, '{{ $tanggalFormatted }}', '{{ addslashes($k->keterangan ?? '') }}', '{{ addslashes($applicant->nama_lengkap ?? '') }}')">
                                            <i class="fa-solid fa-pen-to-square text-slate-500 hover:text-amber-600 text-xs"></i>
                                        </button>

                                        <!-- Tombol Hapus Kasbon -->
                                        <button type="button" class="topbar-icon-btn !w-7 !h-7 !rounded-lg hover:!border-rose-300 hover:!bg-rose-50"
                                            title="Hapus Kasbon"
                                            onclick="confirmGlobalDelete('{{ route('kasbons.destroy', $k->id) }}', 'Kasbon {{ addslashes($applicant->nama_lengkap ?? '') }} (Rp {{ $nominalFormatted }})')">
                                            <i class="fa-solid fa-trash text-slate-500 hover:text-rose-600 text-xs"></i>
                                        </button>

                                    @else
                                        {{-- AKSI TEKNISI / SALES (PEMOHON) --}}
                                        @if ($k->status === 'pending')
                                            <!-- Tombol Edit Permohonan (Ubah Nominal / Keterangan oleh Pemohon) -->
                                            <button type="button"
                                                class="topbar-icon-btn !w-7 !h-7 !rounded-lg"
                                                title="Ubah Permohonan Kasbon"
                                                onclick="openEditKasbonModal({{ $k->id }}, {{ $k->nominal }}, '{{ $tanggalFormatted }}', '{{ addslashes($k->keterangan ?? '') }}', '{{ addslashes($applicant->nama_lengkap ?? '') }}')">
                                                <i class="fa-solid fa-pen-to-square text-slate-500 hover:text-amber-600 text-xs"></i>
                                            </button>

                                            <!-- Tombol Batalkan Pengajuan -->
                                            <button type="button" class="topbar-icon-btn !w-7 !h-7 !rounded-lg hover:!border-rose-300 hover:!bg-rose-50"
                                                title="Batalkan Pengajuan"
                                                onclick="confirmGlobalDelete('{{ route('kasbons.destroy', $k->id) }}', 'Pengajuan kasbon Rp {{ $nominalFormatted }}')">
                                                <i class="fa-solid fa-trash text-slate-500 hover:text-rose-600 text-xs"></i>
                                            </button>
                                        @else
                                            <span class="text-slate-300 text-xs font-semibold">-</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: CATAT / AJUKAN KASBON -->
    <div class="modal-overlay" id="kasbonModal">
        <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-5 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-xs border border-slate-200 shrink-0">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 m-0">
                            {{ $isAdmin ? 'Catat Kasbon Karyawan' : 'Form Pengajuan Kasbon' }}
                        </h3>
                        <p class="text-[11px] text-slate-500 m-0 mt-0.5">
                            {{ $isAdmin ? 'Catat kasbon operasional untuk Teknisi atau Sales (langsung disetujui).' : 'Ajukan kasbon ke Admin untuk persetujuan.' }}
                        </p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('kasbonModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form action="{{ route('kasbons.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body bg-white p-5">
                    @if ($isAdmin)
                        <div class="form-group mb-3">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1 block">
                                Pemohon / Penerima Kasbon <span class="text-rose-500">*</span>
                            </label>
                            <select name="user_id" class="form-input-custom !font-normal w-full bg-white text-slate-800 border-slate-300 text-xs" required>
                                <option value="">-- Pilih Karyawan (Teknisi / Sales) --</option>
                                <optgroup label="Teknisi">
                                    @foreach ($teknisis as $t)
                                        <option value="{{ $t->id }}">{{ $t->nama_lengkap }} (Teknisi)</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Sales">
                                    @foreach ($allSales as $s)
                                        <option value="{{ $s->id }}">{{ $s->nama_lengkap }} (Sales)</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    @else
                        <!-- Info Pemohon untuk Teknisi / Sales -->
                        <div class="form-group mb-3">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1 block">Nama Pemohon</label>
                            <div class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-800 flex items-center justify-between">
                                <span>{{ $user->nama_lengkap }}</span>
                                <span class="{{ $isTeknisi ? 'badge-role-teknisi' : 'badge-role-sales' }}">
                                    {{ $user->jabatan }}
                                </span>
                            </div>
                        </div>
                    @endif

                    <div class="form-grid-2 mb-3">
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1 block">
                                Nominal Kasbon (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="nominal" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" required
                                placeholder="Contoh: 150000" min="1000" step="1000">
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1 block">
                                Tanggal <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" required
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    @if ($isAdmin)
                        <div class="form-group mb-3">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1 block">
                                Bukti / Nota Pembayaran (Opsional)
                            </label>
                            <input type="file" name="bukti" class="form-input !font-normal bg-white text-slate-700 border-slate-300 text-xs py-1.5"
                                accept="image/jpeg,image/png,application/pdf" capture="environment">
                            <span class="text-[10px] text-slate-400 mt-1 block">Format: JPG, PNG, atau PDF (Maks. 5MB)</span>
                        </div>
                    @endif

                    <div class="form-group mb-0">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1 block">
                            Keterangan / Keperluan Kasbon
                        </label>
                        <textarea name="keterangan" class="form-textarea !font-normal bg-white text-slate-800 border-slate-300 text-xs" rows="2"
                            placeholder="Alasan atau catatan kasbon operasional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-5 py-3 flex items-center justify-between gap-2">
                    <button type="button" class="btn-cancel !h-8 !px-3.5 text-xs font-semibold flex items-center gap-1.5"
                        onclick="closeCustomModal('kasbonModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !h-8 !px-4 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid {{ $isAdmin ? 'fa-floppy-disk' : 'fa-paper-plane' }}"></i>
                        {{ $isAdmin ? 'Simpan Kasbon' : 'Kirim Pengajuan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: UBAH / EDIT KASBON (Untuk Pemohon & Admin) -->
    <div class="modal-overlay" id="editKasbonModal">
        <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-5 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs border border-amber-200 shrink-0">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 m-0">Ubah Permohonan Kasbon</h3>
                        <p class="text-[11px] text-slate-500 m-0 mt-0.5">Perbarui nominal atau rincian pengajuan kasbon.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('editKasbonModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="editKasbonForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body bg-white p-5">
                    <div class="form-group mb-3">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1 block">Pemohon</label>
                        <div class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-800" id="editApplicantName">
                            -
                        </div>
                    </div>

                    <div class="form-grid-2 mb-3">
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1 block">
                                Nominal Kasbon (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" id="editNominal" name="nominal" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" required
                                placeholder="Contoh: 150000" min="1000" step="1000">
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1 block">
                                Tanggal <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" id="editTanggal" name="tanggal" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" required>
                        </div>
                    </div>

                    @if ($isAdmin)
                        <div class="form-group mb-3">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1 block">
                                Ganti Bukti / Nota (Opsional)
                            </label>
                            <input type="file" name="bukti" class="form-input !font-normal bg-white text-slate-700 border-slate-300 text-xs py-1.5"
                                accept="image/jpeg,image/png,application/pdf" capture="environment">
                            <span class="text-[10px] text-slate-400 mt-1 block">Biarkan kosong jika tidak ingin mengubah lampiran nota.</span>
                        </div>
                    @endif

                    <div class="form-group mb-0">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1 block">
                            Keterangan / Keperluan Kasbon
                        </label>
                        <textarea id="editKeterangan" name="keterangan" class="form-textarea !font-normal bg-white text-slate-800 border-slate-300 text-xs" rows="2"
                            placeholder="Alasan atau catatan kasbon operasional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-5 py-3 flex items-center justify-between gap-2">
                    <button type="button" class="btn-cancel !h-8 !px-3.5 text-xs font-semibold flex items-center gap-1.5"
                        onclick="closeCustomModal('editKasbonModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !h-8 !px-4 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($isAdmin)
        <!-- MODAL 3: ACC / SETUJUI KASBON -->
        <div class="modal-overlay" id="accModal">
            <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
                <div class="modal-header border-b border-slate-200 bg-white px-5 py-3.5 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs border border-emerald-200 shrink-0">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 m-0">Setujui (ACC) Kasbon</h3>
                            <p class="text-[11px] text-slate-500 m-0 mt-0.5">Konfirmasi persetujuan pengajuan kasbon.</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('accModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <form id="accForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body bg-white p-5">
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg mb-3">
                            <div class="text-[11px] text-slate-500 mb-0.5">Pemohon:</div>
                            <div class="text-xs font-bold text-slate-900" id="accApplicantName">-</div>
                            <div class="text-[11px] font-semibold text-emerald-600 mt-1" id="accApplicantPerf">-</div>
                            <div class="mt-2 pt-2 border-t border-slate-200 flex items-center justify-between">
                                <span class="text-xs text-slate-500 font-medium">Nominal Kasbon:</span>
                                <span class="text-sm font-black text-rose-600" id="accNominal">Rp 0</span>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1 block">
                                Lampirkan Bukti Transfer / Nota (Opsional)
                            </label>
                            <input type="file" name="bukti" class="form-input !font-normal bg-white text-slate-700 border-slate-300 text-xs py-1.5"
                                accept="image/jpeg,image/png,application/pdf" capture="environment">
                            <span class="text-[10px] text-slate-400 mt-1 block">Lampirkan struk / bukti transfer pencairan kasbon (JPG, PNG, atau PDF).</span>
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1 block">Catatan Persetujuan (Opsional)</label>
                            <input type="text" name="catatan_admin" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs"
                                placeholder="Contoh: Disetujui, dana ditransfer via BCA">
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-200 bg-slate-50 px-5 py-3 flex items-center justify-between gap-2">
                        <button type="button" class="btn-cancel !h-8 !px-3.5 text-xs font-semibold flex items-center gap-1.5"
                            onclick="closeCustomModal('accModal')">
                            <i class="fa-solid fa-xmark"></i> Batal
                        </button>
                        <button type="submit" class="btn-primary-admin !h-8 !px-4 text-xs font-semibold shadow-xs flex items-center gap-1.5 !bg-emerald-600 hover:!bg-emerald-700">
                            <i class="fa-solid fa-check"></i> Setujui (ACC)
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 4: TOLAK KASBON -->
        <div class="modal-overlay" id="rejectModal">
            <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
                <div class="modal-header border-b border-slate-200 bg-white px-5 py-3.5 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs border border-rose-200 shrink-0">
                            <i class="fa-solid fa-xmark"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 m-0">Tolak Pengajuan Kasbon</h3>
                            <p class="text-[11px] text-slate-500 m-0 mt-0.5">Berikan alasan penolakan kasbon pemohon.</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('rejectModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body bg-white p-5">
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg mb-3">
                            <div class="text-[11px] text-slate-500 mb-0.5">Pemohon:</div>
                            <div class="text-xs font-bold text-slate-900" id="rejectApplicantName">-</div>
                            <div class="mt-2 pt-2 border-t border-slate-200 flex items-center justify-between">
                                <span class="text-xs text-slate-500 font-medium">Nominal Kasbon:</span>
                                <span class="text-sm font-black text-rose-600" id="rejectNominal">Rp 0</span>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1 block">
                                Alasan Penolakan <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="catatan_admin" class="form-textarea !font-normal bg-white text-slate-800 border-slate-300 text-xs" rows="2" required
                                placeholder="Contoh: Melebihi plafon kasbon / Aktivasi belum mencukupi..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-200 bg-slate-50 px-5 py-3 flex items-center justify-between gap-2">
                        <button type="button" class="btn-cancel !h-8 !px-3.5 text-xs font-semibold flex items-center gap-1.5"
                            onclick="closeCustomModal('rejectModal')">
                            <i class="fa-solid fa-xmark"></i> Batal
                        </button>
                        <button type="submit" class="btn-primary-admin !h-8 !px-4 text-xs font-semibold shadow-xs flex items-center gap-1.5 !bg-rose-600 hover:!bg-rose-700">
                            <i class="fa-solid fa-ban"></i> Tolak Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

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

            function openEditKasbonModal(id, nominal, tanggal, keterangan, applicantName) {
                $('#editKasbonForm').attr('action', '/kasbons/' + id);
                $('#editApplicantName').text(applicantName);
                $('#editNominal').val(nominal);
                $('#editTanggal').val(tanggal);
                $('#editKeterangan').val(keterangan);
                openCustomModal('editKasbonModal');
            }

            @if ($isAdmin)
                function openAccModal(id, name, nominal, role, perf) {
                    $('#accForm').attr('action', '/kasbons/' + id + '/approve');
                    $('#accApplicantName').text(name + ' (' + role + ')');
                    $('#accApplicantPerf').text(perf);
                    $('#accNominal').text('Rp ' + nominal);
                    openCustomModal('accModal');
                }

                function openRejectModal(id, name, nominal) {
                    $('#rejectForm').attr('action', '/kasbons/' + id + '/reject');
                    $('#rejectApplicantName').text(name);
                    $('#rejectNominal').text('Rp ' + nominal);
                    openCustomModal('rejectModal');
                }
            @endif
        </script>
    @endsection
</x-layouts.app>
