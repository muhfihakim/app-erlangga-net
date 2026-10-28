 {{-- Modal Tambah Akun --}}
 <div class="modal-overlay" id="addUserModal">
     <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
         <form id="addForm" method="POST" action="{{ route('accounts.store') }}" novalidate>
             @csrf
             <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                 <div class="modal-title">
                     <i class="fa-solid fa-user-plus text-primary"></i>
                     <span class="text-slate-900 font-bold text-[17px]">Tambah Akun Baru</span>
                 </div>
                 <button type="button" class="modal-close" onclick="closeCustomModal('addUserModal')">
                     <i class="fa-solid fa-xmark"></i>
                 </button>
             </div>
             <div class="modal-body p-8 space-y-6 bg-white">
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">Nama Lengkap <span class="text-rose-500">*</span></label>
                         <input type="text" class="form-input" name="nama_lengkap" placeholder="Nama Lengkap" required autocomplete="off">
                     </div>
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">Email <span class="text-rose-500">*</span></label>
                         <input type="email" class="form-input" name="email" placeholder="email@example.com" required autocomplete="off">
                     </div>
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">Nomor WhatsApp</label>
                         <input type="text" class="form-input" name="no_whatsapp" placeholder="081234567890" autocomplete="off">
                     </div>
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">Password <span class="text-rose-500">*</span></label>
                         <input type="password" class="form-input" name="password" placeholder="Minimal 8 karakter" required autocomplete="off">
                     </div>
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">NIK</label>
                         <input type="text" class="form-input" name="nik" placeholder="Nomor Induk Kependudukan" autocomplete="off">
                     </div>
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">Jenis Kelamin</label>
                         <select class="form-input-custom w-full bg-white text-slate-900 border-slate-300" name="jenis_kelamin">
                             <option value="">Pilih Jenis Kelamin</option>
                             <option value="Laki-laki">Laki-laki</option>
                             <option value="Perempuan">Perempuan</option>
                         </select>
                     </div>
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">Tanggal Lahir</label>
                         <input type="date" class="form-input" name="tanggal_lahir" autocomplete="off">
                     </div>
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">Jabatan <span class="text-rose-500">*</span></label>
                         <select class="form-input-custom w-full bg-white text-slate-900 border-slate-300" name="jabatan" required>
                             <option value="">Pilih Jabatan</option>
                             <option value="Super Admin">Super Admin</option>
                             <option value="Administrator">Administrator</option>
                             <option value="Web Developer">Web Developer</option>
                             <option value="Sales">Sales</option>
                             <option value="Teknisi">Teknisi</option>
                         </select>
                     </div>
                     <div class="form-group md:col-span-2 !mb-0">
                         <label class="form-label !text-slate-800 !font-bold">Alamat Lengkap</label>
                         <textarea class="form-textarea" name="alamat" placeholder="Alamat Lengkap Pengguna" rows="3" autocomplete="off"></textarea>
                     </div>
                 </div>
             </div>
             <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                 <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" onclick="closeCustomModal('addUserModal')">
                     <i class="fa-solid fa-xmark"></i> Batal
                 </button>
                 <button type="submit" class="btn-save !flex !items-center !justify-center gap-2">
                     <i class="fa-solid fa-floppy-disk"></i> Simpan Akun
                 </button>
             </div>
         </form>
     </div>
 </div>

 {{-- Modal Edit Akun --}}
 <div class="modal-overlay" id="editModal">
     <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
         <form id="editForm" method="POST" novalidate>
             @csrf
             @method('PUT')
             <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                 <div class="modal-title">
                     <i class="fa-solid fa-pen-to-square text-amber-500"></i>
                     <span class="text-slate-900 font-bold text-[17px]" id="edit_modal_title">Edit Akun Pengguna</span>
                 </div>
                 <button type="button" class="modal-close" onclick="closeCustomModal('editModal')">
                     <i class="fa-solid fa-xmark"></i>
                 </button>
             </div>
             <div class="modal-body p-8 space-y-6 bg-white">
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">Nama Lengkap <span class="text-rose-500">*</span></label>
                         <input type="text" class="form-input" id="edit_nama_lengkap" name="nama_lengkap" required autocomplete="off">
                     </div>
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">Email <span class="text-rose-500">*</span></label>
                         <input type="email" class="form-input" id="edit_email" name="email" required autocomplete="off">
                     </div>
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">Nomor WhatsApp</label>
                         <input type="text" class="form-input" id="edit_no_whatsapp" name="no_whatsapp" placeholder="081234567890" autocomplete="off">
                     </div>
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">Ganti Password (Opsional)</label>
                         <input type="password" class="form-input" name="password" placeholder="Kosongkan jika tidak ingin ganti" autocomplete="off">
                     </div>
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">NIK</label>
                         <input type="text" class="form-input" id="edit_nik" name="nik" autocomplete="off">
                     </div>
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">Jenis Kelamin</label>
                         <select class="form-input-custom w-full bg-white text-slate-900 border-slate-300" id="edit_jenis_kelamin" name="jenis_kelamin">
                             <option value="">Pilih Jenis Kelamin</option>
                             <option value="Laki-laki">Laki-laki</option>
                             <option value="Perempuan">Perempuan</option>
                         </select>
                     </div>
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">Tanggal Lahir</label>
                         <input type="date" class="form-input" id="edit_tanggal_lahir" name="tanggal_lahir" autocomplete="off">
                     </div>
                     <div class="form-group">
                         <label class="form-label !text-slate-800 !font-bold">Jabatan <span class="text-rose-500">*</span></label>
                         <select class="form-input-custom w-full bg-white text-slate-900 border-slate-300" id="edit_jabatan" name="jabatan" required>
                             <option value="Super Admin">Super Admin</option>
                             <option value="Administrator">Administrator</option>
                             <option value="Web Developer">Web Developer</option>
                             <option value="Sales">Sales</option>
                             <option value="Teknisi">Teknisi</option>
                         </select>
                     </div>
                     <div class="form-group md:col-span-2 !mb-0">
                         <label class="form-label !text-slate-800 !font-bold">Alamat Lengkap</label>
                         <textarea class="form-textarea" id="edit_alamat" name="alamat" rows="3" autocomplete="off"></textarea>
                     </div>
                 </div>
             </div>
             <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                 <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2" onclick="closeCustomModal('editModal')">
                     <i class="fa-solid fa-xmark"></i> Batal
                 </button>
                 <button type="submit" class="btn-save !flex !items-center !justify-center gap-2 !bg-amber-500">
                     <i class="fa-solid fa-check"></i> Perbarui Akun
                 </button>
             </div>
         </form>
     </div>
 </div>

 {{-- Modal Hapus --}}
 <div class="modal-overlay" id="deleteModal">
     <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
         <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
             <div class="flex items-center gap-3">
                 <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm border border-rose-100 shrink-0">
                     <i class="fa-solid fa-trash-can"></i>
                 </div>
                 <div>
                     <h3 class="text-base font-bold text-slate-900 m-0">Hapus Akun</h3>
                     <p class="text-xs text-slate-500 m-0 mt-0.5">Konfirmasi penghapusan pengguna.</p>
                 </div>
             </div>
             <button type="button" class="modal-close" onclick="closeCustomModal('deleteModal')">
                 <i class="fa-solid fa-xmark"></i>
             </button>
         </div>
         <div class="modal-body p-5 bg-white">
             <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 space-y-1">
                 <div class="font-bold flex items-center gap-1.5 text-rose-900">
                     <i class="fa-solid fa-triangle-exclamation"></i> Peringatan:
                 </div>
                 <p class="m-0 leading-relaxed text-slate-700">
                     Apakah Anda yakin ingin menghapus akun <strong class="text-rose-700 font-bold" id="delete_user_name"></strong>? Tindakan ini tidak dapat dibatalkan.
                 </p>
             </div>
         </div>
         <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2">
             <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('deleteModal')">Batal</button>
             <form id="deleteForm" method="POST" class="m-0">
                 @csrf
                 @method('DELETE')
                 <button type="submit" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !text-white !h-9 !px-5 text-xs font-semibold">Ya, Hapus</button>
             </form>
         </div>
     </div>
 </div>

 {{-- Modal QR Code 2FA --}}
 <div class="modal-overlay" id="qrModal">
     <div class="modal-box max-w-sm bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden text-center">
         <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
             <div class="modal-title">
                 <i class="fa-solid fa-qrcode text-primary"></i>
                 <span class="text-slate-900 font-bold text-base">QR Code 2FA</span>
             </div>
             <button type="button" class="modal-close" onclick="closeCustomModal('qrModal')">
                 <i class="fa-solid fa-xmark"></i>
             </button>
         </div>
         <div class="modal-body p-6" id="qrModalBody">
             <!-- Konten diisi via JS -->
         </div>
         <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex justify-end">
             <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('qrModal')">
                 Tutup
             </button>
         </div>
     </div>
 </div>

 {{-- Modal Reset 2FA --}}
 <div class="modal-overlay" id="reset2FAModal">
     <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
         <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
             <div class="flex items-center gap-3">
                 <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm border border-amber-100 shrink-0">
                     <i class="fa-solid fa-shield-halved"></i>
                 </div>
                 <div>
                     <h3 class="text-base font-bold text-slate-900 m-0">Reset 2FA</h3>
                     <p class="text-xs text-slate-500 m-0 mt-0.5">Setel ulang keamanan Google Authenticator.</p>
                 </div>
             </div>
             <button type="button" class="modal-close" onclick="closeCustomModal('reset2FAModal')">
                 <i class="fa-solid fa-xmark"></i>
             </button>
         </div>
         <div class="modal-body p-5 bg-white">
             <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-amber-900">
                    <i class="fa-solid fa-triangle-exclamation"></i> Konfirmasi:
                </div>
                <p class="m-0 leading-relaxed text-slate-700">
                    Setel ulang keamanan Google Authenticator untuk akun ini? Pengguna harus melakukan setup ulang pada saat login berikutnya.
                </p>
             </div>
         </div>
         <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2">
             <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('reset2FAModal')">Batal</button>
             <form id="reset2FAForm" method="POST" class="m-0">
                 @csrf
                 <button type="submit" class="btn-primary-admin !bg-amber-600 hover:!bg-amber-700 !text-white !h-9 !px-5 text-xs font-semibold">Ya, Reset</button>
             </form>
         </div>
     </div>
 </div>

 {{-- Modal Toggle Status --}}
 <div class="modal-overlay" id="toggleStatusModal">
     <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl rounded-2xl overflow-hidden">
         <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
             <div class="flex items-center gap-3">
                 <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm border border-sky-100 shrink-0">
                     <i class="fa-solid fa-power-off"></i>
                 </div>
                 <div>
                     <h3 class="text-base font-bold text-slate-900 m-0">Konfirmasi Status</h3>
                     <p class="text-xs text-slate-500 m-0 mt-0.5">Ubah status aktif akun pengguna.</p>
                 </div>
             </div>
             <button type="button" class="modal-close" onclick="closeCustomModal('toggleStatusModal')">
                 <i class="fa-solid fa-xmark"></i>
             </button>
         </div>
         <div class="modal-body p-5 bg-white">
             <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 space-y-1">
                 <p class="m-0 leading-relaxed text-slate-700 text-center font-medium">
                     <span id="toggle-status-text"></span> akun <strong class="text-sky-600 font-bold" id="toggle_user_name"></strong>?
                 </p>
             </div>
         </div>
         <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between gap-2">
             <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('toggleStatusModal')">Batal</button>
             <form id="toggleStatusForm" method="POST" class="m-0">
                 @csrf
                 <button type="submit" class="btn-primary-admin !h-9 !px-5 text-xs font-semibold" id="toggle-status-btn-submit">Ya, Lanjutkan</button>
             </form>
         </div>
     </div>
 </div>
     </div>
 </div>
