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
                            <li class="breadcrumb-item active" aria-current="page">Daftar Layanan Bot</li>
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
                    <h3 class="card-title">Pertanyaan untuk Layanan: {{ $service->name }}</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    {{-- <a href="{{ route('services.create') }}" class="btn btn-success mb-3">+ Tambah Layanan</a> --}}
                    <button type="button" class="btn btn-success mb-3 btn-sm" data-bs-toggle="modal"
                        data-bs-target="#addQuestionModal">
                        <i class="bi bi-plus-square"></i> Tambah Pertanyaan
                    </button>
                    <a href="{{ route('services.index') }}" class="btn btn-secondary mb-3 btn-sm">← Kembali ke
                        Layanan</a>
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="text-center align-middle">Urutan</th>
                                <th class="text-center align-middle">Pertanyaan</th>
                                <th class="text-center align-middle">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($questions as $q)
                                <tr>
                                    <td>{{ $q->order }}</td>
                                    <td>{{ $q->question_text }}</td>
                                    <td class="text-center">
                                        {{-- Tombol Edit memicu modal --}}
                                        <button type="button" class="btn btn-sm btn-warning edit-question-btn"
                                            data-bs-toggle="modal" data-bs-target="#editQuestionModal"
                                            data-action="{{ route('questions.update', $q) }}"
                                            data-text="{{ $q->question_text }}" data-order="{{ $q->order }}"
                                            data-type="{{ $q->question_type }}">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                        {{-- Form Hapus dengan class untuk SweetAlert --}}
                                        <form method="POST" action="{{ route('questions.destroy', $q) }}"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger btn-hapus-pertanyaan">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="modal fade" id="addQuestionModal" tabindex="-1" aria-labelledby="addQuestionModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addQuestionModalLabel">Tambah Pertanyaan Baru</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form method="POST" action="{{ route('questions.store') }}">
                                    @csrf
                                    <input type="hidden" name="service_id" value="{{ $service->id }}">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="question_type" class="form-label">Jenis Pertanyaan</label>
                                            <select id="question_type" name="question_type" class="form-control"
                                                required>
                                                <option value="text">Teks</option>
                                                <option value="attachment">Lampiran / Media</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="question_text" class="form-label">Teks Pertanyaan</label>
                                            <textarea id="question_text" name="question_text" class="form-control" rows="3"
                                                placeholder="Masukkan Teks Pertanyaan Bot Anda" autocomplete="off" required>{{ old('question_text') }} </textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="order" class="form-label">Urutan</label>
                                            <input type="number" id="order" name="order" class="form-control"
                                                value="{{ old('order') }}"
                                                placeholder="Masukkan Urutan Pertanyaan Bot Anda" autocomplete="off"
                                                required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>



                    <div class="modal fade" id="editQuestionModal" tabindex="-1"
                        aria-labelledby="editQuestionModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editQuestionModalLabel">Edit Pertanyaan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="editQuestionForm" method="POST" action="">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="edit_question_type" class="form-label">Jenis
                                                Pertanyaan</label>
                                            <select id="edit_question_type" name="question_type" class="form-control"
                                                required>
                                                <option value="text"
                                                    {{ old('question_type') === 'text' ? 'selected' : '' }}>Teks
                                                </option>
                                                <option value="attachment"
                                                    {{ old('question_type') === 'attachment' ? 'selected' : '' }}>
                                                    Lampiran / Media</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_question_text" class="form-label">Teks Pertanyaan</label>
                                            <textarea id="edit_question_text" name="question_text" class="form-control" rows="3"
                                                placeholder="Masukkan Teks Pertanyaan Bot Anda" autocomplete="off" required>{{ old('question_text') }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_order" class="form-label">Urutan</label>
                                            <input type="number" id="edit_order" name="order"
                                                class="form-control" value="{{ old('order') }}"
                                                placeholder="Masukkan Urutan Pertanyaan Bot Anda" autocomplete="off"
                                                required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary btn-sm">Simpan
                                            Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
            });
        </script>
        <script>
            const editQuestionModal = document.getElementById('editQuestionModal');
            if (editQuestionModal) {
                editQuestionModal.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;

                    // Ambil data dari tombol edit
                    const actionUrl = button.getAttribute('data-action');
                    const questionText = button.getAttribute('data-text');
                    const questionOrder = button.getAttribute('data-order');
                    const questionType = button.getAttribute('data-type');

                    // Ambil elemen form di modal
                    const modalForm = editQuestionModal.querySelector('#editQuestionForm');
                    const textInput = editQuestionModal.querySelector('#edit_question_text');
                    const orderInput = editQuestionModal.querySelector('#edit_order');
                    const typeInput = editQuestionModal.querySelector('#edit_question_type');

                    // Set data ke form
                    modalForm.action = actionUrl;
                    textInput.value = questionText;
                    orderInput.value = questionOrder;
                    typeInput.value = questionType;
                });

                // Reset isi modal setiap kali ditutup agar tidak tertinggal data lama
                editQuestionModal.addEventListener('hidden.bs.modal', () => {
                    const modalForm = editQuestionModal.querySelector('#editQuestionForm');
                    modalForm.reset();
                });
            }

            // === SCRIPT UNTUK KONFIRMASI HAPUS PERTANYAAN ===
            document.querySelectorAll('.btn-hapus-pertanyaan').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const form = this.closest('form');
                    Swal.fire({
                        title: 'Anda Yakin?',
                        text: "Pertanyaan ini akan dihapus secara permanen.",
                        icon: 'warning',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-danger btn-sm mx-1',
                            cancelButton: 'btn btn-secondary btn-sm mx-1'
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        </script>
        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @if (session('alert.config'))
            <script>
                Swal.fire({!! session('alert.config') !!});
            </script>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
    @endsection
</x-layouts.app>
