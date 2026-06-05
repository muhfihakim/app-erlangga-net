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
                                        <div class="notif-text">${notif.description}</div>
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
