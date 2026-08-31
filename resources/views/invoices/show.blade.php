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
        <button type="button" onclick="openCustomModal('generateSingleModal')" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !gap-2 inline-flex items-center shadow-sm ml-auto">
            <i class="fa-solid fa-plus"></i> Buat Tagihan Baru
        </button>
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
                            <td class="text-right font-medium text-slate-700">{{ number_format($invoice->total, 0, ',', '.') }}</td>
                            <td class="text-center text-slate-500">{{ $invoice->created_at->format('d/m/Y') }}</td>
                            <td class="text-center font-medium text-slate-600">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') : '-' }}</td>
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
                                <div class="inline-flex items-center justify-center gap-1.5 flex-wrap">
                                    <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="btn-primary-admin !h-8 !px-2.5 !text-[11px] !bg-sky-600 hover:!bg-sky-700 !gap-1 inline-flex items-center justify-center" title="Unduh PDF">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                    @if($invoice->status == 'unpaid')
                                        <form action="{{ route('invoices.pay_xendit', $invoice->id) }}" method="POST" class="inline-block no-loader" data-no-validation="true" onsubmit="openXenditModal(event, this)">
                                            @csrf
                                            <button type="submit" class="btn-primary-admin !h-8 !px-3 !text-[11px] !bg-indigo-600 hover:!bg-indigo-700 !gap-1.5 inline-flex items-center" title="Bayar via Xendit">
                                                <i class="fa-solid fa-credit-card"></i> Xendit
                                            </button>
                                        </form>
                                        @if($invoice->xendit_invoice_url)
                                            <button type="button" class="btn-primary-admin !h-8 !px-3 !text-[11px] !bg-amber-500 hover:!bg-amber-600 !gap-1.5 inline-flex items-center" onclick="syncXendit({{ $invoice->id }}, this)" title="Cek & Sinkronkan Status Xendit">
                                                <i class="fa-solid fa-arrows-rotate"></i> Cek Status
                                            </button>
                                        @endif
                                        <button type="button" class="btn-primary-admin !h-8 !px-3 !text-[11px] !bg-emerald-600 hover:!bg-emerald-700 !gap-1.5 inline-flex items-center" onclick="openPayModal({{ $invoice->id }})" title="Bayar Manual">
                                            <i class="fa-solid fa-money-bill-wave"></i> Manual
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400 font-bold"><i class="fa-solid fa-check"></i> Selesai</span>
                                    @endif
                                </div>
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

    <!-- Modal Link Xendit -->
    <div class="modal-overlay" id="xenditModal">
        <div class="modal-box max-w-3xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-link text-indigo-500"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Pembayaran Xendit</span>
                </div>
                <button type="button" class="modal-close" onclick="closeXenditModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-6 space-y-4 bg-white text-center">
                <div class="flex items-center gap-2 mb-4">
                    <input type="text" id="xenditLink" class="form-input-custom w-full !bg-slate-100" readonly>
                    <button type="button" class="btn-primary-admin !h-10 !px-4 !bg-indigo-600 hover:!bg-indigo-700" onclick="copyXenditLink()" title="Salin Link">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                </div>
                <div class="border rounded-xl overflow-hidden shadow-sm" style="height: 500px; background: #f8fafc;">
                    <iframe id="xenditIframe" src="" width="100%" height="100%" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Buat Tagihan Baru -->
    <div class="modal-overlay" id="generateSingleModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-file-circle-plus text-sky-600"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Buat Tagihan Baru?</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('generateSingleModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-8 bg-white">
                <div class="bg-sky-50 p-4 rounded-2xl border border-sky-100 text-center">
                    <i class="fa-solid fa-file-invoice text-sky-600 text-3xl mb-3"></i>
                    <p class="text-sm font-bold text-slate-800 leading-relaxed">
                        Apakah Anda yakin ingin membuat tagihan faktur baru untuk pelanggan <span class="text-sky-600 font-bold">{{ $pppoe->preOrder->nama_lengkap ?? $pppoe->username }}</span>?
                    </p>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <form action="{{ route('invoices.generate_single', $pppoe->id) }}" method="POST" class="w-full flex justify-center gap-3">
                    @csrf
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" 
                        style="padding: 10px 24px;"
                        onclick="closeCustomModal('generateSingleModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-save !bg-sky-600 hover:!bg-sky-700 !flex !items-center !justify-center gap-2"
                        style="padding: 10px 24px;">
                        <i class="fa-solid fa-plus"></i> Ya, Buat Tagihan!
                    </button>
                </form>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script>
            function openPayModal(invoiceId) {
                document.getElementById('payForm').action = `/invoices/${invoiceId}/pay-manual`;
                openCustomModal('payModal');
            }

            function closePayModal() {
                closeCustomModal('payModal');
            }

            function openXenditModal(event, form) {
                event.preventDefault();
                event.stopPropagation();
                
                const btn = form.querySelector('button[type="submit"]');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Loading...';
                btn.disabled = true;

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: new FormData(form)
                })
                .then(async res => {
                    const data = await res.json().catch(() => null);
                    if (!res.ok) {
                        throw new Error((data && data.error) ? data.error : `Error ${res.status}: Gagal memproses pembayaran`);
                    }
                    return data;
                })
                .then(data => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    
                    if (data && data.url) {
                        document.getElementById('xenditLink').value = data.url;
                        document.getElementById('xenditIframe').src = data.url;
                        openCustomModal('xenditModal');
                    } else if (data && data.error) {
                        showToast(data.error, 'error');
                    }
                })
                .catch(err => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    showToast(err.message || 'Gagal mengambil URL pembayaran Xendit. Cek koneksi.', 'error');
                });
            }

            function syncXendit(invoiceId, btn) {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                btn.disabled = true;

                fetch(`/invoices/${invoiceId}/sync-xendit`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(async res => {
                    const data = await res.json().catch(() => null);
                    if (!res.ok) {
                        throw new Error((data && data.error) ? data.error : `Error ${res.status}`);
                    }
                    return data;
                })
                .then(data => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    showToast(data.message || 'Status berhasil disinkronkan!', 'success');
                    if (data.status === 'paid' || data.status === 'SETTLED') {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                })
                .catch(err => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    showToast(err.message || 'Gagal sinkronisasi status Xendit.', 'error');
                });
            }

            function closeXenditModal() {
                closeCustomModal('xenditModal');
                document.getElementById('xenditIframe').src = "";
                window.location.reload();
            }

            function copyXenditLink() {
                const copyText = document.getElementById("xenditLink");
                if (!copyText || !copyText.value) return;
                
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(copyText.value).then(() => {
                        showToast('Link Xendit berhasil disalin!', 'success');
                    }).catch(() => {
                        document.execCommand('copy');
                        showToast('Link Xendit berhasil disalin!', 'success');
                    });
                } else {
                    document.execCommand('copy');
                    showToast('Link Xendit berhasil disalin!', 'success');
                }
            }
        </script>
    @endsection
</x-layouts.app>
