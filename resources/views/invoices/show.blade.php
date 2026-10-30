<x-layouts.app>
    @section('Css')
        <style>
            .admin-table td {
                padding: 10px 14px;
                font-size: 13px;
            }
            .admin-table th {
                padding: 11px 14px;
                font-size: 12px;
                font-weight: 700;
                color: #64748b;
                letter-spacing: 0.05em;
                text-transform: uppercase !important;
            }
        </style>
    @endsection

    @php
        $unpaidInvoices = $pppoe->invoices->where('status', 'unpaid');
        $unpaidCount = $unpaidInvoices->count();
        $unpaidAmount = $unpaidInvoices->sum('total');
        $earliestUnpaid = $unpaidInvoices->sortBy('due_date')->first();

        $nextDueDate = null;
        $dueStatusText = '';
        $dueStatusBadgeClass = '';
        $dueIconClass = 'fa-solid fa-calendar-check text-emerald-600';
        $dueIconBg = '#ecfdf5';

        if ($earliestUnpaid && $earliestUnpaid->due_date) {
            $due = \Carbon\Carbon::parse($earliestUnpaid->due_date);
            $isOverdue = $due->isPast() && !$due->isToday();
            $nextDueDate = $due->format('d/m/Y');

            if ($isOverdue) {
                $diff = (int) $due
                    ->copy()
                    ->startOfDay()
                    ->diffInDays(now()->startOfDay());
                $dueStatusText = 'Lewat ' . $diff . ' hari';
                $dueStatusBadgeClass = 'background:#fff1f2; color:#e11d48; border:1px solid #fecdd3;';
                $dueIconClass = 'fa-solid fa-clock-rotate-left text-rose-600';
                $dueIconBg = '#fff1f2';
            } else {
                $dueStatusText = 'Jatuh Tempo';
                $dueStatusBadgeClass = 'background:#fffbeb; color:#b45309; border:1px solid #fef3c7;';
                $dueIconClass = 'fa-solid fa-hourglass-half text-amber-600';
                $dueIconBg = '#fffbeb';
            }
        } else {
            if ($pppoe->tanggal_aktif) {
                $dt = \Carbon\Carbon::parse($pppoe->tanggal_aktif);
                if ($dt->isPast()) {
                    $nextDue = now()
                        ->copy()
                        ->setDay(min($dt->day, now()->daysInMonth));
                    if ($nextDue->isPast()) {
                        $nextDue = now()
                            ->copy()
                            ->addMonth()
                            ->setDay(min($dt->day, now()->addMonth()->daysInMonth));
                    }
                } else {
                    $nextDue = $dt;
                }
                $nextDueDate = $nextDue->format('d/m/Y');
                $dueStatusText = 'Siklus Berikutnya';
                $dueStatusBadgeClass = 'background:#ecfdf5; color:#059669; border:1px solid #a7f3d0;';
            } else {
                $nextDueDate = '-';
                $dueStatusText = 'Belum Ada';
                $dueStatusBadgeClass = 'background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0;';
            }
        }
    @endphp

    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}"
                class="topbar-icon-btn !w-9 !h-9 !rounded-xl hover:!border-sky-300 hover:!bg-sky-50 flex items-center justify-center shrink-0"
                title="Kembali ke Daftar Invoice">
                <i class="fa-solid fa-arrow-left text-slate-600"></i>
            </a>
            <div>
                <h1 style="font-size:22px;font-weight:900;margin:0;">
                    Detail Tagihan: {{ $pppoe->preOrder->nama_lengkap ?? 'Pelanggan' }}
                </h1>
                <p style="font-size:13px;color:#64748b;margin:3px 0 0;">
                    Username PPPoE: <strong class="text-slate-800">{{ $pppoe->username }}</strong> • No. WhatsApp: <strong
                        class="text-slate-800">{{ $pppoe->preOrder->no_whatsapp ?? '-' }}</strong>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 ml-auto">
            <button type="button" onclick="openCustomModal('generateSingleModal')"
                class="btn-primary-admin !h-9 !px-4 text-xs font-semibold !flex !items-center !justify-center gap-2 !bg-sky-600 hover:!bg-sky-700">
                <i class="fa-solid fa-plus"></i> Buat Tagihan Manual
            </button>
        </div>
    </div>

    <!-- Summary Stats Grid -->
    <div class="stats-grid mb-6">
        <!-- Card 1: Tagihan Selanjutnya -->
        <div class="stat-card">
            <div class="stat-icon" style="background:{{ $dueIconBg }};">
                <i class="{{ $dueIconClass }}"></i>
            </div>
            <div>
                <div class="stat-val flex items-center gap-2 flex-wrap">
                    {{ $nextDueDate }}
                    <span class="badge"
                        style="{{ $dueStatusBadgeClass }} font-size:10px; padding:1px 6px; border-radius:4px; font-weight:700;">{{ $dueStatusText }}</span>
                </div>
                <div class="stat-lbl">Tagihan Selanjutnya</div>
            </div>
        </div>

        <!-- Card 2: Status Tagihan -->
        <div class="stat-card">
            <div class="stat-icon"
                style="background:{{ $unpaidCount > 0 ? '#fee2e2' : '#dcfce7' }};color:{{ $unpaidCount > 0 ? '#be123c' : '#15803d' }};">
                <i class="fa-solid {{ $unpaidCount > 0 ? 'fa-file-invoice-dollar' : 'fa-circle-check' }}"></i>
            </div>
            <div>
                <div class="stat-val" style="color:{{ $unpaidCount > 0 ? '#be123c' : '#15803d' }};">
                    @if ($unpaidCount > 0)
                        {{ $unpaidCount }} Belum Lunas <span
                            style="font-size:11.5px;font-weight:600;color:#e11d48;">(Rp
                            {{ number_format($unpaidAmount, 0, ',', '.') }})</span>
                    @else
                        Lunas <span style="font-size:11.5px;font-weight:600;color:#64748b;">(Semua Terbayar)</span>
                    @endif
                </div>
                <div class="stat-lbl">Status Pembayaran</div>
            </div>
        </div>

        <!-- Card 3: Paket & Biaya -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0e7ff;color:#4338ca;">
                <i class="fa-solid fa-wifi"></i>
            </div>
            <div>
                <div class="stat-val">{{ $pppoe->paket->nama_paket ?? '-' }}</div>
                <div class="stat-lbl">Rp
                    {{ number_format($pppoe->paket ? $pppoe->paket->total_harga : $pppoe->paket->harga ?? 0, 0, ',', '.') }}
                    / bulan</div>
            </div>
        </div>

        <!-- Card 4: Tipe & Periode Billing -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4;color:#16a34a;">
                <i class="fa-solid fa-arrows-rotate"></i>
            </div>
            <div>
                <div class="stat-val">{{ strtoupper($pppoe->tipe_pembayaran ?? 'Prepaid') }}</div>
                <div class="stat-lbl">Periode: {{ ucfirst($pppoe->periode_billing ?? 'Fixed') }}</div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-200 mb-6 flex items-start gap-3 shadow-sm">
            <i class="fa-solid fa-circle-check text-emerald-500 text-lg mt-0.5"></i>
            <div>
                <strong class="font-bold">Berhasil!</strong>
                <p class="text-sm mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="table-card mt-4">
        <div class="table-responsive">
            <table class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: left;">No. Invoice</th>
                        <th style="text-align: right;">Total</th>
                        <th style="text-align: center;">Tgl Dibuat</th>
                        <th style="text-align: center;">Jatuh Tempo</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center; width: 220px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pppoe->invoices as $invoice)
                        <tr>
                            <td class="text-center font-bold text-slate-400">{{ $loop->iteration }}</td>
                            <td class="text-left font-mono font-bold text-slate-800 text-xs">{{ $invoice->invoice_number }}</td>
                            <td class="text-right font-mono font-bold text-slate-900 text-xs">
                                Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                            <td class="text-center text-slate-600 font-medium text-xs">{{ $invoice->created_at->format('d/m/Y') }}</td>
                            <td class="text-center font-medium text-xs {{ $invoice->status == 'unpaid' && \Carbon\Carbon::parse($invoice->due_date)->isPast() && !\Carbon\Carbon::parse($invoice->due_date)->isToday() ? 'text-rose-600 font-bold' : 'text-slate-600' }}">
                                {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="text-center">
                                @if ($invoice->status == 'paid')
                                    <div class="inline-flex flex-col items-center gap-1">
                                        <span class="badge badge-published" style="font-size:10px; padding:2px 8px;">LUNAS</span>
                                        @if($invoice->payment_method)
                                            @php
                                                $pm = strtoupper($invoice->payment_method);
                                                $pmBadge = 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;';
                                                $pmIcon = 'fa-money-bill-wave';
                                                if (str_contains($pm, 'BCA') || str_contains($pm, 'BRI') || str_contains($pm, 'BNI') || str_contains($pm, 'MANDIRI') || str_contains($pm, 'PERMATA') || str_contains($pm, 'CIMB') || str_contains($pm, 'BSI')) {
                                                    $pmBadge = 'background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe;';
                                                    $pmIcon = 'fa-building-columns';
                                                } elseif (str_contains($pm, 'QRIS')) {
                                                    $pmBadge = 'background:#fdf2f8; color:#be185d; border:1px solid #fbcfe8;';
                                                    $pmIcon = 'fa-qrcode';
                                                } elseif (str_contains($pm, 'OVO') || str_contains($pm, 'DANA') || str_contains($pm, 'SHOPEEPAY') || str_contains($pm, 'GOPAY') || str_contains($pm, 'EWALLET')) {
                                                    $pmBadge = 'background:#faf5ff; color:#7e22ce; border:1px solid #e9d5ff;';
                                                    $pmIcon = 'fa-wallet';
                                                } elseif (str_contains($pm, 'XENDIT') || str_contains($pm, 'VIRTUAL_ACCOUNT')) {
                                                    $pmBadge = 'background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe;';
                                                    $pmIcon = 'fa-credit-card';
                                                } elseif ($pm === 'CASH') {
                                                    $pmBadge = 'background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;';
                                                    $pmIcon = 'fa-money-bill-wave';
                                                } elseif ($pm === 'CASHLESS') {
                                                    $pmBadge = 'background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0;';
                                                    $pmIcon = 'fa-receipt';
                                                }
                                            @endphp
                                            <span class="badge" style="{{ $pmBadge }} font-size:9.5px; padding:1px 6px; border-radius:4px; font-weight:700;">
                                                <i class="fa-solid {{ $pmIcon }} mr-1"></i> {{ $pm }}
                                            </span>
                                        @endif
                                    </div>
                                @elseif($invoice->status == 'unpaid')
                                    <span class="badge" style="background:#fef2f2; color:#be123c; font-size:10px; padding:2px 8px; border:1px solid #fecdd3; font-weight:700;">BELUM LUNAS</span>
                                @else
                                    <span class="badge" style="background:#f1f5f9; color:#64748b; font-size:10px; padding:2px 8px;">{{ strtoupper($invoice->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="inline-flex items-center justify-center gap-1.5 flex-wrap">
                                    <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank"
                                        class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-sky-300 hover:!bg-sky-50 flex items-center justify-center shrink-0"
                                        title="Unduh PDF">
                                        <i class="fa-solid fa-file-pdf text-sky-600"></i>
                                    </a>
                                    @if ($invoice->status == 'unpaid')
                                        <form action="{{ route('invoices.pay_xendit', $invoice->id) }}" method="POST"
                                            class="inline-block no-loader m-0" data-no-validation="true"
                                            onsubmit="openXenditModal(event, this)">
                                            @csrf
                                            <button type="submit"
                                                class="btn-primary-admin !h-8 !px-2.5 !text-xs !rounded-lg !bg-indigo-600 hover:!bg-indigo-700 !gap-1.5 inline-flex items-center font-semibold"
                                                title="Bayar via Xendit">
                                                <i class="fa-solid fa-credit-card"></i> Xendit
                                            </button>
                                        </form>
                                        @if ($invoice->xendit_invoice_url)
                                            <button type="button"
                                                class="btn-primary-admin !h-8 !px-2.5 !text-xs !rounded-lg !bg-amber-500 hover:!bg-amber-600 !gap-1.5 inline-flex items-center font-semibold"
                                                onclick="syncXendit({{ $invoice->id }}, this)"
                                                title="Cek Status Xendit">
                                                <i class="fa-solid fa-arrows-rotate"></i> Cek Status
                                            </button>
                                        @endif
                                        <button type="button"
                                            class="btn-primary-admin !h-8 !px-2.5 !text-xs !rounded-lg !bg-emerald-600 hover:!bg-emerald-700 !gap-1.5 inline-flex items-center font-semibold"
                                            onclick="openPayModal({{ $invoice->id }}, '{{ $invoice->invoice_number }}', '{{ number_format($invoice->total, 0, ',', '.') }}')"
                                            title="Bayar Manual">
                                            <i class="fa-solid fa-money-bill-wave"></i> Manual
                                        </button>
                                    @else
                                        <span class="badge badge-published !text-[10px] !px-2 !py-0.5">
                                            <i class="fa-solid fa-check mr-1"></i> Selesai
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-400 py-6 font-semibold">Belum ada riwayat invoice untuk pelanggan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Bayar Manual -->
    <div class="modal-overlay" id="payModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm border border-emerald-200 shrink-0">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Konfirmasi Pembayaran Manual</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Catat penerimaan kasir atau transfer bank langsung.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closePayModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="payForm" method="POST" novalidate>
                @csrf
                <div class="modal-body p-6 bg-white">
                    <!-- Invoice Summary Card -->
                    <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/70 flex items-center justify-between mb-3.5">
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Nomor Invoice</span>
                            <span class="text-sm font-black text-slate-800 font-mono block mt-0.5" id="payModalInvNum">-</span>
                            <span class="text-xs text-slate-500 mt-1 block">Pelanggan: <strong class="text-slate-700">{{ $pppoe->preOrder->nama_lengkap ?? $pppoe->username }}</strong></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">Total Tagihan</span>
                            <span class="text-base font-black text-emerald-700 block mt-0.5" id="payModalTotal">Rp 0</span>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Metode Pembayaran Penerimaan <span class="text-rose-500">*</span></label>
                        <select name="payment_method" class="form-input-custom !h-10 !font-normal w-full bg-white text-slate-800 border-slate-300 text-xs" required>
                            <option value="cash">Cash / Tunai (Diterima Kasir/Teknisi)</option>
                            <option value="cashless">Cashless (Transfer Bank Manual / QRIS)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2 rounded-b-2xl">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closePayModal()">Batal</button>
                    <button type="submit" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold !bg-emerald-600 hover:!bg-emerald-700">
                        <i class="fa-solid fa-circle-check"></i> Konfirmasi Lunas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Link Xendit -->
    <div class="modal-overlay" id="xenditModal">
        <div class="modal-box max-w-3xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm border border-indigo-200 shrink-0">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Pembayaran Xendit</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Payment gateway invoice checkout.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeXenditModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-6 bg-white">
                <div class="flex items-center gap-2 mb-3.5">
                    <input type="text" id="xenditLink" class="form-input !h-10 !font-normal bg-slate-50 text-slate-700 border-slate-300 text-xs" readonly>
                    <button type="button" class="btn-primary-admin !h-10 !px-4 text-xs font-semibold !bg-indigo-600 hover:!bg-indigo-700 shrink-0"
                        onclick="copyXenditLink()" title="Salin Link">
                        <i class="fa-solid fa-copy"></i> Salin Link
                    </button>
                </div>
                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm" style="height: 480px; background: #f8fafc;">
                    <iframe id="xenditIframe" src="" width="100%" height="100%" frameborder="0"></iframe>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2 rounded-b-2xl">
                <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeXenditModal()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Buat Tagihan Baru -->
    <div class="modal-overlay" id="generateSingleModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-200 shrink-0">
                        <i class="fa-solid fa-file-circle-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Buat Tagihan Baru</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Terbitkan invoice manual untuk pelanggan ini.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('generateSingleModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form action="{{ route('invoices.generate_single', $pppoe->id) }}" method="POST" class="m-0" novalidate>
                @csrf
                <div class="modal-body p-6 bg-white">
                    <div class="bg-sky-50 p-4 rounded-xl border border-sky-200 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center text-sm shrink-0 mt-0.5">
                            <i class="fa-solid fa-file-invoice"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-sky-900 block">Konfirmasi Tagihan Baru</span>
                            <p class="text-xs text-slate-600 m-0 mt-1 leading-relaxed">
                                Apakah Anda yakin ingin membuat tagihan faktur baru untuk pelanggan <span class="font-semibold text-sky-700">{{ $pppoe->preOrder->nama_lengkap ?? $pppoe->username }}</span>?
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2 rounded-b-2xl">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                        onclick="closeCustomModal('generateSingleModal')">Batal</button>
                    <button type="submit" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold !bg-sky-600 hover:!bg-sky-700">
                        <i class="fa-solid fa-plus"></i> Ya, Buat Tagihan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script>
            function openPayModal(invoiceId, invoiceNumber, totalFormatted) {
                document.getElementById('payForm').action = `/invoices/${invoiceId}/pay-manual`;
                if (invoiceNumber) document.getElementById('payModalInvNum').innerText = invoiceNumber;
                if (totalFormatted) document.getElementById('payModalTotal').innerText = 'Rp ' + totalFormatted;
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
                            throw new Error((data && data.error) ? data.error :
                                `Error ${res.status}: Gagal memproses pembayaran`);
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

            $(document).ready(function() {
                $('.modal-overlay').on('click', function(e) {
                    if (e.target === this) {
                        if (this.id === 'xenditModal') {
                            closeXenditModal();
                        } else {
                            closeCustomModal(this.id);
                        }
                    }
                });
            });
        </script>
    @endsection
</x-layouts.app>
