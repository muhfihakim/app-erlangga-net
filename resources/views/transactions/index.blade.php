<x-layouts.app>
    @section('Css')
        <style>
            #transaction-table td {
                padding: 10px 14px;
                font-size: 13px;
                vertical-align: middle;
            }

            #transaction-table th {
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
    <div style="margin-bottom:1.25rem;">
        <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
            Log Riwayat Transaksi
        </h1>
        <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Riwayat aktivitas penerimaan pembayaran manual kasir & gateway online Xendit.</p>
    </div>

    <!-- Stats Cards Grid -->
    <div class="stats-grid mb-6">
        <!-- Monthly Earning -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-emerald-700">Rp {{ number_format($monthlyEarning, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">
                    Rp {{ number_format($lastMonthEarning, 0, ',', '.') }} bulan lalu
                </div>
                <div class="stat-lbl truncate">Pendapatan Bulanan</div>
            </div>
        </div>

        <!-- Monthly Expenses -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#be123c;">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-rose-700">Rp {{ number_format($monthlyExpenses, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">
                    Rp {{ number_format($lastMonthExpenses, 0, ',', '.') }} bulan lalu
                </div>
                <div class="stat-lbl truncate">Pengeluaran Bulanan</div>
            </div>
        </div>

        <!-- Monthly Profit -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0e7ff;color:#4338ca;">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-indigo-700">Rp {{ number_format($monthlyProfit, 0, ',', '.') }}</div>
                <div class="text-[11px] text-emerald-600 font-semibold truncate mt-0.5">
                    <i class="fa-solid fa-arrow-trend-up mr-0.5"></i> Pendapatan Bersih
                </div>
                <div class="stat-lbl truncate">Laba Bersih Bulanan</div>
            </div>
        </div>

        <!-- Monthly Transaction -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0284c7;">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-sky-700">{{ number_format($monthlyTxCount, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">
                    {{ number_format($lastMonthTxCount, 0, ',', '.') }} bulan lalu
                </div>
                <div class="stat-lbl truncate">Total Transaksi Bulanan</div>
            </div>
        </div>
    </div>

    <!-- Table Toolbar -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('transactions.index') }}" class="w-full" id="filterForm">
            <div class="filter-group">
                <div style="flex: 1; min-width: 160px;">
                    <label class="form-label-custom">Metode Pembayaran</label>
                    <select name="payment_method" class="form-input-custom !h-[38px] text-xs" id="filter-payment-method">
                        <option value="">Semua Metode</option>
                        <option value="cash" {{ $filterMethod === 'cash' ? 'selected' : '' }}>Cash (Tunai)</option>
                        <option value="cashless" {{ $filterMethod === 'cashless' ? 'selected' : '' }}>Cashless (Transfer/QRIS)</option>
                        <option value="xendit" {{ $filterMethod === 'xendit' ? 'selected' : '' }}>Xendit Payment Gateway</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 140px;">
                    <label class="form-label-custom">Status</label>
                    <select name="status" class="form-input-custom !h-[38px] text-xs" id="filter-status">
                        <option value="">Semua Status</option>
                        <option value="success" {{ $filterStatus === 'success' ? 'selected' : '' }}>Berhasil (Success)</option>
                        <option value="pending" {{ $filterStatus === 'pending' ? 'selected' : '' }}>Menunggu (Pending)</option>
                        <option value="failed" {{ $filterStatus === 'failed' ? 'selected' : '' }}>Gagal (Failed)</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 140px;">
                    <label class="form-label-custom">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-input-custom !h-[38px] text-xs" id="filter-date-from" value="{{ $filterDateFrom }}">
                </div>
                <div style="flex: 1; min-width: 140px;">
                    <label class="form-label-custom">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-input-custom !h-[38px] text-xs" id="filter-date-to" value="{{ $filterDateTo }}">
                </div>
                <div style="flex: 1.5; min-width: 200px;">
                    <label class="form-label-custom">Cari Transaksi</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input custom-search !h-[38px] text-xs" id="custom-search"
                            placeholder="Cari invoice, pelanggan, username..." autocomplete="off"
                            onkeydown="return event.key != 'Enter';" />
                    </div>
                </div>
                <div class="flex items-end shrink-0">
                    <button type="submit" class="btn-primary-admin !h-[38px] !px-4 text-xs font-semibold !flex !items-center !justify-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="transaction-table" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 45px;">#</th>
                        <th style="text-align: center; width: 130px;">Tgl Bayar</th>
                        <th style="text-align: left; min-width: 170px;">Pelanggan</th>
                        <th style="text-align: center; width: 140px;">No. Invoice</th>
                        <th style="text-align: right; width: 130px;">Nominal</th>
                        <th style="text-align: center; width: 130px;">Metode</th>
                        <th style="text-align: center; width: 110px;">Status</th>
                        <th style="text-align: center; width: 90px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Detail Riwayat Transaksi -->
    <div class="modal-overlay" id="detailTransactionModal">
        <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-200 shrink-0">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Detail Riwayat Transaksi</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Informasi lengkap transaksi penerimaan pembayaran.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('detailTransactionModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-6 bg-white space-y-4">
                <!-- Summary Card -->
                <div class="p-4 rounded-xl border border-sky-200 bg-sky-50/60 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Nominal Pembayaran</span>
                        <span class="text-2xl font-black text-slate-900 font-mono block mt-0.5" id="detailModalNominal">Rp 0</span>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-xs text-slate-500">Invoice:</span>
                            <span class="text-xs font-bold font-mono text-sky-700" id="detailModalInvoice">-</span>
                        </div>
                    </div>
                    <div class="text-right" id="detailModalStatus">
                        <span class="badge badge-published !text-xs !px-3 !py-1">SUCCESS</span>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-1">Tanggal Bayar</span>
                        <span class="font-bold text-slate-800" id="detailModalTanggal">-</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-1">Metode Pembayaran</span>
                        <span class="font-bold text-slate-800" id="detailModalMetode">-</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-1">Nama Pelanggan</span>
                        <span class="font-bold text-slate-800" id="detailModalNama">-</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-1">Username PPPoE</span>
                        <span class="font-bold text-slate-800 font-mono" id="detailModalUsername">-</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-1">Paket Layanan</span>
                        <span class="font-bold text-slate-800" id="detailModalPaket">-</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-1">No. WhatsApp</span>
                        <span class="font-bold text-slate-800" id="detailModalWhatsapp">-</span>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                    <span class="text-slate-400 block text-[11px] font-medium mb-1">ID Referensi / Xendit Payment ID</span>
                    <span class="font-mono text-slate-700 font-semibold break-all" id="detailModalXenditId">-</span>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                    <span class="text-slate-400 block text-[11px] font-medium mb-1">Alamat Pemasangan</span>
                    <span class="text-slate-700 leading-relaxed block" id="detailModalAlamat">-</span>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2 rounded-b-2xl">
                <div class="flex items-center gap-2">
                    <a id="detailModalInvoiceBtn" href="#" class="btn-primary-admin !h-9 !px-3.5 text-xs font-semibold !bg-indigo-600 hover:!bg-indigo-700 !flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-file-invoice"></i> Lihat Invoice
                    </a>
                    <a id="detailModalPrintBtn" href="#" target="_blank" class="btn-primary-admin !h-9 !px-3.5 text-xs font-semibold !bg-rose-600 hover:!bg-rose-700 !flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-file-pdf"></i> Cetak PDF
                    </a>
                </div>
                <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('detailTransactionModal')">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            function showTransactionDetail(tx) {
                document.getElementById('detailModalNominal').innerText = tx.nominal || 'Rp 0';
                document.getElementById('detailModalInvoice').innerText = tx.invoice_number || '-';
                document.getElementById('detailModalTanggal').innerText = tx.tanggal_bayar || '-';
                document.getElementById('detailModalMetode').innerText = tx.metode || '-';
                document.getElementById('detailModalNama').innerText = tx.nama || '-';
                document.getElementById('detailModalUsername').innerText = tx.username || '-';
                document.getElementById('detailModalPaket').innerText = tx.paket || '-';
                document.getElementById('detailModalWhatsapp').innerText = tx.whatsapp || '-';
                document.getElementById('detailModalXenditId').innerText = tx.xendit_id || '-';
                document.getElementById('detailModalAlamat').innerText = tx.alamat || '-';

                // Status badge
                const stEl = document.getElementById('detailModalStatus');
                if (tx.status === 'SUCCESS' || tx.status === 'BERHASIL') {
                    stEl.innerHTML = '<span class="badge badge-published !text-xs !px-3 !py-1 font-bold"><i class="fa-solid fa-circle-check mr-1"></i> BERHASIL</span>';
                } else if (tx.status === 'PENDING') {
                    stEl.innerHTML = '<span class="badge !text-xs !px-3 !py-1 font-bold" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a;"><i class="fa-solid fa-clock mr-1"></i> PENDING</span>';
                } else {
                    stEl.innerHTML = '<span class="badge !text-xs !px-3 !py-1 font-bold" style="background:#fef2f2; color:#be123c; border:1px solid #fecdd3;"><i class="fa-solid fa-circle-xmark mr-1"></i> GAGAL</span>';
                }

                // Invoice link
                const invBtn = document.getElementById('detailModalInvoiceBtn');
                if (tx.invoice_url) {
                    invBtn.href = tx.invoice_url;
                    invBtn.style.display = 'inline-flex';
                } else {
                    invBtn.style.display = 'none';
                }

                // Print PDF link
                const printBtn = document.getElementById('detailModalPrintBtn');
                if (tx.print_url) {
                    printBtn.href = tx.print_url;
                    printBtn.style.display = 'inline-flex';
                } else {
                    printBtn.style.display = 'none';
                }

                openCustomModal('detailTransactionModal');
            }

            $(document).ready(function() {
                const table = $('#transaction-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('transactions.index') }}",
                        data: function(d) {
                            d.payment_method = $('#filter-payment-method').val();
                            d.status = $('#filter-status').val();
                            d.date_from = $('#filter-date-from').val();
                            d.date_to = $('#filter-date-to').val();
                        }
                    },
                    columns: [{
                            data: 'no',
                            name: 'no',
                            searchable: false,
                            orderable: false,
                            className: 'text-center text-slate-400 font-bold',
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: 'payment_date_formatted',
                            name: 'payment_date'
                        },
                        {
                            data: 'pelanggan',
                            name: 'pelanggan'
                        },
                        {
                            data: 'invoice_number',
                            name: 'invoice_number'
                        },
                        {
                            data: 'nominal',
                            name: 'amount_paid'
                        },
                        {
                            data: 'metode',
                            name: 'payment_method'
                        },
                        {
                            data: 'status_badge',
                            name: 'status'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            searchable: false,
                            orderable: false,
                            className: 'text-center'
                        }
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

                // Filter on select or date change
                $('#filter-payment-method, #filter-status, #filter-date-from, #filter-date-to').on('change', function() {
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
