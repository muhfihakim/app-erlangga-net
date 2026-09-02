<x-layouts.app>
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
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                <a href="{{ route('invoices.index') }}" class="text-slate-400 hover:text-sky-600 transition-colors"><i
                        class="fa-solid fa-arrow-left mr-1"></i></a>
                Detail Tagihan: {{ $pppoe->preOrder->nama_lengkap ?? 'Pelanggan' }}
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0; margin-left:28px;">
                Username PPPoE: <strong class="text-slate-800">{{ $pppoe->username }}</strong> | No. WhatsApp: <strong
                    class="text-slate-800">{{ $pppoe->preOrder->no_whatsapp ?? '-' }}</strong>
            </p>
        </div>
        <div class="flex items-center gap-2 ml-auto">
            <button type="button" onclick="openCustomModal('generateSingleModal')"
                class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !text-white !gap-2 inline-flex items-center shadow-sm">
                <i class="fa-solid fa-plus"></i> Buat Tagihan Manual
            </button>
        </div>
    </div>

    <!-- Summary Cards Grid -->
    <div
        style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <!-- Card 1: Tagihan Selanjutnya -->
        <div
            style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:1.1rem 1.25rem; display:flex; align-items:center; gap:1rem; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div
                style="width:46px; height:46px; border-radius:10px; background:{{ $dueIconBg }}; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
                <i class="{{ $dueIconClass }}"></i>
            </div>
            <div>
                <div
                    style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">
                    Tagihan Selanjutnya</div>
                <div
                    style="font-size:17px; font-weight:900; color:#1e293b; display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                    {{ $nextDueDate }}
                    <span class="badge"
                        style="{{ $dueStatusBadgeClass }} font-size:10.5px; padding:2px 7px; border-radius:5px; font-weight:700;">{{ $dueStatusText }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Status Tagihan -->
        <div
            style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:1.1rem 1.25rem; display:flex; align-items:center; gap:1rem; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div
                style="width:46px; height:46px; border-radius:10px; background:{{ $unpaidCount > 0 ? '#fff1f2' : '#ecfdf5' }}; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
                <i
                    class="fa-solid {{ $unpaidCount > 0 ? 'fa-file-invoice-dollar text-rose-600' : 'fa-circle-check text-emerald-600' }}"></i>
            </div>
            <div>
                <div
                    style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">
                    Status Pembayaran</div>
                @if ($unpaidCount > 0)
                    <div style="font-size:15px; font-weight:800; color:#be123c;">
                        {{ $unpaidCount }} Belum Lunas <span
                            style="font-size:12.5px; font-weight:600; color:#e11d48;">(Rp
                            {{ number_format($unpaidAmount, 0, ',', '.') }})</span>
                    </div>
                @else
                    <div style="font-size:15px; font-weight:800; color:#059669;">
                        Lunas <span style="font-size:12px; font-weight:500; color:#64748b;">(Semua Terbayar)</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Card 3: Paket & Biaya -->
        <div
            style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:1.1rem 1.25rem; display:flex; align-items:center; gap:1rem; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div
                style="width:46px; height:46px; border-radius:10px; background:#e0e7ff; display:flex; align-items:center; justify-content:center; font-size:18px; color:#4338ca; flex-shrink:0;">
                <i class="fa-solid fa-wifi"></i>
            </div>
            <div>
                <div
                    style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">
                    Paket Internet</div>
                <div style="font-size:15px; font-weight:800; color:#1e293b;">
                    {{ $pppoe->paket->nama_paket ?? '-' }}
                </div>
                <div style="font-size:11.5px; color:#64748b; font-weight:600;">Rp
                    {{ number_format($pppoe->paket ? $pppoe->paket->total_harga : $pppoe->paket->harga ?? 0, 0, ',', '.') }}
                    / bulan</div>
            </div>
        </div>

        <!-- Card 4: Tipe & Periode Billing -->
        <div
            style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:1.1rem 1.25rem; display:flex; align-items:center; gap:1rem; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div
                style="width:46px; height:46px; border-radius:10px; background:#f0fdf4; display:flex; align-items:center; justify-content:center; font-size:18px; color:#16a34a; flex-shrink:0;">
                <i class="fa-solid fa-arrows-rotate"></i>
            </div>
            <div>
                <div
                    style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">
                    Metode & Siklus</div>
                <div style="font-size:14.5px; font-weight:800; color:#1e293b;">
                    {{ strtoupper($pppoe->tipe_pembayaran ?? 'Prepaid') }}
                </div>
                <div style="font-size:11.5px; color:#64748b; font-weight:600;">Periode: <span class="badge"
                        style="background:#f1f5f9; color:#475569; font-size:10.5px; padding:1px 6px; border-radius:4px; font-weight:700;">{{ ucfirst($pppoe->periode_billing ?? 'Fixed') }}</span>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl border border-emerald-200 mb-4">
            <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="table-card mt-4">
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
                            <td class="text-right font-medium text-slate-700">
                                {{ number_format($invoice->total, 0, ',', '.') }}</td>
                            <td class="text-center text-slate-500">{{ $invoice->created_at->format('d/m/Y') }}</td>
                            <td class="text-center font-medium text-slate-600">
                                {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="text-center">
                                @if ($invoice->status == 'paid')
                                    <div class="inline-flex flex-col items-center gap-1">
                                        <span class="badge badge-published">LUNAS</span>
                                        @if($invoice->payment_method)
                                            @php
                                                $pm = strtoupper($invoice->payment_method);
                                                $pmBadge = 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;';
                                                $pmIcon = 'fa-money-bill-wave';
                                                if (str_contains($pm, 'BCA')) {
                                                    $pmBadge = 'background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe;';
                                                    $pmIcon = 'fa-building-columns';
                                                } elseif (str_contains($pm, 'BRI')) {
                                                    $pmBadge = 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;';
                                                    $pmIcon = 'fa-building-columns';
                                                } elseif (str_contains($pm, 'BNI') || str_contains($pm, 'MANDIRI') || str_contains($pm, 'PERMATA') || str_contains($pm, 'CIMB') || str_contains($pm, 'BSI')) {
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
                                            <span class="badge" style="{{ $pmBadge }} font-size:10px; padding:2px 7px; border-radius:4px; font-weight:700;">
                                                <i class="fa-solid {{ $pmIcon }} mr-1"></i> {{ $pm }}
                                            </span>
                                        @endif
                                    </div>
                                @elseif($invoice->status == 'unpaid')
                                    <span class="badge" style="background:#fef2f2;color:#be123c;">BELUM LUNAS</span>
                                @else
                                    <span class="badge"
                                        style="background:#f1f5f9;color:#64748b;">{{ strtoupper($invoice->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="inline-flex items-center justify-center gap-1.5 flex-wrap">
                                    <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank"
                                        class="btn-primary-admin !h-8 !px-2.5 !text-[11px] !bg-sky-600 hover:!bg-sky-700 !gap-1 inline-flex items-center justify-center"
                                        title="Unduh PDF">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                    @if ($invoice->status == 'unpaid')
                                        <form action="{{ route('invoices.pay_xendit', $invoice->id) }}" method="POST"
                                            class="inline-block no-loader" data-no-validation="true"
                                            onsubmit="openXenditModal(event, this)">
                                            @csrf
                                            <button type="submit"
                                                class="btn-primary-admin !h-8 !px-3 !text-[11px] !bg-indigo-600 hover:!bg-indigo-700 !gap-1.5 inline-flex items-center"
                                                title="Bayar via Xendit">
                                                <i class="fa-solid fa-credit-card"></i> Xendit
                                            </button>
                                        </form>
                                        @if ($invoice->xendit_invoice_url)
                                            <button type="button"
                                                class="btn-primary-admin !h-8 !px-3 !text-[11px] !bg-amber-500 hover:!bg-amber-600 !gap-1.5 inline-flex items-center"
                                                onclick="syncXendit({{ $invoice->id }}, this)"
                                                title="Cek & Sinkronkan Status Xendit">
                                                <i class="fa-solid fa-arrows-rotate"></i> Cek Status
                                            </button>
                                        @endif
                                        <button type="button"
                                            class="btn-primary-admin !h-8 !px-3 !text-[11px] !bg-emerald-600 hover:!bg-emerald-700 !gap-1.5 inline-flex items-center"
                                            onclick="openPayModal({{ $invoice->id }}, '{{ $invoice->invoice_number }}', '{{ number_format($invoice->total, 0, ',', '.') }}')"
                                            title="Bayar Manual">
                                            <i class="fa-solid fa-money-bill-wave"></i> Manual
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400 font-bold"><i class="fa-solid fa-check"></i>
                                            Selesai</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-400 py-4">Belum ada riwayat invoice untuk
                                pelanggan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Bayar Manual -->
    <div class="modal-overlay" id="payModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-cash-register text-emerald-600"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Konfirmasi Pembayaran Manual</span>
                </div>
                <button type="button" class="modal-close" onclick="closePayModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="payForm" method="POST">
                @csrf
                <div class="modal-body p-6 space-y-4 bg-white">
                    <!-- Invoice Summary Card -->
                    <div
                        class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/60 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Nomor Invoice
                            </div>
                            <div class="text-[14px] font-black text-slate-800 font-mono" id="payModalInvNum">-</div>
                            <div class="text-[11px] text-slate-500 mt-1">Pelanggan: <strong
                                    class="text-slate-700">{{ $pppoe->preOrder->nama_lengkap ?? $pppoe->username }}</strong>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wide">Total Bayar
                            </div>
                            <div class="text-lg font-black text-emerald-700" id="payModalTotal">Rp 0</div>
                        </div>
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label !text-slate-800 !font-bold">Metode Pembayaran Penerimaan</label>
                        <select name="payment_method" class="form-input-custom w-full" required
                            style="height: 44px; font-size: 13px;">
                            <option value="cash">Cash / Tunai (Diterima Kasir/Teknisi)</option>
                            <option value="cashless">Cashless (Transfer Bank Manual / QRIS)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl flex justify-end gap-3">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        style="padding: 10px 20px;" onclick="closePayModal()">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit"
                        class="btn-save !flex !items-center !justify-center gap-2 !bg-emerald-600 hover:!bg-emerald-700 !text-white"
                        style="padding: 10px 24px;">
                        <i class="fa-solid fa-check-circle"></i> Konfirmasi Lunas
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
                    <button type="button" class="btn-primary-admin !h-10 !px-4 !bg-indigo-600 hover:!bg-indigo-700"
                        onclick="copyXenditLink()" title="Salin Link">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                </div>
                <div class="border rounded-xl overflow-hidden shadow-sm" style="height: 500px; background: #f8fafc;">
                    <iframe id="xenditIframe" src="" width="100%" height="100%"
                        frameborder="0"></iframe>
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
                        Apakah Anda yakin ingin membuat tagihan faktur baru untuk pelanggan <span
                            class="text-sky-600 font-bold">{{ $pppoe->preOrder->nama_lengkap ?? $pppoe->username }}</span>?
                    </p>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <form action="{{ route('invoices.generate_single', $pppoe->id) }}" method="POST"
                    class="w-full flex justify-center gap-3">
                    @csrf
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        style="padding: 10px 24px;" onclick="closeCustomModal('generateSingleModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit"
                        class="btn-save !bg-sky-600 hover:!bg-sky-700 !flex !items-center !justify-center gap-2"
                        style="padding: 10px 24px;">
                        <i class="fa-solid fa-plus"></i> Ya, Buat Tagihan!
                    </button>
                </form>
            </div>
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
        </script>
    @endsection
</x-layouts.app>
