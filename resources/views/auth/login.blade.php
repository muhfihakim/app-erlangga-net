<!DOCTYPE html>
<html lang="id">

<head>
    <title>ERLANGGA NET - Sistem Informasi Pengelolaan Pengguna Layanan Internet</title>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta name="description"
        content="Sistem Informasi ERLANGGA NET untuk pengelolaan, pemantauan, dan administrasi pengguna layanan internet secara terpusat, efisien, dan mudah.">
    <meta name="keywords"
        content="ERLANGGA NET, sistem informasi, pengelolaan pengguna, layanan internet, administrasi jaringan, billing internet, monitoring pengguna, ISP, manajemen pelanggan">
    <meta name="author" content="Muhammad Luthfi Hakim">
    <meta name="copyright" content="Muhammad Luthfi Hakim">

    <meta property="og:title" content="ERLANGGA NET: Sistem Informasi Pengelolaan Pengguna Layanan Internet">
    <meta property="og:description"
        content="Platform terpusat untuk administrasi, monitoring, dan penagihan (billing) pengguna layanan internet Anda.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://app.erlangga.net">
    <meta property="og:image" content="https://app.erlangga.net/dist/login/images/bg-1.png">
    <meta property="og:image:alt" content="Logo Sistem Informasi ERLANGGA NET">
    <meta property="og:site_name" content="ERLANGGA NET">
    <meta property="og:locale" content="id_ID">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="ERLANGGA NET: Sistem Informasi Pengelolaan Pengguna Layanan Internet">
    <meta name="twitter:description"
        content="Kelola dan pantau seluruh pengguna layanan internet Anda dengan mudah dan efisien menggunakan ERLANGGA NET.">
    <meta name="twitter:image" content="https://app.erlangga.net/dist/login/images/bg-1.png">
    <meta name="twitter:image:alt" content="Antarmuka Sistem Informasi ERLANGGA NET">

    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="NGOPI" />
    <link rel="manifest" href="/site.webmanifest" />

    <link rel="stylesheet" href="{{ asset('dist/login/css/style.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>

<body>
    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-5">
                    <div class="wrap">
                        <div class="img" style="background-image: url({{ asset('dist/login/images/bg-1.png') }})">
                        </div>
                        <div class="login-wrap p-4 p-md-5">
                            <div class="d-flex">
                                <div class="w-100">
                                    <h3 style="text-align: center" class="mb-4">Login | Erlangga Net</h3>
                                </div>
                            </div>
                            <form method="POST" class="signin-form" action="{{ route('login') }}">
                                @csrf
                                <div class="form-group mt-3">

                                    <input class="form-control" type="email" name="email"
                                        placeholder="Masukkan Email Anda" autocomplete="off" required>
                                    @error('email')
                                        <span role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <input class="form-control" type="password" name="password"
                                        placeholder="Masukkan Password Anda" autocomplete="off" required>
                                    @error('password')
                                        <span role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                                <div style="text-align: center" class="form-group">
                                    <div class="turnstile-wrapper">
                                        <div class="cf-turnstile"
                                            data-sitekey="{{ config('services.turnstile.site_key') }}"
                                            data-theme="light">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="form-control btn btn-primary rounded submit px-3">
                                        Sign In
                                    </button>
                                </div>
                            </form>
                            <p class="text-center">
                                v0.2-beta | <span style="text-align: center">Powered by PT. BBS @
                                    {{ date('Y') }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>


<script src="{{ asset('dist/login/js/jquery.min.js') }}"></script>
<script src="{{ asset('dist/login/js/popper.js') }}"></script>
<script src="{{ asset('dist/login/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('dist/login/js/main.js') }}"></script>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

</html>
