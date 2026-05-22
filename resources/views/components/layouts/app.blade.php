<!doctype html>
<html lang="id">
<x-layouts.header />

<body>
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
            const open = sb.classList.toggle('open');
            if (open) {
                ov.classList.add('show');
            } else {
                ov.classList.remove('show');
            }
        }

        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.theme = 'light';
                icon.className = 'fa-regular fa-moon';
            } else {
                html.classList.add('dark');
                localStorage.theme = 'dark';
                icon.className = 'fa-regular fa-sun';
            }
        }

        // Apply theme on load
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            document.addEventListener('DOMContentLoaded', () => {
                const icon = document.getElementById('theme-icon');
                if (icon) icon.className = 'fa-regular fa-sun';
            });
        }
    </script>
</body>
</html>
