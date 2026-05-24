<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manajemen ODP</title>
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta name="description"
        content="Sistem Informasi ERLANGGA NET untuk pengelolaan, pemantauan, dan administrasi pengguna layanan internet secara terpusat, efisien, dan mudah.">
    <meta name="keywords"
        content="ERLANGGA NET, sistem informasi, pengelolaan pengguna, layanan internet, administrasi jaringan, billing internet, monitoring pengguna, ISP, manajemen pelanggan">
    <meta name="author" content="Muhammad Luthfi Hakim">
    <meta name="copyright" content="Muhammad Luthfi Hakim">

    <meta property="og:title" content="ERLANGGA NET: Sistem Informasi Pengelolaan Pengguna Layanan Internet">
    <meta property="og:description"
        content="Platform terpusat untuk administrasi, monitoring, dan penagihan (billing) pengguna layanan internet Anda.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://app.erlangga.net/">
    <meta property="og:image" content="https://app.erlangga.net/dist/login/images/bg-1.png">
    <meta property="og:image:alt" content="Logo Sistem Informasi ERLANGGA NET">
    <meta property="og:site_name" content="ERLANGGA NET">
    <meta property="og:locale" content="id_ID">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="ERLANGGA NET: Sistem Informasi Pengelolaan Pengguna Layanan Internet">
    <meta name="twitter:description"
        content="Kelola dan pantau seluruh pengguna layanan internet Anda dengan mudah dan efisien menggunakan ERLANGGA NET.">
    <meta name="twitter:image" content="https://app.erlangga.net/dist/login/images/bg-1.png">
    <meta name="twitter:image:alt" content="Antarmuka Sistem Informasi ERLANGGA NET">

    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

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

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">

    <style>
        body {
            background-color: #f4f6f9;
        }

        /* FAB Button */
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

        /* Table Tweaks */
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before {
            top: 50%;
            transform: translateY(-50%);
            background-color: #007bff;
        }

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

        @media (max-width: 576px) {
            .modal-dialog {
                margin: 0.5rem;
            }
        }

        /* GPS Accuracy Indicator */
        .gps-accuracy {
            font-size: 0.8rem;
            margin-top: 5px;
            font-style: italic;
        }

        .gps-good {
            color: #28a745;
            font-weight: bold;
        }

        .gps-bad {
            color: #dc3545;
        }

        .gps-wait {
            color: #e0a800;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>

<body class="layout-top-nav">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand-md navbar-light navbar-white shadow-sm">
            <div class="container d-flex justify-content-center">
                <a href="#" class="navbar-brand m-0"> <i class="fas fa-network-wired text-primary mr-2"></i>
                    <span class="brand-text font-weight-bold">Manajemen ODP</span>
                </a>
            </div>
        </nav>

        <!-- Content -->
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
                    <!-- Statistik -->
                    <div class="row mb-3">
                        <div class="col-4">
                            <div class="small-box bg-info mb-0">
                                <div class="inner p-2 text-center">
                                    <h4 class="font-weight-bold mb-0">{{ $stats['total'] }}</h4>
                                    <p class="mb-0 small">Total</p>
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
                </div>
            </div>

            <div class="content">
                <div class="container">
                    <div class="card">
                        <div class="card-body p-2">
                            <table id="odpTable" class="table table-bordered table-striped dt-responsive nowrap"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <!-- Priority 1 = Selalu muncul, Priority rendah = sembunyi di tombol + -->
                                        <th style="text-align: center; vertical-align: middle" data-priority="1">ID
                                            ODP
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
                                            <td class="font-weight-bold align-middle">ODP-{{ $odp->id }}</td>
                                            <td style="text-align: center; vertical-align: middle"><span
                                                    class="badge {{ $odp->jenis_odp == 'GPON' ? 'badge-success' : 'badge-warning' }}">{{ $odp->jenis_odp }}</span>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle">
                                                <div class="btn-group">
                                                    <button class="btn btn-warning btn-sm btn-edit"
                                                        data-id="{{ $odp->id }}"
                                                        data-jenis="{{ $odp->jenis_odp }}"
                                                        data-lokasi="{{ $odp->lokasi_odp }}"
                                                        data-koordinat="{{ $odp->titik_koordinat }}"
                                                        data-keterangan="{{ $odp->keterangan }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm"
                                                        onclick="confirmGlobalDelete('{{ route('odp.destroy', $odp->id) }}', 'ODP-{{ $odp->id }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle">
                                                {{ $odp->lokasi_odp }}</td>
                                            <td style="text-align: center; vertical-align: middle">
                                                <a href="https://www.google.com/maps/search/?api=1&query={{ $odp->titik_koordinat }}"
                                                    target="_blank" class="btn btn-outline-primary btn-sm btn-block">
                                                    <i class="fas fa-map-marked-alt mr-1"></i> Maps
                                                </a>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle">
                                                {{ $odp->keterangan ?? '-' }}</td>
                                            <td style="text-align: center; vertical-align: middle">
                                                @if ($odp->foto_odp)
                                                    <a href="{{ asset('storage/' . $odp->foto_odp) }}"
                                                        target="_blank"
                                                        class="btn btn-outline-secondary btn-sm btn-block"><i
                                                            class="fas fa-image mr-1"></i> Foto</a>
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
                    <div class="mb-5"></div>
                </div>
            </div>
        </div>

        <!-- FAB -->
        <a href="#" class="fab" id="btnTambah"><i class="fas fa-plus"></i></a>

        <!-- Modal Form (Tambah/Edit) -->
        <div class="modal fade" id="odpModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form id="odpForm" method="POST" enctype="multipart/form-data" class="w-100">
                    @csrf
                    <div id="method-field"></div>

                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white" id="modalHeader">
                            <h5 class="modal-title" id="odpModalLabel">Tambah ODP Baru</h5>
                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Jenis ODP <span class="text-danger">*</span></label>
                                <div class="d-flex flex-wrap mt-1">
                                    <div class="custom-control custom-radio mr-4">
                                        <input class="custom-control-input" type="radio" id="odp_epon"
                                            name="jenis_odp" value="EPON" required>
                                        <label for="odp_epon" class="custom-control-label">EPON</label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input custom-control-input-primary"
                                            type="radio" id="odp_gpon" name="jenis_odp" value="GPON" required>
                                        <label for="odp_gpon" class="custom-control-label">GPON</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Alamat / Patokan <span class="text-danger">*</span></label>
                                <textarea name="lokasi_odp" id="lokasi_odp" class="form-control" rows="2" placeholder="Depan rumah pak RT..."
                                    required></textarea>
                            </div>

                            <div class="form-group">
                                <label>Koordinat GPS (Satelit) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="titik_koordinat" id="titik_koordinat"
                                        class="form-control" placeholder="Menunggu sinyal GPS..." required readonly>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-info" id="btnGetLoc"
                                            onclick="getHighAccuracyLocation()">
                                            <i class="fas fa-satellite-dish"></i> Ambil
                                        </button>
                                    </div>
                                </div>
                                <div id="gps-status" class="gps-accuracy">Klik "Ambil" dan tunggu akurasi < 20m.</div>
                                </div>

                                <div class="form-group">
                                    <label>Keterangan</label>
                                    <input type="text" name="keterangan" id="keterangan" class="form-control"
                                        placeholder="Catatan tambahan...">
                                </div>
                                <div class="form-group">
                                    <label>Foto (Opsional)</label>
                                    <div class="custom-file">
                                        <input type="file" name="foto_odp" class="custom-file-input"
                                            id="customFile" accept="image/*">
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
                ordering: false
            });

            @if (session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top',
                    icon: 'success',
                    title: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 3000
                });
            @endif
            @if ($errors->any())
            showToast('Periksa inputan Anda!', 'error');
        @endif

            // Modal Tambah
            $('#btnTambah').click(function() {
                $('#odpForm')[0].reset();
                $('#odpForm').attr('action', '{{ route('odp.store') }}');
                $('#method-field').html('');
                $('#odpModalLabel').text('Tambah ODP Baru');
                $('#modalHeader').removeClass('bg-warning').addClass('bg-primary');
                $('#btnSimpan').removeClass('btn-warning').addClass('btn-primary').text('Simpan Data');
                $('#gps-status').html('Klik "Ambil" untuk mencari sinyal.');
                $('#odpModal').modal('show');
            });

            // Modal Edit
            $('.btn-edit').click(function() {
                let id = $(this).data('id');
                let jenis = $(this).data('jenis');
                let lokasi = $(this).data('lokasi');
                let koordinat = $(this).data('koordinat');
                let keterangan = $(this).data('keterangan');

                $("input[name=jenis_odp][value=" + jenis + "]").prop('checked', true);
                $('#lokasi_odp').val(lokasi);
                $('#titik_koordinat').val(koordinat);
                $('#keterangan').val(keterangan);

                let url = "{{ route('odp.update', ':id') }}".replace(':id', id);
                $('#odpForm').attr('action', url);
                $('#method-field').html('<input type="hidden" name="_method" value="PUT">');

                $('#odpModalLabel').text('Edit ODP #' + id);
                $('#modalHeader').removeClass('bg-primary').addClass('bg-warning');
                $('#btnSimpan').removeClass('btn-primary').addClass('btn-warning').text('Update Data');
                $('#gps-status').html('Koordinat tersimpan.');
                $('#odpModal').modal('show');
            });
        });

        // --- FUNGSI GPS HIGH ACCURACY (HARDWARE) ---
        let watchID;

        function getHighAccuracyLocation() {
            if (!navigator.geolocation) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Browser tidak mendukung GPS.'
                });
                return;
            }

            const inputField = document.getElementById('titik_koordinat');
            const statusField = document.getElementById('gps-status');
            const btn = document.getElementById('btnGetLoc');

            // UI Loading
            inputField.value = "";
            inputField.placeholder = "Mencari satelit...";
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            // Opsi KUNCI untuk memaksa GPS Hardware
            const options = {
                enableHighAccuracy: true, // Wajib TRUE agar HP menyalakan modul GPS
                timeout: 30000, // 30 detik waktu tunggu
                maximumAge: 0 // Dilarang pakai cache lokasi lama
            };

            // Menggunakan watchPosition untuk memantau sampai akurasi membaik
            watchID = navigator.geolocation.watchPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const long = position.coords.longitude;
                    const accuracy = position.coords.accuracy; // dalam meter

                    // Update Status Real-time
                    if (accuracy <= 15) {
                        // Jika akurasi <= 15 meter, anggap akurat dan STOP scanning
                        inputField.value = lat + ", " + long;
                        statusField.innerHTML =
                            `<span class="gps-good"><i class="fas fa-check-circle"></i> Sinyal Kuat! Akurasi: ${Math.round(accuracy)}m (Satelit)</span>`;

                        // Stop GPS scanning untuk hemat baterai
                        navigator.geolocation.clearWatch(watchID);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-satellite-dish"></i> Ambil Ulang';

                        showToast(`Lokasi terkunci! Akurasi ${Math.round(accuracy)}m`, 'success');

                    } else {
                        // Jika akurasi masih buruk (biasanya tower seluler), update info dan lanjut scan
                        inputField.value = lat + ", " + long;
                        statusField.innerHTML =
                            `<span class="gps-wait"><i class="fas fa-satellite"></i> Menunggu sinyal lebih baik... Akurasi: ${Math.round(accuracy)}m</span>`;
                    }
                },
                function(error) {
                    // Handle Error
                    navigator.geolocation.clearWatch(watchID);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-redo"></i> Coba Lagi';
                    inputField.placeholder = "Gagal mengambil lokasi.";

                    let msg = "Gagal mengakses GPS.";
                    if (error.code == 1) msg = "Izin lokasi ditolak. Cek pengaturan browser.";
                    if (error.code == 2) msg = "Sinyal GPS hilang. Pastikan berada di luar ruangan.";
                    if (error.code == 3) msg = "Waktu habis (Timeout). Sinyal lemah.";

                    statusField.innerHTML = `<span class="gps-bad"><i class="fas fa-times-circle"></i> ${msg}</span>`;
                },
                options
            );
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus?',
                text: "Data ini akan hilang.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
            });
        }
    </script>
</body>

</html>
