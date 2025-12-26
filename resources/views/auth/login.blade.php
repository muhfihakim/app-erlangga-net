<!DOCTYPE html>
<html lang="id">

<head>
    <title>BBS NET - Sistem Informasi Pengelolaan Pengguna Layanan Internet</title>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta name="description"
        content="Sistem Informasi BBS NET untuk pengelolaan, pemantauan, dan administrasi pengguna layanan internet secara terpusat, efisien, dan mudah.">
    <meta name="keywords"
        content="BBS NET, sistem informasi, pengelolaan pengguna, layanan internet, administrasi jaringan, billing internet, monitoring pengguna, ISP, manajemen pelanggan">
    <meta name="author" content="[Nama Organisasi/Pengembang BBS NET]">
    <meta name="copyright" content="[Nama Organisasi/Pengembang BBS NET]">

    <meta property="og:title" content="BBS NET: Sistem Informasi Pengelolaan Pengguna Layanan Internet">
    <meta property="og:description"
        content="Platform terpusat untuk administrasi, monitoring, dan penagihan (billing) pengguna layanan internet Anda.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://mybbs.id/layanan/bbsnet">
    <meta property="og:image" content="https://www.alamy.com/stock-photo/logo-bbs.html?blackwhite=1">
    <meta property="og:image:alt" content="Logo Sistem Informasi BBS NET">
    <meta property="og:site_name" content="BBS NET">
    <meta property="og:locale" content="id_ID">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="BBS NET: Sistem Informasi Pengelolaan Pengguna Layanan Internet">
    <meta name="twitter:description"
        content="Kelola dan pantau seluruh pengguna layanan internet Anda dengan mudah dan efisien menggunakan BBS NET.">
    <meta name="twitter:image" content="https://www.alamy.com/stock-photo/logo-bbs.html?blackwhite=1">
    <meta name="twitter:image:alt" content="Antarmuka Sistem Informasi BBS NET">

    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="NGOPI" />
    <link rel="manifest" href="/site.webmanifest" />

    <link rel="stylesheet" href="{{ asset('dist/css/login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>

<body>
    <div class="container" id="container">

        <div class="form-container sign-up">
            <form method="#">
                <h1 style="text-align: center;">Bantuan</h1>

                <div style="text-align: center; margin-top: 15px;">
                    <p>Jika Anda mengalami <strong>kesulitan atau kendala saat login</strong>, silakan <strong>hubungi
                            Administrator (Admin)</strong> Anda untuk bantuan lebih lanjut.</p>
                </div>
            </form>
        </div>

        <div class="form-container sign-in">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <h1>BBS NET</h1>
                <p>Manage Your Internet Service Provider</p>
                <span></span>
                <input type="email" placeholder="Masukkan Email Anda" name="email" autocomplete="off" required>
                @error('email')
                    <span role="alert">
                        {{ $message }}
                    </span>
                @enderror
                <input type="password" placeholder="Password Anda" name="password" autocomplete="off" required>
                @error('password')
                    <span role="alert">
                        {{ $message }}
                    </span>
                @enderror
                <br>
                <!-- Cloudflare Turnstile -->
                {{-- <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light">
                </div> --}}
                <div class="turnstile-wrapper">
                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"
                        data-theme="light"></div>
                </div>

                <button>MASUK</button>
                <br>
                <span style="text-align: center">Dibuat Oleh Muhammad Luthfi Hakim</span>
                <span style="text-align: center">{{ date('Y') }}</span>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Selamat Datang!</h1>
                    <p>Kembali ke halaman masuk.</p>
                    <button class="hidden" id="login">Masuk</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Selamat Datang!</h1>
                    <p>Silahkan masuk untuk melanjutkan.</p>
                    <button class="hidden" id="register">Bantuan</button>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="{{ asset('dist/js/main.js') }}"></script>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

</html>
