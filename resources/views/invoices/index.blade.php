<x-layouts.app>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Invoice Pelanggan</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola invoice dan terima pembayaran manual.</p>
        </div>
        <form action="{{ route('invoices.generate') }}" method="POST" class="ml-auto">
            @csrf
            <button type="submit" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700" onclick="return confirm('Generate tagihan bulan ini untuk semua pelanggan aktif (PPPoE)?')">
                <i class="fa-solid fa-bolt"></i> Generate Tagihan Masal
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl border border-emerald-200 mb-4">
            <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="table-card mt-6">
        <div class="table-responsive">
            <table class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: left;">Pelanggan</th>
                        <th style="text-align: left;">Paket Layanan</th>
                        <th style="text-align: center;">Tgl Aktif</th>
                        <th style="text-align: center;">Status Tagihan</th>
                        <th style="text-align: center;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pppoes as $pppoe)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-left font-bold text-slate-700">
                                {{ $pppoe->preOrder->nama_lengkap ?? '-' }}<br>
                                <span class="text-xs text-slate-500 font-normal">{{ $pppoe->username }}</span>
                            </td>
                            <td class="text-left">
                                <span class="text-indigo-600 font-bold">{{ $pppoe->paket->nama_paket ?? '-' }}</span><br>
                                <span class="text-xs text-slate-500">Rp {{ number_format($pppoe->paket->harga ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-center font-bold text-slate-700">
                                {{ $pppoe->tanggal_aktif ? $pppoe->tanggal_aktif->format('d/m/Y') : '-' }}
                            </td>
                            <td class="text-center">
                                @php
                                    $unpaidCount = $pppoe->invoices->where('status', 'unpaid')->count();
                                @endphp
                                @if($unpaidCount > 0)
                                    <span class="badge" style="background:#fef2f2;color:#be123c;">{{ $unpaidCount }} BELUM LUNAS</span>
                                @else
                                    <span class="badge badge-published">LUNAS</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('invoices.show', $pppoe->id) }}" class="btn-primary-admin !h-8 !px-3 !text-[11px] !bg-indigo-600 hover:!bg-indigo-700 inline-flex items-center gap-2">
                                    <i class="fa-solid fa-eye"></i> Lihat Tagihan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-slate-400 py-4">Belum ada data pelanggan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
