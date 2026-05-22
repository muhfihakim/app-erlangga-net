<!--begin::Sidebar-->
<aside class="app-sidebar bg-white border-end shadow-sm" data-bs-theme="light">
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand border-bottom-0 py-4">
        <!--begin::Brand Link-->
        <a href="/" class="brand-link border-bottom-0 text-center w-100">
            <!--begin::Brand Image-->
            <img src="{{ asset('dist/assets/img/logo.png') }}" alt="Logo Erlangga Net" class="brand-image" style="float: none; max-height: 40px;" />
            <!--end::Brand Image-->
        </a>
        <!--end::Brand Link-->
    </div>
    <!--end::Sidebar Brand-->
    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper px-3">
        <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column gap-1" data-lte-toggle="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('dashboards.index') }}"
                        class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 {{ Request::is('dashboard') ? 'bg-indigo-50 text-indigo-600 active' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="bi bi-grid-1x2"></i>
                        <p class="mb-0">Dashboard</p>
                    </a>
                </li>
                <li class="nav-header text-uppercase fs-xs fw-bold text-gray-400 mt-3 mb-1 px-3" style="font-size: 0.7rem; letter-spacing: 0.05rem;">Pelanggan</li>
                @if (auth()->check() && in_array(auth()->user()->jabatan, ['Administrator', 'Sales']))
                    <li class="nav-item">
                        <a href="{{ route('pre_orders.index') }}"
                            class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 {{ Request::is('pre-orders') ? 'bg-indigo-50 text-indigo-600 active' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="bi bi-person-plus"></i>
                            <p class="mb-0">Pre-Order</p>
                        </a>
                    </li>
                @endif
                @if (auth()->check() && auth()->user()->jabatan === 'Teknisi')
                    <li class="nav-item">
                        <a href="{{ route('customers.index') }}"
                            class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 {{ Request::is('customers') ? 'bg-indigo-50 text-indigo-600 active' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="bi bi-person-check"></i>
                            <p class="mb-0">Aktivasi</p>
                        </a>
                    </li>
                @endif
                {{-- Tautan untuk Sales dan Administrator (Lihat Daftar Pelanggan Aktif) --}}
                @if (auth()->check() && (auth()->user()->jabatan === 'Administrator' || auth()->user()->jabatan === 'Sales'))
                    <li class="nav-item">
                        <a href="{{ route('customers.active_list') }}"
                            class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 {{ Request::is('customers/active-list') ? 'bg-indigo-50 text-indigo-600 active' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="bi bi-person-check"></i>
                            <p class="mb-0">Aktivasi</p>
                        </a>
                    </li>
                @endif
                @if (auth()->check() && (auth()->user()->jabatan === 'Administrator' || auth()->user()->jabatan === 'Teknisi'))
                    <li class="nav-item">
                        <a href="{{ route('maintenances.index') }}"
                            class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 {{ Request::is('maintenances') ? 'bg-indigo-50 text-indigo-600 active' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="bi bi-person-gear"></i>
                            <p class="mb-0">Pemeliharaan</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('dismantles.index') }}"
                            class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 {{ Request::is('dismantles') ? 'bg-indigo-50 text-indigo-600 active' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="bi bi-person-x"></i>
                            <p class="mb-0">Dismantle</p>
                        </a>
                    </li>
                @endif
                @if (auth()->check() && auth()->user()->jabatan === 'Administrator')
                    <li class="nav-header text-uppercase fs-xs fw-bold text-gray-400 mt-3 mb-1 px-3" style="font-size: 0.7rem; letter-spacing: 0.05rem;">Perangkat</li>
                    <li class="nav-item">
                        <a href="#" class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 text-gray-400 opacity-50 cursor-not-allowed">
                            <i class="bi bi-router"></i>
                            <p class="mb-0">Router <span class="badge bg-indigo-100 text-indigo-600 rounded-pill ms-auto" style="font-size: 0.6rem;">Soon</span></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 text-gray-400 opacity-50 cursor-not-allowed">
                            <i class="bi bi-router"></i>
                            <p class="mb-0">PPPoE <span class="badge bg-indigo-100 text-indigo-600 rounded-pill ms-auto" style="font-size: 0.6rem;">Soon</span></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 text-gray-400 opacity-50 cursor-not-allowed">
                            <i class="bi bi-router"></i>
                            <p class="mb-0">Traffic <span class="badge bg-indigo-100 text-indigo-600 rounded-pill ms-auto" style="font-size: 0.6rem;">Soon</span></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 text-gray-400 opacity-50 cursor-not-allowed">
                            <i class="bi bi-router"></i>
                            <p class="mb-0">Log <span class="badge bg-indigo-100 text-indigo-600 rounded-pill ms-auto" style="font-size: 0.6rem;">Soon</span></p>
                        </a>
                    </li>
                    <li class="nav-header text-uppercase fs-xs fw-bold text-gray-400 mt-3 mb-1 px-3" style="font-size: 0.7rem; letter-spacing: 0.05rem;">Manajemen</li>
                    <li class="nav-item">
                        <a href="{{ route('accounts.index') }}"
                            class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 {{ Request::is('accounts') ? 'bg-indigo-50 text-indigo-600 active' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="bi bi-people"></i>
                            <p class="mb-0">Akun</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('activitylog.index') }}"
                            class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 {{ Request::is('activity-log') ? 'bg-indigo-50 text-indigo-600 active' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="bi bi-activity"></i>
                            <p class="mb-0">Aktivitas Log</p>
                        </a>
                    </li>
                    <li class="nav-header text-uppercase fs-xs fw-bold text-gray-400 mt-3 mb-1 px-3" style="font-size: 0.7rem; letter-spacing: 0.05rem;">Laporan</li>
                    <li class="nav-item">
                        <a href="{{ route('activitylog.aktivasi') }}"
                            class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 {{ Request::is('laporan/aktivasi') ? 'bg-indigo-50 text-indigo-600 active' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="bi bi-person-check-fill"></i>
                            <p class="mb-0">Aktivasi</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('activitylog.maintenance') }}"
                            class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 {{ Request::is('laporan/maintenance') ? 'bg-indigo-50 text-indigo-600 active' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="bi bi-person-fill-gear"></i>
                            <p class="mb-0">Pemeliharaan</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('activitylog.dismantle') }}"
                            class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 {{ Request::is('laporan/dismantle') ? 'bg-indigo-50 text-indigo-600 active' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="bi bi-person-fill-x"></i>
                            <p class="mb-0">Dismantle</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('activitylog.preorder') }}"
                            class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 {{ Request::is('laporan/preorder') ? 'bg-indigo-50 text-indigo-600 active' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="bi bi-person-fill-add"></i>
                            <p class="mb-0">Pre-Order</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('activitylog.expired') }}"
                            class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 {{ Request::is('laporan/preorder-expired') ? 'bg-indigo-50 text-indigo-600 active' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="bi bi-clock-history"></i>
                            <p class="mb-0">PO Expired</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('activitylog.maintenance_expired') }}"
                            class="nav-link rounded-2xl px-3 py-2 d-flex align-items-center gap-2 {{ Request::is('laporan/maintenance-expired') ? 'bg-indigo-50 text-indigo-600 active' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="bi bi-clock-history"></i>
                            <p class="mb-0">MT Expired</p>
                        </a>
                    </li>
                @endif
            </ul>
            <!--end::Sidebar Menu-->
        </nav>
    </div>
    <!--end::Sidebar Wrapper-->
</aside>
<!--end::Sidebar-->
