{{-- Modal Tambah Pre-Order --}}
@if (auth()->user()->jabatan === 'Sales' || auth()->user()->jabatan === 'Administrator')
    <div class="modal fade" id="addPreOrderModal" tabindex="-1" aria-labelledby="addPreOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPreOrderModalLabel">Tambah Pre-Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pre_orders.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Field tersembunyi untuk Sales --}}
                    <input type="hidden" name="id_sales" value="{{ auth()->user()->id }}">
                    <input type="hidden" name="nama_sales" value="{{ auth()->user()->nama_lengkap }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="paket_internet" class="form-label">Paket Internet</label>
                            <select class="form-control" id="paket_internet" name="paket_internet" required>
                                <option value="">Pilih Paket Internet</option>
                                <option value="Bronze 10Mbps - 112K/Bulan">Bronze 10Mbps - 112K/Bulan</option>
                                <option value="Silver 20Mbps - 179K/Bulan">Silver 20Mbps - 179K/Bulan</option>
                                <option value="Gold 30Mbps - 239K/Bulan">Gold 30Mbps - 239K/Bulan</option>
                                <option value="Platinum 50Mbps - 279K/Bulan">Platinum 50Mbps - 279K/Bulan
                                </option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap"
                                placeholder="Masukkan Nama Lengkap" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="no_identitas" class="form-label">Nomor Identitas</label>
                            <input type="text" class="form-control" name="no_identitas"
                                placeholder="Masukkan No. Identitas KTP/SIM" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="no_whatsapp" class="form-label">Nomor WhatsApp</label>
                            <input type="text" class="form-control" name="no_whatsapp"
                                placeholder="Masukkan No. WhatsApp Aktif" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="alamat_email" class="form-label">Alamat Email</label>
                            <input type="email" class="form-control" name="alamat_email"
                                placeholder="Masukkan Alamat Email" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="alamat_lengkap" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" name="alamat_lengkap" placeholder="Masukkan Alamat Lengkap" required autocomplete="off"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="titik_koordinat" class="form-label">Titik Koordinat</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="titik_koordinat" name="titik_koordinat"
                                    placeholder="Titik Koordinat Pemasangan" required readonly>
                                <button type="button" class="btn btn-primary" id="get-location-btn">Ambil</button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="foto_ktp" class="form-label">Foto KTP</label>
                            <input type="file" class="form-control" name="foto_ktp">
                        </div>
                        <div class="mb-3">
                            <label for="foto_rumah" class="form-label">Foto Rumah</label>
                            <input type="file" class="form-control" name="foto_rumah">
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
@endif
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Data Pre-Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_paket_internet" class="form-label">Paket Internet</label>
                        <select class="form-control" id="edit_paket_internet" name="paket_internet" required>
                            <option value="">Pilih Paket Internet</option>
                            <option value="Bronze 10Mbps - 112K/Bulan">Bronze 10Mbps - 112K/Bulan</option>
                            <option value="Silver 20Mbps - 179K/Bulan">Silver 20Mbps - 179K/Bulan</option>
                            <option value="Gold 30Mbps - 239K/Bulan">Gold 30Mbps - 239K/Bulan</option>
                            <option value="Platinum 50Mbps - 279K/Bulan">Platinum 50Mbps - 279K/Bulan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="edit_nama_lengkap" name="nama_lengkap"
                            placeholder="Masukkan Nama Lengkap" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="edit_no_identitas" class="form-label">Nomor Identitas</label>
                        <input type="text" class="form-control" id="edit_no_identitas" name="no_identitas"
                            placeholder="Masukkan No. Identitas KTP/SIM" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="edit_no_whatsapp" class="form-label">Nomor WhatsApp</label>
                        <input type="text" class="form-control" id="edit_no_whatsapp" name="no_whatsapp"
                            placeholder="Masukkan No. WhatsApp Aktif" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="edit_alamat_email" class="form-label">Alamat Email</label>
                        <input type="email" class="form-control" id="edit_alamat_email" name="alamat_email"
                            placeholder="Masukkan Alamat Email" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="edit_alamat_lengkap" class="form-label">Alamat Lengkap</label>
                        <textarea class="form-control" id="edit_alamat_lengkap" name="alamat_lengkap" placeholder="Masukkan Alamat Lengkap"
                            required autocomplete="off"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_titik_koordinat" class="form-label">Titik Koordinat</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="edit_titik_koordinat"
                                name="titik_koordinat" placeholder="Titik Koordinat Pemasangan" readonly>
                            <button type="button" class="btn btn-primary" id="edit-get-location-btn">Ambil</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_foto_ktp" class="form-label">Foto KTP</label>
                        <div id="current_foto_ktp" class="mt-2"></div>
                        <input type="file" class="form-control" id="edit_foto_ktp" name="foto_ktp">
                    </div>
                    <div class="mb-3">
                        <label for="edit_foto_rumah" class="form-label">Foto Rumah</label>
                        <div id="current_foto_rumah" class="mt-2"></div>
                        <input type="file" class="form-control" id="edit_foto_rumah" name="foto_rumah">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
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
                Apakah Anda yakin ingin menghapus data pre-order ini?
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
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Identitas & Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Nomor Identitas (NIK):</h6>
                <p id="detail_identitas" class="fs-4 fw-bold text-primary"></p>

                <hr>

                <h6>Alamat Email:</h6>
                <p id="detail_email" class="fs-5 fw-bold text-info"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalLabel">Titik Lokasi Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modal-map" style="height: 500px;"></div>
            </div>
            <div class="modal-footer">
                <a id="gmaps-button" href="#" target="_blank" class="btn btn-primary btn-sm">Lihat di
                    Gmaps</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="viewKtpModal" tabindex="-1" aria-labelledby="viewKtpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewKtpModalLabel">Foto KTP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="ktp_image" src="" class="img-fluid" alt="Foto KTP">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="viewRumahModal" tabindex="-1" aria-labelledby="viewRumahModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRumahpModalLabel">Foto Rumah</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="rumah_image" src="" class="img-fluid" alt="Foto Rumah">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="alamatModal" tabindex="-1" aria-labelledby="alamatModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alamatModalLabel">Alamat Lengkap</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="full_alamat"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="delegateModal" tabindex="-1" aria-labelledby="delegateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="delegateModalLabel">Delegasi Tugas PO: <span
                        id="delegate_po_name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="delegateForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <label for="teknisi_pilihan" class="form-label">Pilih Teknisi</label>
                    <select class="form-control" id="teknisi_pilihan" name="id_teknisi" required>
                        <option value="">-- Pilih Teknisi --</option>
                        {{-- Data teknisi diisi dari Controller --}}
                        @foreach ($teknisiUsers as $teknisi)
                            <option value="{{ $teknisi->id }}">{{ $teknisi->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Tugaskan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="resetModalLabel">Konfirmasi Reset Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Anda yakin ingin **MENGHAPUS** penugasan untuk PO:</p>
                <h5 class="text-danger fw-bold" id="reset_po_name"></h5>
                <p class="mt-3">Aksi ini akan menghapus Teknisi yang ditugaskan dan tanggal delegasi. PO akan kembali
                    menjadi Tugas Umum.</p>
            </div>
            <div class="modal-footer">
                <form id="resetForm" method="POST" action="">
                    @csrf
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-x-circle-fill"></i> Reset
                        Tugas</button>
                </form>
            </div>
        </div>
    </div>
</div>
