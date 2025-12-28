<x-layouts.app>
    @section('Css')
        <link rel="stylesheet" href="{{ asset('dist/css/dataTables.bootstrap4.min.css') }}">
    @endsection

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <h3>Laporan Pengerjaan Maintenance</h3>
            </div>
        </div>
        <div class="app-content">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <form method="GET" action="{{ route('activitylog.maintenance') }}">
                        <div class="row align-items-end g-3">
                            <div class="col-md-3">
                                <label>Teknisi</label>
                                <select name="teknisi_id" class="form-control">
                                    <option value="">-- Semua Teknisi --</option>
                                    @foreach ($teknisis as $t)
                                        <option value="{{ $t->id }}"
                                            {{ $teknisiId == $t->id ? 'selected' : '' }}>{{ $t->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3"><label>Dari</label><input type="date" name="start_date"
                                    class="form-control" value="{{ $start }}"></div>
                            <div class="col-md-3"><label>Sampai</label><input type="date" name="end_date"
                                    class="form-control" value="{{ $end }}"></div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                                @if ($start && $end)
                                    <a href="{{ route('activitylog.maintenance.export', ['start_date' => $start, 'end_date' => $end, 'teknisi_id' => $teknisiId]) }}"
                                        class="btn btn-success w-100">PDF</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $total }}</h3>
                            <p>Total Pengerjaan</p>
                        </div>
                        <div class="icon"><i class="bi bi-tools"></i></div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <table id="mTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tgl Selesai</th>
                                <th>Pelanggan</th>
                                <th>Teknisi Utama</th>
                                <th>Partner</th>
                                <th>Solusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($maintenances as $m)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $m->completed_at->format('d-m-Y H:i') }}</td>
                                    <td>{{ $m->customer->nama_lengkap }}</td>
                                    <td><strong>{{ $m->teknisiEksekutor->nama_lengkap ?? 'N/A' }}</strong></td>
                                    <td>{{ $m->partner ?: '-' }}</td>
                                    <td>{{ Str::limit($m->solusi_kendala, 30) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('dist/js/dataTables.bootstrap4.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                $('#mTable').DataTable({
                    "ordering": false
                });
            });
        </script>
    @endsection
</x-layouts.app>
