@php
    $user = auth()->user();
    $isAdmin = $user && $user->isAdmin();
    $isTeknisi = $user && $user->jabatan === 'Teknisi';
    $isSales = $user && $user->jabatan === 'Sales';
    $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

    $isNetActive = Request::is('pppoe*') || Request::is('olt*') || Request::is('odp*') || Request::is('genieacs*') || Request::is('monitoring*');
    $isCustActive = Request::is('pre-orders*') || Request::is('customers*') || Request::is('maintenances*');
    $isFinActive = Request::is('invoices*') || Request::is('transactions*') || Request::is('keuangan*') || Request::is('kasbons*') || Request::is('withdrawals*');
    $isReportActive = Request::is('laporan*');
    $isSysActive = Request::is('pakets*') || Request::is('nas*') || Request::is('accounts*') || Request::is('tracking*') || Request::is('activity-log*') || Request::is('migration*') || Request::is('settings*');
@endphp

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo" style="display: flex; justify-content: center; align-items: center; padding: 1rem 0rem;">
        <img src="{{ asset('dist/assets/img/logo.png') }}" alt="Erlangga Net Logo"
            style="height: 58px; width: auto; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));">
    </div>

    <!-- Navigation -->
    <nav style="padding-top: 0.85rem; padding-bottom: 1.5rem; flex: 1;">
        <!-- 1. DASHBOARD (Overview) -->
        <a class="sidebar-link {{ Request::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboards.index') }}">
            <i class="fa-solid fa-gauge"></i>
            <span>Overview</span>
        </a>

        <!-- 2. JARINGAN & ROUTER (Accordion) -->
        @if ($isAdmin || $isTeknisi)
            <div class="sidebar-group">
                <button type="button" class="sidebar-group-btn {{ $isNetActive ? 'active open' : '' }}"
                    data-group-target="group-jaringan" onclick="toggleSidebarGroup('group-jaringan')">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-network-wired" style="width: 18px; text-align: center;"></i>
                        <span>Jaringan & Router</span>
                    </div>
                    <i class="fa-solid fa-chevron-down sidebar-group-arrow"></i>
                </button>
                <div class="sidebar-group-content {{ $isNetActive ? 'open' : '' }}" id="group-jaringan">
                    <a class="sidebar-sublink {{ Request::is('pppoe') ? 'active' : '' }}" href="{{ route('pppoe.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Sesi Aktif (Live)</span>
                    </a>
                    @if ($isAdmin)
                        <a class="sidebar-sublink {{ Request::is('pppoe-accounts*') ? 'active' : '' }}" href="{{ route('pppoe_accounts.index') }}">
                            <i class="fa-solid fa-circle-dot"></i>
                            <span>Daftar Akun PPPoE</span>
                        </a>
                    @endif
                    <a class="sidebar-sublink {{ Request::is('olt*') ? 'active' : '' }}" href="{{ route('olt.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Manajemen OLT</span>
                    </a>
                    <a class="sidebar-sublink {{ Request::is('odp*') ? 'active' : '' }}" href="{{ route('odp.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Manajemen ODP</span>
                    </a>
                    @if ($isAdmin)
                        <a class="sidebar-sublink {{ Request::is('genieacs*') ? 'active' : '' }}" href="{{ route('genieacs.index') }}">
                            <i class="fa-solid fa-circle-dot"></i>
                            <span>Modem ONT (ACS)</span>
                        </a>
                        <a class="sidebar-sublink {{ Request::is('monitoring') ? 'active' : '' }}" href="{{ route('monitoring.index') }}">
                            <i class="fa-solid fa-circle-dot"></i>
                            <span>Traffic Monitoring</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- 3. LAYANAN PELANGGAN (Accordion) -->
        <div class="sidebar-group">
            <button type="button" class="sidebar-group-btn {{ $isCustActive ? 'active open' : '' }}"
                data-group-target="group-pelanggan" onclick="toggleSidebarGroup('group-pelanggan')">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-users" style="width: 18px; text-align: center;"></i>
                    <span>Layanan Pelanggan</span>
                </div>
                <i class="fa-solid fa-chevron-down sidebar-group-arrow"></i>
            </button>
            <div class="sidebar-group-content {{ $isCustActive ? 'open' : '' }}" id="group-pelanggan">
                @if ($isAdmin || $isSales)
                    <a class="sidebar-sublink {{ Request::is('pre-orders') ? 'active' : '' }}" href="{{ route('pre_orders.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Pre-Order (PSB)</span>
                    </a>
                @endif
                <a class="sidebar-sublink {{ Request::is('pre-orders/waiting-list*') ? 'active' : '' }}" href="{{ route('pre_orders.waiting_list') }}">
                    <i class="fa-solid fa-circle-dot"></i>
                    <span>Prospek Tunggu (Ekspansi)</span>
                </a>
                <a class="sidebar-sublink {{ Request::is('customers') || Request::is('customers/active-list') ? 'active' : '' }}"
                    href="{{ $isTeknisi ? route('customers.index') : route('customers.active_list') }}">
                    <i class="fa-solid fa-circle-dot"></i>
                    <span>Data Pelanggan</span>
                </a>
                @if ($isAdmin || $isTeknisi)
                    <a class="sidebar-sublink {{ Request::is('maintenances*') ? 'active' : '' }}" href="{{ route('maintenances.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Tiket Pemeliharaan</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- 4. GUDANG & ASET (Single Link) -->
        @if ($isAdmin)
            <a class="sidebar-link {{ Request::is('assets*') ? 'active' : '' }}" href="{{ route('assets.index') }}">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>Pengelolaan Aset</span>
            </a>
        @endif

        <!-- 5. BILLING & KEUANGAN (Accordion) -->
        @if ($isAdmin)
            <div class="sidebar-group">
                <button type="button" class="sidebar-group-btn {{ $isFinActive ? 'active open' : '' }}"
                    data-group-target="group-keuangan" onclick="toggleSidebarGroup('group-keuangan')">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-file-invoice-dollar" style="width: 18px; text-align: center;"></i>
                        <span>Billing & Keuangan</span>
                    </div>
                    <i class="fa-solid fa-chevron-down sidebar-group-arrow"></i>
                </button>
                <div class="sidebar-group-content {{ $isFinActive ? 'open' : '' }}" id="group-keuangan">
                    <a class="sidebar-sublink {{ Request::is('invoices*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Invoice Tagihan</span>
                    </a>
                    <a class="sidebar-sublink {{ Request::is('transactions*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Riwayat Transaksi</span>
                    </a>
                    <a class="sidebar-sublink {{ Request::is('keuangan/teknisi*') ? 'active' : '' }}" href="{{ route('finance.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Pendapatan Teknisi</span>
                    </a>
                    <a class="sidebar-sublink {{ Request::is('keuangan/kasbon*') ? 'active' : '' }}" href="{{ route('kasbons.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Kasbon Teknisi</span>
                    </a>
                    @if ($isSuperAdmin)
                        <a class="sidebar-sublink {{ Request::is('withdrawals*') ? 'active' : '' }}" href="{{ route('withdrawals.index') }}" style="color: #10b981;">
                            <i class="fa-solid fa-circle-dot" style="color: #10b981;"></i>
                            <span>XenPlatform</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- 6. LAPORAN (Accordion) -->
        @if ($isAdmin)
            <div class="sidebar-group">
                <button type="button" class="sidebar-group-btn {{ $isReportActive ? 'active open' : '' }}"
                    data-group-target="group-laporan" onclick="toggleSidebarGroup('group-laporan')">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-chart-pie" style="width: 18px; text-align: center;"></i>
                        <span>Laporan Sistem</span>
                    </div>
                    <i class="fa-solid fa-chevron-down sidebar-group-arrow"></i>
                </button>
                <div class="sidebar-group-content {{ $isReportActive ? 'open' : '' }}" id="group-laporan">
                    <a class="sidebar-sublink {{ Request::is('laporan/aktivasi') ? 'active' : '' }}" href="{{ route('activitylog.aktivasi') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Lap. Aktivasi</span>
                    </a>
                    <a class="sidebar-sublink {{ Request::is('laporan/aktivasi-sales') ? 'active' : '' }}" href="{{ route('activitylog.aktivasi_sales') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Lap. Aktivasi Sales</span>
                    </a>
                    <a class="sidebar-sublink {{ Request::is('laporan/maintenance') ? 'active' : '' }}" href="{{ route('activitylog.maintenance') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Lap. Pemeliharaan</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- 7. MANAJEMEN & PENGATURAN (Accordion) -->
        @if ($isAdmin)
            <div class="sidebar-group">
                <button type="button" class="sidebar-group-btn {{ $isSysActive ? 'active open' : '' }}"
                    data-group-target="group-sistem" onclick="toggleSidebarGroup('group-sistem')">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-gear" style="width: 18px; text-align: center;"></i>
                        <span>Sistem & Pengaturan</span>
                    </div>
                    <i class="fa-solid fa-chevron-down sidebar-group-arrow"></i>
                </button>
                <div class="sidebar-group-content {{ $isSysActive ? 'open' : '' }}" id="group-sistem">
                    <a class="sidebar-sublink {{ Request::is('pakets*') ? 'active' : '' }}" href="{{ route('pakets.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Paket Layanan</span>
                    </a>
                    <a class="sidebar-sublink {{ Request::is('nas*') ? 'active' : '' }}" href="{{ route('nas.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Router MikroTik</span>
                    </a>
                    <a class="sidebar-sublink {{ Request::is('accounts') ? 'active' : '' }}" href="{{ route('accounts.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Manajemen Staf / Akun</span>
                    </a>
                    <a class="sidebar-sublink {{ Request::is('tracking') ? 'active' : '' }}" href="{{ route('accounts.tracking') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Pelacakan Lokasi</span>
                    </a>
                    <a class="sidebar-sublink {{ Request::is('activity-log') ? 'active' : '' }}" href="{{ route('activitylog.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Log Aktivitas</span>
                    </a>
                    <a class="sidebar-sublink {{ Request::is('migration*') ? 'active' : '' }}" href="{{ route('migration.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Backup & Restore</span>
                    </a>
                    <a class="sidebar-sublink {{ Request::is('settings*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                        <i class="fa-solid fa-circle-dot"></i>
                        <span>Pengaturan Sistem</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- 8. LOGOUT -->
        <form action="{{ route('logout') }}" method="POST" id="logout-form" class="hidden">@csrf</form>
        <a class="sidebar-link" href="#" style="color: #f87171; margin-top: 0.5rem;"
            onclick="localStorage.removeItem('app_token'); document.getElementById('logout-form').submit(); return false;">
            <i class="fa-solid fa-arrow-right-from-bracket" style="color: #f87171;"></i>
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

<script>
    function toggleSidebarGroup(groupId) {
        const content = document.getElementById(groupId);
        const btn = document.querySelector(`[data-group-target="${groupId}"]`);
        if (!content || !btn) return;

        const isOpen = content.classList.contains('open');
        if (isOpen) {
            content.classList.remove('open');
            btn.classList.remove('active');
            btn.classList.remove('open');
        } else {
            content.classList.add('open');
            btn.classList.add('active');
            btn.classList.add('open');
        }
    }
</script>
