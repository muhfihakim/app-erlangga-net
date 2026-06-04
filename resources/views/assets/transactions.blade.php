<x-layouts.app>
    @section('Css')
        <style>
            /* Hakatech DataTables Customization */
            .dataTables_wrapper { padding: 0; }
            .dataTables_filter { display: none; }
            .dataTables_paginate {
                @apply flex justify-end items-center gap-[16px] p-5 border-t border-slate-100;
            }
            .paginate_button {
                width: 36px; height: 36px; border-radius: 10px;
                @apply border border-slate-200 bg-white text-slate-500 font-semibold text-sm transition-all duration-200 inline-flex items-center justify-center cursor-pointer no-underline mx-1;
            }
            .paginate_button.current {
                @apply bg-primary border-primary text-white;
                box-shadow: 0 4px 10px rgba(14, 165, 233, 0.3);
            }

            .badge-masuk { background: #dcfce7; color: #15803d; }
            .badge-keluar { background: #fee2e2; color: #dc2626; }

            /* Toolbar & Search – Matching Pre-Order */
            .table-toolbar {
                background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
                padding: 1rem 1.25rem; display: flex; align-items: center;
                justify-content: space-between; gap: 12px; flex-wrap: wrap;
                margin-bottom: 1rem; box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }
            .filter-group { display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; width: 100%; }
            .form-label-custom { display: block; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.025em; }
            .form-input-custom {
                width: 100%; padding: 8px 12px; border: 2px solid #e2e8f0; border-radius: 10px;
                font-size: 13px; font-weight: 600; color: #334155; background: #f8fafc; outline: none; height: 40px;
            }
            .form-input-custom:focus { border-color: #0ea5e9; background: #fff; }

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
            @if (request('start_date') && request('end_date'))
                <a href="{{ route('assets.export', request()->query()) }}" class="btn-primary-admin !bg-rose-600 !flex !items-center !justify-center gap-2">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            @endif
        </div>
    </div>

    <!-- Filters Section (Matching Pre-Order Style) -->
    <div class="table-toolbar">
        <form action="{{ route('assets.transactions') }}" method="GET" class="w-full">
            <div class="filter-group">
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label-custom">Tipe Transaksi</label>
                    <select name="type" class="form-input-custom">
                        <option value="">Semua Jenis</option>
                        <option value="Masuk" {{ request('type') === 'Masuk' ? 'selected' : '' }}>Barang Masuk (+)</option>
                        <option value="Keluar" {{ request('type') === 'Keluar' ? 'selected' : '' }}>Barang Keluar (-)</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label-custom">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-input-custom" value="{{ request('start_date') }}">
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label-custom">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-input-custom" value="{{ request('end_date') }}">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary-admin !h-[40px] !px-6 !flex !items-center gap-2">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('assets.transactions') }}" class="topbar-icon-btn !w-[40px] !h-[40px] !rounded-xl !bg-white" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
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
                                @if ($trans->user)
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
        @if ($transactions->hasPages())
            <div class="p-4 bg-slate-50 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @endsection
</x-layouts.app>
