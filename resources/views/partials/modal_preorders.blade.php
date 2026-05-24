{{-- Modal Tambah Pre-Order --}}
@if (auth()->user()->jabatan === 'Sales' || auth()->user()->jabatan === 'Administrator')
    <div class="modal-overlay" id="addPreOrderModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-user-plus text-primary"></i>
                    <span class="text-slate-900 font-bold">Tambah Pre-Order Baru</span>
                </div>
                <button class="modal-close" onclick="closeCustomModal('addPreOrderModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-tabs bg-white border-b border-slate-200">
                <button class="modal-tab active" data-tab="add-tab-biodata">
                    <i class="fa-solid fa-user"></i> Biodata
                </button>
                <button class="modal-tab" data-tab="add-tab-paket">
                    <i class="fa-solid fa-box-archive"></i> Paket & Lokasi
                </button>
                <button class="modal-tab" data-tab="add-tab-media">
                    <i class="fa-solid fa-camera"></i> Foto / Media
                </button>
            </div>

            <form action="{{ route('pre_orders.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id_sales" value="{{ auth()->user()->id }}">
                <input type="hidden" name="nama_sales" value="{{ auth()->user()->nama_lengkap }}">

                <div class="modal-body bg-white">
                    <!-- Tab Biodata -->
                    <div class="tab-panel active" id="add-tab-biodata">
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Nama Lengkap <span
                                    class="text-rose-500">*</span></label>
                            <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                                name="nama_lengkap" placeholder="Nama lengkap calon pelanggan" required>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">No. Identitas (NIK)</label>
                                <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                                    name="no_identitas" placeholder="3201..." required>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">No. WhatsApp <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                                    name="no_whatsapp" placeholder="0812..." required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Alamat Email</label>
                            <input type="email" class="form-input bg-white text-slate-900 border-slate-300"
                                name="alamat_email" placeholder="nama@email.com">
                        </div>
                    </div>

                    <!-- Tab Paket & Lokasi -->
                    <div class="tab-panel" id="add-tab-paket">
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Pilih Paket Internet <span
                                    class="text-rose-500">*</span></label>
                            <select class="form-select bg-white text-slate-900 border-slate-300" name="paket_internet"
                                required>
                                <option value="">-- Pilih Paket --</option>
                                <option value="Bronze 10Mbps - 112K/Bulan">Bronze 10Mbps - 112K/Bulan</option>
                                <option value="Silver 20Mbps - 179K/Bulan">Silver 20Mbps - 179K/Bulan</option>
                                <option value="Gold 30Mbps - 239K/Bulan">Gold 30Mbps - 239K/Bulan</option>
                                <option value="Platinum 50Mbps - 279K/Bulan">Platinum 50Mbps - 279K/Bulan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Alamat Lengkap Pemasangan <span
                                    class="text-rose-500">*</span></label>
                            <textarea class="form-textarea bg-white text-slate-900 border-slate-300" name="alamat_lengkap"
                                placeholder="Jl. Raya No. 123..." required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Titik Koordinat</label>
                            <div class="flex gap-2">
                                <input type="text" class="form-input bg-slate-50 text-slate-900 border-slate-300"
                                    id="titik_koordinat" name="titik_koordinat" placeholder="Koordinat..." readonly>
                                <button type="button"
                                    class="btn-primary-admin !shadow-none !flex !items-center !justify-center"
                                    id="get-location-btn">
                                    <i class="fa-solid fa-location-crosshairs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Media -->
                    <div class="tab-panel" id="add-tab-media">
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Foto KTP</label>
                            <input type="file" class="form-input bg-white text-slate-900 border-slate-300"
                                name="foto_ktp" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Foto Rumah / Lokasi</label>
                            <input type="file" class="form-input bg-white text-slate-900 border-slate-300"
                                name="foto_rumah" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-white rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        onclick="closeCustomModal('addPreOrderModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-save !flex !items-center !justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Data
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
            <button class="modal-close" onclick="closeCustomModal('editModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-tabs bg-white border-b border-slate-200">
            <button class="modal-tab active" data-tab="edit-tab-biodata">Biodata</button>
            <button class="modal-tab" data-tab="edit-tab-paket">Paket & Lokasi</button>
            <button class="modal-tab" data-tab="edit-tab-media">Foto</button>
        </div>

        <form id="editForm" method="POST" enctype="multipart/form-data">
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
                        <select class="form-select bg-white text-slate-900 border-slate-300" id="edit_paket_internet"
                            name="paket_internet" required>
                            <option value="Bronze 10Mbps - 112K/Bulan">Bronze 10Mbps - 112K/Bulan</option>
                            <option value="Silver 20Mbps - 179K/Bulan">Silver 20Mbps - 179K/Bulan</option>
                            <option value="Gold 30Mbps - 239K/Bulan">Gold 30Mbps - 239K/Bulan</option>
                            <option value="Platinum 50Mbps - 279K/Bulan">Platinum 50Mbps - 279K/Bulan</option>
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
                            <input type="text" class="form-input bg-slate-50 text-slate-900 border-slate-300"
                                id="edit_titik_koordinat" name="titik_koordinat" readonly>
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
            <div class="modal-footer border-t border-slate-200 bg-white rounded-b-2xl">
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
    <div class="modal-box max-w-4xl bg-white border border-slate-300 shadow-2xl">
        <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
            <div class="modal-title">
                <i class="fa-solid fa-circle-info text-primary"></i>
                <span class="text-slate-900 font-bold">Detail Informasi Pelanggan</span>
            </div>
            <button class="modal-close" onclick="closeCustomModal('detailModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body !p-0 bg-white">
            <div class="grid grid-cols-1 lg:grid-cols-5 h-full">
                <!-- Left Side: Data Pelanggan -->
                <div class="lg:col-span-3 p-8 space-y-8 bg-white">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Biodata -->
                        <div class="space-y-4">
                            <h6
                                class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] border-l-4 border-primary pl-3">
                                Biodata Inti</h6>
                            <div class="space-y-4">
                                <div>
                                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Nomor Identitas
                                        (NIK)</span>
                                    <p id="detail_identitas"
                                        class="text-xl font-black text-slate-900 leading-none mt-1"></p>
                                </div>
                                <div>
                                    <span class="text-[11px] font-bold text-slate-400 block uppercase">Alamat
                                        Email</span>
                                    <p id="detail_email" class="text-base font-black text-primary truncate"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Layanan -->
                        <div class="space-y-4">
                            <h6
                                class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] border-l-4 border-indigo-500 pl-3">
                                Paket Layanan</h6>
                            <div class="bg-indigo-50 p-4 rounded-2xl border border-indigo-200">
                                <span class="text-[10px] font-black text-indigo-400 block uppercase mb-1">Paket
                                    Internet</span>
                                <p id="detail_paket" class="text-lg font-black text-indigo-800 leading-tight"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Lokasi -->
                    <div class="space-y-4">
                        <h6
                            class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] border-l-4 border-emerald-500 pl-3">
                            Informasi Lokasi</h6>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                                    <span class="text-[10px] font-bold text-slate-500 block uppercase mb-1">Alamat
                                        Pemasangan</span>
                                    <p id="detail_alamat" class="text-sm font-bold text-slate-800 leading-relaxed">
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-200">
                                    <span class="text-[10px] font-bold text-emerald-600 block uppercase mb-1">Koordinat
                                        GPS</span>
                                    <p id="detail_koordinat" class="text-xs font-black text-emerald-800 break-all">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Lampiran -->
                <div class="lg:col-span-2 p-8 bg-slate-50 border-l border-slate-200 space-y-6">
                    <h6 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Lampiran Dokumen</h6>

                    <div class="space-y-6">
                        <!-- KTP -->
                        <div class="group relative text-center">
                            <span class="text-[11px] font-black text-slate-600 block mb-2 uppercase text-center">Foto
                                KTP</span>
                            <div
                                class="aspect-video rounded-2xl overflow-hidden border-2 border-white shadow-md bg-white flex items-center justify-center transition-transform hover:scale-[1.02] duration-300">
                                <img id="detail_foto_ktp" src=""
                                    class="w-full h-full object-cover hidden cursor-pointer" alt="KTP"
                                    onclick="window.open(this.src, '_blank')">
                                <div id="no_ktp_placeholder" class="text-slate-300 text-xs text-center">
                                    <i class="fa-solid fa-image-portrait text-3xl mb-1 block"></i>
                                    Tidak ada
                                </div>
                            </div>
                        </div>

                        <!-- Rumah -->
                        <div class="group relative text-center">
                            <span class="text-[11px] font-black text-slate-600 block mb-2 uppercase text-center">Foto
                                Rumah</span>
                            <div
                                class="aspect-video rounded-2xl overflow-hidden border-2 border-white shadow-md bg-white flex items-center justify-center transition-transform hover:scale-[1.02] duration-300">
                                <img id="detail_foto_rumah" src=""
                                    class="w-full h-full object-cover hidden cursor-pointer" alt="Rumah"
                                    onclick="window.open(this.src, '_blank')">
                                <div id="no_rumah_placeholder" class="text-slate-300 text-xs text-center">
                                    <i class="fa-solid fa-house-chimney text-3xl mb-1 block"></i>
                                    Tidak ada
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-primary/5 p-4 rounded-2xl border border-primary/10">
                        <p class="text-[10px] text-slate-500 leading-relaxed font-medium italic text-center">
                            * Klik gambar untuk melihat ukuran penuh.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer !bg-white border-t border-slate-200 rounded-b-2xl">
            <button type="button"
                class="btn-cancel w-full !py-3.5 !text-sm !rounded-xl !bg-slate-100 !text-slate-600 hover:!bg-slate-200 !flex !items-center !justify-center gap-2 transition-colors"
                onclick="closeCustomModal('detailModal')">
                <i class="fa-solid fa-xmark"></i> Tutup Detail
            </button>
        </div>
    </div>
</div>



{{-- Map Modal --}}
<div class="modal-overlay" id="mapModal">
    <div class="modal-box max-w-5xl bg-white border border-slate-300 shadow-2xl">
        <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
            <div class="modal-title text-slate-900 font-bold">Lokasi Pemasangan</div>
            <button class="modal-close" onclick="closeCustomModal('mapModal')"><i
                    class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body !p-0 overflow-hidden bg-white">
            <div id="modal-map" style="width: 100%;" class="h-[50vh] md:h-[500px]"></div>
        </div>
        <div class="modal-footer border-t border-slate-200 bg-white rounded-b-2xl">
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

{{-- Delegate Modal --}}
<div class="modal-overlay" id="delegateModal">
    <div class="modal-box bg-white border border-slate-300 shadow-2xl">
        <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
            <div class="modal-title text-slate-900 font-bold">
                <i class="fa-solid fa-user-gear text-sky-500"></i>
                <span>Delegasi Tugas</span>
            </div>
            <button class="modal-close" onclick="closeCustomModal('delegateModal')"><i
                    class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="delegateForm" method="POST">
            @csrf
            <div class="modal-body bg-white">
                <p class="text-sm text-slate-600 mb-4 text-center">Tugaskan <strong id="delegate_po_name"
                        class="text-primary"></strong> kepada teknisi.</p>
                <div class="form-group">
                    <label class="form-label text-slate-800 font-bold">Pilih Teknisi</label>
                    <select class="form-select bg-white text-slate-900 border-slate-300" name="id_teknisi" required>
                        <option value="">-- Cari Teknisi --</option>
                        @foreach ($teknisiUsers as $teknisi)
                            <option value="{{ $teknisi->id }}">{{ $teknisi->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-white rounded-b-2xl">
                <button type="button" class="btn-cancel flex-1 !flex !items-center !justify-center gap-2"
                    onclick="closeCustomModal('delegateModal')">
                    <i class="fa-solid fa-xmark"></i> Batal
                </button>
                <button type="submit" class="btn-save flex-1 !flex !items-center !justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Tugaskan Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Reset Modal --}}
<div class="modal-overlay" id="resetModal">
    <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
        <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
            <div class="modal-title text-slate-900 font-bold">Reset Penugasan</div>
            <button class="modal-close" onclick="closeCustomModal('resetModal')"><i
                    class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body p-6 text-center bg-white">
            <p class="text-slate-700 font-bold text-sm">Hapus penugasan teknisi untuk:</p>
            <h5 class="text-lg font-black text-rose-500 my-2" id="reset_po_name"></h5>
            <p class="text-xs text-slate-500 italic">PO akan kembali menjadi "Tugas Umum".</p>
        </div>
        <div class="modal-footer border-t border-slate-200 bg-white rounded-b-2xl">
            <form id="resetForm" method="POST" class="w-full flex gap-3">
                @csrf
                <button type="button" class="btn-cancel flex-1 !flex !items-center !justify-center gap-2"
                    onclick="closeCustomModal('resetModal')">
                    <i class="fa-solid fa-circle-xmark"></i> Batal
                </button>
                <button type="submit"
                    class="btn-save !bg-amber-500 flex-1 !flex !items-center !justify-center gap-2">
                    <i class="fa-solid fa-arrow-rotate-left"></i> Reset Tugas
                </button>
            </form>
        </div>
    </div>
</div>
