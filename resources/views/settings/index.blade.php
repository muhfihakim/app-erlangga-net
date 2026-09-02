<x-layouts.app>
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Pengaturan Sistem & Integrasi Server
            </h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0;">
                Kelola konfigurasi parameter MikroTik, OLT, database FreeRADIUS, dan Xendit yang tersimpan aman di
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
            <i class="fa-solid fa-database mr-2 text-emerald-500"></i> FreeRADIUS Server
        </button>
        <button type="button" class="modal-tab {{ $activeTab === 'xendit' ? 'active' : '' }} !rounded-xl !py-2.5 !px-5"
            data-tab="tab-xendit">
            <i class="fa-solid fa-credit-card mr-2 text-amber-500"></i> Xendit Payment
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

    <!-- Tab 3: FreeRADIUS -->
    <div class="tab-panel {{ $activeTab === 'radius' ? 'active' : '' }}" id="tab-radius">
        <div class="table-card p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-database text-emerald-600"></i> Database Server FreeRADIUS / daloRADIUS
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Koneksi database MySQL server FreeRADIUS untuk autentikasi
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
                        <label class="form-label font-bold text-slate-700">Host / IP Database FreeRADIUS <span
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
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Konfigurasi FreeRADIUS
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
