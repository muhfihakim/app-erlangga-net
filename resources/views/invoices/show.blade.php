<x-layouts.app>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">
                <a href="{{ route('invoices.index') }}" class="text-slate-400 hover:text-primary transition-colors"><i class="fa-solid fa-arrow-left mr-2"></i></a>
                Detail Tagihan: {{ $pppoe->preOrder->nama_lengkap ?? 'Pelanggan' }}
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0; margin-left:35px;">
                Username PPPoE: <strong>{{ $pppoe->username }}</strong> | Paket: <strong>{{ $pppoe->paket->nama_paket ?? '-' }}</strong>
            </p>
        </div>
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
                        <th style="text-align: left;">No. Invoice</th>
                        <th style="text-align: right;">Total (Rp)</th>
                        <th style="text-align: center;">Tgl Dibuat</th>
                        <th style="text-align: center;">Jatuh Tempo</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pppoe->invoices as $invoice)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-left font-bold text-slate-700">{{ $invoice->invoice_number }}</td>
                            <td class="text-right font-black text-slate-800">{{ number_format($invoice->total, 0, ',', '.') }}</td>
                            <td class="text-center text-slate-500">{{ $invoice->created_at->format('d/m/Y') }}</td>
                            <td class="text-center font-bold text-rose-600">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') : '-' }}</td>
                            <td class="text-center">
                                @if($invoice->status == 'paid')
                                    <span class="badge badge-published">LUNAS</span>
                                @elseif($invoice->status == 'unpaid')
                                    <span class="badge" style="background:#fef2f2;color:#be123c;">BELUM LUNAS</span>
                                @else
                                    <span class="badge" style="background:#f1f5f9;color:#64748b;">{{ strtoupper($invoice->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="btn-primary-admin !h-8 !px-3 !text-[11px] !bg-sky-600 hover:!bg-sky-700 mr-1" title="Unduh PDF">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                                @if($invoice->status == 'unpaid')
                                    <button type="button" class="btn-primary-admin !h-8 !px-3 !text-[11px] !bg-emerald-600 hover:!bg-emerald-700" onclick="openPayModal({{ $invoice->id }})" title="Bayar">
                                        <i class="fa-solid fa-money-bill-wave"></i> Bayar
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400 font-bold"><i class="fa-solid fa-check"></i> Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-400 py-4">Belum ada riwayat invoice untuk pelanggan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Bayar Manual -->
    <div class="modal-overlay" id="payModal">
        <div class="modal-box max-w-sm bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-money-bill-wave text-emerald-500"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Bayar Manual</span>
                </div>
                <button type="button" class="modal-close" onclick="closePayModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="payForm" method="POST">
                @csrf
                <div class="modal-body p-6 space-y-4 bg-white">
                    <div class="form-group !mb-0">
                        <label class="form-label !text-slate-800 !font-bold">Metode Pembayaran</label>
                        <select name="payment_method" class="form-input-custom w-full" required>
                            <option value="cash">Cash (Tunai)</option>
                            <option value="cashless">Cashless (Transfer/Qris)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel w-1/2 flex items-center justify-center gap-2" onclick="closePayModal()">
                        Batal
                    </button>
                    <button type="submit" class="btn-save w-1/2 flex items-center justify-center gap-2 !bg-emerald-600 hover:!bg-emerald-700">
                        Proses
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script>
            function openPayModal(invoiceId) {
                document.getElementById('payForm').action = `/invoices/${invoiceId}/pay-manual`;
                const modal = document.getElementById('payModal');
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
            function closePayModal() {
                const modal = document.getElementById('payModal');
                modal.classList.remove('open');
                document.body.style.overflow = '';
            }
            document.querySelector('#payModal .modal-overlay').addEventListener('click', function(e) {
                if (e.target === this) closePayModal();
            });
        </script>
    @endsection
</x-layouts.app>
