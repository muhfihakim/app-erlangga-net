<x-layouts.app>
    @section('Css')
        <style>
            @keyframes indeterminateProgress {
                0% { transform: translateX(-100%); width: 35%; }
                50% { transform: translateX(60%); width: 55%; }
                100% { transform: translateX(250%); width: 35%; }
            }
            .animate-indeterminate {
                animation: indeterminateProgress 1.4s ease-in-out infinite;
            }
            .admin-table td {
                padding: 10px 14px;
                font-size: 13px;
                vertical-align: middle;
            }
            .admin-table th {
                padding: 11px 14px;
                font-size: 12px;
                font-weight: 700;
                color: #64748b;
                letter-spacing: 0.05em;
                text-transform: uppercase !important;
                vertical-align: middle;
            }
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
                gap: 0.75rem;
                margin-bottom: 1.25rem;
            }
            .stat-card {
                background: #fff;
                border-radius: 12px;
                padding: 0.75rem 1rem;
                border: 1px solid #e2e8f0;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
                display: flex;
                align-items: center;
                gap: 12px;
                min-height: 72px;
                transition: all 0.2s;
            }
            .stat-card:hover {
                border-color: #cbd5e1;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }
            .stat-icon {
                width: 40px;
                height: 40px;
                min-width: 40px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
                flex-shrink: 0;
            }
            .stat-val {
                font-size: 17px;
                font-weight: 800;
                color: #0f172a;
                line-height: 1.25;
                display: flex;
                align-items: baseline;
                gap: 5px;
                flex-wrap: wrap;
            }
            .stat-lbl {
                font-size: 12px;
                font-weight: 600;
                color: #64748b;
                margin-top: 2px;
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
        <div class="flex items-center gap-2 ml-auto flex-wrap">
            <button type="button" onclick="openNextAdjustmentModal()"
                class="btn-secondary !h-9 !px-3.5 text-xs font-semibold !flex !items-center !justify-center gap-2 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 rounded-xl transition-all shadow-xs"
                title="Atur Penyesuaian Tagihan Siklus Berikutnya">
                <i class="fa-solid fa-sliders text-sky-600"></i>
                <span>Penyesuaian Siklus Berikutnya</span>
                @if((float)($pppoe->penyesuaian_tagihan ?? 0) != 0)
                    <span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:9.5px; padding:1px 6px; border-radius:4px; font-weight:700;">
                        {{ ($pppoe->penyesuaian_tagihan > 0 ? '+' : '') . number_format($pppoe->penyesuaian_tagihan, 0, ',', '.') }}
                    </span>
                @endif
            </button>
            <button type="button" onclick="openGenerateModal()"
                class="btn-primary-admin !h-9 !px-4 text-xs font-semibold !flex !items-center !justify-center gap-2 !bg-sky-600 hover:!bg-sky-700 shadow-xs">
                <i class="fa-solid fa-plus"></i> Buat Tagihan Baru
            </button>
        </div>
    </div>

    @if((float)($pppoe->penyesuaian_tagihan ?? 0) != 0)
        <!-- Scheduled Next Bill Adjustment Banner -->
        <div class="bg-gradient-to-r from-sky-50 to-indigo-50/50 border border-sky-200/80 rounded-xl p-3.5 mb-5 flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center shrink-0 border border-sky-200">
                    <i class="fa-solid fa-sliders text-sm"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-sky-950 flex items-center gap-2 flex-wrap">
                        <span>Penyesuaian Siklus Tagihan Berikutnya:</span>
                        <span class="badge" style="background:#0284c7; color:#fff; font-size:10.5px; padding:2px 8px; border-radius:6px; font-weight:800;">
                            {{ ($pppoe->penyesuaian_tagihan > 0 ? '+ Rp ' : '- Rp ') . number_format(abs($pppoe->penyesuaian_tagihan), 0, ',', '.') }}
                        </span>
                        <span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:10px; padding:1px 6px; border-radius:4px; font-weight:700;">
                            {{ ($pppoe->penyesuaian_tipe ?? 'sekali') === 'sekali' ? '1x Siklus Berikutnya' : 'Berulang Tiap Bulan' }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-600 mt-0.5">
                        Keterangan: <strong class="text-slate-800">{{ $pppoe->keterangan_penyesuaian ?: 'Penyesuaian Tagihan' }}</strong>. Penyesuaian ini akan otomatis diikutsertakan saat tagihan diterbitkan.
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="openNextAdjustmentModal()" class="topbar-icon-btn !h-8 !px-3 text-xs font-semibold bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 !w-auto !rounded-lg" title="Edit Penyesuaian">
                    <i class="fa-solid fa-pen-to-square mr-1"></i> Ubah
                </button>
                <form action="{{ route('invoices.clear_next_adjustment', $pppoe->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan penyesuaian tagihan berikutnya?');" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="topbar-icon-btn !w-8 !h-8 text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 !rounded-lg" title="Hapus Penyesuaian">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Summary Stats Grid -->
    <div class="stats-grid mb-6">
        <!-- Card 1: Tagihan Selanjutnya -->
        <div class="stat-card">
            <div class="stat-icon" style="background:{{ $dueIconBg }};">
                <i class="{{ $dueIconClass }}"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val">
                    <span>{{ $nextDueDate }}</span>
                    <span class="badge shrink-0"
                        style="{{ $dueStatusBadgeClass }} font-size:9.5px; padding:1px 6px; border-radius:4px; font-weight:700;">{{ $dueStatusText }}</span>
                </div>
                <div class="stat-lbl truncate">Tagihan Selanjutnya</div>
            </div>
        </div>

        <!-- Card 2: Status Tagihan -->
        <div class="stat-card">
            <div class="stat-icon"
                style="background:{{ $unpaidCount > 0 ? '#fee2e2' : '#dcfce7' }};color:{{ $unpaidCount > 0 ? '#be123c' : '#15803d' }};">
                <i class="fa-solid {{ $unpaidCount > 0 ? 'fa-file-invoice-dollar' : 'fa-circle-check' }}"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val" style="color:{{ $unpaidCount > 0 ? '#be123c' : '#15803d' }};">
                    @if ($unpaidCount > 0)
                        <span>{{ $unpaidCount }} Belum Lunas</span>
                        <span class="text-[11px] font-semibold text-rose-600 font-mono">(Rp {{ number_format($unpaidAmount, 0, ',', '.') }})</span>
                    @else
                        <span>Lunas</span>
                        <span class="text-[11px] font-medium text-slate-500 font-normal">(Semua Terbayar)</span>
                    @endif
                </div>
                <div class="stat-lbl truncate">Status Pembayaran</div>
            </div>
        </div>

        <!-- Card 3: Paket & Biaya -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0e7ff;color:#4338ca;">
                <i class="fa-solid fa-wifi"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val truncate" title="{{ $pppoe->paket->nama_paket ?? '-' }}">
                    <span>{{ $pppoe->paket->nama_paket ?? '-' }}</span>
                </div>
                <div class="stat-lbl truncate">
                    Rp {{ number_format($pppoe->paket ? $pppoe->paket->total_harga : $pppoe->paket->harga ?? 0, 0, ',', '.') }} / bulan
                </div>
            </div>
        </div>

        <!-- Card 4: Tipe & Periode Billing -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4;color:#16a34a;">
                <i class="fa-solid fa-arrows-rotate"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val">
                    <span>{{ strtoupper($pppoe->tipe_pembayaran ?? 'Prepaid') }}</span>
                    <span class="badge shrink-0"
                        style="background:#f1f5f9; color:#475569; font-size:9.5px; padding:1px 6px; border-radius:4px; font-weight:700;">{{ ucfirst($pppoe->periode_billing ?? 'Fixed') }}</span>
                </div>
                <div class="stat-lbl truncate">Metode & Siklus</div>
            </div>
        </div>
    </div>

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
                        <th style="text-align: center; width: 150px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pppoe->invoices as $invoice)
                        <tr>
                            <td class="text-center font-bold text-slate-400">{{ $loop->iteration }}</td>
                            <td class="text-left font-mono font-bold text-slate-800 text-xs">{{ $invoice->invoice_number }}</td>
                            <td class="text-right font-mono font-bold text-slate-900 text-xs">
                                <div>Rp {{ number_format($invoice->total, 0, ',', '.') }}</div>
                                @if((float)($invoice->penyesuaian ?? 0) != 0)
                                    <div class="mt-0.5">
                                        <span class="badge" style="{{ $invoice->penyesuaian > 0 ? 'background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;' : 'background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;' }} font-size:9.5px; padding:1px 5px; border-radius:4px; font-weight:700;" title="{{ $invoice->catatan_penyesuaian ?: 'Penyesuaian' }}">
                                            {{ ($invoice->penyesuaian > 0 ? '+' : '') . number_format($invoice->penyesuaian, 0, ',', '.') }}
                                            @if($invoice->catatan_penyesuaian)
                                                <span class="font-normal font-sans text-[9px] opacity-90">({{ \Illuminate\Support\Str::limit($invoice->catatan_penyesuaian, 14) }})</span>
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </td>
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
                            <td class="text-center" style="white-space: nowrap;">
                                <div class="flex items-center justify-center gap-1.5 flex-nowrap">
                                    <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank"
                                        class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-rose-300 hover:!bg-rose-50"
                                        title="Cetak Faktur (PDF)">
                                        <i class="fa-solid fa-print text-slate-500 hover:text-rose-600"></i>
                                    </a>
                                    @if (!empty($pppoe->preOrder->no_whatsapp ?? ''))
                                        <a href="{{ route('whatsapp.index', ['pppoe_id' => $pppoe->id, 'invoice_id' => $invoice->id]) }}"
                                            class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-emerald-300 hover:!bg-emerald-50"
                                            title="Kirim Tagihan via WhatsApp Center">
                                            <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                                        </a>
                                    @endif
                                    @if ($invoice->status == 'unpaid')
                                        <form action="{{ route('invoices.pay_xendit', $invoice->id) }}" method="POST"
                                            class="inline-flex items-center m-0 p-0 no-loader shrink-0" data-no-validation="true"
                                            onsubmit="openXenditModal(event, this)">
                                            @csrf
                                            <button type="submit"
                                                class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-indigo-300 hover:!bg-indigo-50"
                                                title="Bayar via Xendit Online">
                                                <i class="fa-solid fa-credit-card text-indigo-600"></i>
                                            </button>
                                        </form>
                                        @if ($invoice->xendit_invoice_url)
                                            <button type="button"
                                                class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-amber-300 hover:!bg-amber-50"
                                                onclick="syncXendit({{ $invoice->id }}, this)"
                                                title="Cek Status Xendit">
                                                <i class="fa-solid fa-arrows-rotate text-amber-600"></i>
                                            </button>
                                        @endif
                                        <button type="button"
                                            class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-sky-300 hover:!bg-sky-50"
                                            onclick="openAdjustModal({{ $invoice->id }}, '{{ $invoice->invoice_number }}', {{ (float)$invoice->amount }}, {{ (float)($invoice->penyesuaian ?? 0) }}, '{{ addslashes($invoice->catatan_penyesuaian ?? '') }}', {{ (float)$invoice->tax }}, {{ (float)$invoice->discount }}, '{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : '' }}')"
                                            title="Sesuaikan Tagihan">
                                            <i class="fa-solid fa-sliders text-sky-600"></i>
                                        </button>
                                        <button type="button"
                                            class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-emerald-300 hover:!bg-emerald-50"
                                            onclick="openPayModal({{ $invoice->id }}, '{{ $invoice->invoice_number }}', '{{ number_format($invoice->total, 0, ',', '.') }}')"
                                            title="Konfirmasi Pembayaran Manual">
                                            <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
                                        </button>
                                    @else
                                        <span class="topbar-icon-btn !w-8 !h-8 !rounded-lg !bg-emerald-50 !border-emerald-200 inline-flex items-center justify-center shrink-0 cursor-default" title="Tagihan Selesai (Lunas)">
                                            <i class="fa-solid fa-check text-emerald-600"></i>
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
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
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
                <div class="modal-body p-6 bg-white space-y-4">
                    <!-- Invoice Summary Card -->
                    <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/70 flex items-center justify-between">
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

                    <div class="space-y-1.5">
                        <label class="form-label text-slate-700 font-medium text-xs block !mb-0">Metode Pembayaran Penerimaan <span class="text-rose-500">*</span></label>
                        <select name="payment_method" class="form-input-custom !h-10 !font-normal w-full bg-white text-slate-800 border-slate-300 text-xs" required>
                            <option value="cash">Cash / Tunai (Diterima Kasir/Teknisi)</option>
                            <option value="cashless">Cashless (Transfer Bank Manual / QRIS)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2">
                    <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5" onclick="closePayModal()"><i class="fa-solid fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn-primary-admin !h-8.5 !px-5 text-xs font-semibold shadow-xs flex items-center gap-1.5 !bg-emerald-600 hover:!bg-emerald-700">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Link Xendit -->
    <div class="modal-overlay" id="xenditModal">
        <div class="modal-box max-w-3xl bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
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
                <div class="relative border border-slate-200 rounded-xl overflow-hidden shadow-sm" style="height: 480px; background: #f8fafc;">
                    <!-- Loading Bar / State -->
                    <div id="xenditLoadingState" class="absolute inset-0 flex flex-col items-center justify-center bg-white z-20">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-3 shadow-xs border border-indigo-100">
                            <i class="fa-solid fa-credit-card animate-pulse"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800 m-0">Menyiapkan Checkout Xendit...</h4>
                        <p class="text-xs text-slate-500 m-0 mt-1 text-center max-w-xs">
                            Menghubungkan ke gateway pembayaran dan memuat metode checkout aman.
                        </p>
                        <!-- Neat, centered progress bar -->
                        <div class="w-64 bg-slate-100 rounded-full h-1.5 overflow-hidden shadow-inner mt-4">
                            <div class="h-full bg-indigo-600 rounded-full animate-indeterminate"></div>
                        </div>
                        <span class="text-[11px] text-slate-400 font-medium mt-2.5">Mohon tunggu sebentar...</span>
                    </div>

                    <iframe id="xenditIframe" src="" width="100%" height="100%" frameborder="0"></iframe>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2">
                <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5" onclick="closeXenditModal()"><i class="fa-solid fa-xmark"></i> Tutup</button>
            </div>
        </div>
    </div>

    <!-- 1. Modal Buat Tagihan Baru (Custom / With Adjustments) -->
    <div class="modal-overlay" id="generateSingleModal">
        <div class="modal-box max-w-xl bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-200 shrink-0">
                        <i class="fa-solid fa-file-circle-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Buat Tagihan Baru</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Terbitkan invoice tagihan untuk pelanggan ini.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('generateSingleModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form action="{{ route('invoices.generate_single', $pppoe->id) }}" method="POST" class="m-0" novalidate>
                @csrf
                <input type="hidden" name="amount" id="genAmount" value="{{ (float)($pppoe->paket->harga ?? 0) }}" oninput="updateGenTotal()">
                <div class="modal-body p-6 bg-white space-y-4.5">
                    <!-- Paket Langganan Info Card -->
                    <div class="p-3.5 rounded-xl border border-sky-200 bg-sky-50/70 flex items-center justify-between">
                        <div>
                            <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider block">Paket Internet</span>
                            <span class="text-sm font-black text-slate-800 block mt-0.5">{{ $pppoe->paket->nama_paket ?? 'Paket Broadband' }}</span>
                            <span class="text-xs text-slate-500 mt-0.5 block">Pelanggan: <strong class="text-slate-700">{{ $pppoe->preOrder->nama_lengkap ?? $pppoe->username }}</strong></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider block">Harga Pokok</span>
                            <span class="text-sm font-bold text-slate-800 font-mono block mt-0.5">Rp {{ number_format($pppoe->paket->harga ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Row Penyesuaian & Jatuh Tempo (2 Kolom) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="form-label text-slate-700 font-medium text-xs block !mb-0">Penyesuaian Tagihan (Rp)</label>
                            <input type="number" step="any" name="penyesuaian" id="genPenyesuaian"
                                value="{{ (float)($pppoe->penyesuaian_tagihan ?? 0) }}"
                                oninput="updateGenTotal()"
                                class="form-input !h-9 text-xs w-full font-mono bg-white text-slate-800 border-slate-300"
                                placeholder="0">
                        </div>
                        <div class="space-y-1.5">
                            <label class="form-label text-slate-700 font-medium text-xs block !mb-0">Tanggal Jatuh Tempo <span class="text-rose-500">*</span></label>
                            <input type="date" name="due_date" id="genDueDate"
                                value="{{ now()->addDays(7)->format('Y-m-d') }}"
                                class="form-input !h-9 text-xs w-full bg-white text-slate-800 border-slate-300" required>
                        </div>
                    </div>

                    <!-- Row Keterangan Penyesuaian -->
                    <div class="space-y-1.5">
                        <label class="form-label text-slate-700 font-medium text-xs block !mb-0">Keterangan Penyesuaian</label>
                        <input type="text" name="catatan_penyesuaian" id="genCatatan"
                            value="{{ $pppoe->keterangan_penyesuaian ?? '' }}"
                            class="form-input !h-9 text-xs w-full bg-white text-slate-800 border-slate-300"
                            placeholder="Misal: Penyesuaian biaya, penambahan layanan, dsb.">
                    </div>

                    <!-- Row Pajak & Diskon (2 Kolom) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="form-label text-slate-700 font-medium text-xs block !mb-0">Pajak (Rp)</label>
                            <input type="number" step="any" name="tax" id="genTax"
                                value="{{ (float)($pppoe->pajak ?? ($pppoe->paket->pajak ?? 0)) }}"
                                oninput="updateGenTotal()"
                                class="form-input !h-9 text-xs w-full font-mono bg-white text-slate-800 border-slate-300">
                        </div>
                        <div class="space-y-1.5">
                            <label class="form-label text-slate-700 font-medium text-xs block !mb-0">Diskon (Rp)</label>
                            <input type="number" step="any" name="discount" id="genDiscount"
                                value="{{ (float)($pppoe->diskon ?? ($pppoe->paket->diskon ?? 0)) }}"
                                oninput="updateGenTotal()"
                                class="form-input !h-9 text-xs w-full font-mono bg-white text-slate-800 border-slate-300">
                        </div>
                    </div>

                    <!-- Live Total Card -->
                    <div class="p-3.5 rounded-xl border border-sky-300 bg-sky-50 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-sky-800 uppercase tracking-wider block">Total Tagihan Diterbitkan</span>
                            <span class="text-xs text-sky-600 block mt-0.5">Pokok + Penyesuaian + Pajak - Diskon</span>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-black text-sky-700 font-mono block" id="genModalTotal">Rp 0</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2">
                    <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5"
                        onclick="closeCustomModal('generateSingleModal')"><i class="fa-solid fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn-primary-admin !h-8.5 !px-5 text-xs font-semibold shadow-xs flex items-center gap-1.5 !bg-sky-600 hover:!bg-sky-700">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Modal Sesuaikan Tagihan Berjalan (Adjust Unpaid Invoice) -->
    <div class="modal-overlay" id="adjustInvoiceModal">
        <div class="modal-box max-w-xl bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-200 shrink-0">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Sesuaikan Tagihan</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Faktur: <span id="adjustModalInvNumber" class="font-mono font-bold text-sky-600">-</span></p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('adjustInvoiceModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="adjustInvoiceForm" method="POST" class="m-0" novalidate>
                @csrf
                <div class="modal-body p-6 bg-white space-y-4.5">
                    <!-- Row 1: Harga Pokok & Jatuh Tempo (2 Kolom Lega) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="form-label text-slate-700 font-medium text-xs block !mb-0">Harga Pokok (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" name="amount" id="adjustAmount" oninput="updateAdjustTotal()"
                                class="form-input !h-9 text-xs w-full font-mono bg-white text-slate-800 border-slate-300" required>
                        </div>
                        <div class="space-y-1.5">
                            <label class="form-label text-slate-700 font-medium text-xs block !mb-0">Tanggal Jatuh Tempo</label>
                            <input type="date" name="due_date" id="adjustDueDate"
                                class="form-input !h-9 text-xs w-full bg-white text-slate-800 border-slate-300">
                        </div>
                    </div>

                    <!-- Row 2: Penyesuaian & Keterangan (2 Kolom Lega) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="form-label text-slate-700 font-medium text-xs block !mb-0">Penyesuaian Tagihan (Rp)</label>
                            <input type="number" step="any" name="penyesuaian" id="adjustPenyesuaian" oninput="updateAdjustTotal()"
                                class="form-input !h-9 text-xs w-full font-mono bg-white text-slate-800 border-slate-300" placeholder="0">
                        </div>
                        <div class="space-y-1.5">
                            <label class="form-label text-slate-700 font-medium text-xs block !mb-0">Keterangan Penyesuaian</label>
                            <input type="text" name="catatan_penyesuaian" id="adjustCatatan"
                                class="form-input !h-9 text-xs w-full bg-white text-slate-800 border-slate-300"
                                placeholder="Misal: Penyesuaian biaya, penambahan layanan, dsb.">
                        </div>
                    </div>

                    <!-- Row 3: Pajak & Diskon (2 Kolom Lega) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="form-label text-slate-700 font-medium text-xs block !mb-0">Pajak (Rp)</label>
                            <input type="number" step="any" name="tax" id="adjustTax" oninput="updateAdjustTotal()"
                                class="form-input !h-9 text-xs w-full font-mono bg-white text-slate-800 border-slate-300">
                        </div>
                        <div class="space-y-1.5">
                            <label class="form-label text-slate-700 font-medium text-xs block !mb-0">Diskon (Rp)</label>
                            <input type="number" step="any" name="discount" id="adjustDiscount" oninput="updateAdjustTotal()"
                                class="form-input !h-9 text-xs w-full font-mono bg-white text-slate-800 border-slate-300">
                        </div>
                    </div>

                    <!-- Total Live Preview -->
                    <div class="p-3.5 rounded-xl border border-emerald-300 bg-emerald-50/80 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">Total Tagihan Baru</span>
                            <span class="text-xs text-emerald-600 block mt-0.5">Nominal yang akan ditagihkan ke pelanggan</span>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-black text-emerald-700 font-mono block" id="adjustModalTotal">Rp 0</span>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-500 m-0 leading-relaxed bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                        <i class="fa-solid fa-circle-info text-sky-500 mr-1"></i> Jika invoice ini telah memiliki link pembayaran Xendit online, link akan di-reset otomatis agar checkout menggunakan nominal baru yang telah disesuaikan.
                    </p>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2">
                    <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5"
                        onclick="closeCustomModal('adjustInvoiceModal')"><i class="fa-solid fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn-primary-admin !h-8.5 !px-5 text-xs font-semibold shadow-xs flex items-center gap-1.5 !bg-sky-600 hover:!bg-sky-700">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Modal Atur Penyesuaian Siklus Berikutnya (Next Bill Adjustment) -->
    <div class="modal-overlay" id="nextAdjustmentModal">
        <div class="modal-box max-w-lg bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-200 shrink-0">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Penyesuaian Siklus Berikutnya</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Atur penyesuaian biaya untuk tagihan mendatang.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('nextAdjustmentModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form action="{{ route('invoices.set_next_adjustment', $pppoe->id) }}" method="POST" class="m-0" novalidate>
                @csrf
                <div class="modal-body p-6 bg-white space-y-4.5">
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Pelanggan</span>
                            <span class="text-xs font-bold text-slate-800 block mt-0.5">{{ $pppoe->preOrder->nama_lengkap ?? $pppoe->username }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Paket Normal</span>
                            <span class="text-xs font-semibold text-slate-700 block mt-0.5">{{ $pppoe->paket->nama_paket ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="form-label text-slate-700 font-medium text-xs block !mb-0">
                            Nominal Penyesuaian (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="any" name="penyesuaian_tagihan"
                            value="{{ (float)($pppoe->penyesuaian_tagihan ?? 0) }}"
                            class="form-input !h-9 text-xs w-full font-mono bg-white text-slate-800 border-slate-300"
                            placeholder="Misal: 50000" required>
                        <span class="text-[11px] text-slate-400 mt-0.5 block">Masukkan angka positif untuk penambahan biaya, atau angka negatif untuk potongan.</span>
                    </div>

                    <div class="space-y-1.5">
                        <label class="form-label text-slate-700 font-medium text-xs block !mb-0">Keterangan Penyesuaian</label>
                        <input type="text" name="keterangan_penyesuaian"
                            value="{{ $pppoe->keterangan_penyesuaian ?? '' }}"
                            class="form-input !h-9 text-xs w-full bg-white text-slate-800 border-slate-300"
                            placeholder="Misal: Penyesuaian biaya, penambahan layanan, dsb.">
                    </div>

                    <div class="space-y-1.5">
                        <label class="form-label text-slate-700 font-medium text-xs block !mb-0">Masa Berlaku Penyesuaian <span class="text-rose-500">*</span></label>
                        <div class="space-y-2 pt-0.5">
                            <label class="flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer text-xs">
                                <input type="radio" name="penyesuaian_tipe" value="sekali" {{ ($pppoe->penyesuaian_tipe ?? 'sekali') === 'sekali' ? 'checked' : '' }} class="mt-0.5 text-sky-600 focus:ring-sky-500">
                                <div>
                                    <strong class="text-slate-800 block">1x (Hanya untuk 1 tagihan berikutnya)</strong>
                                    <span class="text-slate-500 text-[11px]">Setelah faktur siklus berikutnya terbit, penyesuaian otomatis di-reset ke 0.</span>
                                </div>
                            </label>
                            <label class="flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer text-xs">
                                <input type="radio" name="penyesuaian_tipe" value="berulang" {{ ($pppoe->penyesuaian_tipe ?? '') === 'berulang' ? 'checked' : '' }} class="mt-0.5 text-sky-600 focus:ring-sky-500">
                                <div>
                                    <strong class="text-slate-800 block">Berulang Setiap Bulan</strong>
                                    <span class="text-slate-500 text-[11px]">Penyesuaian berlaku untuk seluruh tagihan bulanan sampai dinonaktifkan.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2">
                    <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5"
                        onclick="closeCustomModal('nextAdjustmentModal')"><i class="fa-solid fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn-primary-admin !h-8.5 !px-5 text-xs font-semibold shadow-xs flex items-center gap-1.5 !bg-sky-600 hover:!bg-sky-700">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script>
            function updateGenTotal() {
                const amount = parseFloat(document.getElementById('genAmount')?.value || 0);
                const penyesuaian = parseFloat(document.getElementById('genPenyesuaian')?.value || 0);
                const tax = parseFloat(document.getElementById('genTax')?.value || 0);
                const discount = parseFloat(document.getElementById('genDiscount')?.value || 0);
                const total = Math.max(0, amount + penyesuaian + tax - discount);
                const totalEl = document.getElementById('genModalTotal');
                if (totalEl) totalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
            }

            function updateAdjustTotal() {
                const amount = parseFloat(document.getElementById('adjustAmount')?.value || 0);
                const penyesuaian = parseFloat(document.getElementById('adjustPenyesuaian')?.value || 0);
                const tax = parseFloat(document.getElementById('adjustTax')?.value || 0);
                const discount = parseFloat(document.getElementById('adjustDiscount')?.value || 0);
                const total = Math.max(0, amount + penyesuaian + tax - discount);
                const totalEl = document.getElementById('adjustModalTotal');
                if (totalEl) totalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
            }

            function openGenerateModal() {
                updateGenTotal();
                openCustomModal('generateSingleModal');
            }

            function openNextAdjustmentModal() {
                openCustomModal('nextAdjustmentModal');
            }

            function openAdjustModal(invoiceId, invoiceNumber, amount, penyesuaian, catatan, tax, discount, dueDate) {
                document.getElementById('adjustInvoiceForm').action = `/invoices/${invoiceId}/adjust`;
                document.getElementById('adjustModalInvNumber').innerText = invoiceNumber;
                document.getElementById('adjustAmount').value = amount;
                document.getElementById('adjustPenyesuaian').value = penyesuaian;
                document.getElementById('adjustCatatan').value = catatan || '';
                document.getElementById('adjustTax').value = tax;
                document.getElementById('adjustDiscount').value = discount;
                if (dueDate) document.getElementById('adjustDueDate').value = dueDate;
                updateAdjustTotal();
                openCustomModal('adjustInvoiceModal');
            }

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
                
                // Rapi & pas pada tombol icon 32x32px tanpa overflow text
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-indigo-600 text-sm"></i>';
                btn.disabled = true;

                // Tampilkan modal langsung dengan loading state modern
                document.getElementById('xenditLink').value = 'Menghubungkan ke gateway Xendit...';
                document.getElementById('xenditIframe').src = '';
                const loadingEl = document.getElementById('xenditLoadingState');
                if (loadingEl) loadingEl.style.display = 'flex';
                openCustomModal('xenditModal');

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
                            const iframe = document.getElementById('xenditIframe');
                            iframe.src = data.url;
                            
                            iframe.onload = function() {
                                if (loadingEl) $(loadingEl).fadeOut(250);
                            };
                            // Fallback jika event onload iframe cross-origin dibatasi browser
                            setTimeout(() => {
                                if (loadingEl) $(loadingEl).fadeOut(250);
                            }, 2000);
                        } else if (data && data.error) {
                            closeCustomModal('xenditModal');
                            showToast(data.error, 'error');
                        }
                    })
                    .catch(err => {
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                        closeCustomModal('xenditModal');
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
                const loadingEl = document.getElementById('xenditLoadingState');
                if (loadingEl) loadingEl.style.display = 'flex';
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
