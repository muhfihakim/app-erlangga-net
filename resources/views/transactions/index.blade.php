<x-layouts.app>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Log Transaksi</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Riwayat aktivitas pembayaran manual & otomatis via gateway.</p>
        </div>
    </div>

    <div class="table-card mt-6">
        <div class="table-responsive">
            <table class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: center;">Tgl Pembayaran</th>
                        <th style="text-align: center;">Pelanggan</th>
                        <th style="text-align: center;">No. Invoice</th>
                        <th style="text-align: center;">Nominal (Rp)</th>
                        <th style="text-align: center;">Metode</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-center">{{ $trx->payment_date ? \Carbon\Carbon::parse($trx->payment_date)->format('d/m/Y H:i') : '-' }}</td>
                            <td class="text-center">{{ $trx->invoice->pppoe->preOrder->nama_lengkap ?? '-' }}</td>
                            <td class="text-center font-bold text-slate-700">{{ $trx->invoice->invoice_number ?? '-' }}</td>
                            <td class="text-center font-black text-slate-800">{{ number_format($trx->amount_paid, 0, ',', '.') }}</td>
                            <td class="text-center">{{ strtoupper($trx->payment_method) }}</td>
                            <td class="text-center">
                                @if($trx->status == 'success')
                                    <span class="badge badge-published">SUCCESS</span>
                                @elseif($trx->status == 'pending')
                                    <span class="badge" style="background:#fef3c7;color:#b45309;">PENDING</span>
                                @else
                                    <span class="badge" style="background:#fef2f2;color:#be123c;">{{ strtoupper($trx->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-400 py-4">Belum ada riwayat transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
