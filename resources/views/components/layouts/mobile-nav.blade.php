<!-- Mobile Bottom Navbar -->
<div class="mobile-bottom-nav">
    <a href="{{ route('dashboards.index') }}" class="mobile-nav-item {{ Request::is('dashboard') ? 'active' : '' }}">
        <i class="fa-solid fa-gauge"></i>
        <span>Home</span>
    </a>
    
    @if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->jabatan === 'Teknisi' || auth()->user()->jabatan === 'Sales'))
        <a href="{{ auth()->user()->jabatan === 'Teknisi' ? route('customers.index') : route('customers.active_list') }}" class="mobile-nav-item {{ Request::is('customers') || Request::is('customers/active-list') ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i>
            <span>Pelanggan</span>
        </a>
    @else
        <a href="{{ route('odp.index') }}" class="mobile-nav-item {{ Request::is('odp*') ? 'active' : '' }}">
            <i class="fa-solid fa-tower-broadcast"></i>
            <span>ODP</span>
        </a>
    @endif

    @if (auth()->check() && auth()->user()->isAdmin())
        <a href="{{ route('invoices.index') }}" class="mobile-nav-item {{ Request::is('invoices*') ? 'active' : '' }}">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            <span>Invoice</span>
        </a>
        <a href="{{ route('pppoe_accounts.index') }}" class="mobile-nav-item {{ Request::is('pppoe-accounts*') ? 'active' : '' }}">
            <i class="fa-solid fa-users-gear"></i>
            <span>PPPoE</span>
        </a>
    @elseif (auth()->check() && auth()->user()->jabatan === 'Teknisi')
        <a href="{{ route('maintenances.index') }}" class="mobile-nav-item {{ Request::is('maintenances*') ? 'active' : '' }}">
            <i class="fa-solid fa-screwdriver-wrench"></i>
            <span>Pemeliharaan</span>
        </a>
        <a href="{{ route('pppoe.index') }}" class="mobile-nav-item {{ Request::is('pppoe*') ? 'active' : '' }}">
            <i class="fa-solid fa-network-wired"></i>
            <span>PPPoE</span>
        </a>
    @elseif (auth()->check() && auth()->user()->jabatan === 'Sales')
        <a href="{{ route('pre_orders.index') }}" class="mobile-nav-item {{ Request::is('pre-orders*') ? 'active' : '' }}">
            <i class="fa-solid fa-user-plus"></i>
            <span>Pre-Order</span>
        </a>
    @endif

    <button type="button" class="mobile-nav-item" onclick="openMobileMenuSheet()">
        <i class="fa-solid fa-bars"></i>
        <span>Lainnya</span>
    </button>
</div>

<!-- Mobile Menu Bottom Sheet -->
<div class="mobile-menu-sheet-overlay" id="mobile-menu-sheet-overlay" onclick="closeMobileMenuSheet()"></div>
<div class="mobile-menu-sheet" id="mobile-menu-sheet">
    <div class="mobile-menu-sheet-header">
        <div class="mobile-menu-sheet-handle"></div>
        <h3 style="margin: 15px 0 0 0; font-size: 18px; font-weight: 800; color: #0f172a;">Menu Navigasi Lengkap</h3>
    </div>
    <div class="mobile-menu-sheet-content">
        
        <div class="sidebar-section-label">Infrastruktur</div>
        <div class="mobile-menu-grid">
            @if (auth()->check() && auth()->user()->jabatan !== 'Sales')
                <a class="mobile-grid-item {{ Request::is('olt*') ? 'active' : '' }}" href="{{ route('olt.index') }}">
                    <i class="fa-solid fa-server"></i>
                    <span>Manajemen OLT</span>
                </a>
            @endif
            <a class="mobile-grid-item {{ Request::is('odp*') ? 'active' : '' }}" href="{{ route('odp.index') }}">
                <i class="fa-solid fa-tower-broadcast"></i>
                <span>Manajemen ODP</span>
            </a>
            @if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->jabatan === 'Teknisi'))
                <a class="mobile-grid-item {{ Request::is('pppoe') ? 'active' : '' }}" href="{{ route('pppoe.index') }}">
                    <i class="fa-solid fa-network-wired"></i>
                    <span>PPPoE Aktif</span>
                </a>
            @endif
            @if (auth()->check() && auth()->user()->isAdmin())
                <a class="mobile-grid-item {{ Request::is('pppoe-accounts*') ? 'active' : '' }}" href="{{ route('pppoe_accounts.index') }}">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>Manajemen PPPoE</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('monitoring') ? 'active' : '' }}" href="{{ route('monitoring.index') }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Monitoring</span>
                </a>
            @endif
        </div>

        <div class="sidebar-section-label" style="margin-top:.75rem;">Pelanggan</div>
        <div class="mobile-menu-grid">
            @if (auth()->check() && in_array(auth()->user()->jabatan, ['Administrator', 'Sales']))
                <a class="mobile-grid-item {{ Request::is('pre-orders') ? 'active' : '' }}" href="{{ route('pre_orders.index') }}">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Pre-Order</span>
                </a>
            @endif
            @if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->jabatan === 'Teknisi' || auth()->user()->jabatan === 'Sales'))
                <a class="mobile-grid-item {{ Request::is('customers') || Request::is('customers/active-list') ? 'active' : '' }}" href="{{ auth()->user()->jabatan === 'Teknisi' ? route('customers.index') : route('customers.active_list') }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Pelanggan</span>
                </a>
            @endif
            @if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->jabatan === 'Teknisi'))
                <a class="mobile-grid-item {{ Request::is('maintenances') ? 'active' : '' }}" href="{{ route('maintenances.index') }}">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                    <span>Pemeliharaan</span>
                </a>
            @endif
            @if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->jabatan === 'Teknisi' || auth()->user()->jabatan === 'Sales'))
                <a class="mobile-grid-item {{ Request::is('dismantles') ? 'active' : '' }}" href="{{ route('dismantles.index') }}">
                    <i class="fa-solid fa-user-xmark"></i>
                    <span>Pencabutan</span>
                </a>
            @endif
            @if (auth()->check() && auth()->user()->jabatan !== 'Sales')
                <a class="mobile-grid-item {{ Request::is('supporting*') ? 'active' : '' }}" href="{{ route('supporting.index') }}">
                    <i class="fa-solid fa-handshake-angle"></i>
                    <span>Perbantuan</span>
                </a>
            @endif
        </div>

        @if (auth()->check() && auth()->user()->isAdmin())
            <div class="sidebar-section-label" style="margin-top:.75rem;">Gudang</div>
            <div class="mobile-menu-grid">
                <a class="mobile-grid-item {{ Request::is('assets*') ? 'active' : '' }}" href="{{ route('assets.index') }}">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>Pengelolaan Aset</span>
                </a>
            </div>

            <div class="sidebar-section-label" style="margin-top:.75rem;">Keuangan</div>
            <div class="mobile-menu-grid">
                <a class="mobile-grid-item {{ Request::is('invoices*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Invoice Pelanggan</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('transactions*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Log Transaksi</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('keuangan/teknisi*') ? 'active' : '' }}" href="{{ route('finance.index') }}">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Pendapatan Teknisi</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('keuangan/kasbon*') ? 'active' : '' }}" href="{{ route('kasbons.index') }}">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <span>Kasbon Teknisi</span>
                </a>
            </div>

            <div class="sidebar-section-label" style="margin-top:.75rem;">Laporan</div>
            <div class="mobile-menu-grid">
                <a class="mobile-grid-item {{ Request::is('laporan/aktivasi') ? 'active' : '' }}" href="{{ route('activitylog.aktivasi') }}">
                    <i class="fa-solid fa-file-contract"></i>
                    <span>Lap. Aktivasi</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('laporan/aktivasi-sales') ? 'active' : '' }}" href="{{ route('activitylog.aktivasi_sales') }}">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Lap. Aktivasi Sales</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('laporan/maintenance') ? 'active' : '' }}" href="{{ route('activitylog.maintenance') }}">
                    <i class="fa-solid fa-file-medical"></i>
                    <span>Lap. Pemeliharaan</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('laporan/dismantle') ? 'active' : '' }}" href="{{ route('activitylog.dismantle') }}">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Lap. Pencabutan</span>
                </a>
            </div>

            <div class="sidebar-section-label" style="margin-top:.75rem;">Manajemen</div>
            <div class="mobile-menu-grid">
                <a class="mobile-grid-item {{ Request::is('pakets*') ? 'active' : '' }}" href="{{ route('pakets.index') }}">
                    <i class="fa-solid fa-box-open"></i>
                    <span>Paket Layanan</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('nas*') ? 'active' : '' }}" href="{{ route('nas.index') }}">
                    <i class="fa-solid fa-network-wired"></i>
                    <span>Router (NAS)</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('accounts') ? 'active' : '' }}" href="{{ route('accounts.index') }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Akun</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('tracking') ? 'active' : '' }}" href="{{ route('accounts.tracking') }}">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Pelacakan Akun</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('activity-log') ? 'active' : '' }}" href="{{ route('activitylog.index') }}">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>Aktivitas Log</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('migration*') ? 'active' : '' }}" href="{{ route('migration.index') }}">
                    <i class="fa-solid fa-file-import"></i>
                    <span>Pulihkan Data</span>
                </a>
            </div>

            <div class="sidebar-section-label" style="margin-top:.75rem;">Pengaturan</div>
            <div class="mobile-menu-grid">
                <a class="mobile-grid-item {{ Request::is('settings*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Pengaturan Sistem</span>
                </a>
                <a class="mobile-grid-item" href="#" style="color:#f87171;" onclick="localStorage.removeItem('app_token'); document.getElementById('logout-form').submit(); return false;">
                    <i class="fa-solid fa-arrow-right-from-bracket" style="color:#f87171;"></i>
                    <span>Keluar</span>
                </a>
            </div>
        @else
            <div class="sidebar-section-label" style="margin-top:.75rem;">Pengaturan</div>
            <div class="mobile-menu-grid">
                <a class="mobile-grid-item" href="#" style="color:#f87171;" onclick="localStorage.removeItem('app_token'); document.getElementById('logout-form').submit(); return false;">
                    <i class="fa-solid fa-arrow-right-from-bracket" style="color:#f87171;"></i>
                    <span>Keluar</span>
                </a>
            </div>
        @endif
        <div style="height: 50px;"></div>
    </div>
</div>

<script>
    function openMobileMenuSheet() {
        document.getElementById('mobile-menu-sheet-overlay').classList.add('show');
        document.getElementById('mobile-menu-sheet').classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeMobileMenuSheet() {
        document.getElementById('mobile-menu-sheet-overlay').classList.remove('show');
        document.getElementById('mobile-menu-sheet').classList.remove('show');
        document.body.style.overflow = '';
    }
</script>
