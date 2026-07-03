<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo" style="display: flex; justify-content: center; align-items: center; padding: 0.5rem 0rem;">
        <img src="{{ asset('dist/assets/img/logo.png') }}" alt="Erlangga Net Logo"
            style="height: 65px; width: auto; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
    </div>

    <!-- Navigation -->
    <nav style="padding-top:.5rem;">
        <div class="sidebar-section-label">Dashboard</div>
        <a class="sidebar-link {{ Request::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboards.index') }}">
            <i class="fa-solid fa-gauge"></i>
            <span>Overview</span>
        </a>

        <div class="sidebar-section-label" style="margin-top:.5rem;">Infrastruktur</div>
        @if (auth()->user()->jabatan !== 'Sales')
            <a class="sidebar-link {{ Request::is('olt*') ? 'active' : '' }}" href="{{ route('olt.index') }}">
                <i class="fa-solid fa-server"></i>
                <span>Manajemen OLT</span>
            </a>
        @endif
        <a class="sidebar-link {{ Request::is('odp*') ? 'active' : '' }}" href="{{ route('odp.index') }}">
            <i class="fa-solid fa-tower-broadcast"></i>
            <span>Manajemen ODP</span>
        </a>
        @if (auth()->user()->isAdmin() || auth()->user()->jabatan === 'Teknisi')
            <a class="sidebar-link {{ Request::is('pppoe*') ? 'active' : '' }}" href="{{ route('pppoe.index') }}">
                <i class="fa-solid fa-network-wired"></i>
                <span>PPPoE Aktif</span>
            </a>
        @endif
        @if (auth()->user()->isAdmin())
            <a class="sidebar-link {{ Request::is('monitoring') ? 'active' : '' }}"
                href="{{ route('monitoring.index') }}">
                <i class="fa-solid fa-chart-line"></i>
                <span>Monitoring</span>
            </a>
        @endif

        <div class="sidebar-section-label" style="margin-top:.5rem;">Pelanggan</div>
        @if (auth()->check() && in_array(auth()->user()->jabatan, ['Administrator', 'Sales']))
            <a class="sidebar-link {{ Request::is('pre-orders') ? 'active' : '' }}"
                href="{{ route('pre_orders.index') }}">
                <i class="fa-solid fa-user-plus"></i>
                <span>Pre-Order</span>
            </a>
        @endif

        @if (auth()->check() &&
                (auth()->user()->isAdmin() || auth()->user()->jabatan === 'Teknisi' || auth()->user()->jabatan === 'Sales'))
            <a class="sidebar-link {{ Request::is('customers') || Request::is('customers/active-list') ? 'active' : '' }}"
                href="{{ auth()->user()->jabatan === 'Teknisi' ? route('customers.index') : route('customers.active_list') }}">
                <i class="fa-solid fa-users"></i>
                <span>Pelanggan</span>
            </a>
            @if(auth()->user()->isAdmin() || auth()->user()->jabatan === 'Teknisi')
            <a class="sidebar-link {{ Request::is('maintenances') ? 'active' : '' }}"
                href="{{ route('maintenances.index') }}">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <span>Pemeliharaan</span>
            </a>
            @endif
            <a class="sidebar-link {{ Request::is('dismantles') ? 'active' : '' }}"
                href="{{ route('dismantles.index') }}">
                <i class="fa-solid fa-user-xmark"></i>
                <span>Pencabutan</span>
            </a>
            @if (auth()->user()->jabatan !== 'Sales')
                <a class="sidebar-link {{ Request::is('supporting*') ? 'active' : '' }}"
                    href="{{ route('supporting.index') }}">
                    <i class="fa-solid fa-handshake-angle"></i>
                    <span>Perbantuan</span>
                </a>
            @endif
        @endif

        @if (auth()->check() && auth()->user()->isAdmin())
            <div class="sidebar-section-label" style="margin-top:.5rem;">Gudang</div>
            <a class="sidebar-link {{ Request::is('assets*') ? 'active' : '' }}" href="{{ route('assets.index') }}">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>Pengelolaan Aset</span>
            </a>

            <div class="sidebar-section-label" style="margin-top:.5rem;">Keuangan</div>
            <a class="sidebar-link {{ Request::is('keuangan/teknisi*') ? 'active' : '' }}"
                href="{{ route('finance.index') }}">
                <i class="fa-solid fa-wallet"></i>
                <span>Pendapatan Teknisi</span>
            </a>
            <a class="sidebar-link {{ Request::is('keuangan/kasbon*') ? 'active' : '' }}"
                href="{{ route('kasbons.index') }}">
                <i class="fa-solid fa-hand-holding-dollar"></i>
                <span>Kasbon Teknisi</span>
            </a>

            <div class="sidebar-section-label" style="margin-top:.5rem;">Laporan</div>
            <a class="sidebar-link {{ Request::is('laporan/aktivasi') ? 'active' : '' }}"
                href="{{ route('activitylog.aktivasi') }}">
                <i class="fa-solid fa-file-contract"></i>
                <span>Lap. Aktivasi</span>
            </a>
            <a class="sidebar-link {{ Request::is('laporan/aktivasi-sales') ? 'active' : '' }}"
                href="{{ route('activitylog.aktivasi_sales') }}">
                <i class="fa-solid fa-file-invoice"></i>
                <span>Lap. Aktivasi Sales</span>
            </a>
            <a class="sidebar-link {{ Request::is('laporan/maintenance') ? 'active' : '' }}"
                href="{{ route('activitylog.maintenance') }}">
                <i class="fa-solid fa-file-medical"></i>
                <span>Lap. Pemeliharaan</span>
            </a>
            <a class="sidebar-link {{ Request::is('laporan/dismantle') ? 'active' : '' }}"
                href="{{ route('activitylog.dismantle') }}">
                <i class="fa-solid fa-file-excel"></i>
                <span>Lap. Pencabutan</span>
            </a>

            <div class="sidebar-section-label" style="margin-top:.5rem;">Manajemen</div>
            <a class="sidebar-link {{ Request::is('accounts') ? 'active' : '' }}"
                href="{{ route('accounts.index') }}">
                <i class="fa-solid fa-users"></i>
                <span>Akun</span>
            </a>
            <a class="sidebar-link {{ Request::is('tracking') ? 'active' : '' }}"
                href="{{ route('accounts.tracking') }}">
                <i class="fa-solid fa-map-location-dot"></i>
                <span>Pelacakan Akun</span>
            </a>
            <a class="sidebar-link {{ Request::is('activity-log') ? 'active' : '' }}"
                href="{{ route('activitylog.index') }}">
                <i class="fa-solid fa-clipboard-list"></i>
                <span>Aktivitas Log</span>
            </a>
        @endif

        <div class="sidebar-section-label" style="margin-top:.5rem;">Pengaturan</div>
        <form action="{{ route('logout') }}" method="POST" id="logout-form" class="hidden">@csrf</form>
        <a class="sidebar-link" href="#" style="color:#f87171;"
            onclick="localStorage.removeItem('app_token'); document.getElementById('logout-form').submit(); return false;">
            <i class="fa-solid fa-arrow-right-from-bracket" style="color:#f87171;"></i>
            <span>Keluar</span>
        </a>
    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                {{ strtoupper(substr(auth()->user()->nama_lengkap ?? 'U', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <div class="sidebar-user-name truncate">{{ auth()->user()->nama_lengkap ?? 'Pengguna' }}</div>
                <div class="sidebar-user-role truncate">{{ auth()->user()->jabatan ?? 'Jabatan' }}</div>
            </div>
        </div>
    </div>
</aside>
