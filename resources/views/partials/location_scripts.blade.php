{{-- Geolocation Tracking (Standard Browser HTML5) --}}
@if (auth()->check() && in_array(auth()->user()->jabatan, ['Teknisi', 'Sales']))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if ('geolocation' in navigator) {
            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

            navigator.geolocation.watchPosition(
                function(position) {
                    if (!position || !position.coords) return;
                    
                    fetch("{{ route('location.update') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        })
                    })
                    .then(res => res.json())
                    .catch(err => console.debug("Location sync failed:", err));
                },
                function(error) {
                    console.debug("Geolocation info:", error.message);
                },
                {
                    enableHighAccuracy: true,
                    maximumAge: 30000,
                    timeout: 27000
                }
            );
        }
    });
</script>
@endif
