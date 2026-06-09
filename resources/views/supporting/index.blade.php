<x-layouts.app>
    @section('Css')
        <style>
            .info-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1.25rem 1.5rem;
                display: flex;
                align-items: center;
                gap: 2rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
                margin-bottom: 1rem;
                flex-wrap: wrap;
            }
            .info-item { flex: 1 1 calc(16.666% - 1rem); min-width: 120px; }
            .info-item .lbl {
                font-size: 11px;
                font-weight: 800;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: .05em;
                margin-bottom: 2px;
            }
            .info-item .val {
                font-size: 15px;
                font-weight: 700;
                color: #0f172a;
            }
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 1rem;
                margin-bottom: 1rem;
            }
            .stat-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1rem 1.25rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }
            .stat-card .lbl {
                font-size: 11px;
                font-weight: 800;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: .05em;
                margin-bottom: 4px;
            }
            .stat-card .val {
                font-size: 20px;
                font-weight: 900;
            }
            .partner-card-scroll {
                max-height: 200px;
                overflow-y: auto;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 0.5rem;
                background: #f8fafc;
            }
            .partner-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 8px 12px;
                border-radius: 6px;
                margin-bottom: 4px;
                background: #fff;
                border: 1px solid #f1f5f9;
                transition: all .2s;
                cursor: pointer;
            }
            .partner-item:hover {
                background: #f1f5f9;
                border-color: #e2e8f0;
            }
            @media (max-width: 768px) {
                .stats-grid { grid-template-columns: repeat(2, 1fr); }
            }
        </style>
    @endsection

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Perbantuan BBSNET</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Catatan bantuan teknis dalam berbagai kegiatan.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="openModal()" class="btn btn-primary" style="background:#0f172a;color:#fff;border:none;padding:8px 16px;border-radius:8px;font-weight:700;font-size:13px;display:flex;align-items:center;gap:8px;">
                <i class="fa-solid fa-plus"></i> Tambah Catatan
            </button>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="lbl">Total Perbantuan</div>
            <div class="val" style="color:#0f172a;">{{ $stats['total'] }}</div>
        </div>
        @foreach($stats['scoreboard']->take(3) as $sb)
        <div class="stat-card">
            <div class="lbl">Top: {{ $sb['name'] }}</div>
            <div class="val" style="color:#0ea5e9;">{{ $sb['count'] }} <span style="font-size:12px;color:#64748b;font-weight:600;">Kali</span></div>
        </div>
        @endforeach
    </div>

    <!-- Filters & Search -->
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1.25rem;box-shadow:0 1px 6px rgba(0,0,0,.05);margin-bottom:1rem;">
        <form action="{{ route('supporting.index') }}" method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:200px;">
                <label style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;margin-bottom:4px;display:block;">Cari Keterangan</label>
                <input type="text" name="search" value="{{ request('search') }}" id="searchInput" placeholder="Cari..." style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
            </div>
            <div style="width:150px;">
                <label style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;margin-bottom:4px;display:block;">Tipe</label>
                <select name="activity_type" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                    <option value="">Semua</option>
                    <option value="PO" {{ request('activity_type') == 'PO' ? 'selected' : '' }}>PO</option>
                    <option value="Pemeliharaan" {{ request('activity_type') == 'Pemeliharaan' ? 'selected' : '' }}>Pemeliharaan</option>
                    <option value="Buat ODP" {{ request('activity_type') == 'Buat ODP' ? 'selected' : '' }}>Buat ODP</option>
                    <option value="Aktivasi Jalur" {{ request('activity_type') == 'Aktivasi Jalur' ? 'selected' : '' }}>Aktivasi Jalur</option>
                </select>
            </div>
            <div style="width:150px;">
                <label style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;margin-bottom:4px;display:block;">Mulai</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
            </div>
            <div style="width:150px;">
                <label style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;margin-bottom:4px;display:block;">Selesai</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
            </div>
            <button type="submit" class="btn btn-sm btn-dark" style="padding:10px 16px;border-radius:8px;background:#1e293b;border:none;color:#fff;font-weight:700;font-size:13px;">Filter</button>
            <a href="{{ route('supporting.export', request()->all()) }}" class="btn btn-sm btn-danger" style="padding:10px 16px;border-radius:8px;background:#ef4444;border:none;color:#fff;font-weight:700;font-size:13px;">PDF</a>
        </form>
    </div>

    <!-- Table -->
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.05);overflow:hidden;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;text-align:left;">
                        <th style="padding:12px 16px;font-weight:800;color:#475569;text-transform:uppercase;font-size:11px;">Tanggal</th>
                        <th style="padding:12px 16px;font-weight:800;color:#475569;text-transform:uppercase;font-size:11px;">Tipe</th>
                        <th style="padding:12px 16px;font-weight:800;color:#475569;text-transform:uppercase;font-size:11px;">Teknisi Utama</th>
                        <th style="padding:12px 16px;font-weight:800;color:#475569;text-transform:uppercase;font-size:11px;">Partner</th>
                        <th style="padding:12px 16px;font-weight:800;color:#475569;text-transform:uppercase;font-size:11px;">Keterangan</th>
                        <th style="padding:12px 16px;font-weight:800;color:#475569;text-transform:uppercase;font-size:11px;">Foto</th>
                        <th style="padding:12px 16px;font-weight:800;color:#475569;text-transform:uppercase;font-size:11px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($activities as $act)
                    <tr style="border-bottom:1px solid #f1f5f9;transition:all .2s;" class="activity-row">
                        <td style="padding:12px 16px;font-weight:700;color:#0f172a;">{{ $act->activity_date->format('d/m/Y') }}</td>
                        <td style="padding:12px 16px;">
                            <span style="padding:4px 10px;border-radius:6px;font-size:11px;font-weight:800;background:#f1f5f9;color:#475569;">{{ $act->activity_type }}</span>
                        </td>
                        <td style="padding:12px 16px;font-weight:600;">{{ $act->mainTechnician->nama_lengkap ?? '-' }}</td>
                        <td style="padding:12px 16px;">
                            @php
                                $partners = \App\Models\User::whereIn('id', $act->partner_ids ?? [])->pluck('nama_lengkap');
                            @endphp
                            @foreach($partners as $p)
                                <div style="font-size:11px;color:#64748b;">• {{ $p }}</div>
                            @endforeach
                        </td>
                        <td style="padding:12px 16px;color:#64748b;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $act->description ?? '-' }}</td>
                        <td style="padding:12px 16px;">
                            @if($act->photo)
                                <a href="{{ asset('storage/'.$act->photo) }}" target="_blank" style="color:#0ea5e9;font-weight:700;font-size:11px;">LIHAT FOTO</a>
                            @else
                                <span style="color:#cbd5e1;font-size:11px;">TIDAK ADA</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <div style="display:flex;justify-content:center;gap:8px;">
                                <button onclick="editModal({{ $act->toJson() }})" style="width:32px;height:32px;border-radius:8px;background:#f1f5f9;color:#475569;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;"><i class="fa-solid fa-pen-to-square"></i></button>
                                <form action="{{ route('supporting.destroy', $act->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="width:32px;height:32px;border-radius:8px;background:#fef2f2;color:#ef4444;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;"><i class="fa-solid fa-trash"></i></button>
                                </form>
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
        <div style="padding:1rem;border-top:1px solid #f1f5f9;">
            {{ $activities->links() }}
        </div>
    </div>

    @include('supporting.modal')

    @section('Js')
    <script>
        function openModal() {
            document.getElementById('modalTitle').innerText = 'Tambah Perbantuan';
            document.getElementById('modalForm').action = "{{ route('supporting.store') }}";
            document.getElementById('modalMethod').value = 'POST';
            document.getElementById('modalForm').reset();
            document.querySelectorAll('.partner-check').forEach(c => c.checked = false);
            document.getElementById('supportingModal').classList.remove('hidden');
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

            document.getElementById('supportingModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('supportingModal').classList.add('hidden');
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
