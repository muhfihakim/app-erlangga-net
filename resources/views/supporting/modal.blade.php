<div class="modal-overlay" id="supportingModal">
    <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
        <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
            <div class="modal-title">
                <i class="fa-solid fa-handshake-angle text-primary"></i>
                <span class="text-slate-900 font-bold" id="modalTitle">Tambah Perbantuan</span>
            </div>
            <button class="modal-close" onclick="closeCustomModal('supportingModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="modalForm" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            <input type="hidden" name="_method" id="modalMethod" value="POST">
            
            <div class="modal-body bg-white">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Tipe Kegiatan <span class="text-rose-500">*</span></label>
                        <select name="activity_type" required class="form-input-custom w-full bg-white text-slate-900 border-slate-300">
                            <option value="PO">PO</option>
                            <option value="Pemeliharaan">Pemeliharaan</option>
                            <option value="Buat ODP">Buat ODP</option>
                            <option value="Aktivasi Jalur">Aktivasi Jalur</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Tanggal <span class="text-rose-500">*</span></label>
                        <input type="date" name="activity_date" required class="form-input bg-white text-slate-900 border-slate-300">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Teknisi Utama <span class="text-rose-500">*</span></label>
                    <select name="main_technician_id" required class="form-input-custom w-full bg-white text-slate-900 border-slate-300">
                        <option value="">-- Pilih Teknisi --</option>
                        @foreach($technicians as $t)
                            <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Partner (Opsional)</label>
                    <div class="partner-card-scroll">
                        @foreach($technicians as $t)
                            <label class="partner-item" for="partner_{{ $t->id }}">
                                <input type="checkbox" name="partner_ids[]" value="{{ $t->id }}" id="partner_{{ $t->id }}" class="partner-check w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                <span class="text-sm font-600 text-slate-700">{{ $t->nama_lengkap }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Keterangan</label>
                    <textarea name="description" rows="3" class="form-textarea bg-white text-slate-900 border-slate-300" placeholder="Detail kegiatan..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Foto Kegiatan (Opsional)</label>
                    <input type="file" name="photo" accept="image/*" class="form-input bg-white text-slate-900 border-slate-300">
                </div>
            </div>

            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" onclick="closeCustomModal('supportingModal')">
                    <i class="fa-solid fa-xmark"></i> Batal
                </button>
                <button type="submit" class="btn-save !flex !items-center !justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>
