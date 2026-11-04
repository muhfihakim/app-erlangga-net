@php
    $user = auth()->user();
    $isAdmin = $user && $user->isAdmin();
    $isTeknisi = $user && $user->jabatan === 'Teknisi';
    $isSales = $user && $user->jabatan === 'Sales';
    $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
@endphp

<!-- Mobile Bottom Navbar -->
<div class="mobile-bottom-nav">
    {{-- 1. Home / Overview (Semua Role) --}}
    <a href="{{ route('dashboards.index') }}" class="mobile-nav-item {{ Request::is('dashboard*') ? 'active' : '' }}">
        <i class="fa-solid fa-gauge"></i>
        <span>Home</span>
    </a>
    
    @if ($isAdmin)
        {{-- ROLE: ADMIN / SUPER ADMIN --}}
        <a href="{{ route('customers.active_list') }}" class="mobile-nav-item {{ Request::is('customers*') ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i>
            <span>Pelanggan</span>
        </a>
        <a href="{{ route('invoices.index') }}" class="mobile-nav-item {{ Request::is('invoices*') ? 'active' : '' }}">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            <span>Invoice</span>
        </a>
        <a href="{{ route('pppoe_accounts.index') }}" class="mobile-nav-item {{ Request::is('pppoe-accounts*') || Request::is('pppoe') ? 'active' : '' }}">
            <i class="fa-solid fa-users-gear"></i>
            <span>PPPoE</span>
        </a>
    @elseif ($isTeknisi)
        {{-- ROLE: TEKNISI --}}
        <a href="{{ route('customers.index') }}" class="mobile-nav-item {{ Request::is('customers*') ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i>
            <span>Pelanggan</span>
        </a>
        <a href="{{ route('maintenances.index') }}" class="mobile-nav-item {{ Request::is('maintenances*') ? 'active' : '' }}">
            <i class="fa-solid fa-screwdriver-wrench"></i>
            <span>Pemeliharaan</span>
        </a>
        <a href="{{ route('pppoe.index') }}" class="mobile-nav-item {{ Request::is('pppoe*') ? 'active' : '' }}">
            <i class="fa-solid fa-network-wired"></i>
            <span>PPPoE</span>
        </a>
    @elseif ($isSales)
        {{-- ROLE: SALES --}}
        <a href="{{ route('pre_orders.index') }}" class="mobile-nav-item {{ Request::is('pre-orders') ? 'active' : '' }}">
            <i class="fa-solid fa-user-plus"></i>
            <span>Pre-Order</span>
        </a>
        <a href="{{ route('customers.active_list') }}" class="mobile-nav-item {{ Request::is('customers*') ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i>
            <span>Pelanggan</span>
        </a>
        <a href="{{ route('kasbons.index') }}" class="mobile-nav-item {{ Request::is('keuangan/kasbon*') || Request::is('kasbons*') ? 'active' : '' }}">
            <i class="fa-solid fa-hand-holding-dollar"></i>
            <span>Kasbon</span>
        </a>
    @else
        {{-- ROLE: LAINNYA / FALLBACK --}}
        <a href="{{ route('customers.active_list') }}" class="mobile-nav-item {{ Request::is('customers*') ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i>
            <span>Pelanggan</span>
        </a>
        <a href="{{ route('odp.index') }}" class="mobile-nav-item {{ Request::is('odp*') ? 'active' : '' }}">
            <i class="fa-solid fa-tower-broadcast"></i>
            <span>ODP</span>
        </a>
        <a href="{{ route('kasbons.index') }}" class="mobile-nav-item {{ Request::is('keuangan/kasbon*') || Request::is('kasbons*') ? 'active' : '' }}">
            <i class="fa-solid fa-hand-holding-dollar"></i>
            <span>Kasbon</span>
        </a>
    @endif

    {{-- Tombol Menu Lengkap (Sheet Drawer) --}}
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
        <h3 style="margin: 12px 0 0 0; font-size: 17px; font-weight: 800; color: #0f172a;">Menu Navigasi Lengkap</h3>
        @if ($user)
            <p style="margin: 3px 0 0 0; font-size: 11px; color: #64748b; font-weight: 500;">
                {{ $user->nama_lengkap ?? $user->name }} • <span style="color: #0284c7; font-weight: 700;">{{ $user->jabatan ?? 'Staf' }}</span>
            </p>
        @endif
    </div>
    <div class="mobile-menu-sheet-content">
        
        {{-- 1. Jaringan & Router --}}
        @if ($isAdmin || $isTeknisi)
            <div class="sidebar-section-label">Jaringan & Router</div>
            <div class="mobile-menu-grid">
                <a class="mobile-grid-item {{ Request::is('pppoe') ? 'active' : '' }}" href="{{ route('pppoe.index') }}">
                    <i class="fa-solid fa-network-wired"></i>
                    <span>PPPoE Aktif</span>
                </a>
                @if ($isAdmin)
                    <a class="mobile-grid-item {{ Request::is('pppoe-accounts*') ? 'active' : '' }}" href="{{ route('pppoe_accounts.index') }}">
                        <i class="fa-solid fa-users-gear"></i>
                        <span>Akun PPPoE</span>
                    </a>
                @endif
                <a class="mobile-grid-item {{ Request::is('pppoe-sessions*') ? 'active' : '' }}" href="{{ route('pppoe_sessions.index') }}">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Sesi PPPoE</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('olt*') ? 'active' : '' }}" href="{{ route('olt.index') }}">
                    <i class="fa-solid fa-server"></i>
                    <span>Manajemen OLT</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('odp*') ? 'active' : '' }}" href="{{ route('odp.index') }}">
                    <i class="fa-solid fa-tower-broadcast"></i>
                    <span>Manajemen ODP</span>
                </a>
                @if ($isAdmin)
                    <a class="mobile-grid-item {{ Request::is('genieacs*') ? 'active' : '' }}" href="{{ route('genieacs.index') }}">
                        <i class="fa-solid fa-wifi"></i>
                        <span>Modem (ACS)</span>
                    </a>
                @endif
            </div>
        @endif

        {{-- 2. Layanan Pelanggan --}}
        <div class="sidebar-section-label" style="margin-top:.75rem;">Layanan Pelanggan</div>
        <div class="mobile-menu-grid">
            @if ($isAdmin || $isSales)
                <a class="mobile-grid-item {{ Request::is('pre-orders') ? 'active' : '' }}" href="{{ route('pre_orders.index') }}">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Pre-Order</span>
                </a>
            @endif
            <a class="mobile-grid-item {{ Request::is('pre-orders/waiting-list*') ? 'active' : '' }}" href="{{ route('pre_orders.waiting_list') }}">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Prospek Tunggu</span>
            </a>
            <a class="mobile-grid-item {{ Request::is('customers') || Request::is('customers/active-list') ? 'active' : '' }}" href="{{ $isTeknisi ? route('customers.index') : route('customers.active_list') }}">
                <i class="fa-solid fa-users"></i>
                <span>Pelanggan</span>
            </a>
            @if ($isAdmin || $isTeknisi)
                <a class="mobile-grid-item {{ Request::is('maintenances*') ? 'active' : '' }}" href="{{ route('maintenances.index') }}">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                    <span>Pemeliharaan</span>
                </a>
            @endif
        </div>

        {{-- 3. Gudang & Aset --}}
        @if ($isAdmin)
            <div class="sidebar-section-label" style="margin-top:.75rem;">Gudang & Aset</div>
            <div class="mobile-menu-grid">
                <a class="mobile-grid-item {{ Request::is('assets*') ? 'active' : '' }}" href="{{ route('assets.index') }}">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>Pengelolaan Aset</span>
                </a>
            </div>

            {{-- WhatsApp Center --}}
            <div class="sidebar-section-label" style="margin-top:.75rem;">Komunikasi Pelanggan</div>
            <div class="mobile-menu-grid">
                <a class="mobile-grid-item {{ Request::is('whatsapp*') ? 'active' : '' }}" href="{{ route('whatsapp.index') }}">
                    <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                    <span>WhatsApp Center</span>
                </a>
            </div>
        @endif

        {{-- 4. Billing & Keuangan --}}
        @if ($isAdmin || $isTeknisi || $isSales)
            <div class="sidebar-section-label" style="margin-top:.75rem;">Billing & Keuangan</div>
            <div class="mobile-menu-grid">
                @if ($isAdmin)
                    <a class="mobile-grid-item {{ Request::is('invoices*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span>Invoice Tagihan</span>
                    </a>
                    <a class="mobile-grid-item {{ Request::is('transactions*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Log Transaksi</span>
                    </a>
                    <a class="mobile-grid-item {{ Request::is('keuangan/teknisi*') ? 'active' : '' }}" href="{{ route('finance.index') }}">
                        <i class="fa-solid fa-wallet"></i>
                        <span>Pendapatan Teknisi</span>
                    </a>
                @endif
                <a class="mobile-grid-item {{ Request::is('keuangan/kasbon*') || Request::is('kasbons*') ? 'active' : '' }}" href="{{ route('kasbons.index') }}">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <span>Kasbon</span>
                </a>
                @if ($isSuperAdmin)
                    <a class="mobile-grid-item {{ Request::is('withdrawals*') ? 'active' : '' }}" href="{{ route('withdrawals.index') }}">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                        <span>XenPlatform</span>
                    </a>
                @endif
            </div>
        @endif

        {{-- 5. Laporan Sistem --}}
        @if ($isAdmin)
            <div class="sidebar-section-label" style="margin-top:.75rem;">Laporan Sistem</div>
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
            </div>
        @endif

        {{-- 6. Sistem & Manajemen --}}
        @if ($isAdmin)
            <div class="sidebar-section-label" style="margin-top:.75rem;">Sistem & Manajemen</div>
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
                    <span>Manajemen Akun</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('tracking') ? 'active' : '' }}" href="{{ route('accounts.tracking') }}">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Pelacakan Akun</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('activity-log') ? 'active' : '' }}" href="{{ route('activitylog.index') }}">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>Log Aktivitas</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('migration*') ? 'active' : '' }}" href="{{ route('migration.index') }}">
                    <i class="fa-solid fa-database"></i>
                    <span>Backup & Restore</span>
                </a>
                <a class="mobile-grid-item {{ Request::is('settings*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Pengaturan</span>
                </a>
            </div>
        @endif

        {{-- 7. Akun & Aplikasi --}}
        <div class="sidebar-section-label" style="margin-top:.75rem;">Akun & Aplikasi</div>
        <div class="mobile-menu-grid">
            <a class="mobile-grid-item {{ Request::is('changelogs*') ? 'active' : '' }}" href="{{ route('changelogs.index') }}">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Riwayat Versi</span>
            </a>
            <a class="mobile-grid-item" href="#" style="color:#f87171;" onclick="localStorage.removeItem('app_token'); (document.getElementById('mobile-logout-form') || document.getElementById('logout-form')).submit(); return false;">
                <i class="fa-solid fa-arrow-right-from-bracket" style="color:#f87171;"></i>
                <span>Keluar</span>
            </a>
        </div>
        <form action="{{ route('logout') }}" method="POST" id="mobile-logout-form" class="hidden">@csrf</form>
        <div style="height: 50px;"></div>
    </div>
</div>

<script>
    function openMobileMenuSheet() {
        const overlay = document.getElementById('mobile-menu-sheet-overlay');
        const sheet = document.getElementById('mobile-menu-sheet');
        if (overlay) overlay.classList.add('show');
        if (sheet) sheet.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeMobileMenuSheet() {
        const overlay = document.getElementById('mobile-menu-sheet-overlay');
        const sheet = document.getElementById('mobile-menu-sheet');
        if (overlay) overlay.classList.remove('show');
        if (sheet) sheet.classList.remove('show');
        document.body.style.overflow = '';
    }
</script>
