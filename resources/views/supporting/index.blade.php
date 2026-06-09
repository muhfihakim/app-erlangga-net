<x-layouts.app>
    @section('Css')
        <style>
            /* Hakatech DataTables Customization */
            .dataTables_wrapper { padding: 0; }
            .dataTables_filter { display: none; }
            .dataTables_paginate {
                display: flex;
                justify-content: flex-end;
                padding: 1.25rem 1.5rem;
                gap: 4px;
                border-top: 1px solid #f1f5f9;
            }
            .paginate_button {
                width: 34px;
                height: 34px;
                border-radius: 8px;
                border: 1.5px solid #e2e8f0;
                background: #fff;
                color: #64748b !important;
                font-size: 13px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                text-decoration: none !important;
            }
            .paginate_button.current {
                background: #0ea5e9;
                border-color: #0ea5e9;
                color: #fff !important;
            }
            .paginate_button.disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
            #supporting-table thead th, #supporting-table tbody td {
                text-transform: uppercase !important; font-weight: 600; font-size: 12px;
            }
            /* Custom Modal Styles for Partner Selection */
            .partner-card-scroll {
                max-height: 200px;
                overflow-y: auto;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 12px;
            }
            .partner-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 8px;
                border-radius: 8px;
                transition: background 0.2s;
            }
            .partner-item:hover {
                background: #f1f5f9;
            }
        </style>
    @endsection

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Perbantuan BBSNET</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Catatan bantuan teknis dalam berbagai kegiatan.</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if(request()->filled('start_date') || request()->filled('end_date') || request()->filled('activity_type') || request()->filled('search') || request()->filled('teknisi_id'))
                <a href="{{ route('supporting.export', request()->all()) }}" class="btn-primary-admin !bg-rose-600 !h-10 !px-6 flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            @endif
            <button type="button" class="btn-primary-admin btn-glow-primary !flex !items-center !justify-center gap-2"
                onclick="addModal()"><i class="fa-solid fa-plus"></i> Tambah Catatan</button>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;"><i class="fa-solid fa-handshake-angle"></i></div>
            <div>
                <div class="stat-val">{{ $stats['total'] }}</div>
                <div class="stat-lbl">Total Perbantuan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;"><i class="fa-solid fa-calendar-check"></i></div>
            <div>
                <div class="stat-val">{{ $stats['this_month'] }}</div>
                <div class="stat-lbl">Bulan Ini</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9;color:#475569;"><i class="fa-solid fa-calendar-minus"></i></div>
            <div>
                <div class="stat-val">{{ $stats['last_month'] }}</div>
                <div class="stat-lbl">Bulan Lalu</div>
            </div>
        </div>
        @if(auth()->user()->isAdmin())
            @foreach($stats['scoreboard']->take(1) as $sb)
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-trophy"></i></div>
                <div>
                    <div class="stat-val">{{ $sb['count'] }}</div>
                    <div class="stat-lbl">Top: {{ $sb['name'] }}</div>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <!-- Filters & Search -->
    <div class="table-toolbar">
        <form action="{{ route('supporting.index') }}" method="GET" class="w-full">
            <div class="filter-group">
                <div style="flex:1; min-width: 200px;">
                    <label class="form-label-custom">Cari Keterangan</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" name="search" value="{{ request('search') }}" id="custom-search" class="search-input custom-search" placeholder="Cari..." autocomplete="off">
                    </div>
                </div>
                <div style="width:180px;">
                    <label class="form-label-custom">Tipe Kegiatan</label>
                    <select name="activity_type" class="form-input-custom">
                        <option value="">Semua Tipe</option>
                        <option value="PO" {{ request('activity_type') == 'PO' ? 'selected' : '' }}>PO</option>
                        <option value="Pemeliharaan" {{ request('activity_type') == 'Pemeliharaan' ? 'selected' : '' }}>Pemeliharaan</option>
                        <option value="Buat ODP" {{ request('activity_type') == 'Buat ODP' ? 'selected' : '' }}>Buat ODP</option>
                        <option value="Aktivasi Jalur" {{ request('activity_type') == 'Aktivasi Jalur' ? 'selected' : '' }}>Aktivasi Jalur</option>
                    </select>
                </div>
                @if(auth()->user()->isAdmin())
                <div style="width:180px;">
                    <label class="form-label-custom">Teknisi</label>
                    <select name="teknisi_id" class="form-input-custom">
                        <option value="">Semua Teknisi</option>
                        @foreach($technicians as $t)
                            <option value="{{ $t->id }}" {{ request('teknisi_id') == $t->id ? 'selected' : '' }}>{{ $t->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div style="width:150px;">
                    <label class="form-label-custom">Mulai</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-input-custom">
                </div>
                <div style="width:150px;">
                    <label class="form-label-custom">Selesai</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-input-custom">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary-admin !h-10 !px-6 flex items-center gap-2">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="supporting-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: center; width: 100px;">Tanggal</th>
                        <th style="text-align: center;">Tipe</th>
                        <th style="text-align: center;">Teknisi Utama</th>
                        <th style="text-align: center;">Partner</th>
                        <th style="text-align: center;">Keterangan</th>
                        <th style="text-align: center; width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($activities as $act)
                    <tr class="activity-row">
                        <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                        <td style="text-align: center; color: #64748b; font-size: 12px;">{{ $act->activity_date->format('d-m-Y') }}</td>
                        <td style="text-align: center;">
                            <span class="badge" style="background:#f1f5f9;color:#475569;">{{ $act->activity_type }}</span>
                        </td>
                        <td style="text-align: center; font-weight: 700;">{{ $act->mainTechnician->nama_lengkap ?? '-' }}</td>
                        <td style="text-align: left;">
                            @php
                                $partners = \App\Models\User::whereIn('id', $act->partner_ids ?? [])->pluck('nama_lengkap');
                            @endphp
                            @foreach($partners as $p)
                                <div style="font-size:11px;color:#64748b;">• {{ $p }}</div>
                            @endforeach
                        </td>
                        <td style="text-align: left; color:#64748b; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $act->description ?? '-' }}</td>
                        <td style="text-align: center;">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" onclick='showDetail(@json($act), "{{ $act->mainTechnician->nama_lengkap ?? '-' }}", "{{ implode(', ', $partners->toArray()) }}", "{{ $act->photo ? Storage::url($act->photo) : '' }}")'>
                                    <i class="fa-solid fa-circle-info text-primary"></i>
                                </button>
                                <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" onclick='editModal(@json($act))'>
                                    <i class="fa-solid fa-pen-to-square text-amber-500"></i>
                                </button>
                                <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" onclick="confirmGlobalDelete('{{ route('supporting.destroy', $act->id) }}', 'Catatan {{ $act->activity_type }} - {{ $act->activity_date->format('d/m/Y') }}')">
                                    <i class="fa-solid fa-trash text-rose-600"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding:40px;text-align:center;color:#94a3b8;font-weight:600;">Belum ada data perbantuan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('supporting.modal')

    {{-- Detail Modal --}}
    <div class="modal-overlay" id="detailModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-circle-info text-primary"></i>
                    <span class="text-slate-900 font-bold">Detail Perbantuan</span>
                </div>
                <button class="modal-close" onclick="closeCustomModal('detailModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body bg-white p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 block uppercase">Tanggal & Tipe</span>
                            <p id="det_tanggal_tipe" class="text-sm font-bold text-slate-800"></p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 block uppercase">Teknisi Utama</span>
                            <p id="det_teknisi_utama" class="text-sm font-bold text-slate-800"></p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 block uppercase">Partner</span>
                            <p id="det_partner" class="text-sm font-medium text-slate-600"></p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 block uppercase">Keterangan</span>
                            <p id="det_keterangan" class="text-sm text-slate-600 leading-relaxed"></p>
                        </div>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 block uppercase mb-2">Foto Kegiatan</span>
                        <div class="aspect-video rounded-xl overflow-hidden border-2 border-slate-100 bg-slate-50 flex items-center justify-center">
                            <img id="det_foto" src="" class="w-full h-full object-cover hidden cursor-pointer" onclick="window.open(this.src, '_blank')">
                            <div id="det_no_foto" class="text-slate-300 text-xs text-center hidden">
                                <i class="fa-solid fa-image text-3xl mb-2 block"></i>
                                Tidak ada foto
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button type="button" class="btn-cancel w-full" onclick="closeCustomModal('detailModal')">
                    <i class="fa-solid fa-xmark"></i> Tutup Detail
                </button>
            </div>
        </div>
    </div>

    @section('Scripts')
    <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            const table = $('#supporting-table').DataTable({
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

            $('#custom-search').on('keyup', function() {
                table.search(this.value).draw();
            });
        });

        function showDetail(data, mainTech, partners, photoUrl) {
            const date = new Date(data.activity_date).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
            document.getElementById('det_tanggal_tipe').innerText = `${date} - ${data.activity_type}`;
            document.getElementById('det_teknisi_utama').innerText = mainTech;
            document.getElementById('det_partner').innerText = partners || '-';
            document.getElementById('det_keterangan').innerText = data.description || '-';

            const img = document.getElementById('det_foto');
            const noImg = document.getElementById('det_no_foto');
            
            if (photoUrl) {
                img.src = photoUrl;
                img.classList.remove('hidden');
                noImg.classList.add('hidden');
            } else {
                img.classList.add('hidden');
                noImg.classList.remove('hidden');
            }

            openCustomModal('detailModal');
        }

        function addModal() {
            document.getElementById('modalTitle').innerText = 'Tambah Perbantuan';
            document.getElementById('modalForm').action = "{{ route('supporting.store') }}";
            document.getElementById('modalMethod').value = 'POST';
            document.getElementById('modalForm').reset();
            document.querySelectorAll('.partner-check').forEach(c => c.checked = false);
            openCustomModal('supportingModal');
        }

        function editModal(data) {
            document.getElementById('modalTitle').innerText = 'Edit Perbantuan';
            document.getElementById('modalForm').action = "/supporting/" + data.id;
            document.getElementById('modalMethod').value = 'PUT';
            
            document.querySelector('[name="activity_type"]').value = data.activity_type;
            document.querySelector('[name="activity_date"]').value = data.activity_date.split('T')[0];
            
            const mainTechEl = document.querySelector('[name="main_technician_id"]');
            if (mainTechEl) mainTechEl.value = data.main_technician_id;
            
            document.querySelector('[name="description"]').value = data.description || '';
            
            document.querySelectorAll('.partner-check').forEach(c => {
                c.checked = data.partner_ids && data.partner_ids.includes(parseInt(c.value));
            });

            openCustomModal('supportingModal');
        }
    </script>
    @endsection
</x-layouts.app>
