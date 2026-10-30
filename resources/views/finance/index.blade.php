<x-layouts.app>
    @section('Css')
        <style>
            #finance-table td,
            .admin-table td {
                padding: 10px 14px;
                font-size: 13px;
                vertical-align: middle;
            }

            #finance-table th,
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
                grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
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

            .dataTables_wrapper .dataTables_processing {
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                background: rgba(255, 255, 255, 0.95) !important;
                padding: 15px 25px !important;
                border-radius: 12px !important;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important;
                z-index: 50 !important;
                margin: 0 !important;
                border: 1px solid #e2e8f0 !important;
                width: auto !important;
                height: auto !important;
                text-align: center;
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
                Pendapatan Teknisi
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Laporan estimasi pendapatan teknisi dari aktivasi pelanggan.</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if ($isFiltered)
                <a href="{{ route('finance.export', ['start_date' => $start, 'end_date' => $end, 'teknisi_id' => $teknisiId, 'type' => 'pdf']) }}"
                    class="btn-primary-admin !h-9 !px-4 text-xs font-semibold !flex !items-center !justify-center gap-1.5 !bg-rose-600 hover:!bg-rose-700 shadow-sm" target="_blank">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            @endif
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-sky-700">{{ number_format($customers->count(), 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Pelanggan Terpasang</div>
                <div class="stat-lbl truncate">Total Aktivasi</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4;color:#15803d;">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-slate-900 font-mono">Rp {{ number_format($baseFee, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Per Pasang Baru</div>
                <div class="stat-lbl truncate">Tarif Dasar / Aktivasi</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-amber-600 font-mono">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Total Alokasi Fee</div>
                <div class="stat-lbl truncate">Pendapatan Kotor</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#be123c;">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-rose-600 font-mono">Rp {{ number_format($totalKasbon, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Pemotongan Kasbon</div>
                <div class="stat-lbl truncate">Total Kasbon</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-emerald-700 font-mono">Rp {{ number_format($pendapatanBersih, 0, ',', '.') }}</div>
                <div class="text-[11px] text-emerald-600 font-semibold truncate mt-0.5">
                    <i class="fa-solid fa-arrow-trend-up mr-0.5"></i> Setelah Kasbon
                </div>
                <div class="stat-lbl truncate">Pendapatan Bersih</div>
            </div>
        </div>
    </div>

    <!-- Team Size Stats -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0f9ff;color:#0ea5e9;">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-sky-600">{{ $stats['solo'] }} Kali</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">1 Orang (100% Fee)</div>
                <div class="stat-lbl truncate">Aktivasi Sendiri</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5;color:#10b981;">
                <i class="fa-solid fa-user-group"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-emerald-600">{{ $stats['pair'] }} Kali</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">2 Orang (Bagi 2)</div>
                <div class="stat-lbl truncate">Aktivasi Berdua</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fefce8;color:#eab308;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-amber-600">{{ $stats['triple'] }} Kali</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">3 Orang (Bagi 3)</div>
                <div class="stat-lbl truncate">Aktivasi Bertiga</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff1f2;color:#f43f5e;">
                <i class="fa-solid fa-users-viewfinder"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-rose-600">{{ $stats['quad'] + $stats['more'] }} Kali</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">4+ Orang (Bagi Rata)</div>
                <div class="stat-lbl truncate">Berempat atau Lebih</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('finance.index') }}" class="w-full">
            <div class="filter-group">
                <!-- Filter Teknisi -->
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label-custom">Teknisi</label>
                    <select name="teknisi_id" class="form-input-custom !h-[38px] text-xs w-full">
                        <option value="">Semua Teknisi</option>
                        @foreach ($teknisis as $t)
                            <option value="{{ $t->id }}" {{ $teknisiId == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Periode Tanggal -->
                <div style="flex: 1; min-width: 140px;">
                    <label class="form-label-custom">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-input-custom !h-[38px] text-xs w-full"
                        value="{{ $start }}">
                </div>
                <div style="flex: 1; min-width: 140px;">
                    <label class="form-label-custom">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-input-custom !h-[38px] text-xs w-full"
                        value="{{ $end }}">
                </div>

                <!-- Search -->
                <div style="flex: 1.5; min-width: 200px;">
                    <label class="form-label-custom">Cari Pelanggan</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input custom-search !h-[38px] text-xs" id="custom-search"
                            placeholder="Cari nama, ID, alamat..." autocomplete="off" />
                    </div>
                </div>

                <!-- Filter Button -->
                <div class="flex items-end shrink-0">
                    <button type="submit"
                        class="btn-primary-admin !h-[38px] !px-4 text-xs font-semibold !flex !items-center !justify-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="finance-table" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 45px;">#</th>
                        <th style="text-align: center; width: 120px;">Tgl Aktivasi</th>
                        <th style="text-align: left; min-width: 180px;">Pelanggan</th>
                        <th style="text-align: left; min-width: 180px;">Tim Teknisi</th>
                        <th style="text-align: center; width: 110px;">Total Tim</th>
                        <th style="text-align: right; width: 140px;">Pendapatan</th>
                        <th style="text-align: center; width: 85px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $c)
                        @php
                            $partners = is_array($c->partner_ids)
                                ? $c->partner_ids
                                : json_decode($c->partner_ids, true);
                            $partnerNames = [];
                            if (is_array($partners) && count($partners) > 0) {
                                foreach ($partners as $pid) {
                                    $partnerUser = $allUsers[$pid] ?? null;
                                    if ($partnerUser) {
                                        $partnerNames[] = $partnerUser->nama_lengkap;
                                    }
                                }
                            }
                            $partnerText = count($partnerNames) > 0 ? implode(', ', $partnerNames) : '-';
                            $picName = $c->teknisiUtama->nama_lengkap ?? 'Unknown';
                            $paketName = $c->paket->nama_paket ?? '-';

                            $modalData = htmlspecialchars(json_encode([
                                'id' => $c->id,
                                'nama' => $c->nama_lengkap,
                                'id_pelanggan' => $c->id_pelanggan ?? '-',
                                'no_whatsapp' => $c->no_whatsapp ?? '-',
                                'alamat' => $c->alamat_lengkap ?? '-',
                                'paket' => $paketName,
                                'tanggal' => \Carbon\Carbon::parse($c->created_at)->format('d F Y, H:i') . ' WIB',
                                'pic' => $picName,
                                'partners' => $partnerText,
                                'total_techs' => $c->total_techs . ' Orang',
                                'fee' => 'Rp ' . number_format($c->calculated_fee, 0, ',', '.'),
                                'base_fee' => 'Rp ' . number_format($baseFee, 0, ',', '.'),
                            ]), ENT_QUOTES, 'UTF-8');
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                {{ $loop->iteration }}
                            </td>
                            <td style="text-align: center; color: #475569; font-size: 12px; font-weight: 600;">
                                {{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y') }}
                            </td>
                            <td style="text-align: left;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 13px;">{{ $c->nama_lengkap }}</div>
                                <div style="font-size: 11px; color: #64748b; font-mono font-medium mt-0.5">
                                    ID: {{ $c->id_pelanggan ?? '-' }}
                                </div>
                            </td>
                            <td style="text-align: left;">
                                <div style="font-weight: 600; color: #334155; font-size: 12.5px; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-user-check text-sky-600 text-xs"></i> {{ $picName }}
                                </div>
                                @if (count($partnerNames) > 0)
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px; display: flex; align-items: center; gap: 5px;">
                                        <i class="fa-solid fa-user-plus text-slate-400 text-[10px]"></i> {{ $partnerText }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if ($c->total_techs == 1)
                                    <span class="badge" style="background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd; font-size:11px; padding:2.5px 8px; font-weight:700; border-radius:6px;">
                                        <i class="fa-solid fa-user mr-1"></i> Solo (1)
                                    </span>
                                @elseif ($c->total_techs == 2)
                                    <span class="badge" style="background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; font-size:11px; padding:2.5px 8px; font-weight:700; border-radius:6px;">
                                        <i class="fa-solid fa-user-group mr-1"></i> Duet (2)
                                    </span>
                                @else
                                    <span class="badge" style="background:#fefce8; color:#a16207; border:1px solid #fde047; font-size:11px; padding:2.5px 8px; font-weight:700; border-radius:6px;">
                                        <i class="fa-solid fa-users mr-1"></i> Tim ({{ $c->total_techs }})
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if ($c->calculated_fee > 0)
                                    <div style="font-weight: 700; color: #059669; font-family: monospace; font-size: 13px;">
                                        Rp {{ number_format($c->calculated_fee, 0, ',', '.') }}
                                    </div>
                                    <div style="font-size: 10px; color: #94a3b8; font-family: monospace;">
                                        Total: Rp {{ number_format($baseFee, 0, ',', '.') }}
                                    </div>
                                @else
                                    <div style="font-weight: 700; color: #94a3b8;">-</div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <button type="button"
                                    class="btn-primary-admin !h-8 !px-2.5 !text-xs !rounded-lg !bg-sky-600 hover:!bg-sky-700 inline-flex items-center justify-center gap-1.5 font-semibold shrink-0 shadow-sm"
                                    onclick="showFinanceDetail({{ $modalData }})" title="Lihat Detail Komisi">
                                    <i class="fa-solid fa-eye"></i> Detail
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Rincian Kasbon Table -->
    @if(count($kasbons) > 0)
        <div style="margin-top:2rem; margin-bottom:0.75rem;">
            <h2 class="text-base font-bold text-slate-800 flex items-center gap-2 m-0">
                <i class="fa-solid fa-money-bill-transfer text-rose-600"></i> Rincian Kasbon Periode Ini
            </h2>
        </div>
        <div class="table-card">
            <div class="table-responsive">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 45px;">#</th>
                            <th style="text-align: center; width: 120px;">Tanggal</th>
                            <th style="text-align: left; min-width: 180px;">Teknisi</th>
                            <th style="text-align: left;">Keterangan</th>
                            <th style="text-align: right; width: 150px;">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kasbons as $k)
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                                <td style="text-align: center; color: #475569; font-size: 12px; font-weight: 600;">{{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') }}</td>
                                <td style="text-align: left; font-weight: 700; color: #0f172a; font-size: 13px;">{{ $k->teknisi->nama_lengkap ?? 'Unknown' }}</td>
                                <td style="text-align: left; color: #475569; font-size: 13px;">{{ $k->keterangan ?? '-' }}</td>
                                <td style="text-align: right; font-weight: 700; color: #e11d48; font-family: monospace; font-size: 13px;">Rp {{ number_format($k->nominal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Modal Detail Pembagian Komisi -->
    <div class="modal-overlay" id="detailFinanceModal">
        <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-200 shrink-0">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Detail Pembagian Komisi</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Rincian pengerjaan teknisi dan alokasi fee per aktivasi.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('detailFinanceModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-6 bg-white space-y-4">
                <!-- Summary Card -->
                <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/60 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Bagian Fee Teknisi</span>
                        <span class="text-2xl font-black text-emerald-700 font-mono block mt-0.5" id="detailModalFee">Rp 0</span>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-xs text-slate-500">Tarif Dasar:</span>
                            <span class="text-xs font-bold font-mono text-slate-700" id="detailModalBaseFee">Rp 0</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="badge" style="background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd; font-size:12px; padding:3px 10px; font-weight:700; border-radius:6px;" id="detailModalTotalTechs">
                            1 Orang
                        </span>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-1">Nama Pelanggan</span>
                        <span class="font-bold text-slate-800" id="detailModalNama">-</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-1">ID Pelanggan</span>
                        <span class="font-bold text-slate-800 font-mono" id="detailModalIdPelanggan">-</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-1">Tanggal Aktivasi</span>
                        <span class="font-bold text-slate-800" id="detailModalTanggal">-</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-1">Paket Layanan</span>
                        <span class="font-bold text-slate-800" id="detailModalPaket">-</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-1">Teknisi Utama (PIC)</span>
                        <span class="font-bold text-slate-800" id="detailModalPic">-</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-1">Partner Teknisi</span>
                        <span class="font-bold text-slate-800" id="detailModalPartners">-</span>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                    <span class="text-slate-400 block text-[11px] font-medium mb-1">No. WhatsApp Pelanggan</span>
                    <span class="font-bold text-slate-800 font-mono" id="detailModalWhatsapp">-</span>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                    <span class="text-slate-400 block text-[11px] font-medium mb-1">Alamat Pemasangan</span>
                    <span class="text-slate-700 leading-relaxed block" id="detailModalAlamat">-</span>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2 rounded-b-2xl">
                <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('detailFinanceModal')">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            function showFinanceDetail(data) {
                document.getElementById('detailModalFee').innerText = data.fee || 'Rp 0';
                document.getElementById('detailModalBaseFee').innerText = data.base_fee || 'Rp 0';
                document.getElementById('detailModalTotalTechs').innerText = data.total_techs || '-';
                document.getElementById('detailModalNama').innerText = data.nama || '-';
                document.getElementById('detailModalIdPelanggan').innerText = data.id_pelanggan || '-';
                document.getElementById('detailModalTanggal').innerText = data.tanggal || '-';
                document.getElementById('detailModalPaket').innerText = data.paket || '-';
                document.getElementById('detailModalPic').innerText = data.pic || '-';
                document.getElementById('detailModalPartners').innerText = data.partners || '-';
                document.getElementById('detailModalWhatsapp').innerText = data.no_whatsapp || '-';
                document.getElementById('detailModalAlamat').innerText = data.alamat || '-';

                openCustomModal('detailFinanceModal');
            }

            $(document).ready(function() {
                const table = $('#finance-table').DataTable({
                    pageLength: 10,
                    lengthChange: false,
                    ordering: false,
                    info: false,
                    responsive: true,
                    dom: 'tpir',
                    language: {
                        zeroRecords: "Data tidak ditemukan",
                        paginate: {
                            previous: '<i class="fa-solid fa-chevron-left"></i>',
                            next: '<i class="fa-solid fa-chevron-right"></i>'
                        }
                    }
                });

                // Debounced Live Search
                let searchTimeout;
                $('#custom-search').on('keyup input', function() {
                    const val = this.value;
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        table.search(val).draw();
                    }, 300);
                });
            });
        </script>
    @endsection
</x-layouts.app>
