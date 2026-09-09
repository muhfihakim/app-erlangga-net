<x-layouts.app>
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Penarikan Saldo Xendit
            </h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0;">
                Kelola pencairan dana real-time dari rekening tampungan Xendit ke rekening bank tujuan dengan proteksi
                keamanan OTP.
            </p>
        </div>
    </div>

    <!-- Summary Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
        <!-- Saldo Xendit -->
        <div
            class="table-card p-6 bg-gradient-to-br from-slate-900 to-slate-800 text-white relative overflow-hidden shadow-xl">
            <div
                class="absolute -right-8 -bottom-8 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none">
            </div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase tracking-wider text-slate-400 font-bold">Saldo Aktif Xendit</span>
                <a href="{{ route('withdrawals.index') }}"
                    class="text-xs text-emerald-400 hover:text-emerald-300 font-bold flex items-center gap-1">
                    <i class="fa-solid fa-rotate-right"></i> Refresh
                </a>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-400 font-mono">
                Rp {{ number_format($balance, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                <i class="fa-solid fa-circle-check text-emerald-500"></i> Saldo CASH yang siap ditarik kapan saja
            </p>
        </div>

        <!-- Total Pending -->
        <div class="table-card p-6 bg-white border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase tracking-wider text-slate-500 font-bold">Dalam Proses / Pending</span>
                <div
                    class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-amber-600 font-mono">
                Rp {{ number_format($totalPending, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Menunggu konfirmasi bank penerima</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        <!-- Form Pengajuan Penarikan -->
        <div class="lg:col-span-6">
            <div class="table-card p-6 bg-white border border-slate-200 shadow-sm h-full flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-5">
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-money-bill-transfer text-emerald-600"></i> Form Penarikan Dana
                        </h2>
                    </div>

                    <form id="formWithdrawal" onsubmit="event.preventDefault(); initiateWithdrawal();">

                        <!-- Pilihan Rekening Tersimpan -->
                        <div class="form-group mb-5 p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center justify-between mb-2">
                                <label
                                    class="form-label !mb-0 font-bold text-slate-700 flex items-center gap-1.5 text-xs">
                                    <i class="fa-solid fa-bookmark text-emerald-600"></i> Gunakan Rekening Tersimpan
                                </label>
                                <button type="button" onclick="openAddBankModal()"
                                    class="text-xs text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-plus-circle"></i> Tambah Rekening
                                </button>
                            </div>
                            <select id="selectSavedBank" class="form-input bg-white text-xs font-medium"
                                onchange="applySavedBankAccount(this.value)">
                                <option value="">-- Pilih dari Rekening Tersimpan (Otomatis Terisi) --</option>
                                @foreach ($bankAccounts as $acc)
                                    <option value="{{ $acc->id }}" data-bank-code="{{ $acc->bank_code }}"
                                        data-bank-name="{{ $acc->bank_name }}"
                                        data-account-number="{{ $acc->account_number }}"
                                        data-account-name="{{ $acc->account_holder_name }}"
                                        data-no-whatsapp="{{ $acc->no_whatsapp }}"
                                        {{ $acc->is_default ? 'selected' : '' }}>
                                        {{ $acc->bank_name }} - {{ $acc->account_number }} (a.n
                                        {{ $acc->account_holder_name }})
                                        {{ $acc->no_whatsapp ? '[WA: ' . $acc->no_whatsapp . ']' : '' }}
                                        {{ $acc->is_default ? '[REKENING UTAMA]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-slate-400 text-[10px] block mt-1">Pilih rekening untuk mengisi data bank
                                dan nomor WhatsApp penerima OTP secara otomatis.</small>
                        </div>

                        <!-- Bank Tujuan -->
                        <div class="form-group mb-4">
                            <label class="form-label font-bold text-slate-700">Bank / E-Wallet Penerima <span
                                    class="text-rose-500">*</span></label>
                            <select id="wd_bank_code" class="form-input bg-white" required>
                                <option value="" disabled selected>-- Pilih Bank / E-Wallet Tujuan --</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank['code'] }}">{{ $bank['name'] }} ({{ $bank['code'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <!-- Nomor Rekening -->
                            <div class="form-group !mb-0">
                                <label class="form-label font-bold text-slate-700">Nomor Rekening <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" id="wd_account_number"
                                    class="form-input font-mono font-bold text-slate-800" placeholder="1234567890"
                                    required>
                            </div>

                            <!-- Nama Pemilik Rekening -->
                            <div class="form-group !mb-0">
                                <label class="form-label font-bold text-slate-700">Nama Pemilik Rekening <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" id="wd_account_holder_name"
                                    class="form-input uppercase font-semibold" placeholder="MUHAMMAD FIHAKIM" required>
                            </div>
                        </div>

                        <!-- Nomor WhatsApp Penerima OTP -->
                        <div class="form-group mb-4">
                            <label class="form-label font-bold text-slate-700 flex items-center justify-between">
                                <span>Nomor WhatsApp Penerima OTP <span class="text-rose-500">*</span></span>
                                <span class="text-[10px] text-emerald-600 font-semibold"><i
                                        class="fa-brands fa-whatsapp"></i> OTP dikirim ke sini</span>
                            </label>
                            <input type="text" id="wd_no_whatsapp"
                                class="form-input font-mono font-bold text-slate-800" placeholder="081234567890"
                                required>
                            <small class="text-slate-400 text-[10px] block mt-1">Nomor WhatsApp yang akan menerima 6
                                digit kode OTP verifikasi penarikan saldo.</small>
                        </div>

                        <!-- Nominal Penarikan -->
                        <div class="form-group mb-4">
                            <label class="form-label font-bold text-slate-700">Nominal Penarikan (Rp) <span
                                    class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span
                                    class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
                                <input type="number" id="wd_amount"
                                    class="form-input !pl-11 font-mono font-bold text-base text-slate-800"
                                    placeholder="100000" min="10000" max="{{ (int) $balance }}" required>
                            </div>

                            <!-- Quick Amount Buttons -->
                            <div class="flex flex-wrap gap-1.5 mt-2.5">
                                <button type="button" onclick="setWithdrawAmount(50000)"
                                    class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">50rb</button>
                                <button type="button" onclick="setWithdrawAmount(100000)"
                                    class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">100rb</button>
                                <button type="button" onclick="setWithdrawAmount(500000)"
                                    class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">500rb</button>
                                <button type="button" onclick="setWithdrawAmount(1000000)"
                                    class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">1
                                    Juta</button>
                                <button type="button" onclick="setWithdrawAmount({{ (int) $balance }})"
                                    class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold">Semua
                                    Saldo</button>
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div class="form-group mb-6">
                            <label class="form-label font-bold text-slate-700">Catatan Penarikan (Opsional)</label>
                            <input type="text" id="wd_description" class="form-input text-xs"
                                placeholder="Penarikan Saldo XenPlatform Erlangga Net">
                        </div>

                        <button type="submit" id="btnInitiateWithdrawal"
                            class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm flex items-center justify-center gap-2 transition-all shadow-lg shadow-emerald-600/20">
                            <i class="fa-solid fa-key"></i> Lanjutkan & Verifikasi OTP
                        </button>
                    </form>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 text-[11px] text-slate-400 flex items-center gap-2">
                    <i class="fa-brands fa-whatsapp text-emerald-600 text-sm"></i>
                    <span>Setiap transaksi dilindungi verifikasi OTP 6 digit via WhatsApp demi keamanan kas.</span>
                </div>
            </div>
        </div>

        <!-- Daftar Rekening Bank Tersimpan & Kelola -->
        <div class="lg:col-span-6">
            <div
                class="table-card p-6 bg-white border border-slate-200 shadow-sm h-full flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-credit-card text-indigo-600"></i> Daftar Rekening Bank Tersimpan
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Kelola rekening bank tujuan pencairan dana</p>
                        </div>
                        <button type="button" onclick="openAddBankModal()"
                            class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-1.5 transition-all shadow-sm">
                            <i class="fa-solid fa-plus"></i> Tambah Bank
                        </button>
                    </div>

                    <!-- List of saved bank accounts -->
                    <div class="space-y-3 max-h-[460px] overflow-y-auto pr-1">
                        @forelse($bankAccounts as $acc)
                            <div
                                class="p-4 rounded-2xl border {{ $acc->is_default ? 'border-emerald-500/50 bg-emerald-50/20' : 'border-slate-200 bg-slate-50/60' }} relative transition-all hover:border-slate-300">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center font-bold text-xs text-indigo-600 shadow-sm flex-shrink-0">
                                            {{ $acc->bank_code }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-bold text-slate-900 text-sm leading-tight">
                                                    {{ $acc->bank_name }}</h4>
                                                @if ($acc->is_default)
                                                    <span
                                                        class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-extrabold border border-emerald-300">
                                                        UTAMA
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="font-mono font-bold text-slate-700 text-xs mt-1">
                                                {{ $acc->account_number }}</p>
                                            <p class="text-[11px] text-slate-500 uppercase font-semibold">a.n
                                                {{ $acc->account_holder_name }}</p>
                                            @if ($acc->no_whatsapp)
                                                <p
                                                    class="text-[11px] text-emerald-600 font-semibold mt-0.5 flex items-center gap-1">
                                                    <i class="fa-brands fa-whatsapp"></i> {{ $acc->no_whatsapp }}
                                                </p>
                                            @endif
                                            @if ($acc->notes)
                                                <p class="text-[10px] text-slate-400 mt-1 italic">{{ $acc->notes }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-1.5">
                                        @if (!$acc->is_default)
                                            <form action="{{ route('bank_accounts.set_default', $acc->id) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="px-2 py-1 rounded-lg bg-slate-200 hover:bg-emerald-100 text-slate-600 hover:text-emerald-700 text-[11px] font-semibold transition-all"
                                                    title="Jadikan Rekening Utama">
                                                    Set Utama
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" onclick="openEditBankModal({{ json_encode($acc) }})"
                                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs transition-all"
                                            title="Edit Rekening">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form action="{{ route('bank_accounts.destroy', $acc->id) }}" method="POST"
                                            class="inline"
                                            onsubmit="return confirm('Hapus rekening bank ini dari daftar tersimpan?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center text-xs transition-all"
                                                title="Hapus Rekening">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div
                                class="p-8 text-center text-slate-400 border border-dashed border-slate-200 rounded-2xl">
                                <i class="fa-solid fa-credit-card text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs font-semibold text-slate-600">Belum ada rekening bank tersimpan.</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Simpan rekening bank Anda agar tidak perlu
                                    mengetik ulang saat melakukan penarikan.</p>
                                <button type="button" onclick="openAddBankModal()"
                                    class="mt-3 px-4 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-xs font-bold transition-all inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-plus"></i> Tambah Rekening Pertama
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span>Rekening yang diset <strong>UTAMA</strong> akan langsung terisi di form penarikan secara
                        otomatis.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Penarikan Dana -->
    <div class="table-card bg-white border border-slate-200 shadow-sm">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-slate-700"></i> Riwayat Penarikan Dana
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Daftar seluruh transaksi pencairan saldo Xendit oleh Super
                    Admin</p>
            </div>
            <div class="text-xs font-bold text-slate-500 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg">
                Total: {{ $withdrawals->total() }} Transaksi
            </div>
        </div>

        <div class="table-responsive">
            <table class="admin-table w-full text-xs">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th>Tanggal & Waktu</th>
                        <th>No. Referensi / ID Xendit</th>
                        <th>Bank Tujuan</th>
                        <th>Rekening & Nama Penerima</th>
                        <th style="text-align: right;">Jumlah (Rp)</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center; width: 110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $wd)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                {{ ($withdrawals->currentPage() - 1) * $withdrawals->perPage() + $loop->iteration }}
                            </td>
                            <td class="whitespace-nowrap">
                                <span
                                    class="font-bold text-slate-800 block">{{ $wd->created_at->format('d/m/Y') }}</span>
                                <span class="text-[11px] text-slate-400">{{ $wd->created_at->format('H:i:s') }}
                                    WIB</span>
                            </td>
                            <td class="font-mono text-[11px]">
                                <span class="font-bold text-indigo-600 block">{{ $wd->reference_id }}</span>
                                <span class="text-slate-400">{{ $wd->xendit_disbursement_id ?: '-' }}</span>
                            </td>
                            <td>
                                <span class="font-bold text-slate-800">{{ $wd->bank_name ?: $wd->bank_code }}</span>
                                <span
                                    class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-mono ml-1 font-bold">{{ $wd->bank_code }}</span>
                            </td>
                            <td>
                                <span
                                    class="font-mono font-bold text-slate-900 block">{{ $wd->account_number }}</span>
                                <span
                                    class="text-[11px] text-slate-500 uppercase">{{ $wd->account_holder_name }}</span>
                            </td>
                            <td class="text-right font-mono font-bold text-slate-900 text-sm whitespace-nowrap">
                                Rp {{ number_format($wd->amount, 0, ',', '.') }}
                            </td>
                            <td class="text-center whitespace-nowrap">
                                @if ($wd->status === 'COMPLETED')
                                    <span
                                        class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                                        <i class="fa-solid fa-circle-check mr-1"></i> SELESAI
                                    </span>
                                @elseif($wd->status === 'PENDING')
                                    <span
                                        class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-bold">
                                        <i class="fa-solid fa-clock mr-1"></i> PENDING
                                    </span>
                                @else
                                    <span
                                        class="px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-[11px] font-bold"
                                        title="{{ $wd->failure_code }}">
                                        <i class="fa-solid fa-circle-xmark mr-1"></i> GAGAL
                                    </span>
                                @endif
                            </td>
                            <td class="text-center whitespace-nowrap">
                                @if ($wd->xendit_disbursement_id && $wd->status === 'PENDING')
                                    <button type="button" onclick="syncWithdrawalStatus({{ $wd->id }})"
                                        class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-xs font-bold transition-all">
                                        <i class="fa-solid fa-rotate mr-1"></i> Cek Status
                                    </button>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-receipt text-2xl mb-2 text-slate-300"></i>
                                <p>Belum ada riwayat penarikan dana.</p>
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
            <form action="{{ route('bank_accounts.store') }}" method="POST">
                @csrf
                <input type="hidden" name="bank_name" id="add_bank_name">

                <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                    <div class="modal-title">
                        <i class="fa-solid fa-plus-circle text-indigo-600"></i>
                        <span class="text-slate-900 font-bold text-[17px]">Tambah Rekening Bank</span>
                    </div>
                    <button type="button" class="modal-close" onclick="closeAddBankModal()">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body p-6 space-y-4 bg-white">
                    <div class="form-group">
                        <label class="form-label !text-slate-800 !font-bold">Bank Tujuan <span
                                class="text-rose-500">*</span></label>
                        <select name="bank_code" id="add_bank_code"
                            class="form-input-custom w-full bg-white text-slate-900 border-slate-300" required
                            onchange="setBankName(this, 'add_bank_name')">
                            <option value="" disabled selected>-- Pilih Bank / E-Wallet --</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank['code'] }}" data-name="{{ $bank['name'] }}">
                                    {{ $bank['name'] }} ({{ $bank['code'] }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label !text-slate-800 !font-bold">Nomor Rekening <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="account_number" class="form-input font-mono font-bold"
                            placeholder="Nomor rekening" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label !text-slate-800 !font-bold">Nama Pemilik Rekening <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="account_holder_name" class="form-input uppercase font-semibold"
                            placeholder="Nama sesuai buku tabungan" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label !text-slate-800 !font-bold">Nomor WhatsApp Penerima OTP <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="no_whatsapp" class="form-input font-mono font-bold"
                            placeholder="081234567890" required>
                        <small class="text-slate-400 text-[11px]">Kode OTP verifikasi penarikan ke rekening ini akan
                            dikirimkan ke nomor ini.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label !text-slate-800 !font-bold">Catatan / Label (Opsional)</label>
                        <input type="text" name="notes" class="form-input text-xs"
                            placeholder="misal: Rekening Utama BCA">
                    </div>

                    <div class="form-group flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_default" id="add_is_default" value="1"
                            class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                        <label for="add_is_default"
                            class="text-xs font-semibold text-slate-700 cursor-pointer">Jadikan Rekening Utama
                            (Default)</label>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        onclick="closeAddBankModal()">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit"
                        class="btn-save !flex !items-center !justify-center gap-2 !bg-indigo-600 hover:!bg-indigo-700">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Rekening
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Rekening Bank -->
    <div class="modal-overlay" id="modalEditBank">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <form id="formEditBank" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="bank_name" id="edit_bank_name">

                <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                    <div class="modal-title">
                        <i class="fa-solid fa-pen-to-square text-amber-500"></i>
                        <span class="text-slate-900 font-bold text-[17px]">Edit Rekening Bank</span>
                    </div>
                    <button type="button" class="modal-close" onclick="closeEditBankModal()">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body p-6 space-y-4 bg-white">
                    <div class="form-group">
                        <label class="form-label !text-slate-800 !font-bold">Bank Tujuan <span
                                class="text-rose-500">*</span></label>
                        <select name="bank_code" id="edit_bank_code"
                            class="form-input-custom w-full bg-white text-slate-900 border-slate-300" required
                            onchange="setBankName(this, 'edit_bank_name')">
                            <option value="" disabled>-- Pilih Bank / E-Wallet --</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank['code'] }}" data-name="{{ $bank['name'] }}">
                                    {{ $bank['name'] }} ({{ $bank['code'] }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label !text-slate-800 !font-bold">Nomor Rekening <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="account_number" id="edit_account_number"
                            class="form-input font-mono font-bold" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label !text-slate-800 !font-bold">Nama Pemilik Rekening <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="account_holder_name" id="edit_account_holder_name"
                            class="form-input uppercase font-semibold" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label !text-slate-800 !font-bold">Nomor WhatsApp Penerima OTP <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="no_whatsapp" id="edit_no_whatsapp"
                            class="form-input font-mono font-bold" placeholder="081234567890" required>
                        <small class="text-slate-400 text-[11px]">Kode OTP verifikasi penarikan ke rekening ini akan
                            dikirimkan ke nomor ini.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label !text-slate-800 !font-bold">Catatan / Label (Opsional)</label>
                        <input type="text" name="notes" id="edit_notes" class="form-input text-xs">
                    </div>

                    <div class="form-group flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_default" id="edit_is_default" value="1"
                            class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                        <label for="edit_is_default"
                            class="text-xs font-semibold text-slate-700 cursor-pointer">Jadikan Rekening Utama
                            (Default)</label>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        onclick="closeEditBankModal()">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit"
                        class="btn-save !flex !items-center !justify-center gap-2 !bg-amber-500 hover:!bg-amber-600">
                        <i class="fa-solid fa-check"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- OTP Verification Modal -->
    <div class="modal-overlay" id="otpModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Verifikasi Kode OTP</span>
                </div>
                <button type="button" class="modal-close" onclick="closeOtpModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body p-6 space-y-4 bg-white">
                <div class="text-center">
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2 text-2xl shadow-inner">
                        <i class="fa-brands fa-whatsapp"></i>
                    </div>
                    <p class="text-xs text-slate-500" id="otpRecipientInfo">
                        Masukkan 6-digit kode OTP yang telah dikirimkan ke WhatsApp Anda.
                    </p>
                </div>

                <!-- Detail Transaksi Singkat -->
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 text-xs space-y-1.5">
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
                        <span class="font-black text-emerald-600 text-sm font-mono" id="otp_preview_amount">-</span>
                    </div>
                </div>

                <!-- OTP Input -->
                <div>
                    <label class="form-label !text-slate-800 !font-bold text-center block mb-2">Kode OTP (6
                        Digit)</label>
                    <input type="text" id="inputOtp" maxlength="6"
                        class="form-input text-center font-mono text-2xl font-black tracking-widest text-slate-800 py-2.5 bg-slate-50 focus:bg-white"
                        placeholder="••••••" autofocus>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>Kadaluarsa: <strong id="otpCountdown" class="text-rose-600 font-mono">05:00</strong></span>
                    <button type="button" id="btnResendOtp" onclick="resendOtp()"
                        class="text-indigo-600 hover:text-indigo-800 font-bold">
                        Kirim Ulang OTP
                    </button>
                </div>
            </div>

            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                    onclick="closeOtpModal()">
                    <i class="fa-solid fa-xmark"></i> Batal
                </button>
                <button type="button" id="btnConfirmWithdrawal" onclick="executeWithdrawal()"
                    class="btn-save !flex !items-center !justify-center gap-2 !bg-emerald-600 hover:!bg-emerald-700">
                    <i class="fa-solid fa-check"></i> Konfirmasi & Tarik
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
