<x-layouts.app title="Manajemen Router (NAS)">
    @section('Css')
        <style>
            .dataTables_wrapper { padding: 0; }
            .dataTables_filter { display: none; }
            .dataTables_paginate {
                display: flex; justify-content: flex-end; padding: 1rem 1.25rem; gap: 4px; border-top: 1px solid #f1f5f9;
            }
            .paginate_button {
                width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid #e2e8f0; background: #fff;
                color: #64748b !important; font-size: 12px; font-weight: 600; display: inline-flex;
                align-items: center; justify-content: center; cursor: pointer;
            }
            .paginate_button.current { background: #0ea5e9; border-color: #0ea5e9; color: #fff !important; }
        </style>
    @endsection

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Manajemen Router (NAS)</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola data Router / Server Akses untuk Radius.</p>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="button" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold" onclick="openNasModal()">
                Tambah
            </button>
        </div>
    </div>

    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input custom-search" placeholder="Cari nama atau IP router..." autocomplete="off" />
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table id="nasTable" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 45px;">#</th>
                        <th style="text-align: left;">Nama Singkat</th>
                        <th style="text-align: left;">IP Address</th>
                        <th style="text-align: left;">Secret Key</th>
                        <th style="text-align: center; width: 100px;">Tipe</th>
                        <th style="text-align: left;">Deskripsi</th>
                        <th style="text-align: center; width: 90px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($routers as $router)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                            <td style="text-align: left; font-weight: 700; color: #0f172a;">{{ $router->shortname }}</td>
                            <td style="text-align: left; color: #059669; font-weight: 700; font-family: monospace;">{{ $router->nasname }}</td>
                            <td style="text-align: left; color: #e11d48; font-weight: 600; font-family: monospace;">{{ $router->secret }}</td>
                            <td style="text-align: center;">
                                <span class="badge text-[10px] font-bold py-0.5 px-2" style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;">
                                    {{ strtoupper($router->type ?? 'Mikrotik') }}
                                </span>
                            </td>
                            <td style="color: #64748b; font-size: 12px;">{{ $router->description ?? '-' }}</td>
                            <td style="text-align: center;">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                        onclick="editNasModal({{ $router->id }}, '{{ $router->shortname }}', '{{ $router->nasname }}', '{{ $router->secret }}', '{{ $router->description }}')"
                                        title="Edit"><i class="fa-solid fa-pen-to-square text-slate-500 hover:text-amber-600"></i></button>
                                    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                        onclick="confirmGlobalDelete('{{ route('nas.destroy', $router->id) }}', 'Router {{ $router->shortname }}')"
                                        title="Hapus"><i class="fa-solid fa-trash text-slate-500 hover:text-rose-600"></i></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form NAS -->
    <div class="modal-overlay" id="formNasModal">
        <div class="modal-box max-w-xl bg-white border border-slate-300 shadow-2xl">
            <form id="formNas" method="POST" action="{{ route('nas.store') }}" novalidate>
                @csrf
                <input type="hidden" name="_method" id="nasMethod" value="POST">
                
                <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                            <i class="fa-solid fa-network-wired"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 m-0" id="modalNasTitle">Tambah Router (NAS)</h3>
                            <p class="text-[11px] text-slate-500 font-normal m-0 mt-0.5">Konfigurasi router access server RADIUS</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('formNasModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body p-6 bg-white">
                    <div class="form-grid-2 mb-3.5">
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Nama Singkat <span class="text-rose-500">*</span></label>
                            <input type="text" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" id="shortname" name="shortname" required placeholder="Contoh: Mikrotik Pusat">
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">IP Address (NAS) <span class="text-rose-500">*</span></label>
                            <input type="text" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs font-mono" id="nasname" name="nasname" required placeholder="Contoh: 192.168.1.1">
                        </div>
                    </div>
                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Secret Key <span class="text-rose-500">*</span></label>
                        <input type="text" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs font-mono" id="secret" name="secret" required placeholder="Rahasia Radius">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Deskripsi / Lokasi</label>
                        <textarea class="form-textarea !font-normal bg-white text-slate-800 border-slate-300 text-xs" id="description" name="description" rows="2" placeholder="Informasi tambahan lokasi router..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2.5">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold" onclick="closeCustomModal('formNasModal')">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary-admin !h-9 !px-5 text-xs font-semibold">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                const table = $('#nasTable').DataTable({
                    "pageLength": 15,
                    "lengthChange": false,
                    "ordering": false,
                    "info": false,
                    "responsive": true,
                    "dom": 'tpir',
                    "language": {
                        "zeroRecords": "Data tidak ditemukan",
                        "paginate": { "previous": '<i class="fa-solid fa-chevron-left"></i>', "next": '<i class="fa-solid fa-chevron-right"></i>' }
                    }
                });
                $('.custom-search').on('keyup', function() { table.search(this.value).draw(); });
                $('.modal-overlay').on('click', function(e) { if (e.target === this) closeCustomModal(this.id); });
            });

            function openNasModal() {
                $('#modalNasTitle').text('Tambah Router (NAS)');
                $('#formNas').attr('action', '{{ route('nas.store') }}');
                $('#nasMethod').val('POST');
                $('#shortname').val('');
                $('#nasname').val('');
                $('#secret').val('');
                $('#description').val('');
                document.getElementById('formNasModal').classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function editNasModal(id, shortname, nasname, secret, description) {
                $('#modalNasTitle').text('Edit Router (NAS)');
                $('#formNas').attr('action', `/nas/${id}`);
                $('#nasMethod').val('PUT');
                $('#shortname').val(shortname);
                $('#nasname').val(nasname);
                $('#secret').val(secret);
                $('#description').val(description);
                document.getElementById('formNasModal').classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function closeCustomModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) { modal.classList.remove('open'); document.body.style.overflow = ''; }
            }
        </script>
    @endsection
</x-layouts.app>