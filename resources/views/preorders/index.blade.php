<x-layouts.app>
    @section('Css')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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

            .filter-group {
                display: flex;
                align-items: flex-end;
                gap: 12px;
                flex-wrap: wrap;
                width: 100%;
            }

            .form-label-custom {
                display: block;
                font-size: 11px;
                font-weight: 800;
                color: #64748b;
                text-transform: uppercase;
                margin-bottom: 6px;
                letter-spacing: 0.025em;
            }

            .form-input-custom {
                width: 100%;
                padding: 8px 12px;
                border: 2px solid #e2e8f0;
                border-radius: 10px;
                font-size: 13px;
                font-weight: 600;
                color: #334155;
                background: #f8fafc;
                outline: none;
                height: 40px;
                transition: all 0.2s;
            }

            .form-input-custom:focus {
                border-color: #0ea5e9;
                background: #fff;
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
                font-weight: 600;
                outline: none;
                background: #f8fafc;
                height: 40px;
                transition: all 0.2s;
            }

            .search-input:focus {
                border-color: #0ea5e9;
                background: #fff;
            }

            #preorder-table thead th,
            #preorder-table tbody td {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 12px;
            }
        </style>
    @endsection

    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Pre-Order Pelanggan</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola data pendaftaran calon pelanggan internet.</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if (auth()->user()->jabatan === 'Sales' || auth()->user()->jabatan === 'Administrator')
                <button type="button" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                    onclick="openCustomModal('addPreOrderModal')"><i class="fa-solid fa-user-plus"></i> Tambah
                    Pre-Order</button>
            @endif
            <a href="{{ route('dashboards.index') }}" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;"><i class="fa-solid fa-house"></i>
                Dashboard</a>
        </div>
    </div>

    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;"><i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalThisMonth }}</div>
                <div class="stat-lbl">Bulan Ini</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-calendar-week"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalThisWeek }}</div>
                <div class="stat-lbl">Minggu Ini</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;"><i class="fa-solid fa-hourglass-start"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalPending }}</div>
                <div class="stat-lbl">Belum Aktivasi</div>
            </div>
        </div>
    </div>

    <div class="table-toolbar">
        <form method="GET" action="{{ route('pre_orders.index') }}" class="w-full">
            <div class="filter-group">
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label-custom">Status</label>
                    <select name="status" class="form-input-custom">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ $filterStatus === 'pending' ? 'selected' : '' }}>Menunggu Aktivasi
                        </option>
                        <option value="aktif" {{ $filterStatus === 'aktif' ? 'selected' : '' }}>Berlangganan (Aktif)
                        </option>
                        <option value="berhenti" {{ $filterStatus === 'berhenti' ? 'selected' : '' }}>Berhenti (Dismantle)
                        </option>
                        <option value="unspek" {{ $filterStatus === 'unspek' ? 'selected' : '' }}>Ditangguhkan (Unspek)
                        </option>
                    </select>
                </div>
                @if (Auth::user()->jabatan === 'Administrator')
                    <div style="flex: 1; min-width: 180px;">
                        <label class="form-label-custom">Sales</label>
                        <select name="id_sales" class="form-input-custom">
                            <option value="">Semua Sales</option>
                            @foreach ($salesUsers as $sales)
                                <option value="{{ $sales->id }}" {{ $filterSales == $sales->id ? 'selected' : '' }}>
                                    {{ $sales->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div style="flex: 1.5; min-width: 200px;">
                    <label class="form-label-custom">Cari Pelanggan</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input custom-search" placeholder="Cari nama, alamat..."
                            autocomplete="off" />
                    </div>
                </div>
                <button type="submit" class="btn-primary-admin !h-10 !px-6 flex items-center gap-2">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table id="preorder-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: center; width: 100px;">Tgl PO</th>
                        <th style="text-align: center;">Sales</th>
                        <th style="text-align: center;">Nama Pelanggan</th>
                        <th style="text-align: center;">Paket</th>
                        <th style="text-align: center; width: 180px;">Status / Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($preOrders as $po)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}
                            </td>
                            <td style="text-align: center; color: #64748b; font-size: 12px;">
                                {{ \Carbon\Carbon::parse($po->created_at)->format('d-m-Y') }}</td>
                            <td style="text-align: center;">{{ $po->nama_sales }}</td>
                            <td style="text-align: center; font-weight: 700;">{{ $po->nama_lengkap }}</td>
                            <td style="text-align: center;"><span class="badge"
                                    style="background: #e0f2fe; color: #0369a1;">{{ $po->paket_internet }}</span></td>
                            <td style="text-align: center;">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                        onclick="openCustomModal('detailModal', this)"
                                        data-nama="{{ $po->nama_lengkap }}" data-identitas="{{ $po->no_identitas }}"
                                        data-email-lengkap="{{ $po->alamat_email }}"
                                        data-alamat-lengkap="{{ $po->alamat_lengkap }}"
                                        data-paket="{{ $po->paket_internet }}"
                                        data-koordinat="{{ $po->titik_koordinat }}"
                                        data-foto-ktp="{{ $po->foto_ktp ? asset('storage/' . $po->foto_ktp) : '' }}"
                                        data-foto-rumah="{{ $po->foto_rumah ? asset('storage/' . $po->foto_rumah) : '' }}"><i
                                            class="fa-solid fa-circle-info text-primary"></i></button>
                                    @if ($po->is_unsubscribed)
                                        <span class="badge badge-archived">BERHENTI</span>
                                    @elseif ($po->is_active)
                                        <span class="badge badge-published">AKTIF</span>
                                    @elseif ($po->is_unspek)
                                        <span class="badge" style="background:#fef2f2;color:#be123c;">UNSPEK</span>
                                        @if (Auth::user()->jabatan === 'Administrator')
                                            <form action="{{ route('pre_orders.reopen_unspek', $po->id) }}" method="POST"
                                                class="inline">@csrf<button type="submit"
                                                    class="topbar-icon-btn !w-8 !h-8 !rounded-lg"><i
                                                        class="fa-solid fa-folder-open text-sky-500"></i></button></form>
                                        @endif
                                    @else
                                        @if (in_array(auth()->user()->jabatan, ['Administrator', 'Teknisi']))
                                            <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                                onclick="openUnspekModal('{{ $po->id }}', '{{ $po->nama_lengkap }}')"><i
                                                    class="fa-solid fa-ban text-amber-500"></i></button>
                                        @endif
                                        @if (auth()->user()->jabatan === 'Teknisi')
                                            <button type="button" class="btn-primary-admin !h-8 !px-2 !text-[10px]"
                                                onclick="openActivationModal(this)" data-po-id="{{ $po->id }}"
                                                data-po-sales-id="{{ $po->id_sales }}"
                                                data-po-nama="{{ $po->nama_lengkap }}"
                                                data-po-paket="{{ $po->paket_internet }}"><i
                                                    class="fa-solid fa-bolt"></i></button>
                                        @endif
                                        @if (in_array(auth()->user()->jabatan, ['Administrator', 'Sales']))
                                            <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                                onclick="openCustomModal('editModal', this)"
                                                data-po-id="{{ $po->id }}" data-po-nama="{{ $po->nama_lengkap }}"
                                                data-po-paket="{{ $po->paket_internet }}"
                                                data-po-wa="{{ $po->no_whatsapp }}"
                                                data-po-email="{{ $po->alamat_email }}"
                                                data-po-alamat="{{ $po->alamat_lengkap }}"
                                                data-po-identitas="{{ $po->no_identitas }}"
                                                data-po-koordinat="{{ $po->titik_koordinat }}"><i
                                                    class="fa-solid fa-pen-to-square text-amber-500"></i></button>
                                            <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                                onclick="confirmGlobalDelete('{{ route('pre_orders.destroy', $po->id) }}', '{{ $po->nama_lengkap }}')"><i
                                                    class="fa-solid fa-trash text-rose-600"></i></button>
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

    @include('partials.modal_preorders')
    @include('partials.script_preorders')
</x-layouts.app>
