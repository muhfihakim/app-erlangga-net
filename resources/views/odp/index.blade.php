<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manajemen ODP</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- DataTables BS4 & Responsive -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        /* CSS Khusus Mobile */
        body {
            background-color: #f4f6f9;
        }

        /* Floating Action Button (Tombol Tambah Melayang) */
        .fab {
            position: fixed;
            width: 60px;
            height: 60px;
            bottom: 30px;
            right: 30px;
            background-color: #007bff;
            color: #FFF;
            border-radius: 50px;
            text-align: center;
            box-shadow: 2px 2px 3px #999;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: transform 0.2s;
        }

        .fab:hover {
            background-color: #0069d9;
            color: white;
            transform: scale(1.1);
        }

        /* Perbaikan padding tabel di mobile */
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before {
            top: 50%;
            transform: translateY(-50%);
            background-color: #007bff;
        }

        /* Card styling */
        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #f0f0f0;
            border-radius: 10px 10px 0 0 !important;
        }

        /* Modal Fullscreen di HP kecil */
        @media (max-width: 576px) {
            .modal-dialog {
                margin: 0.5rem;
            }
        }
    </style>
</head>

<body class="layout-top-nav">
    <div class="wrapper">

        <!-- Navbar Sederhana -->
        <nav class="main-header navbar navbar-expand-md navbar-light navbar-white shadow-sm">
            <div class="container d-flex justify-content-center">
                <a href="#" class="navbar-brand m-0"> <i class="fas fa-network-wired text-primary mr-2"></i>
                    <span class="brand-text font-weight-bold">Manajemen ODP</span>
                </a>
            </div>
        </nav>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container">
                    <div class="row mb-2">
                        <div class="col-12 text-center">
                            <h1 class="m-0 text-dark" style="font-size: 1.5rem;">Daftar Titik ODP</h1>
                            <p class="text-muted small">Pengumpulan Data ODP
                                <br>
                                Powered by: PT. Baraya Bareng Sadulur
                            </p>
                        </div>
                    </div>
                    <!-- Statistik Row -->
                    <div class="row mb-3">
                        <div class="col-4">
                            <div class="small-box bg-info mb-0">
                                <div class="inner p-2 text-center">
                                    <h4 class="font-weight-bold mb-0">{{ $stats['total'] }}</h4>
                                    <p class="mb-0 small">Total ODP</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="small-box bg-success mb-0">
                                <div class="inner p-2 text-center">
                                    <h4 class="font-weight-bold mb-0">{{ $stats['gpon'] }}</h4>
                                    <p class="mb-0 small">GPON</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="small-box bg-warning mb-0">
                                <div class="inner p-2 text-center">
                                    <h4 class="font-weight-bold mb-0 text-white">{{ $stats['epon'] }}</h4>
                                    <p class="mb-0 small text-white">EPON</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Statistik -->
                </div>
            </div>

            <div class="content">
                <div class="container">
                    <!-- Card Utama -->
                    <div class="card">
                        <div class="card-body p-2"> <!-- Padding kecil untuk mobile -->
                            <table id="odpTable" class="table table-bordered table-striped dt-responsive nowrap"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <!-- Priority 1 = Selalu muncul, Priority rendah = sembunyi di tombol + -->
                                        <th style="text-align: center; vertical-align: middle" data-priority="1">ID ODP
                                        </th>
                                        <th style="text-align: center; vertical-align: middle" data-priority="2">Jenis
                                        </th>
                                        <th style="text-align: center; vertical-align: middle" data-priority="3">Opsi
                                        </th>
                                        <th style="text-align: center; vertical-align: middle">Lokasi</th>
                                        <th style="text-align: center; vertical-align: middle">Koordinat</th>
                                        <th style="text-align: center; vertical-align: middle">Keterangan</th>
                                        <th style="text-align: center; vertical-align: middle">Foto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($odps as $odp)
                                        <tr>
                                            <td style="text-align: center; vertical-align: middle"
                                                class="font-weight-bold align-middle">
                                                ODP-{{ $odp->id }}
                                            </td>
                                            <td style="text-align: center; vertical-align: middle" class="align-middle">
                                                <span
                                                    class="badge {{ $odp->jenis_odp == 'GPON' ? 'badge-success' : 'badge-warning' }}">
                                                    {{ $odp->jenis_odp }}
                                                </span>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle" class="align-middle">
                                                <div class="btn-group">
                                                    {{-- Tombol Edit memicu Modal Consolidated via JS --}}
                                                    <button class="btn btn-warning btn-sm btn-edit"
                                                        data-id="{{ $odp->id }}" data-jenis="{{ $odp->jenis_odp }}"
                                                        data-lokasi="{{ $odp->lokasi_odp }}"
                                                        data-koordinat="{{ $odp->titik_koordinat }}"
                                                        data-keterangan="{{ $odp->keterangan }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm"
                                                        onclick="confirmDelete({{ $odp->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <form id="delete-form-{{ $odp->id }}"
                                                    action="{{ route('odp.destroy', $odp->id) }}" method="POST"
                                                    style="display: none;">
                                                    @csrf @method('DELETE')
                                                </form>
                                            </td>
                                            <!-- Kolom detail -->
                                            <td style="text-align: center; vertical-align: middle">
                                                {{ $odp->lokasi_odp }}</td>
                                            <td style="text-align: center; vertical-align: middle">
                                                <a href="https://www.google.com/maps/search/?api=1&query={{ $odp->titik_koordinat }}"
                                                    target="_blank" class="btn btn-outline-primary btn-sm btn-block">
                                                    <i class="fas fa-map-marked-alt mr-1"></i> Buka Maps
                                                </a>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle">
                                                {{ $odp->keterangan ?? '-' }}</td>
                                            <td style="text-align: center; vertical-align: middle">
                                                @if ($odp->foto_odp)
                                                    <a href="{{ asset('storage/' . $odp->foto_odp) }}" target="_blank"
                                                        class="btn btn-outline-secondary btn-sm btn-block">
                                                        <i class="fas fa-image mr-1"></i> Lihat Foto
                                                    </a>
                                                @else
                                                    <span class="text-muted italic">Tidak ada</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mb-5"></div> <!-- Spacer buat FAB -->
                </div>
            </div>
        </div>

        <!-- Floating Action Button (FAB) -->
        <a href="#" class="fab" id="btnTambah">
            <i class="fas fa-plus"></i>
        </a>

        <!-- Modal Consolidated (Tambah & Edit) -->
        <div class="modal fade" id="odpModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form id="odpForm" method="POST" enctype="multipart/form-data" class="w-100">
                    @csrf
                    <!-- Method PUT akan disuntikkan lewat JS jika Edit -->
                    <div id="method-field"></div>

                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white" id="modalHeader">
                            <h5 class="modal-title" id="odpModalLabel">Tambah ODP Baru</h5>
                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <!-- Input Nama ODP Dihapus -->

                            <div class="form-group">
                                <label>Jenis ODP <span class="text-danger">*</span></label>
                                <select name="jenis_odp" id="jenis_odp" class="form-control" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="EPON">EPON</option>
                                    <option value="GPON">GPON</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Alamat / Patokan <span class="text-danger">*</span></label>
                                <textarea name="lokasi_odp" id="lokasi_odp" class="form-control" rows="2" placeholder="Depan rumah pak RT..."
                                    required></textarea>
                            </div>

                            <div class="form-group">
                                <label>Koordinat GPS <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="titik_koordinat" id="titik_koordinat"
                                        class="form-control" placeholder="-6.xxxx, 106.xxxx" required readonly>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-info"
                                            onclick="getLocation('titik_koordinat')">
                                            <i class="fas fa-map-marker-alt"></i> Ambil
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Klik 'Ambil' untuk deteksi lokasi otomatis.</small>
                            </div>

                            <!-- Input Kapasitas & Terisi Dihapus -->

                            <div class="form-group">
                                <label>Keterangan</label>
                                <input type="text" name="keterangan" id="keterangan" class="form-control"
                                    placeholder="Catatan tambahan...">
                            </div>
                            <div class="form-group">
                                <label>Foto (Opsional)</label>
                                <div class="custom-file">
                                    <input type="file" name="foto_odp" class="custom-file-input" id="customFile"
                                        accept="image/*">
                                    <label class="custom-file-label" for="customFile">Pilih file</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan Data</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>

    <script>
        $(document).ready(function() {
            bsCustomFileInput.init();

            $('#odpTable').DataTable({
                responsive: true,
                lengthChange: false,
                ordering: false,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari ODP...",
                    zeroRecords: "Tidak ada data",
                    info: "Hal _PAGE_ dari _PAGES_",
                    infoEmpty: "Kosong",
                    paginate: {
                        previous: "<",
                        next: ">"
                    }
                }
            });

            // SweetAlert Feedback
            @if (session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 3000
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Periksa kembali inputan Anda!'
                });
            @endif

            // LOGIKA MODAL TAMBAH (Reset Form)
            $('#btnTambah').click(function() {
                // Reset Form
                $('#odpForm')[0].reset();
                // Kembalikan ke mode POST (Create)
                $('#odpForm').attr('action', '{{ route('odp.store') }}');
                $('#method-field').html(''); // Hapus method PUT
                // Ubah Tampilan Modal
                $('#odpModalLabel').text('Tambah ODP Baru');
                $('#modalHeader').removeClass('bg-warning').addClass('bg-primary');
                $('#btnSimpan').removeClass('btn-warning').addClass('btn-primary').text('Simpan Data');
                // Tampilkan Modal
                $('#odpModal').modal('show');
            });

            // LOGIKA MODAL EDIT (Isi Form)
            $('.btn-edit').click(function() {
                let id = $(this).data('id');
                // let nama = $(this).data('nama'); // Nama sudah dihapus
                let jenis = $(this).data('jenis');
                let lokasi = $(this).data('lokasi');
                let koordinat = $(this).data('koordinat');
                let keterangan = $(this).data('keterangan');

                // Isi Form
                $('#jenis_odp').val(jenis);
                $('#lokasi_odp').val(lokasi);
                $('#titik_koordinat').val(koordinat);
                $('#keterangan').val(keterangan);

                // Ubah ke mode PUT (Update)
                let url = "{{ route('odp.update', ':id') }}";
                url = url.replace(':id', id);
                $('#odpForm').attr('action', url);
                $('#method-field').html('<input type="hidden" name="_method" value="PUT">');

                // Ubah Tampilan Modal
                $('#odpModalLabel').text('Edit ODP #' + id);
                $('#modalHeader').removeClass('bg-primary').addClass('bg-warning');
                $('#btnSimpan').removeClass('btn-primary').addClass('btn-warning').text('Update Data');

                // Tampilkan Modal
                $('#odpModal').modal('show');
            });
        });

        // Fitur GPS
        function getLocation(inputId) {
            if (navigator.geolocation) {
                let inputField = document.getElementById(inputId);
                inputField.placeholder = "Mencari lokasi...";

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        inputField.value = position.coords.latitude + "," + position.coords.longitude;
                        Swal.fire({
                            toast: true,
                            position: 'top',
                            icon: 'success',
                            title: 'Lokasi ditemukan!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    function(error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Pastikan GPS aktif dan izin diberikan.'
                        });
                    }, {
                        enableHighAccuracy: true
                    }
                );
            } else {
                alert("Browser tidak support GPS.");
            }
        }

        // Konfirmasi Hapus
        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus?',
                text: "Data ODP ini akan hilang permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            })
        }
    </script>
</body>

</html>
