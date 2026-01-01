<x-layouts.app>
    @section('Css')
        <link rel="stylesheet" href="{{ asset('dist/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/responsive.bootstrap4.min.css') }}">
    @endsection
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">Pelanggan</h3>
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
                    <h3 class="card-title">
                        @if (auth()->user()->jabatan === 'Administrator')
                            Data Keseluruhan Pelanggan
                        @else
                            Pelanggan Anda
                        @endif
                    </h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('dashboards.index') }}" class="btn btn-secondary mb-3 btn-sm">← Kembali</a>
                    <table id="example1" class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="text-align: center">No</th>
                                <th style="text-align: center">ID Pelanggan</th>
                                <th style="text-align: center">Nama Pelanggan</th>
                                <th style="text-align: center">Paket</th>
                                <th style="text-align: center">No. WA</th>
                                <th style="text-align: center">Teknisi</th>
                                <th style="text-align: center">Tgl. Aktivasi</th>
                                <th style="text-align: center">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customers as $customer)
                                <tr class="{{ $customer->trashed() ?: '' }}">
                                    <td style="text-align: center; vertical-align: middle">{{ $loop->iteration }}</td>
                                    <td style="text-align: center; vertical-align: middle">{{ $customer->id_pelanggan }}
                                    </td>
                                    <td style="vertical-align: middle">
                                        <strong>{{ $customer->nama_lengkap }}</strong>
                                        @if ($customer->trashed())
                                            <span class="badge bg-secondary ms-1">Non-Aktif</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">PPPOE:
                                            {{ $customer->pppoe_username ?? '-' }}</small>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle">
                                        {{ $customer->paket_internet }}</td>
                                    <td style="text-align: center; vertical-align: middle">
                                        <a href="https://wa.me/{{ $customer->no_whatsapp }}" target="_blank"
                                            class="btn btn-success btn-sm">
                                            <i class="bi bi-whatsapp"></i> {{ $customer->no_whatsapp }}
                                        </a>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle">
                                        <strong>{{ $customer->teknisiUtama->nama_lengkap ?? 'N/A' }}</strong>
                                        @if ($customer->partner)
                                            <br><small class="text-muted">Partner: {{ $customer->partner }}</small>
                                        @endif
                                    </td>
                                    <td style="text-align: center; vertical-align: middle">
                                        {{ \Carbon\Carbon::parse($customer->created_at)->format('d-m-Y') }}</td>
                                    <td style="text-align: center; vertical-align: middle">
                                        <button type="button" class="btn btn-sm btn-info detail-po-btn"
                                            data-bs-toggle="modal" data-bs-target="#poDetailModal"
                                            data-customer-id="{{ $customer->id_pelanggan }}"
                                            data-pppoe-user="{{ $customer->pppoe_username }}"
                                            data-pppoe-pass="{{ $customer->pppoe_password }}"
                                            data-jenis-modem="{{ $customer->jenis_modem }}"
                                            data-sn-perangkat="{{ $customer->sn_perangkat }}"
                                            data-pembayaran="{{ $customer->pembayaran }}"
                                            data-tgl-aktivasi="{{ \Carbon\Carbon::parse($customer->created_at)->format('d-m-Y') }}"
                                            data-po-paket="{{ $customer->paket_internet }}"
                                            data-po-sales="{{ $customer->sales->nama_lengkap ?? 'N/A' }}"
                                            data-po-nik="{{ $customer->preOrder->no_identitas ?? 'N/A' }}"
                                            data-po-email="{{ $customer->preOrder->alamat_email ?? 'N/A' }}"
                                            data-po-alamat="{{ $customer->alamat_lengkap }}"
                                            data-po-wa="{{ $customer->no_whatsapp }}"
                                            data-po-tgl-po="{{ \Carbon\Carbon::parse($customer->preOrder->created_at ?? $customer->created_at)->format('d-m-Y') }}"
                                            data-po-tikor="{{ $customer->preOrder->titik_koordinat ?? 'N/A' }}"
                                            data-foto-petugas="{{ asset('storage/' . $customer->foto_petugas) }}"
                                            data-foto-terpasang="{{ asset('storage/' . $customer->foto_terpasang) }}"
                                            data-foto-speedtest="{{ asset('storage/' . $customer->foto_speedtest) }}">
                                            <i class="bi bi-search"></i>
                                        </button>

                                        @if (auth()->user()->jabatan === 'Administrator')
                                            @if ($customer->trashed())
                                                {{-- Tombol Aktifkan Kembali --}}
                                                <button class="btn btn-success btn-sm"
                                                    onclick="confirmRestore({{ $customer->id }}, '{{ $customer->nama_lengkap }}')">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Aktifkan
                                                </button>
                                                <form id="restore-form-{{ $customer->id }}"
                                                    action="{{ route('customers.restore', $customer->id) }}"
                                                    method="POST" style="display: none;">@csrf</form>
                                            @else
                                                {{-- Tombol Nonaktifkan (Destroy) --}}
                                                <button class="btn btn-danger btn-sm"
                                                    onclick="confirmDelete({{ $customer->id }}, '{{ $customer->nama_lengkap }}')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <form id="delete-form-{{ $customer->id }}"
                                                    action="{{ route('customers.destroy', $customer->id) }}"
                                                    method="POST" style="display: none;">@csrf @method('DELETE')</form>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal fade" id="poDetailModal" tabindex="-1" aria-labelledby="poDetailModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="poDetailModalLabel">Detail Pelanggan: <span
                                        id="detail_nama_pelanggan"></span></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="bi bi-info-circle-fill"></i> Data Pelanggan & PO</h6>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th>Nama Sales</th>
                                                <td id="detail_sales"></td>
                                            </tr>
                                            <tr>
                                                <th>Tgl. Pre-Order</th>
                                                <td id="detail_tgl_po"></td>
                                            </tr>
                                            <tr>
                                                <th>No. Identitas (NIK)</th>
                                                <td id="detail_nik"></td>
                                            </tr>
                                            <tr>
                                                <th>Alamat Email</th>
                                                <td id="detail_email"></td>
                                            </tr>
                                            <tr>
                                                <th>No. WhatsApp</th>
                                                <td id="detail_wa"></td>
                                            </tr>
                                            <tr>
                                                <th>Alamat Lengkap</th>
                                                <td id="detail_alamat_lengkap"></td>
                                            </tr>
                                            <tr>
                                                <th>Titik Koordinat</th>
                                                <td id="detail_tikor">
                                                    <a id="detail_tikor_link" href="#" target="_blank"
                                                        class="btn btn-sm btn-primary ms-2"><i
                                                            class="bi bi-geo-alt-fill"></i> Lihat di Map</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div class="col-md-6">
                                        <h6><i class="bi bi-tools"></i> Data Aktivasi & Teknis</h6>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th>ID Pelanggan</th>
                                                <td id="detail_customer_id"></td>
                                            </tr>
                                            <tr>
                                                <th>Paket Internet</th>
                                                <td id="detail_paket"></td>
                                            </tr>
                                            <tr>
                                                <th>PPPoE Username</th>
                                                <td id="detail_pppoe_user"></td>
                                            </tr>
                                            <tr>
                                                <th>PPPoE Password</th>
                                                <td id="detail_pppoe_pass"></td>
                                            </tr>
                                            <tr>
                                                <th>Jenis Modem</th>
                                                <td id="detail_jenis_modem"></td>
                                            </tr>
                                            <tr>
                                                <th>SN Perangkat</th>
                                                <td id="detail_sn"></td>
                                            </tr>
                                            <tr>
                                                <th>Metode Pembayaran</th>
                                                <td id="detail_pembayaran"></td>
                                            </tr>
                                            <tr>
                                                <th>Tgl. Aktivasi</th>
                                                <td id="detail_tgl_aktivasi"></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <hr>

                                <h6><i class="bi bi-camera-fill"></i> Dokumentasi Aktivasi</h6>
                                <div class="row text-center mt-3">
                                    <div class="col-md-4">
                                        <p class="fw-bold">Foto Petugas</p>
                                        <img id="img_petugas" src="" class="img-fluid img-thumbnail"
                                            alt="Foto Petugas">
                                    </div>
                                    <div class="col-md-4">
                                        <p class="fw-bold">Foto Perangkat Terpasang</p>
                                        <img id="img_terpasang" src="" class="img-fluid img-thumbnail"
                                            alt="Foto Terpasang">
                                    </div>
                                    <div class="col-md-4">
                                        <p class="fw-bold">Foto Speedtest</p>
                                        <img id="img_speedtest" src="" class="img-fluid img-thumbnail"
                                            alt="Foto Speedtest">
                                    </div>
                                </div>

                                <h6 class="mt-4"><i class="bi bi-file-text-fill"></i> Keterangan:</h6>
                                <p id="detail_keterangan"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm"
                                    data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
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

        <script>
            $(function() {
                $("#example1").DataTable({
                    "responsive": true,
                    "lengthChange": false,
                    "autoWidth": false,
                    "ordering": false,
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Logika untuk Modal Detail Pelanggan (poDetailModal)
                const poDetailModal = document.getElementById('poDetailModal');

                if (poDetailModal) {
                    poDetailModal.addEventListener('show.bs.modal', function(event) {
                        const button = event.relatedTarget;

                        // --- PENGAMBILAN DATA AKTIVASI & TEKNIS ---
                        // Pastikan nama attribute data-XXX di tombol HTML SAMA PERSIS dengan ini!
                        const customerId = button.getAttribute('data-customer-id');
                        const poPaket = button.getAttribute('data-po-paket');
                        const pppoeUser = button.getAttribute('data-pppoe-user');
                        const pppoePass = button.getAttribute('data-pppoe-pass');
                        const jenisModem = button.getAttribute('data-jenis-modem');
                        const snPerangkat = button.getAttribute('data-sn-perangkat');
                        const pembayaran = button.getAttribute('data-pembayaran');
                        const tglAktivasi = button.getAttribute('data-tgl-aktivasi');
                        const keterangan = button.getAttribute('data-keterangan');
                        const poNama = button.closest('tr').querySelector('td:nth-child(4)').textContent;

                        // --- PENGAMBILAN DATA FOTO ---
                        const fotoPetugas = button.getAttribute('data-foto-petugas');
                        const fotoTerpasang = button.getAttribute('data-foto-terpasang');
                        const fotoSpeedtest = button.getAttribute('data-foto-speedtest');

                        // --- PENGAMBILAN DATA PO (untuk kelengkapan) ---
                        const poTikor = button.getAttribute('data-po-tikor');
                        const poSales = button.getAttribute('data-po-sales');
                        const poTglPo = button.getAttribute('data-po-tgl-po');
                        const poNik = button.getAttribute('data-po-nik');
                        const poEmail = button.getAttribute('data-po-email');
                        const poWa = button.getAttribute('data-po-wa');
                        const poAlamat = button.getAttribute('data-po-alamat');

                        // --- PENGISIAN DATA PO & ADMINISTRATIF ---

                        // Judul
                        document.getElementById('detail_nama_pelanggan').textContent = poNama;

                        // Data Pelanggan & PO
                        document.getElementById('detail_sales').textContent = poSales;
                        document.getElementById('detail_tgl_po').textContent = poTglPo;
                        document.getElementById('detail_nik').textContent = poNik;
                        document.getElementById('detail_email').textContent = poEmail;
                        document.getElementById('detail_wa').textContent = poWa;
                        document.getElementById('detail_alamat_lengkap').textContent = poAlamat;

                        // Titik Koordinat
                        const detailTikorEl = document.getElementById('detail_tikor');
                        const tikorLink = document.getElementById('detail_tikor_link');

                        detailTikorEl.textContent = poTikor;
                        if (poTikor && poTikor !== 'N/A') {
                            tikorLink.href = `http://googleusercontent.com/maps.google.com/?q=${poTikor}`;
                            tikorLink.style.display = 'inline-block';
                        } else {
                            tikorLink.style.display = 'none';
                        }

                        // --- PENGISIAN DATA AKTIVASI & TEKNIS ---
                        document.getElementById('detail_customer_id').textContent = customerId;
                        // Paket Internet sudah ada di kolom table, atau ambil dari data-po-paket
                        document.getElementById('detail_paket').textContent = poPaket;
                        document.getElementById('detail_pppoe_user').textContent = pppoeUser;
                        document.getElementById('detail_pppoe_pass').textContent = pppoePass;
                        document.getElementById('detail_jenis_modem').textContent = jenisModem;
                        document.getElementById('detail_sn').textContent = snPerangkat;
                        document.getElementById('detail_pembayaran').textContent = pembayaran;
                        document.getElementById('detail_tgl_aktivasi').textContent = tglAktivasi;

                        // --- PENGISIAN FOTO ---
                        document.getElementById('img_petugas').src = fotoPetugas;
                        document.getElementById('img_terpasang').src = fotoTerpasang;
                        document.getElementById('img_speedtest').src = fotoSpeedtest;

                        // --- PENGISIAN KETERANGAN ---
                        document.getElementById('detail_keterangan').textContent = keterangan;
                    });
                }
            });
        </script>
        <script>
            function confirmDelete(id, name) {
                Swal.fire({
                    title: 'Nonaktifkan?',
                    text: "Pelanggan " + name + " akan dipindahkan ke daftar Non-Aktif.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Nonaktifkan'
                }).then((r) => {
                    if (r.isConfirmed) document.getElementById('delete-form-' + id).submit();
                });
            }

            function confirmRestore(id, name) {
                Swal.fire({
                    title: 'Aktifkan Kembali?',
                    text: "Pelanggan " + name + " akan berlangganan kembali.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Ya, Aktifkan'
                }).then((r) => {
                    if (r.isConfirmed) document.getElementById('restore-form-' + id).submit();
                });
            }
        </script>
    @endsection
</x-layouts.app>
