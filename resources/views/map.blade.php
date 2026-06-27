<x-layouts.app>
    <div style="margin-bottom: 1.5rem;">
        <h1 style="font-size:22px;font-weight:900;margin:0;">Pelacakan Akun</h1>
        <p style="font-size:13px;color: #64748b;margin:3px 0 0;">Monitoring lokasi Teknisi dan Sales secara real-time.</p>
    </div>

    <div class="table-card p-6" style="padding: 1.5rem;">
        <div id="map" style="height: 600px; width: 100%; border-radius: 8px; z-index: 1;"></div>
    </div>

    @section('Scripts')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var map = L.map('map').setView([-6.200000, 106.816666], 10); // Default location
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                var blueIcon = new L.Icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                var redIcon = new L.Icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                var locations = @json($locations);
                var markers = [];
                
                locations.forEach(function(loc) {
                    if (loc.latitude && loc.longitude) {
                        var markerIcon = loc.jabatan === 'Sales' ? redIcon : blueIcon;
                        var marker = L.marker([loc.latitude, loc.longitude], {icon: markerIcon}).addTo(map)
                            .bindPopup(`<b>${loc.nama_lengkap}</b><br>${loc.jabatan}<br>Update: ${new Date(loc.updated_at).toLocaleString()}`);
                        markers.push(marker);
                    }
                });

                if (markers.length > 0) {
                    var group = new L.featureGroup(markers);
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            });
        </script>
    @endsection
</x-layouts.app>
