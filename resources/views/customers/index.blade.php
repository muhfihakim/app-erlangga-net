<x-layouts.app>
    @section('Css')
        <link rel="stylesheet" href="{{ asset('dist/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/responsive.bootstrap4.min.css') }}">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endsection
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">Aktivasi</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Aktivasi</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Data Keseluruhan Aktivasi</h3>
                </div>
                <div class="card-body">
                    @if (Auth::user()->jabatan === 'Administrator')
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="text-align: center">No</th>
                                    <th>Tgl PO</th>
                                    <th>Sales</th>
                                    <th>Nama</th>
                                    <th>Paket</th>
                                    <th>Ditugaskan Kepada</th>
                                    <th>Opsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allPreOrders as $po)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ \Carbon\Carbon::parse($po->created_at)->format('d-m-Y') }}</td>
                                        <td>{{ $po->nama_sales }}</td>
                                        <td>{{ $po->nama_lengkap }}</td>
                                        <td>{{ $po->paket_internet }}</td>
                                        <td>
                                            @if ($po->id_teknisi_delegasi)
                                                <span class="badge bg-info">{{ $po->delegated_to }}</span>
                                                <p class="small text-muted mb-0">
                                                    {{ \Carbon\Carbon::parse($po->delegated_at)->format('d/m H:i') }}
                                                </p>
                                            @else
                                                <span class="badge bg-secondary">Belum Ditunjuk</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                </div>
            </div>
        @else
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">1. Tugas Delegasi Khusus (Prioritas)</h3>
                </div>
                <div class="card-body">
                    <table id="delegatedTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center">No</th>
                                <th style="text-align: center">Tgl PO</th>
                                <th style="text-align: center">Sales</th>
                                <th style="text-align: center">Nama</th>
                                <th style="text-align: center">Paket</th>
                                <th style="text-align: center">Status</th>
                                <th style="text-align: center">Deadline</th>
                                <th style="text-align: center">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($delegatedTasks as $po)
                                <tr>
                                    <td style="text-align: center; vertical-align: middle">{{ $loop->iteration }}</td>
                                    <td style="text-align: center; vertical-align: middle">
                                        {{ \Carbon\Carbon::parse($po->created_at)->format('d-m-Y') }}</td>
                                    <td style="text-align: center; vertical-align: middle">{{ $po->nama_sales }}</td>
                                    <td style="text-align: center; vertical-align: middle">{{ $po->nama_lengkap }}</td>
                                    <td style="text-align: center; vertical-align: middle">{{ $po->paket_internet }}
                                    </td>

                                    {{-- Kolom Status --}}
                                    <td style="text-align: center; vertical-align: middle">
                                        @if ($po->is_locked)
                                            <span class="badge bg-danger">EXPIRED</span>
                                        @elseif ($po->deadline_status == 'SEGERA')
                                            <span class="badge bg-warning text-dark">SEGERA</span>
                                        @else
                                            <span class="badge bg-success">AKTIF</span>
                                        @endif
                                    </td>

                                    {{-- Kolom Deadline --}}
                                    <td style="text-align: center; vertical-align: middle">
                                        {{ $po->deadline_date ?? '-' }}
                                    </td>

                                    <td style="text-align: center; vertical-align: middle">
                                        <a href="https://wa.me/{{ $po->no_whatsapp }}" target="_blank"
                                            class="btn btn-success btn-sm"><i class="bi bi-whatsapp"></i>
                                        </a>
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $po->titik_koordinat }}"
                                            target="_blank" class="btn btn-sm btn-info">
                                            <i class="bi bi-geo-alt-fill"></i>
                                        </a>
                                        {{-- LOGIKA LOCKED STATUS --}}
                                        @if ($po->is_locked)
                                            <button type="button" class="btn btn-danger btn-sm" disabled>
                                                <i class="bi bi-lock-fill"></i> Locked
                                            </button>
                                            <p class="text-danger small mt-1 mb-0" style="font-size: 0.75rem;">Hubungi
                                                Admin</p>
                                        @else
                                            <button type="button" class="btn btn-success btn-sm activate-btn"
                                                data-bs-toggle="modal" data-bs-target="#activationModal"
                                                data-po-id="{{ $po->id }}"
                                                data-po-sales-id="{{ $po->id_sales }}"
                                                data-po-nama="{{ $po->nama_lengkap }}"
                                                data-po-paket="{{ $po->paket_internet }}">
                                                <i class="bi bi-person-check"></i> Aktivasi
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card card-secondary mt-4">
                <div class="card-header">
                    <h3 class="card-title">2. Tugas Umum</h3>
                </div>
                <div class="card-body">
                    <table id="generalTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center">No</th>
                                <th style="text-align: center">Tgl PO</th>
                                <th style="text-align: center">Sales</th>
                                <th style="text-align: center">Nama</th>
                                <th style="text-align: center">Paket</th>
                                <th style="text-align: center">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($generalTasks as $po)
                                <tr>
                                    <td style="text-align: center; vertical-align: middle">{{ $loop->iteration }}</td>
                                    <td style="text-align: center; vertical-align: middle">
                                        {{ \Carbon\Carbon::parse($po->created_at)->format('d-m-Y') }}</td>
                                    <td style="text-align: center; vertical-align: middle">{{ $po->nama_sales }}</td>
                                    <td style="text-align: center; vertical-align: middle">{{ $po->nama_lengkap }}</td>
                                    <td style="text-align: center; vertical-align: middle">{{ $po->paket_internet }}
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <a href="https://wa.me/{{ $po->no_whatsapp }}" target="_blank"
                                            class="btn btn-success btn-sm"><i class="bi bi-whatsapp"></i>
                                        </a>
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $po->titik_koordinat }}"
                                            target="_blank" class="btn btn-sm btn-info">
                                            <i class="bi bi-geo-alt-fill"></i>
                                        </a>
                                        <button type="button" class="btn btn-success btn-sm activate-btn"
                                            data-bs-toggle="modal" data-bs-target="#activationModal"
                                            data-po-id="{{ $po->id }}" data-po-sales-id="{{ $po->id_sales }}"
                                            data-po-nama="{{ $po->nama_lengkap }}"
                                            data-po-paket="{{ $po->paket_internet }}">
                                            <i class="bi bi-person-check"></i> Aktivasi
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        <div class="modal fade" id="activationModal" tabindex="-1" aria-labelledby="activationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="activationModalLabel">Formulir Aktivasi Pelanggan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form id="activationForm" method="POST" action="{{ route('customers.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" id="modal_pre_order_id" name="pre_order_id" required>
                            <input type="hidden" id="modal_id_sales" name="id_sales" required>
                            <input type="hidden" name="id_teknisi" value="{{ auth()->id() }}">

                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Data Pre-Order</h6>
                                    <div class="mb-3">
                                        <label for="po_nama_lengkap" class="form-label">Nama Pelanggan
                                            (PO)</label>
                                        <input type="text" class="form-control" id="po_nama_lengkap" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="po_paket" class="form-label">Paket Internet (PO)</label>
                                        <input type="text" class="form-control" id="po_paket" readonly>
                                    </div>
                                    {{-- <div class="mb-3">
                                        <label for="partner_ids" class="form-label">Partner / Teknisi Tambahan
                                            (Opsional)</label>
                                        <select class="form-control select2" id="partner_ids" name="partner_ids[]"
                                            multiple="multiple" data-placeholder="Pilih Rekan Teknisi">
                                            @foreach ($teknisiUsers as $t)
                                                @if ($t->id != auth()->id())
                                                    <option value="{{ $t->id }}">{{ $t->nama_lengkap }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="partner" id="partner_names">
                                    </div> --}}
                                    <div class="mb-3">
                                        <label class="form-label d-block fw-bold">Partner / Teknisi Tambahan</label>
                                        <div class="card card-secondary p-3"
                                            style="max-height: 200px; overflow-y: auto; background-color: #f8f9fa;">
                                            <div class="row">
                                                @forelse ($teknisiUsers as $t)
                                                    {{-- Jangan munculkan diri sendiri dalam pilihan partner --}}
                                                    @if ($t->id != auth()->id())
                                                        <div class="col-md-6 col-sm-12 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input partner-checkbox"
                                                                    type="checkbox" name="partner_ids[]"
                                                                    value="{{ $t->id }}"
                                                                    id="partner_{{ $t->id }}">
                                                                <label class="form-check-label"
                                                                    for="partner_{{ $t->id }}">
                                                                    {{ $t->nama_lengkap }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @empty
                                                    <div class="col-12 text-muted small italic">
                                                        Tidak ada teknisi lain yang tersedia.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                        {{-- Hidden input tetap dipertahankan jika controller Anda masih membutuhkannya,
         namun biasanya controller akan memproses array partner_ids[] secara langsung --}}
                                        <input type="hidden" name="partner" id="partner_names">
                                    </div>
                                    <div class="mb-3">
                                        <label for="keterangan" class="form-label">Keterangan Aktivasi</label>
                                        <textarea class="form-control" id="keterangan" name="keterangan" rows="2"></textarea>
                                    </div>
                                    <h6 class="text-primary mt-4">Dokumentasi Foto</h6>
                                    <div class="mb-3">
                                        <label for="foto_petugas" class="form-label">Foto Petugas (Wajah
                                            Teknisi)</label>
                                        <input type="file" class="form-control" id="foto_petugas"
                                            name="foto_petugas" accept="image/*" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="foto_terpasang" class="form-label">Foto Perangkat
                                            Terpasang</label>
                                        <input type="file" class="form-control" id="foto_terpasang"
                                            name="foto_terpasang" accept="image/*" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="foto_speedtest" class="form-label">Foto Hasil
                                            Speedtest</label>
                                        <input type="file" class="form-control" id="foto_speedtest"
                                            name="foto_speedtest" accept="image/*" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-success">Data Teknis & Billing</h6>
                                    <div class="mb-3">
                                        <label for="id_pelanggan" class="form-label">ID Pelanggan
                                            (Billing)</label>
                                        <input type="text" class="form-control" id="id_pelanggan"
                                            name="id_pelanggan" placeholder="123456" required autocomplete="off">
                                    </div>
                                    <div class="mb-3">
                                        <label for="pppoe_username" class="form-label">Akun PPPoE
                                            (Username)</label>
                                        <input type="text" class="form-control" id="pppoe_username"
                                            name="pppoe_username" placeholder="pppoe@erlangga.net" required
                                            autocomplete="off">
                                    </div>
                                    <div class="mb-3">
                                        <label for="pppoe_password" class="form-label">Sandi PPPoE</label>
                                        <input type="text" class="form-control" id="pppoe_password"
                                            name="pppoe_password" placeholder="123" required autocomplete="off">
                                    </div>
                                    {{-- <div class="mb-3">
                                        <label for="jenis_modem" class="form-label">Jenis Modem</label>
                                        <select class="form-control" id="jenis_modem" name="jenis_modem" required>
                                            <option value="">Pilih Jenis Modem</option>
                                            <option value="EPON">EPON</option>
                                            <option value="GPON">GPON</option>
                                        </select>
                                    </div> --}}
                                    <div class="mb-3">
                                        <label class="form-label d-block fw-bold">Jenis Modem</label>
                                        <div class="d-flex flex-wrap mt-2">
                                            <!-- Radio EPON -->
                                            <div class="custom-control custom-radio mr-4">
                                                <input class="custom-control-input" type="radio" id="modem_epon"
                                                    name="jenis_modem" value="EPON" required>
                                                <label for="modem_epon" class="custom-control-label">EPON</label>
                                            </div>

                                            <!-- Radio GPON -->
                                            <div class="custom-control custom-radio">
                                                <input class="custom-control-input custom-control-input-primary"
                                                    type="radio" id="modem_gpon" name="jenis_modem" value="GPON"
                                                    required>
                                                <label for="modem_gpon" class="custom-control-label">GPON</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="sn_perangkat" class="form-label">SN Perangkat ONT</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="sn_perangkat"
                                                name="sn_perangkat" required autocomplete="off">
                                            <button class="btn btn-outline-secondary" type="button" id="start-scan">
                                                <i class="fas fa-qrcode"></i> Scan
                                            </button>
                                        </div>
                                    </div>

                                    <div id="reader-container" style="display:none; margin-top: 10px;">
                                        <div id="reader" style="width: 100%;"></div>
                                        <button type="button" class="btn btn-danger btn-sm mt-2"
                                            id="stop-scan">Tutup Kamera</button>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label d-block fw-bold">Metode Pembayaran</label>
                                        <div class="d-flex flex-wrap mt-2">
                                            <!-- Radio Cash -->
                                            <div class="custom-control custom-radio mr-4">
                                                <input class="custom-control-input" type="radio" id="pay_cash"
                                                    name="pembayaran" value="Cash" required>
                                                <label for="pay_cash" class="custom-control-label">Cash</label>
                                            </div>
                                            <!-- Radio Aplikasi -->
                                            <div class="custom-control custom-radio">
                                                <input class="custom-control-input custom-control-input-primary"
                                                    type="radio" id="pay_aplikasi" name="pembayaran"
                                                    value="Aplikasi" required>
                                                <label for="pay_aplikasi"
                                                    class="custom-control-label">Aplikasi</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success btn-sm">Aktivasi Pelanggan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.min.js') }}"></script>
        <script src="{{ asset('dist/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('dist/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('dist/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('dist/js/responsive.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('dist/js/dataTables.buttons.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://unpkg.com/html5-qrcode"></script>

        <script>
            const html5QrCode = new Html5Qrcode("reader");
            const snInput = document.getElementById('sn_perangkat');
            const readerContainer = document.getElementById('reader-container');

            const config = {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 150
                } // Menyesuaikan bentuk barcode SN yang biasanya memanjang
            };

            document.getElementById('start-scan').addEventListener('click', function() {
                readerContainer.style.display = 'block';

                html5QrCode.start({
                        facingMode: "environment"
                    }, // Menggunakan kamera belakang
                    config,
                    (decodedText) => {
                        // Jika berhasil scan:
                        snInput.value = decodedText; // Isi input otomatis
                        stopScanner(); // Matikan kamera
                        alert("Scan Berhasil: " + decodedText);
                    },
                    (errorMessage) => {
                        // Abaikan error saat mencari barcode (terus scanning)
                    }
                ).catch((err) => {
                    alert("Gagal mengakses kamera: " + err);
                });
            });

            document.getElementById('stop-scan').addEventListener('click', stopScanner);

            function stopScanner() {
                html5QrCode.stop().then(() => {
                    readerContainer.style.display = 'none';
                }).catch((err) => {
                    console.error("Gagal menghentikan scanner", err);
                });
            }
        </script>
        <script>
            $(function() {
                $("#example1").DataTable({
                    "responsive": true,
                    "lengthChange": false,
                    "autoWidth": false,
                    "ordering": false,
                });
                $("#delegatedTable").DataTable({
                    "responsive": true,
                    "lengthChange": true,
                    "autoWidth": false,
                    "ordering": false,
                });
                $("#generalTable").DataTable({
                    "responsive": true,
                    "lengthChange": true,
                    "autoWidth": false,
                    "ordering": false
                });
            });
        </script>
        <script>
            $(document).ready(function() {
                // Inisialisasi Select2
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });

                // Otomatis mengisi input hidden 'partner' dengan nama-nama yang dipilih
                $('#partner_ids').on('change', function() {
                    let selectedNames = $(this).find('option:selected').map(function() {
                        return $(this).text().trim();
                    }).get().join(', ');

                    $('#partner_names').val(selectedNames);
                });
            });
        </script>
        <script>
            // Logika untuk menampilkan SweetAlert
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 3000
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '{{ session('error') }}',
                });
            @endif
        </script>
        <script>
            // Listener untuk tombol Aktivasi di tabel
            document.addEventListener('DOMContentLoaded', function() {
                const activationModal = document.getElementById('activationModal');

                if (activationModal) {
                    activationModal.addEventListener('show.bs.modal', function(event) {
                        const button = event.relatedTarget;

                        // Ambil data PO dari tombol
                        const poId = button.getAttribute('data-po-id');
                        const poSalesId = button.getAttribute('data-po-sales-id');
                        const poNama = button.getAttribute('data-po-nama');
                        const poPaket = button.getAttribute('data-po-paket');

                        // Isi hidden field dan field readonly di modal
                        document.getElementById('modal_pre_order_id').value = poId;
                        document.getElementById('modal_id_sales').value = poSalesId;
                        document.getElementById('po_nama_lengkap').value = poNama;
                        document.getElementById('po_paket').value = poPaket;

                        // Reset form input teknis agar tidak ada sisa data lama
                        document.getElementById('id_pelanggan').value = '';
                        document.getElementById('pppoe_username').value = '';
                        document.getElementById('pppoe_password').value = '';
                        document.getElementById('sn_perangkat').value = '';
                        document.getElementById('jenis_modem').selectedIndex = 0;
                        document.getElementById('pembayaran').selectedIndex = 0;
                        document.getElementById('partner').value = '';
                        document.getElementById('keterangan').value = '';
                    });
                }
            });
        </script>
    @endsection
</x-layouts.app>
