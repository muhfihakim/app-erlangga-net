<x-layouts.app>
    @section('Css')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            #waiting-list-table td {
                padding: 12px 14px;
                font-size: 13px;
                vertical-align: middle;
            }
        </style>
    @endsection

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Prospek Tunggu (Waiting List Ekspansi)</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Daftar calon pelanggan ditangguhkan (Unspek) yang menunggu perluasan jaringan, tiang baru, atau penambahan kapasitas ODP.</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 mb-5 border-b border-slate-200 pb-2">
        <a href="{{ route('pre_orders.index') }}"
            class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all">
            Pre-Order Aktif
        </a>
        <a href="{{ route('pre_orders.waiting_list') }}"
            class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-primary text-white shadow-sm flex items-center gap-2">
            <span>Waiting List Ekspansi</span>
            @if($totalWaiting > 0)
                <span class="bg-white/20 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $totalWaiting }}</span>
            @endif
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef2f2;color:#be123c;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalWaiting }}</div>
                <div class="stat-lbl">Total Waiting List</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fffbeb;color:#b45309;">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalPendingReview }}</div>
                <div class="stat-lbl">Menunggu Review</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5;color:#047857;">
                <i class="fa-solid fa-network-wired"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalApproved }}</div>
                <div class="stat-lbl">Terverifikasi Ekspansi</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                <i class="fa-solid fa-calendar"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalThisMonth }}</div>
                <div class="stat-lbl">Masuk Bulan Ini</div>
            </div>
        </div>
    </div>

    <!-- Toolbar & Filters -->
    <div class="table-toolbar">
        <form method="GET" action="{{ route('pre_orders.waiting_list') }}" class="w-full">
            <div class="filter-group">
                <div style="flex: 1; min-width: 170px;">
                    <label class="form-label-custom">Status Review</label>
                    <select name="status_review" class="form-input-custom">
                        <option value="semua" {{ $filterReview === 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="pending" {{ $filterReview === 'pending' ? 'selected' : '' }}>Menunggu Review</option>
                        <option value="approved" {{ $filterReview === 'approved' ? 'selected' : '' }}>Terverifikasi Ekspansi</option>
                    </select>
                </div>
                @if (Auth::user()->isAdmin())
                    <div style="flex: 1; min-width: 170px;">
                        <label class="form-label-custom">Sales</label>
                        <select name="id_sales" class="form-input-custom">
                            <option value="">Semua Sales</option>
                            @foreach ($salesUsers as $sales)
                                <option value="{{ $sales->id }}" {{ $filterSales == $sales->id ? 'selected' : '' }}>
                                    {{ $sales->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div style="flex: 1.5; min-width: 200px;">
                    <label class="form-label-custom">Cari Prospek</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" class="search-input custom-search" id="waiting-list-search" placeholder="Cari nama, alamat, kendala..."
                            autocomplete="off" onkeydown="return event.key != 'Enter';" />
                    </div>
                </div>
                <button type="submit" class="btn-primary-admin !h-10 !px-6 flex items-center justify-center">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="waiting-list-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 45px;">#</th>
                        <th style="text-align: center; width: 95px;">Tgl List</th>
                        <th style="text-align: left; min-width: 130px; width: 140px;">Sales</th>
                        <th style="text-align: left; min-width: 220px;">Calon Pelanggan</th>
                        <th style="text-align: center; width: 120px;">Paket</th>
                        <th style="text-align: left; min-width: 200px;">Kendala Lapangan</th>
                        <th style="text-align: center; width: 130px;">Koordinat</th>
                        <th style="text-align: center; width: 130px;">Status</th>
                        <th style="text-align: center; width: 160px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($waitingList as $po)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                            <td style="text-align: center; font-mono; font-size: 12px; color: #64748b;">
                                {{ $po->updated_at ? $po->updated_at->format('d/m/Y') : ($po->created_at ? $po->created_at->format('d/m/Y') : '-') }}
                            </td>
                            <td style="text-align: left; font-size: 12px; font-weight: 600; color: #334155;">
                                <i class="fa-regular fa-user text-[11px] text-slate-400 mr-1.5"></i>
                                {{ $po->nama_sales ?: ($po->sales ? $po->sales->nama_lengkap : '-') }}
                            </td>
                            <td style="text-align: left;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 13px;">{{ $po->nama_lengkap }}</div>
                                @if ($po->no_whatsapp)
                                    @php
                                        $cleanWa = preg_replace('/[^0-9]/', '', $po->no_whatsapp);
                                        if (str_starts_with($cleanWa, '0')) $cleanWa = '62' . substr($cleanWa, 1);
                                    @endphp
                                    <div class="mt-0.5">
                                        <a href="https://wa.me/{{ $cleanWa }}" target="_blank"
                                            class="text-[11px] font-mono text-emerald-700 hover:underline inline-flex items-center gap-1 font-semibold"
                                            title="Chat via WhatsApp">
                                            <i class="fa-brands fa-whatsapp text-emerald-600"></i> {{ $po->no_whatsapp }}
                                        </a>
                                    </div>
                                @endif
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;" class="line-clamp-2">
                                    {{ $po->alamat_lengkap }}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; font-weight: 600; font-size: 11px;">
                                    {{ $po->paket ? $po->paket->nama_paket : '-' }}
                                </span>
                            </td>
                            <td style="text-align: left;">
                                <div class="p-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs leading-relaxed">
                                    <div class="font-bold text-[10px] uppercase text-amber-700 mb-0.5 flex items-center gap-1">
                                        <i class="fa-solid fa-triangle-exclamation text-[10px]"></i> Catatan Lapangan
                                    </div>
                                    <div>{{ $po->keterangan_unspek ?: 'Belum ada catatan kendala.' }}</div>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @if ($po->titik_koordinat && str_contains($po->titik_koordinat, ','))
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $po->titik_koordinat }}" target="_blank"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-mono font-semibold transition-colors"
                                        title="Buka lokasi di Google Maps">
                                        <i class="fa-solid fa-map-location-dot text-indigo-600"></i> Peta
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum ada</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if ($po->is_unspek_approved)
                                    <span class="badge text-[10px] font-bold" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">
                                        EKSPANSI
                                    </span>
                                @else
                                    <span class="badge text-[10px] font-bold" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;">
                                        PENDING REVIEW
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Re-open Button (Aktifkan ke PO) --}}
                                    @if (Auth::user()->isAdmin() || (auth()->user()->jabatan === 'Sales' && $po->id_sales == auth()->id()))
                                        @if (!$po->is_unspek_approved)
                                            <form action="{{ route('pre_orders.approve_unspek', $po->id) }}" method="POST" class="inline"
                                                onsubmit="return confirm('Setujui calon pelanggan ini resmi masuk ke Waiting List Ekspansi?')">
                                                @csrf
                                                <button type="submit" class="btn-primary-admin !h-8 !px-2.5 !text-[11px] !bg-emerald-600 hover:!bg-emerald-700"
                                                    title="Setujui Masuk Waiting List">
                                                    Setujui
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('pre_orders.reopen_unspek', $po->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Aktifkan kembali calon pelanggan ini ke antrean Pre-Order & Aktivasi?')">
                                            @csrf
                                            <button type="submit" class="btn-primary-admin !h-8 !px-2.5 !text-[11px] !bg-sky-600 hover:!bg-sky-700"
                                                title="Aktifkan ke Pre-Order">
                                                Aktifkan ke PO
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Detail Button --}}
                                    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                        onclick="openCustomModal('detailModal', this)"
                                        data-po-id="{{ $po->id }}"
                                        data-nama="{{ $po->nama_lengkap }}"
                                        data-identitas="{{ $po->no_identitas ?: '-' }}"
                                        data-email-lengkap="{{ $po->alamat_email ?: '-' }}"
                                        data-wa="{{ $po->no_whatsapp ?: '-' }}"
                                        data-sales="{{ $po->sales ? $po->sales->nama_lengkap : ($po->nama_sales ?: '-') }}"
                                        data-tgl="{{ $po->created_at ? $po->created_at->format('d/m/Y H:i') : '-' }}"
                                        data-alamat-lengkap="{{ $po->alamat_lengkap ?: '-' }}"
                                        data-paket="{{ $po->paket ? $po->paket->nama_paket : '-' }}"
                                        data-paket-harga="{{ $po->paket ? 'Rp ' . number_format($po->paket->harga, 0, ',', '.') : '' }}"
                                        data-koordinat="{{ $po->titik_koordinat ?: '' }}"
                                        data-status="{{ $po->is_unspek_approved ? 'Waiting List Ekspansi' : 'Menunggu Review Unspek' }}"
                                        data-status-badge="{{ $po->is_unspek_approved ? 'badge-published' : 'badge-warning' }}"
                                        data-foto-ktp="{{ $po->foto_ktp ? Storage::url($po->foto_ktp) : '' }}"
                                        data-foto-rumah="{{ $po->foto_rumah ? Storage::url($po->foto_rumah) : '' }}"
                                        title="Detail">
                                        <i class="fa-solid fa-circle-info text-slate-500"></i>
                                    </button>

                                    {{-- Delete Button --}}
                                    @if (Auth::user()->isAdmin() || (auth()->user()->jabatan === 'Sales' && $po->id_sales == auth()->id()))
                                        <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-rose-300 hover:!bg-rose-50"
                                            onclick="confirmGlobalDelete('{{ route('pre_orders.destroy', $po->id) }}', '{{ $po->nama_lengkap }}')"
                                            title="Hapus">
                                            <i class="fa-solid fa-trash text-rose-500"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-slate-400 text-sm italic">
                                Belum ada calon pelanggan di daftar prospek tunggu (waiting list ekspansi).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('partials.modal_preorders')
    @include('partials.script_preorders')

    @push('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#waiting-list-table').DataTable({
                "pageLength": 10,
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

            $('#waiting-list-search').on('keyup', function() {
                table.search(this.value).draw();
            });
        });
    </script>
    @endpush
</x-layouts.app>
