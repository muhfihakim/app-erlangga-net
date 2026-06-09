<x-layouts.app>
    @section('Css')
        <style>
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
        @foreach($stats['scoreboard']->take(3) as $sb)
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;"><i class="fa-solid fa-trophy"></i></div>
            <div>
                <div class="stat-val">{{ $sb['count'] }}</div>
                <div class="stat-lbl">{{ $sb['name'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Filters & Search -->
    <div class="table-toolbar">
        <form action="{{ route('supporting.index') }}" method="GET" class="w-full">
            <div class="filter-group">
                <div style="flex:1; min-width: 200px;">
                    <label class="form-label-custom">Cari Keterangan</label>
                    <div class="search-wrap !max-w-full">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" name="search" value="{{ request('search') }}" id="searchInput" class="search-input custom-search" placeholder="Cari..." autocomplete="off">
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
                    <a href="{{ route('supporting.export', request()->all()) }}" class="btn-primary-admin !bg-rose-600 !h-10 !px-6 flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf"></i> PDF
                    </a>
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
                        <th style="text-align: center;">Foto</th>
                        <th style="text-align: center; width: 100px;">Aksi</th>
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
                            @if($act->photo)
                                <a href="{{ asset('storage/'.$act->photo) }}" target="_blank" class="badge" style="background:#e0f2fe;color:#0369a1;text-decoration:none;">LIHAT</a>
                            @else
                                <span class="badge" style="background:#f1f5f9;color:#cbd5e1;">TIADA</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" onclick="editModal({{ $act->toJson() }})">
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
                        <td colspan="8" style="padding:40px;text-align:center;color:#94a3b8;font-weight:600;">Belum ada data perbantuan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:1rem;border-top:1px solid #f1f5f9;">
            {{ $activities->links() }}
        </div>
    </div>

    @include('supporting.modal')

    @section('Scripts')
    <script>
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
            document.querySelector('[name="main_technician_id"]').value = data.main_technician_id;
            document.querySelector('[name="description"]').value = data.description || '';
            
            document.querySelectorAll('.partner-check').forEach(c => {
                c.checked = data.partner_ids && data.partner_ids.includes(parseInt(c.value));
            });

            openCustomModal('supportingModal');
        }

        // Live Search
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll('.activity-row').forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(val) ? '' : 'none';
            });
        });
    </script>
    @endsection
</x-layouts.app>
