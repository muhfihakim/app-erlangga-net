<div id="supportingModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-900 text-slate-900" id="modalTitle">Tambah Perbantuan</h3>
                    <button onclick="closeModal()" class="text-slate-400 hover:text-slate-500">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                <form id="modalForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="modalMethod" value="POST">
                    
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                        <div>
                            <label class="form-label text-slate-800 font-bold uppercase text-[11px] mb-2 block">Tipe Kegiatan</label>
                            <select name="activity_type" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-slate-900 outline-none">
                                <option value="PO">PO</option>
                                <option value="Pemeliharaan">Pemeliharaan</option>
                                <option value="Buat ODP">Buat ODP</option>
                                <option value="Aktivasi Jalur">Aktivasi Jalur</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-slate-800 font-bold uppercase text-[11px] mb-2 block">Tanggal</label>
                            <input type="date" name="activity_date" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-slate-900 outline-none">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-slate-800 font-bold uppercase text-[11px] mb-2 block">Teknisi Utama</label>
                        <select name="main_technician_id" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-slate-900 outline-none">
                            <option value="">Pilih Teknisi</option>
                            @foreach($technicians as $t)
                                <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-slate-800 font-bold uppercase text-[11px] mb-2 block">Partner (Opsional)</label>
                        <div class="partner-card-scroll">
                            @foreach($technicians as $t)
                                <label class="partner-item" for="partner_{{ $t->id }}">
                                    <input type="checkbox" name="partner_ids[]" value="{{ $t->id }}" id="partner_{{ $t->id }}" class="partner-check w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                    <span class="text-sm font-600 text-slate-700">{{ $t->nama_lengkap }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-slate-800 font-bold uppercase text-[11px] mb-2 block">Keterangan</label>
                        <textarea name="description" rows="3" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-slate-900 outline-none" placeholder="Detail kegiatan..."></textarea>
                    </div>

                    <div class="mb-6">
                        <label class="form-label text-slate-800 font-bold uppercase text-[11px] mb-2 block">Foto Kegiatan (Opsional)</label>
                        <input type="file" name="photo" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-700 file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-700 text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm font-700 text-white bg-slate-900 rounded-lg hover:bg-slate-800">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
