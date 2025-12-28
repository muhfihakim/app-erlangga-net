<x-layouts.app>
    @section('Css')
        <link rel="stylesheet" href="{{ asset('dist/css/dataTables.bootstrap4.min.css') }}">
    @endsection

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <h3>Laporan Pencabutan Perangkat (Dismantle)</h3>
            </div>
        </div>
        <div class="app-content">
            <div class="card card-primary card-outline">
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
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                                @if ($start && $end)
                                    <a href="{{ route('activitylog.dismantle.export', ['start_date' => $start, 'end_date' => $end, 'teknisi_id' => $teknisiId]) }}"
                                        class="btn btn-success w-100"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $total }}</h3>
                            <p>Total Dismantle Selesai</p>
                        </div>
                        <div class="icon"><i class="bi bi-box-arrow-right"></i></div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <table id="dismantleTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Tgl Selesai</th>
                                <th>Pelanggan</th>
                                <th>ID Pelanggan</th>
                                <th>Teknisi</th>
                                <th>Foto Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dismantles as $d)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $d->completed_at->format('d/m/Y H:i') }}</td>
                                    <td><strong>{{ $d->customer->nama_lengkap ?? 'Data Terhapus' }}</strong></td>
                                    <td>{{ $d->customer->id_pelanggan ?? '-' }}</td>
                                    <td>{{ $d->teknisiEksekutor->nama_lengkap ?? 'N/A' }}</td>
                                    <td class="text-center">
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
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('dist/js/dataTables.bootstrap4.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                $('#dismantleTable').DataTable({
                    "ordering": false
                });
            });
        </script>
    @endsection
</x-layouts.app>
