 {{-- Modal Export Log --}}
 <div class="modal-overlay" id="exportLogModal">
     <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
         <form action="{{ route('activitylog.export') }}" method="GET" target="_blank">
             <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                 <div class="modal-title">
                     <i class="fa-solid fa-file-pdf text-rose-500"></i>
                     <span class="text-slate-900 font-bold text-[17px]">Export Log ke PDF</span>
                 </div>
                 <button type="button" class="modal-close" onclick="closeCustomModal('exportLogModal')">
                     <i class="fa-solid fa-xmark"></i>
                 </button>
             </div>
             <div class="modal-body p-8 space-y-6 bg-white">
                 <div class="bg-rose-50 p-4 rounded-2xl border border-rose-100 mb-6">
                    <p class="text-[11px] font-bold text-rose-700 leading-relaxed">
                        <i class="fa-solid fa-circle-info mr-1"></i> Pilih rentang tanggal untuk mengunduh riwayat aktivitas dalam format dokumen PDF.
                    </p>
                 </div>
                 <div class="space-y-4">
                     <div class="form-group">
                         <label for="start_date" class="form-label !text-slate-800 !font-bold">Dari Tanggal</label>
                         <input type="date" name="start_date" id="start_date" class="form-input" required>
                     </div>
                     <div class="form-group">
                         <label for="end_date" class="form-label !text-slate-800 !font-bold">Sampai Tanggal</label>
                         <input type="date" name="end_date" id="end_date" class="form-input" required>
                     </div>
                 </div>
             </div>
             <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                 <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" onclick="closeCustomModal('exportLogModal')">
                     <i class="fa-solid fa-xmark"></i> Batal
                 </button>
                 <button type="submit" class="btn-save !flex !items-center !justify-center gap-2 !bg-rose-600">
                     <i class="fa-solid fa-file-export"></i> Unduh PDF
                 </button>
             </div>
         </form>
     </div>
 </div>
