{{-- Background Geolocation Tracking for Capacitor WebView --}}
@if (auth()->check())
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Detect if running inside Capacitor APK
        if (window.Capacitor && window.Capacitor.isNativePlatform()) {
            // Check if BackgroundGeolocation plugin is available
            const BackgroundGeolocation = window.Capacitor.Plugins.BackgroundGeolocation;
            
            if (BackgroundGeolocation) {
                console.log("Background Geolocation plugin found. Starting watcher...");

                BackgroundGeolocation.addWatcher(
                    {
                        backgroundMessage: "Erlangga Net melacak lokasi Anda di latar belakang.",
                        backgroundTitle: "Pelacakan Aktif",
                        requestPermissions: true,
                        stale: false,
                        distanceFilter: 10 // Update every 10 meters
                    },
                    function callback(location, error) {
                        if (error) {
                            if (error.code === "NOT_AUTHORIZED") {
                                console.warn("Location permission not granted.");
                            }
                            return console.error("BG Geo Error:", error);
                        }

                        // Send location to Laravel backend
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        fetch("{{ route('location.update') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                lat: location.latitude,
                                lng: location.longitude
                            })
                        }).then(res => res.json())
                          .then(data => console.log("Location synced:", data))
                          .catch(err => console.error("Location sync failed:", err));
                    }
                ).then(function(watcher_id) {
                    console.log("Background Geolocation active. Watcher ID:", watcher_id);
                }).catch(function(err) {
                    console.error("Failed to start Background Geolocation:", err);
                });
            } else {
                console.warn("BackgroundGeolocation plugin is not injected.");
            }
        }
    });
</script>
@endif
