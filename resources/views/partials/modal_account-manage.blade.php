 <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="addUserModalLabel">Tambah Akun Baru</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <form id="addForm" method="POST" action="{{ route('accounts.store') }}">
                 @csrf
                 <div class="modal-body">
                     <div class="mb-3">
                         <label for="add_nama_lengkap" class="form-label">Nama Lengkap</label>
                         <input type="text" class="form-control" id="add_nama_lengkap" name="nama_lengkap"
                             placeholder="Masukkan Nama Lengkap" required autocomplete="off">
                     </div>
                     <div class="mb-3">
                         <label for="add_email" class="form-label">Alamat Email</label>
                         <input type="email" class="form-control" id="add_email" name="email"
                             placeholder="Masukkan Alamat Email" required autocomplete="off">
                     </div>
                     <div class="mb-3">
                         <label for="add_password" class="form-label">Password</label>
                         <input type="password" class="form-control" id="add_password" name="password"
                             placeholder="Masukkan Password" required autocomplete="off">
                     </div>
                     <div class="mb-3">
                         <label for="add_nik" class="form-label">NIK</label>
                         <input type="text" class="form-control" id="add_nik" name="nik"
                             placeholder="Masukkan NIK" autocomplete="off">
                     </div>
                     <div class="mb-3">
                         <label for="add_alamat" class="form-label">Alamat Lengkap</label>
                         <textarea class="form-control" id="add_alamat" name="alamat" placeholder="Masukkan Alamat Lengkap" autocomplete="off"></textarea>
                     </div>
                     <div class="mb-3">
                         <label for="add_jenis_kelamin" class="form-label">Jenis Kelamin</label>
                         <select class="form-control" id="add_jenis_kelamin" name="jenis_kelamin">
                             <option value="">Pilih Jenis Kelamin</option>
                             <option value="Laki-laki">Laki-laki</option>
                             <option value="Perempuan">Perempuan</option>
                         </select>
                     </div>
                     <div class="mb-3">
                         <label for="add_tanggal_lahir" class="form-label">Tanggal Lahir</label>
                         <input type="date" class="form-control" id="add_tanggal_lahir" name="tanggal_lahir"
                             autocomplete="off">
                     </div>
                     <div class="mb-3">
                         <label for="add_jabatan" class="form-label">Jabatan</label>
                         <select class="form-control" id="add_jabatan" name="jabatan">
                             <option value="">Pilih Jabatan</option>
                             <option value="Administrator">Administrator</option>
                             <option value="Sales">Sales</option>
                             <option value="Teknisi">Teknisi</option>
                         </select>
                     </div>
                 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                     <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                 </div>
             </form>
         </div>
     </div>
 </div>
 <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="editModalLabel">Edit Akun Pengguna</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <form id="editForm" method="POST" action="">
                 @csrf
                 @method('PUT')
                 <div class="modal-body">
                     <div class="mb-3">
                         <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                         <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                             placeholder="Masukkan Nama Lengkap" required autocomplete="off">
                     </div>
                     <div class="mb-3">
                         <label for="email" class="form-label">Alamat Email</label>
                         <input type="email" class="form-control" id="email" name="email"
                             placeholder="Masukkan Alamat Email" required autocomplete="off">
                     </div>
                     <div class="mb-3">
                         <label for="password" class="form-label">Password Baru (Opsional)</label>
                         <input type="password" class="form-control" id="password" name="password"
                             placeholder="Kosongkan jika tidak ingin mengganti password" autocomplete="off">
                     </div>
                     <div class="mb-3">
                         <label for="nik" class="form-label">NIK</label>
                         <input type="text" class="form-control" id="nik" name="nik"
                             placeholder="Masukkan NIK" autocomplete="off">
                     </div>
                     <div class="mb-3">
                         <label for="alamat" class="form-label">Alamat</label>
                         <textarea class="form-control" id="alamat" name="alamat" placeholder="Masukkan Alamat Lengkap"
                             autocomplete="off"></textarea>
                     </div>
                     <div class="mb-3">
                         <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                         <select class="form-control" id="jenis_kelamin" name="jenis_kelamin">
                             <option value="">Pilih Jenis Kelamin</option>
                             <option value="Laki-laki">Laki-laki</option>
                             <option value="Perempuan">Perempuan</option>
                         </select>
                     </div>
                     <div class="mb-3">
                         <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                         <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                             autocomplete="off">
                     </div>
                     <div class="mb-3">
                         <label for="jabatan" class="form-label">Jabatan</label>
                         <select class="form-control" id="jabatan" name="jabatan">
                             <option value="Administrator">Administrator</option>
                             <option value="Sales">Sales</option>
                             <option value="Teknisi">Teknisi</option>
                         </select>
                     </div>
                 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                     <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                 </div>
             </form>
         </div>
     </div>
 </div>
 <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 Apakah Anda yakin ingin menghapus akun ini?
             </div>
             <div class="modal-footer">
                 <form id="deleteForm" method="POST" action="">
                     @csrf
                     @method('DELETE')
                     <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                     <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                 </form>
             </div>
         </div>
     </div>
 </div>
 <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title">QR Code 2FA</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body text-center" id="qrModalBody">
                 <div class="spinner-border text-primary" role="status">
                     <span class="visually-hidden">Loading...</span>
                 </div>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
             </div>
         </div>
     </div>
 </div>
 <div class="modal fade" id="reset2FAModal" tabindex="-1" aria-labelledby="reset2FAModalLabel" aria-hidden="true">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title">Reset 2FA</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 Apakah Anda yakin ingin mereset Google Authenticator untuk akun ini?
             </div>
             <div class="modal-footer">
                 <form id="reset2FAForm" method="POST">
                     @csrf
                     <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                     <button type="submit" class="btn btn-danger btn-sm">Ya, Reset</button>
                 </form>
             </div>
         </div>
     </div>
 </div>
