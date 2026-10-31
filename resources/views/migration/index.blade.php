<x-layouts.app>
    @section('Css')
        <style>
            .dataTables_wrapper {
                padding: 0;
                position: relative;
                min-height: 180px;
            }
            .dataTables_filter {
                display: none;
            }
            .dataTables_paginate {
                display: flex;
                align-items: center;
                gap: 4px;
            }
            .paginate_button {
                height: 30px;
                padding: 0 10px;
                border-radius: 6px;
                border: 1px solid #e2e8f0;
                background: #fff;
                color: #475569 !important;
                font-size: 11px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.15s ease;
            }
            .paginate_button:hover:not(.current):not(.disabled) {
                background: #f8fafc;
                border-color: #cbd5e1;
                color: #1e293b !important;
            }
            .paginate_button.current {
                background: #4f46e5;
                border-color: #4f46e5;
                color: #fff !important;
            }
            .paginate_button.disabled {
                opacity: 0.5;
                cursor: not-allowed;
                background: #f8fafc;
                border-color: #e2e8f0;
                color: #94a3b8 !important;
            }
            .dataTables_info {
                font-size: 11px;
                color: #64748b;
                font-weight: 500;
            }
            .dataTables_wrapper .dataTables_processing {
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                background: rgba(255, 255, 255, 0.95) !important;
                padding: 10px 18px !important;
                border-radius: 10px !important;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
                z-index: 50 !important;
                margin: 0 !important;
                border: 1px solid #e2e8f0 !important;
                width: auto !important;
                height: auto !important;
                text-align: center;
            }
            #backup-log-table td {
                padding: 10px 14px;
                vertical-align: middle;
            }
        </style>
    @endsection

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Backup & Restore Data</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">
                Cadangkan database ke format .CSV, pulihkan akun PPPoE atau Full Backup, dan kelola riwayat cadangan sistem.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openCustomModal('resetModal')"
                class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !px-3.5 !h-8.5 !text-xs font-semibold shadow-xs">
                Reset & Kosongkan Data
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 text-emerald-700 p-3.5 rounded-xl border border-emerald-200 mb-4 flex items-start gap-3 shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-500 text-base mt-0.5"></i>
            <div>
                <strong class="font-bold text-xs">Berhasil!</strong>
                <p class="text-xs mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-rose-50 text-rose-700 p-3.5 rounded-xl border border-rose-200 mb-4 flex items-start gap-3 shadow-xs">
            <i class="fa-solid fa-circle-exclamation text-rose-500 text-base mt-0.5"></i>
            <div>
                <strong class="font-bold text-xs">Terjadi Kesalahan!</strong>
                <p class="text-xs mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-rose-50 text-rose-700 p-3.5 rounded-xl border border-rose-200 mb-4 shadow-xs">
            <div class="flex items-center gap-2 font-bold text-xs mb-1">
                <i class="fa-solid fa-circle-exclamation text-rose-500"></i> Mohon Periksa Input:
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Stat Cards Summary -->
    <div class="stats-grid mb-5">
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

    <!-- SEKSI 1: OPSI BACKUP DATA (.CSV) -->
    <div class="mb-5">
        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
            <div>
                <h2 class="text-sm sm:text-base font-bold text-slate-800 m-0">
                    Opsi Cadangkan / Backup Data (.CSV)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5 m-0">
                    Pilih kategori data untuk diunduh dalam format spreadsheet .CSV berstandar UTF-8 (kompatibel penuh dengan Microsoft Excel).
                </p>
            </div>
            <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                Format Output: .CSV UTF-8
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3.5">
            <!-- Card Backup 1: Full Backup -->
            <div class="bg-white p-4 rounded-xl border-2 border-indigo-100 shadow-xs hover:border-indigo-300 transition-all flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0">
                    <span class="bg-indigo-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-bl-lg uppercase tracking-wider">
                        Master
                    </span>
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 mb-1">Backup Seluruh Data</h3>
                    <p class="text-[11px] text-slate-500 leading-relaxed mb-3">
                        Master CSV menggabungkan seluruh tabel: <strong>Paket Layanan, Akun Login Sistem, Pelanggan, PPPoE, Faktur Tagihan,</strong> dan <strong>Riwayat Transaksi</strong>.
                    </p>
                </div>
                <a href="{{ route('migration.backup', 'full') }}"
                    class="btn-primary-admin !bg-indigo-600 hover:!bg-indigo-700 !w-full !justify-center !h-8 text-xs font-semibold shadow-xs mt-1">
                    Unduh Master CSV
                </a>
            </div>

            <!-- Card Backup 2: Pelanggan & PPPoE -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs hover:border-emerald-300 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-800">Pelanggan & PPPoE</h3>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded">
                            {{ number_format($dbStats['pppoes']) }} Akun
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed mb-3">
                        Mencakup Username & Password PPPoE, IP Address, Nama Pelanggan, No WA, Alamat, Koordinat, NIK, SN Perangkat, Modem, Teknisi & Sales.
                    </p>
                </div>
                <a href="{{ route('migration.backup', 'customers_pppoe') }}"
                    class="btn-primary-admin !bg-emerald-600 hover:!bg-emerald-700 !w-full !justify-center !h-8 text-xs font-semibold shadow-xs mt-1">
                    Unduh CSV Pelanggan
                </a>
            </div>

            <!-- Card Backup 3: Akun Sistem (Users) -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs hover:border-amber-300 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-800">Akun Pengguna Sistem</h3>
                        <span class="text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">
                            {{ number_format($dbStats['users']) }} User
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed mb-3">
                        Data akun pengguna aplikasi: Administrator, Teknisi, Sales, Staf Kasir, info login, No WhatsApp, Email, dan Hak Akses Jabatan.
                    </p>
                </div>
                <a href="{{ route('migration.backup', 'users') }}"
                    class="btn-primary-admin !bg-amber-600 hover:!bg-amber-700 !w-full !justify-center !h-8 text-xs font-semibold shadow-xs mt-1">
                    Unduh CSV Akun
                </a>
            </div>

            <!-- Card Backup 4: Transaksi & Keuangan -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs hover:border-sky-300 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-800">Transaksi & Keuangan</h3>
                        <span class="text-[10px] font-bold text-sky-700 bg-sky-50 border border-sky-200 px-1.5 py-0.5 rounded">
                            {{ number_format($dbStats['transactions']) }} Data
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed mb-3">
                        Seluruh riwayat transaksi pembayaran tagihan, nomor invoice, nominal bayar, biaya admin, kanal bayar, dan tanggal verifikasi pembayaran.
                    </p>
                </div>
                <a href="{{ route('migration.backup', 'finance') }}"
                    class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !w-full !justify-center !h-8 text-xs font-semibold shadow-xs mt-1">
                    Unduh CSV Keuangan
                </a>
            </div>
        </div>
    </div>

    <!-- SEKSI 2: PULIHKAN & IMPORT DATA -->
    <div class="mb-5">
        <div class="mb-3">
            <h2 class="text-sm sm:text-base font-bold text-slate-800 m-0">
                Pulihkan & Import Data
            </h2>
            <p class="text-xs text-slate-500 mt-0.5 m-0">
                Pilih file untuk memulihkan akun PPPoE saja (Excel .xlsx / .csv) atau Full Backup sistem (.csv).
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5">
            <!-- Form 1: PPPoE Saja (Excel XLSX / CSV) -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 mb-0.5">1. Pulihkan / Import Pelanggan & PPPoE</h3>
                    <p class="text-[11px] text-slate-500 mb-2">File Backup CSV Pelanggan atau Excel (.xlsx)</p>
                    <p class="text-[11px] text-slate-600 mb-3 leading-relaxed">
                        Gunakan form ini untuk memulihkan data <strong>Pelanggan, PPPoE, Username, Password, IP Address, Alamat, NIK, dan Koordinat</strong> dari file cadangan <code class="bg-emerald-50 text-emerald-700 px-1 py-0.5 rounded font-mono">backup_pelanggan_pppoe_*.csv</code> atau file Excel <code class="bg-slate-100 text-slate-700 px-1 py-0.5 rounded font-mono">users_ppp_export.xlsx</code>.
                    </p>

                    <form action="{{ route('migration.import') }}" method="POST" enctype="multipart/form-data" id="formPppoeOnly">
                        @csrf
                        <input type="hidden" name="import_type" value="pppoe_only">

                        <div class="form-group mb-2.5">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1 block">Pilih File Backup CSV / Excel (.csv / .xlsx): <span class="text-rose-500">*</span></label>
                            <input type="file" name="pppoe_file" id="pppoe_file_input"
                                accept=".xlsx, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv"
                                required class="form-input !font-normal w-full bg-slate-50 border-slate-300 text-xs py-1">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Contoh: <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-600 font-mono">backup_pelanggan_pppoe.csv</code></span>
                        </div>

                        <div class="mb-3 p-2.5 bg-slate-50 rounded-lg border border-slate-200">
                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" name="sync_radius" checked
                                    class="w-4 h-4 mt-0.5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Sinkronkan ke Database RADIUS</span>
                                    <p class="text-[10px] text-slate-500 mt-0.5">Memasukkan username, password, IP ke tabel radcheck, radreply, radusergroup.</p>
                                </div>
                            </label>
                        </div>

                        <button type="button" onclick="triggerImportModal('pppoe')"
                            class="btn-primary-admin !bg-emerald-600 hover:!bg-emerald-700 !w-full !justify-center !h-8 text-xs font-semibold shadow-xs">
                            Pulihkan Akun PPPoE
                        </button>
                    </form>
                </div>
            </div>

            <!-- Form 2: Full Backup Sistem (Paket + PPPoE + Faktur + Transaksi) -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 mb-0.5">2. Pulihkan / Import Full Backup Sistem</h3>
                    <p class="text-[11px] text-slate-500 mb-2">File Backup Lengkap (.csv)</p>
                    <p class="text-[11px] text-slate-600 mb-3 leading-relaxed">
                        Gunakan form ini untuk memulihkan <strong>seluruh data sistem</strong> sekaligus: Paket Layanan, Akun Pengguna Sistem, Akun PPPoE & Pelanggan, Faktur Invoice, dan Transaksi dari file <code class="bg-indigo-50 text-indigo-700 px-1 py-0.5 rounded font-mono">backup_seluruh_data_*.csv</code>.
                    </p>

                    <form action="{{ route('migration.import') }}" method="POST" enctype="multipart/form-data" id="formFullBackup">
                        @csrf
                        <input type="hidden" name="import_type" value="full">

                        <div class="form-group mb-2.5">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1 block">Pilih File Backup CSV (.csv): <span class="text-rose-500">*</span></label>
                            <input type="file" name="full_backup_file" id="full_backup_file_input"
                                accept=".csv, text/csv" required class="form-input !font-normal w-full bg-slate-50 border-slate-300 text-xs py-1">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Contoh: <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-600 font-mono">backup_seluruh_data.csv</code></span>
                        </div>

                        <div class="mb-3 p-2.5 bg-slate-50 rounded-lg border border-slate-200 space-y-1.5">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="sync_radius" checked
                                    class="w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <span class="text-xs font-bold text-slate-800">Sinkronkan ke Database RADIUS</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="import_invoices" checked
                                    class="w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <span class="text-xs font-bold text-slate-800">Import Faktur / Invoice Tagihan</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="import_transactions" checked
                                    class="w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <span class="text-xs font-bold text-slate-800">Import Riwayat Transaksi Pembayaran</span>
                            </label>
                        </div>

                        <button type="button" onclick="triggerImportModal('full')"
                            class="btn-primary-admin !h-8 !w-full !justify-center text-xs font-semibold shadow-xs">
                            Pulihkan Full Backup
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SEKSI 3: RIWAYAT & LOG BACKUP (.CSV) -->
    <div class="mb-6">
        <div class="table-card overflow-hidden">
            <div class="p-3.5 sm:p-4 border-b border-slate-200 flex items-center justify-between flex-wrap gap-3 bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 m-0">
                        Riwayat Log Backup Data (.CSV)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5 m-0">
                        Daftar arsip cadangan yang telah dibuat di server dan dapat diunduh ulang sewaktu-waktu.
                    </p>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="search-wrap !mb-0">
                        <input type="search" id="search-backup-logs" class="search-input !h-8 !text-xs !w-44 sm:!w-60" placeholder="Cari arsip backup..." autocomplete="off" />
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-white border border-slate-200 text-slate-600 rounded-lg shadow-2xs shrink-0">
                        Total: <strong id="total-backup-badge">{{ number_format($totalBackupLogs) }}</strong> Arsip
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table id="backup-log-table" class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="text-left" style="width: 180px;">Waktu Backup</th>
                            <th class="text-left" style="width: 160px;">Kategori Data</th>
                            <th class="text-left">Nama File CSV</th>
                            <th class="text-center" style="width: 100px;">Jumlah Baris</th>
                            <th class="text-center" style="width: 95px;">Ukuran File</th>
                            <th class="text-left" style="width: 120px;">Oleh</th>
                            <th class="text-center" style="width: 125px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Confirm Delete Backup Modal -->
    <div class="modal-overlay" id="deleteBackupModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-5 py-3.5 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 m-0">Hapus File Backup</h3>
                    <p class="text-[11px] text-slate-500 m-0 mt-0.5">Konfirmasi penghapusan arsip cadangan.</p>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('deleteBackupModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="bg-rose-50 p-3.5 rounded-xl border border-rose-200 text-center">
                    <p class="text-xs font-bold text-slate-800 leading-relaxed mb-2">
                        Apakah Anda yakin ingin menghapus arsip file backup ini dari server?
                    </p>
                    <div class="text-xs font-semibold text-rose-700 font-mono bg-white py-1.5 px-3 rounded-lg border border-rose-200 inline-block max-w-full truncate shadow-xs mb-2"
                        id="deleteBackupFilename">
                        -
                    </div>
                    <p class="text-[11px] text-slate-500 m-0">
                        File .CSV yang tersimpan di server akan dihapus secara permanen beserta catatan riwayatnya.
                    </p>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-5 py-3 flex items-center justify-end gap-2">
                <button type="button" class="btn-cancel !h-8 !px-3.5 text-xs font-semibold" onclick="closeCustomModal('deleteBackupModal')">
                    Batal
                </button>
                <form id="formDeleteBackup" action="" method="POST" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !text-white !h-8 !px-4 text-xs font-semibold">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirm Import Modal -->
    <div class="modal-overlay" id="confirmImportModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-5 py-3.5 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 m-0" id="confirm-import-title">Konfirmasi Import Data</h3>
                    <p class="text-[11px] text-slate-500 m-0 mt-0.5">Migrasi & impor database dari CSV/Excel.</p>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('confirmImportModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="bg-indigo-50/70 p-3.5 rounded-xl border border-indigo-100 text-center" id="confirm-import-box">
                    <p class="text-xs font-bold text-slate-800 leading-relaxed mb-2" id="confirm-import-msg">
                        Apakah Anda yakin ingin mengimpor data dari file ini?
                    </p>
                    <div class="text-xs font-semibold text-indigo-700 font-mono bg-white py-1.5 px-3 rounded-lg border border-indigo-200 inline-block max-w-full truncate shadow-xs"
                        id="confirm-import-filename">
                        -
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-5 py-3 flex items-center justify-end gap-2">
                <button type="button" class="btn-cancel !h-8 !px-3.5 text-xs font-semibold" onclick="closeCustomModal('confirmImportModal')">
                    Batal
                </button>
                <button type="button" id="btnExecuteImport" class="btn-primary-admin !h-8 !px-4 text-xs font-semibold" onclick="proceedImport()">
                    Mulai Import
                </button>
            </div>
        </div>
    </div>

    <!-- Confirm Reset Modal -->
    <div class="modal-overlay" id="resetModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header border-b border-slate-200 bg-white px-5 py-3.5 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 m-0">Kosongkan Data</h3>
                    <p class="text-[11px] text-slate-500 m-0 mt-0.5">Pembersihan tabel data & reset paket.</p>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('resetModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="bg-rose-50 p-3.5 rounded-xl border border-rose-200 text-center">
                    <p class="text-xs font-semibold text-slate-800 leading-relaxed m-0">
                        Tindakan ini akan mengosongkan seluruh tabel <strong class="text-rose-600 font-bold">PPPoE, Faktur Invoice,
                            Transaksi Pembayaran, dan Paket duplikat lama</strong> serta membuat kembali 4 Paket Resmi yang bersih.
                    </p>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-5 py-3 flex items-center justify-end gap-2">
                <button type="button" class="btn-cancel !h-8 !px-3.5 text-xs font-semibold" onclick="closeCustomModal('resetModal')">
                    Batal
                </button>
                <form action="{{ route('migration.reset') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !text-white !h-8 !px-4 text-xs font-semibold">
                        Kosongkan Data
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Loading Progress -->
    <div class="modal-overlay modal-centered" id="loadingModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl p-5 text-center rounded-2xl">
            <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center text-lg mx-auto mb-2.5 animate-bounce">
                <i class="fa-solid fa-rotate fa-spin"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-800 mb-1" id="loadingTitle">Sedang Mengimpor Data...</h3>
            <p class="text-xs text-slate-500 mb-3" id="loadingSubtitle">
                Mohon jangan menutup halaman ini. Sistem sedang memproses data dan menyinkronkan ke database Radius.
            </p>
            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden mb-2">
                <div class="bg-indigo-600 h-full w-full animate-pulse"></div>
            </div>
            <span class="text-[10px] text-slate-400 font-medium">Proses membutuhkan waktu sekitar 10-20 detik...</span>
        </div>
    </div>

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            let currentActiveForm = null;

            function triggerImportModal(type) {
                if (type === 'pppoe') {
                    const input = document.getElementById('pppoe_file_input');
                    if (!input.files || input.files.length === 0) {
                        alert('Silakan pilih file Excel / CSV akun PPPoE terlebih dahulu!');
                        input.focus();
                        return;
                    }

                    const fileName = input.files[0].name;
                    currentActiveForm = 'formPppoeOnly';

                    $('#confirm-import-title').text('Konfirmasi Import Akun PPPoE');
                    $('#confirm-import-box').attr('class', 'bg-emerald-50/70 p-3.5 rounded-xl border border-emerald-100 text-center');
                    $('#confirm-import-msg').html('Apakah Anda yakin ingin mengimpor akun PPPoE dari file <span class="text-emerald-700 font-bold">' + fileName + '</span>?');
                    $('#confirm-import-filename').attr('class', 'mt-2 text-xs font-semibold text-emerald-700 font-mono bg-white py-1.5 px-3 rounded-lg border border-emerald-200 inline-block max-w-full truncate shadow-xs').text(fileName);
                    $('#btnExecuteImport').attr('class', 'btn-primary-admin !bg-emerald-600 hover:!bg-emerald-700 !h-8 !px-4 text-xs font-semibold').text('Mulai Import');

                    $('#loadingTitle').text('Sedang Mengimpor Akun PPPoE...');
                    $('#loadingSubtitle').text('Sistem sedang membaca file Excel dan menyinkronkan data PPPoE ke Database RADIUS.');

                    openCustomModal('confirmImportModal');
                } else {
                    const input = document.getElementById('full_backup_file_input');
                    if (!input.files || input.files.length === 0) {
                        alert('Silakan pilih file backup CSV lengkap terlebih dahulu!');
                        input.focus();
                        return;
                    }

                    const fileName = input.files[0].name;
                    currentActiveForm = 'formFullBackup';

                    $('#confirm-import-title').text('Konfirmasi Import Full Backup');
                    $('#confirm-import-box').attr('class', 'bg-indigo-50/70 p-3.5 rounded-xl border border-indigo-100 text-center');
                    $('#confirm-import-msg').html('Apakah Anda yakin ingin memulihkan seluruh Paket, PPPoE, Faktur, dan Transaksi dari file <span class="text-indigo-700 font-bold">' + fileName + '</span>?');
                    $('#confirm-import-filename').attr('class', 'mt-2 text-xs font-semibold text-indigo-700 font-mono bg-white py-1.5 px-3 rounded-lg border border-indigo-200 inline-block max-w-full truncate shadow-xs').text(fileName);
                    $('#btnExecuteImport').attr('class', 'btn-primary-admin !bg-indigo-600 hover:!bg-indigo-700 !h-8 !px-4 text-xs font-semibold').text('Mulai Import');

                    $('#loadingTitle').text('Sedang Memproses Full Backup...');
                    $('#loadingSubtitle').text('Sistem sedang mengimpor paket, akun PPPoE, faktur invoice, dan riwayat transaksi.');

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

            $(document).ready(function() {
                const backupTable = $('#backup-log-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('migration.index') }}',
                    columns: [
                        { data: 'created_at', name: 'created_at', className: 'text-left whitespace-nowrap' },
                        { data: 'backup_type', name: 'backup_type', className: 'text-left whitespace-nowrap' },
                        { data: 'file_name', name: 'file_name', className: 'text-left' },
                        { data: 'total_rows', name: 'total_rows', className: 'text-center whitespace-nowrap' },
                        { data: 'formatted_size', name: 'file_size', className: 'text-center whitespace-nowrap' },
                        { data: 'user_name', name: 'user_name', className: 'text-left whitespace-nowrap' },
                        { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center whitespace-nowrap' }
                    ],
                    order: [[0, 'desc']],
                    pageLength: 10,
                    lengthChange: false,
                    ordering: true,
                    info: true,
                    responsive: true,
                    dom: 't<"flex flex-col sm:flex-row items-center justify-between gap-3 p-3.5 border-t border-slate-200"ip>',
                    language: {
                        processing: '<div class="text-xs font-semibold text-indigo-600">Memuat riwayat log...</div>',
                        zeroRecords: '<div class="p-6 text-center text-xs text-slate-400">Tidak ada riwayat log backup yang cocok.</div>',
                        emptyTable: '<div class="p-6 text-center text-xs text-slate-400">Belum ada riwayat log backup di server.</div>',
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ arsip',
                        infoEmpty: 'Menampilkan 0 arsip',
                        infoFiltered: '(disaring dari _MAX_ total arsip)',
                        paginate: {
                            previous: 'Sebelumnya',
                            next: 'Berikutnya'
                        }
                    },
                    drawCallback: function(settings) {
                        const total = settings.fnRecordsTotal ? settings.fnRecordsTotal() : 0;
                        $('#total-backup-badge').text(total.toLocaleString('id-ID'));
                    }
                });

                $('#search-backup-logs').on('keyup input', function() {
                    backupTable.search(this.value).draw();
                });
            });
        </script>
    @endsection
</x-layouts.app>
