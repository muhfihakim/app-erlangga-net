<x-layouts.app>
    @section('Css')
        <style>
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
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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

    <!-- Page Header -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                XenPlatform
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">
                Kelola saldo dan pencairan dana real-time akun XenPlatform dengan proteksi OTP.
            </p>
        </div>
    </div>

    <!-- Summary Stats Cards -->
    <div class="stats-grid mb-6">
        <!-- Saldo Aktif XenPlatform -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5;color:#059669;">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-emerald-600">Rp {{ number_format($balance, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Saldo Siap Ditarik</div>
                <div class="stat-lbl truncate">Saldo XenPlatform</div>
            </div>
        </div>

        <!-- Total Pending -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#fffbeb;color:#d97706;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-amber-600">Rp {{ number_format($totalPending, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Menunggu Konfirmasi</div>
                <div class="stat-lbl truncate">Dalam Proses (Pending)</div>
            </div>
        </div>

        <!-- Total Transaksi -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff;color:#2563eb;">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-val text-blue-600">{{ $withdrawals->total() }} Transaksi</div>
                <div class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Riwayat Pencairan</div>
                <div class="stat-lbl truncate">Total Penarikan</div>
            </div>
        </div>
    </div>

    <!-- Main Grid: Form Penarikan & Rekening Tersimpan -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">
        <!-- Form Pengajuan Penarikan -->
        <div class="lg:col-span-6">
            <div class="table-card p-5 bg-white border border-slate-200 shadow-sm h-full flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 m-0">Form Penarikan Dana</h2>
                            <p class="text-xs text-slate-500 m-0 mt-0.5">Pencairan saldo ke rekening bank tujuan.</p>
                        </div>
                    </div>

                    <form id="formWithdrawal" onsubmit="event.preventDefault(); initiateWithdrawal();">
                        <!-- Pilihan Rekening Tersimpan -->
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 mb-3.5">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="form-label text-slate-700 font-medium text-xs mb-0">
                                    Gunakan Rekening Tersimpan
                                </label>
                                <button type="button" onclick="openAddBankModal()"
                                    class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold">
                                    Tambah Rekening
                                </button>
                            </div>
                            <select id="selectSavedBank" class="form-input-custom !h-[38px] text-xs w-full bg-white"
                                onchange="applySavedBankAccount(this.value)">
                                <option value="">-- Pilih Rekening Tersimpan --</option>
                                @foreach ($bankAccounts as $acc)
                                    <option value="{{ $acc->id }}" data-bank-code="{{ $acc->bank_code }}"
                                        data-bank-name="{{ $acc->bank_name }}"
                                        data-account-number="{{ $acc->account_number }}"
                                        data-account-name="{{ $acc->account_holder_name }}"
                                        data-no-whatsapp="{{ $acc->no_whatsapp }}"
                                        {{ $acc->is_default ? 'selected' : '' }}>
                                        {{ $acc->bank_name }} - {{ $acc->account_number }} (a.n {{ $acc->account_holder_name }})
                                        {{ $acc->is_default ? '[UTAMA]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bank Tujuan -->
                        <div class="form-group mb-3.5">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">
                                Bank / E-Wallet Penerima <span class="text-rose-500">*</span>
                            </label>
                            <select id="wd_bank_code" class="form-input-custom !h-[38px] text-xs w-full bg-white" required>
                                <option value="" disabled selected>-- Pilih Bank / E-Wallet Tujuan --</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank['code'] }}">{{ $bank['name'] }} ({{ $bank['code'] }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-grid-2 mb-3.5">
                            <!-- Nomor Rekening -->
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">
                                    Nomor Rekening <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="wd_account_number"
                                    class="form-input !h-[38px] text-xs font-mono font-bold text-slate-800"
                                    placeholder="1234567890" required>
                            </div>

                            <!-- Nama Pemilik Rekening -->
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">
                                    Nama Pemilik <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="wd_account_holder_name"
                                    class="form-input !h-[38px] text-xs uppercase font-semibold"
                                    placeholder="Nama sesuai buku tabungan" required>
                            </div>
                        </div>

                        <!-- Nomor WhatsApp Penerima OTP -->
                        <div class="form-group mb-3.5">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">
                                Nomor WhatsApp Penerima OTP <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="wd_no_whatsapp"
                                class="form-input !h-[38px] text-xs font-mono font-bold text-slate-800"
                                placeholder="081234567890" required>
                            <span class="text-[11px] text-slate-400 mt-1 block">Nomor WhatsApp untuk menerima 6 digit kode OTP verifikasi penarikan.</span>
                        </div>

                        <!-- Nominal Penarikan -->
                        <div class="form-group mb-3.5">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">
                                Nominal Penarikan (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">Rp</span>
                                <input type="number" id="wd_amount"
                                    class="form-input !h-[38px] !pl-9 text-xs font-mono font-bold text-slate-800"
                                    placeholder="100000" min="10000" max="{{ (int) $balance }}" required>
                            </div>

                            <!-- Quick Amount Buttons -->
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <button type="button" onclick="setWithdrawAmount(50000)"
                                    class="px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors">50rb</button>
                                <button type="button" onclick="setWithdrawAmount(100000)"
                                    class="px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors">100rb</button>
                                <button type="button" onclick="setWithdrawAmount(500000)"
                                    class="px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors">500rb</button>
                                <button type="button" onclick="setWithdrawAmount(1000000)"
                                    class="px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors">1 Juta</button>
                                <button type="button" onclick="setWithdrawAmount({{ (int) $balance }})"
                                    class="px-2.5 py-1 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-semibold transition-colors">Semua Saldo</button>
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div class="form-group mb-4">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Catatan Penarikan (Opsional)</label>
                            <input type="text" id="wd_description" class="form-input !h-[38px] text-xs"
                                placeholder="Penarikan Saldo XenPlatform Erlangga Net">
                        </div>

                        <button type="submit" id="btnInitiateWithdrawal"
                            class="btn-primary-admin !h-10 text-xs font-semibold w-full flex items-center justify-center shadow-sm">
                            Lanjutkan Verifikasi OTP
                        </button>
                    </form>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400 text-center">
                    Setiap penarikan dana dilindungi verifikasi OTP via WhatsApp demi keamanan saldo.
                </div>
            </div>
        </div>

        <!-- Daftar Rekening Bank Tersimpan -->
        <div class="lg:col-span-6">
            <div class="table-card p-5 bg-white border border-slate-200 shadow-sm h-full flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3.5">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 m-0">Rekening Tersimpan</h2>
                            <p class="text-xs text-slate-500 m-0 mt-0.5">Daftar rekening tujuan pencairan dana.</p>
                        </div>
                        <button type="button" onclick="openAddBankModal()"
                            class="btn-primary-admin !h-8 !px-3 text-xs font-semibold shadow-sm">
                            Tambah Bank
                        </button>
                    </div>

                    <!-- List of saved bank accounts -->
                    <div class="space-y-2.5 max-h-[460px] overflow-y-auto pr-1">
                        @forelse($bankAccounts as $acc)
                            <div class="p-3 rounded-xl border {{ $acc->is_default ? 'border-emerald-300 bg-emerald-50/20' : 'border-slate-200 bg-slate-50/40' }} transition-colors hover:border-slate-300">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-700 shrink-0">
                                            {{ $acc->bank_code }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-slate-900 text-xs truncate">{{ $acc->bank_name }}</span>
                                                @if ($acc->is_default)
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        UTAMA
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs font-semibold text-slate-700 font-mono mt-0.5">
                                                {{ $acc->account_number }} <span class="font-sans font-normal text-slate-500 text-[11px]">a.n {{ $acc->account_holder_name }}</span>
                                            </div>
                                            @if ($acc->no_whatsapp)
                                                <div class="text-[11px] text-slate-500 font-mono">
                                                    WA: {{ $acc->no_whatsapp }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        @if (!$acc->is_default)
                                            <form action="{{ route('bank_accounts.set_default', $acc->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="px-2 py-1 rounded-md bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 text-[11px] font-semibold border border-slate-200 transition-colors"
                                                    title="Jadikan Rekening Utama">
                                                    Set Utama
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" onclick="openEditBankModal({{ json_encode($acc) }})"
                                            class="topbar-icon-btn !w-7 !h-7 !rounded-lg" title="Edit Rekening">
                                            <i class="fa-solid fa-pen text-[11px] text-slate-500"></i>
                                        </button>
                                        <form action="{{ route('bank_accounts.destroy', $acc->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Hapus rekening bank ini dari daftar tersimpan?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="topbar-icon-btn !w-7 !h-7 !rounded-lg hover:!bg-rose-50" title="Hapus Rekening">
                                                <i class="fa-solid fa-trash text-[11px] text-rose-500"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-slate-400 border border-dashed border-slate-200 rounded-xl">
                                <p class="text-xs font-semibold text-slate-600 m-0">Belum ada rekening bank tersimpan.</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Simpan rekening bank Anda agar pengajuan penarikan dana lebih cepat.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400">
                    Rekening <strong>UTAMA</strong> akan terpilih secara otomatis pada form penarikan.
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Penarikan Dana -->
    <div class="table-card bg-white border border-slate-200 shadow-sm mb-6">
        <div class="p-4 md:p-5 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h2 class="text-sm font-bold text-slate-900 m-0">Riwayat Penarikan Dana</h2>
                <p class="text-xs text-slate-500 m-0 mt-0.5">Daftar seluruh transaksi pencairan saldo XenPlatform.</p>
            </div>
            <div class="text-xs font-semibold text-slate-600 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-lg">
                Total: {{ $withdrawals->total() }} Transaksi
            </div>
        </div>

        <div class="table-responsive">
            <table class="admin-table w-full text-xs">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th style="width: 130px; text-align: center;">Tanggal & Waktu</th>
                        <th style="width: 200px; text-align: left;">No. Referensi</th>
                        <th style="width: 140px; text-align: left;">Bank Tujuan</th>
                        <th style="text-align: left;">Rekening Penerima</th>
                        <th style="width: 160px; text-align: right; padding-right: 24px;">Jumlah</th>
                        <th style="width: 110px; text-align: center;">Status</th>
                        <th style="width: 90px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $wd)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                {{ ($withdrawals->currentPage() - 1) * $withdrawals->perPage() + $loop->iteration }}
                            </td>
                            <td style="text-align: center;">
                                <div class="font-semibold text-slate-800">{{ $wd->created_at->format('d/m/Y') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $wd->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td style="text-align: left;">
                                <div class="font-semibold text-indigo-600 font-mono text-[11px]">{{ $wd->reference_id }}</div>
                                <div class="text-slate-400 text-[10px] font-mono">{{ $wd->xendit_disbursement_id ?: '-' }}</div>
                            </td>
                            <td style="text-align: left;">
                                <div class="font-semibold text-slate-800">{{ $wd->bank_name ?: $wd->bank_code }}</div>
                                <div class="text-[10px] text-slate-400 font-mono font-bold">{{ $wd->bank_code }}</div>
                            </td>
                            <td style="text-align: left;">
                                <div class="font-mono font-bold text-slate-900">{{ $wd->account_number }}</div>
                                <div class="text-[11px] text-slate-500 uppercase">{{ $wd->account_holder_name }}</div>
                            </td>
                            <td style="text-align: right; padding-right: 24px;">
                                <span class="font-bold text-slate-900 text-[13px]">
                                    Rp {{ number_format($wd->amount, 0, ',', '.') }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if ($wd->status === 'COMPLETED')
                                    <span class="badge badge-published !text-[11px] !px-2.5 !py-0.5">
                                        SELESAI
                                    </span>
                                @elseif($wd->status === 'PENDING')
                                    <span class="badge badge-draft !text-[11px] !px-2.5 !py-0.5">
                                        PENDING
                                    </span>
                                @else
                                    <span class="badge badge-inactive !text-[11px] !px-2.5 !py-0.5" title="{{ $wd->failure_code }}">
                                        GAGAL
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if ($wd->xendit_disbursement_id && $wd->status === 'PENDING')
                                    <button type="button" onclick="syncWithdrawalStatus({{ $wd->id }})"
                                        class="btn-primary-admin !h-7 !px-2 text-[11px] font-semibold">
                                        Cek Status
                                    </button>
                                @else
                                    <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Belum ada riwayat penarikan dana.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($withdrawals->hasPages())
            {{ $withdrawals->links() }}
        @elseif($withdrawals->total() > 0)
            <div class="flex items-center justify-between p-4 md:p-5 border-t border-slate-100">
                <p class="text-xs text-slate-500 font-medium m-0">
                    Menampilkan <span class="font-bold text-slate-800">{{ $withdrawals->firstItem() }}</span> sampai
                    <span class="font-bold text-slate-800">{{ $withdrawals->lastItem() }}</span> dari <span
                        class="font-bold text-slate-800">{{ $withdrawals->total() }}</span> data
                </p>
            </div>
        @endif
    </div>

    <!-- Modal Tambah Rekening Bank -->
    <div class="modal-overlay" id="modalAddBank">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Tambah Rekening Bank</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Simpan rekening bank tujuan penarikan dana.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeAddBankModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('bank_accounts.store') }}" method="POST">
                @csrf
                <input type="hidden" name="bank_name" id="add_bank_name">

                <div class="modal-body p-6 bg-white">
                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">
                            Bank Tujuan <span class="text-rose-500">*</span>
                        </label>
                        <select name="bank_code" id="add_bank_code"
                            class="form-input-custom !font-normal w-full bg-white text-slate-800 border-slate-300 text-xs" required
                            onchange="setBankName(this, 'add_bank_name')">
                            <option value="" disabled selected>-- Pilih Bank / E-Wallet --</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank['code'] }}" data-name="{{ $bank['name'] }}">
                                    {{ $bank['name'] }} ({{ $bank['code'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">
                            Nomor Rekening <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="account_number"
                            class="form-input !font-normal font-mono bg-white text-slate-800 border-slate-300 text-xs"
                            placeholder="Nomor rekening" required>
                    </div>

                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">
                            Nama Pemilik Rekening <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="account_holder_name"
                            class="form-input !font-normal uppercase bg-white text-slate-800 border-slate-300 text-xs"
                            placeholder="Nama sesuai buku tabungan" required>
                    </div>

                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">
                            Nomor WhatsApp Penerima OTP <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="no_whatsapp"
                            class="form-input !font-normal font-mono bg-white text-slate-800 border-slate-300 text-xs"
                            placeholder="081234567890" required>
                        <span class="text-[11px] text-slate-400 mt-1 block">Kode OTP penarikan ke rekening ini akan dikirimkan ke nomor ini.</span>
                    </div>

                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Catatan / Label (Opsional)</label>
                        <input type="text" name="notes"
                            class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs"
                            placeholder="misal: Rekening Utama BCA">
                    </div>

                    <div class="form-group mb-0 flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_default" id="add_is_default" value="1"
                            class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                        <label for="add_is_default" class="text-xs font-medium text-slate-700 cursor-pointer">
                            Jadikan Rekening Utama (Default)
                        </label>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2.5">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                        onclick="closeAddBankModal()">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !h-9 !px-5 text-xs font-semibold">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Rekening Bank -->
    <div class="modal-overlay" id="modalEditBank">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Edit Rekening Bank</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Perbarui data rekening bank penerima.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeEditBankModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="formEditBank" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="bank_name" id="edit_bank_name">

                <div class="modal-body p-6 bg-white">
                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">
                            Bank Tujuan <span class="text-rose-500">*</span>
                        </label>
                        <select name="bank_code" id="edit_bank_code"
                            class="form-input-custom !font-normal w-full bg-white text-slate-800 border-slate-300 text-xs" required
                            onchange="setBankName(this, 'edit_bank_name')">
                            <option value="" disabled>-- Pilih Bank / E-Wallet --</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank['code'] }}" data-name="{{ $bank['name'] }}">
                                    {{ $bank['name'] }} ({{ $bank['code'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">
                            Nomor Rekening <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="account_number" id="edit_account_number"
                            class="form-input !font-normal font-mono bg-white text-slate-800 border-slate-300 text-xs" required>
                    </div>

                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">
                            Nama Pemilik Rekening <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="account_holder_name" id="edit_account_holder_name"
                            class="form-input !font-normal uppercase bg-white text-slate-800 border-slate-300 text-xs" required>
                    </div>

                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">
                            Nomor WhatsApp Penerima OTP <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="no_whatsapp" id="edit_no_whatsapp"
                            class="form-input !font-normal font-mono bg-white text-slate-800 border-slate-300 text-xs"
                            placeholder="081234567890" required>
                        <span class="text-[11px] text-slate-400 mt-1 block">Kode OTP penarikan ke rekening ini akan dikirimkan ke nomor ini.</span>
                    </div>

                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Catatan / Label (Opsional)</label>
                        <input type="text" name="notes" id="edit_notes"
                            class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs">
                    </div>

                    <div class="form-group mb-0 flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_default" id="edit_is_default" value="1"
                            class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                        <label for="edit_is_default" class="text-xs font-medium text-slate-700 cursor-pointer">
                            Jadikan Rekening Utama (Default)
                        </label>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2.5">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                        onclick="closeEditBankModal()">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !h-9 !px-5 text-xs font-semibold">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- OTP Verification Modal -->
    <div class="modal-overlay" id="otpModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Verifikasi Kode OTP</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Konfirmasi penarikan saldo XenPlatform.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeOtpModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body p-6 bg-white">
                <p class="text-xs text-slate-500 text-center m-0 mb-4" id="otpRecipientInfo">
                    Masukkan 6-digit kode OTP yang telah dikirimkan ke WhatsApp Anda.
                </p>

                <!-- Detail Transaksi Singkat -->
                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200 text-xs space-y-1.5 mb-4">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Bank Tujuan:</span>
                        <span class="font-bold text-slate-800" id="otp_preview_bank">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nomor Rekening:</span>
                        <span class="font-bold text-slate-800 font-mono" id="otp_preview_account">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama Penerima:</span>
                        <span class="font-bold text-slate-800" id="otp_preview_name">-</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 pt-2 mt-2">
                        <span class="text-slate-700 font-bold">Nominal Penarikan:</span>
                        <span class="font-bold text-emerald-600 text-sm font-mono" id="otp_preview_amount">-</span>
                    </div>
                </div>

                <!-- OTP Input -->
                <div class="mb-3">
                    <label class="form-label text-slate-700 font-medium text-center block mb-1.5 text-xs">Kode OTP (6 Digit)</label>
                    <input type="text" id="inputOtp" maxlength="6"
                        class="form-input text-center font-mono text-2xl font-black tracking-widest text-slate-800 py-2 bg-slate-50 focus:bg-white"
                        placeholder="••••••" autofocus>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>Kadaluarsa: <strong id="otpCountdown" class="text-rose-600 font-mono">05:00</strong></span>
                    <button type="button" id="btnResendOtp" onclick="resendOtp()"
                        class="text-indigo-600 hover:text-indigo-800 font-semibold text-xs">
                        Kirim Ulang OTP
                    </button>
                </div>
            </div>

            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2.5">
                <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                    onclick="closeOtpModal()">
                    Batal
                </button>
                <button type="button" id="btnConfirmWithdrawal" onclick="executeWithdrawal()"
                    class="btn-primary-admin !h-9 !px-5 text-xs font-semibold">
                    Konfirmasi Penarikan
                </button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script>
            let currentPayload = {};
            let countdownTimer = null;
            let timeLeft = 300;

            function setWithdrawAmount(amount) {
                document.getElementById('wd_amount').value = amount;
            }

            function setBankName(selectEl, targetInputId) {
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                const bankName = selectedOption ? selectedOption.getAttribute('data-name') : '';
                document.getElementById(targetInputId).value = bankName || selectEl.value;
            }

            function applySavedBankAccount(bankAccountId) {
                if (!bankAccountId) return;
                const selectEl = document.getElementById('selectSavedBank');
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                if (!selectedOption) return;

                const bankCode = selectedOption.getAttribute('data-bank-code');
                const accountNumber = selectedOption.getAttribute('data-account-number');
                const accountName = selectedOption.getAttribute('data-account-name');
                const noWhatsapp = selectedOption.getAttribute('data-no-whatsapp');

                if (bankCode) document.getElementById('wd_bank_code').value = bankCode;
                if (accountNumber) document.getElementById('wd_account_number').value = accountNumber;
                if (accountName) document.getElementById('wd_account_holder_name').value = accountName;
                if (noWhatsapp) document.getElementById('wd_no_whatsapp').value = noWhatsapp;
            }

            function openAddBankModal() {
                openCustomModal('modalAddBank');
            }

            function closeAddBankModal() {
                closeCustomModal('modalAddBank');
            }

            function openEditBankModal(acc) {
                const form = document.getElementById('formEditBank');
                form.action = `/bank-accounts/${acc.id}`;

                document.getElementById('edit_bank_code').value = acc.bank_code;
                document.getElementById('edit_bank_name').value = acc.bank_name;
                document.getElementById('edit_account_number').value = acc.account_number;
                document.getElementById('edit_account_holder_name').value = acc.account_holder_name;
                document.getElementById('edit_no_whatsapp').value = acc.no_whatsapp || '';
                document.getElementById('edit_notes').value = acc.notes || '';
                document.getElementById('edit_is_default').checked = acc.is_default == 1;

                openCustomModal('modalEditBank');
            }

            function closeEditBankModal() {
                closeCustomModal('modalEditBank');
            }

            function initiateWithdrawal() {
                const bankCode = document.getElementById('wd_bank_code').value;
                const accountNumber = document.getElementById('wd_account_number').value.trim();
                const accountHolderName = document.getElementById('wd_account_holder_name').value.trim();
                const noWhatsapp = document.getElementById('wd_no_whatsapp').value.trim();
                const amount = parseFloat(document.getElementById('wd_amount').value);
                const description = document.getElementById('wd_description').value.trim();

                if (!bankCode) {
                    showToast('Silakan pilih Bank / E-Wallet tujuan.', 'error');
                    return;
                }
                if (!accountNumber) {
                    showToast('Silakan masukkan nomor rekening tujuan.', 'error');
                    return;
                }
                if (!accountHolderName) {
                    showToast('Silakan masukkan nama pemilik rekening.', 'error');
                    return;
                }
                if (!noWhatsapp) {
                    showToast('Silakan masukkan nomor WhatsApp penerima OTP.', 'error');
                    return;
                }
                if (!amount || amount < 10000) {
                    showToast('Minimal penarikan saldo adalah Rp 10.000.', 'error');
                    return;
                }

                currentPayload = {
                    bank_code: bankCode,
                    account_number: accountNumber,
                    account_holder_name: accountHolderName,
                    no_whatsapp: noWhatsapp,
                    amount: amount,
                    description: description,
                    _token: '{{ csrf_token() }}'
                };

                const btn = document.getElementById('btnInitiateWithdrawal');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Mengirim Kode OTP...';

                fetch('{{ route('withdrawals.send_otp') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(currentPayload)
                    })
                    .then(res => res.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;

                        if (data.success) {
                            showToast(data.message, 'success');

                            // Populate modal preview
                            document.getElementById('otpRecipientInfo').innerText = data.message;
                            document.getElementById('otp_preview_bank').innerText = bankCode;
                            document.getElementById('otp_preview_account').innerText = accountNumber;
                            document.getElementById('otp_preview_name').innerText = accountHolderName.toUpperCase();
                            document.getElementById('otp_preview_amount').innerText = 'Rp ' + Number(amount).toLocaleString(
                                'id-ID');
                            document.getElementById('inputOtp').value = data.debug_otp || '';

                            openOtpModal();
                            startCountdown(data.expires_in || 300);
                        } else {
                            showToast(data.message || 'Gagal mengirim OTP.', 'error');
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        showToast('Network error saat mengirim OTP.', 'error');
                    });
            }

            function executeWithdrawal() {
                const otp = document.getElementById('inputOtp').value.trim();
                if (!otp || otp.length !== 6) {
                    showToast('Silakan masukkan 6 digit kode OTP.', 'error');
                    return;
                }

                const btn = document.getElementById('btnConfirmWithdrawal');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Memproses Payout...';

                const finalPayload = {
                    ...currentPayload,
                    otp: otp
                };

                fetch('{{ route('withdrawals.withdraw') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(finalPayload)
                    })
                    .then(res => res.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;

                        if (data.success) {
                            closeOtpModal();
                            showToast(data.message, 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1200);
                        } else {
                            showToast(data.message || 'Penarikan gagal diproses.', 'error');
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        showToast('Terjadi kesalahan saat memproses penarikan.', 'error');
                    });
            }

            function resendOtp() {
                if (!currentPayload.amount) return;
                initiateWithdrawal();
            }

            function syncWithdrawalStatus(id) {
                fetch(`/withdrawals/${id}/sync`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showToast(data.message || 'Gagal sinkronisasi status.', 'error');
                        }
                    })
                    .catch(() => showToast('Gagal menghubungi server.', 'error'));
            }

            function openOtpModal() {
                openCustomModal('otpModal');
                setTimeout(() => {
                    const input = document.getElementById('inputOtp');
                    if (input) input.focus();
                }, 150);
            }

            function closeOtpModal() {
                closeCustomModal('otpModal');
                if (countdownTimer) clearInterval(countdownTimer);
            }

            function startCountdown(seconds) {
                if (countdownTimer) clearInterval(countdownTimer);
                timeLeft = seconds;
                const el = document.getElementById('otpCountdown');

                countdownTimer = setInterval(() => {
                    timeLeft--;
                    if (timeLeft <= 0) {
                        clearInterval(countdownTimer);
                        el.innerText = '00:00 (Kadaluarsa)';
                        return;
                    }
                    const m = Math.floor(timeLeft / 60).toString().padStart(2, '0');
                    const s = (timeLeft % 60).toString().padStart(2, '0');
                    el.innerText = `${m}:${s}`;
                }, 1000);
            }

            // Auto-apply default bank on page load
            $(document).ready(function() {
                const selectEl = document.getElementById('selectSavedBank');
                if (selectEl && selectEl.value) {
                    applySavedBankAccount(selectEl.value);
                }
            });
        </script>
    @endsection
</x-layouts.app>
