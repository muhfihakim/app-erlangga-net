<div class="modal-overlay" id="supportingModal">
    <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
        <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
            <div class="modal-title">
                <i class="fa-solid fa-handshake-angle text-primary"></i>
                <span class="text-slate-900 font-bold" id="modalTitle">Tambah Perbantuan</span>
            </div>
            <button type="button" class="modal-close" onclick="closeCustomModal('supportingModal')">
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
                    @if(auth()->user()->isAdmin())
                        <select name="main_technician_id" id="main_technician_id" required class="form-input-custom w-full bg-white text-slate-900 border-slate-300">
                            <option value="">-- Pilih Teknisi Utama --</option>
                            @foreach($technicians as $t)
                                <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="relative">
                            <input type="text" class="form-input bg-slate-50 text-slate-900 border-slate-300 cursor-not-allowed" value="{{ auth()->user()->nama_lengkap }}" readonly>
                            <input type="hidden" name="main_technician_id" value="{{ auth()->id() }}">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">* Otomatis berdasarkan akun Anda.</p>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Partner / Teknisi Tambahan (Opsional)</label>
                    <div class="partner-card-scroll">
                        <div class="grid grid-cols-1 gap-1">
                            @forelse($technicians as $t)
                                @if(auth()->user()->isAdmin() || $t->id != auth()->id())
                                    <div class="partner-item">
                                        <input class="partner-check w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary" 
                                            type="checkbox" name="partner_ids[]" value="{{ $t->id }}" id="partner_{{ $t->id }}">
                                        <label class="text-sm font-semibold text-slate-700 cursor-pointer w-full" for="partner_{{ $t->id }}">
                                            {{ $t->nama_lengkap }}
                                        </label>
                                    </div>
                                @endif
                            @empty
                                <div class="text-center py-4 text-slate-400 italic text-sm">Tidak ada teknisi lain.</div>
                            @endforelse
                        </div>
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
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
