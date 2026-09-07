<x-layouts.app>
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Pengaturan Sistem & Integrasi Server
            </h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0;">
                Kelola konfigurasi parameter MikroTik, OLT, database RADIUS, dan Xendit yang tersimpan aman di
                Database.
            </p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    @php
        $activeTab = request('tab', 'mikrotik');
    @endphp
    <div class="modal-tabs bg-white border border-slate-200 rounded-2xl p-1 mb-6 flex gap-2 flex-wrap shadow-sm">
        <button type="button" class="modal-tab {{ $activeTab === 'mikrotik' ? 'active' : '' }} !rounded-xl !py-2.5 !px-5"
            data-tab="tab-mikrotik">
            <i class="fa-solid fa-microchip mr-2 text-indigo-500"></i> MikroTik RouterOS
        </button>
        <button type="button" class="modal-tab {{ $activeTab === 'olt' ? 'active' : '' }} !rounded-xl !py-2.5 !px-5"
            data-tab="tab-olt">
            <i class="fa-solid fa-server mr-2 text-sky-500"></i> OLT Telnet
        </button>
        <button type="button" class="modal-tab {{ $activeTab === 'radius' ? 'active' : '' }} !rounded-xl !py-2.5 !px-5"
            data-tab="tab-radius">
            <i class="fa-solid fa-database mr-2 text-emerald-500"></i> Server RADIUS
        </button>
        <button type="button" class="modal-tab {{ $activeTab === 'xendit' ? 'active' : '' }} !rounded-xl !py-2.5 !px-5"
            data-tab="tab-xendit">
            <i class="fa-solid fa-credit-card mr-2 text-amber-500"></i> Xendit Payment
        </button>
        <button type="button" class="modal-tab {{ $activeTab === 'whatsapp' ? 'active' : '' }} !rounded-xl !py-2.5 !px-5"
            data-tab="tab-whatsapp">
            <i class="fa-brands fa-whatsapp mr-2 text-emerald-500"></i> WhatsApp API (api.co.id)
        </button>
        <button type="button" class="modal-tab {{ $activeTab === 'genieacs' ? 'active' : '' }} !rounded-xl !py-2.5 !px-5"
            data-tab="tab-genieacs">
            <i class="fa-solid fa-satellite-dish mr-2 text-rose-500"></i> GenieACS (TR-069)
        </button>
    </div>

    <!-- Tab 1: MikroTik -->
    <div class="tab-panel {{ $activeTab === 'mikrotik' ? 'active' : '' }}" id="tab-mikrotik">
        <div class="table-card p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-microchip text-indigo-600"></i> Konfigurasi API MikroTik RouterOS
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Digunakan untuk monitoring realtime traffic, sinkronisasi
                        profile PPPoE, dan pengecekan interface.</p>
                </div>
                <span class="badge" style="background:#e0e7ff;color:#4338ca;font-weight:700;">Port Default: 8728
                    (API)</span>
            </div>

            <form action="{{ route('settings.update') }}" method="POST" id="form-mikrotik">
                @csrf
                <input type="hidden" name="tab" value="mikrotik">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Host / IP Router MikroTik <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="mikrotik_host" id="mikrotik_host" class="form-input"
                            value="{{ old('mikrotik_host', $settings['mikrotik']['host']) }}"
                            placeholder="misal: 192.168.10.1 atau 10.10.10.1" required>
                        <small class="text-slate-400 text-[11px]">IP Address atau hostname router yang dapat diakses
                            dari server Laravel.</small>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Port API MikroTik <span
                                class="text-rose-500">*</span></label>
                        <input type="number" name="mikrotik_port" id="mikrotik_port" class="form-input"
                            value="{{ old('mikrotik_port', $settings['mikrotik']['port']) }}" placeholder="8728"
                            required>
                        <small class="text-slate-400 text-[11px]">Port service API RouterOS (default: 8728 atau 8729
                            untuk SSL).</small>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Username API <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="mikrotik_user" id="mikrotik_user" class="form-input"
                            value="{{ old('mikrotik_user', $settings['mikrotik']['user']) }}" placeholder="admin"
                            required>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Password API</label>
                        <div class="relative">
                            <input type="password" name="mikrotik_pass" id="mikrotik_pass" class="form-input !pr-10"
                                value="{{ old('mikrotik_pass', $settings['mikrotik']['pass']) }}"
                                placeholder="Kosongkan jika tanpa password">
                            <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                onclick="togglePasswordVisibility('mikrotik_pass', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Default Interface Traffic</label>
                        <input type="text" name="mikrotik_interface" class="form-input"
                            value="{{ old('mikrotik_interface', $settings['mikrotik']['interface']) }}"
                            placeholder="ether1">
                        <small class="text-slate-400 text-[11px]">Nama interface utama untuk monitoring bandwidth di
                            dashboard.</small>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Timeout Koneksi (detik)</label>
                        <input type="number" name="mikrotik_timeout" class="form-input"
                            value="{{ old('mikrotik_timeout', $settings['mikrotik']['timeout']) }}" placeholder="10">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" onclick="testConnectionAjax('mikrotik')"
                        class="btn-primary-admin !bg-slate-700 hover:!bg-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-plug"></i> <span id="btn-test-mikrotik-text">Test Koneksi MikroTik</span>
                    </button>
                    <button type="submit"
                        class="btn-primary-admin !bg-indigo-600 hover:!bg-indigo-700 flex items-center gap-2 !px-6">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Konfigurasi MikroTik
                    </button>
                </div>
            </form>
        </div>

        <!-- MikroTik RouterOS CLI Script Generator & Guide Card -->
        <div class="table-card p-6 mt-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-4 mb-6 gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-terminal text-indigo-600"></i> Generator & Panduan Script Terminal MikroTik
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Script konfigurasi RouterOS untuk mengaktifkan API, Server RADIUS, Incoming CoA (Auto-Kick), dan Auto-Redirect Portal Isolir (app.erlangga.net/isolir).
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge" style="background:#e0e7ff;color:#4338ca;font-weight:700;">
                        <i class="fa-solid fa-code mr-1"></i> RouterOS v6 / v7 CLI
                    </span>
                </div>
            </div>

            <!-- Script Tabs Navigation -->
            <div class="flex gap-2 mb-4 flex-wrap">
                <button type="button" class="script-tab-btn active px-4 py-2 rounded-xl text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm" data-target="script-section-isolir">
                    <i class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-300"></i> 1. Isolir & NAT Redirect
                </button>
                <button type="button" class="script-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200" data-target="script-section-radius">
                    <i class="fa-solid fa-database mr-1.5 text-emerald-500"></i> 2. Server RADIUS & CoA (Auto-Kick)
                </button>
                <button type="button" class="script-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200" data-target="script-section-api">
                    <i class="fa-solid fa-plug mr-1.5 text-sky-500"></i> 3. API Service & User
                </button>
                <button type="button" class="script-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200" data-target="script-section-all">
                    <i class="fa-solid fa-scroll mr-1.5 text-indigo-500"></i> 4. Script Lengkap (All-in-One)
                </button>
            </div>

            <!-- Script 1: Isolir & NAT Redirect -->
            <div class="script-panel active" id="script-section-isolir">
                <div class="relative bg-slate-950 rounded-2xl p-5 border border-slate-800 shadow-inner">
                    <div class="flex items-center justify-between mb-3 border-b border-slate-800/80 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-slate-400 text-xs font-mono ml-2">mikrotik-terminal ~ isolir-nat.rsc</span>
                        </div>
                        <button type="button" onclick="copyScriptToClipboard('code-script-isolir')" 
                                class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold flex items-center gap-1.5 transition-all shadow">
                            <i class="fa-solid fa-copy"></i> Salin Script
                        </button>
                    </div>
                    <pre class="overflow-x-auto text-emerald-400 font-mono text-xs leading-relaxed max-h-96 selection:bg-rose-500 selection:text-white"><code id="code-script-isolir"></code></pre>
                </div>
            </div>

            <!-- Script 2: Server RADIUS & CoA -->
            <div class="script-panel hidden" id="script-section-radius">
                <div class="relative bg-slate-950 rounded-2xl p-5 border border-slate-800 shadow-inner">
                    <div class="flex items-center justify-between mb-3 border-b border-slate-800/80 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-slate-400 text-xs font-mono ml-2">mikrotik-terminal ~ radius-coa.rsc</span>
                        </div>
                        <button type="button" onclick="copyScriptToClipboard('code-script-radius')" 
                                class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold flex items-center gap-1.5 transition-all shadow">
                            <i class="fa-solid fa-copy"></i> Salin Script
                        </button>
                    </div>
                    <pre class="overflow-x-auto text-emerald-400 font-mono text-xs leading-relaxed max-h-96 selection:bg-rose-500 selection:text-white"><code id="code-script-radius"></code></pre>
                </div>
            </div>

            <!-- Script 3: API Service & User -->
            <div class="script-panel hidden" id="script-section-api">
                <div class="relative bg-slate-950 rounded-2xl p-5 border border-slate-800 shadow-inner">
                    <div class="flex items-center justify-between mb-3 border-b border-slate-800/80 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-slate-400 text-xs font-mono ml-2">mikrotik-terminal ~ api-setup.rsc</span>
                        </div>
                        <button type="button" onclick="copyScriptToClipboard('code-script-api')" 
                                class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold flex items-center gap-1.5 transition-all shadow">
                            <i class="fa-solid fa-copy"></i> Salin Script
                        </button>
                    </div>
                    <pre class="overflow-x-auto text-emerald-400 font-mono text-xs leading-relaxed max-h-96 selection:bg-rose-500 selection:text-white"><code id="code-script-api"></code></pre>
                </div>
            </div>

            <!-- Script 4: All-in-One Full Script -->
            <div class="script-panel hidden" id="script-section-all">
                <div class="relative bg-slate-950 rounded-2xl p-5 border border-slate-800 shadow-inner">
                    <div class="flex items-center justify-between mb-3 border-b border-slate-800/80 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-slate-400 text-xs font-mono ml-2">mikrotik-terminal ~ full-setup-all.rsc</span>
                        </div>
                        <button type="button" onclick="copyScriptToClipboard('code-script-all')" 
                                class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold flex items-center gap-1.5 transition-all shadow">
                            <i class="fa-solid fa-copy"></i> Salin Semua Script
                        </button>
                    </div>
                    <pre class="overflow-x-auto text-emerald-400 font-mono text-xs leading-relaxed max-h-96 selection:bg-rose-500 selection:text-white"><code id="code-script-all"></code></pre>
                </div>
            </div>

            <!-- Step by step instructions box -->
            <div class="mt-6 p-4 rounded-xl bg-blue-50/70 border border-blue-200 text-xs text-slate-700 space-y-2">
                <div class="font-bold text-blue-900 flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-circle-info text-blue-600"></i> Langkah Menjalankan Script di MikroTik
                </div>
                <ol class="list-decimal list-inside space-y-1 text-slate-600 pl-1 leading-relaxed">
                    <li>Buka aplikasi <strong>Winbox</strong> dan login ke router MikroTik utama Anda.</li>
                    <li>Buka menu <strong>New Terminal</strong> di sisi kiri Winbox.</li>
                    <li>Salin script dari generator di atas, lalu <strong>Paste (Klik Kanan -> Paste)</strong> ke terminal MikroTik dan tekan <strong>Enter</strong>.</li>
                    <li>Pastikan aturan sudah muncul di menu <strong>IP -> Firewall -> NAT & Filter Rules</strong>, dan menu <strong>Radius</strong>.</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Tab 2: OLT -->
    <div class="tab-panel {{ $activeTab === 'olt' ? 'active' : '' }}" id="tab-olt">
        <div class="table-card p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-server text-sky-600"></i> Konfigurasi OLT Telnet (ZTE / Fiberhome / HSGQ)
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Digunakan untuk manajemen ONU, pengecekan redaman optik (RX
                        Power), dan registrasi otomatis.</p>
                </div>
                <span class="badge" style="background:#e0f2fe;color:#0369a1;font-weight:700;">Port Default: 23
                    (Telnet)</span>
            </div>

            <form action="{{ route('settings.update') }}" method="POST" id="form-olt">
                @csrf
                <input type="hidden" name="tab" value="olt">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Host / IP OLT <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="olt_host" id="olt_host" class="form-input"
                            value="{{ old('olt_host', $settings['olt']['host']) }}" placeholder="misal: 10.10.10.2"
                            required>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Port Telnet OLT <span
                                class="text-rose-500">*</span></label>
                        <input type="number" name="olt_port" id="olt_port" class="form-input"
                            value="{{ old('olt_port', $settings['olt']['port']) }}" placeholder="23" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Username Telnet OLT <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="olt_username" id="olt_username" class="form-input"
                            value="{{ old('olt_username', $settings['olt']['username']) }}" placeholder="admin"
                            required>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Password Telnet OLT</label>
                        <div class="relative">
                            <input type="password" name="olt_password" id="olt_password" class="form-input !pr-10"
                                value="{{ old('olt_password', $settings['olt']['password']) }}"
                                placeholder="Password OLT">
                            <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                onclick="togglePasswordVisibility('olt_password', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" onclick="testConnectionAjax('olt')"
                        class="btn-primary-admin !bg-slate-700 hover:!bg-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-plug"></i> <span id="btn-test-olt-text">Test Koneksi OLT</span>
                    </button>
                    <button type="submit"
                        class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 flex items-center gap-2 !px-6">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Konfigurasi OLT
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab 3: RADIUS -->
    <div class="tab-panel {{ $activeTab === 'radius' ? 'active' : '' }}" id="tab-radius">
        <div class="table-card p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-database text-emerald-600"></i> Database Server RADIUS
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Koneksi database MySQL server RADIUS untuk autentikasi
                        user PPPoE (radcheck, radreply, userinfo).</p>
                </div>
                <span class="badge" style="background:#dcfce7;color:#15803d;font-weight:700;">Koneksi DB
                    Terpisah</span>
            </div>

            <form action="{{ route('settings.update') }}" method="POST" id="form-radius">
                @csrf
                <input type="hidden" name="tab" value="radius">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Host / IP Database RADIUS <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="db_radius_host" id="db_radius_host" class="form-input"
                            value="{{ old('db_radius_host', $settings['radius']['host']) }}"
                            placeholder="misal: 203.2.151.130 atau 127.0.0.1" required>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Port MySQL Database <span
                                class="text-rose-500">*</span></label>
                        <input type="number" name="db_radius_port" id="db_radius_port" class="form-input"
                            value="{{ old('db_radius_port', $settings['radius']['port']) }}"
                            placeholder="3306 atau 3002" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Nama Database <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="db_radius_database" id="db_radius_database" class="form-input"
                            value="{{ old('db_radius_database', $settings['radius']['database']) }}"
                            placeholder="radius" required>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Username DB <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="db_radius_username" id="db_radius_username" class="form-input"
                            value="{{ old('db_radius_username', $settings['radius']['username']) }}"
                            placeholder="root" required>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Password DB</label>
                        <div class="relative">
                            <input type="password" name="db_radius_password" id="db_radius_password"
                                class="form-input !pr-10"
                                value="{{ old('db_radius_password', $settings['radius']['password']) }}"
                                placeholder="Password database">
                            <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                onclick="togglePasswordVisibility('db_radius_password', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" onclick="testConnectionAjax('radius')"
                        class="btn-primary-admin !bg-slate-700 hover:!bg-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-plug"></i> <span id="btn-test-radius-text">Test Koneksi Database
                            RADIUS</span>
                    </button>
                    <button type="submit"
                        class="btn-primary-admin !bg-emerald-600 hover:!bg-emerald-700 flex items-center gap-2 !px-6">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Konfigurasi RADIUS
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab 4: Xendit Payment Gateway -->
    <div class="tab-panel {{ $activeTab === 'xendit' ? 'active' : '' }}" id="tab-xendit">
        <div class="table-card p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-credit-card text-amber-600"></i> Payment Gateway Xendit
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Digunakan untuk pembuatan link pembayaran invoice otomatis
                        (VA BCA, Mandiri, BRI, QRIS, OVO, dll).</p>
                </div>
                <span class="badge" style="background:#fef3c7;color:#b45309;font-weight:700;">API v7 Ready</span>
            </div>

            <form action="{{ route('settings.update') }}" method="POST" id="form-xendit">
                @csrf
                <input type="hidden" name="tab" value="xendit">

                <div class="form-group mb-5">
                    <label class="form-label font-bold text-slate-700">Xendit Secret API Key</label>
                    <div class="relative">
                        <input type="password" name="xendit_secret_key" id="xendit_secret_key"
                            class="form-input !pr-10 font-mono"
                            value="{{ old('xendit_secret_key', $settings['xendit']['secret_key']) }}"
                            placeholder="xnd_development_... atau xnd_production_...">
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            onclick="togglePasswordVisibility('xendit_secret_key', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <small class="text-slate-400 text-[11px]">Dapatkan dari Dashboard Xendit -> Settings -> Developers
                        -> API Keys (Secret Key).</small>
                </div>

                <div class="form-group mb-5">
                    <label class="form-label font-bold text-slate-700">Xendit Webhook Verification Token</label>
                    <div class="relative">
                        <input type="password" name="xendit_webhook_token" id="xendit_webhook_token"
                            class="form-input !pr-10 font-mono"
                            value="{{ old('xendit_webhook_token', $settings['xendit']['webhook_token']) }}"
                            placeholder="Token verifikasi webhook dari dashboard Xendit">
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            onclick="togglePasswordVisibility('xendit_webhook_token', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <small class="text-slate-400 text-[11px]">Token verifikasi webhook untuk memvalidasi callback
                        pembayaran otomatis.</small>
                </div>

                <div class="form-group mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <label class="form-label font-bold text-slate-700 flex items-center justify-between">
                        <span>Webhook Callback URL (Pasang di Dashboard Xendit)</span>
                        <button type="button" onclick="copyToClipboard('{{ $settings['xendit']['webhook_url'] }}')"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-copy"></i> Salin URL
                        </button>
                    </label>
                    <input type="text" class="form-input bg-white font-mono text-xs text-slate-600"
                        value="{{ $settings['xendit']['webhook_url'] }}" readonly>
                    <small class="text-slate-500 text-[11px] mt-1 block">
                        Daftarkan URL di atas pada menu <strong>Xendit Dashboard -> Settings -> Webhooks -> Invoice
                            Paid</strong>.
                    </small>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" onclick="testConnectionAjax('xendit')"
                        class="btn-primary-admin !bg-slate-700 hover:!bg-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-plug"></i> <span id="btn-test-xendit-text">Test Validitas API Key
                            Xendit</span>
                    </button>
                    <button type="submit"
                        class="btn-primary-admin !bg-amber-600 hover:!bg-amber-700 flex items-center gap-2 !px-6">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Konfigurasi Xendit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab 5: WhatsApp Gateway (api.co.id) -->
    <div class="tab-panel {{ $activeTab === 'whatsapp' ? 'active' : '' }}" id="tab-whatsapp">
        <div class="table-card p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-brands fa-whatsapp text-emerald-600"></i> Integrasi WhatsApp Gateway & OTP (api.co.id)
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Digunakan untuk pengiriman notifikasi transaksi, tagihan, dan kode OTP verifikasi penarikan saldo Super Admin.</p>
                </div>
                <span class="badge" style="background:#ecfdf5;color:#059669;font-weight:700;">api.co.id / Custom Gateway</span>
            </div>

            <form action="{{ route('settings.update') }}" method="POST" id="form-whatsapp">
                @csrf
                <input type="hidden" name="tab" value="whatsapp">

                <div class="form-group mb-5">
                    <label class="form-label font-bold text-slate-700">WhatsApp API Endpoint URL <span class="text-rose-500">*</span></label>
                    <input type="text" name="whatsapp_api_url" id="whatsapp_api_url" class="form-input font-mono text-xs"
                        value="{{ old('whatsapp_api_url', $settings['whatsapp']['api_url']) }}"
                        placeholder="https://api.co.id/api/v1/send-message" required>
                    <small class="text-slate-400 text-[11px]">Endpoint URL provider WhatsApp API (Default: <code>https://api.co.id/api/v1/send-message</code>).</small>
                </div>

                <div class="form-group mb-5">
                    <label class="form-label font-bold text-slate-700">API Key / Token Authorization</label>
                    <div class="relative">
                        <input type="password" name="whatsapp_api_key" id="whatsapp_api_key"
                            class="form-input !pr-10 font-mono"
                            value="{{ old('whatsapp_api_key', $settings['whatsapp']['api_key']) }}"
                            placeholder="API Key atau Bearer Token dari akun api.co.id Anda">
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            onclick="togglePasswordVisibility('whatsapp_api_key', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <small class="text-slate-400 text-[11px]">Dapatkan dari Dashboard api.co.id -> Menu API Keys / Token. Jika dikosongkan, sistem berjalan dalam mode simulasi.</small>
                </div>

                <div class="form-group mb-6">
                    <label class="form-label font-bold text-slate-700">Sender / Device ID (Opsional)</label>
                    <input type="text" name="whatsapp_sender" id="whatsapp_sender" class="form-input font-mono"
                        value="{{ old('whatsapp_sender', $settings['whatsapp']['sender']) }}"
                        placeholder="misal: 6281234567890">
                    <small class="text-slate-400 text-[11px]">Nomor pengirim atau ID perangkat yang terdaftar di api.co.id. Digunakan untuk seluruh notifikasi penagihan pelanggan, kwitansi invoice, dan OTP penarikan.</small>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" onclick="testConnectionAjax('whatsapp')"
                        class="btn-primary-admin !bg-slate-700 hover:!bg-slate-800 flex items-center gap-2">
                        <i class="fa-brands fa-whatsapp"></i> <span id="btn-test-whatsapp-text">Kirim Pesan Tes WhatsApp</span>
                    </button>
                    <button type="submit"
                        class="btn-primary-admin !bg-emerald-600 hover:!bg-emerald-700 flex items-center gap-2 !px-6">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Konfigurasi WhatsApp
                    </button>
                </div>
            </form>
    <!-- Tab 6: GenieACS (TR-069) -->
    <div class="tab-panel {{ $activeTab === 'genieacs' ? 'active' : '' }}" id="tab-genieacs">
        <div class="table-card p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-satellite-dish text-rose-600"></i> Integrasi Server GenieACS (TR-069 / CWMP)
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Digunakan untuk remote manajemen ONT/CPE pelanggan: ganti SSID & Password Wi-Fi, monitoring perangkat terhubung, redaman optik, dan suhu perangkat.</p>
                </div>
                <span class="badge" style="background:#ffe4e6;color:#e11d48;font-weight:700;">Port Default: 3000 (UI API) / 7557 (NBI)</span>
            </div>

            <form action="{{ route('settings.update') }}" method="POST" id="form-genieacs">
                @csrf
                <input type="hidden" name="tab" value="genieacs">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">GenieACS Server URL / IP <span class="text-rose-500">*</span></label>
                        <input type="text" name="genieacs_url" id="genieacs_url" class="form-input font-mono text-xs"
                            value="{{ old('genieacs_url', $settings['genieacs']['url']) }}"
                            placeholder="http://203.2.151.130:3000" required>
                        <small class="text-slate-400 text-[11px]">URL server GenieACS UI atau NBI (contoh: <code>http://203.2.151.130:3000</code>).</small>
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Timeout Koneksi (detik)</label>
                        <input type="number" name="genieacs_timeout" id="genieacs_timeout" class="form-input"
                            value="{{ old('genieacs_timeout', $settings['genieacs']['timeout']) }}" placeholder="10">
                        <small class="text-slate-400 text-[11px]">Batas waktu timeout HTTP request ke server GenieACS.</small>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Username GenieACS <span class="text-rose-500">*</span></label>
                        <input type="text" name="genieacs_username" id="genieacs_username" class="form-input font-mono"
                            value="{{ old('genieacs_username', $settings['genieacs']['username']) }}"
                            placeholder="admin" required>
                        <small class="text-slate-400 text-[11px]">Username akun administrator GenieACS.</small>
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Password GenieACS</label>
                        <div class="relative">
                            <input type="password" name="genieacs_password" id="genieacs_password"
                                class="form-input !pr-10 font-mono"
                                value="{{ old('genieacs_password', $settings['genieacs']['password']) }}"
                                placeholder="Password GenieACS">
                            <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                onclick="togglePasswordVisibility('genieacs_password', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-slate-400 text-[11px]">Password akun administrator GenieACS.</small>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" onclick="testConnectionAjax('genieacs')"
                        class="btn-primary-admin !bg-slate-700 hover:!bg-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-satellite-dish"></i> <span id="btn-test-genieacs-text">Uji Koneksi GenieACS</span>
                    </button>
                    <button type="submit"
                        class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 flex items-center gap-2 !px-6">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Konfigurasi GenieACS
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script>
            // Tab switching
            $(document).on('click', '.modal-tab', function() {
                const tabId = $(this).data('tab');
                $('.modal-tab').removeClass('active');
                $(this).addClass('active');
                $('.tab-panel').removeClass('active');
                $('#' + tabId).addClass('active');

                // Update URL query parameter without page reload
                const tabName = tabId.replace('tab-', '');
                const url = new URL(window.location);
                url.searchParams.set('tab', tabName);
                window.history.replaceState({}, '', url);
            });

            // Toggle password visibility
            function togglePasswordVisibility(fieldId, btn) {
                const field = document.getElementById(fieldId);
                const icon = btn.querySelector('i');
                if (field.type === 'password') {
                    field.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    field.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }

            // Copy to clipboard with Toast
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    showToast('Webhook URL berhasil disalin ke clipboard!', 'success');
                });
            }

            // Copy terminal script to clipboard
            function copyScriptToClipboard(elementId) {
                const text = document.getElementById(elementId)?.innerText || '';
                if (!text) return;
                navigator.clipboard.writeText(text).then(() => {
                    showToast('Script MikroTik berhasil disalin ke clipboard!', 'success');
                });
            }

            // MikroTik Script Tabs Switching
            $(document).on('click', '.script-tab-btn', function() {
                const targetId = $(this).data('target');
                $('.script-tab-btn').removeClass('active bg-indigo-600 text-white shadow-sm').addClass('bg-slate-100 text-slate-600');
                $(this).addClass('active bg-indigo-600 text-white shadow-sm').removeClass('bg-slate-100 text-slate-600');
                
                $('.script-panel').addClass('hidden').removeClass('active');
                $('#' + targetId).removeClass('hidden').addClass('active');
            });

            // Dynamic MikroTik Script Generator
            function updateMikrotikScripts() {
                const webDomain = 'app.erlangga.net';
                const radiusHost = '{{ $settings['radius']['host'] ?: config('database.connections.radius.host', '127.0.0.1') }}';
                const radiusSecret = '{{ \App\Models\Setting::get('radius_secret', 'secret123') }}';
                const isolirPool = '10.254.0.2-10.254.0.254';
                const apiPort = document.getElementById('mikrotik_port')?.value.trim() || '{{ $settings['mikrotik']['port'] }}';
                const apiUser = document.getElementById('mikrotik_user')?.value.trim() || '{{ $settings['mikrotik']['user'] }}';
                const apiPass = document.getElementById('mikrotik_pass')?.value.trim() || 'password_anda';

                // 1. Script Isolir & NAT Redirect
                const scriptIsolir = `# ================================================================
# 1. BUAT IP POOL KHUSUS ISOLIR
# ================================================================
/ip pool add name=pool-isolir ranges=\${isolirPool}

# ================================================================
# 2. DAFTAR WHITELIST (XENDIT PAYMENT, DNS & DOMAIN BILLING)
# ================================================================
/ip firewall address-list
add list=WHITELIST-ISOLIR address=api.xendit.co comment="Xendit API"
add list=WHITELIST-ISOLIR address=checkout.xendit.co comment="Xendit Checkout Gateway"
add list=WHITELIST-ISOLIR address=xendit.co comment="Xendit Main Portal"
add list=WHITELIST-ISOLIR address=\${webDomain} comment="Domain Aplikasi & Portal Isolir (\${webDomain}/isolir)"

# ================================================================
# 3. FIREWALL NAT: REDIRECT PORT 80 KE PORTAL ISOLIR
# ================================================================
/ip firewall nat
add chain=dstnat action=dst-nat to-addresses=\${webDomain} to-ports=80 protocol=tcp \\
    dst-port=80 src-address-list=ISOLIR dst-address-list=!WHITELIST-ISOLIR \\
    comment="REDIRECT PELANGGAN ISOLIR KE http://\${webDomain}/isolir"

# ================================================================
# 4. FIREWALL FILTER: IZINKAN DNS & XENDIT, BLOK INTERNET LAIN
# ================================================================
/ip firewall filter
add chain=forward action=accept protocol=udp dst-port=53 src-address-list=ISOLIR comment="ALLOW DNS UDP ISOLIR"
add chain=forward action=accept protocol=tcp dst-port=53 src-address-list=ISOLIR comment="ALLOW DNS TCP ISOLIR"
add chain=forward action=accept src-address-list=ISOLIR dst-address-list=WHITELIST-ISOLIR comment="ALLOW XENDIT & ISOLIR PORTAL"
add chain=forward action=drop src-address-list=ISOLIR comment="BLOCK SEMUA INTERNET PELANGGAN ISOLIR"`;

                // 2. Script Server RADIUS & CoA
                const scriptRadius = `# ================================================================
# 1. TAMBAHKAN SERVER RADIUS CLIENT
# ================================================================
/radius add service=ppp address=\${radiusHost} secret="\${radiusSecret}" \\
    authentication-port=1812 accounting-port=1813 timeout=3s comment="Server RADIUS Billing"

# ================================================================
# 2. AKTIFKAN RADIUS PADA PPPOE SERVER (AAA)
# ================================================================
/ppp aaa set use-radius=yes accounting=yes interim-update=5m

# ================================================================
# 3. AKTIFKAN RADIUS INCOMING (CoA) UNTUK AUTO-KICK SESI
# ================================================================
/radius incoming set accept=yes port=3799`;

                // 3. Script API Service & User
                const scriptApi = `# ================================================================
# 1. AKTIFKAN SERVICE API ROUTEROS
# ================================================================
/ip service set api port=\${apiPort} disabled=no

# ================================================================
# 2. BUAT USER GROUP KHUSUS APLIKASI
# ================================================================
/user group add name=laravel-group policy=api,read,write,test,password comment="Group API Laravel"

# ================================================================
# 3. BUAT USER API UNTUK APLIKASI LARAVEL
# ================================================================
/user add name=\${apiUser} group=laravel-group password="\${apiPass}" comment="User API Laravel Erlangga"`;

                // 4. Script All-in-One Full
                const scriptAll = `# ================================================================
# SCRIPT LENGKAP KONFIGURASI MIKROTIK ROUTEROS (ALL-IN-ONE)
# ERLANGGA NET ISP BILLING & ISOLIR SYSTEM
# ================================================================

# --- [1/3] SERVICE API & USER ---
\${scriptApi}

# --- [2/3] SERVER RADIUS & INCOMING COA (AUTO-KICK) ---
\${scriptRadius}

# --- [3/3] ISOLIR POOL, WHITELIST & REDIRECT NAT (\${webDomain}/isolir) ---
\${scriptIsolir}`;

                const codeIsolir = document.getElementById('code-script-isolir');
                const codeRadius = document.getElementById('code-script-radius');
                const codeApi = document.getElementById('code-script-api');
                const codeAll = document.getElementById('code-script-all');

                if (codeIsolir) codeIsolir.textContent = scriptIsolir;
                if (codeRadius) codeRadius.textContent = scriptRadius;
                if (codeApi) codeApi.textContent = scriptApi;
                if (codeAll) codeAll.textContent = scriptAll;
            }

            // Inisialisasi script pada page load
            $(document).ready(function() {
                updateMikrotikScripts();
            });

            // AJAX test connection with Toast
            function testConnectionAjax(type) {
                const btnText = document.getElementById('btn-test-' + type + '-text');
                const originalBtnText = btnText.innerHTML;
                btnText.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Sedang Menguji...';

                let payload = {
                    type: type,
                    _token: '{{ csrf_token() }}'
                };

                if (type === 'mikrotik') {
                    payload.host = document.getElementById('mikrotik_host').value;
                    payload.port = document.getElementById('mikrotik_port').value;
                    payload.user = document.getElementById('mikrotik_user').value;
                    payload.pass = document.getElementById('mikrotik_pass').value;
                } else if (type === 'olt') {
                    payload.host = document.getElementById('olt_host').value;
                    payload.port = document.getElementById('olt_port').value;
                    payload.username = document.getElementById('olt_username').value;
                    payload.password = document.getElementById('olt_password').value;
                } else if (type === 'radius') {
                    payload.host = document.getElementById('db_radius_host').value;
                    payload.port = document.getElementById('db_radius_port').value;
                    payload.database = document.getElementById('db_radius_database').value;
                    payload.username = document.getElementById('db_radius_username').value;
                    payload.password = document.getElementById('db_radius_password').value;
                } else if (type === 'xendit') {
                    payload.secret_key = document.getElementById('xendit_secret_key').value;
                } else if (type === 'whatsapp') {
                    const testPhone = prompt('Masukkan nomor WhatsApp tujuan untuk tes pengiriman pesan:', '{{ auth()->user()->no_whatsapp ?? \App\Models\Setting::get('company_phone', '081234567890') }}');
                    if (!testPhone) {
                        btnText.innerHTML = originalBtnText;
                        return;
                    }
                    payload.target = testPhone;
                } else if (type === 'genieacs') {
                    payload.url = document.getElementById('genieacs_url').value;
                    payload.username = document.getElementById('genieacs_username').value;
                    payload.password = document.getElementById('genieacs_password').value;
                }

                fetch('{{ route('settings.test_connection') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json().then(data => ({
                        status: res.status,
                        body: data
                    })))
                    .then(res => {
                        btnText.innerHTML = originalBtnText;

                        if (res.status === 200 && res.body.success) {
                            showToast(res.body.message, 'success');
                        } else {
                            showToast(res.body.message || 'Terjadi kesalahan saat menguji koneksi.', 'error');
                        }
                    })
                    .catch(err => {
                        btnText.innerHTML = originalBtnText;
                        showToast('Network error / endpoint gagal dihubungi.', 'error');
                    });
            }
        </script>
    @endsection
</x-layouts.app>
