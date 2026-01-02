<!--begin::Head-->

<head>
    <title>BBS NET - Sistem Informasi Pengelolaan Pengguna Layanan Internet</title>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta name="description"
        content="Sistem Informasi BBS NET untuk pengelolaan, pemantauan, dan administrasi pengguna layanan internet secara terpusat, efisien, dan mudah.">
    <meta name="keywords"
        content="BBS NET, sistem informasi, pengelolaan pengguna, layanan internet, administrasi jaringan, billing internet, monitoring pengguna, ISP, manajemen pelanggan">
    <meta name="author" content="Muhammad Luthfi Hakim">
    <meta name="copyright" content="Muhammad Luthfi Hakim">

    <meta property="og:title" content="BBS NET: Sistem Informasi Pengelolaan Pengguna Layanan Internet">
    <meta property="og:description"
        content="Platform terpusat untuk administrasi, monitoring, dan penagihan (billing) pengguna layanan internet Anda.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://app.erlangga.net/">
    <meta property="og:image" content="https://app.erlangga.net/dist/login/images/bg-1.png">
    <meta property="og:image:alt" content="Logo Sistem Informasi BBS NET">
    <meta property="og:site_name" content="BBS NET">
    <meta property="og:locale" content="id_ID">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="BBS NET: Sistem Informasi Pengelolaan Pengguna Layanan Internet">
    <meta name="twitter:description"
        content="Kelola dan pantau seluruh pengguna layanan internet Anda dengan mudah dan efisien menggunakan BBS NET.">
    <meta name="twitter:image" content="https://app.erlangga.net/dist/login/images/bg-1.png">
    <meta name="twitter:image:alt" content="Antarmuka Sistem Informasi BBS NET">

    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
        integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg=" crossorigin="anonymous" />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI=" crossorigin="anonymous" />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.css') }}" />
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css?v=3.2.0') }}">
    <!--end::Required Plugin(AdminLTE)-->
    @yield('Css')
</head>
