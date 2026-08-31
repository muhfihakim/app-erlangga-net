<x-layouts.app>
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Pulihkan & Import Data
            </h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0;">
                Pilih file untuk memulihkan akun PPPoE saja (Excel) atau Full Backup (Paket, PPPoE, Faktur & Transaksi).
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

    <!-- 2 Forms Grid (PPPoE Only vs Full Backup) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        <!-- Form 1: PPPoE Saja (Excel XLSX / CSV) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-file-excel"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">1. Import Akun PPPoE Saja</h2>
                        <p class="text-xs text-slate-500">File Excel (.xlsx) / CSV Users PPP</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                    Gunakan form ini jika Anda hanya ingin memasukkan data <strong>Akun PPPoE, Username, Password, IP
                        Address, Nama Pelanggan, No WA, dan Alamat</strong> dari file export Excel Radboox (tanpa faktur
                    dan riwayat transaksi).
                </p>

                <form action="{{ route('migration.import') }}" method="POST" enctype="multipart/form-data"
                    id="formPppoeOnly">
                    @csrf
                    <input type="hidden" name="import_type" value="pppoe_only">

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih File Excel / CSV (.xlsx /
                            .csv):</label>
                        <input type="file" name="pppoe_file" id="pppoe_file_input"
                            accept=".xlsx, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv"
                            required class="form-input-custom w-full !p-2 text-xs bg-slate-50">
                        <span class="text-[11px] text-slate-400 mt-1 block">Contoh: <code
                                class="bg-slate-100 px-1 py-0.5 rounded text-slate-600 font-mono">radboox_export_8_31_2026.xlsx</code></span>
                    </div>

                    <div class="mb-5 p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="sync_radius" checked
                                class="w-4 h-4 mt-0.5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-bold text-slate-800">Sinkronkan ke FreeRADIUS /
                                    daloRADIUS</span>
                                <p class="text-[11px] text-slate-500">Memasukkan username, password, IP, profil ke tabel
                                    radcheck, radreply, radusergroup, dan userinfo.</p>
                            </div>
                        </label>
                    </div>

                    <button type="button" onclick="triggerImportModal('pppoe')"
                        class="btn-primary-admin !bg-emerald-600 hover:!bg-emerald-700 !w-full !justify-center !py-3 !text-xs flex items-center gap-2 shadow-md">
                        <i class="fa-solid fa-file-excel"></i> Import Akun PPPoE
                    </button>
                </form>
            </div>
        </div>

        <!-- Form 2: Full Backup Radboox (Paket + PPPoE + Faktur + Transaksi) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">2. Import Full Backup Radboox</h2>
                        <p class="text-xs text-slate-500">File Backup Lengkap (.csv)</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                    Gunakan form ini untuk memulihkan <strong>seluruh data sistem</strong> sekaligus: Paket Layanan,
                    Akun PPPoE, seluruh Faktur / Invoice, dan Riwayat Transaksi Pembayaran dari file backup lengkap CSV
                    Radboox.
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
                                class="bg-slate-100 px-1 py-0.5 rounded text-slate-600 font-mono">radboox_backup_8_31_2026
                                (1).csv</code></span>
                    </div>

                    <div class="mb-5 p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2.5">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="sync_radius" checked
                                class="w-4 h-4 mt-0.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <div>
                                <span class="text-xs font-bold text-slate-800">Sinkronkan ke FreeRADIUS</span>
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

    <!-- Confirm Import Modal (Same UI Style as Global Delete) -->
    <div class="modal-overlay" id="confirmImportModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-file-import text-indigo-600" id="confirm-import-icon"></i>
                    <span class="text-slate-900 font-bold text-[17px]" id="confirm-import-title">Konfirmasi Import
                        Data</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('confirmImportModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-8 bg-white">
                <div class="bg-indigo-50/70 p-4 rounded-2xl border border-indigo-100 text-center"
                    id="confirm-import-box">
                    <i class="fa-solid fa-cloud-arrow-up text-indigo-600 text-3xl mb-3"
                        id="confirm-import-body-icon"></i>
                    <p class="text-sm font-bold text-slate-800 leading-relaxed" id="confirm-import-msg">
                        Apakah Anda yakin ingin mengimpor data dari file ini?
                    </p>
                    <div class="mt-3 text-xs font-semibold text-indigo-700 font-mono bg-white py-2 px-3 rounded-xl border border-indigo-200 inline-block max-w-full truncate shadow-sm"
                        id="confirm-import-filename">
                        -
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <div class="w-full flex justify-center gap-3">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        style="padding: 10px 24px;" onclick="closeCustomModal('confirmImportModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="button" id="btnExecuteImport"
                        class="btn-save !bg-indigo-600 !flex !items-center !justify-center gap-2"
                        style="padding: 10px 24px;" onclick="proceedImport()">
                        <i class="fa-solid fa-play"></i> Ya, Mulai Import!
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Reset Modal (Same UI Style as Global Delete) -->
    <div class="modal-overlay" id="resetModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-trash text-rose-600"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Kosongkan Data?</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('resetModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-8 bg-white">
                <div class="bg-rose-50 p-4 rounded-2xl border border-rose-100 text-center">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-3xl mb-3"></i>
                    <p class="text-sm font-bold text-slate-800 leading-relaxed">
                        Tindakan ini akan mengosongkan seluruh tabel <span class="text-rose-600">PPPoE, Faktur Invoice,
                            Transaksi Pembayaran, dan Paket duplikat lama</span> serta membuat kembali 4 Paket Resmi
                        yang bersih.
                    </p>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <form action="{{ route('migration.reset') }}" method="POST"
                    class="w-full flex justify-center gap-3">
                    @csrf
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        style="padding: 10px 24px;" onclick="closeCustomModal('resetModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-save !bg-rose-600 !flex !items-center !justify-center gap-2"
                        style="padding: 10px 24px;">
                        <i class="fa-solid fa-trash-can"></i> Ya, Kosongkan Data!
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Loading Progress -->
    <div class="modal-overlay" id="loadingModal">
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
                    $('#loadingSubtitle').text('Sistem sedang membaca file Excel dan menyinkronkan data PPPoE ke FreeRADIUS.');

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
        </script>
    @endsection
</x-layouts.app>
