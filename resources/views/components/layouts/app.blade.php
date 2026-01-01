<!doctype html>
<html lang="id">
<x-layouts.header />

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
        <x-layouts.navbar />
        <x-layouts.sidebar />
        {{ $slot }}
        <x-layouts.footer />
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
        integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ=" crossorigin="anonymous"></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="{{ asset('dist/js/adminlte.js') }}"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
        const Default = {
            scrollbarTheme: 'os-theme-light',
            scrollbarAutoHide: 'leave',
            scrollbarClickScroll: true,
        };
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script>
    <!--end::OverlayScrollbars Configure-->
    <!-- OPTIONAL SCRIPTS -->
    @yield('Scripts')
    @if (auth()->check())
        <style>
            /* Overlay blur untuk blokir halaman */
            #location-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                backdrop-filter: blur(6px);
                background: rgba(0, 0, 0, 0.5);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                text-align: center;
                font-family: Arial, sans-serif;
                flex-direction: column;
                padding: 20px;
            }

            #location-overlay h2 {
                font-size: 1.8rem;
                margin-bottom: 10px;
            }

            #location-overlay p {
                font-size: 1.1rem;
                margin-bottom: 15px;
                max-width: 400px;
            }

            #retry-btn {
                background-color: #28a745;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                font-size: 1rem;
                cursor: pointer;
                transition: background-color 0.2s ease;
            }

            #retry-btn:hover {
                background-color: #218838;
            }
        </style>

        <div id="location-overlay" style="display:none;">
            <h2>Akses Lokasi Diperlukan</h2>
            <p>Silakan aktifkan GPS dan izinkan situs ini mengakses lokasi Anda agar dapat melanjutkan.</p>
            <button id="retry-btn">Coba Lagi</button>
        </div>

        {{-- <script>
            let locationAllowed = false;

            // Fungsi utama untuk meminta lokasi
            function requestLocation() {
                if (!navigator.geolocation) {
                    showOverlay("Browser Anda tidak mendukung pelacakan lokasi.");
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    position => {
                        // ✅ Lokasi berhasil diperoleh
                        locationAllowed = true;
                        hideOverlay();

                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;

                        // Kirim data lokasi ke server
                        fetch('{{ route('location.update') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    latitude,
                                    longitude
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('✅ Lokasi berhasil dikirim:', data.message);
                            })
                            .catch(error => {
                                console.error('❌ Gagal mengirim lokasi:', error);
                            });
                    },
                    error => {
                        if (error.code === error.PERMISSION_DENIED) {
                            // 🚫 Jika ditolak, tampilkan overlay
                            showOverlay("Anda wajib mengizinkan akses lokasi untuk menggunakan sistem ini.");
                            locationAllowed = false;
                        } else {
                            console.error('Gagal mendapatkan lokasi:', error.message);
                        }
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000
                    }
                );
            }

            // Tampilkan overlay blur
            function showOverlay(message) {
                const overlay = document.getElementById('location-overlay');
                overlay.style.display = 'flex';
                overlay.querySelector('p').innerHTML = message;
                document.body.style.overflow = 'hidden'; // Nonaktifkan scroll
            }

            // Sembunyikan overlay jika lokasi diizinkan
            function hideOverlay() {
                const overlay = document.getElementById('location-overlay');
                overlay.style.display = 'none';
                document.body.style.overflow = 'auto';
            }

            // Tombol “Coba Lagi”
            document.getElementById('retry-btn').addEventListener('click', () => {
                requestLocation();
            });

            // Jalankan fungsi pertama kali
            requestLocation();

            // 🔁 Periksa terus setiap 10 detik jika belum diizinkan
            setInterval(() => {
                if (!locationAllowed) {
                    showOverlay("Anda belum mengizinkan lokasi. Aktifkan GPS lalu klik 'Coba Lagi'.");
                } else {
                    requestLocation(); // kirim lokasi rutin
                }
            }, 10000);
        </script> --}}
    @endif

</body>
<!--end::Body-->

</html>
