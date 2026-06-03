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
            max-width: 400px;
        }

        /* ── BRAND ───────────────────────────────────────────── */
        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
        }

        .brand-mark {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: var(--navy);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            position: relative;
        }

        /* Gold corner accent */
        .brand-mark::after {
            content: '';
            position: absolute;
            bottom: 4px;
            right: 4px;
            width: 10px;
            height: 10px;
            border-radius: 3px;
            background: var(--gold);
        }

        .brand-mark i {
            font-size: 22px;
            color: #fff;
            position: relative;
            z-index: 1;
        }

        .brand-name {
            font-size: 20px;
            font-weight: 600;
            letter-spacing: -.3px;
            color: var(--text-main);
            line-height: 1;
            margin-bottom: 4px;
        }

        .brand-name em {
            font-style: normal;
            color: var(--blue);
        }

        .brand-tagline {
            font-size: 11.5px;
            color: var(--text-faint);
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        /* ── CARD ────────────────────────────────────────────── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 18px;
            overflow: hidden;
        }

        .card-head {
            padding: 28px 32px 0;
            text-align: center;
        }

        .card-head-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .card-head-sub {
            font-size: 12px;
            color: var(--text-faint);
        }

        .card-body {
            padding: 24px 32px 28px;
        }

        /* ── FORM ELEMENTS ───────────────────────────────────── */
        .field {
            margin-bottom: 16px;
        }

        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 6px;
            letter-spacing: .1px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            color: var(--text-faint);
            pointer-events: none;
            width: 16px;
            text-align: center;
        }

        .field-input {
            width: 100%;
            height: 42px;
            padding: 0 42px 0 38px;
            background: #f8f9fb;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: inherit;
            font-size: 13.5px;
            color: var(--text-main);
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
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
            box-shadow: 0 0 0 3px rgba(29, 94, 160, 0.1);
        }

        .field-input.is-invalid {
            border-color: var(--danger);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
            background: var(--danger-lt);
        }

        .eye-toggle {
            position: absolute;
            right: 11px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            color: var(--text-faint);
            font-size: 13px;
            line-height: 1;
            display: flex;
            align-items: center;
            transition: color .15s;
        }

        .eye-toggle:hover {
            color: var(--text-muted);
        }

        .field-error {
            display: none;
            font-size: 11px;
            color: var(--danger);
            font-style: italic;
            margin-top: 5px;
            font-weight: 500;
        }

        .field-error.show {
            display: block;
        }

        /* ── TURNSTILE ───────────────────────────────────────── */
        .turnstile-row {
            display: flex;
            justify-content: center;
            margin: 18px 0;
            padding: 12px;
            background: #f8f9fb;
            border: 1px solid var(--border);
            border-radius: 10px;
            min-height: 68px;
            align-items: center;
        }

        /* ── SUBMIT BUTTON ───────────────────────────────────── */
        .btn-submit {
            width: 100%;
            height: 44px;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: .2px;
            transition: background .15s ease, transform .1s ease;
        }

        .btn-submit:hover {
            background: var(--navy-mid);
        }

        .btn-submit:active {
            transform: scale(0.99);
        }

        .btn-submit i {
            font-size: 12px;
            transition: transform .2s;
        }

        .btn-submit:hover i {
            transform: translateX(2px);
        }

        /* ── CARD FOOTER ─────────────────────────────────────── */
        .card-foot {
            border-top: 1px solid var(--border);
            padding: 13px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafbfc;
        }

        .foot-ver {
            font-size: 10.5px;
            color: var(--text-faint);
            font-family: 'DM Mono', monospace;
            letter-spacing: .2px;
        }

        .foot-links {
            display: flex;
            gap: 16px;
        }

        .foot-link {
            font-size: 11px;
            color: var(--text-faint);
            text-decoration: none;
            transition: color .15s;
        }

        .foot-link:hover {
            color: var(--blue);
        }

        /* ── TOAST ───────────────────────────────────────────── */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
            pointer-events: none;
        }

        .custom-toast {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            min-width: 240px;
            max-width: 320px;
            pointer-events: all;
            animation: slideUp .2s ease;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .06);
        }

        .custom-toast.out {
            animation: slideDown .2s ease forwards;
        }

        .toast-icon {
            font-size: 15px;
            margin-top: 1px;
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

        .toast-content {}

        .toast-title {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-main);
            line-height: 1;
            margin-bottom: 2px;
        }

        .toast-message {
            font-size: 11.5px;
            color: var(--text-muted);
            line-height: 1.4;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(8px);
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
                transform: translateY(8px);
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="shell">

            {{-- BRAND --}}
            <div class="brand">
                <div class="brand-mark">
                    <i class="fa-solid fa-microchip"></i>
                </div>
                <div class="brand-name">Erlangga<em>Net</em></div>
                <div class="brand-tagline">Sistem Informasi Layanan Internet</div>
            </div>

            {{-- CARD --}}
            <div class="card">

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

                        <button type="submit" class="btn-submit">
                            Sign In Sekarang <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>
                </div>

                <div class="card-foot">
                    <span class="foot-ver">v1.0.0 · PT. BBS © {{ date('Y') }}</span>
                    <div class="foot-links">
                        <a href="#" class="foot-link">Lupa Password?</a>
                        <a href="#" class="foot-link">Bantuan</a>
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
                if (form.checkValidity()) return; // Let Laravel handle valid submits

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
