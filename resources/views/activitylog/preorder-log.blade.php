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
                        <h3 class="mb-0">Laporan Pre-Order</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Laporan Pre-Order</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <!-- Card Filter -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Filter Laporan</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('activitylog.preorder') }}">
                        <div class="row align-items-end g-3">
                            <div class="col-md-3">
                                <label>Pilih Sales</label>
                                <select name="sales_id" class="form-control">
                                    <option value="">-- Semua Sales --</option>
                                    @foreach ($allSales as $s)
                                        <option value="{{ $s->id }}" {{ $salesId == $s->id ? 'selected' : '' }}>
                                            {{ $s->nama_lengkap }}
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
                                    <a href="{{ route('activitylog.preorder.export', ['start_date' => $start, 'end_date' => $end, 'sales_id' => $salesId]) }}"
                                        class="btn btn-success w-100">
                                        <i class="bi bi-filetype-pdf"></i> Export PDF
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card Tabel -->
            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="preorderTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle">No</th>
                                    <th style="text-align: center; vertical-align: middle">Waktu Input</th>
                                    <th>Nama Sales</th>
                                    <th>Detail Pelanggan</th>
                                    <th>Paket</th>
                                    <th style="text-align: center; vertical-align: middle">Status PO</th>
                                </tr>
                            </thead>
                            {{-- <tbody>
                                @foreach ($preorders as $po)
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle">{{ $loop->iteration }}
                                        </td>
                                        <td style="text-align: center; vertical-align: middle">
                                            {{ $po->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $po->sales->nama_lengkap ?? $po->nama_sales }}</>
                                        </td>
                                        <td>{{ $po->nama_lengkap }}
                                        </td>
                                        <td>{{ $po->paket_internet }}</td>
                                        <td style="text-align: center; vertical-align: middle">
                                            @if ($po->id_teknisi_delegasi)
                                                <span class="badge bg-success">Sudah Didelegasi</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Menunggu Delegasi</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody> --}}
                            <tbody>
                                @foreach ($preorders as $po)
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle">{{ $loop->iteration }}
                                        </td>
                                        <td style="text-align: center; vertical-align: middle">
                                            {{ optional($po->created_at)->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>{{ $po->sales->nama_lengkap ?? $po->nama_sales }}</td>
                                        <td>
                                            <strong>{{ $po->nama_lengkap }}</strong><br>
                                            <small class="text-muted">{{ $po->no_whatsapp }}</small>
                                        </td>
                                        <td>{{ $po->paket_internet }}</td>
                                        <td style="text-align: center; vertical-align: middle">
                                            @if ($po->po_status === 'Aktif')
                                                <span class="badge bg-success"><i class="bi bi-check-circle"></i>
                                                    Aktif</span>
                                            @elseif ($po->po_status === 'Berhenti')
                                                <span class="badge bg-secondary"><i class="bi bi-person-x"></i>
                                                    Berhenti</span>
                                            @else
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock"></i>
                                                    Pending</span>
                                            @endif
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
        <script>
            $(document).ready(function() {
                $('#preorderTable').DataTable({
                    "responsive": true,
                    "ordering": false
                });
            });
        </script>
    @endsection
</x-layouts.app>
