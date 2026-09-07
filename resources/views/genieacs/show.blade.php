<x-layouts.app>
    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
        <div class="flex items-center gap-3">
            <a href="{{ route('genieacs.index') }}" class="btn-cancel !h-10 !w-10 !p-0 flex items-center justify-center rounded-xl" title="Kembali ke Daftar ONT">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 style="font-size:20px;font-weight:900;margin:0;">
                        {{ $device['manufacturer'] }} {{ $device['model'] }}
                    </h1>
                    @if (!empty($device['is_online']))
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            ONLINE
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-500">
                            Offline
                        </span>
                    @endif
                    <span class="badge font-mono text-[10px] bg-slate-100 text-slate-700">{{ $device['pon_mode'] }}</span>
                </div>
                <p style="font-size:13px;color:#64748b;margin:2px 0 0;" class="font-mono">
                    SN: <span class="text-slate-900 font-bold">{{ $device['serial_number'] }}</span> &bull; IP: <span class="text-slate-900 font-semibold">{{ $device['ip_address'] }}</span> &bull; PPPoE: <span class="text-sky-600 font-semibold">{{ $device['pppoe_username'] }}</span>
                </p>
            </div>
        </div>

        <!-- Quick Control Buttons -->
        <div class="flex items-center gap-2">
            <button type="button" onclick="refreshOntData()" class="btn-primary-admin !bg-slate-700 hover:!bg-slate-800 !h-10 flex items-center gap-2" id="btn-refresh-ont">
                <i class="fa-solid fa-rotate"></i> <span>Refresh Parameter</span>
            </button>
            <button type="button" onclick="rebootOnt()" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !h-10 flex items-center gap-2" id="btn-reboot-ont">
                <i class="fa-solid fa-power-off"></i> <span>Reboot ONT</span>
            </button>
        </div>
    </div>

    <!-- Alert / Inform banner -->
    <div class="mb-6 p-3.5 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between text-xs text-slate-600 flex-wrap gap-2">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-sky-500 text-sm"></i>
            <span>Inform Terakhir ke GenieACS: <strong class="text-slate-800 font-mono">{{ $device['last_inform'] }}</strong></span>
            @if ($device['uptime'])
                <span class="text-slate-300">|</span>
                <span>PPP Uptime: <strong class="text-slate-800 font-mono">{{ $device['uptime'] }}</strong></span>
            @endif
        </div>
        <div class="text-slate-400 font-mono text-[11px]">
            Device ID: {{ $device['id'] }}
        </div>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Column: Specs, Optical & Customer Info (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Card: Optik & Suhu -->
            <div class="table-card p-6">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-heart-pulse text-rose-500"></i> Status Optik & Suhu Perangkat
                </h3>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <!-- Suhu ONT -->
                    <div class="p-4 rounded-2xl border {{ floatval($device['temperature']) > 65 ? 'bg-rose-50/70 border-rose-200' : 'bg-slate-50 border-slate-100' }}">
                        <div class="flex items-center justify-between text-slate-500 text-xs mb-1.5 font-medium">
                            <span>Suhu ONT</span>
                            <i class="fa-solid fa-temperature-three-quarters text-sm {{ floatval($device['temperature']) > 65 ? 'text-rose-500' : 'text-slate-400' }}"></i>
                        </div>
                        <div class="text-2xl font-black font-mono {{ floatval($device['temperature']) > 65 ? 'text-rose-600' : 'text-slate-900' }}">
                            {{ $device['temperature'] ? ($device['temperature'] . ' °C') : '-' }}
                        </div>
                        <div class="text-[11px] mt-1 {{ floatval($device['temperature']) > 65 ? 'text-rose-500 font-semibold' : 'text-slate-400' }}">
                            {{ floatval($device['temperature']) > 65 ? 'Suhu Tinggi (>65°C)' : 'Normal (<60°C)' }}
                        </div>
                    </div>

                    <!-- Redaman Optik (RX Power) -->
                    @php
                        $rxNum = floatval(str_replace(['dBm', ' '], '', $device['rx_power'] ?? '0'));
                        $rxStatus = 'Normal';
                        $rxClass = 'bg-emerald-50 border-emerald-200 text-emerald-600';
                        if ($rxNum < -27) {
                            $rxStatus = 'Redaman Tinggi';
                            $rxClass = 'bg-rose-50 border-rose-200 text-rose-600';
                        } elseif ($rxNum < -24) {
                            $rxStatus = 'Waspada Redaman';
                            $rxClass = 'bg-amber-50 border-amber-200 text-amber-600';
                        }
                    @endphp
                    <div class="p-4 rounded-2xl border {{ $rxClass }}">
                        <div class="flex items-center justify-between text-xs mb-1.5 font-medium opacity-80">
                            <span>Redaman RX</span>
                            <i class="fa-solid fa-wave-square text-sm"></i>
                        </div>
                        <div class="text-2xl font-black font-mono">
                            {{ $device['rx_power'] ? ($device['rx_power'] . ' dBm') : '-' }}
                        </div>
                        <div class="text-[11px] font-semibold mt-1">
                            {{ $device['rx_power'] ? $rxStatus : 'Tidak ada data' }}
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-xl p-3 text-xs space-y-2 text-slate-600">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Mode PON:</span>
                        <span class="font-bold text-slate-800">{{ $device['pon_mode'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">VLAN ID Internet:</span>
                        <span class="font-mono font-bold text-slate-800">{{ $device['vlan'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Alamat IP WAN:</span>
                        <span class="font-mono font-bold text-slate-800">{{ $device['ip_address'] ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card: Spesifikasi Hardware -->
            <div class="table-card p-6">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-microchip text-indigo-500"></i> Spesifikasi Perangkat
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-400">Vendor / Manufaktur</span>
                        <span class="font-bold text-slate-800">{{ $device['manufacturer'] }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-400">Tipe / Model ONT</span>
                        <span class="font-bold text-slate-800">{{ $device['model'] }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-400">Nomor Seri (Serial Number)</span>
                        <span class="font-mono font-bold text-sky-600">{{ $device['serial_number'] }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-400">OUI</span>
                        <span class="font-mono text-slate-700">{{ $device['oui'] }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-400">Versi Firmware (Software)</span>
                        <span class="font-mono text-slate-700">{{ $device['software_version'] }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-slate-400">Versi Hardware</span>
                        <span class="font-mono text-slate-700">{{ $device['hardware_version'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Card: Informasi Pelanggan Terkait -->
            <div class="table-card p-6">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-user-check text-emerald-500"></i> Data Pelanggan Terkait
                </h3>

                @if ($customer)
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-400">Nama Pelanggan</span>
                            <span class="font-bold text-slate-800 text-sm">{{ $customer->nama_lengkap }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-400">ID Pelanggan</span>
                            <span class="font-mono font-bold text-slate-700">{{ $customer->id_pelanggan }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-400">WhatsApp / Telepon</span>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->nomor_telepon) }}" target="_blank"
                                class="text-emerald-600 font-bold hover:underline flex items-center gap-1">
                                <i class="fa-brands fa-whatsapp"></i> {{ $customer->nomor_telepon }}
                            </a>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-400">Paket Layanan</span>
                            <span class="badge bg-sky-50 text-sky-700 font-bold">{{ $customer->paket_layanan ?? 'Paket Internet' }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-400">Alamat</span>
                            <span class="text-slate-700 text-right max-w-[200px]">{{ $customer->alamat ?? '-' }}</span>
                        </div>
                        <div class="pt-2">
                            <a href="{{ route('customers.index', ['search' => $customer->id_pelanggan]) }}"
                                class="btn-primary-admin !bg-slate-100 !text-slate-700 hover:!bg-slate-200 !w-full justify-center !text-xs">
                                <i class="fa-solid fa-address-card mr-1"></i> Buka Profil Lengkap Pelanggan
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-center py-6 text-slate-400">
                        <i class="fa-solid fa-user-slash text-3xl mb-2 text-slate-300"></i>
                        <p class="text-xs font-semibold text-slate-600">Username PPPoE tidak ditemukan di Database Pelanggan</p>
                        <p class="text-[11px] text-slate-400 mt-1">Username saat ini: <code>{{ $device['pppoe_username'] }}</code></p>
                    </div>
                @endif
            </div>

        </div>

        <!-- Right Column: Wi-Fi Management & Connected Devices (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Card: Form Pengaturan Wi-Fi (SSID & Password) -->
            <div class="table-card p-6 border-2 border-sky-100">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-wifi text-sky-600"></i> Konfigurasi Wi-Fi Pelanggan
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Ubah nama Wi-Fi (SSID) dan kata sandi tanpa perlu ke lokasi pelanggan.</p>
                    </div>
                    <span class="badge bg-sky-50 text-sky-700 font-bold border border-sky-200">TR-069 Instant Sync</span>
                </div>

                <!-- Tabs 2.4G / 5G if dual band -->
                @if (!empty($device['wlan5']))
                    <div class="flex gap-2 mb-5 p-1 bg-slate-100 rounded-xl">
                        <button type="button" onclick="switchWifiTab('2g')" id="tab-btn-2g"
                            class="flex-1 py-2 rounded-lg text-xs font-bold transition-all bg-white text-slate-900 shadow-sm flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-wifi"></i> Wi-Fi 2.4 GHz (Utama)
                        </button>
                        <button type="button" onclick="switchWifiTab('5g')" id="tab-btn-5g"
                            class="flex-1 py-2 rounded-lg text-xs font-bold transition-all text-slate-500 hover:text-slate-800 flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-bolt"></i> Wi-Fi 5 GHz (High Speed)
                        </button>
                    </div>
                @endif

                <!-- Form WLAN 1 (2.4 GHz) -->
                <form id="form-wifi-2g" onsubmit="submitWifiForm(event, 1)">
                    @csrf
                    <input type="hidden" name="wlan_index" value="1">
                    
                    <div class="space-y-4">
                        <div class="form-group !mb-0">
                            <label class="form-label font-bold text-slate-700 flex items-center justify-between">
                                <span>Nama Wi-Fi (SSID) <span class="text-rose-500">*</span></span>
                                <span class="text-[11px] text-slate-400 font-normal">Saat ini: <strong class="text-slate-700 font-mono">{{ $device['wlan1']['ssid'] }}</strong></span>
                            </label>
                            <input type="text" name="ssid" id="wifi_ssid_1" class="form-input font-semibold text-slate-900"
                                value="{{ $device['wlan1']['ssid'] }}" maxlength="32" placeholder="Nama Wi-Fi Pelanggan" required>
                            <small class="text-slate-400 text-[11px]">Nama jaringan Wi-Fi yang akan terdeteksi di HP/Laptop pelanggan.</small>
                        </div>

                        <div class="form-group !mb-0">
                            <label class="form-label font-bold text-slate-700">
                                <span>Password Wi-Fi Baru</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="wifi_password_1" class="form-input !pr-10 font-mono"
                                    minlength="8" maxlength="63" placeholder="Masukkan password baru (minimal 8 karakter)">
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                    onclick="togglePasswordVisibility('wifi_password_1', this)">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-slate-400 text-[11px]">Kosongkan jika hanya ingin mengubah nama SSID saja tanpa mengganti password.</small>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2 text-xs text-slate-500">
                            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                                <span>Status Wi-Fi:</span>
                                <span class="font-bold text-emerald-600">{{ $device['wlan1']['status'] ?? 'Aktif' }}</span>
                            </div>
                            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                                <span>Channel:</span>
                                <span class="font-bold text-slate-800">{{ $device['wlan1']['channel'] ?? 'Auto' }}</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-end">
                            <button type="submit" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 flex items-center gap-2 !px-6" id="btn-save-wifi-1">
                                <i class="fa-solid fa-floppy-disk"></i> Terapkan Wi-Fi 2.4 GHz
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Form WLAN 5 (5 GHz, if exists) -->
                @if (!empty($device['wlan5']))
                    <form id="form-wifi-5g" onsubmit="submitWifiForm(event, 5)" class="hidden">
                        @csrf
                        <input type="hidden" name="wlan_index" value="5">
                        
                        <div class="space-y-4">
                            <div class="form-group !mb-0">
                                <label class="form-label font-bold text-slate-700 flex items-center justify-between">
                                    <span>Nama Wi-Fi 5 GHz (SSID) <span class="text-rose-500">*</span></span>
                                    <span class="text-[11px] text-slate-400 font-normal">Saat ini: <strong class="text-slate-700 font-mono">{{ $device['wlan5']['ssid'] }}</strong></span>
                                </label>
                                <input type="text" name="ssid" id="wifi_ssid_5" class="form-input font-semibold text-slate-900"
                                    value="{{ $device['wlan5']['ssid'] }}" maxlength="32" placeholder="Nama Wi-Fi 5 GHz Pelanggan" required>
                            </div>

                            <div class="form-group !mb-0">
                                <label class="form-label font-bold text-slate-700">Password Wi-Fi 5 GHz Baru</label>
                                <div class="relative">
                                    <input type="password" name="password" id="wifi_password_5" class="form-input !pr-10 font-mono"
                                        minlength="8" maxlength="63" placeholder="Masukkan password baru 5 GHz">
                                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                        onclick="togglePasswordVisibility('wifi_password_5', this)">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex justify-end">
                                <button type="submit" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 flex items-center gap-2 !px-6" id="btn-save-wifi-5">
                                    <i class="fa-solid fa-floppy-disk"></i> Terapkan Wi-Fi 5 GHz
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>

            <!-- Card: Perangkat Terhubung (Connected Devices / Host Table) -->
            <div class="table-card p-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-users text-indigo-600"></i> Perangkat Terhubung (Host Table)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar smartphone, laptop, dan smart device yang saat ini terkoneksi ke ONT ini.</p>
                    </div>
                    <span class="badge bg-indigo-50 text-indigo-700 font-bold border border-indigo-100">
                        {{ count($device['hosts']) }} Perangkat Terdeteksi
                    </span>
                </div>

                @if (!empty($device['hosts']) && count($device['hosts']) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                                    <th class="py-2.5 px-3">Nama Perangkat</th>
                                    <th class="py-2.5 px-3">Alamat IP</th>
                                    <th class="py-2.5 px-3">MAC Address</th>
                                    <th class="py-2.5 px-3 text-center">Koneksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                @foreach ($device['hosts'] as $host)
                                    @php
                                        $hname = $host['hostname'];
                                        $icon = 'fa-laptop';
                                        if (preg_match('/(android|iphone|redmi|oppo|vivo|samsung|poco|xiaomi|infinix|realme|huawei)/i', $hname)) {
                                            $icon = 'fa-mobile-screen-button';
                                        } elseif (preg_match('/(tv|smart|tcl|androidtv|box|coolita)/i', $hname)) {
                                            $icon = 'fa-tv';
                                        }
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-2.5 px-3">
                                            <div class="flex items-center gap-2 font-bold text-slate-800">
                                                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs flex-shrink-0">
                                                    <i class="fa-solid {{ $icon }}"></i>
                                                </div>
                                                <span class="truncate max-w-[180px]">{{ $hname }}</span>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-3 font-mono text-slate-600">
                                            {{ $host['ip'] }}
                                        </td>
                                        <td class="py-2.5 px-3 font-mono text-[11px] text-slate-500 uppercase">
                                            {{ $host['mac'] }}
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="badge !text-[10px] !py-0.5 !px-2 bg-sky-50 text-sky-700 font-medium">
                                                {{ $host['interface'] === '802.11' ? 'Wi-Fi' : ($host['interface'] ?? 'Wi-Fi') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-network-wired text-3xl mb-2 text-slate-300"></i>
                        <p class="text-xs font-semibold text-slate-600">Belum ada perangkat terhubung yang terdata</p>
                        <p class="text-[11px] text-slate-400 mt-1">Klik tombol <strong>Refresh Parameter</strong> di atas untuk membaca ulang Host Table dari ONT.</p>
                    </div>
                @endif
            </div>

        </div>

    </div>

    @section('Scripts')
        <script>
            const deviceId = @json($device['id']);

            function switchWifiTab(band) {
                if (band === '2g') {
                    $('#form-wifi-2g').removeClass('hidden');
                    $('#form-wifi-5g').addClass('hidden');
                    $('#tab-btn-2g').addClass('bg-white text-slate-900 shadow-sm').removeClass('text-slate-500');
                    $('#tab-btn-5g').removeClass('bg-white text-slate-900 shadow-sm').addClass('text-slate-500');
                } else {
                    $('#form-wifi-2g').addClass('hidden');
                    $('#form-wifi-5g').removeClass('hidden');
                    $('#tab-btn-5g').addClass('bg-white text-slate-900 shadow-sm').removeClass('text-slate-500');
                    $('#tab-btn-2g').removeClass('bg-white text-slate-900 shadow-sm').addClass('text-slate-500');
                }
            }

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

            function submitWifiForm(e, wlanIndex) {
                e.preventDefault();
                const ssid = document.getElementById(`wifi_ssid_${wlanIndex}`).value;
                const password = document.getElementById(`wifi_password_${wlanIndex}`).value;
                const btn = document.getElementById(`btn-save-wifi-${wlanIndex}`);
                const origText = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengirim ke ONT via TR-069...';

                fetch(`/genieacs/${encodeURIComponent(deviceId)}/wifi`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ssid, password, wlan_index: wlanIndex })
                })
                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                .then(res => {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                    if (res.status === 200 && res.body.success) {
                        showToast(res.body.message, 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showToast(res.body.message || 'Gagal mengubah Wi-Fi.', 'error');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                    showToast('Network error saat mengirim ke GenieACS.', 'error');
                });
            }

            function rebootOnt() {
                if (!confirm('Apakah Anda yakin ingin mereboot perangkat ONT ini? Koneksi internet pelanggan akan terputus sebentar.')) {
                    return;
                }

                const btn = document.getElementById('btn-reboot-ont');
                const origText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengirim Reboot...';

                fetch(`/genieacs/${encodeURIComponent(deviceId)}/reboot`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                .then(res => {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                    if (res.status === 200 && res.body.success) {
                        showToast(res.body.message, 'success');
                    } else {
                        showToast(res.body.message || 'Gagal mengirim perintah reboot.', 'error');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                    showToast('Network error saat menghubungi GenieACS.', 'error');
                });
            }

            function refreshOntData() {
                const btn = document.getElementById('btn-refresh-ont');
                const origText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Meminta Refresh...';

                fetch(`/genieacs/${encodeURIComponent(deviceId)}/refresh`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                .then(res => {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                    if (res.status === 200 && res.body.success) {
                        showToast('Perintah refresh berhasil dikirim. Memuat ulang tampilan...', 'success');
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        showToast(res.body.message || 'Gagal mengirim perintah refresh.', 'error');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                    showToast('Network error saat menghubungi GenieACS.', 'error');
                });
            }
        </script>
    @endsection
</x-layouts.app>
