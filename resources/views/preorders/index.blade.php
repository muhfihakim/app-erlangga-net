<x-layouts.app>
    @section('Css')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            #preorder-table td {
                padding: 11px 14px;
                font-size: 13px;
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
                    onclick="openCustomModal('addPreOrderModal')">Tambah
</button>
            @endif
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 mb-5 border-b border-slate-200 pb-2">
        <a href="{{ route('pre_orders.index') }}"
            class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-primary text-white shadow-sm">
            Pre-Order Aktif
        </a>
        <a href="{{ route('pre_orders.waiting_list') }}"
            class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all flex items-center gap-2">
            <span>Waiting List Ekspansi</span>
            @if($totalUnspek > 0)
                <span class="bg-rose-100 text-rose-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $totalUnspek }}</span>
            @endif
        </a>
    </div>

    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;"><i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalThisMonth }}</div>
                <div class="stat-lbl">PO Bulan Ini</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fffbeb;color:#b45309;"><i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalBelumPppoe }}</div>
                <div class="stat-lbl">Belum Ada PPPoE</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;"><i class="fa-solid fa-network-wired"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalSudahPppoe }}</div>
                <div class="stat-lbl">PPPoE Siap</div>
            </div>
        </div>
        <a href="{{ route('pre_orders.waiting_list') }}" class="stat-card hover:border-rose-300 transition-colors block cursor-pointer" title="Klik untuk membuka Daftar Prospek Tunggu">
            <div class="stat-icon" style="background:#fef2f2;color:#be123c;"><i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalUnspek }}</div>
                <div class="stat-lbl">Waiting List Ekspansi</div>
            </div>
        </a>
    </div>

    <div class="table-toolbar">
        <form method="GET" action="{{ route('pre_orders.index') }}" class="w-full">
            <div class="filter-group">
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label-custom">Status</label>
                    <select name="status" class="form-input-custom">
                        <option value="belum_pppoe" {{ $filterStatus === 'belum_pppoe' ? 'selected' : '' }}>Belum Ada PPPoE (Menunggu Admin)</option>
                        <option value="sudah_pppoe" {{ $filterStatus === 'sudah_pppoe' ? 'selected' : '' }}>PPPoE Siap (Menunggu Aktivasi)</option>
                        <option value="unspek" {{ $filterStatus === 'unspek' ? 'selected' : '' }}>Ditangguhkan (Unspek)</option>
                        <option value="semua" {{ $filterStatus === 'semua' ? 'selected' : '' }}>Semua Status PO</option>
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
                        <th style="text-align: center; width: 45px;">#</th>
                        <th style="text-align: center; width: 95px;">Tgl PO</th>
                        <th style="text-align: left; min-width: 150px; width: 160px;">Sales</th>
                        <th style="text-align: left; min-width: 240px;">Nama Pelanggan</th>
                        <th style="text-align: center; width: 130px;">Paket</th>
                        <th style="text-align: center; width: 170px;">Status / Opsi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    @include('partials.modal_preorders')
    @include('partials.script_preorders')
</x-layouts.app>
