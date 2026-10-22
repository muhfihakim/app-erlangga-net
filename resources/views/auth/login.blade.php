<!DOCTYPE html>
<html lang="id">

<head>
    <title>Login | ERLANGGA NET</title>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --navy: #0d1f35;
            --navy-mid: #1a3555;
            --blue: #1d5ea0;
            --blue-lt: #e8f1fb;
            --gold: #c8a45a;
            --gold-lt: #f8f1e4;
            --text-main: #1e2533;
            --text-muted: #6b7280;
            --text-faint: #9ca3af;
            --bg-page: #f0f3f8;
            --bg-card: #ffffff;
            --border: #e2e6ed;
            --border-focus: #1d5ea0;
            --danger: #dc2626;
            --danger-lt: #fef2f2;
        }

        html,
        body {
            height: 100%;
            font-family: 'DM Sans', 'Helvetica Neue', sans-serif;
            font-size: 14px;
            color: var(--text-main);
            background: var(--bg-page);
            -webkit-font-smoothing: antialiased;
        }

        /* ── LAYOUT ──────────────────────────────────────────── */
        .page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem 1rem;
        }

        .shell {
            width: 100%;
            max-width: 850px;
        }

        /* ── CARD ────────────────────────────────────────────── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 18px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 768px) {
            .card {
                grid-template-columns: 1fr;
            }

            .card-widgets {
                display: none !important;
            }

            .shell {
                max-width: 400px;
            }
        }

        .card-left {
            display: flex;
            flex-direction: column;
        }

        .card-head {
            padding: 32px 32px 0;
            text-align: center;
        }

        .card-head-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .card-head-sub {
            font-size: 13px;
            color: var(--text-faint);
        }

        .card-body {
            padding: 28px 32px;
            flex: 1;
        }

        /* ── FORM ELEMENTS ───────────────────────────────────── */
        .field {
            margin-bottom: 18px;
        }

        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
            letter-spacing: .1px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: var(--text-faint);
            pointer-events: none;
            width: 16px;
            text-align: center;
        }

        .field-input {
            width: 100%;
            height: 44px;
            padding: 0 42px 0 40px;
            background: #f8f9fb;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: inherit;
            font-size: 13.5px;
            color: var(--text-main);
            outline: none;
            transition: all .2s ease;
        }

        .field-input::placeholder {
            color: var(--text-faint);
        }

        .field-input:hover:not(:focus) {
            border-color: #c8cdd6;
        }

        .field-input:focus {
            background: var(--bg-card);
            border-color: var(--border-focus);
            box-shadow: 0 0 0 4px rgba(29, 94, 160, 0.1);
        }

        .field-input.is-invalid {
            border-color: var(--danger);
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.08);
            background: var(--danger-lt);
        }

        .eye-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            color: var(--text-faint);
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: color .2s;
        }

        .eye-toggle:hover {
            color: var(--text-muted);
        }

        .field-error {
            display: none;
            font-size: 11.5px;
            color: var(--danger);
            font-style: italic;
            margin-top: 6px;
            font-weight: 500;
        }

        .field-error.show {
            display: block;
        }

        /* ── TURNSTILE ───────────────────────────────────────── */
        .turnstile-row {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            padding: 12px;
            background: #f8f9fb;
            border: 1px dashed var(--border);
            border-radius: 10px;
            min-height: 68px;
            align-items: center;
        }

        /* ── SUBMIT BUTTON ───────────────────────────────────── */
        .btn-submit {
            width: 100%;
            height: 46px;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: .2px;
            transition: all .2s ease;
        }

        .btn-submit:hover:not(.loading) {
            background: var(--navy-mid);
            box-shadow: 0 4px 12px rgba(13, 31, 53, 0.2);
        }

        .btn-submit:active:not(.loading) {
            transform: scale(0.98);
        }

        .btn-submit i {
            font-size: 13px;
            transition: transform .2s;
        }

        .btn-submit:hover:not(.loading) i.fa-arrow-right {
            transform: translateX(4px);
        }

        .btn-submit.loading {
            pointer-events: none;
            opacity: 0.85;
            background: var(--navy-mid);
        }

        .btn-submit .fa-spinner {
            display: none;
            animation: spinBtn 1s linear infinite;
        }

        .btn-submit.loading .fa-arrow-right {
            display: none;
        }

        .btn-submit.loading .fa-spinner {
            display: inline-block;
        }

        @keyframes spinBtn {
            100% {
                transform: rotate(360deg);
            }
        }

        /* ── CARD FOOTER ─────────────────────────────────────── */
        .card-foot {
            border-top: 1px solid var(--border);
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fafbfc;
        }

        .foot-ver {
            font-size: 11px;
            color: var(--text-faint);
            font-family: 'DM Mono', monospace;
            letter-spacing: .5px;
        }

        /* ── WIDGETS RIGHT PANEL ─────────────────────────────── */
        .card-widgets {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 100%);
            padding: 40px 32px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 24px;
            position: relative;
            overflow: hidden;
        }

        .card-widgets::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(200, 164, 90, 0.08) 0%, transparent 60%);
            animation: pulseGlow 6s infinite alternate;
        }

        @keyframes pulseGlow {
            0% {
                transform: scale(1) translate(0, 0);
            }

            100% {
                transform: scale(1.1) translate(10px, 10px);
            }
        }

        .widget-box {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
            transition: transform 0.3s ease;
        }

        .widget-box:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.06);
        }

        .widget-title {
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 0.3px;
        }

        .widget-title i {
            color: var(--gold);
        }

        /* Traffic Bar Chart */
        .bar-chart {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            height: 70px;
        }

        .bar {
            flex: 1;
            height: 100%;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 4px 4px 0 0;
            position: relative;
            overflow: hidden;
        }

        .bar-fill {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: var(--blue-lt);
            border-radius: 4px 4px 0 0;
            animation: growBar 1.5s ease-out infinite alternate;
            transform-origin: bottom;
        }

        .bar:nth-child(1) .bar-fill {
            height: 45%;
            animation-delay: 0s;
            background: #3b82f6;
        }

        .bar:nth-child(2) .bar-fill {
            height: 75%;
            animation-delay: -0.4s;
            background: #60a5fa;
        }

        .bar:nth-child(3) .bar-fill {
            height: 55%;
            animation-delay: -0.8s;
            background: #93c5fd;
        }

        .bar:nth-child(4) .bar-fill {
            height: 90%;
            animation-delay: -1.2s;
            background: var(--gold);
        }

        .bar:nth-child(5) .bar-fill {
            height: 35%;
            animation-delay: -1.6s;
            background: #bfdbfe;
        }

        @keyframes growBar {
            0% {
                transform: scaleY(0.85);
            }

            100% {
                transform: scaleY(1.05);
            }
        }

        /* Status Pie Chart */
        .pie-chart-wrap {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .pie-chart {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: conic-gradient(var(--gold) 0% 75%, rgba(255, 255, 255, 0.1) 75% 100%);
            animation: spinPie 8s linear infinite;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
            position: relative;
            flex-shrink: 0;
        }

        .pie-chart::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 56px;
            height: 56px;
            background: var(--navy-mid);
            border-radius: 50%;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        @keyframes spinPie {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .pie-stats {
            color: rgba(255, 255, 255, 0.85);
            font-size: 12px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .pie-stat {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .pie-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.2);
        }

        .pie-dot.gold {
            background: var(--gold);
        }

        .pie-dot.gray {
            background: rgba(255, 255, 255, 0.2);
        }

        /* ── TOAST ───────────────────────────────────────────── */
        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
        }

        .custom-toast {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            min-width: 260px;
            max-width: 340px;
            pointer-events: all;
            animation: slideUp .3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
        }

        .custom-toast.out {
            animation: slideDown .25s ease-in forwards;
        }

        .toast-icon {
            font-size: 18px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .toast-success .toast-icon {
            color: #15803d;
        }

        .toast-error .toast-icon {
            color: var(--danger);
        }

        .toast-info .toast-icon {
            color: var(--blue);
        }

        .toast-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.1;
            margin-bottom: 4px;
        }

        .toast-message {
            font-size: 12.5px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(15px);
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="shell">

            {{-- CARD --}}
            <div class="card">

                {{-- LEFT: LOGIN FORM --}}
                <div class="card-left">
                    <div class="card-head">
                        <div class="card-head-title">Masuk ke Panel</div>
                        <div class="card-head-sub">Gunakan kredensial akun yang telah diberikan</div>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('login') }}" novalidate id="login-form">
                            @csrf

                            {{-- Email --}}
                            <div class="field">
                                <label class="field-label" for="email">Email Address</label>
                                <div class="input-wrap">
                                    <i class="fa-regular fa-envelope input-icon"></i>
                                    <input type="email" id="email" name="email"
                                        class="field-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                        placeholder="nama@email.com" value="{{ old('email') }}" autocomplete="email"
                                        required>
                                </div>
                                @error('email')
                                    <div class="field-error show">{{ $message }}</div>
                                @enderror
                                <div class="field-error" id="email-js-error"></div>
                            </div>

                            {{-- Password --}}
                            <div class="field">
                                <label class="field-label" for="password">Password</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-lock input-icon"></i>
                                    <input type="password" id="password" name="password"
                                        class="field-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                        placeholder="••••••••" required>
                                    <button type="button" class="eye-toggle" id="eye-toggle"
                                        title="Tampilkan/sembunyikan password">
                                        <i class="fa-regular fa-eye" id="eye-icon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="field-error show">{{ $message }}</div>
                                @enderror
                                <div class="field-error" id="password-js-error"></div>
                            </div>

                            {{-- Cloudflare Turnstile --}}
                            <div class="turnstile-row">
                                <div class="turnstile-wrapper">
                                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"
                                        data-theme="light">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-submit" id="btn-submit">
                                <span>Sign In Sekarang</span>
                                <i class="fa-solid fa-arrow-right"></i>
                                <i class="fa-solid fa-spinner"></i>
                            </button>
                        </form>
                    </div>

                    <div class="card-foot">
                        <span class="foot-ver">v1.2.0 · PT. BBS © {{ date('Y') }}</span>
                    </div>
                </div>

                {{-- RIGHT: WIDGETS --}}
                <div class="card-widgets">

                    {{-- Widget 1: Traffic --}}
                    <div class="widget-box">
                        <div class="widget-title">
                            <i class="fa-solid fa-chart-simple"></i> Live Traffic
                        </div>
                        <div class="bar-chart">
                            <div class="bar">
                                <div class="bar-fill"></div>
                            </div>
                            <div class="bar">
                                <div class="bar-fill"></div>
                            </div>
                            <div class="bar">
                                <div class="bar-fill"></div>
                            </div>
                            <div class="bar">
                                <div class="bar-fill"></div>
                            </div>
                            <div class="bar">
                                <div class="bar-fill"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Widget 2: Status --}}
                    <div class="widget-box">
                        <div class="widget-title">
                            <i class="fa-solid fa-network-wired"></i> Network Status
                        </div>
                        <div class="pie-chart-wrap">
                            <div class="pie-chart"></div>
                            <div class="pie-stats">
                                <div class="pie-stat">
                                    <div class="pie-dot gold"></div> Online (75%)
                                </div>
                                <div class="pie-stat">
                                    <div class="pie-dot gray"></div> Offline (25%)
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>

    {{-- Toast Container --}}
    <div id="toast-container" class="toast-container"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    <script>
        // ── Toast ────────────────────────────────────────────────
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `custom-toast toast-${type}`;

            const icons = {
                success: 'fa-circle-check',
                error: 'fa-circle-xmark',
                info: 'fa-circle-info'
            };
            const titles = {
                success: 'Berhasil',
                error: 'Gagal',
                info: 'Informasi'
            };

            toast.innerHTML = `
            <div class="toast-icon"><i class="fa-solid ${icons[type] || icons.info}"></i></div>
            <div class="toast-content">
                <div class="toast-title">${titles[type] || 'Informasi'}</div>
                <div class="toast-message">${message}</div>
            </div>
        `;

            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.add('out');
                setTimeout(() => toast.remove(), 250);
            }, 4000);
        }

        $(document).ready(function() {

            // ── Server-side flash messages ───────────────────────
            @if (session('error'))
                showToast('{{ session('error') }}', 'error');
            @endif
            @if (session('success'))
                showToast('{{ session('success') }}', 'success');
            @endif

            // ── Auto-Login via LocalStorage (Remember Token) ──
            const appToken = localStorage.getItem('app_token');
            if (appToken) {
                $('#btn-submit').addClass('loading');
                $('#btn-submit span').text('Otentikasi otomatis...');
                
                fetch('/magic-login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + appToken,
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        window.location.href = '/dashboard';
                    } else {
                        localStorage.removeItem('app_token');
                        $('#btn-submit').removeClass('loading');
                        $('#btn-submit span').text('Sign In Sekarang');
                    }
                }).catch(() => {
                    localStorage.removeItem('app_token');
                    $('#btn-submit').removeClass('loading');
                    $('#btn-submit span').text('Sign In Sekarang');
                });
            }

            // ── Password visibility toggle ────────────────────────
            $('#eye-toggle').on('click', function() {
                const input = $('#password');
                const icon = $('#eye-icon');
                const isHidden = input.attr('type') === 'password';
                input.attr('type', isHidden ? 'text' : 'password');
                icon.toggleClass('fa-eye', !isHidden).toggleClass('fa-eye-slash', isHidden);
            });

            // ── Inline error helpers ──────────────────────────────
            function setFieldError(inputId, errorId, message) {
                $('#' + inputId).addClass('is-invalid');
                $('#' + errorId).text(message).addClass('show');
            }

            function clearFieldError(inputId, errorId) {
                $('#' + inputId).removeClass('is-invalid');
                $('#' + errorId).text('').removeClass('show');
            }

            $('#email').on('input', function() {
                clearFieldError('email', 'email-js-error');
            });
            $('#password').on('input', function() {
                clearFieldError('password', 'password-js-error');
            });

            // ── Client-side validation ────────────────────────────
            $('#login-form').on('submit', function(e) {
                const form = this;

                // Jika sedang loading, jangan submit lagi
                if ($('#btn-submit').hasClass('loading')) {
                    e.preventDefault();
                    return;
                }

                if (form.checkValidity()) {
                    // Validasi lolos, tambahkan animasi loading
                    $('#btn-submit').addClass('loading');
                    $('#btn-submit span').text('Memproses...');
                    return; // Biarkan form tersubmit ke server
                }

                e.preventDefault();
                e.stopPropagation();

                const emailInput = $('#email')[0];
                const pwInput = $('#password')[0];
                let toasted = false;

                if (emailInput.validity.valueMissing) {
                    setFieldError('email', 'email-js-error', 'Email wajib diisi.');
                } else if (emailInput.validity.typeMismatch) {
                    setFieldError('email', 'email-js-error', 'Format email tidak valid.');
                }

                if (pwInput.validity.valueMissing) {
                    setFieldError('password', 'password-js-error', 'Password wajib diisi.');
                }

                if (!toasted) {
                    const firstInvalid = $(form).find(':invalid').first();
                    const label = firstInvalid.attr('name') === 'email' ? 'Email' : 'Password';
                    showToast(label + ' belum diisi dengan benar.', 'error');
                    firstInvalid.focus();
                }
            });
        });
    </script>
</body>

</html>
