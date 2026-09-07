<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Layanan - {{ $companyName }}</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Tailwind CSS CDN for standalone customer isolation portal -->
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
        .pulse-btn {
            animation: pulse-subtle 2.5s infinite ease-in-out;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-between selection:bg-rose-500 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="bg-slate-900/80 backdrop-blur-md border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <i class="fa-solid fa-wifi text-white text-base"></i>
                </div>
                <div>
                    <h1 class="text-base font-black tracking-wider text-white leading-none uppercase">{{ $companyName }}</h1>
                    <p class="text-[11px] text-slate-400 font-medium tracking-tight mt-0.5">Customer Care & Billing Portal</p>
                </div>
            </div>
            
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $csWhatsapp) }}?text=Halo%20Admin%20{{ urlencode($companyName) }},%20saya%20butuh%20bantuan%20terkait%20tagihan%20internet%20saya" 
               target="_blank" 
               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20 text-xs font-semibold transition-all">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span class="hidden sm:inline">Bantuan CS</span>
            </a>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-4xl mx-auto px-4 py-6 w-full flex-grow flex flex-col items-center">

        @if(session('success'))
            <div class="w-full mb-6 p-4 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-200 flex items-center gap-3 text-sm">
                <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="w-full mb-6 p-4 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-200 flex items-center gap-3 text-sm">
                <i class="fa-solid fa-circle-exclamation text-rose-400 text-lg"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if($justPaid)
            <div id="paymentSuccessNotice" class="w-full mb-6 p-5 rounded-2xl bg-gradient-to-r from-emerald-950/80 to-slate-900 border border-emerald-500/40 text-emerald-100 shadow-2xl flex flex-col sm:flex-row items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-check-double text-emerald-400 text-2xl animate-bounce"></i>
                </div>
                <div class="flex-grow text-center sm:text-left">
                    <h3 class="text-base font-bold text-white">Pembayaran Sedang Diverifikasi / Berhasil!</h3>
                    <p class="text-xs text-slate-300 mt-1">Sistem kami sedang menyinkronkan status pembayaran Anda dengan router MikroTik. Internet Anda akan aktif kembali secara otomatis dalam hitungan detik.</p>
                </div>
                <a href="{{ route('isolir.index', ['q' => $searchQuery]) }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold whitespace-nowrap transition-colors">
                    <i class="fa-solid fa-rotate-right mr-1.5"></i> Muat Ulang Status
                </a>
            </div>
        @endif

        <!-- Header Status Banner -->
        @if($isIsolated || $unpaidInvoices->isNotEmpty())
            <div class="w-full bg-gradient-to-b from-rose-950/60 to-slate-900/90 border border-rose-500/30 rounded-3xl p-6 sm:p-8 text-center shadow-2xl relative overflow-hidden mb-8">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-rose-600/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 w-48 h-48 bg-amber-600/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-2xl bg-rose-500/15 border border-rose-500/30 flex items-center justify-center text-rose-400 text-2xl sm:text-3xl mb-4 shadow-inner">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                
                <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight uppercase">
                    Layanan Internet Ditangguhkan Sementara
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 max-w-xl mx-auto mt-2 leading-relaxed">
                    Masa aktif layanan internet Anda telah berakhir atau memiliki tagihan tertunggak. Segera lakukan pelunasan untuk mengaktifkan kembali kecepatan dan koneksi internet Anda secara otomatis.
                </p>

                <div class="inline-flex items-center gap-2 mt-4 px-3.5 py-1.5 rounded-full bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-rose-400 animate-ping"></span>
                    Status Akun: Terisolir (Unpaid)
                </div>
            </div>
        @elseif($pppoe && $unpaidInvoices->isEmpty())
            <div class="w-full bg-gradient-to-b from-emerald-950/60 to-slate-900/90 border border-emerald-500/30 rounded-3xl p-6 sm:p-8 text-center shadow-2xl relative overflow-hidden mb-8">
                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-2xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-2xl sm:text-3xl mb-4 shadow-inner">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                    Layanan Internet Anda Aktif
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 max-w-lg mx-auto mt-2 leading-relaxed">
                    Tidak ada tagihan tertunggak pada akun ini. Layanan internet Anda siap digunakan dengan kecepatan optimal.
                </p>
            </div>
        @else
            <div class="w-full bg-gradient-to-b from-slate-800/80 to-slate-900/90 border border-slate-700/60 rounded-3xl p-6 sm:p-8 text-center shadow-2xl relative overflow-hidden mb-8">
                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-2xl bg-blue-500/15 border border-blue-500/30 flex items-center justify-center text-blue-400 text-2xl sm:text-3xl mb-4">
                    <i class="fa-solid fa-id-card-clip"></i>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                    Cek & Bayar Tagihan Internet
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 max-w-lg mx-auto mt-2 leading-relaxed">
                    Masukkan Username PPPoE, Nomor WhatsApp terdaftar, atau Nomor Faktur untuk melihat rincian tagihan Anda.
                </p>
            </div>
        @endif

        <!-- Search Bar -->
        <div class="w-full mb-8">
            <form action="{{ route('isolir.search') }}" method="POST" class="relative flex items-center">
                @csrf
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input type="text" 
                           name="q" 
                           value="{{ $searchQuery }}"
                           placeholder="Cari Username PPPoE / No WhatsApp / No Faktur (cth: INV-2026...)..." 
                           class="w-full pl-11 pr-28 py-3.5 bg-slate-800/90 border border-slate-700 rounded-2xl text-sm text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-inner">
                    <button type="submit" 
                            class="absolute right-2 top-2 bottom-2 px-5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition-all shadow-md flex items-center gap-1.5">
                        <span>Cari</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </button>
                </div>
            </form>
            @if($autoDetected)
                <p class="text-[11px] text-slate-400 mt-2 text-center flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-bolt text-amber-400"></i> Akun terdeteksi otomatis dari alamat IP Anda ({{ $clientIp }})
                </p>
            @endif
        </div>

        @if($pppoe)
            <!-- Customer Details Card -->
            <div class="w-full bg-slate-800/60 border border-slate-700/80 rounded-2xl p-5 mb-8 backdrop-blur-sm shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-700/80 pb-3.5 mb-4">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-user-circle text-blue-400 text-lg"></i>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Informasi Pelanggan</span>
                    </div>
                    <span class="text-[11px] px-2.5 py-1 rounded-md bg-slate-700 text-slate-300 font-mono font-bold">
                        IP: {{ $clientIp }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                    <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-700/40">
                        <span class="text-slate-400 block text-[11px]">Nama Pelanggan</span>
                        <span class="font-bold text-white text-sm truncate block mt-0.5">
                            {{ $pppoe->preOrder ? ($pppoe->preOrder->nama_lengkap ?? '-') : $pppoe->username }}
                        </span>
                    </div>

                    <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-700/40">
                        <span class="text-slate-400 block text-[11px]">Username PPPoE</span>
                        <span class="font-bold text-blue-300 text-sm font-mono truncate block mt-0.5">
                            {{ $pppoe->username }}
                        </span>
                    </div>

                    <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-700/40">
                        <span class="text-slate-400 block text-[11px]">Paket Langganan</span>
                        <span class="font-bold text-emerald-400 text-sm truncate block mt-0.5">
                            {{ $pppoe->paket ? $pppoe->paket->nama_paket : '-' }}
                        </span>
                    </div>

                    <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-700/40">
                        <span class="text-slate-400 block text-[11px]">WhatsApp Terdaftar</span>
                        <span class="font-bold text-white text-sm font-mono truncate block mt-0.5">
                            {{ $pppoe->preOrder && $pppoe->preOrder->no_whatsapp ? $pppoe->preOrder->no_whatsapp : '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Invoices Section -->
            <div class="w-full">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-emerald-400"></i>
                        Daftar Tagihan Internet
                    </h3>
                    @if($unpaidInvoices->isNotEmpty())
                        <span class="px-2.5 py-0.5 rounded-full bg-rose-500/20 border border-rose-500/30 text-rose-400 text-xs font-bold">
                            {{ $unpaidInvoices->count() }} Belum Dibayar
                        </span>
                    @endif
                </div>

                @if($pppoe->invoices->isEmpty())
                    <div class="bg-slate-800/40 border border-slate-700/60 rounded-2xl p-8 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-receipt text-2xl mb-2 text-slate-500"></i>
                        <p>Belum ada riwayat tagihan untuk akun ini.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($pppoe->invoices as $invoice)
                            @php
                                $isUnpaid = $invoice->status === 'unpaid';
                                $due = $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date) : null;
                                $isOverdue = $isUnpaid && $due && $due->isPast() && !$due->isToday();
                            @endphp
                            <div class="bg-slate-800/80 border {{ $isUnpaid ? 'border-rose-500/40 shadow-rose-950/20' : 'border-slate-700/60' }} rounded-2xl p-5 shadow-lg relative overflow-hidden transition-all">
                                @if($isUnpaid)
                                    <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-rose-500"></div>
                                @else
                                    <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-emerald-500"></div>
                                @endif

                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                    <!-- Left: Invoice metadata -->
                                    <div class="space-y-1 pl-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-mono font-bold text-white text-sm">{{ $invoice->invoice_number }}</span>
                                            
                                            @if($isUnpaid)
                                                <span class="px-2 py-0.5 rounded-md bg-rose-500/20 text-rose-400 border border-rose-500/30 text-[11px] font-bold">
                                                    BELUM DIBAYAR
                                                </span>
                                                @if($isOverdue)
                                                    <span class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[11px] font-semibold">
                                                        <i class="fa-solid fa-clock-rotate-left mr-1"></i> Lewat Jatuh Tempo
                                                    </span>
                                                @endif
                                            @else
                                                <span class="px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[11px] font-bold">
                                                    <i class="fa-solid fa-circle-check mr-1"></i> LUNAS
                                                </span>
                                                @if($invoice->payment_method)
                                                    <span class="text-[11px] text-slate-400 font-mono">({{ $invoice->payment_method }})</span>
                                                @endif
                                            @endif
                                        </div>

                                        <p class="text-xs text-slate-400">
                                            Jatuh Tempo: <span class="text-slate-200 font-medium">{{ $due ? $due->translatedFormat('d F Y') : '-' }}</span>
                                            <span class="mx-1.5 text-slate-600">•</span>
                                            Diterbitkan: <span class="text-slate-300">{{ $invoice->created_at->translatedFormat('d M Y') }}</span>
                                        </p>

                                        <!-- Price breakdown snippet -->
                                        <div class="text-[11px] text-slate-400 flex flex-wrap gap-x-3 pt-1">
                                            <span>Paket: Rp {{ number_format($invoice->amount, 0, ',', '.') }}</span>
                                            @if($invoice->tax > 0)
                                                <span class="text-slate-400">+ PPN: Rp {{ number_format($invoice->tax, 0, ',', '.') }}</span>
                                            @endif
                                            @if($invoice->discount > 0)
                                                <span class="text-emerald-400">- Diskon: Rp {{ number_format($invoice->discount, 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Right: Total & Action Button -->
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between lg:justify-end gap-3 pl-2 lg:pl-0 border-t lg:border-t-0 border-slate-700/60 pt-3 lg:pt-0">
                                        <div class="text-left sm:text-right">
                                            <span class="text-[10px] uppercase tracking-wider text-slate-400 block font-semibold">Total Tagihan</span>
                                            <span class="text-lg sm:text-xl font-black text-white">
                                                Rp {{ number_format($invoice->total, 0, ',', '.') }}
                                            </span>
                                        </div>

                                        @if($isUnpaid)
                                            <form action="{{ route('isolir.pay', $invoice->id) }}" method="POST" class="w-full sm:w-auto">
                                                @csrf
                                                <button type="submit" 
                                                        class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-xs sm:text-sm shadow-lg shadow-blue-600/30 flex items-center justify-center gap-2 pulse-btn transition-all">
                                                    <i class="fa-solid fa-credit-card"></i>
                                                    <span>Bayar Sekarang</span>
                                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="px-4 py-2 rounded-xl bg-slate-700/50 text-emerald-400 text-xs font-semibold flex items-center gap-1.5">
                                                <i class="fa-solid fa-check"></i> Sudah Dibayar
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if($isUnpaid)
                                    <!-- Payment Channels Supported -->
                                    <div class="mt-4 pt-3 border-t border-slate-700/50 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-shield-halved text-blue-400"></i> Pembayaran Instan & Otomatis via Xendit
                                        </span>
                                        <span class="text-slate-300 font-medium">
                                            BCA • BRI • Mandiri • BNI • QRIS • GoPay • OVO • ShopeePay • Indomaret/Alfamart
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            @if(!empty($searchQuery))
                <div class="w-full bg-slate-800/40 border border-slate-700/60 rounded-2xl p-8 text-center text-slate-400">
                    <div class="w-12 h-12 rounded-full bg-slate-700/50 flex items-center justify-center mx-auto mb-3 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-lg"></i>
                    </div>
                    <h4 class="text-white font-bold text-sm">Akun Tidak Ditemukan</h4>
                    <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                        Tidak ditemukan data pelanggan dengan kata kunci "<span class="text-slate-200 font-mono">{{ $searchQuery }}</span>". Pastikan Username atau Nomor WhatsApp yang dimasukkan sudah benar.
                    </p>
                </div>
            @endif
        @endif

        <!-- FAQ / Instructions Card -->
        <div class="w-full mt-10 bg-slate-800/40 border border-slate-700/60 rounded-2xl p-5 text-xs text-slate-300 space-y-3">
            <h4 class="font-bold text-white text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-question text-blue-400"></i>
                Cara Pembayaran & Re-Aktivasi Otomatis
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                <div class="bg-slate-900/40 p-3 rounded-xl border border-slate-700/30">
                    <span class="w-5 h-5 rounded-full bg-blue-500/20 text-blue-400 font-bold text-[10px] inline-flex items-center justify-center mb-1.5">1</span>
                    <h5 class="font-bold text-white mb-1">Klik Bayar Sekarang</h5>
                    <p class="text-slate-400 text-[11px]">Pilih metode bayar favorit Anda (Transfer Bank Virtual Account, QRIS, e-Wallet, atau Gerai Retail).</p>
                </div>
                <div class="bg-slate-900/40 p-3 rounded-xl border border-slate-700/30">
                    <span class="w-5 h-5 rounded-full bg-blue-500/20 text-blue-400 font-bold text-[10px] inline-flex items-center justify-center mb-1.5">2</span>
                    <h5 class="font-bold text-white mb-1">Lakukan Pembayaran</h5>
                    <p class="text-slate-400 text-[11px]">Selesaikan transaksi sesuai nominal dan instruksi yang tertera di layar checkout Xendit.</p>
                </div>
                <div class="bg-slate-900/40 p-3 rounded-xl border border-slate-700/30">
                    <span class="w-5 h-5 rounded-full bg-blue-500/20 text-blue-400 font-bold text-[10px] inline-flex items-center justify-center mb-1.5">3</span>
                    <h5 class="font-bold text-white mb-1">Internet Aktif Kembali</h5>
                    <p class="text-slate-400 text-[11px]">Sistem secara otomatis membuka isolir dan memulihkan kecepatan internet Anda dalam 10-30 detik.</p>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 bg-slate-950 py-6 mt-12">
        <div class="max-w-4xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 text-center sm:text-left">
            <div>
                <p class="font-bold text-slate-400">{{ $companyName }} Internet Service Provider</p>
                <p class="text-[11px] mt-0.5">© {{ date('Y') }} Hak Cipta Dilindungi. Layanan Billing Otomatis.</p>
            </div>
            <div class="flex items-center gap-4">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $csWhatsapp) }}" target="_blank" class="hover:text-emerald-400 transition-colors flex items-center gap-1.5">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>WhatsApp CS</span>
                </a>
            </div>
        </div>
    </footer>

    <!-- Polling Script for Unpaid Invoices -->
    @if($pppoe && $unpaidInvoices->isNotEmpty())
        <script>
            (function() {
                const unpaidInvoiceIds = @json($unpaidInvoices->pluck('id'));
                if (!unpaidInvoiceIds || unpaidInvoiceIds.length === 0) return;

                let pollInterval = setInterval(() => {
                    unpaidInvoiceIds.forEach(id => {
                        fetch(`/isolir/${id}/status`, {
                            headers: { 'Accept': 'application/json' }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.is_paid) {
                                clearInterval(pollInterval);
                                // Reload page to show active status
                                window.location.href = "{{ route('isolir.index', ['q' => $pppoe->username, 'paid' => 1]) }}";
                            }
                        })
                        .catch(err => console.debug('Polling check...', err));
                    });
                }, 5000);
            })();
        </script>
    @endif

</body>
</html>
