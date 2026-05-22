<x-layouts.app>
    @section('Css')
        <style>
            /* Toolbar Styling */
            .table-toolbar {
                background: #fff; border: 1px solid #e2e8f0;
                border-radius: 14px; padding: 1rem 1.25rem;
                display: flex; align-items: center; justify-content: space-between;
                gap: 12px; flex-wrap: wrap; margin-bottom: 1rem;
                box-shadow: 0 1px 6px rgba(0,0,0,.05);
            }
            .search-wrap { position: relative; flex: 1; min-width: 200px; max-width: 360px; }
            .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px; pointer-events: none; }
            .search-input {
                width: 100%; padding: 9px 12px 9px 36px;
                border: 2px solid #e2e8f0; border-radius: 10px;
                font-size: 13px; font-family: 'Inter', sans-serif; color: #374151;
                outline: none; transition: all .25s;
                background: #f8fafc;
            }
            .search-input:focus { border-color: #0ea5e9; background: #fff; }
            .search-input::placeholder { color: #94a3b8; }
        </style>
    @endsection

    <!-- Page header -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Dashboard</h1>
            <p style="font-size:13px;color: #64748b;margin:3px 0 0;">Selamat datang kembali, <strong>{{ Auth::user()->nama_lengkap }}</strong>.</p>
        </div>
    </div>

    <!-- Stats cards -->
    @if ($user->jabatan !== 'Teknisi')
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $totalPoThisMonth }}</div>
                    <div class="stat-lbl">Total PO Bulan Ini</div>
                    <div class="stat-trend {{ $percentageChange >= 0 ? 'stat-up' : 'stat-down' }}">
                        <i class="fa-solid {{ $percentageChange >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i> 
                        {{ round(abs($percentageChange), 1) }}% dibanding bulan lalu
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $totalPoPreviousMonth }}</div>
                    <div class="stat-lbl">PO Bulan Sebelumnya</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $totalAktivasiThisMonth }}</div>
                    <div class="stat-lbl">Total Aktivasi</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#fef2f2;color:#dc2626;">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $totalPendingThisMonth }}</div>
                    <div class="stat-lbl">Total Pending</div>
                </div>
            </div>
        </div>
    @endif

    @if (Auth::user()->jabatan === 'Teknisi')
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                    <i class="fa-solid fa-calendar-week"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $aktivasiMingguIni ?? 0 }}</div>
                    <div class="stat-lbl">Aktivasi Minggu Ini</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $aktivasiBulanIni ?? 0 }}</div>
                    <div class="stat-lbl">Aktivasi Bulan Ini</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                    <i class="fa-solid fa-pause-circle"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $pendingTugas ?? 0 }}</div>
                    <div class="stat-lbl">Total Pending</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef2f2;color:#dc2626;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $totalExpired ?? 0 }}</div>
                    <div class="stat-lbl">Total Expired</div>
                </div>
            </div>
        </div>
    @endif

    @if ($user->jabatan === 'Administrator')
        <div style="margin-top: 2rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                <h3 style="font-size: 16px; font-weight: 800; margin: 0;">Aktivitas Terbaru</h3>
            </div>

            <!-- Custom Toolbar -->
            <div class="table-toolbar">
                <div class="search-wrap">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="search" class="search-input" id="custom-search"
                           placeholder="Cari aktivitas, user, atau deskripsi..."
                           autocomplete="off"/>
                </div>
                <div style="display:flex;gap:8px;">
                    <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        Log Sistem
                    </div>
                </div>
            </div>

            <div class="table-card">
                <div class="table-responsive">
                    <table id="activity-log-table" class="admin-table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 50px;">#</th>
                                <th style="text-align: center; width: 180px;">Waktu</th>
                                <th style="text-align: center; width: 220px;">User</th>
                                <th style="text-align: center; width: 160px;">Aktivitas</th>
                                <th style="text-align: center;">Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                                    <td style="text-align: center; color: #64748b; font-size: 12px; font-weight: 600;">
                                        {{ $log->logged_at->format('d-m-Y H:i:s') }}
                                    </td>
                                    <td style="font-weight: 600;">
                                        <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                                            <div style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9, #7c3aed); display: flex; align-items: center; justify-content: center; font-size: 11px; color: #fff; font-weight: 800; flex-shrink: 0;">
                                                {{ strtoupper(substr($log->user->nama_lengkap ?? 'U', 0, 1)) }}
                                            </div>
                                            <span class="truncate">{{ $log->user->nama_lengkap ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align: center">
                                        <span class="badge" style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 10px;">
                                            {{ $log->activity_type }}
                                        </span>
                                    </td>
                                    <td style="text-align: center; font-size: 13px;">{{ $log->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 4rem 2rem;">
                                        <i class="fa-solid fa-inbox" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 1rem; display: block;"></i>
                                        <p style="font-weight: 600; color: #94a3b8; margin: 0;">Belum ada aktivitas yang tercatat.</p>
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
        <div style="margin-top: 2rem;">
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-list-check text-primary"></i> Tugas Delegasi Khusus Anda
            </h3>
            <p style="font-size: 13px; color: #64748b; margin-bottom: 1.5rem;">Daftar Pre-Order yang ditugaskan kepada Anda dan belum diaktivasi.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                @forelse ($delegatedTasks as $task)
                    <div class="stat-card" style="flex-direction: column; align-items: stretch; gap: 0; padding: 0; overflow: hidden;">
                        <div style="padding: 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: flex-start;">
                            <div style="min-width: 0;">
                                <h4 style="font-size: 14px; font-weight: 700; margin: 0; text-transform: uppercase;">{{ Str::limit($task->nama_lengkap, 25) }}</h4>
                                <span class="badge {{ $task->bg_color === 'bg-danger' ? 'badge-archived' : 'badge-published' }}" style="margin-top: 0.5rem; font-size: 9px;">
                                    {{ $task->status_display }}
                                </span>
                            </div>
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b;">
                                <i class="fa-solid fa-user-gear"></i>
                            </div>
                        </div>
                        <div style="padding: 1.25rem; font-size: 13px;">
                            <div style="display: flex; gap: 8px; margin-bottom: 0.75rem;">
                                <i class="fa-solid fa-wifi" style="width: 16px; color: #94a3b8;"></i>
                                <span>Paket: <strong>{{ $task->paket_internet }}</strong></span>
                            </div>
                            <div style="display: flex; gap: 8px; margin-bottom: 0.75rem;">
                                <i class="fa-solid fa-map-location-dot" style="width: 16px; color: #94a3b8;"></i>
                                <span>{{ Str::limit($task->alamat_lengkap, 35) }}</span>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <i class="fa-brands fa-whatsapp" style="width: 16px; color: #10b981;"></i>
                                <span style="font-family: monospace;">{{ $task->no_whatsapp }}</span>
                            </div>
                        </div>
                        <div style="padding: 1rem 1.25rem; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-size: 10px; color: #94a3b8;">
                                Deadline: <strong style="color: #ef4444;">{{ $task->deadline_date ?? 'N/A' }}</strong>
                            </div>
                            <a href="{{ route('customers.index') }}" class="btn-primary-admin" style="padding: 6px 12px; font-size: 11px;">
                                Detail Tugas <i class="fa-solid fa-arrow-right ml-1" style="font-size: 10px;"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; padding: 3rem; background: #fff; border-radius: 14px; text-align: center; border: 2px dashed #e2e8f0;">
                        <i class="fa-solid fa-circle-check" style="font-size: 3rem; color: #dcfce7; margin-bottom: 1rem; display: block;"></i>
                        <p style="font-weight: 600; color: #94a3b8;">Tidak ada tugas delegasi khusus saat ini.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                const table = $('#activity-log-table').DataTable({
                    "pageLength": 10,
                    "lengthChange": false,
                    "ordering": false,
                    "info": false,
                    "responsive": true,
                    "dom": 'tpir', // Hide default search box
                    "language": {
                        "zeroRecords": "Data tidak ditemukan",
                        "paginate": {
                            "previous": '<i class="fa-solid fa-chevron-left"></i>',
                            "next": '<i class="fa-solid fa-chevron-right"></i>'
                        }
                    }
                });

                // Link custom search input to DataTables
                $('#custom-search').on('keyup', function() {
                    table.search(this.value).draw();
                });
            });
        </script>
    @endsection
</x-layouts.app>
