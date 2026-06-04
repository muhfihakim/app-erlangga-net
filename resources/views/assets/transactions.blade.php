<x-layouts.app>
    @section('Css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            /* Hakatech DataTables Customization */
            .dataTables_wrapper { padding: 0; }
            .dataTables_filter { display: none; }
            .dataTables_paginate {
                display: flex; justify-content: flex-end; padding: 1.25rem 1.5rem; gap: 4px;
                border-top: 1px solid #f1f5f9;
            }
            .paginate_button {
                width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid #e2e8f0;
                background: #fff; color: #64748b !important; font-size: 13px; font-weight: 600;
                display: inline-flex; align-items: center; justify-content: center; cursor: pointer;
            }
            .paginate_button.current { background: #0ea5e9; border-color: #0ea5e9; color: #fff !important; }

            .badge-masuk { background: #dcfce7; color: #15803d; }
            .badge-keluar { background: #fee2e2; color: #dc2626; }

            .table-toolbar {
                background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
                padding: 1rem 1.25rem; display: flex; align-items: center;
                justify-content: space-between; gap: 12px; flex-wrap: wrap;
                margin-bottom: 1rem; box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }

            .filter-card {
                background: white; border: 1px solid #e2e8f0; border-radius: 16px;
                padding: 1.5rem; margin-bottom: 1.25rem; box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }

            .form-label-custom {
                font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase;
                margin-bottom: 6px; display: block; letter-spacing: 0.025em;
            }

            .form-input-custom {
                width: 100%; height: 40px !important; padding: 8px 12px;
                border: 2px solid #e2e8f0; border-radius: 10px; font-size: 13px;
                font-weight: 600; color: #334155; background: #f8fafc; outline: none; transition: all 0.2s;
            }
            .form-input-custom:focus {
                border-color: #0ea5e9; background: #fff; box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
            }

            /* Select2 Height Match - OLT Style */
            .select2-container--default .select2-selection--single {
                height: 40px !important; border: 2px solid #e2e8f0 !important; border-radius: 10px !important;
                display: flex !important; align-items: center !important; background-color: #f8fafc !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 40px !important; padding-left: 12px !important; font-size: 13px !important; font-weight: 600 !important; color: #334155 !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px !important; }
            .select2-container--default.select2-container--focus .select2-selection--single {
                border-color: #0ea5e9 !important; background-color: #fff !important; box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1) !important;
            }

            .admin-table thead th {
                text-transform: uppercase !important; font-weight: 700; font-size: 11px;
                color: #64748b; letter-spacing: 0.05em; text-align: center;
            }
            .admin-table tbody td { font-size: 12px; font-weight: 600; text-align: center; }
        </style>
    @endsection

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Riwayat Mutasi Barang</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Log lengkap arus keluar dan masuk inventaris gudang.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('assets.index') }}" class="btn-cancel !bg-white !border-slate-200 !text-slate-600 !flex !items-center !justify-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Stok
            </a>
            <button onclick="window.print()" class="btn-primary-admin !bg-slate-800 !flex !items-center !justify-center gap-2">
                <i class="fa-solid fa-file-export"></i> Export Laporan
            </button>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filter-card">
        <form action="{{ route('assets.transactions') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label-custom">Tipe Transaksi</label>
                <select name="type" class="form-input-custom select2-basic" style="width: 100%;">
                    <option value="">Semua Jenis</option>
                    <option value="Masuk" {{ request('type') === 'Masuk' ? 'selected' : '' }}>Barang Masuk (+)</option>
                    <option value="Keluar" {{ request('type') === 'Keluar' ? 'selected' : '' }}>Barang Keluar (-)</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label-custom">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-input-custom" value="{{ request('start_date') }}">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label-custom">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-input-custom" value="{{ request('end_date') }}">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary-admin !h-[40px] !px-6 !flex !items-center gap-2">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('assets.transactions') }}" class="btn-cancel !h-[40px] !w-[40px] !p-0 !flex !items-center !justify-center" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table id="transactionTable" class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Tanggal</th>
                        <th style="text-align: left;">Nama Barang</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Penerima / Pengambil</th>
                        <th style="text-align: left;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trans)
                        <tr>
                            <td style="color: #94a3b8; font-weight: 700;">
                                {{ ($transactions->currentPage() - 1) * $transactions->perPage() + $loop->iteration }}
                            </td>
                            <td style="color: #64748b; font-size: 11px;">
                                {{ \Carbon\Carbon::parse($trans->date)->format('d/m/Y') }}
                            </td>
                            <td style="text-align: left; font-weight: 800; color: #0f172a;">
                                {{ $trans->asset->name }}
                            </td>
                            <td>
                                <span class="badge {{ $trans->type === 'Masuk' ? 'badge-masuk' : 'badge-keluar' }}">
                                    {{ strtoupper($trans->type) }}
                                </span>
                            </td>
                            <td>
                                <span style="font-weight: 900; font-size: 14px; {{ $trans->type === 'Masuk' ? 'color: #059669;' : 'color: #dc2626;' }}">
                                    {{ $trans->type === 'Masuk' ? '+' : '-' }}{{ number_format($trans->quantity) }}
                                </span>
                            </td>
                            <td>
                                @if($trans->user)
                                    <div class="flex items-center justify-center gap-2">
                                        <div style="width: 24px; height: 24px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; color: #64748b;">
                                            {{ strtoupper(substr($trans->user->nama_lengkap, 0, 1)) }}
                                        </div>
                                        <span style="font-weight: 700; color: #334155;">{{ $trans->user->nama_lengkap }}</span>
                                    </div>
                                @else
                                    <span style="color: #94a3b8; font-style: italic;">{{ $trans->external_person_name ?: '-' }}</span>
                                @endif
                            </td>
                            <td style="text-align: left; color: #64748b; font-size: 11px; font-style: italic;">
                                {{ $trans->description }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 3rem 0; color: #94a3b8; font-weight: 600;">Belum ada data transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="p-4 bg-slate-50 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                $('.select2-basic').select2({
                    width: '100%',
                    minimumResultsForSearch: Infinity
                });
            });
        </script>
    @endsection
</x-layouts.app>
