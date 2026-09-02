<x-layouts.app>
    @section('Css')
        <style>
            #transaction-table thead th,
            #transaction-table tbody td {
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

    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Log Riwayat Transaksi
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Riwayat aktivitas penerimaan pembayaran manual &
                gateway online.</p>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="stats-grid mb-6" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <!-- Monthly Earning -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
                <div class="stat-val">Rp {{ number_format($monthlyEarning, 0, ',', '.') }}</div>
                <div style="font-size:11px; color:#64748b; font-weight:600; margin-top:2px;">
                    Rp {{ number_format($lastMonthEarning, 0, ',', '.') }} Last Month
                </div>
                <div class="stat-lbl" style="margin-top:2px;">Monthly Earning</div>
            </div>
        </div>

        <!-- Monthly Expenses -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#be123c;">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <div>
                <div class="stat-val">Rp {{ number_format($monthlyExpenses, 0, ',', '.') }}</div>
                <div style="font-size:11px; color:#64748b; font-weight:600; margin-top:2px;">
                    Rp {{ number_format($lastMonthExpenses, 0, ',', '.') }} Last Month
                </div>
                <div class="stat-lbl" style="margin-top:2px;">Monthly Expenses</div>
            </div>
        </div>

        <!-- Monthly Profit -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0e7ff;color:#4338ca;">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <div class="stat-val">Rp {{ number_format($monthlyProfit, 0, ',', '.') }}</div>
                <div style="font-size:11px; color:#059669; font-weight:600; margin-top:2px;">
                    <i class="fa-solid fa-arrow-trend-up mr-1"></i> Pendapatan Bersih
                </div>
                <div class="stat-lbl" style="margin-top:2px;">Monthly Profit</div>
            </div>
        </div>

        <!-- Monthly Transaction -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0284c7;">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($monthlyTxCount, 0, ',', '.') }}</div>
                <div style="font-size:11px; color:#64748b; font-weight:600; margin-top:2px;">
                    {{ number_format($lastMonthTxCount, 0, ',', '.') }} Last Month
                </div>
                <div class="stat-lbl" style="margin-top:2px;">Monthly Transaction</div>
            </div>
        </div>
    </div>

    <!-- Table Toolbar -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('transactions.index') }}" class="w-full" id="filterForm">
            <div class="filter-group">
                <div style="flex: 1; min-width: 170px;">
                    <label class="form-label-custom">Metode Pembayaran</label>
                    <select name="payment_method" class="form-input-custom" id="filter-payment-method">
                        <option value="">Semua Metode</option>
                        <option value="cash" {{ $filterMethod === 'cash' ? 'selected' : '' }}>Cash (Tunai)</option>
                        <option value="cashless" {{ $filterMethod === 'cashless' ? 'selected' : '' }}>Cashless
                            (Transfer/Qris)</option>
                        <option value="xendit" {{ $filterMethod === 'xendit' ? 'selected' : '' }}>Xendit Payment Gateway
                        </option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label class="form-label-custom">Status</label>
                    <select name="status" class="form-input-custom" id="filter-status">
                        <option value="">Semua Status</option>
                        <option value="success" {{ $filterStatus === 'success' ? 'selected' : '' }}>SUCCESS (Berhasil)
                        </option>
                        <option value="pending" {{ $filterStatus === 'pending' ? 'selected' : '' }}>PENDING (Menunggu)
                        </option>
                        <option value="failed" {{ $filterStatus === 'failed' ? 'selected' : '' }}>FAILED (Gagal)
                        </option>
                    </select>
                </div>
                <div style="flex: 1.5; min-width: 220px;">
                    <label class="form-label-custom">Live Search</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input custom-search" id="custom-search"
                            placeholder="Cari nama, invoice, nominal..." autocomplete="off"
                            onkeydown="return event.key != 'Enter';" />
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
            <table id="transaction-table" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: center; width: 130px;">Tgl Bayar</th>
                        <th style="text-align: left;">Pelanggan</th>
                        <th style="text-align: center; width: 140px;">No. Invoice</th>
                        <th style="text-align: right; width: 130px;">Nominal</th>
                        <th style="text-align: center; width: 130px;">Metode</th>
                        <th style="text-align: center; width: 110px;">Status</th>
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
                const table = $('#transaction-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('transactions.index') }}",
                        data: function(d) {
                            d.payment_method = $('#filter-payment-method').val();
                            d.status = $('#filter-status').val();
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
                            name: 'status',
                            searchable: false,
                            orderable: false
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

                // Filter on select change
                $('#filter-payment-method, #filter-status').on('change', function() {
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
