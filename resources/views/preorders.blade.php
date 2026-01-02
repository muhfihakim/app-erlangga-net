<x-layouts.app>
    @section('Css')
        <link rel="stylesheet" href="{{ asset('dist/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/responsive.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/buttons.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
                            <li class="breadcrumb-item active" aria-current="page">Pre-Order</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Data Keseluruhan Pre-Order</h3>
                </div>
                <div class="card-body">
                    {{-- Tombol Tambah PO, hanya untuk Sales --}}
                    @if (auth()->user()->jabatan === 'Sales' || auth()->user()->jabatan === 'Administrator')
                        <button type="button" class="btn btn-success mb-3 btn-sm" data-bs-toggle="modal"
                            data-bs-target="#addPreOrderModal">
                            <i class="bi bi-plus-square"></i> Tambah Pre-Order
                        </button>
                        <a href="{{ route('dashboards.index') }}" class="btn btn-secondary mb-3 btn-sm">← Kembali</a>
                    @endif

                    <table id="example1" class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="text-align: center">No</th>
                                <th style="text-align: center">Tgl PO</th>
                                <th style="text-align: center">Sales</th>
                                <th style="text-align: center">Nama</th>
                                <th style="text-align: center">Paket</th>
                                <th style="text-align: center">Alamat</th>
                                <th style="text-align: center">Foto</th>
                                <th style="text-align: center">No. WA</th>
                                <th style="text-align: center">Opsi</th>
                            </tr>
                        </thead>
                        {{-- <tbody>
                            @foreach ($preOrders as $po)
                                <tr>
                                    <td style="text-align: center; vertical-align: middle;">{{ $loop->iteration }}</td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{ \Carbon\Carbon::parse($po->created_at)->format('d-m-Y') }}</td>
                                    <td style="text-align: center; vertical-align: middle;">{{ $po->nama_sales }}</td>
                                    <td style="vertical-align: middle">{{ $po->nama_lengkap }}</td>
                                    <td style="vertical-align: middle">{{ $po->paket_internet }}
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#alamatModal"
                                            data-alamat-lengkap="{{ $po->alamat_lengkap }}">
                                            {{ Str::limit($po->alamat_lengkap, 15, '...') }}
                                        </a>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        @if ($po->foto_ktp)
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#viewKtpModal"
                                                data-ktp-url="{{ asset('storage/' . $po->foto_ktp) }}">
                                                <i class="bi bi-eye"></i> KTP
                                            </button>
                                        @else
                                            -
                                        @endif

                                        @if ($po->foto_rumah)
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#viewRumahModal"
                                                data-rumah-url="{{ asset('storage/' . $po->foto_rumah) }}">
                                                <i class="bi bi-eye"></i> Rumah
                                            </button>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <a href="https://wa.me/{{ $po->no_whatsapp }}" target="_blank"
                                            class="btn btn-success btn-sm">
                                            <i class="bi bi-whatsapp"></i> {{ $po->no_whatsapp }}
                                        </a>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#detailModal" data-identitas="{{ $po->no_identitas }}"
                                            data-email-lengkap="{{ $po->alamat_email }}">
                                            <i class="bi bi-person-lines-fill"></i>
                                        </button>

                                        @if ($po->titik_koordinat)
                                            <button type="button" class="btn btn-primary btn-sm show-map-btn"
                                                data-bs-toggle="modal" data-bs-target="#mapModal"
                                                data-koordinat="{{ $po->titik_koordinat }}">
                                                <i class="bi bi-geo-alt-fill"></i>
                                            </button>
                                        @else
                                            -
                                        @endif

                                        @if (Auth::user()->jabatan === 'Administrator')
                                            @php
                                                $isDelegated = $po->id_teknisi_delegasi !== null;
                                                $isExpired = $po->is_expired ?? false;
                                                $isSegeraExpired = $po->is_segera_expired ?? false;
                                                $isActiveAdmin = $po->is_active ?? false;
                                                $teknisi = $isDelegated
                                                    ? App\Models\User::find($po->id_teknisi_delegasi)
                                                    : null;
                                                $teknisiNama = $teknisi->nama_lengkap ?? 'N/A';
                                                $delegatedAt = $po->delegated_at
                                                    ? \Carbon\Carbon::parse($po->delegated_at)->format('d/m')
                                                    : '';
                                                $delegatedFullDate = $po->delegated_at
                                                    ? \Carbon\Carbon::parse($po->delegated_at)->format('d-m-Y')
                                                    : 'N/A';
                                            @endphp

                                            @if ($isActiveAdmin)
                                                <button type="button" class="btn btn-success btn-sm" disabled
                                                    title="Pelanggan Sudah Aktif">
                                                    <i class="bi bi-person-check"></i> AKTIF
                                                </button>
                                                <p class="text-success small mt-1 mb-0" style="font-size: 0.75rem;">
                                                    Lihat di Pelanggan Aktif</p>
                                            @elseif ($isDelegated)
                                                @php
                                                    $buttonClass = 'btn-secondary';
                                                    if ($isExpired) {
                                                        $buttonClass = 'btn-danger';
                                                    } elseif ($isSegeraExpired) {
                                                        $buttonClass = 'btn-warning text-dark';
                                                    }
                                                @endphp
                                                <button type="button" class="btn {{ $buttonClass }} btn-sm" disabled
                                                    title="Ditugaskan ke {{ $teknisiNama }} pada {{ $delegatedFullDate }}">
                                                    @if ($isExpired)
                                                        <i class="bi bi-clock-history"></i> EXPIRED
                                                    @elseif ($isSegeraExpired)
                                                        <i class="bi bi-hourglass-split"></i> SEGERA!
                                                    @else
                                                        <i class="bi bi-check-circle-fill"></i> Ditugaskan
                                                    @endif
                                                </button>
                                                <small
                                                    class="d-block mt-1 {{ $isExpired ? 'text-danger' : 'text-muted' }}"
                                                    style="font-size: 0.85rem;">
                                                    {{ Str::limit($teknisiNama, 10, '...') }} ({{ $delegatedAt }})
                                                </small>
                                                <button type="button" class="btn btn-danger btn-sm reset-btn mt-1"
                                                    data-bs-toggle="modal" data-bs-target="#resetModal"
                                                    data-po-id="{{ $po->id }}"
                                                    data-po-nama="{{ $po->nama_lengkap }}">
                                                    <i class="bi bi-x-circle"></i> Reset
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-info btn-sm delegate-btn"
                                                    data-bs-toggle="modal" data-bs-target="#delegateModal"
                                                    data-po-id="{{ $po->id }}"
                                                    data-po-nama="{{ $po->nama_lengkap }}">
                                                    <i class="bi bi-person-badge"></i> Tugas
                                                </button>
                                            @endif

                                            <button type="button" class="btn btn-warning btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-po-id="{{ $po->id }}"
                                                data-po-nama="{{ $po->nama_lengkap }}"
                                                data-po-paket="{{ $po->paket_internet }}"
                                                data-po-wa="{{ $po->no_whatsapp }}"
                                                data-po-email="{{ $po->alamat_email }}"
                                                data-po-alamat="{{ $po->alamat_lengkap }}"
                                                data-po-identitas="{{ $po->no_identitas }}"
                                                data-po-koordinat="{{ $po->titik_koordinat }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <button type="button" class="btn btn-danger btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                data-po-id="{{ $po->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>

                                        @elseif (Auth::user()->jabatan === 'Sales')
                                            @php
                                                $isAlreadyActive = \App\Models\Customer::where(
                                                    'pre_order_id',
                                                    $po->id,
                                                )->exists();
                                            @endphp

                                            @if ($isAlreadyActive)
                                                <div class="mt-2">
                                                    <span class="badge bg-success p-2">
                                                        <i class="bi bi-check-all"></i> Sudah Aktif
                                                    </span>
                                                </div>
                                            @else
                                                <button type="button" class="btn btn-warning btn-sm"
                                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                                    data-po-id="{{ $po->id }}"
                                                    data-po-nama="{{ $po->nama_lengkap }}"
                                                    data-po-paket="{{ $po->paket_internet }}"
                                                    data-po-wa="{{ $po->no_whatsapp }}"
                                                    data-po-email="{{ $po->alamat_email }}"
                                                    data-po-alamat="{{ $po->alamat_lengkap }}"
                                                    data-po-identitas="{{ $po->no_identitas }}"
                                                    data-po-koordinat="{{ $po->titik_koordinat }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>

                                                <button type="button" class="btn btn-danger btn-sm"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                    data-po-id="{{ $po->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </td>

                                </tr>
                            @endforeach
                        </tbody> --}}
                        <tbody>
                            @foreach ($preOrders as $po)
                                <tr>
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{ $preOrders->count() - $loop->index }}
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{ \Carbon\Carbon::parse($po->created_at)->format('d-m-Y') }}</td>
                                    <td style="text-align: center; vertical-align: middle;">{{ $po->nama_sales }}</td>
                                    <td style="vertical-align: middle">{{ $po->nama_lengkap }}</td>
                                    <td style="vertical-align: middle">{{ $po->paket_internet }}</td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#alamatModal"
                                            data-alamat-lengkap="{{ $po->alamat_lengkap }}">
                                            {{ Str::limit($po->alamat_lengkap, 15, '...') }}
                                        </a>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        @if ($po->foto_ktp)
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#viewKtpModal"
                                                data-ktp-url="{{ asset('storage/' . $po->foto_ktp) }}">
                                                <i class="bi bi-eye"></i> KTP
                                            </button>
                                        @else
                                            -
                                        @endif

                                        @if ($po->foto_rumah)
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#viewRumahModal"
                                                data-rumah-url="{{ asset('storage/' . $po->foto_rumah) }}">
                                                <i class="bi bi-eye"></i> Rumah
                                            </button>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <a href="https://wa.me/{{ $po->no_whatsapp }}" target="_blank"
                                            class="btn btn-success btn-sm">
                                            <i class="bi bi-whatsapp"></i> {{ $po->no_whatsapp }}
                                        </a>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{-- 1. Tombol Universal (Detail & Map) --}}
                                        <div class="btn-group gap-1">
                                            <button type="button" class="btn btn-secondary btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#detailModal"
                                                data-identitas="{{ $po->no_identitas }}"
                                                data-email-lengkap="{{ $po->alamat_email }}">
                                                <i class="bi bi-person-lines-fill"></i>
                                            </button>

                                            @if ($po->titik_koordinat)
                                                <button type="button" class="btn btn-primary btn-sm show-map-btn"
                                                    data-bs-toggle="modal" data-bs-target="#mapModal"
                                                    data-koordinat="{{ $po->titik_koordinat }}">
                                                    <i class="bi bi-geo-alt-fill"></i>
                                                </button>
                                            @endif
                                        </div>

                                        {{-- 2. LOGIKA STATUS BERDASARKAN AKTIVASI --}}
                                        @if ($po->is_unsubscribed)
                                            {{-- KONDISI A: PERNAH AKTIF TAPI BERHENTI (SOFT DELETED) --}}
                                            <div class="mt-2">
                                                <span class="badge bg-secondary p-2 w-100">
                                                    <i class="bi bi-person-x"></i> BERHENTI
                                                </span>
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">Pernah
                                                    Berlangganan</small>
                                            </div>
                                        @elseif ($po->is_active)
                                            {{-- KONDISI B: SEDANG AKTIF BERLANGGANAN --}}
                                            <div class="mt-2">
                                                <span class="badge bg-success p-2 w-100">
                                                    <i class="bi bi-person-check"></i> AKTIF
                                                </span>
                                            </div>
                                        @else
                                            {{-- KONDISI C: BELUM AKTIF / PROSES PENUGASAN --}}
                                            <div class="mt-2 d-flex flex-column gap-1">
                                                @if (Auth::user()->jabatan === 'Administrator')
                                                    @if ($po->id_teknisi_delegasi)
                                                        {{-- Status Penugasan Admin --}}
                                                        <button type="button"
                                                            class="btn {{ $po->is_expired ? 'btn-danger' : ($po->is_segera_expired ? 'btn-warning text-dark' : 'btn-secondary') }} btn-sm"
                                                            disabled>
                                                            <i class="bi bi-clock"></i>
                                                            {{ $po->is_expired ? 'EXPIRED' : 'DITUGASKAN' }}
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-outline-danger btn-sm reset-btn"
                                                            data-bs-toggle="modal" data-bs-target="#resetModal"
                                                            data-po-id="{{ $po->id }}"
                                                            data-po-nama="{{ $po->nama_lengkap }}">
                                                            <i class="bi bi-x-circle"></i> Reset
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                            class="btn btn-info btn-sm delegate-btn"
                                                            data-bs-toggle="modal" data-bs-target="#delegateModal"
                                                            data-po-id="{{ $po->id }}"
                                                            data-po-nama="{{ $po->nama_lengkap }}">
                                                            <i class="bi bi-person-badge"></i> Tugas
                                                        </button>
                                                    @endif
                                                @endif

                                                {{-- Tombol Edit & Hapus (Hanya muncul jika lead belum jadi customer aktif/berhenti) --}}
                                                <div class="btn-group gap-1 justify-content-center">
                                                    <button type="button" class="btn btn-warning btn-sm"
                                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                                        data-po-id="{{ $po->id }}"
                                                        data-po-nama="{{ $po->nama_lengkap }}"
                                                        data-po-paket="{{ $po->paket_internet }}"
                                                        data-po-wa="{{ $po->no_whatsapp }}"
                                                        data-po-email="{{ $po->alamat_email }}"
                                                        data-po-alamat="{{ $po->alamat_lengkap }}"
                                                        data-po-identitas="{{ $po->no_identitas }}"
                                                        data-po-koordinat="{{ $po->titik_koordinat }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                        data-po-id="{{ $po->id }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    @include('partials.modal_preorders')
    @include('partials.script_preorders')
</x-layouts.app>
