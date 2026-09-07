<x-layouts.app>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                <i class="fa-solid fa-building-columns text-emerald-600"></i> Penarikan Saldo Xendit (XenPlatform Payout)
            </h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0;">
                Kelola pencairan dana real-time dari rekening tampungan Xendit ke rekening bank tujuan dengan proteksi keamanan OTP.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="badge" style="background:#ecfdf5;color:#059669;font-weight:800;border:1px solid #a7f3d0;padding:6px 14px;border-radius:12px;">
                <i class="fa-solid fa-shield-halved mr-1.5"></i> Super Admin Exclusive
            </span>
        </div>
    </div>

    <!-- Summary Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <!-- Saldo Xendit -->
        <div class="table-card p-6 bg-gradient-to-br from-slate-900 to-slate-800 text-white relative overflow-hidden shadow-xl">
            <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase tracking-wider text-slate-400 font-bold">Saldo Aktif Xendit</span>
                <a href="{{ route('withdrawals.index') }}" class="text-xs text-emerald-400 hover:text-emerald-300 font-bold flex items-center gap-1">
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

        <!-- Total Selesai -->
        <div class="table-card p-6 bg-white border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase tracking-wider text-slate-500 font-bold">Total Telah Dicairkan</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-800 font-mono">
                Rp {{ number_format($totalCompleted, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Akumulasi pencairan berhasil</p>
        </div>

        <!-- Total Pending -->
        <div class="table-card p-6 bg-white border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase tracking-wider text-slate-500 font-bold">Dalam Proses / Pending</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
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
                        <span class="badge" style="background:#e0f2fe;color:#0369a1;font-weight:700;">Instan</span>
                    </div>

                    <form id="formWithdrawal" onsubmit="event.preventDefault(); initiateWithdrawal();">
                        
                        <!-- Pilihan Rekening Tersimpan -->
                        <div class="form-group mb-5 p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center justify-between mb-2">
                                <label class="form-label !mb-0 font-bold text-slate-700 flex items-center gap-1.5 text-xs">
                                    <i class="fa-solid fa-bookmark text-emerald-600"></i> Gunakan Rekening Tersimpan
                                </label>
                                <button type="button" onclick="openAddBankModal()" class="text-xs text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-plus-circle"></i> Tambah Rekening
                                </button>
                            </div>
                            <select id="selectSavedBank" class="form-input bg-white text-xs font-medium" onchange="applySavedBankAccount(this.value)">
                                <option value="">-- Pilih dari Rekening Tersimpan (Otomatis Terisi) --</option>
                                @foreach($bankAccounts as $acc)
                                    <option value="{{ $acc->id }}" 
                                            data-bank-code="{{ $acc->bank_code }}"
                                            data-bank-name="{{ $acc->bank_name }}"
                                            data-account-number="{{ $acc->account_number }}"
                                            data-account-name="{{ $acc->account_holder_name }}"
                                            {{ $acc->is_default ? 'selected' : '' }}>
                                        {{ $acc->bank_name }} - {{ $acc->account_number }} (a.n {{ $acc->account_holder_name }}) {{ $acc->is_default ? '[REKENING UTAMA]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-slate-400 text-[10px] block mt-1">Pilih rekening untuk mengisi data bank tujuan secara otomatis.</small>
                        </div>

                        <!-- Bank Tujuan -->
                        <div class="form-group mb-4">
                            <label class="form-label font-bold text-slate-700">Bank / E-Wallet Penerima <span class="text-rose-500">*</span></label>
                            <select id="wd_bank_code" class="form-input bg-white" required>
                                <option value="" disabled selected>-- Pilih Bank / E-Wallet Tujuan --</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank['code'] }}">{{ $bank['name'] }} ({{ $bank['code'] }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <!-- Nomor Rekening -->
                            <div class="form-group !mb-0">
                                <label class="form-label font-bold text-slate-700">Nomor Rekening <span class="text-rose-500">*</span></label>
                                <input type="text" id="wd_account_number" class="form-input font-mono font-bold text-slate-800" placeholder="1234567890" required>
                            </div>

                            <!-- Nama Pemilik Rekening -->
                            <div class="form-group !mb-0">
                                <label class="form-label font-bold text-slate-700">Nama Pemilik Rekening <span class="text-rose-500">*</span></label>
                                <input type="text" id="wd_account_holder_name" class="form-input uppercase font-semibold" placeholder="MUHAMMAD FIHAKIM" required>
                            </div>
                        </div>

                        <!-- Nominal Penarikan -->
                        <div class="form-group mb-4">
                            <label class="form-label font-bold text-slate-700">Nominal Penarikan (Rp) <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
                                <input type="number" id="wd_amount" class="form-input !pl-11 font-mono font-bold text-base text-slate-800" placeholder="100000" min="10000" max="{{ (int) $balance }}" required>
                            </div>

                            <!-- Quick Amount Buttons -->
                            <div class="flex flex-wrap gap-1.5 mt-2.5">
                                <button type="button" onclick="setWithdrawAmount(50000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">50rb</button>
                                <button type="button" onclick="setWithdrawAmount(100000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">100rb</button>
                                <button type="button" onclick="setWithdrawAmount(500000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">500rb</button>
                                <button type="button" onclick="setWithdrawAmount(1000000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">1 Juta</button>
                                <button type="button" onclick="setWithdrawAmount({{ (int) $balance }})" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold">Semua Saldo</button>
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div class="form-group mb-6">
                            <label class="form-label font-bold text-slate-700">Catatan Penarikan (Opsional)</label>
                            <input type="text" id="wd_description" class="form-input text-xs" placeholder="Penarikan Saldo XenPlatform Erlangga Net">
                        </div>

                        <button type="submit" id="btnInitiateWithdrawal" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm flex items-center justify-center gap-2 transition-all shadow-lg shadow-emerald-600/20">
                            <i class="fa-solid fa-key"></i> Lanjutkan & Verifikasi OTP
                        </button>
                    </form>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 text-[11px] text-slate-400 flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                    <span>Setiap transaksi dilindungi verifikasi OTP 6 digit demi keamanan kas.</span>
                </div>
            </div>
        </div>

        <!-- Daftar Rekening Bank Tersimpan & Kelola -->
        <div class="lg:col-span-6">
            <div class="table-card p-6 bg-white border border-slate-200 shadow-sm h-full flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-credit-card text-indigo-600"></i> Daftar Rekening Bank Tersimpan
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Kelola rekening bank tujuan pencairan dana</p>
                        </div>
                        <button type="button" onclick="openAddBankModal()" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-1.5 transition-all shadow-sm">
                            <i class="fa-solid fa-plus"></i> Tambah Bank
                        </button>
                    </div>

                    <!-- List of saved bank accounts -->
                    <div class="space-y-3 max-h-[460px] overflow-y-auto pr-1">
                        @forelse($bankAccounts as $acc)
                            <div class="p-4 rounded-2xl border {{ $acc->is_default ? 'border-emerald-500/50 bg-emerald-50/20' : 'border-slate-200 bg-slate-50/60' }} relative transition-all hover:border-slate-300">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center font-bold text-xs text-indigo-600 shadow-sm flex-shrink-0">
                                            {{ $acc->bank_code }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-bold text-slate-900 text-sm leading-tight">{{ $acc->bank_name }}</h4>
                                                @if($acc->is_default)
                                                    <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-extrabold border border-emerald-300">
                                                        UTAMA
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="font-mono font-bold text-slate-700 text-xs mt-1">{{ $acc->account_number }}</p>
                                            <p class="text-[11px] text-slate-500 uppercase font-semibold">a.n {{ $acc->account_holder_name }}</p>
                                            @if($acc->notes)
                                                <p class="text-[10px] text-slate-400 mt-1 italic">{{ $acc->notes }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-1.5">
                                        @if(!$acc->is_default)
                                            <form action="{{ route('bank_accounts.set_default', $acc->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 rounded-lg bg-slate-200 hover:bg-emerald-100 text-slate-600 hover:text-emerald-700 text-[11px] font-semibold transition-all" title="Jadikan Rekening Utama">
                                                    Set Utama
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" onclick="openEditBankModal({{ json_encode($acc) }})" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs transition-all" title="Edit Rekening">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form action="{{ route('bank_accounts.destroy', $acc->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus rekening bank ini dari daftar tersimpan?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center text-xs transition-all" title="Hapus Rekening">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-400 border border-dashed border-slate-200 rounded-2xl">
                                <i class="fa-solid fa-credit-card text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs font-semibold text-slate-600">Belum ada rekening bank tersimpan.</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Simpan rekening bank Anda agar tidak perlu mengetik ulang saat melakukan penarikan.</p>
                                <button type="button" onclick="openAddBankModal()" class="mt-3 px-4 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-xs font-bold transition-all inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-plus"></i> Tambah Rekening Pertama
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span>Rekening yang diset <strong>UTAMA</strong> akan langsung terisi di form penarikan secara otomatis.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Penarikan Dana -->
    <div class="table-card p-6 bg-white border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
            <div>
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-slate-700"></i> Riwayat Penarikan Dana
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Daftar seluruh transaksi pencairan saldo Xendit oleh Super Admin</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-600 uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-4">No. Referensi / ID Xendit</th>
                        <th class="py-3 px-4">Bank Tujuan</th>
                        <th class="py-3 px-4">Rekening & Nama Penerima</th>
                        <th class="py-3 px-4 text-right">Jumlah (Rp)</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($withdrawals as $wd)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-bold text-slate-800 block">{{ $wd->created_at->format('d/m/Y') }}</span>
                                <span class="text-[11px] text-slate-400">{{ $wd->created_at->format('H:i:s') }} WIB</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px]">
                                <span class="font-bold text-indigo-600 block">{{ $wd->reference_id }}</span>
                                <span class="text-slate-400">{{ $wd->xendit_disbursement_id ?: '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800">{{ $wd->bank_name ?: $wd->bank_code }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-mono ml-1 font-bold">{{ $wd->bank_code }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-slate-900 block">{{ $wd->account_number }}</span>
                                <span class="text-[11px] text-slate-500 uppercase">{{ $wd->account_holder_name }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 text-sm whitespace-nowrap">
                                Rp {{ number_format($wd->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($wd->status === 'COMPLETED')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                                        <i class="fa-solid fa-circle-check mr-1"></i> SELESAI
                                    </span>
                                @elseif($wd->status === 'PENDING')
                                    <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-bold">
                                        <i class="fa-solid fa-clock mr-1"></i> PENDING
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-[11px] font-bold" title="{{ $wd->failure_code }}">
                                        <i class="fa-solid fa-circle-xmark mr-1"></i> GAGAL
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($wd->xendit_disbursement_id && $wd->status === 'PENDING')
                                    <button type="button" onclick="syncWithdrawalStatus({{ $wd->id }})" class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-xs font-bold transition-all">
                                        <i class="fa-solid fa-rotate mr-1"></i> Cek Status
                                    </button>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-receipt text-2xl mb-2 text-slate-300"></i>
                                <p>Belum ada riwayat penarikan dana.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($withdrawals->hasPages())
            <div class="mt-4 border-t border-slate-100 pt-3">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Rekening Bank -->
    <div id="modalAddBank" class="modal-overlay hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="modal-box bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative animate-scale-up">
            <button type="button" onclick="closeAddBankModal()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="flex items-center gap-3 border-b border-slate-100 pb-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Tambah Rekening Bank</h3>
                    <p class="text-xs text-slate-400">Simpan informasi rekening untuk penarikan cepat</p>
                </div>
            </div>

            <form action="{{ route('bank_accounts.store') }}" method="POST">
                @csrf
                <input type="hidden" name="bank_name" id="add_bank_name">

                <div class="form-group mb-4">
                    <label class="form-label font-bold text-slate-700">Bank Tujuan <span class="text-rose-500">*</span></label>
                    <select name="bank_code" id="add_bank_code" class="form-input bg-white" required onchange="setBankName(this, 'add_bank_name')">
                        <option value="" disabled selected>-- Pilih Bank / E-Wallet --</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank['code'] }}" data-name="{{ $bank['name'] }}">{{ $bank['name'] }} ({{ $bank['code'] }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label font-bold text-slate-700">Nomor Rekening <span class="text-rose-500">*</span></label>
                    <input type="text" name="account_number" class="form-input font-mono font-bold" placeholder="Nomor rekening" required>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label font-bold text-slate-700">Nama Pemilik Rekening <span class="text-rose-500">*</span></label>
                    <input type="text" name="account_holder_name" class="form-input uppercase font-semibold" placeholder="Nama sesuai buku tabungan" required>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label font-bold text-slate-700">Catatan / Label (Opsional)</label>
                    <input type="text" name="notes" class="form-input text-xs" placeholder="misal: Rekening Utama BCA Bisnis">
                </div>

                <div class="form-group mb-6 flex items-center gap-2">
                    <input type="checkbox" name="is_default" id="add_is_default" value="1" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    <label for="add_is_default" class="text-xs font-semibold text-slate-700 cursor-pointer">Jadikan Rekening Utama (Default)</label>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeAddBankModal()" class="w-1/3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all">
                        Batal
                    </button>
                    <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-all shadow-md shadow-indigo-600/20">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Rekening
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Rekening Bank -->
    <div id="modalEditBank" class="modal-overlay hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="modal-box bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative animate-scale-up">
            <button type="button" onclick="closeEditBankModal()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="flex items-center gap-3 border-b border-slate-100 pb-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Edit Rekening Bank</h3>
                    <p class="text-xs text-slate-400">Perbarui informasi rekening tersimpan</p>
                </div>
            </div>

            <form id="formEditBank" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="bank_name" id="edit_bank_name">

                <div class="form-group mb-4">
                    <label class="form-label font-bold text-slate-700">Bank Tujuan <span class="text-rose-500">*</span></label>
                    <select name="bank_code" id="edit_bank_code" class="form-input bg-white" required onchange="setBankName(this, 'edit_bank_name')">
                        <option value="" disabled>-- Pilih Bank / E-Wallet --</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank['code'] }}" data-name="{{ $bank['name'] }}">{{ $bank['name'] }} ({{ $bank['code'] }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label font-bold text-slate-700">Nomor Rekening <span class="text-rose-500">*</span></label>
                    <input type="text" name="account_number" id="edit_account_number" class="form-input font-mono font-bold" required>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label font-bold text-slate-700">Nama Pemilik Rekening <span class="text-rose-500">*</span></label>
                    <input type="text" name="account_holder_name" id="edit_account_holder_name" class="form-input uppercase font-semibold" required>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label font-bold text-slate-700">Catatan / Label (Opsional)</label>
                    <input type="text" name="notes" id="edit_notes" class="form-input text-xs">
                </div>

                <div class="form-group mb-6 flex items-center gap-2">
                    <input type="checkbox" name="is_default" id="edit_is_default" value="1" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    <label for="edit_is_default" class="text-xs font-semibold text-slate-700 cursor-pointer">Jadikan Rekening Utama (Default)</label>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeEditBankModal()" class="w-1/3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all">
                        Batal
                    </button>
                    <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-all shadow-md shadow-amber-500/20">
                        <i class="fa-solid fa-check"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- OTP Verification Modal -->
    <div id="otpModal" class="modal-overlay hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="modal-box bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative animate-scale-up">
            <button type="button" onclick="closeOtpModal()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="text-center mb-6">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-2xl shadow-inner">
                    <i class="fa-solid fa-shield-keyhole"></i>
                </div>
                <h3 class="text-lg font-black text-slate-800">Verifikasi Kode OTP</h3>
                <p class="text-xs text-slate-500 mt-1" id="otpRecipientInfo">
                    Masukkan 6-digit kode OTP yang telah dikirimkan ke email Anda.
                </p>
            </div>

            <!-- Detail Transaksi Singkat -->
            <div class="bg-slate-50 rounded-2xl p-4 mb-5 border border-slate-200/80 text-xs space-y-1.5">
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
            <div class="mb-5">
                <label class="form-label font-bold text-slate-700 text-center block mb-2">Kode OTP (6 Digit)</label>
                <input type="text" id="inputOtp" maxlength="6" class="form-input text-center font-mono text-2xl font-black tracking-widest text-slate-800 py-3 bg-slate-50 focus:bg-white" placeholder="••••••" autofocus>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-500 mb-6">
                <span>Kadaluarsa dalam: <strong id="otpCountdown" class="text-rose-600 font-mono">05:00</strong></span>
                <button type="button" id="btnResendOtp" onclick="resendOtp()" class="text-indigo-600 hover:text-indigo-800 font-bold">
                    Kirim Ulang OTP
                </button>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeOtpModal()" class="w-1/3 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all">
                    Batal
                </button>
                <button type="button" id="btnConfirmWithdrawal" onclick="executeWithdrawal()" class="w-2/3 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition-all shadow-lg shadow-emerald-600/30">
                    <i class="fa-solid fa-check"></i> <span>Konfirmasi & Tarik</span>
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

                if (bankCode) document.getElementById('wd_bank_code').value = bankCode;
                if (accountNumber) document.getElementById('wd_account_number').value = accountNumber;
                if (accountName) document.getElementById('wd_account_holder_name').value = accountName;
            }

            function openAddBankModal() {
                document.getElementById('modalAddBank').classList.remove('hidden');
            }

            function closeAddBankModal() {
                document.getElementById('modalAddBank').classList.add('hidden');
            }

            function openEditBankModal(acc) {
                const form = document.getElementById('formEditBank');
                form.action = `/bank-accounts/${acc.id}`;

                document.getElementById('edit_bank_code').value = acc.bank_code;
                document.getElementById('edit_bank_name').value = acc.bank_name;
                document.getElementById('edit_account_number').value = acc.account_number;
                document.getElementById('edit_account_holder_name').value = acc.account_holder_name;
                document.getElementById('edit_notes').value = acc.notes || '';
                document.getElementById('edit_is_default').checked = acc.is_default;

                document.getElementById('modalEditBank').classList.remove('hidden');
            }

            function closeEditBankModal() {
                document.getElementById('modalEditBank').classList.add('hidden');
            }

            function initiateWithdrawal() {
                const bankCode = document.getElementById('wd_bank_code').value;
                const accountNumber = document.getElementById('wd_account_number').value.trim();
                const accountHolderName = document.getElementById('wd_account_holder_name').value.trim();
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
                if (!amount || amount < 10000) {
                    showToast('Minimal penarikan saldo adalah Rp 10.000.', 'error');
                    return;
                }

                currentPayload = {
                    bank_code: bankCode,
                    account_number: accountNumber,
                    account_holder_name: accountHolderName,
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
                        document.getElementById('otp_preview_amount').innerText = 'Rp ' + Number(amount).toLocaleString('id-ID');
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
                    headers: { 'Accept': 'application/json' }
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
                document.getElementById('otpModal').classList.remove('hidden');
                document.getElementById('inputOtp').focus();
            }

            function closeOtpModal() {
                document.getElementById('otpModal').classList.add('hidden');
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
