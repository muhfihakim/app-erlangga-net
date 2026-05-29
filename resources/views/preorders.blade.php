<x-layouts.app>
    @section('Css')
        <style>
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
        </style>
    @endsection

    <!-- Page header -->
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Pre-Order Pelanggan</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola data pendaftaran calon pelanggan internet.</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if (auth()->user()->jabatan === 'Sales' || auth()->user()->jabatan === 'Administrator')
                <button type="button" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                    onclick="openCustomModal('addPreOrderModal')">
                    <i class="fa-solid fa-user-plus"></i> Tambah Pre-Order
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
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalThisMonth }}</div>
                <div class="stat-lbl">Pre-Order Bulan Ini</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="fa-solid fa-calendar-week"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalThisWeek }}</div>
                <div class="stat-lbl">Pre-Order Minggu Ini</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-hourglass-start"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalPending }}</div>
                <div class="stat-lbl">Belum Aktivasi</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef2f2;color:#dc2626;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalExpired }}</div>
                <div class="stat-lbl">Tugas Expired</div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input" id="custom-search" placeholder="Cari nama, alamat, atau paket..."
                autocomplete="off" />
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
            <table id="preorder-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: center; width: 100px;">Tgl PO</th>
                        <th style="text-align: center; width: 150px;">Sales</th>
                        <th style="text-align: center; width: 200px;">Nama</th>
                        <th style="text-align: center; width: 150px;">Paket</th>
                        <th style="text-align: center; width: 120px;">No. WA</th>
                        <th style="text-align: center; width: 180px;">Status / Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($preOrders as $po)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                {{ $preOrders->count() - $loop->index }}</td>
                            <td style="text-align: center; color: #64748b; font-size: 12px; font-weight: 600;">
                                {{ \Carbon\Carbon::parse($po->created_at)->format('d-m-Y') }}
                            </td>
                            <td style="text-align: center; font-weight: 600;">{{ $po->nama_sales }}</td>
                            <td style="text-align: center; font-weight: 700; color: #0f172a;">{{ $po->nama_lengkap }}
                            </td>
                            <td style="text-align: center;">
                                <span class="badge"
                                    style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 10px;">
                                    {{ $po->paket_internet }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <a href="https://wa.me/{{ $po->no_whatsapp }}" target="_blank"
                                    style="color: #10b981; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa-brands fa-whatsapp"></i> {{ $po->no_whatsapp }}
                                </a>
                            </td>
                            <td style="text-align: center;">
                                <div
                                    style="display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap; max-width: 220px; margin: 0 auto;">
                                    {{-- Group Universal Buttons --}}
                                    <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl" title="Detail"
                                        onclick="openCustomModal('detailModal', this)"
                                        data-nama="{{ $po->nama_lengkap }}"
                                        data-identitas="{{ $po->no_identitas }}"
                                        data-email-lengkap="{{ $po->alamat_email }}"
                                        data-alamat-lengkap="{{ $po->alamat_lengkap }}"
                                        data-paket="{{ $po->paket_internet }}"
                                        data-koordinat="{{ $po->titik_koordinat }}"
                                        data-foto-ktp="{{ $po->foto_ktp ? asset('storage/' . $po->foto_ktp) : '' }}"
                                        data-foto-rumah="{{ $po->foto_rumah ? asset('storage/' . $po->foto_rumah) : '' }}">
                                        <i class="fa-solid fa-circle-info text-primary"></i>
                                    </button>

                                    @if ($po->titik_koordinat)
                                        <button type="button"
                                            class="topbar-icon-btn !w-9 !h-9 !rounded-xl show-map-btn" title="Lokasi"
                                            onclick="openCustomModal('mapModal', this)"
                                            data-koordinat="{{ $po->titik_koordinat }}">
                                            <i class="fa-solid fa-map-location-dot text-indigo-500"></i>
                                        </button>
                                    @endif

                                    {{-- Status Logics --}}
                                    @if ($po->is_unsubscribed)
                                        <span class="badge badge-archived !px-3 !py-2">BERHENTI</span>
                                    @elseif ($po->is_active)
                                        <span class="badge badge-published !px-3 !py-2">AKTIF</span>
                                    @else
                                        @if (Auth::user()->jabatan === 'Administrator')
                                            @if ($po->id_teknisi_delegasi)
                                                <span
                                                    class="badge {{ $po->is_expired ? 'badge-archived' : 'badge-published' }} !px-3 !py-2"
                                                    title="{{ $po->is_expired ? 'Expired' : 'Dalam Tugas' }}">
                                                    {{ $po->is_expired ? 'EXP' : 'TUGAS' }}
                                                </span>
                                                <button type="button"
                                                    class="topbar-icon-btn !w-9 !h-9 !rounded-xl reset-btn"
                                                    title="Reset Tugas" onclick="openCustomModal('resetModal', this)"
                                                    data-po-id="{{ $po->id }}"
                                                    data-po-nama="{{ $po->nama_lengkap }}">
                                                    <i class="fa-solid fa-rotate-left text-rose-500"></i>
                                                </button>
                                            @else
                                                <button type="button"
                                                    class="topbar-icon-btn !w-9 !h-9 !rounded-xl delegate-btn"
                                                    title="Tugaskan" onclick="openCustomModal('delegateModal', this)"
                                                    data-po-id="{{ $po->id }}"
                                                    data-po-nama="{{ $po->nama_lengkap }}">
                                                    <i class="fa-solid fa-user-gear text-sky-500"></i>
                                                </button>
                                            @endif
                                        @endif

                                        {{-- Edit & Delete --}}
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            title="Edit" onclick="openCustomModal('editModal', this)"
                                            data-po-id="{{ $po->id }}" data-po-nama="{{ $po->nama_lengkap }}"
                                            data-po-paket="{{ $po->paket_internet }}"
                                            data-po-wa="{{ $po->no_whatsapp }}"
                                            data-po-email="{{ $po->alamat_email }}"
                                            data-po-alamat="{{ $po->alamat_lengkap }}"
                                            data-po-identitas="{{ $po->no_identitas }}"
                                            data-po-koordinat="{{ $po->titik_koordinat }}">
                                            <i class="fa-solid fa-pen-to-square text-amber-500"></i>
                                        </button>
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            title="Hapus" 
                                            onclick="confirmGlobalDelete('{{ route('pre_orders.destroy', $po->id) }}', 'Pre-Order: {{ $po->nama_lengkap }}')">
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

    @include('partials.modal_preorders')
    @include('partials.script_preorders')
</x-layouts.app>
