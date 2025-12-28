 <!--begin::Sidebar-->
 <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
     <!--begin::Sidebar Brand-->
     <div class="sidebar-brand">
         <!--begin::Brand Link-->
         <a href="/" class="brand-link">
             <!--begin::Brand Image-->
             <img src="{{ asset('dist/assets/img/logo.png') }}" alt="Logo Pemprov Papua Tengah" class="brand-image" />
             <!--end::Brand Image-->
         </a>
         <!--end::Brand Link-->
     </div>
     <!--end::Sidebar Brand-->
     <!--begin::Sidebar Wrapper-->
     <div class="sidebar-wrapper">
         <nav class="mt-2">
             <!--begin::Sidebar Menu-->
             <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                 <li class="nav-item">
                     <a href="{{ route('dashboards.index') }}"
                         class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}">
                         <i class="bi bi-exclamation-square"></i>
                         <p>Dashboard</p>
                     </a>
                 </li>
                 <li class="nav-header">PELANGGAN</li>
                 @if (auth()->check() && in_array(auth()->user()->jabatan, ['Administrator', 'Sales']))
                     <li class="nav-item">
                         <a href="{{ route('pre_orders.index') }}"
                             class="nav-link {{ Request::is('pre-orders') ? 'active' : '' }}">
                             <i class="bi bi-person-plus"></i>
                             <p>Pre-Order</p>
                         </a>
                     </li>
                 @endif
                 @if (auth()->check() && auth()->user()->jabatan === 'Teknisi')
                     <li class="nav-item">
                         <a href="{{ route('customers.index') }}"
                             class="nav-link {{ Request::is('customers') ? 'active' : '' }}">
                             <i class="bi bi-person-check"></i>
                             <p>Aktivasi</p>
                         </a>
                     </li>
                 @endif
                 {{-- Tautan untuk Sales dan Administrator (Lihat Daftar Pelanggan Aktif) --}}
                 @if (auth()->check() && (auth()->user()->jabatan === 'Administrator' || auth()->user()->jabatan === 'Sales'))
                     <li class="nav-item">
                         <a href="{{ route('customers.active_list') }}"
                             class="nav-link {{ Request::is('customers/active-list') ? 'active' : '' }}">
                             <i class="bi bi-person-check"></i>
                             <p>Aktivasi</p>
                         </a>
                     </li>
                 @endif
                 @if (auth()->check() && (auth()->user()->jabatan === 'Administrator' || auth()->user()->jabatan === 'Teknisi'))
                     <li class="nav-item">
                         <a href="{{ route('maintenances.index') }}"
                             class="nav-link {{ Request::is('maintenances') ? 'active' : '' }}">
                             <i class="bi bi-person-gear"></i>
                             <p>Pemeliharaan</p>
                         </a>
                     </li>
                     <li class="nav-item">
                         <a href="{{ route('dismantles.index') }}"
                             class="nav-link {{ Request::is('dismantles') ? 'active' : '' }}">
                             <i class="bi bi-person-x"></i>
                             <p>Dismantle</p>
                         </a>
                     </li>
                 @endif
                 @if (auth()->check() && auth()->user()->jabatan === 'Administrator')
                     <li class="nav-header">MANAJEMEN</li>
                     <li class="nav-item">
                         <a href="{{ route('accounts.index') }}"
                             class="nav-link {{ Request::is('accounts') ? 'active' : '' }}">
                             <i class="bi bi-people"></i>
                             <p>Akun</p>
                         </a>
                     </li>
                     <li class="nav-item">
                         <a href="{{ route('accounts.tracking') }}"
                             class="nav-link {{ Request::is('tracking') ? 'active' : '' }}">
                             <i class="bi bi-geo-alt"></i>
                             <p>Lacak Akun</p>
                         </a>
                     </li>
                 @endif
                 @if (auth()->check() && auth()->user()->jabatan === 'Administrator')
                     <li class="nav-item">
                         <a href="{{ route('activitylog.index') }}"
                             class="nav-link {{ Request::is('activity-log') ? 'active' : '' }}">
                             <i class="bi bi-activity"></i>
                             <p>Aktivitas Log</p>
                         </a>
                     </li>
                     <li class="nav-header">LAPORAN</li>
                     <li class="nav-item">
                         <a href="{{ route('activitylog.aktivasi') }}"
                             class="nav-link {{ Request::is('laporan/aktivasi') ? 'active' : '' }}">
                             <i class="bi bi-person-check-fill"></i>
                             <p>Aktivasi</p>
                         </a>
                     </li>
                     <li class="nav-item">
                         <a href="{{ route('activitylog.maintenance') }}"
                             class="nav-link {{ Request::is('laporan/maintenance') ? 'active' : '' }}">
                             <i class="bi bi-person-fill-gear"></i>
                             <p>Pemeliharaan</p>
                         </a>
                     </li>
                     <li class="nav-item">
                         <a href="{{ route('activitylog.dismantle') }}"
                             class="nav-link {{ Request::is('laporan/dismantle') ? 'active' : '' }}">
                             <i class="bi bi-person-fill-x"></i>
                             <p>Dismantle</p>
                         </a>
                     </li>
                     <li class="nav-item">
                         <a href="{{ route('activitylog.expired') }}"
                             class="nav-link {{ Request::is('laporan/expired') ? 'active' : '' }}">
                             <i class="bi bi-person-fill-add"></i>
                             <p>Pre-Order</p>
                         </a>
                     </li>
                     <li class="nav-item">
                         <a href="{{ route('activitylog.expired') }}"
                             class="nav-link {{ Request::is('laporan/expired') ? 'active' : '' }}">
                             <i class="bi bi-clock-history"></i>
                             <p>PO Expired</p>
                         </a>
                     </li>
                     <li class="nav-item">
                         <a href="{{ route('activitylog.maintenance_expired') }}"
                             class="nav-link {{ Request::is('laporan/maintenance-expired') ? 'active' : '' }}">
                             <i class="bi bi-clock-history"></i>
                             <p>MT Expired</p>
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
