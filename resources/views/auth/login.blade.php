<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login | ERLANGGA NET</title>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-br from-primary to-primary-darker items-center justify-center text-white text-3xl shadow-lg mb-4">
                <i class="fa-solid fa-microchip"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-900">Erlangga<span class="text-primary">Net</span></h1>
            <p class="text-slate-500 text-sm font-medium">Sistem Informasi Layanan Internet</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-[20px] shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-slate-200/50 overflow-hidden">
            <div class="p-8">
                <h2 class="text-xl font-extrabold text-slate-900 mb-6 text-center">Masuk ke Panel</h2>
                
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Email Adress</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="email" name="email" class="form-control !pl-11" placeholder="nama@email.com" required autocomplete="off">
                        </div>
                        @error('email')
                            <p class="text-rose-500 text-[11px] mt-1 font-bold italic">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="password" name="password" class="form-control !pl-11" placeholder="••••••••" required>
                        </div>
                        @error('password')
                            <p class="text-rose-500 text-[11px] mt-1 font-bold italic">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-center py-2">
                        <div class="turnstile-wrapper">
                            <div class="cf-turnstile"
                                data-sitekey="{{ config('services.turnstile.site_key') }}"
                                data-theme="light">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full btn-primary-admin !py-3 !text-sm !rounded-xl">
                        Sign In Sekarang <i class="fa-solid fa-arrow-right ml-2"></i>
                    </button>
                </form>
            </div>
            
            <div class="bg-slate-50 p-4 border-t border-slate-100 text-center">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                    v1.0.0 • Powered by PT. BBS @ {{ date('Y') }}
                </p>
            </div>
        </div>

        <div class="mt-8 text-center text-slate-400 text-xs font-medium">
            <a href="#" class="hover:text-primary transition-colors">Lupa Password?</a> • 
            <a href="#" class="hover:text-primary transition-colors">Bantuan</a>
        </div>
    </div>

    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</body>
</html>
