<x-layouts.app>
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                <i class="fa-solid fa-database text-indigo-500"></i> Backup & Restore Data
            </h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0;">
                Cadangkan database ke format .CSV, pulihkan akun PPPoE atau Full Backup, dan kelola riwayat cadangan sistem.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openCustomModal('resetModal')"
                class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !px-4 !py-2.5 !text-xs flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-trash-can"></i> Reset & Kosongkan Data
            </button>
        </div>
    </div>

    @if (session('success'))
        <div
            class="bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-200 mb-6 flex items-start gap-3 shadow-sm">
            <i class="fa-solid fa-circle-check text-emerald-500 text-lg mt-0.5"></i>
            <div>
                <strong class="font-bold">Berhasil!</strong>
                <p class="text-sm mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div
            class="bg-rose-50 text-rose-700 p-4 rounded-xl border border-rose-200 mb-6 flex items-start gap-3 shadow-sm">
            <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg mt-0.5"></i>
            <div>
                <strong class="font-bold">Terjadi Kesalahan!</strong>
                <p class="text-sm mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-rose-50 text-rose-700 p-4 rounded-xl border border-rose-200 mb-6 shadow-sm">
            <div class="flex items-center gap-2 font-bold mb-1">
                <i class="fa-solid fa-circle-exclamation text-rose-500"></i> Mohon Periksa Input:
            </div>
            <ul class="list-disc list-inside text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Stat Cards Summary -->
    <div class="stats-grid mb-6">
        <!-- Card 1: PPPoE -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0e7ff;color:#4338ca;">
                <i class="fa-solid fa-users-gear"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($dbStats['pppoes']) }}</div>
                <div class="stat-lbl">Akun PPPoE Aktif</div>
            </div>
        </div>

        <!-- Card 2: Paket Layanan -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($dbStats['pakets']) }}</div>
                <div class="stat-lbl">Paket Layanan</div>
            </div>
        </div>

        <!-- Card 3: Faktur / Invoices -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($dbStats['invoices']) }}</div>
                <div class="stat-lbl">Faktur / Invoice</div>
            </div>
        </div>

        <!-- Card 4: Transaksi -->
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($dbStats['transactions']) }}</div>
                <div class="stat-lbl">Log Transaksi</div>
            </div>
        </div>
    </div>

    <!-- Informasi Backup Terakhir (Status Banner) -->
    <div class="mb-8">
        @if ($lastBackup)
            <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50 border border-emerald-200/80 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-start sm:items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-md shrink-0">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-600 text-white flex items-center gap-1.5 shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-emerald-200 animate-pulse"></span>
                                    Backup Terakhir
                                </span>
                                <span class="text-xs font-semibold text-slate-500">
                                    {{ $lastBackup->created_at->diffForHumans() }} ({{ $lastBackup->formatted_created_at }})
                                </span>
                            </div>
                            <div class="mt-1.5 flex items-center gap-3 flex-wrap">
                                <span class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-regular fa-file-lines text-emerald-600"></i>
                                    {{ $lastBackup->file_name }}
                                </span>
                                <span class="text-xs text-slate-600 bg-white/80 border border-slate-200/60 px-2.5 py-0.5 rounded-lg font-medium">
                                    Kategori: <strong class="text-slate-800">{{ $lastBackup->backup_type_label }}</strong>
                                </span>
                                <span class="text-xs text-slate-600 bg-white/80 border border-slate-200/60 px-2.5 py-0.5 rounded-lg font-medium">
                                    Total: <strong class="text-slate-800">{{ number_format($lastBackup->total_rows) }}</strong> baris
                                </span>
                                <span class="text-xs text-slate-600 bg-white/80 border border-slate-200/60 px-2.5 py-0.5 rounded-lg font-medium">
                                    Ukuran: <strong class="text-slate-800">{{ $lastBackup->formatted_size }}</strong>
                                </span>
                                <span class="text-xs text-slate-600 bg-white/80 border border-slate-200/60 px-2.5 py-0.5 rounded-lg font-medium">
                                    Oleh: <strong class="text-slate-800">{{ $lastBackup->user_name ?: 'Sistem' }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 self-stretch sm:self-auto shrink-0">
                        <a href="{{ route('migration.backup.download', $lastBackup->id) }}"
                            class="btn-primary-admin !bg-emerald-600 hover:!bg-emerald-700 !px-4 !py-2.5 !text-xs flex items-center justify-center gap-2 shadow-sm w-full sm:w-auto">
                            <i class="fa-solid fa-cloud-arrow-down"></i> Unduh File Backup Ini
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-amber-50 border border-amber-200/80 rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-900">Belum Ada Riwayat Backup</h4>
                        <p class="text-xs text-amber-700 mt-0.5">Sistem belum mendeteksi file cadangan terbaru. Sangat disarankan untuk mencadangkan data secara berkala untuk menjaga keamanan data Anda.</p>
                    </div>
                </div>
                <a href="{{ route('migration.backup', 'full') }}"
                    class="btn-primary-admin !bg-amber-600 hover:!bg-amber-700 !px-4 !py-2 !text-xs flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-database"></i> Backup Seluruh Data Sekarang
                </a>
            </div>
        @endif
    </div>

    <!-- SEKSI 1: OPSI BACKUP DATA (.CSV) -->
    <div class="mb-10">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <div>
                <h2 class="text-base font-black text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-down text-indigo-600"></i>
                    Opsi Cadangkan / Backup Data (.CSV)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pilih kategori data yang ingin Anda unduh dalam format spreadsheet <strong>.CSV</strong> berstandar UTF-8 (kompatibel penuh dengan Microsoft Excel).
                </p>
            </div>
            <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full flex items-center gap-1.5">
                <i class="fa-solid fa-file-csv text-emerald-600"></i> Format Output: .CSV UTF-8
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            <!-- Card Backup 1: Full Backup -->
            <div class="bg-white p-5 rounded-2xl border-2 border-indigo-100 shadow-sm hover:border-indigo-300 hover:shadow-md transition-all flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute top-0 right-0">
                    <span class="bg-indigo-600 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl uppercase tracking-wider">
                        Master
                    </span>
                </div>
                <div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 mb-1">Backup Seluruh Data</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        Master CSV menggabungkan seluruh tabel: <strong>Paket Layanan, Akun Login Sistem, Pelanggan, PPPoE, Faktur Tagihan,</strong> dan <strong>Riwayat Transaksi</strong>.
                    </p>
                </div>
                <a href="{{ route('migration.backup', 'full') }}"
                    class="btn-primary-admin !bg-indigo-600 hover:!bg-indigo-700 !w-full !justify-center !py-2.5 !text-xs flex items-center gap-2 shadow-sm mt-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i> Unduh Master CSV
                </a>
            </div>

            <!-- Card Backup 2: Pelanggan & PPPoE -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-emerald-300 hover:shadow-md transition-all flex flex-col justify-between group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-sm font-bold text-slate-800">Pelanggan & PPPoE</h3>
                        <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                            {{ number_format($dbStats['pppoes']) }} Akun
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        Mencakup Username & Password PPPoE, IP Address, Nama Pelanggan, No WA, Alamat, Koordinat, NIK, SN Perangkat, Modem, Teknisi & Sales.
                    </p>
                </div>
                <a href="{{ route('migration.backup', 'customers_pppoe') }}"
                    class="btn-primary-admin !bg-emerald-600 hover:!bg-emerald-700 !w-full !justify-center !py-2.5 !text-xs flex items-center gap-2 shadow-sm mt-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i> Unduh CSV Pelanggan
                </a>
            </div>

            <!-- Card Backup 3: Akun Sistem (Users) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-amber-300 hover:shadow-md transition-all flex flex-col justify-between group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-sm font-bold text-slate-800">Akun Pengguna Sistem</h3>
                        <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">
                            {{ number_format($dbStats['users']) }} User
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        Data seluruh akun pengguna aplikasi: Administrator, Teknisi, Sales, Staf Kasir, info login, No WhatsApp, Email, dan Hak Akses Jabatan.
                    </p>
                </div>
                <a href="{{ route('migration.backup', 'users') }}"
                    class="btn-primary-admin !bg-amber-600 hover:!bg-amber-700 !w-full !justify-center !py-2.5 !text-xs flex items-center gap-2 shadow-sm mt-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i> Unduh CSV Akun
                </a>
            </div>

            <!-- Card Backup 4: Transaksi & Keuangan -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-sky-300 hover:shadow-md transition-all flex flex-col justify-between group">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-sm font-bold text-slate-800">Transaksi & Keuangan</h3>
                        <span class="text-[11px] font-semibold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md">
                            {{ number_format($dbStats['transactions']) }} Data
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        Seluruh riwayat transaksi pembayaran tagihan, nomor invoice, nominal bayar, biaya admin, kanal bayar, dan tanggal verifikasi pembayaran.
                    </p>
                </div>
                <a href="{{ route('migration.backup', 'finance') }}"
                    class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !w-full !justify-center !py-2.5 !text-xs flex items-center gap-2 shadow-sm mt-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i> Unduh CSV Keuangan
                </a>
            </div>
        </div>
    </div>

    <!-- SEKSI 2: PULIHKAN & IMPORT DATA -->
    <div class="mb-10">
        <div class="mb-4">
            <h2 class="text-base font-black text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-cloud-arrow-up text-emerald-600"></i>
                Pulihkan & Import Data
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Pilih file untuk memulihkan akun PPPoE saja (Excel) atau Full Backup (Paket, PPPoE, Faktur & Transaksi).
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Form 1: PPPoE Saja (Excel XLSX / CSV) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-file-excel"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-800">1. Pulihkan / Import Pelanggan & PPPoE</h2>
                            <p class="text-xs text-slate-500">File Backup CSV Pelanggan / Excel (.xlsx)</p>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                        Gunakan form ini untuk memulihkan data <strong>Pelanggan, PPPoE, Username, Password, IP Address, Alamat, NIK, dan Koordinat</strong> dari file cadangan <code class="bg-emerald-50 text-emerald-700 px-1 py-0.5 rounded font-mono">backup_pelanggan_pppoe_*.csv</code>, file Excel <code class="bg-slate-100 text-slate-700 px-1 py-0.5 rounded font-mono">users_ppp_export.xlsx</code>, maupun file export sebelumnya.
                    </p>

                    <form action="{{ route('migration.import') }}" method="POST" enctype="multipart/form-data"
                        id="formPppoeOnly">
                        @csrf
                        <input type="hidden" name="import_type" value="pppoe_only">

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih File Backup CSV / Excel (.csv / .xlsx):</label>
                            <input type="file" name="pppoe_file" id="pppoe_file_input"
                                accept=".xlsx, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv"
                                required class="form-input-custom w-full !p-2 text-xs bg-slate-50">
                            <span class="text-[11px] text-slate-400 mt-1 block">Contoh: <code
                                    class="bg-slate-100 px-1 py-0.5 rounded text-slate-600 font-mono">backup_pelanggan_pppoe.csv</code> atau <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-600 font-mono">users_ppp_export.xlsx</code></span>
                        </div>

                        <div class="mb-5 p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="sync_radius" checked
                                    class="w-4 h-4 mt-0.5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Sinkronkan ke Database RADIUS</span>
                                    <p class="text-[11px] text-slate-500">Memasukkan username, password, IP, profil ke tabel
                                        radcheck, radreply, radusergroup, dan userinfo.</p>
                                </div>
                            </label>
                        </div>

                        <button type="button" onclick="triggerImportModal('pppoe')"
                            class="btn-primary-admin !bg-emerald-600 hover:!bg-emerald-700 !w-full !justify-center !py-3 !text-xs flex items-center gap-2 shadow-md">
                            <i class="fa-solid fa-file-excel"></i> Pulihkan / Import Akun PPPoE
                        </button>
                    </form>
                </div>
            </div>

            <!-- Form 2: Full Backup Sistem (Paket + PPPoE + Faktur + Transaksi) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-800">2. Pulihkan / Import Full Backup Sistem</h2>
                            <p class="text-xs text-slate-500">File Backup Lengkap (.csv)</p>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                        Gunakan form ini untuk memulihkan <strong>seluruh data sistem</strong> sekaligus: Paket Layanan, Akun Sistem (Users),
                        Akun PPPoE & Pelanggan, Faktur Invoice, dan Transaksi dari file <code class="bg-indigo-50 text-indigo-700 px-1 py-0.5 rounded font-mono">backup_seluruh_data_*.csv</code> maupun file backup sistem sebelumnya.
                    </p>

                    <form action="{{ route('migration.import') }}" method="POST" enctype="multipart/form-data"
                        id="formFullBackup">
                        @csrf
                        <input type="hidden" name="import_type" value="full">

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih File Backup CSV
                                (.csv):</label>
                            <input type="file" name="full_backup_file" id="full_backup_file_input"
                                accept=".csv, text/csv" required class="form-input-custom w-full !p-2 text-xs bg-slate-50">
                            <span class="text-[11px] text-slate-400 mt-1 block">Contoh: <code
                                    class="bg-slate-100 px-1 py-0.5 rounded text-slate-600 font-mono">backup_seluruh_data.csv</code></span>
                        </div>

                        <div class="mb-5 p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2.5">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="sync_radius" checked
                                    class="w-4 h-4 mt-0.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Sinkronkan ke Database RADIUS</span>
                                </div>
                            </label>
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="import_invoices" checked
                                    class="w-4 h-4 mt-0.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Import Faktur / Invoice Tagihan</span>
                                </div>
                            </label>
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="import_transactions" checked
                                    class="w-4 h-4 mt-0.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Import Riwayat Transaksi
                                        Pembayaran</span>
                                </div>
                            </label>
                        </div>

                        <button type="button" onclick="triggerImportModal('full')"
                            class="btn-primary-admin !bg-indigo-600 hover:!bg-indigo-700 !w-full !justify-center !py-3 !text-xs flex items-center gap-2 shadow-md">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Import Full Backup
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- SEKSI 3: RIWAYAT & LOG BACKUP (.CSV) -->
    <div class="mb-10">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3 bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-slate-500"></i>
                        Riwayat Log Backup Data (.CSV)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar arsip cadangan yang telah dibuat di server dan dapat diunduh ulang sewaktu-waktu.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-white border border-slate-200 text-slate-600 rounded-lg shadow-2xs">
                    Total Log: <strong>{{ $backupLogs->count() }}</strong> Arsip
                </span>
            </div>

            @if ($backupLogs->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" style="font-size: 12px;">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-[11px] font-bold tracking-wider">
                                <th class="py-3.5 px-4">Waktu Backup</th>
                                <th class="py-3.5 px-4">Kategori Data</th>
                                <th class="py-3.5 px-4">Nama File CSV</th>
                                <th class="py-3.5 px-4 text-center">Jumlah Baris</th>
                                <th class="py-3.5 px-4 text-center">Ukuran File</th>
                                <th class="py-3.5 px-4">Oleh</th>
                                <th class="py-3.5 px-4 text-center" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @foreach ($backupLogs as $log)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800">{{ $log->formatted_created_at }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $log->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        @if ($log->backup_type === 'full')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                <i class="fa-solid fa-database text-[10px]"></i> {{ $log->backup_type_label }}
                                            </span>
                                        @elseif ($log->backup_type === 'customers_pppoe')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i class="fa-solid fa-users text-[10px]"></i> {{ $log->backup_type_label }}
                                            </span>
                                        @elseif ($log->backup_type === 'users')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i class="fa-solid fa-user-shield text-[10px]"></i> {{ $log->backup_type_label }}
                                            </span>
                                        @elseif ($log->backup_type === 'finance')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                                <i class="fa-solid fa-file-invoice-dollar text-[10px]"></i> {{ $log->backup_type_label }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                <i class="fa-solid fa-file-lines text-[10px]"></i> {{ $log->backup_type_label }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-mono text-xs font-semibold text-slate-800 truncate max-w-xs" title="{{ $log->file_name }}">
                                            {{ $log->file_name }}
                                        </div>
                                        @if ($log->notes)
                                            <div class="text-[11px] text-slate-400 truncate max-w-xs" title="{{ $log->notes }}">
                                                {{ $log->notes }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-700 whitespace-nowrap">
                                        {{ number_format($log->total_rows) }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono text-xs text-slate-600 whitespace-nowrap">
                                        {{ $log->formatted_size }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-xs text-slate-600">
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-regular fa-user text-slate-400"></i>
                                            {{ $log->user_name ?: 'Sistem' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('migration.backup.download', $log->id) }}"
                                                title="Unduh File CSV"
                                                class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition-colors shadow-2xs">
                                                <i class="fa-solid fa-download text-xs"></i>
                                            </a>
                                            <button type="button"
                                                title="Hapus Log & File"
                                                onclick="confirmDeleteBackup({{ $log->id }}, '{{ $log->file_name }}')"
                                                class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-colors shadow-2xs">
                                                <i class="fa-regular fa-trash-can text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-700 mb-1">Belum Ada Riwayat Log Backup</h4>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">
                        Riwayat log dan link unduh cadangan akan otomatis tercatat di tabel ini setiap kali Anda mengunduh file backup di atas.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Confirm Delete Backup Modal -->
    <div class="modal-overlay" id="deleteBackupModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm border border-rose-100 shrink-0">
                        <i class="fa-solid fa-trash-can"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Hapus File Backup</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Konfirmasi penghapusan file arsip backup.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('deleteBackupModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-5 bg-white">
                <div class="bg-rose-50 p-4 rounded-xl border border-rose-200 text-center">
                    <p class="text-xs font-bold text-slate-800 leading-relaxed mb-2">
                        Apakah Anda yakin ingin menghapus arsip file backup ini dari server?
                    </p>
                    <div class="text-xs font-semibold text-rose-700 font-mono bg-white py-1.5 px-3 rounded-lg border border-rose-200 inline-block max-w-full truncate shadow-sm mb-2"
                        id="deleteBackupFilename">
                        -
                    </div>
                    <p class="text-[11px] text-slate-500 m-0">
                        File .CSV yang tersimpan di server akan dihapus secara permanen beserta catatan riwayatnya.
                    </p>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2">
                <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('deleteBackupModal')">
                    Batal
                </button>
                <form id="formDeleteBackup" action="" method="POST" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !text-white !h-9 !px-5 text-xs font-semibold">
                        Ya, Hapus Arsip
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirm Import Modal -->
    <div class="modal-overlay" id="confirmImportModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm border border-indigo-100 shrink-0">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0" id="confirm-import-title">Konfirmasi Import Data</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Migrasi & impor database dari CSV.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('confirmImportModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-5 bg-white">
                <div class="bg-indigo-50/70 p-4 rounded-xl border border-indigo-100 text-center"
                    id="confirm-import-box">
                    <p class="text-xs font-bold text-slate-800 leading-relaxed mb-2" id="confirm-import-msg">
                        Apakah Anda yakin ingin mengimpor data dari file ini?
                    </p>
                    <div class="text-xs font-semibold text-indigo-700 font-mono bg-white py-1.5 px-3 rounded-lg border border-indigo-200 inline-block max-w-full truncate shadow-sm"
                        id="confirm-import-filename">
                        -
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2">
                <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('confirmImportModal')">
                    Batal
                </button>
                <button type="button" id="btnExecuteImport"
                    class="btn-primary-admin !bg-indigo-600 hover:!bg-indigo-700 !text-white !h-9 !px-5 text-xs font-semibold" onclick="proceedImport()">
                    Ya, Mulai Import
                </button>
            </div>
        </div>
    </div>

    <!-- Confirm Reset Modal -->
    <div class="modal-overlay" id="resetModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm border border-rose-100 shrink-0">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Kosongkan Data</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Pembersihan tabel data & reset paket.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('resetModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-5 bg-white">
                <div class="bg-rose-50 p-4 rounded-xl border border-rose-200 text-center">
                    <p class="text-xs font-semibold text-slate-800 leading-relaxed m-0">
                        Tindakan ini akan mengosongkan seluruh tabel <strong class="text-rose-600 font-bold">PPPoE, Faktur Invoice,
                            Transaksi Pembayaran, dan Paket duplikat lama</strong> serta membuat kembali 4 Paket Resmi yang bersih.
                    </p>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2">
                <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('resetModal')">
                    Batal
                </button>
                <form action="{{ route('migration.reset') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !text-white !h-9 !px-5 text-xs font-semibold">
                        Ya, Kosongkan Data
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Loading Progress -->
    <div class="modal-overlay modal-centered" id="loadingModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl p-8 text-center rounded-3xl">
            <div
                class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 animate-bounce">
                <i class="fa-solid fa-rotate fa-spin"></i>
            </div>
            <h3 class="text-lg font-black text-slate-800 mb-1" id="loadingTitle">Sedang Mengimpor Data...</h3>
            <p class="text-xs text-slate-500 mb-4" id="loadingSubtitle">
                Mohon jangan menutup halaman ini. Sistem sedang memproses data dan menyinkronkan ke database Radius.
            </p>
            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden mb-2">
                <div class="bg-indigo-600 h-full w-full animate-pulse"></div>
            </div>
            <span class="text-[11px] text-slate-400 font-medium">Proses membutuhkan waktu sekitar 10-20 detik...</span>
        </div>
    </div>

    @section('Scripts')
        <script>
            let currentActiveForm = null;

            function triggerImportModal(type) {
                if (type === 'pppoe') {
                    const input = document.getElementById('pppoe_file_input');
                    if (!input.files || input.files.length === 0) {
                        showToast('Silakan pilih file Excel / CSV akun PPPoE terlebih dahulu!', 'error');
                        input.focus();
                        return;
                    }

                    const fileName = input.files[0].name;
                    currentActiveForm = 'formPppoeOnly';

                    $('#confirm-import-icon').attr('class', 'fa-solid fa-file-excel text-emerald-600');
                    $('#confirm-import-title').text('Konfirmasi Import Akun PPPoE');
                    $('#confirm-import-box').attr('class',
                        'bg-emerald-50/70 p-4 rounded-2xl border border-emerald-100 text-center');
                    $('#confirm-import-body-icon').attr('class', 'fa-solid fa-file-excel text-emerald-600 text-3xl mb-3');
                    $('#confirm-import-msg').html(
                        'Apakah Anda yakin ingin mengimpor akun PPPoE dari file <span class="text-emerald-700 font-bold">' +
                        fileName + '</span>?');
                    $('#confirm-import-filename').attr('class',
                        'mt-3 text-xs font-semibold text-emerald-700 font-mono bg-white py-2 px-3 rounded-xl border border-emerald-200 inline-block max-w-full truncate shadow-sm'
                    ).text(fileName);
                    $('#btnExecuteImport').attr('class', 'btn-save !bg-emerald-600 !flex !items-center !justify-center gap-2');

                    $('#loadingTitle').text('Sedang Mengimpor Akun PPPoE...');
                    $('#loadingSubtitle').text('Sistem sedang membaca file Excel dan menyinkronkan data PPPoE ke Database RADIUS.');

                    openCustomModal('confirmImportModal');
                } else {
                    const input = document.getElementById('full_backup_file_input');
                    if (!input.files || input.files.length === 0) {
                        showToast('Silakan pilih file backup CSV lengkap terlebih dahulu!', 'error');
                        input.focus();
                        return;
                    }

                    const fileName = input.files[0].name;
                    currentActiveForm = 'formFullBackup';

                    $('#confirm-import-icon').attr('class', 'fa-solid fa-cloud-arrow-up text-indigo-600');
                    $('#confirm-import-title').text('Konfirmasi Import Full Backup');
                    $('#confirm-import-box').attr('class',
                        'bg-indigo-50/70 p-4 rounded-2xl border border-indigo-100 text-center');
                    $('#confirm-import-body-icon').attr('class', 'fa-solid fa-cloud-arrow-up text-indigo-600 text-3xl mb-3');
                    $('#confirm-import-msg').html(
                        'Apakah Anda yakin ingin memulihkan seluruh Paket, PPPoE, Faktur, dan Transaksi dari file <span class="text-indigo-700 font-bold">' +
                        fileName + '</span>?');
                    $('#confirm-import-filename').attr('class',
                        'mt-3 text-xs font-semibold text-indigo-700 font-mono bg-white py-2 px-3 rounded-xl border border-indigo-200 inline-block max-w-full truncate shadow-sm'
                    ).text(fileName);
                    $('#btnExecuteImport').attr('class', 'btn-save !bg-indigo-600 !flex !items-center !justify-center gap-2');

                    $('#loadingTitle').text('Sedang Memproses Full Backup...');
                    $('#loadingSubtitle').text(
                        'Sistem sedang mengimpor paket, akun PPPoE, faktur invoice, dan riwayat transaksi.');

                    openCustomModal('confirmImportModal');
                }
            }

            function proceedImport() {
                if (!currentActiveForm) return;

                closeCustomModal('confirmImportModal');
                openCustomModal('loadingModal');

                const form = document.getElementById(currentActiveForm);
                if (form) {
                    form.submit();
                }
            }

            function confirmDeleteBackup(id, filename) {
                $('#deleteBackupFilename').text(filename);
                $('#formDeleteBackup').attr('action', '/migration/backup/' + id);
                openCustomModal('deleteBackupModal');
            }
        </script>
    @endsection
</x-layouts.app>
