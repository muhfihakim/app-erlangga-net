<x-layouts.app>
    @section('Css')
        <style>
            #preorderTable thead th, #preorderTable tbody td { text-transform: uppercase !important; font-weight: 600; font-size: 12px; }
        </style>
    @endsection

    <!-- Page header -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Laporan Pre-Order</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Riwayat pendaftaran calon pelanggan internet.</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if ($isFiltered)
                <a href="{{ route('activitylog.preorder.export', ['start_date' => $start, 'end_date' => $end, 'sales_id' => $salesId]) }}"
                    class="btn-primary-admin !flex !items-center !justify-center gap-2 !bg-rose-600">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            @endif
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('activitylog.preorder') }}" class="w-full">
            <div class="filter-group">
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label-custom">Sales</label>
                    <select name="sales_id" class="form-input-custom">
                        <option value="">-- Semua Sales --</option>
                        @foreach ($allSales as $s)
                            <option value="{{ $s->id }}" {{ $salesId == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="width: 180px;">
                    <label class="form-label-custom">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-input-custom" value="{{ $start }}">
                </div>
                <div style="width: 180px;">
                    <label class="form-label-custom">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-input-custom" value="{{ $end }}">
                </div>
                <button type="submit" class="btn-primary-admin !h-10 !px-6 !text-[13px] !w-fit flex items-center gap-2">
                    <i class="fa-solid fa-filter"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="preorderTable" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: center; width: 160px;">Waktu Input</th>
                        <th style="text-align: center;">Nama Sales</th>
                        <th style="text-align: center;">Detail Pelanggan</th>
                        <th style="text-align: center; width: 120px;">Paket</th>
                        <th style="text-align: center; width: 150px;">Status PO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($preorders as $po)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                            <td style="text-align: center; font-size: 12px; font-weight: 700; color: #0f172a;">
                                {{ optional($po->created_at)->format('d/m/Y') }}
                                <div style="font-size: 10px; color: #94a3b8; font-weight: 600;">{{ optional($po->created_at)->format('H:i') }} WIB</div>
                            </td>
                            <td style="text-align: center; font-weight: 600;">{{ $po->sales->nama_lengkap ?? $po->nama_sales }}</td>
                            <td style="text-align: left;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $po->nama_lengkap }}</div>
                                <div style="font-size: 11px; color: #64748b;">WA: {{ $po->no_whatsapp }}</div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: #f0f9ff; color: #0ea5e9; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800;">
                                    {{ $po->paket ? $po->paket->nama_paket : '-' }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if ($po->po_status === 'Aktif')
                                    <span class="badge badge-published">AKTIF</span>
                                @elseif ($po->po_status === 'Berhenti')
                                    <span class="badge badge-archived">BERHENTI</span>
                                @else
                                    <span class="badge badge-draft">PENDING</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                $('#preorderTable').DataTable({
                    "pageLength": 15,
                    "lengthChange": false,
                    "ordering": false,
                    "info": false,
                    "responsive": true,
                    "dom": 'tpir',
                    "language": {
                        "zeroRecords": "Data tidak ditemukan",
                        "paginate": {
                            "previous": '<i class="fa-solid fa-chevron-left"></i>',
                            "next": '<i class="fa-solid fa-chevron-right"></i>'
                        }
                    }
                });
            });
        </script>
    @endsection
</x-layouts.app>
