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
                        <h3 class="mb-0">Laporan Pre-Order Exp</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Laporan PO Expired</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <!-- Filter Card -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Filter Laporan</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('activitylog.expired') }}">
                        <div class="row align-items-end g-3">
                            <div class="col-md-3">
                                <label>Pilih Teknisi</label>
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
                                <label>Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ $start }}">
                            </div>
                            <div class="col-md-3">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $end }}">
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                                @if ($start && $end)
                                    <a href="{{ route('activitylog.expired.export', ['start_date' => $start, 'end_date' => $end, 'teknisi_id' => $teknisiId]) }}"
                                        class="btn btn-success w-100">
                                        <i class="bi bi-filetype-pdf"></i> Export PDF
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="poExpiredTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle">No</th>
                                    <th style="text-align: center; vertical-align: middle">Teknisi</th>
                                    <th style="text-align: center; vertical-align: middle">Detail Pre-Order</th>
                                    <th style="text-align: center; vertical-align: middle">Waktu Delegasi</th>
                                    <th style="text-align: center; vertical-align: middle">Waktu Expired</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    @php
                                        // Pastikan data diproses sebagai array
                                        $data = is_array($log->data) ? $log->data : json_decode($log->data, true);
                                    @endphp
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle">{{ $loop->iteration }}
                                        </td>
                                        <td style="text-align: center; vertical-align: middle">
                                            {{ $log->user->nama_lengkap ?? 'N/A' }}
                                        </td>
                                        <td>
                                            @if (isset($data['nama_lengkap']))
                                                <span class="badge bg-info">Pelanggan:
                                                    {{ $data['nama_lengkap'] }}</span><br>
                                                <small class="text-muted">PO ID: #{{ $data['po_id'] ?? '-' }}</small>
                                            @else
                                                {{ $log->description }}
                                            @endif
                                        </td>
                                        <td class="text-center" style="vertical-align: middle">
                                            {{ isset($data['delegated_at']) ? \Carbon\Carbon::parse($data['delegated_at'])->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td style="text-align: center; vertical-align: middle">
                                            {{ $log->logged_at->format('d/m/Y H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.min.js') }}"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('dist/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('dist/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('dist/js/responsive.bootstrap4.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                $('#poExpiredTable').DataTable({
                    "responsive": true,
                    "ordering": false,
                    "pageLength": 10
                });
            });
        </script>
    @endsection
</x-layouts.app>
