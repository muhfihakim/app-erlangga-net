<x-layouts.app>
    <div class="page-header">
        <div>
            <h1 class="page-title">Invoice Pelanggan</h1>
            <p class="page-subtitle">Kelola tagihan pelanggan, cek riwayat invoice, dan terima pembayaran.</p>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0e7ff;color:#4338ca;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val">{{ number_format($totalPppoeCount, 0, ',', '.') }}</div>
                <div class="stat-lbl truncate">Total Akun Pelanggan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#be123c;">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-rose-700">
                    <span>{{ number_format($totalUnpaidCount, 0, ',', '.') }}</span>
                    <span class="text-[11px] font-semibold text-rose-600 font-mono">(Rp {{ number_format($totalUnpaidAmount, 0, ',', '.') }})</span>
                </div>
                <div class="stat-lbl truncate">Tagihan Belum Lunas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff7ed;color:#c2410c;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-amber-700">
                    <span>{{ number_format($totalOverdueCount, 0, ',', '.') }}</span>
                    <span class="text-[11px] font-semibold text-amber-600 font-mono">(Rp {{ number_format($totalOverdueAmount, 0, ',', '.') }})</span>
                </div>
                <div class="stat-lbl truncate">Lewat Jatuh Tempo</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-emerald-700">
                    <span>{{ number_format($totalPaidCount, 0, ',', '.') }}</span>
                    <span class="text-[11px] font-semibold text-emerald-600 font-mono">(Rp {{ number_format($totalPaidAmount, 0, ',', '.') }})</span>
                </div>
                <div class="stat-lbl truncate">Tagihan Lunas</div>
            </div>
        </div>
    </div>

    <!-- Table Toolbar -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('invoices.index') }}" class="w-full" id="filterForm">
            <div class="filter-group">
                <div style="flex: 1; min-width: 170px;">
                    <label class="form-label-custom">Status Tagihan</label>
                    <select name="status_tagihan" class="form-input-custom !h-[38px] text-xs" id="filter-status">
                        <option value="">Semua Status</option>
                        <option value="unpaid" {{ $filterStatus === 'unpaid' ? 'selected' : '' }}>Ada Tagihan Belum Lunas</option>
                        <option value="paid" {{ $filterStatus === 'paid' ? 'selected' : '' }}>Semua Lunas</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label-custom">Paket Layanan</label>
                    <select name="paket_id" class="form-input-custom !h-[38px] text-xs" id="filter-paket">
                        <option value="">Semua Paket</option>
                        @foreach ($pakets as $p)
                            <option value="{{ $p->id }}" {{ $filterPaket == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_paket }} (Rp {{ number_format($p->harga, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="flex: 1.5; min-width: 220px;">
                    <label class="form-label-custom">Cari Tagihan</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input custom-search !h-[38px] text-xs" id="custom-search" placeholder="Cari nama, username, no wa..."
                            autocomplete="off" onkeydown="return event.key != 'Enter';" />
                    </div>
                </div>
                <button type="submit" class="btn-primary-admin !h-[38px] !px-4 text-xs font-semibold !flex !items-center !justify-center gap-2">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="invoice-table" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: left;">Pelanggan</th>
                        <th style="text-align: left; width: 220px;">Paket Layanan</th>
                        <th style="text-align: center; width: 160px;">Tagihan Selanjutnya</th>
                        <th style="text-align: center; width: 170px;">Status Tagihan</th>
                        <th style="text-align: center; width: 70px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                const table = $('#invoice-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('invoices.index') }}",
                        data: function (d) {
                            d.status_tagihan = $('#filter-status').val();
                            d.paket_id = $('#filter-paket').val();
                        }
                    },
                    columns: [
                        { data: 'no', name: 'no', searchable: false, orderable: false, className: 'text-center text-slate-400 font-bold', render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }},
                        { data: 'pelanggan', name: 'username' },
                        { data: 'paket', name: 'paket_id' },
                        { data: 'tagihan_selanjutnya', name: 'tagihan_selanjutnya', searchable: false, orderable: false },
                        { data: 'status_tagihan', name: 'status_tagihan', searchable: false, orderable: false },
                        { data: 'opsi', name: 'opsi', searchable: false, orderable: false }
                    ],
                    pageLength: 10,
                    lengthChange: false,
                    ordering: false,
                    info: false,
                    responsive: true,
                    dom: 'tpir',
                    language: {
                        processing: "<i class='fa-solid fa-circle-notch fa-spin text-sky-500 text-2xl'></i>",
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
                    }, 350);
                });

                // Filter on select change
                $('#filter-status, #filter-paket').on('change', function() {
                    table.draw();
                });

                $('#filterForm').on('submit', function(e) {
                    e.preventDefault();
                    table.draw();
                });

                $('.modal-overlay').on('click', function(e) {
                    if (e.target === this) closeCustomModal(this.id);
                });
            });

            function openCustomModal(modalId) {
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
        </script>
    @endsection
</x-layouts.app>
