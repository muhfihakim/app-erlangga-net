{{-- Background Geolocation Tracking for Capacitor WebView --}}
@if (auth()->check() && in_array(auth()->user()->jabatan, ['Teknisi', 'Sales']))
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
                        backgroundMessage: "Aplikasi berjalan di latar belakang.",
                        backgroundTitle: "Erlangga Net",
                        requestPermissions: true,
                        stale: false,
                        distanceFilter: 5 // Update every 5 meters
                    },
                    function callback(location, error) {
                        if (error) {
                            console.error("BG Geo Error:", error);
                            // Detect if GPS is disabled or permission denied
                            let errorMsg = "Gagal melacak lokasi. Mohon periksa pengaturan GPS Anda.";
                            if (error.code === "NOT_AUTHORIZED") {
                                errorMsg = "Izin lokasi tidak diberikan. Mohon izinkan akses lokasi (Selalu/Allow all the time).";
                            } else if (error.code === "LOCATION_SERVICES_DISABLED") {
                                errorMsg = "GPS HP Anda sedang dimatikan. Mohon nyalakan GPS untuk menggunakan aplikasi.";
                            }
                            
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Perhatian!',
                                    text: errorMsg,
                                    confirmButtonText: 'Mengerti'
                                });
                            } else {
                                alert(errorMsg);
                            }
                            return;
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
                );
                console.log("Background Geolocation watcher request sent.");
            } else {
                console.warn("BackgroundGeolocation plugin is not injected.");
            }
        }
    });
</script>
@endif
