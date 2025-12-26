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
                    <h3 class="card-title">Laporan PO Expired Delegasi</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('activitylog.expired') }}">
                        <div class="row align-items-end g-2"> {{-- g-2 = gap antar kolom --}}
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ $start }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $end }}">
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                {{-- Tombol Filter --}}
                                <button type="submit" class="btn btn-primary">Filter</button>

                                {{-- Tombol Export hanya muncul jika ada tanggal --}}
                                @if ($start && $end)
                                    <a href="{{ route('activitylog.expired.export', [
                                        'start_date' => $start,
                                        'end_date' => $end,
                                        'teknisi_id' => $teknisiId,
                                    ]) }}"
                                        class="btn btn-success">Export PDF</a>
                                @endif
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table id="activity-log-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Waktu Log</th>
                                    <th>Teknisi</th>
                                    <th>PO ID</th>
                                    <th>Delegated At</th>
                                    <th>Expired At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $log->logged_at->format('d-m-Y H:i:s') }}</td>
                                        <td>{{ $log->user->nama_lengkap }}</td>
                                        <td>{{ $log->data['po_id'] ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($log->data['delegated_at'])->format('d-m-Y H:i') }}
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($log->data['expired_at'])->format('d-m-Y H:i') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data.</td>
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
                    "pageLength": 20,
                    "lengthChange": false,
                    "ordering": false,
                    "info": false
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
