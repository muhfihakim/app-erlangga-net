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
                padding: 1rem 1.25rem;
                gap: 4px;
                border-top: 1px solid #f1f5f9;
            }

            .paginate_button {
                width: 32px;
                height: 32px;
                border-radius: 8px;
                border: 1.5px solid #e2e8f0;
                background: #fff;
                color: #64748b !important;
                font-size: 12px;
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
        </style>
    @endsection

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Manajemen Paket Layanan</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola data paket layanan yang tersedia.</p>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <!-- Tombol Sync / Kirim ke MikroTik -->
            <form action="{{ route('pakets.sync_to_mikrotik') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold !bg-indigo-600 hover:!bg-indigo-700" 
                    onclick="return confirm('Kirim dan sinkronkan seluruh paket layanan dari database ke MikroTik (PPP Profile, IP Pool & FreeRADIUS)?')">
                    Sync ke MikroTik
                </button>
            </form>

            <!-- Tombol Tarik Profile dari MikroTik -->
            <form action="{{ route('paket.sync') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold !bg-emerald-600 hover:!bg-emerald-700" 
                    onclick="return confirm('Tarik semua Profile dari MikroTik dan masukkan ke database lokal?')">
                    Tarik dari MikroTik
                </button>
            </form>

            <!-- Tombol Tambah Paket Baru -->
            <button type="button" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold"
                onclick="openPaketModal()">
                Tambah
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
                        <th style="text-align: center; width: 45px;">#</th>
                        <th style="text-align: left;">Nama Paket</th>
                        <th style="text-align: right;">Harga Pokok</th>
                        <th style="text-align: right;">Pajak (Rp)</th>
                        <th style="text-align: right;">Diskon (Rp)</th>
                        <th style="text-align: right;">Total Tagihan</th>
                        <th style="text-align: center;">Limitasi (Radius)</th>
                        <th style="text-align: left;">IP Pool & Gateway</th>
                        <th style="text-align: center; width: 90px;">Pengguna</th>
                        <th style="text-align: center; width: 90px;">Opsi</th>
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
                            <td style="text-align: left;">
                                @if($paket->ip_pool || $paket->local_address)
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if($paket->ip_pool)
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold font-mono bg-indigo-50 text-indigo-700 border border-indigo-200" title="IP Pool">
                                                {{ $paket->ip_pool }}
                                            </span>
                                        @endif
                                        @if($paket->local_address)
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-mono text-slate-600 bg-slate-100 border border-slate-200" title="Local Gateway">
                                                {{ $paket->local_address }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($paket->pool_range)
                                        <div class="text-[10px] font-mono text-slate-400 mt-0.5 truncate max-w-[200px]" title="Range: {{ $paket->pool_range }}">
                                            {{ $paket->pool_range }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="badge text-[10px] font-bold py-0.5 px-2" style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;">
                                    {{ number_format($paket->customers_count) }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                        onclick="editPaketModal({{ $paket->id }}, @js($paket->nama_paket), @js($paket->harga), @js($paket->pajak ?? 0), @js($paket->diskon ?? 0), @js($paket->rate_limit), @js($paket->keterangan), @js($paket->local_address), @js($paket->ip_pool), @js($paket->pool_range))"
                                        title="Edit"><i class="fa-solid fa-pen-to-square text-slate-500 hover:text-amber-600"></i></button>
                                    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                        onclick="confirmGlobalDelete('{{ route('pakets.destroy', $paket->id) }}', 'Paket {{ $paket->nama_paket }}')"
                                        title="Hapus"><i class="fa-solid fa-trash text-slate-500 hover:text-rose-600"></i></button>
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
                
                <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 m-0" id="modalPaketTitle">Tambah Paket Layanan</h3>
                            <p class="text-[11px] text-slate-500 font-normal m-0 mt-0.5">Konfigurasi profil tarif, bandwidth & IP pool</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('formPaketModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body p-6 bg-white">
                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Nama Paket <span class="text-rose-500">*</span></label>
                        <input type="text" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs"
                            id="nama_paket" name="nama_paket" required placeholder="Contoh: Bronze 10Mbps">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 mb-3.5">
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Harga Pokok (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" id="harga"
                                name="harga" required min="0" placeholder="0" oninput="calculatePaketTotal()">
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Pajak (Rp)</label>
                            <input type="number" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" id="pajak"
                                name="pajak" min="0" placeholder="0" oninput="calculatePaketTotal()">
                            <span class="text-[10.5px] text-slate-400 mt-1 block">PPN / Fee Admin</span>
                        </div>
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Diskon (Rp)</label>
                            <input type="number" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" id="diskon"
                                name="diskon" min="0" placeholder="0" oninput="calculatePaketTotal()">
                            <span class="text-[10.5px] text-slate-400 mt-1 block">Potongan Paket</span>
                        </div>
                    </div>

                    <!-- Total Tagihan Preview Card -->
                    <div class="p-3 rounded-xl border border-sky-200 bg-sky-50/70 flex items-center justify-between mb-3.5">
                        <div>
                            <div class="text-[11px] font-bold text-sky-800 uppercase tracking-wide">Total Tagihan Final Paket</div>
                            <div class="text-[11px] text-sky-600">Harga Pokok + Pajak - Diskon</div>
                        </div>
                        <div class="text-base font-black text-sky-700" id="previewTotalTagihan">
                            Rp 0
                        </div>
                    </div>

                    <div class="form-group mb-3.5">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Limit Bandwidth (Radius)</label>
                        <input type="text" class="form-input !font-normal bg-white text-slate-800 border-slate-300 text-xs" id="rate_limit"
                            name="rate_limit" placeholder="Contoh: 10M/20M">
                        <span class="text-[11px] text-slate-400 mt-1 block">Format: [Upload]/[Download], contoh: <code>5M/10M</code> atau <code>10M/20M</code></span>
                    </div>

                    <!-- Alokasi IP Pool & Gateway MikroTik -->
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 mb-3.5 space-y-3">
                        <div class="flex items-center gap-2 text-slate-700 font-bold text-xs uppercase tracking-wider">
                            <i class="fa-solid fa-network-wired text-slate-500"></i>
                            <span>Pengaturan IP Pool & Gateway MikroTik / FreeRADIUS</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Local Address (Gateway)</label>
                                <input type="text" class="form-input !font-normal bg-white text-slate-800 border-slate-300 font-mono text-xs" 
                                    id="local_address" name="local_address" placeholder="Contoh: 182.10.0.1">
                                <span class="text-[10px] text-slate-400 mt-1 block">IP Gateway lokal PPPoE (contoh: <code>182.10.0.1</code>)</span>
                            </div>

                            <div class="form-group !mb-0">
                                <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Nama IP Pool</label>
                                <input type="text" class="form-input !font-normal bg-white text-slate-800 border-slate-300 font-mono text-xs" 
                                    id="ip_pool" name="ip_pool" placeholder="Contoh: pool-10m">
                                <span class="text-[10px] text-slate-400 mt-1 block">Nama pool di MikroTik & RADIUS (contoh: <code>pool-10m</code>)</span>
                            </div>
                        </div>

                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Rentang IP Pool (Pool Range)</label>
                            <input type="text" class="form-input !font-normal bg-white text-slate-800 border-slate-300 font-mono text-xs" 
                                id="pool_range" name="pool_range" placeholder="Contoh: 182.10.0.2-182.10.255.254">
                            <span class="text-[10px] text-slate-400 mt-1 block">Rentang IP pool pelanggan (contoh: <code>182.10.0.2-182.10.255.254</code>)</span>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label text-slate-700 font-medium text-xs mb-1.5 block">Keterangan Tambahan</label>
                        <textarea class="form-textarea !font-normal bg-white text-slate-800 border-slate-300 text-xs" id="keterangan" name="keterangan"
                            rows="2" placeholder="Informasi tambahan..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-end gap-2.5">
                    <button type="button" class="btn-cancel !h-9 !px-4 text-xs font-semibold"
                        onclick="closeCustomModal('formPaketModal')">
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
                $('#local_address').val('');
                $('#ip_pool').val('');
                $('#pool_range').val('');
                $('#keterangan').val('');
                calculatePaketTotal();

                const modal = document.getElementById('formPaketModal');
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function editPaketModal(id, nama, harga, pajak, diskon, rate_limit, ket, local_address, ip_pool, pool_range) {
                $('#modalPaketTitle').text('Edit Paket Layanan');
                $('#formPaket').attr('action', `/pakets/${id}`);
                $('#paketMethod').val('PUT');

                $('#nama_paket').val(nama);
                $('#harga').val(harga);
                $('#pajak').val(pajak || 0);
                $('#diskon').val(diskon || 0);
                $('#rate_limit').val(rate_limit);
                $('#local_address').val(local_address || '');
                $('#ip_pool').val(ip_pool || '');
                $('#pool_range').val(pool_range || '');
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