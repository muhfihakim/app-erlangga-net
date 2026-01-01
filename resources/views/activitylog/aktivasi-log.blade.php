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
                        <h3 class="mb-0">Laporan Aktivasi</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Laporan Aktivasi</li>
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
                    <form method="GET" action="{{ route('activitylog.aktivasi') }}">
                        <div class="row align-items-end g-3">
                            <div class="col-md-3">
                                <label class="form-label">Teknisi</label>
                                <select name="teknisi_id" class="form-control">
                                    <option value="">-- Semua Teknisi --</option>
                                    @foreach ($teknisis as $t)
                                        <option value="{{ $t->id }}"
                                            {{ $teknisiId == $t->id ? 'selected' : '' }}>
                                            {{ $t->nama_lengkap }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                    value="{{ $start }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" name="end_date" id="end_date" class="form-control"
                                    value="{{ $end }}">
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i>
                                    Filter</button>
                                @if ($start && $end)
                                    <a href="{{ route('activitylog.aktivasi.export', ['start_date' => $start, 'end_date' => $end, 'teknisi_id' => $teknisiId]) }}"
                                        class="btn btn-success w-100 text-nowrap"><i class="bi bi-filetype-pdf"></i>
                                        Export PDF</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="aktivasi-table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle">No</th>
                                    <th style="text-align: center; vertical-align: middle">Tgl. Aktivasi</th>
                                    <th style="text-align: center; vertical-align: middle">Nama Pelanggan</th>
                                    <th style="text-align: center; vertical-align: middle">Paket</th>
                                    <th style="text-align: center; vertical-align: middle">Teknisi Utama</th>
                                    <th style="text-align: center; vertical-align: middle">Partner</th>
                                    <th style="text-align: center; vertical-align: middle">Billing ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $c)
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle">{{ $loop->iteration }}
                                        </td>
                                        <td style="text-align: center; vertical-align: middle">
                                            {{ $c->created_at->format('d-m-Y H:i') }}</td>
                                        <td>{{ $c->nama_lengkap }}</td>
                                        <td style="text-align: center; vertical-align: middle">{{ $c->paket_internet }}
                                        </td>
                                        <td style="text-align: center; vertical-align: middle">
                                            {{ $c->teknisiUtama->nama_lengkap ?? 'N/A' }}</td>
                                        <td style="text-align: center; vertical-align: middle">{{ $c->partner ?: '-' }}
                                        </td>
                                        <td style="text-align: center; vertical-align: middle">{{ $c->id_pelanggan }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data ditemukan untuk periode
                                            ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
                $('#aktivasi-table').DataTable({
                    "responsive": true,
                    "ordering": false,
                    "pageLength": 10
                });

                const startDate = document.getElementById('start_date');
                const endDate = document.getElementById('end_date');
                startDate.addEventListener('change', function() {
                    endDate.min = this.value;
                });
            });
        </script>
    @endsection
</x-layouts.app>
