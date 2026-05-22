<!doctype html>
<html lang="id">
<x-layouts.header />

<body class="bg-slate-100 text-slate-900 antialiased font-sans">
    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="admin-layout">
        <!-- Sidebar -->
        <x-layouts.sidebar />

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Navbar -->
            <x-layouts.navbar />

            <!-- Main Content -->
            <main class="page-body" role="main">
                <div class="max-w-[1600px] mx-auto w-full">
                    {{ $slot }}
                </div>
            </main>

            <!-- Footer -->
            <x-layouts.footer />
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @yield('Scripts')
    @include('partials.location_scripts')
    
    <script>
        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const ov = document.getElementById('sidebar-overlay');
            sb.classList.toggle('open');
            ov.classList.toggle('show');
        }
    </script>
</body>
</html>
