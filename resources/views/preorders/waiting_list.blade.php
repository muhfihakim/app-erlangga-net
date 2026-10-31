<x-layouts.app>
    @section('Css')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            #waiting-list-table td {
                padding: 10px 12px;
                font-size: 12px;
                vertical-align: middle;
            }
            .dataTables_wrapper {
                padding: 0;
            }
            .dataTables_filter {
                display: none;
            }
            .dataTables_paginate {
                display: flex;
                align-items: center;
                gap: 4px;
                padding: 0.75rem 1rem;
                border-top: 1px solid #f1f5f9;
                justify-content: flex-end;
            }
            .paginate_button {
                height: 30px;
                padding: 0 10px;
                border-radius: 6px;
                border: 1px solid #e2e8f0;
                background: #fff;
                color: #475569 !important;
                font-size: 11px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                text-decoration: none;
            }
            .paginate_button.current {
                background: #4f46e5;
                border-color: #4f46e5;
                color: #fff !important;
            }
            .paginate_button.disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
        </style>
    @endsection

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Prospek Tunggu (Waiting List Ekspansi)</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Daftar calon pelanggan ditangguhkan (Unspek) yang menunggu perluasan jaringan, tiang baru, atau penambahan kapasitas ODP.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 text-emerald-700 p-3.5 rounded-xl border border-emerald-200 mb-4 flex items-start gap-3 shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-500 text-base mt-0.5"></i>
            <div>
                <strong class="font-bold text-xs">Berhasil!</strong>
                <p class="text-xs mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-rose-50 text-rose-700 p-3.5 rounded-xl border border-rose-200 mb-4 flex items-start gap-3 shadow-xs">
            <i class="fa-solid fa-circle-exclamation text-rose-500 text-base mt-0.5"></i>
            <div>
                <strong class="font-bold text-xs">Terjadi Kesalahan!</strong>
                <p class="text-xs mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Stats Grid -->
    <div class="stats-grid mb-5">
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
                    <select name="status_review" class="form-input-custom !h-8.5 !text-xs">
                        <option value="semua" {{ $filterReview === 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="pending" {{ $filterReview === 'pending' ? 'selected' : '' }}>Menunggu Review</option>
                        <option value="approved" {{ $filterReview === 'approved' ? 'selected' : '' }}>Terverifikasi Ekspansi</option>
                    </select>
                </div>
                @if (Auth::user()->isAdmin())
                    <div style="flex: 1; min-width: 170px;">
                        <label class="form-label-custom">Sales</label>
                        <select name="id_sales" class="form-input-custom !h-8.5 !text-xs">
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
                    <div class="search-wrap !max-w-full !mb-0">
                        <input type="search" class="search-input custom-search !h-8.5 !text-xs" id="waiting-list-search" placeholder="Cari nama, alamat, kendala..."
                            autocomplete="off" onkeydown="return event.key != 'Enter';" />
                    </div>
                </div>
                <button type="submit" class="btn-primary-admin !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card overflow-hidden">
        <div class="table-responsive">
            <table id="waiting-list-table" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 45px;">#</th>
                        <th style="text-align: center; width: 95px;">Tgl List</th>
                        <th style="text-align: left; min-width: 120px; width: 130px;">Sales</th>
                        <th style="text-align: left; min-width: 200px;">Calon Pelanggan</th>
                        <th style="text-align: center; width: 110px;">Paket</th>
                        <th style="text-align: left; min-width: 180px;">Kendala Lapangan</th>
                        <th style="text-align: center; width: 90px;">Koordinat</th>
                        <th style="text-align: center; width: 120px;">Status</th>
                        <th style="text-align: center; width: 190px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($waitingList as $po)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                            <td style="text-align: center; font-family: monospace; font-size: 11px; color: #64748b;">
                                {{ $po->updated_at ? $po->updated_at->format('d/m/Y') : ($po->created_at ? $po->created_at->format('d/m/Y') : '-') }}
                            </td>
                            <td style="text-align: left; font-size: 12px; font-weight: 600; color: #334155;">
                                {{ $po->nama_sales ?: ($po->sales ? $po->sales->nama_lengkap : '-') }}
                            </td>
                            <td style="text-align: left;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 12px;">{{ $po->nama_lengkap }}</div>
                                @if ($po->no_whatsapp)
                                    @php
                                        $cleanWa = preg_replace('/[^0-9]/', '', $po->no_whatsapp);
                                        if (str_starts_with($cleanWa, '0')) $cleanWa = '62' . substr($cleanWa, 1);
                                    @endphp
                                    <div class="mt-0.5">
                                        <a href="https://wa.me/{{ $cleanWa }}" target="_blank"
                                            class="text-[11px] font-mono text-emerald-700 hover:underline inline-flex items-center gap-1 font-semibold"
                                            title="Chat via WhatsApp">
                                            {{ $po->no_whatsapp }}
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
                                    <div class="font-bold text-[10px] uppercase text-amber-700 mb-0.5">
                                        Catatan Lapangan:
                                    </div>
                                    <div>{{ $po->keterangan_unspek ?: 'Belum ada catatan kendala.' }}</div>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @if ($po->titik_koordinat && str_contains($po->titik_koordinat, ','))
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $po->titik_koordinat }}" target="_blank"
                                        class="topbar-icon-btn !w-7 !h-7 !rounded-lg inline-flex items-center justify-center text-emerald-600 hover:text-emerald-700"
                                        title="Buka lokasi di Google Maps: {{ $po->titik_koordinat }}">
                                        <i class="fa-solid fa-location-dot text-xs"></i>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if ($po->is_unspek_approved)
                                    <span class="badge text-[10px] font-bold inline-flex items-center gap-1" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">
                                        <i class="fa-solid fa-circle-check text-emerald-600"></i> EKSPANSI
                                    </span>
                                @else
                                    <span class="badge text-[10px] font-bold inline-flex items-center gap-1" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;">
                                        <i class="fa-solid fa-clock text-amber-600"></i> PENDING REVIEW
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    {{-- Re-open Button (Aktifkan ke PO) --}}
                                    @if (Auth::user()->isAdmin() || (auth()->user()->jabatan === 'Sales' && $po->id_sales == auth()->id()))
                                        @if (!$po->is_unspek_approved)
                                            <form action="{{ route('pre_orders.approve_unspek', $po->id) }}" method="POST" class="inline"
                                                onsubmit="return confirm('Setujui calon pelanggan ini resmi masuk ke Waiting List Ekspansi?')">
                                                @csrf
                                                <button type="submit" class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!bg-emerald-50 hover:!border-emerald-300"
                                                    title="Setujui Masuk Waiting List">
                                                    <i class="fa-solid fa-check text-emerald-600"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('pre_orders.reopen_unspek', $po->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Aktifkan kembali calon pelanggan ini ke antrean Pre-Order & Aktivasi?')">
                                            @csrf
                                            <button type="submit" class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!bg-sky-50 hover:!border-sky-300"
                                                title="Aktifkan ke Pre-Order">
                                                <i class="fa-solid fa-arrow-up-right-from-square text-sky-600"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Detail Button --}}
                                    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!bg-slate-100 hover:!border-slate-300"
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
                                        title="Detail Informasi">
                                        <i class="fa-solid fa-eye text-slate-600"></i>
                                    </button>

                                    {{-- Delete Button --}}
                                    @if (Auth::user()->isAdmin() || (auth()->user()->jabatan === 'Sales' && $po->id_sales == auth()->id()))
                                        <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-rose-300 hover:!bg-rose-50"
                                            onclick="confirmGlobalDelete('{{ route('pre_orders.destroy', $po->id) }}', '{{ addslashes($po->nama_lengkap) }}')"
                                            title="Hapus">
                                            <i class="fa-solid fa-trash text-rose-500"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-slate-400 text-xs italic">
                                Belum ada calon pelanggan di daftar prospek tunggu (waiting list ekspansi).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('partials.modal_preorders')

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <script>
            function openCustomModal(modalId, triggerEl = null) {
                const modal = document.getElementById(modalId);
                if (!modal) return;

                if (triggerEl && modalId === 'detailModal') {
                    const $btn = $(triggerEl);
                    $('#detail_nama_lengkap').text($btn.data('nama') || '-');
                    $('#detail_identitas').text($btn.data('identitas') || '-');
                    $('#detail_email').text($btn.data('email-lengkap') || '-');
                    $('#detail_alamat').text($btn.data('alamat-lengkap') || '-');
                    $('#detail_paket').text($btn.data('paket') || '-');
                    $('#detail_paket_harga').text($btn.data('paket-harga') || '');
                    $('#detail_sales').text($btn.data('sales') || '-');
                    $('#detail_tgl').text($btn.data('tgl') || '-');

                    const statusText = $btn.data('status') || 'Pending';
                    const statusBadge = $btn.data('status-badge') || 'badge-draft';
                    $('#detail_status_badge').text(statusText.toUpperCase()).attr('class', `badge ${statusBadge} text-[10px] py-0.5 px-2`);

                    const wa = $btn.data('wa');
                    if (wa && wa !== '-') {
                        let cleanWa = String(wa).replace(/[^0-9]/g, '');
                        if (cleanWa.startsWith('0')) cleanWa = '62' + cleanWa.substring(1);
                        $('#detail_wa').html(`<a href="https://wa.me/${cleanWa}" target="_blank" class="text-emerald-600 hover:underline font-semibold">${wa}</a>`);
                    } else {
                        $('#detail_wa').text('-');
                    }

                    const coord = $btn.data('koordinat');
                    if (coord && coord !== '-' && coord.includes(',')) {
                        $('#detail_koordinat').html(`<a href="https://www.google.com/maps/search/?api=1&query=${coord}" target="_blank" class="text-indigo-600 hover:underline font-mono">${coord}</a>`);
                    } else {
                        $('#detail_koordinat').text(coord || '-');
                    }

                    const fotoKtp = $btn.data('foto-ktp');
                    if (fotoKtp) {
                        $('#detail_foto_ktp').attr('src', fotoKtp).removeClass('hidden');
                        $('#detail_foto_ktp_empty').addClass('hidden');
                    } else {
                        $('#detail_foto_ktp').addClass('hidden');
                        $('#detail_foto_ktp_empty').removeClass('hidden');
                    }

                    const fotoRumah = $btn.data('foto-rumah');
                    if (fotoRumah) {
                        $('#detail_foto_rumah').attr('src', fotoRumah).removeClass('hidden');
                        $('#detail_foto_rumah_empty').addClass('hidden');
                    } else {
                        $('#detail_foto_rumah').addClass('hidden');
                        $('#detail_foto_rumah_empty').removeClass('hidden');
                    }
                }

                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function closeCustomModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('open');
                    document.body.style.overflow = '';
                }
            }

            $(document).on('click', '.modal-overlay', function(e) {
                if (e.target === this) {
                    $(this).removeClass('open');
                    document.body.style.overflow = '';
                }
            });

            $(document).ready(function() {
                const table = $('#waiting-list-table').DataTable({
                    pageLength: 10,
                    lengthChange: false,
                    ordering: false,
                    info: false,
                    responsive: true,
                    dom: 'tpir',
                    language: {
                        zeroRecords: '<div class="p-4 text-center text-xs text-slate-400">Data tidak ditemukan</div>',
                        paginate: {
                            previous: 'Sebelumnya',
                            next: 'Berikutnya'
                        }
                    }
                });

                $('#waiting-list-search').on('keyup input', function() {
                    table.search(this.value).draw();
                });
            });
        </script>
    @endsection
</x-layouts.app>
