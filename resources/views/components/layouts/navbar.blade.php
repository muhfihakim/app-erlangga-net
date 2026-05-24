<!-- Top bar -->
<div class="topbar">
    <div style="display:flex;align-items:center;gap:8px;">
        <button class="mobile-menu-btn" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="topbar-breadcrumb">
            <i class="fa-solid fa-house text-xs"></i>
            <span class="sep">/</span>
            @if(request()->routeIs('dashboards.index'))
                <span class="current">Dashboard</span>
            @elseif(request()->routeIs('accounts.index'))
                <span>Manajemen</span>
                <span class="sep">/</span>
                <span class="current">Akun Pengguna</span>
            @elseif(request()->routeIs('accounts.tracking'))
                <span>Manajemen</span>
                <span class="sep">/</span>
                <span class="current">Tracking Teknisi</span>
            @elseif(request()->routeIs('activitylog.index'))
                <span>Laporan</span>
                <span class="sep">/</span>
                <span class="current">Log Aktivitas</span>
            @elseif(request()->routeIs('activitylog.preorder'))
                <span>Laporan</span>
                <span class="sep">/</span>
                <span class="current">Laporan Pre-Order</span>
            @elseif(request()->routeIs('activitylog.expired'))
                <span>Laporan</span>
                <span class="sep">/</span>
                <span class="current">Pre-Order Expired</span>
            @elseif(request()->routeIs('activitylog.aktivasi'))
                <span>Laporan</span>
                <span class="sep">/</span>
                <span class="current">Laporan Aktivasi</span>
            @elseif(request()->routeIs('activitylog.maintenance'))
                <span>Laporan</span>
                <span class="sep">/</span>
                <span class="current">Laporan Maintenance</span>
            @elseif(request()->routeIs('activitylog.maintenance_expired'))
                <span>Laporan</span>
                <span class="sep">/</span>
                <span class="current">Maintenance Expired</span>
            @elseif(request()->routeIs('activitylog.dismantle'))
                <span>Laporan</span>
                <span class="sep">/</span>
                <span class="current">Laporan Dismantle</span>
            @elseif(request()->routeIs('maintenances.index'))
                <span>Layanan</span>
                <span class="sep">/</span>
                <span class="current">Pemeliharaan</span>
            @elseif(request()->routeIs('dismantles.index'))
                <span>Layanan</span>
                <span class="sep">/</span>
                <span class="current">Pencabutan</span>
            @elseif(request()->routeIs('pre_orders.index'))
                <span>Penjualan</span>
                <span class="sep">/</span>
                <span class="current">Pre-Order</span>
            @elseif(request()->routeIs('customers.active_list'))
                <span>Penjualan</span>
                <span class="sep">/</span>
                <span class="current">Pelanggan Aktif</span>
            @elseif(request()->routeIs('odp.index'))
                <span>Manajemen</span>
                <span class="sep">/</span>
                <span class="current">Manajemen ODP</span>
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
            <div class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400">
                <i class="fa-solid fa-user"></i>
            </div>
        </div>
    </div>
</div>
