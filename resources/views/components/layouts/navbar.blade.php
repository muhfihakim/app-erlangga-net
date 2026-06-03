<!-- Top bar -->
<div class="topbar">
    <div style="display:flex;align-items:center;gap:8px;">
        <button class="mobile-menu-btn" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="topbar-breadcrumb">
            <i class="fa-solid fa-house text-xs"></i>
            <span class="sep">/</span>
            @if (request()->routeIs('dashboards.index'))
                <span class="current">Dashboard</span>
            @elseif(request()->routeIs('olt.index') ||
                    request()->routeIs('odp.index') ||
                    request()->routeIs('pppoe.index') ||
                    request()->routeIs('monitoring.index'))
                <span>Infrastruktur</span>
                <span class="sep">/</span>
                @if (request()->routeIs('olt.index'))
                    <span class="current">Manajemen OLT</span>
                @elseif(request()->routeIs('odp.index'))
                    <span class="current">Manajemen ODP</span>
                @elseif(request()->routeIs('pppoe.index'))
                    <span class="current">PPPoE Aktif</span>
                @elseif(request()->routeIs('monitoring.index'))
                    <span class="current">Monitoring</span>
                @endif
            @elseif(request()->routeIs('pre_orders.index') ||
                    request()->routeIs('customers.active_list') ||
                    request()->routeIs('customers.index') ||
                    request()->routeIs('maintenances.index') ||
                    request()->routeIs('dismantles.index'))
                <span>Pelanggan</span>
                <span class="sep">/</span>
                @if (request()->routeIs('pre_orders.index'))
                    <span class="current">Pre-Order</span>
                @elseif(request()->routeIs('customers.active_list') || request()->routeIs('customers.index'))
                    <span class="current">Pelanggan</span>
                @elseif(request()->routeIs('maintenances.index'))
                    <span class="current">Pemeliharaan</span>
                @elseif(request()->routeIs('dismantles.index'))
                    <span class="current">Pencabutan</span>
                @endif
            @elseif(request()->routeIs('accounts.index') || request()->routeIs('activitylog.index'))
                <span>Manajemen</span>
                <span class="sep">/</span>
                @if (request()->routeIs('accounts.index'))
                    <span class="current">Akun</span>
                @elseif(request()->routeIs('activitylog.index'))
                    <span class="current">Aktivitas Log</span>
                @endif
            @elseif(request()->routeIs('activitylog.aktivasi') ||
                    request()->routeIs('activitylog.maintenance') ||
                    request()->routeIs('activitylog.dismantle') ||
                    request()->routeIs('activitylog.preorder'))
                <span>Laporan</span>
                <span class="sep">/</span>
                @if (request()->routeIs('activitylog.aktivasi'))
                    <span class="current">Lap. Aktivasi</span>
                @elseif (request()->routeIs('activitylog.maintenance'))
                    <span class="current">Lap. Maintenance</span>
                @elseif (request()->routeIs('activitylog.dismantle'))
                    <span class="current">Lap. Dismantle</span>
                @elseif (request()->routeIs('activitylog.preorder'))
                    <span class="current">Lap. Pre-Order</span>
                @endif
            @elseif(request()->routeIs('finance.index'))
                <span>Keuangan</span>
                <span class="sep">/</span>
                <span class="current">Pendapatan Teknisi</span>
            @else
                <span class="current">Sistem</span>
            @endif
        </div>
    </div>
    <div class="topbar-actions">
        <button class="topbar-icon-btn" title="Notifikasi">
            <i class="fa-regular fa-bell"></i>
            <span class="topbar-notif-dot"></span>
        </button>

        <div class="flex items-center gap-3 ml-2">
            <div class="text-right hidden sm:block">
                <p class="text-xs user-name leading-none">{{ auth()->user()->nama_lengkap ?? 'Pengguna' }}</p>
                <p class="text-[10px] user-role mt-1">{{ auth()->user()->jabatan ?? 'Jabatan' }}</p>
            </div>
            <div
                class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400">
                <i class="fa-solid fa-user"></i>
            </div>
        </div>
    </div>
</div>
