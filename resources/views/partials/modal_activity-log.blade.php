{{-- Modal Export Log --}}
<div class="modal-overlay" id="exportLogModal">
    <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
        <form action="{{ route('activitylog.export') }}" method="GET" target="_blank">
            <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 m-0">Export Log ke PDF</h3>
                        <p class="text-[11px] text-slate-500 font-normal m-0 mt-0.5">Pilih rentang tanggal riwayat aktivitas</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('exportLogModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-6 bg-white">
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 mb-3.5">
                    <p class="text-xs text-slate-600 m-0 leading-relaxed">
                        Pilih rentang tanggal untuk mengunduh arsip riwayat aktivitas sistem ke dalam dokumen PDF.
                    </p>
                </div>
                <div class="form-grid-2 mb-0">
                    <div class="form-group !mb-0">
                        <label for="start_date" class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Dari Tanggal <span class="text-rose-500">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" required value="{{ date('Y-m-01') }}">
                    </div>
                    <div class="form-group !mb-0">
                        <label for="end_date" class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Sampai Tanggal <span class="text-rose-500">*</span></label>
                        <input type="date" name="end_date" id="end_date" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2.5">
                <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('exportLogModal')">
                    Batal
                </button>
                <button type="submit" class="btn-primary-admin !h-9 !px-5 text-xs font-semibold">
                    Unduh PDF
                </button>
            </div>
        </form>
    </div>
</div>
