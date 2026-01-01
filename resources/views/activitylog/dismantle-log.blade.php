<x-layouts.app>
    @section('Css')
        <link rel="stylesheet" href="{{ asset('dist/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/responsive.bootstrap4.min.css') }}">
    @endsection

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">Laporan Dismantle</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Laporan Dismantle</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Filter Laporan</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('activitylog.dismantle') }}">
                        <div class="row align-items-end g-3">
                            <div class="col-md-3">
                                <label>Pilih Teknisi</label>
                                <select name="teknisi_id" class="form-control">
                                    <option value="">-- Semua Teknisi --</option>
                                    @foreach ($teknisis as $t)
                                        <option value="{{ $t->id }}"
                                            {{ $teknisiId == $t->id ? 'selected' : '' }}>{{ $t->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ $start }}">
                            </div>
                            <div class="col-md-3">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $end }}">
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i>
                                    Filter</button>
                                @if ($start && $end)
                                    <a href="{{ route('activitylog.dismantle.export', ['start_date' => $start, 'end_date' => $end, 'teknisi_id' => $teknisiId]) }}"
                                        class="btn btn-success w-100"><i class="bi bi-filetype-pdf"></i> Export PDF</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- <div class="row mt-3">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $total }}</h3>
                            <p>Total Dismantle Selesai</p>
                        </div>
                        <div class="icon"><i class="bi bi-box-arrow-right"></i></div>
                    </div>
                </div>
            </div> --}}

            <div class="card mt-3">
                <div class="card-body">
                    <table id="dismantleTable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="text-align: center; vertical-align: middle">No</th>
                                <th style="text-align: center; vertical-align: middle">Tgl. Selesai</th>
                                <th style="text-align: center; vertical-align: middle">Pelanggan</th>
                                <th style="text-align: center; vertical-align: middle">ID Pelanggan</th>
                                <th style="text-align: center; vertical-align: middle">Teknisi</th>
                                <th style="text-align: center; vertical-align: middle">Foto Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dismantles as $d)
                                <tr>
                                    <td style="text-align: center; vertical-align: middle">{{ $loop->iteration }}</td>
                                    <td style="text-align: center; vertical-align: middle">
                                        {{ $d->completed_at->format('d-m-Y H:i') }}</td>
                                    <td>{{ $d->customer->nama_lengkap ?? 'Data Terhapus' }}</td>
                                    <td style="text-align: center; vertical-align: middle">
                                        {{ $d->customer->id_pelanggan ?? '-' }}</td>
                                    <td style="text-align: center; vertical-align: middle">
                                        {{ $d->teknisiEksekutor->nama_lengkap ?? 'N/A' }}</td>
                                    <td style="text-align: center; vertical-align: middle">
                                        <a href="{{ asset('storage/' . $d->foto_pencabutan) }}" target="_blank"
                                            class="btn btn-xs btn-outline-info">Lihat Foto</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
        <script>
            $(document).ready(function() {
                $('#dismantleTable').DataTable({
                    "responsive": true,
                    "ordering": false,
                    "pageLength": 10
                });
            });
        </script>
    @endsection
</x-layouts.app>
