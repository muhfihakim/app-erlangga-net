<!DOCTYPE html>
<html lang="id">

<head>
    <title>Layanan WhatsApp Pemprov Papua Tengah</title>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta name="description"
        content="Layanan resmi WhatsApp Pemerintah Provinsi Papua Tengah untuk pengaduan, informasi, dan permohonan layanan publik secara cepat dan mudah.">
    <meta name="keywords"
        content="Layanan WhatsApp, Pemprov Papua Tengah, pemerintah, aduan, pengaduan, informasi publik, layanan masyarakat, Papua Tengah">
    <meta name="author" content="Pemerintah Provinsi Papua Tengah">
    <meta name="copyright" content="Pemerintah Provinsi Papua Tengah">

    <meta property="og:title" content="Layanan WhatsApp Pemprov Papua Tengah">
    <meta property="og:description"
        content="Layanan resmi WhatsApp Pemerintah Provinsi Papua Tengah untuk pengaduan, informasi, dan permohonan layanan publik secara cepat dan mudah.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="#">
    <meta property="og:image" content="#">
    <meta property="og:image:alt" content="Layanan WhatsApp Pemerintah Provinsi Papua Tengah">
    <meta property="og:site_name" content="Pemerintah Provinsi Papua Tengah">
    <meta property="og:locale" content="id_ID">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Layanan WhatsApp Pemprov Papua Tengah">
    <meta name="twitter:description"
        content="Layanan resmi WhatsApp Pemerintah Provinsi Papua Tengah untuk pengaduan, informasi, dan permohonan layanan publik secara cepat dan mudah.">
    <meta name="twitter:image" content="#">
    <meta name="twitter:image:alt" content="Layanan WhatsApp Pemerintah Provinsi Papua Tengah">

    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('dist/css/login.css') }}" />
</head>

<body>
    <div class="container" id="container">
        <div class="form-container sign-in">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <!-- Logo -->
                <div class="logo-container">
                    <img src="{{ asset('logo.png') }}" alt="Logo Pemerintah Provinsi Papua Tengah"
                        style="
                max-width: 70px;
                height: 80px;
                display: block;
                margin: 0 auto;
              " />
                </div>
                <br />
                <h4>Pemerintah Provinsi Papua Tengah</h4>
                <br />

                <input type="email" name="email" placeholder="Email" autocomplete="off" />
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <input type="password" name="password" placeholder="Password" autocomplete="off" />
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <!-- <a href="">Forgot your email or password?</a> -->
                <button type="submit">Sign in</button>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>Enter your personal details to use all of site features</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Selamat Datang!</h1>
                    <p>Layanan Aduan dan Pelaporan Pemerintah Provinsi Papua Tengah</p>
                    <!-- <button class="hidden" id="register">Sign Up</button> -->
                </div>
            </div>
        </div>
    </div>
    <script src="./js/script.js"></script>
