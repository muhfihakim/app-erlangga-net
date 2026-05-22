<x-layouts.app>
    @section('Css')
        <!-- DataTables -->
        <link rel="stylesheet" href="{{ asset('dist/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/responsive.bootstrap4.min.css') }}">
    @endsection

    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
                <p class="text-slate-500 text-sm">Welcome back, <span class="font-semibold text-slate-700">{{ Auth::user()->nama_lengkap }}</span></p>
            </div>
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm text-slate-500">
                    <li><a href="#" class="hover:text-primary transition-colors">Home</a></li>
                    <li class="flex items-center space-x-2 text-slate-400">
                        <i class="bi bi-chevron-right text-[10px]"></i>
                        <span class="text-slate-900 font-medium">Dashboard</span>
                    </li>
                </ol>
            </nav>
        </div>

        @if ($user->jabatan !== 'Teknisi')
            <!-- Admin/Management Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                <!-- Total PO Bulan Ini -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="bi bi-receipt text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">Total PO Bulan Ini</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-0.5 leading-none">{{ $totalPoThisMonth }}</h3>
                    </div>
                </div>

                <!-- Total PO Bulan Sebelumnya -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <i class="bi bi-clock-history text-2xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">PO Bulan Sebelumnya</p>
                        <div class="flex items-baseline gap-2 mt-0.5">
                            <h3 class="text-2xl font-bold text-slate-900 leading-none">{{ $totalPoPreviousMonth }}</h3>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $percentageChange >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                @if ($percentageChange > 0)
                                    +{{ round($percentageChange, 1) }}%
                                @elseif ($percentageChange < 0)
                                    {{ round($percentageChange, 1) }}%
                                @else
                                    0%
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Total Aktivasi Bulan Ini -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <i class="bi bi-check-circle text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">Aktivasi Bulan Ini</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-0.5 leading-none">{{ $totalAktivasiThisMonth }}</h3>
                    </div>
                </div>

                <!-- Total Pending Bulan Ini -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <i class="bi bi-hourglass-split text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">Pending Bulan Ini</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-0.5 leading-none">{{ $totalPendingThisMonth }}</h3>
                    </div>
                </div>
            </div>
        @endif

        @if ($user->jabatan === 'Administrator')
            <!-- Activity Log Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-activity text-primary"></i>
                        Aktivitas Terbaru
                    </h3>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-white px-2 py-1 rounded-md border border-slate-100">Log Sistem</span>
                </div>
                <div class="p-0">
                    <div class="overflow-x-auto">
                        <table id="activity-log-table" class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr class="text-slate-500 border-b border-slate-100">
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-[11px] text-center">Waktu</th>
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">User</th>
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-[11px] text-center">Aktivitas</th>
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse ($logs as $log)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-slate-500 text-center font-medium tabular-nums">
                                            {{ $log->logged_at->format('H:i:s d-m-Y') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-[10px] font-bold border border-slate-200">
                                                    {{ strtoupper(substr($log->user->nama_lengkap ?? 'U', 0, 1)) }}
                                                </div>
                                                <span class="font-semibold text-slate-700">{{ $log->user->nama_lengkap ?? 'Unknown' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100">
                                                {{ $log->activity_type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600">
                                            {{ $log->description }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center gap-2">
                                                <i class="bi bi-inbox text-4xl text-slate-200"></i>
                                                <p class="text-slate-400 font-medium">Belum ada aktivitas yang tercatat.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if (Auth::user()->jabatan === 'Teknisi')
            <!-- Teknisi Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="bi bi-calendar-event text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">Aktivasi Minggu Ini</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-0.5 leading-none">{{ $aktivasiMingguIni ?? 0 }}</h3>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <i class="bi bi-check-all text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">Aktivasi Bulan Ini</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-0.5 leading-none">{{ $aktivasiBulanIni ?? 0 }}</h3>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <i class="bi bi-pause-circle text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">Total Pending</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-0.5 leading-none">{{ $pendingTugas ?? 0 }}</h3>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <i class="bi bi-exclamation-triangle text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">Total Expired</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-0.5 leading-none">{{ $totalExpired ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <!-- Delegated Tasks Section -->
            <div class="mt-8 space-y-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                        <i class="bi bi-list-task text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Tugas Delegasi Khusus</h2>
                        <p class="text-sm text-slate-500">Daftar Pre-Order yang ditugaskan kepada Anda</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse ($delegatedTasks as $task)
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-all duration-300 group">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-6">
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-slate-900 group-hover:text-primary transition-colors truncate pr-2" title="{{ $task->nama_lengkap }}">
                                            {{ $task->nama_lengkap }}
                                        </h3>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="inline-block w-2 h-2 rounded-full {{ $task->bg_color === 'bg-danger' ? 'bg-rose-500' : ($task->bg_color === 'bg-warning' ? 'bg-amber-500' : 'bg-slate-400') }}"></span>
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                {{ $task->status_display }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                                        <i class="bi bi-person-badge text-lg"></i>
                                    </div>
                                </div>
                                
                                <div class="space-y-3.5 mb-6">
                                    <div class="flex items-center gap-3 text-sm text-slate-600">
                                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center shrink-0">
                                            <i class="bi bi-wifi text-slate-400"></i>
                                        </div>
                                        <span class="font-medium truncate">{{ $task->paket_internet }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm text-slate-600">
                                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center shrink-0">
                                            <i class="bi bi-geo-alt text-slate-400"></i>
                                        </div>
                                        <span class="truncate" title="{{ $task->alamat_lengkap }}">{{ Str::limit($task->alamat_lengkap, 35) }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm text-slate-600">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                                            <i class="bi bi-whatsapp text-emerald-500"></i>
                                        </div>
                                        <span class="font-semibold tabular-nums">{{ $task->no_whatsapp }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-2 py-4 border-t border-slate-50 text-center">
                                    <div class="space-y-1">
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">PO</p>
                                        <p class="text-xs font-bold text-slate-700">{{ \Carbon\Carbon::parse($task->created_at)->format('d/m/y') }}</p>
                                    </div>
                                    <div class="space-y-1 border-x border-slate-50 px-1">
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Delegasi</p>
                                        <p class="text-xs font-bold text-slate-700">{{ \Carbon\Carbon::parse($task->delegated_at)->format('d/m/y') }}</p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Deadline</p>
                                        <p class="text-xs font-bold text-rose-600">{{ $task->deadline_date ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 group-hover:bg-slate-50 transition-colors">
                                <a href="{{ route('customers.index') }}" class="flex items-center justify-center gap-2 text-sm font-bold text-primary hover:text-primary-dark transition-colors">
                                    Lihat Detail Tugas
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-16 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 text-center">
                            <div class="mx-auto w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center mb-4">
                                <i class="bi bi-patch-check text-3xl text-emerald-500"></i>
                            </div>
                            <h3 class="text-slate-900 text-lg font-bold">Semua Selesai!</h3>
                            <p class="text-slate-500 max-w-xs mx-auto mt-1">Tidak ada tugas delegasi saat ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('dist/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('dist/js/dataTables.responsive.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                if ($('#activity-log-table').length) {
                    $('#activity-log-table').DataTable({
                        "pageLength": 10,
                        "lengthChange": false,
                        "ordering": false,
                        "info": false,
                        "responsive": true,
                        "language": {
                            "search": "",
                            "searchPlaceholder": "Cari log..."
                        }
                    });
                    
                    $('.dataTables_filter input').addClass('bg-slate-50 border-slate-100 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all mb-4');
                }
            });
        </script>
    @endsection
</x-layouts.app>
