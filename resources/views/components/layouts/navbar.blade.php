<!-- Top bar -->
<div class="topbar">
    <div style="display:flex;align-items:center;gap:8px;">
        <button class="mobile-menu-btn" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="topbar-breadcrumb">
            <a href="{{ route('dashboards.index') }}" class="text-slate-400 hover:text-sky-600 transition-colors flex items-center" title="Dashboard">
                <i class="fa-solid fa-house text-xs"></i>
            </a>
            <span class="sep">/</span>
            
            {{-- Dashboard --}}
            @if (request()->routeIs('dashboards.*') || request()->is('dashboard'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="location.href='{{ route('dashboards.index') }}'">Dashboard</span>
                <span class="sep">/</span>
                <span class="current">Overview</span>

            {{-- Jaringan & Router --}}
            @elseif(request()->routeIs('pppoe_accounts.*') || request()->is('pppoe-accounts*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-jaringan')">Jaringan & Router</span>
                <span class="sep">/</span>
                <span class="current">Daftar Akun PPPoE</span>
            @elseif(request()->routeIs('pppoe.*') || request()->is('pppoe'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-jaringan')">Jaringan & Router</span>
                <span class="sep">/</span>
                <span class="current">PPPoE Aktif</span>
            @elseif(request()->routeIs('olt.*') || request()->is('olt*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-jaringan')">Jaringan & Router</span>
                <span class="sep">/</span>
                <span class="current">Manajemen OLT</span>
            @elseif(request()->routeIs('odp.*') || request()->is('odp*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-jaringan')">Jaringan & Router</span>
                <span class="sep">/</span>
                <span class="current">Manajemen ODP</span>
            @elseif(request()->routeIs('genieacs.*') || request()->is('genieacs*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-jaringan')">Jaringan & Router</span>
                <span class="sep">/</span>
                @if (request()->routeIs('genieacs.show'))
                    <a href="{{ route('genieacs.index') }}" class="hover:text-slate-900 transition-colors">Modem ONT (ACS)</a>
                    <span class="sep">/</span>
                    <span class="current">Detail ONT</span>
                @else
                    <span class="current">Modem ONT (ACS)</span>
                @endif
            @elseif(request()->routeIs('monitoring.*') || request()->is('monitoring*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-jaringan')">Jaringan & Router</span>
                <span class="sep">/</span>
                <span class="current">Traffic Monitoring</span>

            {{-- Layanan Pelanggan --}}
            @elseif(request()->routeIs('pre_orders.waiting_list*') || request()->is('pre-orders/waiting-list*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-pelanggan')">Layanan Pelanggan</span>
                <span class="sep">/</span>
                <span class="current">Prospek Tunggu (Ekspansi)</span>
            @elseif(request()->routeIs('pre_orders.*') || request()->is('pre-orders*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-pelanggan')">Layanan Pelanggan</span>
                <span class="sep">/</span>
                <span class="current">Pre-Order (PSB)</span>
            @elseif(request()->routeIs('customers.*') || request()->is('customers*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-pelanggan')">Layanan Pelanggan</span>
                <span class="sep">/</span>
                <span class="current">Data Pelanggan</span>
            @elseif(request()->routeIs('maintenances.*') || request()->is('maintenances*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-pelanggan')">Layanan Pelanggan</span>
                <span class="sep">/</span>
                <span class="current">Tiket Pemeliharaan</span>

            {{-- Gudang & Aset --}}
            @elseif(request()->routeIs('assets.transactions*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="location.href='{{ route('assets.index') }}'">Pengelolaan Aset</span>
                <span class="sep">/</span>
                <span class="current">Log Transaksi</span>
            @elseif(request()->routeIs('assets.*') || request()->is('assets*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="location.href='{{ route('assets.index') }}'">Pengelolaan Aset</span>
                <span class="sep">/</span>
                <span class="current">Daftar Aset</span>

            {{-- Billing & Keuangan --}}
            @elseif(request()->routeIs('invoices.show', 'invoices.print'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-keuangan')">Billing & Keuangan</span>
                <span class="sep">/</span>
                <a href="{{ route('invoices.index') }}" class="hover:text-slate-900 transition-colors">Invoice Tagihan</a>
                <span class="sep">/</span>
                <span class="current">Detail Tagihan</span>
            @elseif(request()->routeIs('invoices.*') || request()->is('invoices*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-keuangan')">Billing & Keuangan</span>
                <span class="sep">/</span>
                <span class="current">Invoice Tagihan</span>
            @elseif(request()->routeIs('transactions.*') || request()->is('transactions*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-keuangan')">Billing & Keuangan</span>
                <span class="sep">/</span>
                <span class="current">Riwayat Transaksi</span>
            @elseif(request()->routeIs('finance.*') || request()->is('keuangan/teknisi*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-keuangan')">Billing & Keuangan</span>
                <span class="sep">/</span>
                <span class="current">Pendapatan Teknisi</span>
            @elseif(request()->routeIs('kasbons.*') || request()->is('keuangan/kasbon*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-keuangan')">Billing & Keuangan</span>
                <span class="sep">/</span>
                <span class="current">Kasbon Teknisi</span>
            @elseif(request()->routeIs('withdrawals.*') || request()->is('withdrawals*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-keuangan')">Billing & Keuangan</span>
                <span class="sep">/</span>
                <span class="current">XenPlatform</span>

            {{-- Laporan Sistem --}}
            @elseif(request()->routeIs('activitylog.aktivasi_sales*') || request()->is('laporan/aktivasi-sales*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-laporan')">Laporan Sistem</span>
                <span class="sep">/</span>
                <span class="current">Lap. Aktivasi Sales</span>
            @elseif(request()->routeIs('activitylog.aktivasi*') || request()->is('laporan/aktivasi*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-laporan')">Laporan Sistem</span>
                <span class="sep">/</span>
                <span class="current">Lap. Aktivasi</span>
            @elseif(request()->routeIs('activitylog.maintenance*') || request()->is('laporan/maintenance*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-laporan')">Laporan Sistem</span>
                <span class="sep">/</span>
                <span class="current">Lap. Pemeliharaan</span>
            @elseif(request()->routeIs('activitylog.preorder*') || request()->is('laporan/preorder*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-laporan')">Laporan Sistem</span>
                <span class="sep">/</span>
                <span class="current">Lap. Pre-Order</span>

            {{-- Sistem & Pengaturan --}}
            @elseif(request()->routeIs('pakets.*', 'paket.*') || request()->is('pakets*') || request()->is('paket*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-sistem')">Sistem & Pengaturan</span>
                <span class="sep">/</span>
                <span class="current">Paket Layanan</span>
            @elseif(request()->routeIs('nas.*') || request()->is('nas*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-sistem')">Sistem & Pengaturan</span>
                <span class="sep">/</span>
                <span class="current">Router MikroTik</span>
            @elseif(request()->routeIs('accounts.tracking') || request()->is('tracking*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-sistem')">Sistem & Pengaturan</span>
                <span class="sep">/</span>
                <span class="current">Pelacakan Lokasi</span>
            @elseif(request()->routeIs('accounts.*', 'admin.users.2fa.*') || request()->is('accounts*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-sistem')">Sistem & Pengaturan</span>
                <span class="sep">/</span>
                <span class="current">Manajemen Staf / Akun</span>
            @elseif(request()->routeIs('activitylog.index*') || request()->is('activity-log*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-sistem')">Sistem & Pengaturan</span>
                <span class="sep">/</span>
                <span class="current">Log Aktivitas</span>
            @elseif(request()->routeIs('migration.*') || request()->is('migration*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-sistem')">Sistem & Pengaturan</span>
                <span class="sep">/</span>
                <span class="current">Backup & Restore</span>
            @elseif(request()->routeIs('settings.*') || request()->is('settings*'))
                <span class="cursor-pointer hover:text-slate-900 transition-colors" onclick="toggleSidebarGroup('group-sistem')">Sistem & Pengaturan</span>
                <span class="sep">/</span>
                <span class="current">Pengaturan Sistem</span>

            {{-- Fallback --}}
            @else
                <span class="current">Sistem</span>
            @endif
        </div>
    </div>
    <div class="topbar-actions">
        <!-- Notification Dropdown Wrapper -->
        <div style="position: relative; display: flex; align-items: center;">
            <button class="topbar-icon-btn" title="Notifikasi" id="notif-btn" type="button">
                <i class="fa-regular fa-bell"></i>
                <span class="topbar-notif-dot hidden" id="notif-dot"></span>
            </button>

            <div class="notif-dropdown" id="notif-dropdown">
                <div class="notif-header">
                    <span class="notif-title">Notifikasi Terbaru</span>
                    <span class="badge bg-sky-100 text-sky-600 !text-[10px] font-black" id="notif-count">0</span>
                </div>
                <div class="notif-list" id="notif-list">
                    <div class="p-8 text-center text-slate-400">
                        <i class="fa-solid fa-spinner fa-spin text-2xl mb-2"></i>
                        <p class="text-xs">Memuat notifikasi...</p>
                    </div>
                </div>
                @if (auth()->user()->isAdmin())
                    <div class="notif-footer">
                        <a href="{{ route('activitylog.index') }}" class="notif-view-all">Lihat Semua Log</a>
                    </div>
                @endif
            </div>
        </div>

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

@push('scripts')
<script>
    $(document).ready(function() {
        const notifBtn = $('#notif-btn');
        const notifDropdown = $('#notif-dropdown');
        const notifList = $('#notif-list');
        const notifDot = $('#notif-dot');
        const notifCount = $('#notif-count');

        // Initial fetch to show dot if there are new items
        fetchNotifications(false);

        notifBtn.click(function(e) {
            e.stopPropagation();
            notifDropdown.toggleClass('show');
            if (notifDropdown.hasClass('show')) {
                fetchNotifications(true);
                notifDot.addClass('hidden'); // Hide dot when opened
            }
        });

        $(document).click(function(e) {
            if (!notifDropdown.is(e.target) && notifDropdown.has(e.target).length === 0) {
                notifDropdown.removeClass('show');
            }
        });

        function fetchNotifications(showLoading) {
            if (showLoading) {
                notifList.html(`
                    <div class="p-8 text-center text-slate-400">
                        <i class="fa-solid fa-spinner fa-spin text-2xl mb-2"></i>
                        <p class="text-xs">Memuat notifikasi...</p>
                    </div>
                `);
            }

            $.ajax({
                url: "{{ route('activitylog.notifications') }}",
                method: "GET",
                success: function(data) {
                    if (data.length > 0) {
                        notifCount.text(data.length);
                        // Check if we should show the dot (optional: compare with last ID in storage)
                        const lastId = localStorage.getItem('last_notif_id');
                        if (!lastId || parseInt(lastId) < data[0].id) {
                            notifDot.removeClass('hidden');
                        }

                        let html = '';
                        data.forEach(notif => {
                            html += `
                                <div class="notif-item">
                                    <div class="notif-icon ${notif.color}">
                                        <i class="fa-solid ${notif.icon}"></i>
                                    </div>
                                    <div class="notif-content">
                                        <div class="notif-text"><span style="font-weight:700; color:#0f172a;">${notif.user}</span>: ${notif.description}</div>
                                        <div class="notif-time">${notif.time}</div>
                                    </div>
                                </div>
                            `;
                        });
                        notifList.html(html);

                        // Update last ID on open
                        if (notifDropdown.hasClass('show')) {
                            localStorage.setItem('last_notif_id', data[0].id);
                        }
                    } else {
                        notifList.html(`
                            <div class="p-8 text-center text-slate-400">
                                <i class="fa-regular fa-bell-slash text-2xl mb-2"></i>
                                <p class="text-xs">Tidak ada notifikasi terbaru</p>
                            </div>
                        `);
                        notifCount.text('0');
                        notifDot.addClass('hidden');
                    }
                },
                error: function() {
                    notifList.html(`
                        <div class="p-8 text-center text-rose-400">
                            <i class="fa-solid fa-circle-exclamation text-2xl mb-2"></i>
                            <p class="text-xs">Gagal memuat data</p>
                        </div>
                    `);
                }
            });
        }

        // Auto-refresh every 5 minutes
        setInterval(() => fetchNotifications(false), 300000);
    });
</script>
@endpush
