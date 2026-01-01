<x-layouts.app>
    @section('Css')
        <link rel="stylesheet" href="{{ asset('dist/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/responsive.bootstrap4.min.css') }}">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .select2-container--bootstrap4 .select2-selection--single {
                height: 38px !important;
            }
        </style>
    @endsection
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">Dismantle</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dismantle</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Data Keseluruhan Dismantle</h3>
                </div>
                <div class="card-body">
                    @if (auth()->user()->jabatan === 'Administrator')
                        <button class="btn btn-success btn-sm mb-3" data-bs-toggle="modal"
                            data-bs-target="#addDismantleModal">
                            <i class="bi bi-plus-square"></i> Tambah Dismantle</button>
                    @endif
                    <a href="{{ route('dashboards.index') }}" class="btn btn-secondary mb-3 btn-sm">← Kembali</a>
                    <table id="dismantleTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th style="text-align: center; vertical-align: middle">No</th>
                                <th style="text-align: center; vertical-align: middle">Pelanggan</th>
                                <th style="text-align: center; vertical-align: middle">Status</th>
                                <th style="text-align: center; vertical-align: middle">Teknisi</th>
                                <th style="text-align: center; vertical-align: middle">Waktu Selesai</th>
                                <th style="text-align: center; vertical-align: middle">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dismantles as $d)
                                <tr>
                                    <td style="text-align: center; vertical-align: middle">
                                        {{ $loop->iteration }}</td>
                                    <td style="vertical-align: middle">
                                        <strong>{{ $d->customer->nama_lengkap }}</strong>
                                        @if ($d->customer->trashed())
                                            <span class="badge bg-secondary">Non-Aktif</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">ID: {{ $d->customer->id_pelanggan }} -
                                            PPPOE: {{ $d->customer->pppoe_username }} </small>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle">
                                        <span class="badge {{ $d->status == 'Selesai' ? 'bg-success' : 'bg-warning' }}">
                                            {{ $d->status }}
                                        </span>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle">
                                        {{ $d->teknisiEksekutor->nama_lengkap ?? ($d->teknisiDelegasi->nama_lengkap ?? 'Tugas Umum') }}
                                    </td>
                                    <td style="text-align: center; vertical-align: middle">
                                        {{ $d->completed_at ? $d->completed_at->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td style="text-align: center; vertical-align: middle">
                                        {{-- Tombol Detail --}}
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#detailDismantleModal"
                                            data-nama="{{ $d->customer->nama_lengkap }}"
                                            data-id-pel="{{ $d->customer->id_pelanggan }}"
                                            data-wa="{{ $d->customer->no_whatsapp }}"
                                            data-tikor="{{ $d->customer->preOrder->titik_koordinat ?? 'N/A' }}"
                                            data-alamat="{{ $d->customer->alamat_lengkap }}"
                                            data-sales="{{ $d->customer->sales->nama_lengkap ?? 'N/A' }}"
                                            data-paket="{{ $d->customer->paket_internet }}"
                                            data-keterangan="{{ $d->keterangan_admin ?: '-' }}"
                                            data-foto="{{ $d->foto_pencabutan ? asset('storage/' . $d->foto_pencabutan) : '' }}">
                                            <i class="bi bi-person-lines-fill"></i>
                                        </button>

                                        {{-- Opsi untuk Teknisi --}}
                                        @if ($d->status === 'Pending' && auth()->user()->jabatan === 'Teknisi')
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#completeDismantleModal" data-id="{{ $d->id }}"
                                                data-nama="{{ $d->customer->nama_lengkap }}">
                                                <i class="bi bi-camera"></i> Selesai
                                            </button>
                                        @endif

                                        {{-- Opsi untuk Administrator --}}
                                        @if (auth()->user()->jabatan === 'Administrator')
                                            @if ($d->status === 'Selesai')
                                                @if ($d->customer->trashed())
                                                    {{-- Tampilan jika pelanggan sudah di-softdelete --}}
                                                    <button class="btn btn-outline-secondary btn-sm" disabled>
                                                        <i class="bi bi-check-all"></i> Pelanggan Terhapus
                                                    </button>
                                                @else
                                                    {{-- Tombol hapus jika pelanggan masih aktif --}}
                                                    <button class="btn btn-danger btn-sm"
                                                        onclick="confirmDeleteCustomer({{ $d->customer_id }}, '{{ $d->customer->nama_lengkap }}')">
                                                        <i class="bi bi-trash"></i> Hapus Pelanggan
                                                    </button>
                                                    <form id="del-cust-{{ $d->customer_id }}"
                                                        action="{{ route('dismantles.deleteCustomer', $d->customer_id) }}"
                                                        method="POST" style="display: none;">@csrf
                                                        @method('DELETE')
                                                    </form>
                                                @endif
                                            @elseif ($d->status === 'Pending')
                                                <button class="btn btn-outline-danger btn-sm"
                                                    onclick="confirmCancelDismantle({{ $d->id }}, '{{ $d->customer->nama_lengkap }}')">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                                <form id="cancel-task-{{ $d->id }}"
                                                    action="{{ route('dismantles.destroy', $d->id) }}" method="POST"
                                                    style="display: none;">@csrf
                                                    @method('DELETE')</form>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <!-- MODAL DETAIL DISMANTLE -->
        <div class="modal fade" id="detailDismantleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"> Info Lengkap Pelanggan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">Nama</th>
                                <td>: <span id="det_nama"></span></td>
                            </tr>
                            <tr>
                                <th>ID Pelanggan</th>
                                <td>: <span id="det_id_pel"></span></td>
                            </tr>
                            <tr>
                                <th>Paket</th>
                                <td>: <span id="det_paket"></span></td>
                            </tr>
                            <tr>
                                <th>WhatsApp</th>
                                <td>: <a href="#" id="det_wa_link" target="_blank"
                                        class="btn btn-xs btn-success text-white"><i class="bi bi-whatsapp"></i> <span
                                            id="det_wa"></span></a>
                                </td>
                            </tr>
                            <tr>
                                <th>Lokasi</th>
                                <td>: <a href="#" id="det_tikor_link" target="_blank"
                                        class="btn btn-xs btn-primary text-white"><i class="bi bi-geo-alt"></i>
                                        Maps</a>
                                </td>
                            </tr>
                            <tr>
                                <th>Sales</th>
                                <td>: <span id="det_sales"></span></td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td>: <span id="det_alamat"></span></td>
                            </tr>
                            <tr>
                                <th>Ket. Admin</th>
                                <td>: <span id="det_keterangan"></span></td>
                            </tr>
                        </table>
                        <div id="det_foto_container" class="mt-2 text-center" style="display: none;">
                            <label class="d-block text-start fw-bold">Bukti Perangkat
                                Dicabut:</label>
                            <a href="#" id="det_foto_link" target="_blank">
                                <img src="" id="det_foto" class="img-fluid rounded border shadow-sm"
                                    style="max-height: 200px;">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah (Admin) - FIXED NESTED FORM -->
        <div class="modal fade" id="addDismantleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form action="{{ route('dismantles.store') }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5>Input Penugasan Dismantle</h5>
                        </div>
                        <div class="modal-body">
                            <label>Cari Pelanggan Aktif</label>
                            <select name="customer_id" id="cust_select" class="form-control select2" required
                                style="width:100%">
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach ($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->nama_lengkap }}
                                        ({{ $c->id_pelanggan }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="row mt-3">
                                <div class="col-md-6 mb-2"><label>WhatsApp</label><input type="text"
                                        id="v_wa" class="form-control" readonly></div>
                                <div class="col-md-6 mb-2"><label>Paket</label><input type="text" id="v_paket"
                                        class="form-control" readonly></div>
                                <div class="col-md-6 mb-2"><label>Sales</label><input type="text" id="v_sales"
                                        class="form-control" readonly></div>
                                <div class="col-md-6 mb-2"><label>Tikor</label><input type="text" id="v_tikor"
                                        class="form-control" readonly></div>
                            </div>
                            <div class="mb-2"><label>Alamat</label>
                                <textarea id="v_alamat" class="form-control" rows="2" readonly></textarea>
                            </div>
                            <div class="mb-2"><label>Alasan / Keterangan</label>
                                <textarea name="keterangan_admin" class="form-control" placeholder="Contoh: Berhenti berlangganan / Pindah rumah"></textarea>
                            </div>
                            <div class="mb-2">
                                <label>Tugaskan Ke (Opsional)</label>
                                <select name="id_teknisi_delegasi" class="form-control">
                                    <option value="">-- Tugas Umum (Bisa diambil siapa saja) --
                                    </option>
                                    @foreach ($teknisis as $t)
                                        <option value="{{ $t->id }}">{{ $t->nama_lengkap }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-success">Kirim
                                Tugas</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Selesai (Teknisi) -->
        <div class="modal fade" id="completeDismantleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form id="completeForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5>Konfirmasi Pencabutan: <span id="n_pel"></span></h5>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted">Pastikan semua perangkat (ONT/Router) sudah diambil
                                kembali.
                            </p>
                            <label>Foto Bukti Perangkat Dicabut</label>
                            <input type="file" name="foto_pencabutan" class="form-control" required
                                accept="image/*">
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary">Submit
                                Laporan</button></div>
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
                $('#dismantleTable').DataTable({
                    "ordering": false,
                    "responsive": true
                });
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    dropdownParent: $('#addDismantleModal')
                });

                @if (session('success'))
                    Swal.fire('Berhasil', '{{ session('success') }}', 'success');
                @endif
                @if (session('error'))
                    Swal.fire('Gagal', '{{ session('error') }}', 'error');
                @endif

                // Auto-fill Logic
                $('#cust_select').on('change', function() {
                    const id = $(this).val();
                    if (id) fetch(`/dismantles/customer/${id}`).then(r => r.json()).then(d => {
                        $('#v_wa').val(d.wa);
                        $('#v_paket').val(d.paket);
                        $('#v_sales').val(d.sales);
                        $('#v_tikor').val(d.tikor);
                        $('#v_alamat').val(d.alamat);
                    });
                });

                // Detail Modal Listener
                const detailModal = document.getElementById('detailDismantleModal');
                detailModal.addEventListener('show.bs.modal', function(event) {
                    const b = event.relatedTarget;
                    const waRaw = b.getAttribute('data-wa');
                    const waFormatted = waRaw.startsWith('0') ? '62' + waRaw.substring(1) : waRaw;
                    const tikor = b.getAttribute('data-tikor');
                    const foto = b.getAttribute('data-foto');

                    document.getElementById('det_nama').textContent = b.getAttribute('data-nama');
                    document.getElementById('det_id_pel').textContent = b.getAttribute('data-id-pel');
                    document.getElementById('det_paket').textContent = b.getAttribute('data-paket');
                    document.getElementById('det_wa').textContent = waRaw;
                    document.getElementById('det_wa_link').href = `https://wa.me/${waFormatted}`;
                    document.getElementById('det_sales').textContent = b.getAttribute('data-sales');
                    document.getElementById('det_alamat').textContent = b.getAttribute('data-alamat');
                    document.getElementById('det_keterangan').textContent = b.getAttribute('data-keterangan');

                    const tLink = document.getElementById('det_tikor_link');
                    if (tikor && tikor !== 'N/A') {
                        tLink.href = `https://www.google.com/maps/search/?api=1&query=${tikor}`;
                        tLink.style.display = 'inline-block';
                    } else {
                        tLink.style.display = 'none';
                    }

                    const fCont = document.getElementById('det_foto_container');
                    if (foto) {
                        document.getElementById('det_foto').src = foto;
                        document.getElementById('det_foto_link').href = foto;
                        fCont.style.display = 'block';
                    } else {
                        fCont.style.display = 'none';
                    }
                });

                // Selesai Modal Listener
                document.getElementById('completeDismantleModal').addEventListener('show.bs.modal', function(e) {
                    const b = e.relatedTarget;
                    document.getElementById('n_pel').textContent = b.getAttribute('data-nama');
                    document.getElementById('completeForm').action =
                        `/dismantles/${b.getAttribute('data-id')}/complete`;
                });
            });

            function confirmDeleteCustomer(id, name) {
                Swal.fire({
                    title: 'Hapus Pelanggan?',
                    text: "Proses dismantle selesai. Hapus data " + name +
                        " dari daftar pelanggan aktif secara permanen?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus Permanen'
                }).then((r) => {
                    if (r.isConfirmed) document.getElementById('del-cust-' + id).submit();
                });
            }

            function confirmCancelDismantle(id, name) {
                Swal.fire({
                    title: 'Batalkan Tugas?',
                    text: "Tugas dismantle untuk " + name + " akan dihapus dari antrean.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Batalkan'
                }).then((r) => {
                    if (r.isConfirmed) document.getElementById('cancel-task-' + id).submit();
                });
            }
        </script>
    @endsection
</x-layouts.app>
