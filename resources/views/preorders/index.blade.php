<x-layouts.app>
    @section('Css')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            #preorder-table thead th, #preorder-table tbody td {
                text-transform: uppercase !important; font-weight: 600; font-size: 12px;
            }
            .dataTables_wrapper {
                position: relative;
                min-height: 200px;
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
        </style>
    @endsection

    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Pre-Order Pelanggan</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola data pendaftaran calon pelanggan internet.</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if (auth()->user()->jabatan === 'Sales' || auth()->user()->isAdmin())
                <button type="button" class="btn-primary-admin btn-glow-primary !flex !items-center !justify-center gap-2"
                    onclick="openCustomModal('addPreOrderModal')"><i class="fa-solid fa-user-plus"></i> Tambah
                    Pre-Order</button>
            @endif
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
                @if (Auth::user()->isAdmin())
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
                        <input type="search" class="search-input custom-search" id="custom-search" placeholder="Cari nama, alamat..."
                            autocomplete="off" onkeydown="return event.key != 'Enter';" />
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
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    @include('partials.modal_preorders')
    @include('partials.script_preorders')
</x-layouts.app>
