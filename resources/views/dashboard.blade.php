<x-layouts.app>
    @section('Css')
        <!-- Ionicons -->
        <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
        <!-- DataTables -->
        <link rel="stylesheet" href="{{ asset('dist/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/responsive.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/buttons.bootstrap4.min.css') }}">
    @endsection
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">Dashboard</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="app-content">
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Small boxes (Stat box) -->
                    <div class="row">
                        @if ($user->jabatan !== 'Teknisi')

                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-info">
                                    <div class="inner text-center">
                                        <!-- Menampilkan Total PO Bulan Ini -->
                                        <h3>{{ $totalPoThisMonth }}</h3>

                                        <p>Total PO Bulan Ini</p>
                                    </div>
                                    <a href="#" class="small-box-footer">Lihat Detail <i
                                            class="fas fa-arrow-circle-right"></i></a>
                                </div>
                            </div>
                            <!-- ./col -->
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-warning">
                                    <div class="inner text-center">
                                        <!-- Menampilkan Total PO Bulan Sebelumnya dan Persentase Perubahan -->
                                        <h3>{{ $totalPoPreviousMonth }}
                                            <!-- Menampilkan persentase perubahan di sini -->
                                            <sup style="font-size: 20px;">
                                                @if ($percentageChange > 0)
                                                    +{{ round($percentageChange, 1) }}% <i class="fas fa-arrow-up"></i>
                                                @elseif ($percentageChange < 0)
                                                    {{ round($percentageChange, 1) }}% <i class="fas fa-arrow-down"></i>
                                                @else
                                                    0%
                                                @endif
                                            </sup>
                                        </h3>
                                        <p>Total PO Bulan Sebelumnya</p>
                                    </div>
                                    <a href="#" class="small-box-footer">Lihat Detail <i
                                            class="fas fa-arrow-circle-right"></i></a>
                                </div>
                            </div>
                            <!-- ./col -->
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner text-center">
                                        <h3>{{ $totalAktivasiThisMonth }}</h3>
                                        <p>Total Aktivasi Bulan Ini</p>
                                    </div>
                                    <a href="#" class="small-box-footer">Lihat Detail <i
                                            class="fas fa-arrow-circle-right"></i></a>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner text-center">
                                        <h3>{{ $totalPendingThisMonth }}</h3>
                                        <p>Total Pending Bulan Ini</p>
                                    </div>
                                    <a href="#" class="small-box-footer">Lihat Detail <i
                                            class="fas fa-arrow-circle-right"></i></a>
                                </div>
                            </div>
                        @endif
                    </div>
                    @if ($user->jabatan === 'Administrator')
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Aktivitas Terbaru</h3>
                            </div>
                            <div class="card-body">
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
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row">
                        @if (Auth::user()->jabatan === 'Teknisi')
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info elevation-1">
                                        {{ $aktivasiMingguIni ?? 0 }}
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Aktivasi Minggu Ini</span>
                                        <span class="info-box-number"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon bg-success elevation-1">
                                        {{ $aktivasiBulanIni ?? 0 }}
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Aktivasi Bulan Ini</span>
                                        <span class="info-box-number"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon bg-warning elevation-1">
                                        {{ $pendingTugas ?? 0 }}
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Pending</span>
                                        <span class="info-box-number"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon bg-danger elevation-1">
                                        {{ $totalExpired ?? 0 }}
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Expired</span>
                                        <span class="info-box-number"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h4><i class="fas fa-tasks text-primary"></i> Tugas Delegasi Khusus Anda</h4>
                                    <p class="text-muted">Daftar Pre-Order yang ditugaskan kepada Anda dan belum
                                        diaktivasi.</p>
                                </div>
                            </div>
                            <div class="row">
                                @forelse ($delegatedTasks as $task)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card {{ $task->bg_color ?? 'bg-light' }}">
                                            <div class="card-header border-0">
                                                <h5 class="card-title text-uppercase fw-bold">
                                                    {{ Str::limit($task->nama_lengkap, 25) }}</h5>
                                                <div class="card-tools">
                                                    <span
                                                        class="badge {{ $task->bg_color ?? 'bg-secondary' }}">{{ $task->status_display }}</span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text mb-1">
                                                    <i class="bi bi-wifi"></i> Paket: {{ $task->paket_internet }}
                                                </p>
                                                <p class="card-text mb-1">
                                                    <i class="bi bi-geo-alt"></i> Alamat:
                                                    {{ Str::limit($task->alamat_lengkap, 35) }}
                                                </p>
                                                <p class="card-text mb-1">
                                                    <i class="bi bi-whatsapp"></i> WhatsApp:
                                                    {{ $task->no_whatsapp }}
                                                </p>
                                                <hr class="my-2">
                                                <div class="row small">
                                                    <div class="col-4 text-center">
                                                        <strong>Tgl PO</strong><br>
                                                        <span
                                                            class="fw-bold">{{ \Carbon\Carbon::parse($task->created_at)->format('d-m-Y') }}</span>
                                                    </div>
                                                    <div class="col-4 text-center">
                                                        <strong>Tgl Delegasi</strong><br>
                                                        <span
                                                            class="fw-bold">{{ \Carbon\Carbon::parse($task->delegated_at)->format('d-m-Y') }}</span>
                                                    </div>
                                                    <div class="col-4 text-center">
                                                        <strong>Deadline</strong><br>
                                                        <span
                                                            class="fw-bold">{{ $task->deadline_date ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer text-center">
                                                <a href="{{ route('customers.index') }}" class="btn btn-sm">
                                                    Lihat Detail Tugas
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-success text-center">
                                            <i class="fas fa-check-circle"></i> Tidak ada tugas delegasi khusus
                                            saat
                                            ini.
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        @endif
                    </div>
            </section>
            <!-- /.content -->
        </div>
    </main>
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
    @endsection
</x-layouts.app>
