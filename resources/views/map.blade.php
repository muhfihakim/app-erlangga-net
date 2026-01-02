<x-layouts.app>
    {{-- Hapus duplikasi CSS Leaflet. --}}
    @section('Css')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            /* Fix Select2 width in Bootstrap forms */
            .select2-container--default .select2-selection--single {
                height: 38px;
                padding-top: 5px;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px;
            }
        </style>
    @endsection
    <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
            <!--begin::Container-->
            <div class="container-fluid">
                <!--begin::Row-->
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">Manajemen</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Lacak Akun</li>
                        </ol>
                    </div>
                </div>
                <!--end::Row-->
            </div>
            <!--end::Container-->
        </div>
        <div class="app-content">
            <div class="card card-primary shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Lacak Akun</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('dashboards.index') }}" class="btn btn-secondary mb-3 btn-sm">← Kembali</a>
                    <div class="mb-3">
                        <label for="user_selector" class="form-label fw-semibold">
                            Pilih Pengguna
                        </label>
                        {{-- ID 'user_selector' tetap sama --}}
                        <select id="user_selector" class="form-select">
                            <option value="">Semua Pengguna</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->nama_lengkap }} — {{ $user->jabatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="border rounded" id="map" style="height: 500px;"></div>
                </div>
            </div>
        </div>
    </main>
    @section('Scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="{{ asset('dist/js/jquery.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                // 1. INISIALISASI SELECT2
                $('#user_selector').select2({
                    placeholder: "Pilih atau cari pengguna...",
                    allowClear: true // Opsi untuk menghapus pilihan
                });

                // Mulai inisialisasi Leaflet setelah Select2
                const map = L.map('map').setView([-6.2088, 106.8456], 12);

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);

                let markers = [];

                // ... (Definisi icons tetap sama) ...

                const icons = {
                    'Sales': L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    }),
                    'Teknisi': L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
                        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    }),
                    'default': L.icon({
                        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                };

                // Hapus semua marker
                function clearMarkers() {
                    markers.forEach(marker => map.removeLayer(marker));
                    markers = [];
                }

                // Fungsi untuk memformat tanggal
                function formatTimestamp(timestamp) {
                    if (!timestamp) return 'Tidak diketahui';
                    // Asumsi timestamp adalah string ISO (contoh: '2025-11-16T07:45:00.000000Z')
                    const date = new Date(timestamp);
                    // Sesuaikan opsi format tanggal sesuai preferensi Anda
                    const options = {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    };
                    return date.toLocaleDateString('id-ID', options);
                }

                // MODIFIKASI FUNGSI addMarkers UNTUK POPUP dan TANPA openPopup()
                function addMarkers(locations) {
                    clearMarkers();

                    if (!Array.isArray(locations) || locations.length === 0) {
                        console.warn("Tidak ada data lokasi yang ditemukan.");
                        map.setView([-6.2088, 106.8456], 12);
                        return;
                    }

                    locations.forEach(location => {
                        const icon = icons[location.jabatan] || icons.default;
                        const coords = `${location.latitude}, ${location.longitude}`;
                        const formattedTime = formatTimestamp(location
                            .updated_at); // Asumsi property: last_update

                        // Membuat konten HTML untuk Popup
                        const popupContent = `
                            <div class="d-flex flex-column" style="min-width: 100px;">
                                <p class="fw-bold mb-1">${location.nama_lengkap}</p>
                                <p class="mb-2">${location.jabatan}</p>
                                <hr class="my-1">
                                <small class="mb-1">
                                    <strong>Terakhir Diperbarui:</strong> <br>${formattedTime}
                                </small>
                                <small class="mb-2">
                                    <strong>Tikor:</strong> <br>${coords}
                                </small>
                                <a href="https://www.google.com/maps/search/?api=1&query=${coords}"
                                   target="_blank"
                                   class="btn btn-sm btn-primary mt-2 text-white">
                                    Buka di Google Maps
                                </a>
                            </div>
                        `;

                        const marker = L.marker([location.latitude, location.longitude], {
                                icon
                            })
                            .addTo(map)
                            .bindPopup(popupContent);
                        // Baris .openPopup() DIHAPUS agar marker tidak langsung terbuka saat dimuat.

                        markers.push(marker);
                    });

                    // Fokus ke area marker
                    const group = new L.featureGroup(markers);
                    map.fitBounds(group.getBounds(), {
                        padding: [30, 30]
                    });
                }

                // Data awal
                const initialLocations = @json($locations);
                addMarkers(initialLocations);

                // Event: pilih pengguna (Menggunakan JQuery untuk Select2)
                $('#user_selector').on('change', function() {
                    const userId = $(this).val();
                    let url = '/api/locations/all';

                    if (userId) {
                        url = '/api/locations/' + userId;
                    }

                    fetch(url)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            addMarkers(data);
                        })
                        .catch(error => {
                            console.error('Error fetching locations:', error);
                        });
                });
            });
        </script>
    @endsection
</x-layouts.app>
