<x-layouts.app>
    @section('Css')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            /* Map Styling */
            #odp-map {
                height: 500px;
                width: 100%;
                border-radius: 16px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                z-index: 1;
            }

            .map-container {
                position: relative;
                margin-bottom: 2rem;
            }

            /* Legend Styling */
            .map-legend {
                position: absolute;
                bottom: 20px;
                left: 20px;
                background: white;
                padding: 12px 16px;
                border-radius: 12px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                z-index: 1000;
            }

            .legend-item {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 11px;
                font-weight: 700;
                color: #475569;
                margin-bottom: 4px;
            }

            .legend-dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
            }

            /* Distance Tool Styling */
            .distance-info {
                position: absolute;
                top: 20px;
                right: 20px;
                background: white;
                padding: 10px 14px;
                border-radius: 10px;
                border: 2px solid #0ea5e9;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                z-index: 1000;
                display: none;
            }

            /* Toolbar Styles */
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
            }

            .search-input {
                width: 100%;
                padding: 9px 12px 9px 36px;
                border: 2px solid #e2e8f0;
                border-radius: 10px;
                font-size: 13px;
                outline: none;
                transition: all .25s;
                background: #f8fafc;
            }

            .search-input:focus {
                border-color: #0ea5e9;
                background: #fff;
            }

            .badge-gpon { background: #e0f2fe; color: #0369a1; }
            .badge-epon { background: #fef3c7; color: #92400e; }

            /* Full Capitalization for Table Only */
            #odp-table thead th, 
            #odp-table tbody td {
                text-transform: uppercase !important;
                font-weight: 600;
            }

            #odp-table tbody td { font-size: 12px; }
            </style>

    @endsection

    @php
        $canCrud = auth()->user()->jabatan === 'Administrator' || auth()->user()->jabatan === 'Teknisi';
    @endphp

    <!-- Page Header -->
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Manajemen ODP (Optical Distribution Point)</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Visualisasi dan pengelolaan titik distribusi
                infrastruktur FTTH.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="button" id="measure-btn"
                class="btn-primary-admin !bg-white !text-slate-700 !border-slate-200 !shadow-none hover:!bg-slate-50 !flex !items-center !justify-center gap-2">
                <i class="fa-solid fa-ruler-combined text-primary"></i> Ukur Jarak
            </button>
            @if ($canCrud)
                <button type="button" onclick="openCustomModal('addOdpModal')"
                    class="btn-primary-admin !flex !items-center !justify-center gap-2">
                    <i class="fa-solid fa-plus"></i> Tambah ODP
                </button>
            @endif
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                <i class="fa-solid fa-tower-broadcast"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['total'] }}</div>
                <div class="stat-lbl">Total ODP</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0f9ff;color:#0ea5e9;">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['gpon'] }}</div>
                <div class="stat-lbl">GPON Mode</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="fa-solid fa-microchip"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['epon'] }}</div>
                <div class="stat-lbl">EPON Mode</div>
            </div>
        </div>
    </div>

    <!-- Map Section -->
    <div class="map-container">
        <div id="odp-map"></div>

        <div class="map-legend">
            <div class="legend-item">
                <div class="legend-dot" style="background: #0ea5e9; border: 2px solid #fff;"></div>
                ODP GPON
            </div>
            <div class="legend-item">
                <div class="legend-dot" style="background: #f59e0b; border: 2px solid #fff;"></div>
                ODP EPON
            </div>
        </div>

        <div id="dist-info" class="distance-info">
            <div class="flex flex-col">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Estimasi Jarak</span>
                <span id="total-dist" class="text-lg font-bold text-primary">0 m</span>
                <div class="flex gap-2 mt-2">
                    <button id="reset-measure" class="text-[10px] font-bold text-rose-500 underline">Reset</button>
                    <button id="close-measure" class="text-[10px] font-bold text-slate-500 underline">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input custom-search" placeholder="Cari lokasi atau jenis ODP..."
                autocomplete="off" />
        </div>
        <div style="display:flex;gap:8px;">
            <div
                style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                Data FTTH Infrastructure
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="odp-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="width: 120px; text-align: center;">Jenis</th>
                        <th>Lokasi ODP</th>
                        <th style="text-align: center; width: 200px;">Koordinat</th>
                        <th>Keterangan</th>
                        <th style="text-align: center; width: 120px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($odps as $odp)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}
                            </td>
                            <td style="text-align: center;">
                                <span
                                    class="badge {{ $odp->jenis_odp === 'GPON' ? 'badge-gpon' : 'badge-epon' }} !text-[10px] !font-bold">
                                    {{ $odp->jenis_odp }}
                                </span>
                            </td>
                            <td style="font-weight: 700; color: #0f172a;">{{ $odp->lokasi_odp }}</td>
                            <td style="text-align: center; font-family: monospace; font-size: 11px; color: #64748b;">
                                {{ $odp->titik_koordinat }}
                            </td>
                            <td style="font-size: 12px; color: #64748b;">{{ $odp->keterangan ?: '-' }}</td>
                            <td style="text-align: center;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                    <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                        title="Lihat di Peta"
                                        onclick="focusOnOdp('{{ $odp->titik_koordinat }}', '{{ $odp->id }}')">
                                        <i class="fa-solid fa-location-dot text-emerald-500"></i>
                                    </button>
                                    @if ($canCrud)
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            title="Edit" onclick="openEditOdpModal(this)"
                                            data-id="{{ $odp->id }}" data-jenis="{{ $odp->jenis_odp }}"
                                            data-lokasi="{{ $odp->lokasi_odp }}"
                                            data-tikor="{{ $odp->titik_koordinat }}"
                                            data-keterangan="{{ $odp->keterangan }}"
                                            data-foto="{{ $odp->foto_odp ? asset('storage/' . $odp->foto_odp) : '' }}">
                                            <i class="fa-solid fa-pen-to-square text-primary"></i>
                                        </button>
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            title="Hapus"
                                            onclick="confirmGlobalDelete('{{ route('odp.destroy', $odp->id) }}', 'ODP {{ $odp->lokasi_odp }}')">
                                            <i class="fa-solid fa-trash text-rose-600"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($canCrud)
        {{-- Modal Tambah ODP --}}
        <div class="modal-overlay" id="addOdpModal">
            <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
                <form action="{{ route('odp.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                        <div class="modal-title">
                            <i class="fa-solid fa-plus-circle text-primary"></i>
                            <span class="text-slate-900 font-bold">Tambah Data ODP</span>
                        </div>
                        <button type="button" class="modal-close" onclick="closeCustomModal('addOdpModal')">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body p-8 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Jenis ODP
                                    <span class="text-rose-500">*</span></label>
                                <select name="jenis_odp" class="form-select" required>
                                    <option value="EPON">EPON (Amber)</option>
                                    <option value="GPON">GPON (Blue)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Titik
                                    Koordinat <span class="text-rose-500">*</span></label>
                                <div class="flex gap-2">
                                    <input type="text" name="titik_koordinat" id="add_tikor" class="form-input"
                                        placeholder="-7.xxx, 110.xxx" required>
                                    <button type="button" class="btn-primary-admin !shadow-none !px-4"
                                        onclick="getAddLocation()">
                                        <i class="fa-solid fa-location-crosshairs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Lokasi / Nama ODP
                                <span class="text-rose-500">*</span></label>
                            <input type="text" name="lokasi_odp" class="form-input"
                                placeholder="Contoh: ODP-KLT-01" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Foto ODP</label>
                            <input type="file" name="foto_odp" class="form-input" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Keterangan</label>
                            <textarea name="keterangan" class="form-textarea" rows="3" placeholder="Informasi tambahan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                        <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                            onclick="closeCustomModal('addOdpModal')">
                            <i class="fa-solid fa-xmark"></i> Batal
                        </button>
                        <button type="submit" class="btn-save !flex !items-center !justify-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i> Simpan Data ODP
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Edit ODP --}}
        <div class="modal-overlay" id="editOdpModal">
            <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
                <form id="editOdpForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                        <div class="modal-title">
                            <i class="fa-solid fa-pen-to-square text-primary"></i>
                            <span class="text-slate-900 font-bold">Edit Data ODP</span>
                        </div>
                        <button type="button" class="modal-close" onclick="closeCustomModal('editOdpModal')">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body p-8 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Jenis ODP
                                    <span class="text-rose-500">*</span></label>
                                <select name="jenis_odp" id="edit_jenis" class="form-select" required>
                                    <option value="EPON">EPON (Amber)</option>
                                    <option value="GPON">GPON (Blue)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Titik
                                    Koordinat <span class="text-rose-500">*</span></label>
                                <div class="flex gap-2">
                                    <input type="text" name="titik_koordinat" id="edit_tikor_input"
                                        class="form-input" required>
                                    <button type="button" class="btn-primary-admin !shadow-none !px-4"
                                        onclick="getEditLocation()">
                                        <i class="fa-solid fa-location-crosshairs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Lokasi / Nama ODP
                                <span class="text-rose-500">*</span></label>
                            <input type="text" name="lokasi_odp" id="edit_lokasi" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Foto ODP
                                (Kosongkan jika tidak diubah)</label>
                            <input type="file" name="foto_odp" class="form-input" accept="image/*">
                            <div id="current_foto_wrap" class="mt-2 hidden">
                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Foto Saat Ini:</p>
                                <img id="current_foto" src=""
                                    class="w-24 h-24 object-cover rounded-lg border border-slate-200">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold uppercase text-[11px]">Keterangan</label>
                            <textarea name="keterangan" id="edit_keterangan" class="form-textarea" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                        <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                            onclick="closeCustomModal('editOdpModal')">
                            <i class="fa-solid fa-xmark"></i> Batal
                        </button>
                        <button type="submit" class="btn-save !flex !items-center !justify-center gap-2">
                            <i class="fa-solid fa-check"></i> Perbarui Data ODP
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @section('Scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                // Initialize DataTable
                const table = $('#odp-table').DataTable({
                    "pageLength": 10,
                    "lengthChange": false,
                    "ordering": false,
                    "info": false,
                    "responsive": true,
                    "dom": 'tpir',
                    "language": {
                        "zeroRecords": "Data ODP tidak ditemukan",
                        "paginate": {
                            "previous": '<i class="fa-solid fa-chevron-left"></i>',
                            "next": '<i class="fa-solid fa-chevron-right"></i>'
                        }
                    }
                });

                $('.custom-search').on('keyup', function() {
                    table.search(this.value).draw();
                });

                // --- MAP LOGIC ---
                // Base Layers
                const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                });

                const gmapsSatellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: '© Google Maps'
                });

                const map = L.map('odp-map', {
                    center: [-7.728, 110.602], // Klaten default
                    zoom: 13,
                    layers: [osm]
                });

                window.odpMap = map; // Expose for focusOnOdp

                const baseMaps = {
                    "Standard (OSM)": osm,
                    "Realistic (Google Satellite)": gmapsSatellite
                };

                L.control.layers(baseMaps).addTo(map);

                // Markers
                const odps = {!! json_encode($odps) !!};
                const markers = L.layerGroup().addTo(map);
                window.odpMarkers = markers; // Expose for focusOnOdp

                odps.forEach(odp => {
                    const coords = odp.titik_koordinat.split(',').map(c => parseFloat(c.trim()));
                    if (coords.length === 2) {
                        const color = odp.jenis_odp === 'GPON' ? '#0ea5e9' : '#f59e0b';
                        const marker = L.circleMarker(coords, {
                            radius: 8,
                            fillColor: color,
                            color: "#fff",
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.9
                        }).addTo(markers);

                        const popupContent = `
                            <div style="min-width: 150px; font-family: 'Inter', sans-serif;">
                                <div style="font-weight: 800; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 6px;">
                                    ${odp.lokasi_odp}
                                </div>
                                <div style="font-size: 11px; margin-bottom: 4px;">
                                    <span style="color: #94a3b8; font-weight: 700;">JENIS:</span>
                                    <span style="font-weight: 800; color: ${color};">${odp.jenis_odp}</span>
                                </div>
                                <div style="font-size: 11px; color: #475569;">
                                    ${odp.keterangan || 'Tanpa keterangan'}
                                </div>
                                ${odp.foto_odp ? `<img src="/storage/${odp.foto_odp}" style="width: 100%; border-radius: 8px; margin-top: 8px;">` : ''}
                                <button onclick="startMeasureFromMarker('${odp.titik_koordinat}')" style="width:100%; margin-top:8px; background:#0ea5e9; color:white; border:none; padding:6px; border-radius:6px; font-size:10px; font-weight:700; cursor:pointer;">
                                    <i class="fa-solid fa-ruler"></i> Ukur dari sini
                                </button>
                            </div>
                        `;
                        marker.bindPopup(popupContent);
                    }
                });

                // Fit map to markers
                if (odps.length > 0) {
                    const bounds = L.latLngBounds(odps.map(o => o.titik_koordinat.split(',').map(c => parseFloat(c
                        .trim()))));
                    map.fitBounds(bounds, {
                        padding: [50, 50]
                    });
                }

                // --- DISTANCE MEASURING TOOL ---
                let isMeasuring = false;
                let measurePoints = [];
                let measureLine = L.polyline([], {
                    color: '#0ea5e9',
                    weight: 4,
                    dashArray: '5, 10'
                }).addTo(map);
                let measureMarkers = L.layerGroup().addTo(map);

                window.startMeasureFromMarker = function(tikor) {
                    resetMeasure();
                    isMeasuring = true;
                    $('#dist-info').fadeIn();
                    const latlng = L.latLng(tikor.split(',').map(c => parseFloat(c.trim())));
                    addMeasurePoint(latlng);
                };

                $('#measure-btn').on('click', function() {
                    if (isMeasuring) {
                        stopMeasure();
                    } else {
                        isMeasuring = true;
                        $(this).addClass('!bg-primary !text-white');
                        $('#dist-info').fadeIn();
                        showToast('Klik pada ODP atau Peta untuk mulai mengukur.', 'info');
                    }
                });

                map.on('click', function(e) {
                    if (isMeasuring) {
                        addMeasurePoint(e.latlng);
                    }
                });

                function addMeasurePoint(latlng) {
                    measurePoints.push(latlng);
                    measureLine.setLatLngs(measurePoints);

                    L.circleMarker(latlng, {
                        radius: 5,
                        fillColor: '#fff',
                        color: '#0ea5e9',
                        weight: 2,
                        fillOpacity: 1
                    }).addTo(measureMarkers);

                    calculateTotalDistance();
                }

                function calculateTotalDistance() {
                    let total = 0;
                    for (let i = 0; i < measurePoints.length - 1; i++) {
                        total += measurePoints[i].distanceTo(measurePoints[i + 1]);
                    }
                    $('#total-dist').text(total.toFixed(1) + ' m');
                }

                function resetMeasure() {
                    measurePoints = [];
                    measureLine.setLatLngs([]);
                    measureMarkers.clearLayers();
                    $('#total-dist').text('0 m');
                }

                function stopMeasure() {
                    isMeasuring = false;
                    $('#measure-btn').removeClass('!bg-primary !text-white');
                    $('#dist-info').fadeOut();
                    resetMeasure();
                }

                $('#reset-measure').on('click', resetMeasure);
                $('#close-measure').on('click', stopMeasure);
            });

            // Focus on ODP from table
            function focusOnOdp(tikor, odpId) {
                const coords = tikor.split(',').map(c => parseFloat(c.trim()));
                if (coords.length === 2) {
                    // Smooth scroll to map
                    document.getElementById('odp-map').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    if (window.odpMap) {
                        // Center map and zoom
                        window.odpMap.setView(coords, 18);

                        // Find and open popup
                        if (window.odpMarkers) {
                            window.odpMarkers.eachLayer(function(marker) {
                                const mCoords = marker.getLatLng();
                                // Check for near equality to avoid float precision issues
                                if (Math.abs(mCoords.lat - coords[0]) < 0.0001 && Math.abs(mCoords.lng - coords[1]) <
                                    0.0001) {
                                    marker.openPopup();
                                }
                            });
                        }
                    }
                }
            }

            // Modal Logic
            function openEditOdpModal(btn) {
                const $btn = $(btn);
                $('#editOdpForm').attr('action', `/odp/${$btn.data('id')}`);
                $('#edit_jenis').val($btn.data('jenis'));
                $('#edit_lokasi').val($btn.data('lokasi'));
                $('#edit_tikor_input').val($btn.data('tikor'));
                $('#edit_keterangan').val($btn.data('keterangan'));

                if ($btn.data('foto')) {
                    $('#current_foto').attr('src', $btn.data('foto'));
                    $('#current_foto_wrap').removeClass('hidden');
                } else {
                    $('#current_foto_wrap').addClass('hidden');
                }

                openCustomModal('editOdpModal');
            }

            // Location Helpers
            function getAddLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        $('#add_tikor').val(`${pos.coords.latitude}, ${pos.coords.longitude}`);
                        showToast('Lokasi berhasil diambil!', 'success');
                    }, err => showToast('Gagal mengambil lokasi.', 'error'));
                }
            }

            function getEditLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        $('#edit_tikor_input').val(`${pos.coords.latitude}, ${pos.coords.longitude}`);
                        showToast('Lokasi berhasil diambil!', 'success');
                    }, err => showToast('Gagal mengambil lokasi.', 'error'));
                }
            }
        </script>
    @endsection
</x-layouts.app>
