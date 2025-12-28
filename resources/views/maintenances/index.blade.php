<x-layouts.app>
    @section('Css')
        <link rel="stylesheet" href="{{ asset('dist/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/responsive.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/buttons.bootstrap4.min.css') }}">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            /* Fix Select2 width in Bootstrap forms */
            .select2-container--default .select2-selection--single {
                height: 38px;
                padding-top: 5px;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px;
            }
        </style>
    @endsection

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">Pemeliharaan (Maintenance)</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            @php $is_admin = Auth::user()->jabatan === 'Administrator'; @endphp

            @if ($is_admin)
                {{-- TAMPILAN ADMINISTRATOR --}}
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Data Keseluruhan Maintenance</h3>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-success btn-sm mb-3" data-bs-toggle="modal"
                            data-bs-target="#addMaintenanceModal">
                            <i class="bi bi-plus-square"></i> Tambah Penugasan
                        </button>
                        <a href="{{ route('dashboards.index') }}" class="btn btn-secondary mb-3 btn-sm">← Kembali</a>
                        <table id="adminTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Pelanggan</th>
                                    <th>Teknisi (Delegasi)</th>
                                    <th>Status</th>
                                    <th style="text-align: center">Opsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allMaintenances as $m)
                                    @php
                                        $deadline = $m->delegated_at
                                            ? \Carbon\Carbon::parse($m->delegated_at)->addDays(2)
                                            : null;
                                        $isLocked =
                                            $m->status === 'Pending' && $deadline && now()->greaterThan($deadline);
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $m->customer->nama_lengkap }} / {{ $m->customer->pppoe_username }}</td>
                                        <td>{{ $m->teknisiDelegasi->nama_lengkap ?? 'Tugas Umum' }}
                                            @if ($m->customer->partner)
                                                <br><small class="text-muted">Partner:
                                                    {{ $m->customer->partner }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $m->status === 'Selesai' ? 'bg-success' : ($isLocked ? 'bg-danger' : 'bg-warning') }}">
                                                {{ $m->status === 'Selesai' ? 'SELESAI' : ($isLocked ? 'LOCKED' : 'PENDING') }}
                                            </span>
                                        </td>
                                        <td style="text-align: center">
                                            {{-- Detail Tugas (Admin) --}}
                                            <button type="button" class="btn btn-secondary btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#detailMaintenanceModal"
                                                data-nama="{{ $m->customer->nama_lengkap }}"
                                                data-id-pel="{{ $m->customer->id_pelanggan }}"
                                                data-pppoe="{{ $m->customer->pppoe_username }}"
                                                data-wa="{{ $m->customer->no_whatsapp }}"
                                                data-tikor="{{ $m->customer->preOrder->titik_koordinat ?? 'N/A' }}"
                                                data-alamat="{{ $m->customer->alamat_lengkap }}"
                                                data-kendala="{{ $m->keterangan_kendala }}"
                                                data-foto="{{ $m->foto_kendala_awal ? asset('storage/' . $m->foto_kendala_awal) : '' }}">
                                                <i class="bi bi-person-lines-fill"></i>
                                            </button>

                                            @if ($m->status === 'Selesai')
                                                {{-- Tombol Lihat Hasil Pengerjaan --}}
                                                <button type="button" class="btn btn-success btn-sm"
                                                    data-bs-toggle="modal" data-bs-target="#resultMaintenanceModal"
                                                    data-nama="{{ $m->customer->nama_lengkap }}"
                                                    data-eksekutor="{{ $m->teknisiEksekutor->nama_lengkap ?? 'N/A' }}"
                                                    data-partner="{{ $m->partner ?: '-' }}"
                                                    data-solusi="{{ $m->solusi_kendala }}"
                                                    data-waktu="{{ \Carbon\Carbon::parse($m->completed_at)->format('d-m-Y H:i') }}"
                                                    data-foto-before="{{ asset('storage/' . $m->foto_sebelum_perbaikan) }}"
                                                    data-foto-after="{{ asset('storage/' . $m->foto_setelah_perbaikan) }}"
                                                    data-foto-speed="{{ asset('storage/' . $m->foto_speedtest) }}"
                                                    data-foto-selfie="{{ asset('storage/' . $m->foto_selfie_teknisi) }}">
                                                    <i class="bi bi-file-earmark-check"></i>
                                                </button>
                                            @else
                                                <button class="btn {{ $isLocked ? 'btn-danger' : 'btn-info' }} btn-sm"
                                                    data-bs-toggle="modal" data-bs-target="#reassignModal"
                                                    data-id="{{ $m->id }}"
                                                    data-nama="{{ $m->customer->nama_lengkap }}">
                                                    <i
                                                        class="bi {{ $isLocked ? 'bi-x-circle' : 'bi-person-badge' }}"></i>
                                                </button>
                                            @endif

                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="confirmDelete({{ $m->id }}, '{{ $m->customer->nama_lengkap }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <form id="delete-form-{{ $m->id }}"
                                                action="{{ route('maintenances.destroy', $m->id) }}" method="POST"
                                                style="display: none;">@csrf @method('DELETE')</form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                {{-- TAMPILAN TEKNISI --}}
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Tugas Delegasi Khusus</h3>
                    </div>
                    <div class="card-body">
                        <table id="delegatedTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Pelanggan</th>
                                    <th>Deadline</th>
                                    <th style="text-align: center">Opsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($delegatedTasks as $m)
                                    @php $isLocked = now()->greaterThan(\Carbon\Carbon::parse($m->delegated_at)->addDays(2)); @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $m->customer->nama_lengkap }}</td>
                                        <td>{{ \Carbon\Carbon::parse($m->delegated_at)->addDays(2)->format('d/m H:i') }}
                                        </td>
                                        <td style="text-align: center">
                                            <button type="button" class="btn btn-secondary btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#detailMaintenanceModal"
                                                data-nama="{{ $m->customer->nama_lengkap }}"
                                                data-id-pel="{{ $m->customer->id_pelanggan }}"
                                                data-pppoe="{{ $m->customer->pppoe_username }}"
                                                data-wa="{{ $m->customer->no_whatsapp }}"
                                                data-tikor="{{ $m->customer->preOrder->titik_koordinat ?? 'N/A' }}"
                                                data-alamat="{{ $m->customer->alamat_lengkap }}"
                                                data-kendala="{{ $m->keterangan_kendala }}"
                                                data-foto="{{ $m->foto_kendala_awal ? asset('storage/' . $m->foto_kendala_awal) : '' }}">
                                                <i class="bi bi-person-lines-fill"></i>
                                            </button>
                                            @if ($isLocked)
                                                <button class="btn btn-danger btn-sm" disabled><i
                                                        class="bi bi-lock-fill"></i> Locked</button>
                                            @else
                                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#completeModal" data-id="{{ $m->id }}"
                                                    data-nama="{{ $m->customer->nama_lengkap }}">Selesai</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card card-secondary mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Tugas Umum</h3>
                    </div>
                    <div class="card-body">
                        <table id="generalTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Pelanggan</th>
                                    <th style="text-align: center">Opsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($generalTasks as $m)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $m->customer->nama_lengkap }}</td>
                                        <td style="text-align: center">
                                            <button type="button" class="btn btn-secondary btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#detailMaintenanceModal"
                                                data-nama="{{ $m->customer->nama_lengkap }}"
                                                data-id-pel="{{ $m->customer->id_pelanggan }}"
                                                data-pppoe="{{ $m->customer->pppoe_username }}"
                                                data-wa="{{ $m->customer->no_whatsapp }}"
                                                data-tikor="{{ $m->customer->preOrder->titik_koordinat ?? 'N/A' }}"
                                                data-alamat="{{ $m->customer->alamat_lengkap }}"
                                                data-kendala="{{ $m->keterangan_kendala }}"
                                                data-foto="{{ $m->foto_kendala_awal ? asset('storage/' . $m->foto_kendala_awal) : '' }}">
                                                <i class="bi bi-person-lines-fill"></i>
                                            </button>
                                            <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#completeModal" data-id="{{ $m->id }}"
                                                data-nama="{{ $m->customer->nama_lengkap }}">Ambil</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="modal fade" id="detailMaintenanceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title"><i class="bi bi-info-circle"></i> Detail Pemeliharaan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">Nama Pelanggan</th>
                                <td>: <span id="det_nama"></span></td>
                            </tr>
                            <tr>
                                <th>ID Pelanggan</th>
                                <td>: <span id="det_id_pel"></span></td>
                            </tr>
                            <tr>
                                <th>Akun PPPoE</th>
                                <td>: <code id="det_pppoe"></code></td>
                            </tr>
                            <tr>
                                <th>WhatsApp</th>
                                <td>:
                                    <a href="#" id="det_wa_link" target="_blank"
                                        class="btn btn-xs btn-success text-white">
                                        <i class="bi bi-whatsapp"></i> <span id="det_wa"></span>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Lokasi (Tikor)</th>
                                <td>:
                                    <a href="#" id="det_tikor_link" target="_blank"
                                        class="btn btn-xs btn-primary text-white">
                                        <i class="bi bi-geo-alt"></i> Lihat di Maps
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td>: <span id="det_alamat"></span></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <hr class="my-1">
                                </td>
                            </tr>
                            <tr>
                                <th>Keterangan Kendala</th>
                                <td>: <span id="det_kendala"></span></td>
                            </tr>
                        </table>
                        <div id="det_foto_container" class="mt-2 text-center" style="display: none;">
                            <label class="d-block text-start small fw-bold">Foto Kendala Awal:</label>
                            <img src="" id="det_foto" class="img-fluid rounded border"
                                style="max-height: 200px;" alt="Foto Kendala">
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm"
                            data-bs-dismiss="modal">Tutup</button></div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="reassignModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form id="reassignForm" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5>Tugaskan Ulang: <span id="re_nama"></span></h5>
                        </div>
                        <div class="modal-body">
                            <label>Pilih Teknisi</label>
                            <select name="id_teknisi_delegasi" class="form-control" required>
                                @foreach ($teknisis as $t)
                                    <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger btn-sm">Tugaskan & Unlock</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="resultMaintenanceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-check-all"></i> Hasil Pengerjaan Maintenance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold border-bottom pb-2">Informasi Pengerjaan</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="40%">Pelanggan</th>
                                        <td>: <span id="res_nama"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Teknisi Utama</th>
                                        <td>: <strong id="res_eksekutor"></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Partner</th>
                                        <td>: <span id="res_partner"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Waktu Selesai</th>
                                        <td>: <span id="res_waktu"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Solusi</th>
                                        <td>: <p id="res_solusi" class="text-muted small"></p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold border-bottom pb-2 text-center">Dokumentasi Foto</h6>
                                <div class="row g-2 text-center">
                                    <div class="col-6">
                                        <small class="d-block">Sebelum</small>
                                        <a href="#" id="link_before" target="_blank"><img src=""
                                                id="res_before" class="img-fluid rounded border img-preview"></a>
                                    </div>
                                    <div class="col-6">
                                        <small class="d-block">Sesudah</small>
                                        <a href="#" id="link_after" target="_blank"><img src=""
                                                id="res_after" class="img-fluid rounded border img-preview"></a>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <small class="d-block">Speedtest</small>
                                        <a href="#" id="link_speed" target="_blank"><img src=""
                                                id="res_speed" class="img-fluid rounded border img-preview"></a>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <small class="d-block">Selfie Teknisi</small>
                                        <a href="#" id="link_selfie" target="_blank"><img src=""
                                                id="res_selfie" class="img-fluid rounded border img-preview"></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah (Admin) -->
        <div class="modal fade" id="addMaintenanceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form action="{{ route('maintenances.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>Buat Tugas Maintenance</h5>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Pilih Pelanggan</label>
                                <select name="customer_id" id="customer_select" class="form-select" required>
                                    <option value="">-- Cari Nama Pelanggan --</option>
                                    @foreach ($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->nama_lengkap }}
                                            ({{ $c->id_pelanggan }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label>PPPoE</label><input type="text" id="pppoe_val"
                                        class="form-control" readonly></div>
                                <div class="col-md-6 mb-3"><label>Tikor</label><input type="text" id="tikor_val"
                                        class="form-control" readonly></div>
                            </div>
                            <div class="mb-3"><label>Alamat</label>
                                <textarea id="alamat_val" class="form-control" rows="2" readonly></textarea>
                            </div>

                            <div class="mb-3">
                                <label>Tugaskan Ke (Opsional)</label>
                                <select name="id_teknisi_delegasi" class="form-control">
                                    <option value="">-- Biarkan Kosong Untuk Tugas Umum --</option>
                                    @foreach ($teknisis as $t)
                                        <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted italic">*Jika kosong, semua teknisi dapat mengerjakan tugas
                                    ini.</small>
                            </div>

                            <div class="mb-3">
                                <label>Keterangan Kendala</label>
                                <textarea name="keterangan_kendala" class="form-control" required
                                    placeholder="Contoh: Los Merah / Router Mati / Lemot"></textarea>
                            </div>
                            <div class="mb-3">
                                <label>Foto Kendala (Jika Ada)</label>
                                <input type="file" name="foto_kendala_awal" class="form-control"
                                    accept="image/*">
                            </div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-success">Simpan
                                Penugasan</button></div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Selesai (Teknisi) -->
        <div class="modal fade" id="completeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form id="completeForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5>Selesaikan Tugas: <span id="nama_p"></span></h5>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Pilih Partner (Rekan Kerja)</label>
                                <select name="partner_ids[]" id="partner_select" class="form-control select2"
                                    multiple="multiple" data-placeholder="Pilih Rekan Teknisi (Bisa lebih dari 1)">
                                    @foreach ($teknisis as $t)
                                        @if ($t->id != auth()->id())
                                            <option value="{{ $t->id }}">{{ $t->nama_lengkap }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Solusi / Tindakan Perbaikan</label>
                                <textarea name="solusi_kendala" class="form-control" required placeholder="Jelaskan tindakan yang dilakukan..."></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Foto Sebelum (Kendala)</label>
                                    <input type="file" name="foto_sebelum" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Foto Sesudah (Perbaikan)</label>
                                    <input type="file" name="foto_sesudah" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Foto Speedtest</label>
                                    <input type="file" name="foto_speedtest" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Foto Selfie Teknisi</label>
                                    <input type="file" name="foto_selfie" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary">Kirim Laporan
                                Selesai</button></div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    @section('Scripts')
        <!-- jQuery -->
        <script src="{{ asset('dist/js/jquery.min.js') }}"></script>
        <!-- Bootstrap 4 -->
        <script src="{{ asset('dist/js/bootstrap.bundle.min.js') }}"></script>
        <!-- DataTables  & js -->
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('dist/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('dist/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('dist/js/responsive.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('dist/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('dist/js/buttons.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('dist/js/buttons.colVis.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            $(document).ready(function() {
                $('#maintenanceTable').DataTable({
                    "ordering": false
                });
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });

                @if (session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '{{ session('success') }}',
                        showConfirmButton: false,
                        timer: 3000
                    });
                @endif
                @if (session('error'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: '{{ session('error') }}'
                    });
                @endif
                // Reassign Logic
                const reassignModal = document.getElementById('reassignModal');
                if (reassignModal) {
                    reassignModal.addEventListener('show.bs.modal', function(e) {
                        const b = e.relatedTarget;
                        document.getElementById('re_nama').textContent = b.getAttribute('data-nama');
                        document.getElementById('reassignForm').action =
                            `/maintenances/${b.getAttribute('data-id')}/reassign`;
                    });
                }
            });
        </script>
        <script>
            $(document).ready(function() {
                // Inisialisasi DataTables berdasarkan ID tabel yang benar
                $('#adminTable, #delegatedTable, #generalTable').DataTable({
                    "ordering": false,
                    "responsive": true
                });

                // Inisialisasi Select2
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });

                @if (session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '{{ session('success') }}',
                        showConfirmButton: false,
                        timer: 3000
                    });
                @endif
                @if (session('error'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: '{{ session('error') }}'
                    });
                @endif

                // Logic Modal Detail
                const detailModal = document.getElementById('detailMaintenanceModal');
                if (detailModal) {
                    detailModal.addEventListener('show.bs.modal', function(event) {
                        const b = event.relatedTarget;
                        let waRaw = b.getAttribute('data-wa') || '';
                        const tikor = b.getAttribute('data-tikor') || '';
                        const foto = b.getAttribute('data-foto') || '';

                        let waFormatted = waRaw;
                        if (waRaw.startsWith('0')) waFormatted = '62' + waRaw.substring(1);

                        document.getElementById('det_nama').textContent = b.getAttribute('data-nama');
                        document.getElementById('det_id_pel').textContent = b.getAttribute('data-id-pel');
                        document.getElementById('det_pppoe').textContent = b.getAttribute('data-pppoe');
                        document.getElementById('det_wa').textContent = waRaw;
                        document.getElementById('det_wa_link').href = `https://wa.me/${waFormatted}`;
                        document.getElementById('det_alamat').textContent = b.getAttribute('data-alamat');

                        const tikorLink = document.getElementById('det_tikor_link');
                        if (tikor && tikor !== 'N/A' && tikor !== '') {
                            tikorLink.href = `https://www.google.com/maps/search/?api=1&query=${tikor}`;
                            tikorLink.style.display = 'inline-block';
                        } else {
                            tikorLink.style.display = 'none';
                        }

                        document.getElementById('det_kendala').textContent = b.getAttribute('data-kendala');

                        const img = document.getElementById('det_foto');
                        if (foto && foto !== '') {
                            img.src = foto;
                            document.getElementById('det_foto_container').style.display = 'block';
                        } else {
                            document.getElementById('det_foto_container').style.display = 'none';
                        }
                    });
                }

                const resultModal = document.getElementById('resultMaintenanceModal');
                if (resultModal) {
                    resultModal.addEventListener('show.bs.modal', function(event) {
                        const b = event.relatedTarget;

                        document.getElementById('res_nama').textContent = b.getAttribute('data-nama');
                        document.getElementById('res_eksekutor').textContent = b.getAttribute('data-eksekutor');
                        document.getElementById('res_partner').textContent = b.getAttribute('data-partner');
                        document.getElementById('res_waktu').textContent = b.getAttribute('data-waktu');
                        document.getElementById('res_solusi').textContent = b.getAttribute('data-solusi');

                        // Set Photos & Links
                        const photos = ['before', 'after', 'speed', 'selfie'];
                        photos.forEach(p => {
                            const url = b.getAttribute(`data-foto-${p}`);
                            document.getElementById(`res_${p}`).src = url;
                            document.getElementById(`link_${p}`).href = url;
                        });
                    });
                }

                // Modal Reassign
                const reassignModal = document.getElementById('reassignModal');
                if (reassignModal) {
                    reassignModal.addEventListener('show.bs.modal', function(e) {
                        const b = e.relatedTarget;
                        document.getElementById('re_nama').textContent = b.getAttribute('data-nama');
                        document.getElementById('reassignForm').action =
                            `/maintenances/${b.getAttribute('data-id')}/reassign`;
                    });
                }

                // Modal Selesai
                const completeModal = document.getElementById('completeModal');
                if (completeModal) {
                    completeModal.addEventListener('show.bs.modal', function(event) {
                        const b = event.relatedTarget;
                        document.getElementById('nama_p').textContent = b.getAttribute('data-nama');
                        document.getElementById('completeForm').action =
                            `/maintenances/${button.getAttribute('data-id')}/complete`;
                    });
                }

                // Auto-fill Admin Form
                $('#customer_select').on('change', function() {
                    const id = $(this).val();
                    if (id) fetch(`/maintenances/customer/${id}`).then(r => r.json()).then(d => {
                        $('#pppoe_val').val(d.pppoe);
                        $('#alamat_val').val(d.alamat);
                        $('#tikor_val').val(d.tikor);
                    });
                });
            });

            function confirmDelete(id, name) {
                Swal.fire({
                    title: 'Hapus Tugas?',
                    text: "Tugas pemeliharaan " + name + " akan dibatalkan.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Batalkan!'
                }).then((result) => {
                    if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
                })
            }
        </script>
        <script>
            $(document).ready(function() {
                $('#maintenanceTable').DataTable({
                    "ordering": false
                });

                // Inisialisasi Select2 untuk semua modal
                $('#customer-select').select2({
                    placeholder: "-- Cari Nama Pelanggan --",
                    allowClear: true // Opsi untuk menghapus pilihan
                });

                // Fix Select2 di dalam modal
                $('#addMaintenanceModal').on('shown.bs.modal', function() {
                    $('#customer_select').select2({
                        theme: 'bootstrap4',
                        dropdownParent: $('#addMaintenanceModal')
                    });
                });
                $('#completeModal').on('shown.bs.modal', function() {
                    $('#partner_select').select2({
                        theme: 'bootstrap4',
                        dropdownParent: $('#completeModal')
                    });
                });

                // Auto-fill Data Pelanggan
                $('#customer_select').on('change', function() {
                    const id = $(this).val();
                    if (id) {
                        fetch(`/maintenances/customer/${id}`)
                            .then(r => r.json())
                            .then(data => {
                                $('#pppoe_val').val(data.pppoe);
                                $('#alamat_val').val(data.alamat);
                                $('#tikor_val').val(data.tikor);
                            });
                    }
                });

                // Pemicu Modal Selesai
                const completeModal = document.getElementById('completeModal');
                completeModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    document.getElementById('nama_p').textContent = button.getAttribute('data-nama');
                    document.getElementById('completeForm').action =
                        `/maintenances/${button.getAttribute('data-id')}/complete`;
                });
            });
        </script>
    @endsection
</x-layouts.app>
