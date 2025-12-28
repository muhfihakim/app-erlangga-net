<x-layouts.app>
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <h3><i class="bi bi-clock-history text-danger"></i> Laporan Maintenance Expired</h3>
            </div>
        </div>
        <div class="app-content">
            <div class="card card-danger card-outline">
                <div class="card-body">
                    <form method="GET" action="{{ route('activitylog.maintenance_expired') }}">
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
                                <button type="submit" class="btn btn-danger w-100">Filter</button>
                                @if ($start && $end)
                                    <a href="{{ route('activitylog.maintenance_expired.export', ['start_date' => $start, 'end_date' => $end, 'teknisi_id' => $teknisiId]) }}"
                                        class="btn btn-secondary w-100">PDF</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Waktu Terdeteksi</th>
                                    <th>Teknisi Terkait</th>
                                    <th>Detail Maintenance</th>
                                    <th>Waktu Delegasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $log->logged_at->format('d-m-Y H:i') }}</td>
                                        <td class="text-danger"><strong>{{ $log->user->nama_lengkap ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            ID Maintenance: {{ $log->data['maintenance_id'] ?? '-' }} <br>
                                            Pelanggan: <strong>{{ $log->data['customer_name'] ?? '-' }}</strong>
                                        </td>
                                        <td>
                                            {{ isset($log->data['delegated_at']) ? \Carbon\Carbon::parse($log->data['delegated_at'])->format('d-m-Y H:i') : '-' }}
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
</x-layouts.app>
