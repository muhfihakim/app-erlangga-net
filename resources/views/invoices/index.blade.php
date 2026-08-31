<x-layouts.app>
    @section('Css')
        <style>
            #invoice-table thead th, #invoice-table tbody td {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 12px;
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

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Invoice Pelanggan
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola tagihan pelanggan, cek riwayat invoice, dan terima pembayaran.</p>
        </div>
        <button type="button" onclick="openCustomModal('generateMassModal')" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !px-5 !py-2.5 !text-xs flex items-center gap-2 shadow-md ml-auto">
            <i class="fa-solid fa-bolt"></i> Generate Tagihan Masal
        </button>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-200 mb-6 flex items-start gap-3 shadow-sm">
            <i class="fa-solid fa-circle-check text-emerald-500 text-lg mt-0.5"></i>
            <div>
                <strong class="font-bold">Berhasil!</strong>
                <p class="text-sm mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Stats Cards Grid -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0e7ff;color:#4338ca;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($totalPppoeCount, 0, ',', '.') }}</div>
                <div class="stat-lbl">Total Akun Pelanggan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#be123c;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($totalUnpaidCount, 0, ',', '.') }} <span style="font-size:12px;color:#be123c;font-weight:600;">(Rp {{ number_format($totalUnpaidAmount, 0, ',', '.') }})</span></div>
                <div class="stat-lbl">Tagihan Belum Lunas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($totalPaidCount, 0, ',', '.') }} <span style="font-size:12px;color:#15803d;font-weight:600;">(Rp {{ number_format($totalPaidAmount, 0, ',', '.') }})</span></div>
                <div class="stat-lbl">Tagihan Lunas / Terbayar</div>
            </div>
        </div>
    </div>

    <!-- Table Toolbar -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('invoices.index') }}" class="w-full" id="filterForm">
            <div class="filter-group">
                <div style="flex: 1; min-width: 170px;">
                    <label class="form-label-custom">Status Tagihan</label>
                    <select name="status_tagihan" class="form-input-custom" id="filter-status">
                        <option value="">Semua Status</option>
                        <option value="unpaid" {{ $filterStatus === 'unpaid' ? 'selected' : '' }}>Ada Tagihan Belum Lunas</option>
                        <option value="paid" {{ $filterStatus === 'paid' ? 'selected' : '' }}>Semua Lunas</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label-custom">Paket Layanan</label>
                    <select name="paket_id" class="form-input-custom" id="filter-paket">
                        <option value="">Semua Paket</option>
                        @foreach ($pakets as $p)
                            <option value="{{ $p->id }}" {{ $filterPaket == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_paket }} (Rp {{ number_format($p->harga, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="flex: 1.5; min-width: 220px;">
                    <label class="form-label-custom">Live Search</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input custom-search" id="custom-search" placeholder="Cari nama, username, no wa..."
                            autocomplete="off" onkeydown="return event.key != 'Enter';" />
                    </div>
                </div>
                <button type="submit" class="btn-primary-admin !h-10 !px-6 flex items-center gap-2">
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
                        <th style="text-align: center; width: 120px;">Tgl Aktif</th>
                        <th style="text-align: center; width: 170px;">Status Tagihan</th>
                        <th style="text-align: center; width: 150px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Konfirmasi Generate Tagihan Masal -->
    <div class="modal-overlay" id="generateMassModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-bolt text-sky-600"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Generate Tagihan Masal?</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('generateMassModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-8 bg-white">
                <div class="bg-sky-50 p-4 rounded-2xl border border-sky-100 text-center">
                    <i class="fa-solid fa-file-invoice-dollar text-sky-600 text-3xl mb-3"></i>
                    <p class="text-sm font-bold text-slate-800 leading-relaxed">
                        Apakah Anda yakin ingin membuat tagihan baru untuk <span class="text-sky-600">semua pelanggan aktif (PPPoE)</span> periode bulan ini?
                    </p>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <form action="{{ route('invoices.generate') }}" method="POST" class="w-full flex justify-center gap-3">
                    @csrf
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" 
                        style="padding: 10px 24px;"
                        onclick="closeCustomModal('generateMassModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-save !bg-sky-600 hover:!bg-sky-700 !flex !items-center !justify-center gap-2"
                        style="padding: 10px 24px;">
                        <i class="fa-solid fa-bolt"></i> Ya, Generate!
                    </button>
                </form>
            </div>
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
                        { data: 'tanggal_aktif', name: 'tanggal_aktif' },
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
            });
        </script>
    @endsection
</x-layouts.app>
