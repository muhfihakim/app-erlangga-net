<!--begin::Header-->
<nav class="app-header navbar navbar-expand bg-body">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Start Navbar Links-->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">PT. Baraya Bareng Sadulur |
                    Erlangga Network</a>
            </li>
        </ul>
        <!--end::Start Navbar Links-->
        <!--begin::End Navbar Links-->
        <ul class="navbar-nav ms-auto">
            <!--begin::Notifications Dropdown Menu-->
            {{-- <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="bi bi-bell-fill"></i>
                    <span class="navbar-badge badge text-bg-warning">1</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <span class="dropdown-item dropdown-header">1 Notifikasi</span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="bi bi-envelope me-2"></i>Powered by PT. BBS.
                    </a>
                    <div class="dropdown-divider"></div>
                </div>
            </li> --}}
            <!--end::Notifications Dropdown Menu-->
            <!--begin::User Menu Dropdown-->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="{{ asset('dist/assets/img/Foto Profil.png') }}" class="user-image rounded-circle shadow"
                        alt="Logo Erlangga Net" />
                    <span class="d-none d-md-inline">{{ auth()->user()->nama_lengkap ?? 'Pengguna' }}</span> -
                    <small>{{ auth()->user()->jabatan ?? 'Jabatan' }}</small>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <!--begin::User Image-->
                    <li class="user-header text-bg-primary">
                        <img src="{{ asset('dist/assets/img/Foto Profil.png') }}" class="rounded-circle shadow"
                            alt="Logo Erlangga Net" />
                        <p>
                            {{ auth()->user()->nama_lengkap ?? 'Pengguna' }}
                        </p>
                        <small>{{ auth()->user()->jabatan ?? 'Jabatan' }}</small>
                    </li>
                    <!--end::User Image-->
                    <!--begin::Menu Footer-->
                    <li class="user-footer">
                        {{-- <a href="#" class="btn btn-default btn-flat">Profil</a> --}}
                        <!-- Form Logout -->
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-default btn-flat float-end">
                                Logout
                            </button>
                        </form>
                    </li>
                    <!--end::Menu Footer-->
                </ul>
            </li>
            <!--end::User Menu Dropdown-->
        </ul>
        <!--end::End Navbar Links-->
    </div>
    <!--end::Container-->
</nav>
<!--end::Header-->
