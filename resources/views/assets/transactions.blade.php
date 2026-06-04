<x-layouts.app>
    @section('Css')
        <style>
            .transaction-table-wrap {
                background: white;
                border-radius: 20px;
                border: 1px solid #e2e8f0;
                overflow: hidden;
            }
            .badge-masuk {
                background: #ecfdf5;
                color: #059669;
                border: 1px solid #10b98133;
            }
            .badge-keluar {
                background: #fff1f2;
                color: #e11d48;
                border: 1px solid #f43f5e33;
            }
            .filter-card {
                background: white;
                border-radius: 16px;
                border: 1px solid #e2e8f0;
                padding: 1.25rem;
            }
        </style>
    @endsection

    <!-- Header -->
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Riwayat Mutasi Barang</h1>
            <p class="text-sm text-slate-500">Log lengkap arus keluar dan masuk inventaris gudang.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('assets.index') }}" class="btn-cancel !bg-white !border-slate-200 !text-slate-600 !flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Stok
            </a>
            <button onclick="window.print()" class="btn-primary-admin !bg-slate-800 !flex items-center gap-2">
                <i class="fa-solid fa-file-export"></i> Export Laporan
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card mb-6">
        <form action="{{ route('assets.transactions') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="form-group mb-0 flex-1 min-w-[200px]">
                <label class="form-label font-bold uppercase text-[10px] text-slate-400">Jenis Transaksi</label>
                <select name="type" class="form-select !h-11">
                    <option value="">Semua Jenis</option>
                    <option value="Masuk" {{ request('type') === 'Masuk' ? 'selected' : '' }}>Masuk (Stok +)</option>
                    <option value="Keluar" {{ request('type') === 'Keluar' ? 'selected' : '' }}>Keluar (Stok -)</option>
                </select>
            </div>
            <div class="form-group mb-0 flex-1 min-w-[200px]">
                <label class="form-label font-bold uppercase text-[10px] text-slate-400">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-input !h-11" value="{{ request('start_date') }}">
            </div>
            <div class="form-group mb-0 flex-1 min-w-[200px]">
                <label class="form-label font-bold uppercase text-[10px] text-slate-400">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-input !h-11" value="{{ request('end_date') }}">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary-admin !h-11 !px-6">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('assets.transactions') }}" class="btn-cancel !h-11 !w-11 !p-0 !flex items-center justify-center" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="transaction-table-wrap">
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th class="text-center w-16">#</th>
                    <th>Tanggal</th>
                    <th>Nama Barang</th>
                    <th class="text-center">Tipe</th>
                    <th class="text-center">Jumlah</th>
                    <th>Penanggung Jawab / Pihak Luar</th>
                    <th>Keterangan / Tujuan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $trans)
                    <tr>
                        <td class="text-center font-bold text-slate-400">
                            {{ ($transactions->currentPage() - 1) * $transactions->perPage() + $loop->iteration }}
                        </td>
                        <td class="text-xs font-bold text-slate-600 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($trans->date)->format('d M Y') }}
                        </td>
                        <td class="font-black text-slate-800 uppercase">
                            {{ $trans->asset->name }}
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $trans->type === 'Masuk' ? 'badge-masuk' : 'badge-keluar' }} !text-[9px] !font-black !px-2">
                                {{ $trans->type }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="font-black {{ $trans->type === 'Masuk' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $trans->type === 'Masuk' ? '+' : '-' }}{{ number_format($trans->quantity) }}
                            </span>
                        </td>
                        <td>
                            @if($trans->user)
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500 uppercase">
                                        {{ substr($trans->user->nama_lengkap, 0, 1) }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-700">{{ $trans->user->nama_lengkap }}</span>
                                </div>
                            @else
                                <span class="text-xs font-bold text-slate-500 italic">{{ $trans->external_person_name ?: '-' }}</span>
                            @endif
                        </td>
                        <td class="text-xs text-slate-500 leading-relaxed max-w-xs truncate" title="{{ $trans->description }}">
                            {{ $trans->description }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <div class="flex flex-col items-center">
                                <i class="fa-solid fa-folder-open text-slate-200 text-4xl mb-3"></i>
                                <p class="text-slate-400 font-bold">Belum ada riwayat transaksi.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="p-6 bg-slate-50 border-t border-slate-200">
            {{ $transactions->appends(request()->query())->links() }}
        </div>
    </div>
</x-layouts.app>
