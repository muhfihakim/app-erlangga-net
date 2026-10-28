{{-- Modal Tambah Pre-Order --}}
@if (auth()->user()->jabatan === 'Sales' || auth()->user()->isAdmin())
    <div class="modal-overlay" id="addPreOrderModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <!-- Header -->
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Tambah Pre-Order Baru</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Registrasi calon pelanggan baru untuk survei dan pemasangan.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('addPreOrderModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-tabs bg-white border-b border-slate-200 px-6">
                <button type="button" class="modal-tab active" data-tab="add-tab-biodata">
                    <i class="fa-regular fa-user text-xs"></i> Biodata
                </button>
                <button type="button" class="modal-tab" data-tab="add-tab-paket">
                    <i class="fa-regular fa-folder-open text-xs"></i> Paket & Lokasi
                </button>
                <button type="button" class="modal-tab" data-tab="add-tab-media">
                    <i class="fa-regular fa-image text-xs"></i> Foto / Media
                </button>
            </div>

            <form action="{{ route('pre_orders.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="id_sales" value="{{ auth()->user()->id }}">
                <input type="hidden" name="nama_sales" value="{{ auth()->user()->nama_lengkap }}">

                <div class="modal-body bg-white p-6 max-h-[70vh] overflow-y-auto">
                    <!-- Tab Biodata -->
                    <div class="tab-panel active" id="add-tab-biodata">
                        <div class="form-group mb-3.5">
                            <label class="form-label text-slate-700 font-medium text-xs">Nama Lengkap <span
                                    class="text-rose-500">*</span></label>
                            <input type="text" class="form-input !font-normal bg-white text-slate-800 border-slate-300"
                                name="nama_lengkap" placeholder="Nama lengkap calon pelanggan" required>
                        </div>
                        <div class="form-grid-2 mb-3.5">
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">No. Identitas (NIK)</label>
                                <input type="text" class="form-input !font-normal bg-white text-slate-800 border-slate-300"
                                    name="no_identitas" placeholder="3201..." required>
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">No. WhatsApp <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" class="form-input !font-normal bg-white text-slate-800 border-slate-300"
                                    name="no_whatsapp" placeholder="0812..." required>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs">Alamat Email</label>
                            <input type="email" class="form-input !font-normal bg-white text-slate-800 border-slate-300"
                                name="alamat_email" placeholder="nama@email.com">
                        </div>
                    </div>

                    <!-- Tab Paket & Lokasi -->
                    <div class="tab-panel" id="add-tab-paket">
                        <div class="form-group mb-3.5">
                            <label class="form-label text-slate-700 font-medium text-xs">Pilih Paket Internet <span
                                    class="text-rose-500">*</span></label>
                            <select class="form-input-custom !font-normal w-full bg-white text-slate-800 border-slate-300"
                                name="paket_id" required>
                                <option value="">-- Pilih Paket --</option>
                                @foreach ($pakets as $paket)
                                    <option value="{{ $paket->id }}">{{ $paket->nama_paket }} - Rp
                                        {{ number_format($paket->harga, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3.5">
                            <label class="form-label text-slate-700 font-medium text-xs">Alamat Lengkap Pemasangan <span
                                    class="text-rose-500">*</span></label>
                            <textarea class="form-textarea !font-normal bg-white text-slate-800 border-slate-300" name="alamat_lengkap"
                                rows="3" placeholder="Jl. Raya No. 123..." required></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs">Titik Koordinat</label>
                            <div class="flex gap-2">
                                <input type="text"
                                    class="form-input !font-normal {{ auth()->user()->isAdmin() ? 'bg-white' : 'bg-slate-50' }} text-slate-800 border-slate-300 font-mono text-xs"
                                    id="titik_koordinat" name="titik_koordinat"
                                    placeholder="{{ auth()->user()->isAdmin() ? 'Ketik manual / klik ikon...' : 'Koordinat...' }}"
                                    {{ auth()->user()->isAdmin() ? '' : 'readonly' }}>
                                <button type="button"
                                    class="btn-primary-admin !h-10 !px-3 !shadow-none !flex !items-center !justify-center"
                                    id="get-location-btn" title="Ambil Lokasi Saat Ini">
                                    <i class="fa-solid fa-location-crosshairs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Media -->
                    <div class="tab-panel" id="add-tab-media">
                        <div class="form-group mb-3.5">
                            <label class="form-label text-slate-700 font-medium text-xs">Foto KTP</label>
                            <input type="file" class="form-input !font-normal bg-white text-slate-700 border-slate-300 text-xs"
                                name="foto_ktp" accept="image/*">
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs">Foto Rumah / Lokasi</label>
                            <input type="file" class="form-input !font-normal bg-white text-slate-700 border-slate-300 text-xs"
                                name="foto_rumah" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                        onclick="closeCustomModal('addPreOrderModal')">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !h-9 !px-5 text-xs font-semibold">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- Modal Edit Pre-Order --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
        <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
            <div class="modal-title">
                <i class="fa-solid fa-pen-to-square text-amber-500"></i>
                <span class="text-slate-900 font-bold">Edit Data Pre-Order</span>
            </div>
            <button type="button" class="modal-close" onclick="closeCustomModal('editModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-tabs bg-white border-b border-slate-200">
            <button class="modal-tab active" data-tab="edit-tab-biodata">Biodata</button>
            <button class="modal-tab" data-tab="edit-tab-paket">Paket & Lokasi</button>
            <button class="modal-tab" data-tab="edit-tab-media">Foto</button>
        </div>

        <form id="editForm" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            <div class="modal-body bg-white">
                <div class="tab-panel active" id="edit-tab-biodata">
                    <div class="form-group">
                        <label class="form-label text-slate-800 font-bold">Nama Lengkap</label>
                        <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                            id="edit_nama_lengkap" name="nama_lengkap" required>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">No. Identitas</label>
                            <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                                id="edit_no_identitas" name="no_identitas" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">No. WhatsApp</label>
                            <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                                id="edit_no_whatsapp" name="no_whatsapp" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label text-slate-800 font-bold">Alamat Email</label>
                        <input type="email" class="form-input bg-white text-slate-900 border-slate-300"
                            id="edit_alamat_email" name="alamat_email">
                    </div>
                </div>

                <div class="tab-panel" id="edit-tab-paket">
                    <div class="form-group">
                        <label class="form-label text-slate-800 font-bold">Paket Internet</label>
                        <select class="form-input-custom w-full bg-white text-slate-900 border-slate-300"
                            id="edit_paket_id" name="paket_id" required>
                            @foreach ($pakets as $paket)
                                <option value="{{ $paket->id }}">{{ $paket->nama_paket }} - Rp
                                    {{ number_format($paket->harga, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label text-slate-800 font-bold">Alamat Lengkap</label>
                        <textarea class="form-textarea bg-white text-slate-900 border-slate-300" id="edit_alamat_lengkap"
                            name="alamat_lengkap" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label text-slate-800 font-bold">Titik Koordinat</label>
                        <div class="flex gap-2">
                            <input type="text"
                                class="form-input {{ auth()->user()->isAdmin() ? 'bg-white' : 'bg-slate-50' }} text-slate-900 border-slate-300"
                                id="edit_titik_koordinat" name="titik_koordinat"
                                placeholder="{{ auth()->user()->isAdmin() ? 'Ketik manual / klik ikon...' : '' }}"
                                {{ auth()->user()->isAdmin() ? '' : 'readonly' }}>
                            <button type="button"
                                class="btn-primary-admin !shadow-none !flex !items-center !justify-center"
                                id="edit-get-location-btn">
                                <i class="fa-solid fa-location-crosshairs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="tab-panel" id="edit-tab-media">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Foto KTP</label>
                            <div id="current_foto_ktp" class="mb-2 border rounded-xl overflow-hidden"></div>
                            <input type="file" class="form-input bg-white text-slate-900 border-slate-300"
                                name="foto_ktp">
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Foto Rumah</label>
                            <div id="current_foto_rumah" class="mb-2 border rounded-xl overflow-hidden"></div>
                            <input type="file" class="form-input bg-white text-slate-900 border-slate-300"
                                name="foto_rumah">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                    onclick="closeCustomModal('editModal')">
                    <i class="fa-solid fa-xmark"></i> Tutup
                </button>
                <button type="submit" class="btn-save !flex !items-center !justify-center gap-2">
                    <i class="fa-solid fa-rotate"></i> Update Data
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Detail Modal --}}
<div class="modal-overlay" id="detailModal">
    <div class="modal-box max-w-3xl bg-white border border-slate-300 shadow-2xl">
        <!-- Header -->
        <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                    <i class="fa-solid fa-address-card"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-base font-bold text-slate-900 m-0">Detail Informasi Pelanggan</h3>
                        <span id="detail_status_badge" class="badge badge-published text-[10px] py-0.5 px-2">AKTIF</span>
                    </div>
                    <p class="text-xs text-slate-500 m-0 mt-0.5">
                        Tgl PO: <span id="detail_tgl" class="font-medium text-slate-700">-</span>
                        <span class="mx-1 text-slate-300">•</span>
                        Sales: <span id="detail_sales" class="font-medium text-slate-700">-</span>
                    </p>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeCustomModal('detailModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="modal-body p-5 bg-white space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                <!-- Left Column: Data Lengkap (7 Cols) -->
                <div class="lg:col-span-7 space-y-3">
                    <!-- Section: Biodata & Kontak -->
                    <div class="border border-slate-200 rounded-xl p-3.5 bg-white">
                        <div class="flex items-center gap-2 mb-2.5 pb-2 border-b border-slate-100">
                            <i class="fa-regular fa-user text-xs text-slate-400"></i>
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Identitas & Kontak</span>
                        </div>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between items-start gap-2">
                                <span class="text-slate-400 shrink-0">Nama Lengkap:</span>
                                <span id="detail_nama_lengkap" class="font-bold text-slate-900 text-right"></span>
                            </div>
                            <div class="flex justify-between items-start gap-2">
                                <span class="text-slate-400 shrink-0">NIK / Identitas:</span>
                                <span id="detail_identitas" class="font-mono font-medium text-slate-800 text-right"></span>
                            </div>
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-slate-400 shrink-0">WhatsApp:</span>
                                <div class="flex items-center gap-1.5 justify-end">
                                    <span id="detail_wa" class="font-mono font-medium text-slate-800"></span>
                                    <a id="detail_wa_btn" href="#" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-1.5 py-0.5 rounded transition">
                                        <i class="fa-brands fa-whatsapp"></i> Chat
                                    </a>
                                </div>
                            </div>
                            <div class="flex justify-between items-start gap-2">
                                <span class="text-slate-400 shrink-0">Email:</span>
                                <span id="detail_email" class="text-slate-700 text-right truncate max-w-[200px]"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Paket Layanan -->
                    <div class="border border-slate-200 rounded-xl p-3.5 bg-slate-50/60">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">Paket Internet</span>
                                <span id="detail_paket" class="text-sm font-bold text-slate-800"></span>
                            </div>
                            <span id="detail_paket_harga" class="text-xs font-bold text-slate-700 font-mono bg-white px-2.5 py-1 rounded-md border border-slate-200 shadow-sm"></span>
                        </div>
                    </div>

                    <!-- Section: Alamat & Lokasi -->
                    <div class="border border-slate-200 rounded-xl p-3.5 bg-white space-y-2.5">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                            <i class="fa-regular fa-map text-xs text-slate-400"></i>
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Lokasi Pemasangan</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase block mb-1">Alamat Lengkap:</span>
                            <p id="detail_alamat" class="text-xs text-slate-700 leading-relaxed m-0 bg-slate-50 p-2 rounded-lg border border-slate-200"></p>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <div>
                                <span class="text-[10px] font-semibold text-slate-400 uppercase block">Titik Koordinat:</span>
                                <span id="detail_koordinat" class="text-xs font-mono font-medium text-slate-700"></span>
                            </div>
                            <a id="detail_maps_link" href="#" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 hover:text-sky-600 bg-white hover:bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-lg transition shadow-sm">
                                <i class="fa-solid fa-location-arrow text-[10px] text-sky-500"></i> Buka Maps
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Lampiran Dokumen (5 Cols) -->
                <div class="lg:col-span-5">
                    <div class="border border-slate-200 rounded-xl p-3.5 bg-slate-50/60 h-full flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-2.5 pb-2 border-b border-slate-200">
                                <i class="fa-regular fa-file-image text-xs text-slate-400"></i>
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Lampiran Dokumen</span>
                            </div>

                            <div class="space-y-3">
                                <!-- Foto KTP -->
                                <div>
                                    <span class="text-[11px] font-semibold text-slate-600 block mb-1">Foto KTP / Identitas</span>
                                    <div class="w-full h-24 rounded-lg overflow-hidden border border-slate-200 bg-white flex items-center justify-center relative group">
                                        <img id="detail_foto_ktp" src="" class="w-full h-full object-cover hidden cursor-pointer transition hover:scale-105 duration-200" alt="Foto KTP" onclick="window.open(this.src, '_blank')">
                                        <div id="no_ktp_placeholder" class="text-slate-300 text-xs text-center flex flex-col items-center gap-1">
                                            <i class="fa-regular fa-id-card text-xl text-slate-300"></i>
                                            <span class="text-[10px] text-slate-400">Tidak dilampirkan</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Foto Rumah -->
                                <div>
                                    <span class="text-[11px] font-semibold text-slate-600 block mb-1">Foto Rumah / Lokasi</span>
                                    <div class="w-full h-24 rounded-lg overflow-hidden border border-slate-200 bg-white flex items-center justify-center relative group">
                                        <img id="detail_foto_rumah" src="" class="w-full h-full object-cover hidden cursor-pointer transition hover:scale-105 duration-200" alt="Foto Rumah" onclick="window.open(this.src, '_blank')">
                                        <div id="no_rumah_placeholder" class="text-slate-300 text-xs text-center flex flex-col items-center gap-1">
                                            <i class="fa-regular fa-image text-xl text-slate-300"></i>
                                            <span class="text-[10px] text-slate-400">Tidak dilampirkan</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="text-[10px] text-slate-400 text-center m-0 mt-3 pt-2 border-t border-slate-200">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px] mr-1"></i> Klik gambar untuk ukuran penuh
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
            <span class="text-xs text-slate-400">Data pendaftaran calon pelanggan internet.</span>
            <button type="button" class="btn-cancel !h-9 !px-5 text-xs font-semibold" onclick="closeCustomModal('detailModal')">
                Tutup Detail
            </button>
        </div>
    </div>
</div>



{{-- Map Modal --}}
<div class="modal-overlay" id="mapModal">
    <div class="modal-box max-w-5xl bg-white border border-slate-300 shadow-2xl">
        <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
            <div class="modal-title text-slate-900 font-bold">Lokasi Pemasangan</div>
            <button type="button" class="modal-close" onclick="closeCustomModal('mapModal')"><i
                    class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body !p-0 overflow-hidden bg-white">
            <div id="modal-map" style="width: 100%;" class="h-[50vh] md:h-[500px]"></div>
        </div>
        <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
            <a id="gmaps-button" href="#" target="_blank"
                class="btn-save !shadow-lg !flex !items-center !justify-center gap-2">
                <i class="fa-solid fa-map-location-dot"></i> Lihat di Google Maps
            </a>
            <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                onclick="closeCustomModal('mapModal')">
                <i class="fa-solid fa-xmark"></i> Tutup
            </button>
        </div>
    </div>
</div>

{{-- Modal Unspek --}}
<div class="modal-overlay" id="unspekModal">
    <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
        <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm border border-amber-100 shrink-0">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 m-0">Unspek / Tangguhkan PO</h3>
                    <p class="text-xs text-slate-500 m-0 mt-0.5">Penangguhan pemasangan pre-order.</p>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeCustomModal('unspekModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="unspekForm" method="POST">
            @csrf
            <div class="modal-body p-5 space-y-3.5 bg-white">
                <div class="bg-amber-50 p-3.5 rounded-xl border border-amber-200">
                    <span class="text-[10px] font-bold text-amber-600 block uppercase">Pelanggan</span>
                    <p id="unspek_po_name" class="text-sm font-bold text-amber-900 m-0"></p>
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label text-slate-700 font-medium block mb-1 text-xs">
                        Alasan Unspek / Penangguhan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="keterangan_unspek" class="form-textarea !font-normal text-xs text-slate-800" required rows="3"
                        placeholder="Contoh: Tiang penuh, Jarak terlalu jauh, Pelanggan membatalkan..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2">
                <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                    onclick="closeCustomModal('unspekModal')">
                    Batal
                </button>
                <button type="submit" class="btn-primary-admin !bg-amber-600 hover:!bg-amber-700 !text-white !h-9 !px-5 text-xs font-semibold">
                    Simpan Unspek
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Buat PPPoE --}}
@if (auth()->user()->isAdmin())
    <div class="modal-overlay" id="createPppoeModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <!-- Header -->
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                        <i class="fa-solid fa-network-wired"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Buat Akun PPPoE</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Konfigurasi akun koneksi internet dan skema billing pelanggan.</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('createPppoeModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-tabs bg-white border-b border-slate-200 px-6">
                <button type="button" class="modal-tab active" data-tab="pppoe-tab-akun">
                    <i class="fa-regular fa-user text-xs"></i> Akun PPPoE
                </button>
                <button type="button" class="modal-tab" data-tab="pppoe-tab-pembayaran">
                    <i class="fa-regular fa-credit-card text-xs"></i> Billing & Pembayaran
                </button>
            </div>

            <form id="createPppoeForm" action="{{ route('pppoe_accounts.store') }}" method="POST">
                @csrf
                <input type="hidden" name="pre_order_id" id="pppoe_pre_order_id">

                <div class="modal-body p-5 bg-white space-y-4">
                    <!-- Tab Akun PPPoE -->
                    <div class="tab-panel active" id="pppoe-tab-akun">
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 mb-4 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Calon Pelanggan</span>
                                <span id="create_pppoe_po_nama_akun" class="text-sm font-bold text-slate-800 truncate block"></span>
                            </div>
                            <span id="create_pppoe_po_alamat_akun" class="text-xs text-slate-500 max-w-[260px] text-right truncate shrink-0"></span>
                        </div>

                        <div class="form-grid-2 mb-3.5">
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Username <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                                    name="username" placeholder="pengguna@erlangga.net" required>
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Password <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                                    name="password" placeholder="123" required>
                            </div>
                        </div>
                        <div class="form-group mb-3.5">
                            <label class="form-label text-slate-700 font-medium text-xs">Profile / Paket <span
                                    class="text-rose-500">*</span></label>
                            <select class="form-input-custom !font-normal w-full bg-white text-slate-800 border-slate-300"
                                id="create_pppoe_paket_id" name="paket_id" required>
                                @foreach ($pakets as $paket)
                                    <option value="{{ $paket->id }}">{{ $paket->nama_paket }} - Rp
                                        {{ number_format($paket->harga, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Router / NAS</label>
                                <select class="form-input-custom !font-normal w-full bg-white text-slate-800 border-slate-300" name="router">
                                    <option value="All">Semua Router (ALL)</option>
                                    @if(isset($routers))
                                        @foreach ($routers as $r)
                                            <option value="{{ $r->shortname }}">{{ $r->shortname }} ({{ $r->nasname }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">IP Address (Opsional)</label>
                                <input type="text" class="form-input bg-white text-slate-900 border-slate-300 font-mono"
                                    name="ip_address" placeholder="192.168.x.x">
                            </div>
                        </div>
                    </div>

                    <!-- Tab Pembayaran -->
                    <div class="tab-panel" id="pppoe-tab-pembayaran">
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 mb-4 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Calon Pelanggan</span>
                                <span id="create_pppoe_po_nama_pembayaran" class="text-sm font-bold text-slate-800 truncate block"></span>
                            </div>
                            <span id="create_pppoe_po_alamat_pembayaran" class="text-xs text-slate-500 max-w-[260px] text-right truncate shrink-0"></span>
                        </div>

                        <div class="form-grid-2 mb-3.5">
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Tipe Pembayaran</label>
                                <select class="form-input-custom !font-normal w-full bg-white text-slate-800 border-slate-300"
                                    name="tipe_pembayaran">
                                    <option value="postpaid">Postpaid (Pasca Bayar)</option>
                                    <option value="prepaid">Prepaid (Pra Bayar)</option>
                                </select>
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Tanggal Aktif</label>
                                <input type="date" class="form-input bg-white text-slate-900 border-slate-300"
                                    name="tanggal_aktif" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="form-grid-2 mb-3.5">
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Periode Billing</label>
                                <select class="form-input-custom !font-normal w-full bg-white text-slate-800 border-slate-300"
                                    name="periode_billing">
                                    <option value="fixed">Fixed (Setiap Awal Bulan)</option>
                                    <option value="renewal">Renewal (Sesuai Tgl Aktif)</option>
                                </select>
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Status Invoice</label>
                                <select class="form-input-custom !font-normal w-full bg-white text-slate-800 border-slate-300"
                                    name="status_invoice">
                                    <option value="belum_bayar">Belum Bayar (Unpaid)</option>
                                    <option value="terbayar">Terbayar (Paid)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-grid-2 mb-3.5">
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Pajak (Rp)</label>
                                <input type="number" class="form-input bg-white text-slate-900 border-slate-300 font-mono"
                                    name="pajak" value="0">
                            </div>
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs">Diskon (Rp)</label>
                                <input type="number" class="form-input bg-white text-slate-900 border-slate-300 font-mono"
                                    name="diskon" value="0">
                            </div>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 mt-2">
                            <label class="flex items-center gap-2.5 cursor-pointer m-0">
                                <input type="checkbox" name="generate_invoice" checked
                                    class="w-4 h-4 text-sky-600 border-slate-300 rounded focus:ring-sky-500">
                                <div>
                                    <span class="text-xs font-semibold text-slate-800 block">Generate Invoice Otomatis</span>
                                    <span class="text-[11px] text-slate-500 block">Buat tagihan faktur pertama langsung saat akun PPPoE disimpan.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                        onclick="closeCustomModal('createPppoeModal')">
                        Batal
                    </button>
                    <button type="submit"
                        class="btn-primary-admin !h-9 !px-5 text-xs font-semibold">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
