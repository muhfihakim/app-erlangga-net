<x-layouts.app title="Manajemen Paket Layanan">
    @section('Css')
        <style>
            /* Hakatech DataTables Customization */
            .dataTables_wrapper {
                padding: 0;
            }

            .dataTables_filter {
                display: none;
            }

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
            }

            .paginate_button.current {
                background: #0ea5e9;
                border-color: #0ea5e9;
                color: #fff !important;
            }

            #paketTable thead th,
            #paketTable tbody td {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 12px;
            }
        </style>
    @endsection

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Manajemen Paket Layanan</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola data paket layanan yang tersedia.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <form action="{{ route('paket.sync') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn-primary-admin !flex !items-center !justify-center gap-2" style="background-color: #10b981; border-color: #10b981;"
                    onclick="return confirm('Tarik semua Profile dari MikroTik dan masukkan ke database lokal?')">
                    <i class="fa-solid fa-rotate"></i> Sync Mikrotik
                </button>
            </form>
            <button type="button" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                onclick="openPaketModal()">
                <i class="fa-solid fa-plus-square"></i> Tambah Paket
            </button>
        </div>
    </div>

    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input custom-search" placeholder="Cari nama paket..."
                autocomplete="off" />
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table id="paketTable" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: left;">Nama Paket</th>
                        <th style="text-align: right;">Harga Pokok</th>
                        <th style="text-align: right;">Pajak (Rp)</th>
                        <th style="text-align: right;">Diskon (Rp)</th>
                        <th style="text-align: right;">Total Tagihan</th>
                        <th style="text-align: center;">Limitasi (Radius)</th>
                        <th style="text-align: center;">Pengguna</th>
                        <th style="text-align: center; width: 100px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pakets as $paket)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                            <td style="text-align: left; font-weight: 700; color: #0f172a;">{{ $paket->nama_paket }}</td>
                            <td style="text-align: right; color: #334155; font-weight: 700;">
                                Rp {{ number_format($paket->harga, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; color: #e11d48; font-weight: 700;">
                                {{ ($paket->pajak > 0) ? '+ ' . number_format($paket->pajak, 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right; color: #059669; font-weight: 700;">
                                {{ ($paket->diskon > 0) ? '- ' . number_format($paket->diskon, 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right; color: #334155; font-weight: 700;">
                                Rp {{ number_format($paket->total_harga, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center; color: #475569; font-weight: 700;">
                                {{ $paket->rate_limit ?? '-' }}
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background:#f1f5f9;color:#64748b;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;border:1px solid #e2e8f0;">
                                    <i class="fa-solid fa-users mr-1"></i> {{ number_format($paket->customers_count) }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                        onclick="editPaketModal({{ $paket->id }}, '{{ $paket->nama_paket }}', '{{ $paket->harga }}', '{{ $paket->pajak ?? 0 }}', '{{ $paket->diskon ?? 0 }}', '{{ $paket->rate_limit }}', '{{ $paket->keterangan }}')"
                                        title="Edit Paket"><i class="fa-solid fa-pen-to-square text-amber-500"></i></button>
                                    <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                        onclick="confirmGlobalDelete('{{ route('pakets.destroy', $paket->id) }}', 'Paket {{ $paket->nama_paket }}')"
                                        title="Hapus"><i class="fa-solid fa-trash text-rose-600"></i></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Paket -->
    <div class="modal-overlay" id="formPaketModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <form id="formPaket" method="POST" action="{{ route('pakets.store') }}" novalidate>
                @csrf
                <input type="hidden" name="_method" id="paketMethod" value="POST">
                
                <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                    <div class="modal-title">
                        <i class="fa-solid fa-box-open text-primary"></i>
                        <span class="text-slate-900 font-bold text-[17px]" id="modalPaketTitle">Tambah Paket</span>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('formPaketModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body p-8 space-y-6 bg-white">
                    <div class="form-group">
                        <label class="form-label text-slate-800 font-bold">Nama Paket <span
                                class="text-rose-500">*</span></label>
                        <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                            id="nama_paket" name="nama_paket" required placeholder="Contoh: Bronze 10Mbps">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Harga Pokok (Rp) <span
                                    class="text-rose-500">*</span></label>
                            <input type="number" class="form-input bg-white text-slate-900 border-slate-300" id="harga"
                                name="harga" required min="0" placeholder="0" oninput="calculatePaketTotal()">
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Pajak (Rp)</label>
                            <input type="number" class="form-input bg-white text-slate-900 border-slate-300" id="pajak"
                                name="pajak" min="0" placeholder="0" oninput="calculatePaketTotal()">
                            <span class="text-[10.5px] text-slate-500 mt-1 block">PPN / Fee Admin</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Diskon (Rp)</label>
                            <input type="number" class="form-input bg-white text-slate-900 border-slate-300" id="diskon"
                                name="diskon" min="0" placeholder="0" oninput="calculatePaketTotal()">
                            <span class="text-[10.5px] text-slate-500 mt-1 block">Potongan Paket</span>
                        </div>
                    </div>

                    <!-- Total Tagihan Preview Card -->
                    <div class="p-3.5 rounded-xl border border-sky-200 bg-sky-50 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] font-bold text-sky-800 uppercase tracking-wide">Total Tagihan Final Paket</div>
                            <div class="text-[12px] text-sky-600">Harga Pokok + Pajak - Diskon</div>
                        </div>
                        <div class="text-lg font-black text-sky-700" id="previewTotalTagihan">
                            Rp 0
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label text-slate-800 font-bold">Limit Bandwidth (Radius)</label>
                        <input type="text" class="form-input bg-white text-slate-900 border-slate-300" id="rate_limit"
                            name="rate_limit" placeholder="Contoh: 10M/20M">
                        <span class="text-[11px] text-slate-500 mt-1 block">Format: [Upload]/[Download], contoh: <code>5M/10M</code> atau <code>10M/20M</code></span>
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label text-slate-800 font-bold">Keterangan Tambahan</label>
                        <textarea class="form-textarea bg-white text-slate-900 border-slate-300" id="keterangan" name="keterangan"
                            rows="3" placeholder="Informasi tambahan..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        onclick="closeCustomModal('formPaketModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" class="btn-save !flex !items-center !justify-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Simpan Paket
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
                const table = $('#paketTable').DataTable({
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
                $('.custom-search').on('keyup', function() {
                    table.search(this.value).draw();
                });
                $('.modal-overlay').on('click', function(e) {
                    if (e.target === this) closeCustomModal(this.id);
                });
            });

            function calculatePaketTotal() {
                const harga = parseFloat($('#harga').val()) || 0;
                const pajak = parseFloat($('#pajak').val()) || 0;
                const diskon = parseFloat($('#diskon').val()) || 0;
                const total = Math.max(0, harga + pajak - diskon);

                $('#previewTotalTagihan').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
            }

            function openPaketModal() {
                $('#modalPaketTitle').text('Tambah Paket Layanan');
                $('#formPaket').attr('action', '{{ route('pakets.store') }}');
                $('#paketMethod').val('POST');

                $('#nama_paket').val('');
                $('#harga').val('');
                $('#pajak').val('');
                $('#diskon').val('');
                $('#rate_limit').val('');
                $('#keterangan').val('');
                calculatePaketTotal();

                const modal = document.getElementById('formPaketModal');
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function editPaketModal(id, nama, harga, pajak, diskon, rate_limit, ket) {
                $('#modalPaketTitle').text('Edit Paket Layanan');
                $('#formPaket').attr('action', `/pakets/${id}`);
                $('#paketMethod').val('PUT');

                $('#nama_paket').val(nama);
                $('#harga').val(harga);
                $('#pajak').val(pajak || 0);
                $('#diskon').val(diskon || 0);
                $('#rate_limit').val(rate_limit);
                $('#keterangan').val(ket);
                calculatePaketTotal();

                const modal = document.getElementById('formPaketModal');
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
        </script>
    @endsection
</x-layouts.app>
