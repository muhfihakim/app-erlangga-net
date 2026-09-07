<x-layouts.app>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                <i class="fa-solid fa-satellite-dish text-rose-600"></i> Manajemen ONT (GenieACS TR-069)
            </h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0;">
                Monitoring parameter ONT/CPE, status online, suhu, redaman optik, dan kontrol Wi-Fi jarak jauh via TR-069.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('settings.index', ['tab' => 'genieacs']) }}"
                class="btn-primary-admin !bg-slate-700 hover:!bg-slate-800 !h-10 flex items-center gap-2">
                <i class="fa-solid fa-sliders"></i> Pengaturan GenieACS
            </a>
            <button onclick="window.location.reload()"
                class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !h-10 flex items-center gap-2">
                <i class="fa-solid fa-rotate"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0284c7;">
                <i class="fa-solid fa-satellite-dish"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($totalCount, 0, ',', '.') }}</div>
                <div class="stat-lbl">Total ONT Terdaftar</div>
                <div class="text-[11px] text-slate-400 mt-1">Perangkat di Server GenieACS</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5;color:#059669;">
                <i class="fa-solid fa-wifi"></i>
            </div>
            <div>
                <div class="stat-val text-emerald-600">{{ number_format($onlineCount, 0, ',', '.') }}</div>
                <div class="stat-lbl">ONT Online (Aktif)</div>
                <div class="text-[11px] text-emerald-500 font-semibold mt-1">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse mr-1"></span> Inform < 10 Menit
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9;color:#64748b;">
                <i class="fa-solid fa-power-off"></i>
            </div>
            <div>
                <div class="stat-val text-slate-600">{{ number_format($offlineCount, 0, ',', '.') }}</div>
                <div class="stat-lbl">ONT Offline / Standby</div>
                <div class="text-[11px] text-slate-400 mt-1">Tidak ada inform > 10 Menit</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="table-card p-4 mb-6">
        <form method="GET" action="{{ route('genieacs.index') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2 flex-1 min-w-[280px]">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari Serial Number, Username PPPoE, SSID, IP Address, atau Model ONT..."
                        class="form-input !pl-10 !h-10 text-sm w-full">
                </div>
                <button type="submit" class="btn-primary-admin !h-10 !px-5 flex items-center gap-2">
                    <i class="fa-solid fa-search"></i> Cari
                </button>
                @if ($search || $statusFilter !== 'all')
                    <a href="{{ route('genieacs.index') }}" class="btn-cancel !h-10 !px-4 flex items-center gap-1.5" title="Reset Filter">
                        <i class="fa-solid fa-xmark"></i> Reset
                    </a>
                @endif
            </div>

            <!-- Status Filter Buttons -->
            <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl">
                <a href="{{ route('genieacs.index', ['search' => $search, 'status' => 'all']) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $statusFilter === 'all' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                    Semua ({{ $totalCount }})
                </a>
                <a href="{{ route('genieacs.index', ['search' => $search, 'status' => 'online']) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $statusFilter === 'online' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-500 hover:text-emerald-700' }}">
                    Online ({{ $onlineCount }})
                </a>
                <a href="{{ route('genieacs.index', ['search' => $search, 'status' => 'offline']) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $statusFilter === 'offline' ? 'bg-slate-700 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                    Offline ({{ $offlineCount }})
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" style="font-size: 13px;">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[11px] tracking-wider">
                        <th class="py-3.5 px-4 text-center" style="width: 70px;">Status</th>
                        <th class="py-3.5 px-4">Perangkat (ONT)</th>
                        <th class="py-3.5 px-4">Pelanggan (PPPoE)</th>
                        <th class="py-3.5 px-4">Alamat IP</th>
                        <th class="py-3.5 px-4">Nama Wi-Fi (SSID)</th>
                        <th class="py-3.5 px-4 text-center">Redaman RX</th>
                        <th class="py-3.5 px-4 text-center">Suhu ONT</th>
                        <th class="py-3.5 px-4">Inform Terakhir</th>
                        <th class="py-3.5 px-4 text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse ($devices as $d)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Status -->
                            <td class="py-3 px-4 text-center">
                                @if (!empty($d['is_online']))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                        Online
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500">
                                        Offline
                                    </span>
                                @endif
                            </td>

                            <!-- Perangkat ONT -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 text-sm flex-shrink-0">
                                        <i class="fa-solid fa-server"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 font-mono text-xs">{{ $d['serial_number'] }}</div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                            <span class="font-semibold text-sky-600">{{ $d['manufacturer'] }}</span>
                                            <span>&bull;</span>
                                            <span>{{ $d['model'] }}</span>
                                            <span class="badge !text-[9px] !py-0 !px-1.5 bg-slate-100 text-slate-600 font-mono">{{ $d['pon_mode'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Pelanggan PPPoE -->
                            <td class="py-3 px-4">
                                @if (!empty($d['pppoe_username']) && $d['pppoe_username'] !== '-')
                                    <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-user text-xs text-slate-400"></i>
                                        <span>{{ $d['pppoe_username'] }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-xs">Belum terhubung PPPoE</span>
                                @endif
                            </td>

                            <!-- IP Address -->
                            <td class="py-3 px-4 font-mono text-xs text-slate-600">
                                {{ $d['ip_address'] ?? '-' }}
                            </td>

                            <!-- SSID Wi-Fi -->
                            <td class="py-3 px-4">
                                @if (!empty($d['ssid']) && $d['ssid'] !== '-')
                                    <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-wifi text-xs text-sky-500"></i>
                                        <span>{{ $d['ssid'] }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-xs">-</span>
                                @endif
                            </td>

                            <!-- Redaman Optik RX Power -->
                            <td class="py-3 px-4 text-center">
                                @if (!empty($d['rx_power']))
                                    @php
                                        $numVal = floatval(str_replace(['dBm', ' '], '', $d['rx_power']));
                                        $isWarning = $numVal < -27 || $numVal > -10;
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-mono font-bold {{ $isWarning ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200' }}">
                                        <i class="fa-solid fa-wave-square text-[10px]"></i>
                                        {{ $d['rx_power'] }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>

                            <!-- Suhu ONT -->
                            <td class="py-3 px-4 text-center">
                                @if (!empty($d['temperature']))
                                    @php
                                        $tempVal = floatval(str_replace(['°C', ' '], '', $d['temperature']));
                                        $isHot = $tempVal > 65;
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-mono font-bold {{ $isHot ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-slate-100 text-slate-700' }}">
                                        <i class="fa-solid fa-temperature-half text-[11px] {{ $isHot ? 'text-rose-500' : 'text-slate-400' }}"></i>
                                        {{ $d['temperature'] }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>

                            <!-- Last Inform -->
                            <td class="py-3 px-4 text-xs text-slate-500">
                                <div class="font-mono text-[11px]">{{ $d['last_inform'] }}</div>
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('genieacs.show', $d['id']) }}"
                                        class="p-1.5 rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-600 hover:text-white transition-colors"
                                        title="Detail & Kelola ONT">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                    <button type="button"
                                        onclick="openQuickWifiModal('{{ $d['id'] }}', '{{ addslashes($d['ssid'] !== '-' ? $d['ssid'] : '') }}')"
                                        class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-colors"
                                        title="Ubah Nama & Password Wi-Fi">
                                        <i class="fa-solid fa-key text-xs"></i>
                                    </button>
                                    <button type="button"
                                        onclick="openRebootModal('{{ $d['id'] }}', '{{ $d['serial_number'] }}')"
                                        class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors"
                                        title="Reboot ONT">
                                        <i class="fa-solid fa-power-off text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-satellite-dish text-4xl mb-3 text-slate-300"></i>
                                <p class="text-sm font-semibold text-slate-600">Tidak ada perangkat ONT ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Coba ubah kata kunci pencarian atau periksa integrasi server GenieACS.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($devices->hasPages())
            <div class="p-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500">
                    Menampilkan {{ $devices->firstItem() ?? 0 }} - {{ $devices->lastItem() ?? 0 }} dari {{ $devices->total() }} ONT
                </span>
                <div>
                    {{ $devices->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Modal: Ubah Wi-Fi -->
    <div class="modal-overlay hidden" id="quickWifiModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title flex items-center gap-2">
                    <i class="fa-solid fa-wifi text-sky-600"></i>
                    <span class="text-slate-900 font-bold text-base">Ubah Wi-Fi ONT</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('quickWifiModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="quickWifiForm" onsubmit="submitQuickWifi(event)">
                @csrf
                <input type="hidden" id="quick_wifi_device_id" name="device_id">
                <div class="modal-body p-6 space-y-4">
                    <div class="bg-sky-50 border border-sky-100 rounded-xl p-3 text-xs text-sky-800">
                        <i class="fa-solid fa-circle-info mr-1 text-sky-600"></i>
                        Perubahan nama SSID dan password akan dikirim langsung ke ONT melalui protokol TR-069.
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Nama Wi-Fi (SSID) <span class="text-rose-500">*</span></label>
                        <input type="text" name="ssid" id="quick_wifi_ssid" class="form-input font-medium"
                            maxlength="32" placeholder="Nama Wi-Fi baru" required>
                        <small class="text-slate-400 text-[11px]">Maksimal 32 karakter.</small>
                    </div>

                    <div class="form-group !mb-0">
                        <label class="form-label font-bold text-slate-700">Password Wi-Fi Baru</label>
                        <div class="relative">
                            <input type="password" name="password" id="quick_wifi_password"
                                class="form-input !pr-10 font-mono"
                                minlength="8" maxlength="63" placeholder="Minimal 8 karakter (kosongkan jika tidak diubah)">
                            <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                onclick="togglePasswordVisibility('quick_wifi_password', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-slate-400 text-[11px]">Panjang 8 - 63 karakter WPA2-PSK.</small>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl p-4 flex justify-end gap-2">
                    <button type="button" class="btn-cancel" onclick="closeCustomModal('quickWifiModal')">Batal</button>
                    <button type="submit" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 flex items-center gap-2" id="btn-submit-quick-wifi">
                        <i class="fa-solid fa-paper-plane"></i> Terapkan ke ONT
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Modal: Reboot ONT -->
    <div class="modal-overlay hidden" id="rebootModal">
        <div class="modal-box max-w-md bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title flex items-center gap-2">
                    <i class="fa-solid fa-power-off text-rose-600"></i>
                    <span class="text-slate-900 font-bold text-base">Reboot ONT Pelanggan</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('rebootModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-6 text-center">
                <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fa-solid fa-rotate-right"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-800 mb-1">Restart Perangkat ONT?</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Perintah reboot akan dikirim ke ONT <span class="font-mono font-bold text-slate-800" id="reboot-serial-span"></span>. Koneksi internet pelanggan akan terputus sementara selama 1-2 menit.
                </p>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl p-4 flex justify-end gap-2">
                <button type="button" class="btn-cancel" onclick="closeCustomModal('rebootModal')">Batal</button>
                <button type="button" onclick="executeReboot()" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 flex items-center gap-2" id="btn-submit-reboot">
                    <i class="fa-solid fa-power-off"></i> Ya, Reboot Sekarang
                </button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script>
            let currentRebootDeviceId = null;

            function openQuickWifiModal(deviceId, currentSsid) {
                document.getElementById('quick_wifi_device_id').value = deviceId;
                document.getElementById('quick_wifi_ssid').value = currentSsid || '';
                document.getElementById('quick_wifi_password').value = '';
                
                const modal = document.getElementById('quickWifiModal');
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }

            function openRebootModal(deviceId, serial) {
                currentRebootDeviceId = deviceId;
                document.getElementById('reboot-serial-span').innerText = serial;
                
                const modal = document.getElementById('rebootModal');
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }

            function closeCustomModal(modalId) {
                const modal = document.getElementById(modalId);
                modal.classList.add('hidden');
                modal.style.display = 'none';
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

            function submitQuickWifi(e) {
                e.preventDefault();
                const deviceId = document.getElementById('quick_wifi_device_id').value;
                const ssid = document.getElementById('quick_wifi_ssid').value;
                const password = document.getElementById('quick_wifi_password').value;
                const btn = document.getElementById('btn-submit-quick-wifi');
                const origText = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengirim ke ONT...';

                fetch(`/genieacs/${encodeURIComponent(deviceId)}/wifi`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ssid, password })
                })
                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                .then(res => {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                    if (res.status === 200 && res.body.success) {
                        showToast(res.body.message, 'success');
                        closeCustomModal('quickWifiModal');
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

            function executeReboot() {
                if (!currentRebootDeviceId) return;

                const btn = document.getElementById('btn-submit-reboot');
                const origText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengirim...';

                fetch(`/genieacs/${encodeURIComponent(currentRebootDeviceId)}/reboot`, {
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
                        closeCustomModal('rebootModal');
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
        </script>
    @endsection
</x-layouts.app>
