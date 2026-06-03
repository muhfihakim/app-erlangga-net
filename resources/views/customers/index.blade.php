<x-layouts.app>
    @section('Css')
        <style>
            /* Toolbar & Search */
            .table-toolbar {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 1rem 1.25rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
                margin-bottom: 1rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }

            .search-wrap {
                position: relative;
                flex: 1;
                min-width: 200px;
                max-width: 360px;
            }

            .search-icon {
                position: absolute;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                color: #94a3b8;
                font-size: 13px;
                pointer-events: none;
            }

            .search-input {
                width: 100%;
                padding: 9px 12px 9px 36px;
                border: 2px solid #e2e8f0;
                border-radius: 10px;
                font-size: 13px;
                font-family: 'Inter', sans-serif;
                color: #374151;
                outline: none;
                transition: all .25s;
                background: #f8fafc;
            }

            .search-input:focus {
                border-color: #0ea5e9;
                background: #fff;
            }

            /* Full Capitalization for Table */
            #customer-table thead th,
            #customer-table tbody td,
            #generalTasksTable thead th,
            #generalTasksTable tbody td {
                text-transform: uppercase !important;
                font-weight: 600;
            }

            #customer-table tbody td,
            #generalTasksTable tbody td {
                font-size: 12px;
            }

            /* Scanner container styling */
            #reader-container {
                display: none;
                margin-bottom: 20px;
                border: 2px solid #0ea5e9;
                border-radius: 14px;
                overflow: hidden;
                box-shadow: 0 4px 12px rgba(14, 165, 233, 0.15);
            }
        </style>
    @endsection

    <!-- Page header -->
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Tugas Aktivasi Baru</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Daftar penugasan aktivasi pelanggan yang siap
                dikerjakan.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('dashboards.index') }}" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                style="background: #f1f5f9; color: #475569; box-shadow: none; border: 1px solid #e2e8f0;"
                onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
        </div>
    </div>

    @if (auth()->user()->jabatan === 'Administrator')
        <div class="stats-grid mb-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                    <i class="fa-solid fa-hourglass-start"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $allPreOrders->count() }}</div>
                    <div class="stat-lbl">Menunggu Aktivasi</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $allPreOrders->whereNotNull('id_teknisi_delegasi')->count() }}</div>
                    <div class="stat-lbl">Sudah Ditunjuk</div>
                </div>
            </div>
        </div>

        <div class="table-toolbar">
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="search" class="search-input custom-search" placeholder="Cari data pre-order..."
                    autocomplete="off" />
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="customer-table" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">#</th>
                            <th style="text-align: center; width: 100px;">Tgl PO</th>
                            <th style="text-align: center; width: 150px;">Sales</th>
                            <th style="text-align: center; width: 200px;">Nama Pelanggan</th>
                            <th style="text-align: center; width: 150px;">Paket</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allPreOrders as $po)
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}
                                </td>
                                <td style="text-align: center; color: #64748b; font-size: 12px; font-weight: 600;">
                                    {{ \Carbon\Carbon::parse($po->created_at)->format('d-m-Y') }}
                                </td>
                                <td style="text-align: center; font-weight: 600;">{{ $po->nama_sales }}</td>
                                <td style="text-align: center; font-weight: 700; color: #0f172a;">
                                    {{ $po->nama_lengkap }}</td>
                                <td style="text-align: center;">
                                    <span class="badge"
                                        style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 10px;">
                                        {{ $po->paket_internet }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- Stats cards for Technician -->
        <div class="stats-grid mb-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#f1f5f9;color:#475569;">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <div class="stat-val">{{ $generalTasks->count() }}</div>
                    <div class="stat-lbl">Total Tugas Aktif</div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="table-toolbar">
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="search" class="search-input custom-search"
                    placeholder="Cari nama pelanggan, paket, atau alamat..." autocomplete="off" />
            </div>
            <div style="display:flex;gap:8px;">
                <div
                    style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    Pool Tugas
                </div>
            </div>
        </div>

        <div class="table-card mb-8">
            <div class="table-responsive">
                <table id="generalTasksTable" class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">#</th>
                            <th style="text-align: center; width: 100px;">Tgl PO</th>
                            <th style="text-align: center;">Nama Pelanggan</th>
                            <th style="text-align: center;">Paket</th>
                            <th style="text-align: center; width: 150px;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($generalTasks as $po)
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                    {{ $loop->iteration }}</td>
                                <td style="text-align: center; color: #64748b; font-size: 12px; font-weight: 600;">
                                    {{ \Carbon\Carbon::parse($po->created_at)->format('d-m-Y') }}
                                </td>
                                <td style="text-align: left;">
                                    <div style="font-weight: 700; color: #0f172a;">{{ $po->nama_lengkap }}</div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                        {{ $po->alamat_lengkap }}
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge"
                                        style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 10px;">
                                        {{ $po->paket_internet }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div
                                        style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            title="Detail" onclick="openActivationDetailModal(this)"
                                            data-nama="{{ $po->nama_lengkap }}"
                                            data-email="{{ $po->alamat_email ?? '-' }}"
                                            data-wa="{{ $po->no_whatsapp }}"
                                            data-alamat="{{ $po->alamat_lengkap }}"
                                            data-paket="{{ $po->paket_internet }}"
                                            data-koordinat="{{ $po->titik_koordinat }}"
                                            data-sales="{{ $po->nama_sales }}"
                                            data-tgl="{{ \Carbon\Carbon::parse($po->created_at)->format('d/m/Y') }}">
                                            <i class="fa-solid fa-circle-info text-primary"></i>
                                        </button>
                                        <button type="button"
                                            class="btn-primary-admin !h-9 !px-3 !text-[11px] activate-btn !flex !items-center !justify-center gap-1"
                                            onclick="openActivationModal(this)" data-po-id="{{ $po->id }}"
                                            data-po-sales-id="{{ $po->id_sales }}"
                                            data-po-nama="{{ $po->nama_lengkap }}"
                                            data-po-paket="{{ $po->paket_internet }}">
                                            <i class="fa-solid fa-bolt"></i> Ambil & Aktivasi
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- MODAL AREA --}}
    @include('customers.modal')

    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="https://unpkg.com/html5-qrcode"></script>

        <script>
            $(document).ready(function() {
                // Shared DataTable Options
                const dtOptions = {
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
                };

                const adminTable = $('#customer-table').DataTable(dtOptions);
                const generalTable = $('#generalTasksTable').DataTable(dtOptions);

                // Custom Search (Live Search)
                $('.custom-search').on('keyup', function() {
                    const val = this.value;
                    if (adminTable) adminTable.search(val).draw();
                    if (generalTable) generalTable.search(val).draw();
                });

                // Tab switching
                $('.modal-tab').on('click', function() {
                    const tabId = $(this).data('tab');
                    const $modalBox = $(this).closest('.modal-box');
                    $modalBox.find('.modal-tab').removeClass('active');
                    $(this).addClass('active');
                    $modalBox.find('.tab-panel').removeClass('active');
                    $('#' + tabId).addClass('active');
                });

                // Close modal on overlay click
                $('.modal-overlay').on('click', function(e) {
                    if (e.target === this) closeCustomModal(this.id);
                });
            });

            function openCustomModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('open');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeCustomModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('open');
                    document.body.style.overflow = '';
                }
            }

            function openActivationDetailModal(triggerEl) {
                const $btn = $(triggerEl);
                $('#det_nama').text($btn.data('nama'));
                $('#det_email').text($btn.data('email'));
                $('#det_wa').text($btn.data('wa'));
                $('#det_alamat').text($btn.data('alamat'));
                $('#det_paket').text($btn.data('paket'));
                $('#det_sales').text($btn.data('sales'));
                $('#det_tgl').text($btn.data('tgl'));

                const wa = String($btn.data('wa') || '');
                const waFormatted = wa.startsWith('0') ? '62' + wa.substring(1) : wa;
                $('#det_wa_link').attr('href', `https://wa.me/${waFormatted}`);

                const tikor = $btn.data('koordinat');
                if (tikor && tikor !== 'N/A') {
                    $('#det_tikor_link').attr('href', `https://www.google.com/maps/search/?api=1&query=${tikor}`).show();
                } else {
                    $('#det_tikor_link').hide();
                }

                openCustomModal('detailActivationModal');
            }

            function openActivationModal(triggerEl) {
                const $btn = $(triggerEl);
                $('#form_po_id').val($btn.data('po-id'));
                $('#form_id_sales').val($btn.data('po-sales-id'));
                $('#display_nama_pelanggan').text($btn.data('po-nama'));
                $('#display_paket_internet').text($btn.data('po-paket'));
                openCustomModal('activationModal');
            }

            // --- QR Scanner Implementation ---
            let html5QrCode = null;

            function startScanner() {
                $('#reader-container').show();
                html5QrCode = new Html5Qrcode("reader");
                const config = {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    }
                };

                html5QrCode.start({
                    facingMode: "environment"
                }, config, (decodedText) => {
                    $('#sn_perangkat').val(decodedText);
                    stopScanner();
                    showToast('Serial Number berhasil dipindai!', 'success');
                }).catch((err) => {
                    console.error("Gagal start scanner", err);
                    showToast('Gagal mengakses kamera.', 'error');
                });
            }

            function stopScanner() {
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        $('#reader-container').hide();
                    }).catch((err) => {
                        console.error("Gagal stop scanner", err);
                    });
                }
            }
        </script>
    @endsection
</x-layouts.app>
