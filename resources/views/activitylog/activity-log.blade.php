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
                            <li class="breadcrumb-item active" aria-current="page">Aktivitas Log</li>
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
                    <h3 class="card-title">Aktivitas Log</h3>
                </div>
                <div class="card-body">
                    {{-- <button type="button" class="btn btn-success mb-3 btn-sm" data-bs-toggle="modal"
                        data-bs-target="#exportLogModal">
                        <i class="bi bi-file-earmark-pdf"></i> Export PDF
                    </button> --}}
                    <a href="{{ route('dashboards.index') }}" class="btn btn-secondary mb-3 btn-sm">← Kembali</a>
                    <div class="table-responsive">
                        <table id="activity-log-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="text-align: center">Waktu</th>
                                    <th style="text-align: center">Nama User</th>
                                    <th style="text-align: center">Aktivitas</th>
                                    <th style="text-align: center">Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle">
                                            {{ $log->logged_at->format('H:i:s d-m-Y') }}
                                        </td>
                                        <td style="text-align: center; vertical-align: middle">
                                            {{ $log->user->nama_lengkap ?? 'Unknown' }}</td>
                                        <td style="text-align: center; vertical-align: middle"><span
                                                class="badge badge-info">{{ $log->activity_type }}</span>
                                        </td>
                                        <td style="vertical-align: middle">{{ $log->description }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">Belum ada aktivitas yang tercatat.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        @include('partials.modal_activity-log')
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!--end::App Main-->
    @section('Scripts')
        <!-- jQuery -->
        <script src="https://adminlte.io/themes/v3/plugins/jquery/jquery.min.js"></script>
        <!-- jQuery UI 1.11.4 -->
        <script src="https://adminlte.io/themes/v3/plugins/jquery-ui/jquery-ui.min.js"></script>
        <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
        <script>
            $.widget.bridge('uibutton', $.ui.button)
        </script>
        <!-- Bootstrap 4 -->
        <script src="https://adminlte.io/themes/v3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- jQuery -->
        <script src="{{ asset('dist/js/jquery.min.js') }}"></script>
        <!-- Bootstrap 4 -->
        <script src="{{ asset('dist/js/bootstrap.bundle.min.js') }}"></script>
        <!-- DataTables  & js -->
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('dist/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('dist/js/dataTables.responsive.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                $('#activity-log-table').DataTable({
                    "responsive": true,
                    "ordering": false,
                    "pageLength": 10
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const startDate = document.getElementById('start_date');
                const endDate = document.getElementById('end_date');

                startDate.addEventListener('change', function() {
                    endDate.min = this.value;
                });
            });
        </script>
    @endsection
</x-layouts.app>
