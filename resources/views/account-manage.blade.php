<x-layouts.app>
    @section('Css')
        <!-- DataTables -->
        <link rel="stylesheet" href="{{ asset('dist/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/responsive.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/buttons.bootstrap4.min.css') }}">
    @endsection
    <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
            <!--begin::Container-->
            <div class="container-fluid">
                <!--begin::Row-->
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">Manajemen</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Akun</li>
                        </ol>
                    </div>
                </div>
                <!--end::Row-->
            </div>
            <!--end::Container-->
        </div>
        <div class="app-content">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Akun</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    {{-- <a href="{{ route('services.create') }}" class="btn btn-success mb-3">+ Tambah Layanan</a> --}}
                    <button type="button" class="btn btn-success mb-3 btn-sm" data-bs-toggle="modal"
                        data-bs-target="#addUserModal">
                        <i class="bi bi-plus-square"></i> Tambah Akun
                    </button>
                    <a href="{{ route('dashboards.index') }}" class="btn btn-secondary mb-3 btn-sm">← Kembali</a>
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center">No</th>
                                <th style="text-align: center">Nama Lengkap</th>
                                <th style="text-align: center">NIK</th>
                                <th style="text-align: center">Alamat</th>
                                <th style="text-align: center">Jenis Kelamin</th>
                                <th style="text-align: center">Tanggal Lahir</th>
                                <th style="text-align: center">Email</th>
                                <th style="text-align: center">Jabatan</th>
                                <th style="text-align: center">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td style="text-align: center; vertical-align: middle">{{ $loop->iteration }}</td>
                                    <td>{{ $user->nama_lengkap }}</td>
                                    <td style="text-align: center; vertical-align: middle">{{ $user->nik }}</td>
                                    <td>{{ $user->alamat }}</td>
                                    <td style="text-align: center; vertical-align: middle">{{ $user->jenis_kelamin }}
                                    </td>
                                    <td style="text-align: center; vertical-align: middle">
                                        {{ \Carbon\Carbon::parse($user->tanggal_lahir)->format('d-m-Y') }}
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td style="text-align: center; vertical-align: middle">{{ $user->jabatan }}</td>
                                    <td style="text-align: center; vertical-align: middle">
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editModal" data-user-id="{{ $user->id }}"
                                            data-user-nama="{{ $user->nama_lengkap }}"
                                            data-user-nik="{{ $user->nik }}" data-user-alamat="{{ $user->alamat }}"
                                            data-user-jk="{{ $user->jenis_kelamin }}"
                                            data-user-tgl="{{ \Carbon\Carbon::parse($user->tanggal_lahir)->format('Y-m-d') }}"
                                            data-user-email="{{ $user->email }}"
                                            data-user-jabatan="{{ $user->jabatan }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        @if ($user->google2fa_secret)
                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                onclick="show2FAQRCode({{ $user->id }})" title="Lihat QR Code">
                                                <i class="bi bi-qr-code"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="reset2FA({{ $user->id }})" title="Reset 2FA">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal" data-user-id="{{ $user->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @include('partials.modal_account-manage')
                <!--end::App Content-->
            </div>
        </div>
    </main>
    <!--end::App Main-->
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
        <!-- AdminLTE App -->
        <script>
            function submitStatus(id, statusValue) {
                document.getElementById('statusInput' + id).value = statusValue;
                document.getElementById('statusForm' + id).submit();
            }
        </script>
        <!-- Page specific script -->
        <script>
            $(function() {
                $("#example1").DataTable({
                    "responsive": true,
                    "lengthChange": false,
                    "autoWidth": false,
                    "ordering": false,
                }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Logika untuk menampilkan SweetAlert
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
                    title: 'Oops...',
                    text: '{{ session('error') }}',
                });
            @endif
        </script>
        <script>
            // Logika untuk Modal Edit
            const editModal = document.getElementById('editModal');
            editModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const userNama = button.getAttribute('data-user-nama');
                const userNik = button.getAttribute('data-user-nik');
                const userAlamat = button.getAttribute('data-user-alamat');
                const userJenisKelamin = button.getAttribute('data-user-jk');
                const userTanggalLahir = button.getAttribute('data-user-tgl');
                const userEmail = button.getAttribute('data-user-email');
                const userJabatan = button.getAttribute('data-user-jabatan');

                const modalTitle = editModal.querySelector('.modal-title');
                const modalForm = editModal.querySelector('#editForm');
                const modalBodyInputNama = editModal.querySelector('#nama_lengkap');
                const modalBodyInputNik = editModal.querySelector('#nik');
                const modalBodyInputAlamat = editModal.querySelector('#alamat');
                const modalBodyInputJenisKelamin = editModal.querySelector('#jenis_kelamin');
                const modalBodyInputTanggalLahir = editModal.querySelector('#tanggal_lahir');
                const modalBodyInputEmail = editModal.querySelector('#email');
                const modalBodyInputJabatan = editModal.querySelector('#jabatan');

                modalTitle.textContent = `Edit Akun: ${userNama}`;
                modalForm.action = `/accounts/${userId}`; // Ubah URL action form
                modalBodyInputNama.value = userNama;
                modalBodyInputNik.value = userNik;
                modalBodyInputAlamat.value = userAlamat;
                modalBodyInputJenisKelamin.value = userJenisKelamin;
                modalBodyInputTanggalLahir.value = userTanggalLahir;
                modalBodyInputEmail.value = userEmail;
                modalBodyInputJabatan.value = userJabatan;
            });

            // Logika untuk Modal Hapus
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const modalForm = deleteModal.querySelector('#deleteForm');

                modalForm.action = `/accounts/${userId}`;
            });

            // Buat satu modal global di luar fungsi (di atas semua script)
            let qrModalInstance = null;

            function reset2FA(userId) {
                const resetModalEl = document.getElementById('reset2FAModal');
                const resetForm = document.getElementById('reset2FAForm');

                // Mengarahkan form ke ID user yang dipilih
                resetForm.action = `/accounts/${userId}/2fa/reset`;

                // Tampilkan modal secara manual
                const resetModal = new bootstrap.Modal(resetModalEl);
                resetModal.show();
            }

            function show2FAQRCode(userId) {
                const qrModalEl = document.getElementById('qrModal');
                const qrModalBody = document.getElementById('qrModalBody');

                // Reset isi konten
                qrModalBody.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Memuat...</span>
        </div>
    `;

                // Cek dan inisialisasi hanya sekali
                if (!qrModalInstance) {
                    qrModalInstance = new bootstrap.Modal(qrModalEl);
                }

                // Tampilkan modal
                qrModalInstance.show();

                // Ambil data QR dari server
                fetch(`/accounts/${userId}/2fa/show`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Gagal mengambil data QR Code.');
                        return response.json();
                    })
                    .then(data => {
                        if (!data.qr || !data.secret) throw new Error('Data tidak lengkap.');
                        qrModalBody.innerHTML = `
            <p>Scan QR Code di Google Authenticator Anda:</p>
            <div class="bg-white border rounded p-3 mb-3 d-inline-block">
                ${data.qr}
            </div>
            <p class="mb-0">Atau masukkan kode manual:</p>
            <code class="d-block fw-bold text-danger mt-2">${data.secret}</code>
        `;
                    })
                    .catch(error => {
                        qrModalBody.innerHTML = `<div class="alert alert-danger text-center">${error.message}</div>`;
                    });
            }
        </script>
    @endsection
</x-layouts.app>
