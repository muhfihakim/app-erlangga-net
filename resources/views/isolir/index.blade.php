<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Isolir - {{ $companyName }}</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes pulse-subtle {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .95; transform: scale(1.01); }
        }
        .pulse-badge {
            animation: pulse-subtle 2.5s infinite ease-in-out;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-between selection:bg-rose-500 selection:text-white antialiased">

    <!-- Header Bar -->
    <header class="bg-slate-900/90 backdrop-blur-md border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-3 sm:py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('dist/assets/img/logo.png') }}" alt="{{ $companyName }} Logo"
                    class="h-10 sm:h-12 w-auto object-contain drop-shadow-md">
                <div class="hidden sm:block">
                    <h1 class="text-sm font-black tracking-wider text-white leading-none uppercase">{{ $companyName }}</h1>
                    <p class="text-[10px] text-slate-400 font-medium tracking-tight mt-0.5">Customer Care & Billing Information</p>
                </div>
            </div>
            
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $csWhatsapp) }}?text=Halo%20Admin%20{{ urlencode($companyName) }},%20saya%20butuh%20bantuan%20terkait%20layanan%20internet%20saya" 
               target="_blank" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20 text-xs font-bold transition-all shadow-sm">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Bantuan CS</span>
            </a>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-4xl mx-auto px-4 py-8 sm:py-12 w-full flex-grow flex flex-col items-center">

        <!-- Hero Notice Card -->
        <div class="w-full bg-gradient-to-b from-rose-950/70 via-slate-900/90 to-slate-900 border border-rose-500/30 rounded-3xl p-6 sm:p-10 text-center shadow-2xl relative overflow-hidden mb-8">
            <div class="absolute -right-16 -bottom-16 w-56 h-56 bg-rose-600/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-16 -top-16 w-56 h-56 bg-amber-600/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-2xl bg-rose-500/15 border border-rose-500/30 flex items-center justify-center text-rose-400 text-2xl sm:text-3xl mb-5 shadow-inner pulse-badge">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <h2 class="text-xl sm:text-3xl font-black text-white tracking-tight uppercase max-w-2xl mx-auto">
                Layanan Internet Ditangguhkan Sementara
            </h2>

            <p class="text-xs sm:text-sm text-slate-300 max-w-xl mx-auto mt-3 leading-relaxed">
                Masa aktif paket internet Anda telah berakhir atau memiliki tagihan yang belum terselesaikan. Silakan lakukan pembayaran tagihan melalui aplikasi resmi <strong>{{ $companyName }}</strong>.
            </p>
        </div>

        @if($pppoe)
            <!-- Customer Information Card (Auto-Detected) -->
            <div class="w-full bg-slate-800/60 border border-slate-700/80 rounded-2xl p-5 mb-8 backdrop-blur-sm shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-700/80 pb-3.5 mb-4">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-user-circle text-blue-400 text-lg"></i>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Rincian Akun Pelanggan</span>
                    </div>
                    @if($clientIp && $clientIp !== '127.0.0.1')
                        <span class="text-[11px] px-2.5 py-1 rounded-md bg-slate-700 text-slate-300 font-mono font-bold">
                            IP: {{ $clientIp }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                    <div class="bg-slate-900/60 p-3.5 rounded-xl border border-slate-700/40">
                        <span class="text-slate-400 block text-[11px]">Nama Pelanggan</span>
                        <span class="font-bold text-white text-sm truncate block mt-0.5">
                            {{ $pppoe->preOrder ? ($pppoe->preOrder->nama_lengkap ?? '-') : $pppoe->username }}
                        </span>
                    </div>

                    <div class="bg-slate-900/60 p-3.5 rounded-xl border border-slate-700/40">
                        <span class="text-slate-400 block text-[11px]">Username PPPoE</span>
                        <span class="font-bold text-blue-300 text-sm font-mono truncate block mt-0.5">
                            {{ $pppoe->username }}
                        </span>
                    </div>

                    <div class="bg-slate-900/60 p-3.5 rounded-xl border border-slate-700/40">
                        <span class="text-slate-400 block text-[11px]">Paket Langganan</span>
                        <span class="font-bold text-emerald-400 text-sm truncate block mt-0.5">
                            {{ $pppoe->paket ? $pppoe->paket->nama_paket : '-' }}
                        </span>
                    </div>

                    <div class="bg-slate-900/60 p-3.5 rounded-xl border border-slate-700/40">
                        <span class="text-slate-400 block text-[11px]">Total Tagihan Belum Bayar</span>
                        <span class="font-bold text-rose-400 text-sm block mt-0.5">
                            @if($unpaidInvoices->isNotEmpty())
                                Rp {{ number_format($unpaidInvoices->sum('total'), 0, ',', '.') }}
                            @else
                                Menunggu Tagihan
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Mobile App Download & Payment Banner -->
        <div class="w-full bg-gradient-to-b from-slate-800/90 to-slate-900/95 border border-slate-700/80 rounded-3xl p-6 sm:p-8 text-center shadow-2xl mb-8">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-blue-500/15 border border-blue-500/30 flex items-center justify-center text-blue-400 text-xl mb-4">
                <i class="fa-solid fa-mobile-screen-button"></i>
            </div>

            <h3 class="text-lg sm:text-xl font-black text-white tracking-tight">
                Bayar Mudah & Cepat Melalui Aplikasi
            </h3>
            <p class="text-xs sm:text-sm text-slate-300 max-w-md mx-auto mt-2 leading-relaxed">
                Unduh aplikasi <strong>{{ $companyName }}</strong> sekarang untuk melakukan pembayaran instan via Transfer Virtual Account, QRIS, maupun e-Wallet.
            </p>

            <!-- Store Download Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-6">
                <!-- Google Play Store Button -->
                <a href="#" 
                   class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-slate-950 hover:bg-slate-800 border border-slate-700 text-white flex items-center justify-center gap-3 transition-all transform hover:-translate-y-0.5 shadow-lg group">
                    <i class="fa-brands fa-google-play text-2xl text-emerald-400 group-hover:scale-110 transition-transform"></i>
                    <div class="text-left leading-tight">
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-semibold">TEMUKAN DI</span>
                        <span class="text-sm font-bold text-white font-sans">Google Play</span>
                    </div>
                </a>

                <!-- Apple App Store Button -->
                <a href="#" 
                   class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-slate-950 hover:bg-slate-800 border border-slate-700 text-white flex items-center justify-center gap-3 transition-all transform hover:-translate-y-0.5 shadow-lg group">
                    <i class="fa-brands fa-apple text-3xl text-slate-200 group-hover:scale-110 transition-transform"></i>
                    <div class="text-left leading-tight">
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-semibold">Download on the</span>
                        <span class="text-sm font-bold text-white font-sans">App Store</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- 3 Simple Steps Guide -->
        <div class="w-full bg-slate-800/40 border border-slate-700/60 rounded-2xl p-6 text-xs text-slate-300 space-y-4">
            <h4 class="font-bold text-white text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-question text-blue-400"></i>
                Panduan Pembayaran & Buka Isolir Otomatis
            </h4>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700/40">
                    <div class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 font-bold text-xs inline-flex items-center justify-center mb-2">1</div>
                    <h5 class="font-bold text-white text-xs mb-1">Unduh Aplikasi</h5>
                    <p class="text-slate-400 text-[11px] leading-relaxed">Download dan pasang aplikasi {{ $companyName }} di smartphone Anda melalui Play Store atau App Store.</p>
                </div>

                <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700/40">
                    <div class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 font-bold text-xs inline-flex items-center justify-center mb-2">2</div>
                    <h5 class="font-bold text-white text-xs mb-1">Login & Pilih Tagihan</h5>
                    <p class="text-slate-400 text-[11px] leading-relaxed">Masuk ke aplikasi menggunakan Akun PPPoE atau WhatsApp terdaftar, lalu pilih menu pembayaran tagihan.</p>
                </div>

                <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700/40">
                    <div class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 font-bold text-xs inline-flex items-center justify-center mb-2">3</div>
                    <h5 class="font-bold text-white text-xs mb-1">Internet Aktif Kembali</h5>
                    <p class="text-slate-400 text-[11px] leading-relaxed">Setelah pembayaran berhasil, sistem akan otomatis membuka status isolir dan internet kembali normal.</p>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 bg-slate-950 py-6 mt-12">
        <div class="max-w-4xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 text-center sm:text-left">
            <div>
                <p class="font-bold text-slate-400">{{ $companyName }} Internet Service Provider</p>
                <p class="text-[11px] mt-0.5">© {{ date('Y') }} Hak Cipta Dilindungi. Sistem Informasi & Billing Pelanggan.</p>
            </div>
            <div class="flex items-center gap-4">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $csWhatsapp) }}" target="_blank" class="hover:text-emerald-400 transition-colors flex items-center gap-1.5 font-medium">
                    <i class="fa-brands fa-whatsapp text-sm text-emerald-400"></i>
                    <span>Hubungi Layanan Pelanggan (CS)</span>
                </a>
            </div>
        </div>
    </footer>

</body>
</html>
