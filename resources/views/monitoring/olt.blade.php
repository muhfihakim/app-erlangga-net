<x-layouts.app>
    @section('Css')
        <style>
            .olt-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                padding: 1.5rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .05);
            }

            .form-label {
                font-size: 11px;
                font-weight: 700;
                color: #94a3b8;
                text-transform: uppercase;
                margin-bottom: 0.5rem;
                display: block;
            }

            .form-input {
                width: 100%;
                padding: 0.75rem 1rem;
                border: 2px solid #f1f5f9;
                border-radius: 12px;
                font-size: 13px;
                font-weight: 600;
                color: #334155;
                transition: all 0.2s;
                background: #f8fafc;
            }

            .form-input:focus {
                border-color: #0ea5e9;
                background: #fff;
                outline: none;
                box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
            }

            .tab-btn {
                padding: 0.75rem 1.5rem;
                font-size: 12px;
                font-weight: 800;
                color: #64748b;
                border-bottom: 2px solid transparent;
                transition: all 0.2s;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .tab-btn.active {
                color: #0ea5e9;
                border-bottom-color: #0ea5e9;
            }

            .tab-content {
                display: none;
            }

            .tab-content.active {
                display: block;
            }

            /* Stats Card Consistency */
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 1.25rem;
                margin-bottom: 1.25rem;
            }

            .stat-card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                padding: 1.25rem;
                display: flex;
                align-items: center;
                gap: 1rem;
                box-shadow: 0 1px 6px rgba(0, 0, 0, 0.05);
            }

            .stat-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                shrink: 0;
            }

            .stat-val {
                font-size: 20px;
                font-weight: 900;
                color: #0f172a;
                line-height: 1.2;
            }

            .stat-lbl {
                font-size: 11px;
                font-weight: 700;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.025em;
            }
        </style>
    @endsection

    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Manajemen OLT</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Monitoring dan aktivasi perangkat OLT ZTE C300 secara
                real-time.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <div class="flex bg-white rounded-xl border border-slate-200 p-1 shadow-sm h-[46px]">
                @if (auth()->user()->isAdmin())
                    <button class="tab-btn active" data-target="panel-generator">
                        <i class="fa-solid fa-terminal"></i> Generator Skrip
                    </button>
                    <button class="tab-btn" data-target="panel-dashboard" id="btn-tab-dashboard">
                        <i class="fa-solid fa-chart-pie"></i> Dashboard OLT
                    </button>
                @else
                    <button class="tab-btn active" data-target="panel-dashboard" id="btn-tab-dashboard">
                        <i class="fa-solid fa-chart-pie"></i> Dashboard OLT
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Generator Panel -->
    @if (auth()->user()->isAdmin())
        <div id="panel-generator" class="tab-content active">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Input Panel -->
                <div class="lg:col-span-4 h-full">
                    <div class="olt-card h-full overflow-y-auto custom-scrollbar">
                        <div
                            class="flex items-center gap-3 mb-6 sticky top-0 bg-white z-10 pb-2 border-b border-slate-100">
                            <div
                                class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-gears"></i>
                            </div>
                            <div class="flex-1">
                                <h6 class="text-sm font-black text-slate-800 leading-tight">Parameter ONU</h6>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Konfigurasi
                                    Perangkat</p>
                            </div>
                            <button type="button" id="btn_quick_check_uncfg" title="Cek Perangkat Baru"
                                class="bg-amber-100 text-amber-700 hover:bg-amber-200 h-9 px-3 rounded-xl text-[10px] font-black transition-all flex items-center gap-2 border border-amber-200 shadow-sm uppercase">
                                <i class="fa-solid fa-bolt-lightning"></i> Cek Baru
                            </button>
                        </div>


                        <div class="space-y-4">
                            <div>
                                <label class="form-label">Tipe OLT / ONU</label>
                                <select id="olt_type" class="form-input">
                                    <option value="ZTE_ERLANGGA">ZTE ERLANGGA</option>
                                    <option value="ZTE_BBS">ZTE BBS</option>
                                    <option value="HUAWEI" disabled>HUAWEI (Coming Soon)</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">OLT Port</label>
                                    <input type="text" id="olt_port" class="form-input" placeholder="1/2/8"
                                        value="1/2/8">
                                </div>
                                <div>
                                    <label class="form-label">ONU ID</label>
                                    <input type="number" id="onu_id" class="form-input" placeholder="55"
                                        value="1">
                                </div>
                            </div>


                            <div>
                                <label class="form-label">Serial Number (SN)</label>
                                <input type="text" id="onu_sn" class="form-input" placeholder="ZTEGXXXXXXXX">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">ONU Type SN</label>
                                    <input type="text" id="onu_type_sn" class="form-input" value="ALL-GPON">
                                </div>
                                <div>
                                    <label class="form-label">VLAN ID</label>
                                    <input type="number" id="vlan_id" class="form-input" value="525">
                                </div>
                            </div>

                            <hr class="border-slate-100 my-2">

                            <div>
                                <label class="form-label">ID Pelanggan (Name)</label>
                                <input type="text" id="cust_id" class="form-input" placeholder="24000XXX"
                                    value="24000007801">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">PPPoE Username</label>
                                    <input type="text" id="pppoe_user" class="form-input"
                                        placeholder="user@erlangga.net">
                                </div>
                                <div>
                                    <label class="form-label">PPPoE Password</label>
                                    <input type="text" id="pppoe_pass" class="form-input" placeholder="password">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Nama WiFi (SSID)</label>
                                    <input type="text" id="wifi_ssid" class="form-input"
                                        placeholder="NAMA WIFI (SSID)">
                                </div>
                                <div>
                                    <label class="form-label">Password WiFi</label>
                                    <input type="text" id="wifi_pass" class="form-input" placeholder="PASSWORD WIFI">
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Description</label>
                                <input type="text" id="onu_desc" class="form-input" value="2$$2$$">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel -->
                <div class="lg:col-span-8 flex flex-col gap-6 h-full">
                    <!-- Script Panel -->
                    <div class="olt-card flex flex-col" style="flex: 1.2; min-height: 350px;">
                        <div class="flex items-center justify-between mb-4 shrink-0">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <i class="fa-solid fa-code"></i>
                                </div>
                                <div>
                                    <h6 class="text-sm font-black text-slate-800 leading-tight">Generated Script</h6>
                                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Siap
                                        digunakan
                                        di Terminal/Putty</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="copyScript()"
                                    class="btn-primary-admin !py-2.5 !px-5 !text-[12px] !bg-indigo-600 hover:!bg-indigo-700 border-none shadow-sm flex items-center gap-2">
                                    <i class="fa-solid fa-copy"></i> SALIN SKRIP
                                </button>
                            </div>
                        </div>

                        <div class="relative flex-1 min-h-0">
                            <textarea id="script_output" readonly
                                class="w-full h-full p-4 bg-slate-50 text-slate-800 font-mono text-[11px] leading-relaxed rounded-xl border-2 border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none shadow-inner custom-scrollbar"></textarea>
                        </div>
                    </div>

                    <!-- Terminal Output -->
                    <div class="rounded-2xl overflow-hidden border-2 border-slate-800 bg-[#0c0c0c] shadow-xl flex flex-col"
                        style="flex: 1; min-height: 250px;">
                        <div class="bg-slate-800 px-4 py-2 flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-terminal text-emerald-400 text-xs"></i>
                                <span class="text-[10px] text-slate-300 font-mono font-bold tracking-wider ml-1">OLT Terminal Output</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <button id="btn_connect_terminal"
                                    class="bg-emerald-500 hover:bg-emerald-400 text-slate-900 px-3 py-1 rounded-lg text-[10px] font-black shadow-sm transition-colors flex items-center gap-1">
                                    <i class="fa-solid fa-plug"></i> HUBUNGKAN
                                </button>
                                <button id="btn_clear_terminal"
                                    class="text-slate-400 hover:text-white transition-colors" title="Clear Terminal">
                                    <i class="fa-solid fa-eraser text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <textarea id="terminal_output"
                            class="w-full h-full p-4 bg-transparent text-[#00ff00] font-mono text-[11px] leading-relaxed outline-none focus:ring-1 focus:ring-emerald-500/50 resize-none custom-scrollbar"
                            placeholder="Ketik perintah/paste skrip di sini, lalu tekan Enter..."></textarea>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Dashboard Panel -->
    <div id="panel-dashboard" class="tab-content {{ !auth()->user()->isAdmin() ? 'active' : '' }}">
        <!-- Stats cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                    <i class="fa-solid fa-signal"></i>
                </div>
                <div>
                    <div class="stat-val" id="stat_power_good">0</div>
                    <div class="stat-lbl">Redaman Bagus</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <div class="stat-val" id="stat_power_fair">0</div>
                    <div class="stat-lbl">Kurang Baik</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#fef2f2;color:#dc2626;">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
                <div>
                    <div class="stat-val" id="stat_power_poor">0</div>
                    <div class="stat-lbl">Redaman Buruk</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#f1f5f9;color:#64748b;">
                    <i class="fa-solid fa-plug-circle-xmark"></i>
                </div>
                <div>
                    <div class="stat-val" id="stat_no_signal">0</div>
                    <div class="stat-lbl">No Signal</div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="table-toolbar">
            <div class="w-full">
                <div class="filter-group">
                    <!-- Filter Interface -->
                    <div style="flex: 1; min-width: 180px;">
                        <label class="form-label-custom">Filter Interface</label>
                        <select id="filter-interface" class="form-input-custom w-full">
                            <option value="">Semua Interface</option>
                            <option value="1/2/">Interface 1/2</option>
                            <option value="1/4/">Interface 1/4</option>
                        </select>
                    </div>

                    <!-- Live Search -->
                    <div style="flex: 1.5; min-width: 200px;">
                        <label class="form-label-custom">Pencarian Cepat</label>
                        <div class="search-wrap !max-w-full">
                            <i class="fa-solid fa-magnifying-glass search-icon"></i>
                            <input type="search" class="search-input" id="olt-livesearch"
                                style="padding: 9px 12px 9px 36px; height: 40px; border-radius: 10px; font-weight: 600;"
                                placeholder="Cari SN, Port, atau Status..." autocomplete="off" />
                        </div>
                    </div>

                    <!-- Sync Button -->
                    <button id="btn_sync_global"
                        class="btn-primary-admin !h-10 !px-6 !text-[13px] !w-fit flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-rotate"></i> SYNC SELURUH OLT
                    </button>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="table-card">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h6 id="dashboard_table_title" class="text-sm font-black text-slate-800">Daftar Perangkat ONU</h6>
                <span id="dashboard_record_count"
                    class="bg-slate-100 text-slate-500 text-[10px] font-bold px-2 py-1 rounded-lg uppercase">0
                    Records</span>
            </div>

            <div class="table-responsive">
                <table id="dashboard-onu-table" class="admin-table">
                    <thead>
                        <tr id="dashboard_table_header">
                            <th style="text-align: center; width: 60px;">#</th>
                            <th style="text-align: center;">PON Port</th>
                            <th style="text-align: center;">Serial Number (SN)</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: center;">RX Power</th>
                            <th style="text-align: center; width: 150px;">Opsi</th>
                        </tr>
                    </thead>
                    <tbody id="dashboard-onu-body">
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 4rem; color: #94a3b8;">
                                <div class="flex flex-col items-center gap-3">
                                    <i class="fa-solid fa-inbox text-5xl opacity-20"></i>
                                    <p class="font-bold text-sm">Klik 'SYNC SELURUH OLT' untuk melihat data</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ONU Detail Modal -->
    <div class="modal-overlay" id="onuDetailModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-circle-info text-primary"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Detail Status ONU</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('onuDetailModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-8 bg-white">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- WAN Info -->
                    <div class="space-y-4">
                        <h6
                            class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-l-4 border-sky-500 pl-3">
                            Informasi WAN (PPPoE)</h6>
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-bold text-slate-400">USERNAME</span>
                                <span id="det_wan_user" class="text-xs font-black text-slate-700">-</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-bold text-slate-400">CURRENT IP</span>
                                <span id="det_wan_ip" class="text-xs font-black text-indigo-600">-</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-bold text-slate-400">VLAN</span>
                                <span id="det_wan_user_vlan" class="text-xs font-black text-slate-700">-</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-bold text-slate-400">RX POWER (ATT)</span>
                                <span id="det_rx_power" class="text-xs font-black text-rose-600">-</span>
                            </div>
                            <div class="flex justify-between items-center border-t border-slate-200 pt-2 mt-2">
                                <span class="text-[11px] font-bold text-slate-400">STATUS WAN</span>
                                <span id="det_wan_status" class="badge !text-[9px]">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Traffic Stats -->
                    <div class="space-y-4">
                        <h6
                            class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-l-4 border-emerald-500 pl-3">
                            Statistik Trafik</h6>
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-bold text-slate-400">INPUT RATE</span>
                                <span id="det_stat_in_rate" class="text-xs font-black text-emerald-600">-</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-bold text-slate-400">OUTPUT RATE</span>
                                <span id="det_stat_out_rate" class="text-xs font-black text-emerald-600">-</span>
                            </div>
                            <div class="flex justify-between items-center border-t border-slate-200 pt-2 mt-2">
                                <span class="text-[11px] font-bold text-slate-400">TOTAL DOWNLOAD</span>
                                <span id="det_stat_in_total" class="text-xs font-black text-slate-700">-</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-bold text-slate-400">TOTAL UPLOAD</span>
                                <span id="det_stat_out_total" class="text-xs font-black text-slate-700">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button type="button" class="btn-cancel w-full" onclick="closeCustomModal('onuDetailModal')">
                    <i class="fa-solid fa-xmark"></i> Tutup Detail
                </button>
            </div>
        </div>
    </div>

    <!-- Unconfigured Selection Modal -->
    <div class="modal-overlay" id="selectUncfgModal">
        <div class="modal-box max-w-xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-list-ul text-amber-500"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Pilih Perangkat Baru</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('selectUncfgModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-6 bg-white">
                <p class="text-xs font-bold text-slate-400 uppercase mb-4 tracking-wider">Perangkat yang belum
                    terdaftar di
                    OLT:</p>
                <div id="uncfg_selection_list" class="space-y-3">
                    <!-- List items will be injected here -->
                </div>
            </div>
            <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button type="button" class="btn-cancel w-full" onclick="closeCustomModal('selectUncfgModal')">
                    <i class="fa-solid fa-xmark"></i> Batal
                </button>
            </div>
        </div>
    </div>


    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            // Tab System
            $('.tab-btn').click(function() {
                const target = $(this).data('target');
                $('.tab-btn').removeClass('active');
                $(this).addClass('active');
                $('.tab-content').removeClass('active');
                $('#' + target).addClass('active');
            });

            let dashboardTable;

            function generateScript() {
                const type = $('#olt_type').val();
                const port = $('#olt_port').val() || '1/2/8';
                const id = $('#onu_id').val() || '1';
                const sn = $('#onu_sn').val() || 'ZTEGXXXXXXXX';
                const vlan = $('#vlan_id').val() || '525';
                const typeSn = $('#onu_type_sn').val() || 'ALL-GPON';
                const desc = $('#onu_desc').val() || '2$$2$$';
                const custId = $('#cust_id').val() || '24000007801';
                const user = $('#pppoe_user').val() || 'USER@erlangga.net';
                const pass = $('#pppoe_pass').val() || 'PASSWORD';
                const ssid = $('#wifi_ssid').val() || 'NAMA_WIFI';
                const wifiPass = $('#wifi_pass').val() || 'KATA_SANDI_WIFI';

                let script = '';

                if (type === 'ZTE_ERLANGGA') {
                    script = `config t
interface gpon-olt_${port}
onu ${id} type ${typeSn} sn ${sn}
!
interface gpon-onu_${port}:${id}
  name ${custId}
  description ${desc}
  tcont 1 name PPPoE profile PPPoE
  gemport 1 name PPPoE tcont 1
  gemport 1 traffic-limit upstream PPPoE downstream PPPoE
  encrypt 1 enable downstream
  service-port 1 vport 1 user-vlan ${vlan} vlan ${vlan}
  service-port 1 description PPPoE
!
pon-onu-mng gpon-onu_${port}:${id}
service PPPoE gemport 1 cos 0 vlan ${vlan}
wan-ip 1 mode pppoe username ${user} password ${pass} vlan-profile Hotspot host 1
wan-ip 1 ping-response enable traceroute-response enable
security-mgmt 1 state enable mode forward protocol web
!
pon-onu-mng gpon-onu_${port}:${id}
  ssid ctrl wifi_0/1 name ${ssid}
  ssid auth wpa wifi_0/1 wpa-wpa2-psk key ${wifiPass}
  tr069-mg 1 acs http://192.168.21.201:7547 validate basic username admin password admin state unlock
  exit
!
end
write

show pon power attenuation gpon-onu_${port}:${id}
show gpon onu state gpon-olt_${port}`;
                } else if (type === 'ZTE_BBS') {
                    script = `config t
interface gpon-olt_${port}
onu ${id} type ${typeSn} sn ${sn}
!
interface gpon-onu_${port}:${id}
  name ${custId}
  description ${desc}
  tcont 1 name PPPoE profile PPPoE
  tcont 2 name Hotspot profile Hotspot
  gemport 1 name PPPoE tcont 1
  gemport 1 traffic-limit upstream PPPoE downstream PPPoE
  encrypt 1 enable downstream
  gemport 2 name Hotspot tcont 1
  gemport 2 traffic-limit upstream Hotspot downstream Hotspot
  encrypt 2 enable downstream
  service-port 1 vport 1 user-vlan 252 vlan 252
  service-port 1 description PPPoE
  service-port 2 vport 2 user-vlan ${vlan} vlan ${vlan}
  service-port 2 description Hotspot
!
pon-onu-mng gpon-onu_${port}:${id}
service PPPoE gemport 1 cos 0 vlan 252
service Hotspot1 gemport 2 cos 0 vlan ${vlan}
wan-ip 1 mode pppoe username ${user} password ${pass} vlan-profile Hotspot1 host 1
wan-ip 1 ping-response enable traceroute-response enable
security-mgmt 1 state enable mode forward protocol web
!
pon-onu-mng gpon-onu_${port}:${id}
  ssid ctrl wifi_0/1 name ${ssid}
  ssid auth wpa wifi_0/1 wpa-wpa2-psk key ${wifiPass}
  tr069-mg 1 acs http://192.168.21.201:7547 validate basic username admin password admin state unlock
  exit
!
end
write

show pon power attenuation gpon-onu_${port}:${id}
show gpon onu state gpon-olt_${port}`;
                }

                $('#script_output').val(script);
            }

            function copyScript() {
                const textarea = document.getElementById('script_output');
                textarea.select();
                document.execCommand('copy');
                showToast('Skrip telah disalin ke clipboard.', 'success');
            }

            function useForActivation(port, sn) {
                $('#olt_port').val(port);
                $('#onu_sn').val(sn);

                // Auto search next ID for this port
                showToast('Mencari ONU ID tersedia...', 'success');
                $.ajax({
                    url: '/api/olt/check-next-id',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        olt_port: port
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#onu_id').val(res.next_id);
                            generateScript();
                            showToast('Data ONU & Next ID dimuat.', 'success');
                        } else {
                            showToast(res.error, 'error');
                        }
                    },
                    error: function() {
                        showToast('Gagal mencari ONU ID.', 'error');
                    }
                });

                $('.tab-btn[data-target="panel-generator"]').click();
                closeCustomModal('selectUncfgModal');
            }

            function checkOnuStatus(port, onuId) {
                // Show loading state in modal or toast
                showToast('Mengambil data detail ONU...', 'success');

                $.ajax({
                    url: '/api/olt/onu-details',
                    type: 'GET',
                    data: {
                        port: port,
                        onu_id: onuId
                    },
                    success: function(res) {
                        if (res.success) {
                            // Fill WAN Info
                            $('#det_wan_user').text(res.wan_info.username);
                            $('#det_wan_ip').text(res.wan_info.ip);
                            $('#det_wan_user_vlan').text(res.wan_info.vlan);
                            $('#det_rx_power').text(res.rx_power);

                            const $status = $('#det_wan_status');
                            $status.text(res.wan_info.status);
                            $status.removeClass('bg-emerald-100 text-emerald-700 bg-rose-100 text-rose-700');
                            if (res.wan_info.status === 'CONNECTED') {
                                $status.addClass('bg-emerald-100 text-emerald-700');
                            } else {
                                $status.addClass('bg-rose-100 text-rose-700');
                            }

                            // Fill Traffic Stats
                            $('#det_stat_in_rate').text(res.stats.input_rate);
                            $('#det_stat_out_rate').text(res.stats.output_rate);
                            $('#det_stat_in_total').text(res.stats.input_total);
                            $('#det_stat_out_total').text(res.stats.output_total);

                            // Open Modal
                            openCustomModal('onuDetailModal');
                            $('#terminal_output').val(res.raw_output);
                            $('#terminal_output').scrollTop($('#terminal_output')[0].scrollHeight);
                        } else {
                            showToast(res.error, 'error');
                        }
                    },
                    error: function() {
                        showToast('Gagal menghubungi OLT.', 'error');
                    }
                });
            }

            $(document).ready(function() {
                $('input, select').on('input change', generateScript);
                generateScript(); // Initial call

                // Handle Quick Check Unconfigured in Generator
                $('#btn_quick_check_uncfg').click(function() {
                    const $btn = $(this);
                    const originalHtml = $btn.html();
                    $btn.html('<i class="fa-solid fa-spinner fa-spin"></i>').prop('disabled', true);
                    showToast('Mencari perangkat unconfigured...', 'success');

                    $.ajax({
                        url: '/api/olt/dashboard-data',
                        type: 'GET',
                        success: function(res) {
                            if (res.success && res.unconfigured.length > 0) {
                                if (res.unconfigured.length === 1) {
                                    // If only one, auto-select it
                                    const onu = res.unconfigured[0];
                                    useForActivation(onu.port, onu.sn);
                                } else {
                                    // If multiple, show selection modal
                                    let html = '';
                                    res.unconfigured.forEach(onu => {
                                        html += `
                                            <div onclick="useForActivation('${onu.port}', '${onu.sn}')" 
                                                class="flex items-center justify-between p-4 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 rounded-2xl cursor-pointer transition-all group">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform">
                                                        <i class="fa-solid fa-bolt"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-black text-slate-800">${onu.sn}</div>
                                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Port: ${onu.port}</div>
                                                    </div>
                                                </div>
                                                <i class="fa-solid fa-chevron-right text-slate-300 group-hover:text-indigo-500 transition-colors"></i>
                                            </div>
                                        `;
                                    });
                                    $('#uncfg_selection_list').html(html);
                                    openCustomModal('selectUncfgModal');
                                    showToast('Ditemukan ' + res.unconfigured.length +
                                        ' perangkat.', 'info');
                                }
                                $('#terminal_output').val(res.raw_output);
                            } else {
                                showToast('Tidak ada perangkat baru (unconfigured) ditemukan.',
                                    'info');
                            }
                        },
                        error: function() {
                            showToast('Gagal menghubungi OLT.', 'error');
                        },
                        complete: function() {
                            $btn.html(originalHtml).prop('disabled', false);
                        }
                    });
                });


                // Handle Sync Global (All ONUs)
                $('#btn_sync_global').click(function() {
                    const $btn = $(this);
                    const originalHtml = $btn.html();
                    $btn.html('<i class="fa-solid fa-spinner fa-spin mr-1"></i> SYNCING...').prop('disabled',
                        true);

                    $.ajax({
                        url: '/api/olt/dashboard-data',
                        type: 'GET',
                        success: function(res) {
                            if (res.success) {
                                $('#dashboard_table_title').text('Status Seluruh ONU OLT');

                                if (dashboardTable) {
                                    dashboardTable.destroy();
                                }

                                let html = '';
                                let count = 0;
                                let stats = {
                                    good: 0,
                                    fair: 0,
                                    poor: 0,
                                    nosignal: 0
                                };

                                // 1. Add Unconfigured (Priority)
                                res.unconfigured.forEach((onu) => {
                                    count++;
                                    stats.nosignal++;
                                    html += `
                                    <tr class="bg-amber-50/30">
                                        <td style="text-align: center; color: #94a3b8; font-weight: 700;">${count}</td>
                                        <td style="text-align: center; font-weight: 600;">${onu.port}</td>
                                        <td style="text-align: center; font-weight: 700; color: #0f172a;">${onu.sn}</td>
                                        <td style="text-align: center;">
                                            <span class="badge" style="background: #fef3c7; color: #d97706; font-size: 10px;">${onu.status}</span>
                                        </td>
                                        <td style="text-align: center; font-weight: 700; color: #64748b;">-</td>
                                        <td style="text-align: center;">
                                            <button onclick="useForActivation('${onu.port}', '${onu.sn}')" 
                                                class="topbar-icon-btn !w-fit !px-4 !h-9 !rounded-xl !bg-emerald-50 hover:!bg-emerald-100 text-emerald-600 transition-all flex items-center gap-2 shadow-none border-none mx-auto">
                                                <i class="fa-solid fa-bolt"></i>
                                                <span class="text-[10px] font-black uppercase">Aktivasi</span>
                                            </button>
                                        </td>
                                    </tr>
                                `;
                                });

                                // 2. Add Configured
                                res.configured.forEach((onu) => {
                                    count++;
                                    let badgeColor = '#f1f5f9';
                                    let textColor = '#64748b';
                                    if (onu.status === 'WORKING') {
                                        badgeColor = '#dcfce7';
                                        textColor = '#15803d';
                                    } else if (onu.status === 'LOS') {
                                        badgeColor = '#fef2f2';
                                        textColor = '#dc2626';
                                    }

                                    let powerColor = '#64748b';
                                    if (onu.rx_power !== '-' && onu.rx_power !==
                                        'no signal') {
                                        const pVal = parseFloat(onu.rx_power);
                                        if (pVal < -27) {
                                            powerColor = '#ef4444';
                                            stats.poor++;
                                        } else if (pVal < -24) {
                                            powerColor = '#f59e0b';
                                            stats.fair++;
                                        } else {
                                            powerColor = '#10b981';
                                            stats.good++;
                                        }
                                    } else {
                                        stats.nosignal++;
                                    }

                                    html += `
                                    <tr>
                                        <td style="text-align: center; color: #94a3b8; font-weight: 700;">${count}</td>
                                        <td style="text-align: center; font-weight: 600;">${onu.port}:${onu.onu_id}</td>
                                        <td style="text-align: center; font-weight: 700; color: #0f172a;">${onu.sn}</td>
                                        <td style="text-align: center;">
                                            <span class="badge" style="background: ${badgeColor}; color: ${textColor}; font-size: 10px;">${onu.status}</span>
                                        </td>
                                        <td style="text-align: center; font-weight: 800; color: ${powerColor}; font-size: 11px;">
                                            ${onu.rx_power}
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="flex items-center justify-center gap-2">
                                                <button onclick="checkOnuStatus('${onu.port}', '${onu.onu_id}')" 
                                                    class="topbar-icon-btn !w-fit !px-4 !h-9 !rounded-xl !bg-indigo-50 hover:!bg-indigo-100 text-indigo-600 transition-all flex items-center gap-2 shadow-none border-none">
                                                    <i class="fa-solid fa-chart-line"></i>
                                                    <span class="text-[10px] font-black uppercase">Cek Status</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                });

                                if (count === 0) {
                                    html =
                                        '<tr><td colspan="6" style="text-align: center; padding: 4rem; color: #94a3b8;">Tidak ada data ONU ditemukan.</td></tr>';
                                }

                                $('#dashboard-onu-body').html(html);
                                $('#dashboard_record_count').text(count + ' Records');

                                // Update Stats
                                $('#stat_power_good').text(stats.good);
                                $('#stat_power_fair').text(stats.fair);
                                $('#stat_power_poor').text(stats.poor);
                                $('#stat_no_signal').text(stats.nosignal);

                                // Re-initialize DataTables
                                dashboardTable = $('#dashboard-onu-table').DataTable({
                                    "pageLength": 15,
                                    "lengthChange": false,
                                    "ordering": true,
                                    "info": false,
                                    "responsive": true,
                                    "dom": 'tpir',
                                    "language": {
                                        "zeroRecords": "Data tidak ditemukan",
                                        "paginate": {
                                            "previous": '<i class="fa-solid fa-chevron-left"></i>',
                                            "next": '<i class="fa-solid fa-chevron-right"></i>'
                                        }
                                    }
                                });

                                showToast('Status seluruh OLT berhasil disinkronkan.', 'success');
                                $('#terminal_output').val(res.raw_output);
                                $('#terminal_output').scrollTop($('#terminal_output')[0]
                                    .scrollHeight);
                            } else {
                                showToast(res.error, 'error');
                            }
                        },
                        error: function() {
                            showToast('Koneksi OLT bermasalah.', 'error');
                        },
                        complete: function() {
                            $btn.html(originalHtml).prop('disabled', false);
                        }
                    });
                });

                // Handle Live Search
                $('#olt-livesearch').on('keyup', function() {
                    if (dashboardTable) {
                        dashboardTable.search(this.value).draw();
                    }
                });

                // Handle Interface Filter
                $('#filter-interface').on('change', function() {
                    if (dashboardTable) {
                        // Filter based on the "PON Port" column (index 1)
                        dashboardTable.column(1).search(this.value).draw();
                    }
                });

                // Handle Clear Terminal
                $('#btn_clear_terminal').click(function() {
                    $('#terminal_output').val('');
                    termState = 'disconnected';
                });

                let termState = 'disconnected';
                const oltIp = '136.1.1.100';

                // Handle Connect
                $('#btn_connect_terminal').click(function() {
                    const $textarea = $('#terminal_output');
                    $textarea.val(`Trying ${oltIp}...\nConnected to ${oltIp}.\nEscape character is '^]'.\n`);
                    $textarea.focus();
                    termState = 'connecting';

                    $.ajax({
                        url: '/api/olt/connect',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.success && res.banner) {
                                $textarea.val($textarea.val() + res.banner);
                                termState = 'username';
                            } else {
                                $textarea.val($textarea.val() + '\nFailed to get banner.');
                                termState = 'disconnected';
                            }
                            $textarea.scrollTop($textarea[0].scrollHeight);
                        },
                        error: function() {
                            $textarea.val($textarea.val() + '\nConnection failed.');
                            termState = 'disconnected';
                        }
                    });
                });

                const zteCommands = [
                    "show", "config", "interface", "gpon-olt", "gpon-onu", "pon-onu-mng",
                    "ssid", "tr069-mg", "service-port", "wan-ip", "security-mgmt", "exit", "write", "end",
                    "show gpon onu state", "show gpon onu baseinfo", "show pon power olt-rx"
                ];

                // Handle Interactive Terminal
                $('#terminal_output').on('keydown', function(e) {
                    const val = $(this).val();
                    const start = this.selectionStart;
                    const end = this.selectionEnd;

                    // Prevent editing past the prompt
                    const lastPromptIndex = Math.max(
                        val.lastIndexOf('Username:'),
                        val.lastIndexOf('Password:'),
                        val.lastIndexOf('ZXAN#')
                    );

                    let protectedLength = 0;
                    if (val.lastIndexOf('ZXAN#') === lastPromptIndex && lastPromptIndex !== -1)
                        protectedLength = lastPromptIndex + 5;
                    else if (val.lastIndexOf('Username:') === lastPromptIndex && lastPromptIndex !== -1)
                        protectedLength = lastPromptIndex + 9;
                    else if (val.lastIndexOf('Password:') === lastPromptIndex && lastPromptIndex !== -1)
                        protectedLength = lastPromptIndex + 9;

                    if (start < protectedLength) {
                        if (!e.ctrlKey && !e.metaKey && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight' && e
                            .key !== 'ArrowUp' && e.key !== 'ArrowDown' && e.key !== 'c') {
                            e.preventDefault();
                            this.selectionStart = this.selectionEnd = val.length;
                        }
                    } else if (start === protectedLength && e.key === 'Backspace') {
                        e.preventDefault();
                    }

                    if (e.key === 'Tab') {
                        e.preventDefault();
                        const val = $(this).val();
                        const start = this.selectionStart;
                        const end = this.selectionEnd;

                        const textBeforeCursor = val.substring(0, start);
                        const lines = textBeforeCursor.split('\n');
                        let lastLine = lines[lines.length - 1];
                        lastLine = lastLine.replace(/^ZXAN#/, '').trimStart();

                        if (lastLine.length > 0) {
                            const matches = zteCommands.filter(c => c.startsWith(lastLine));
                            if (matches.length === 1) {
                                const remainder = matches[0].substring(lastLine.length);
                                const newVal = val.substring(0, start) + remainder + " " + val.substring(end);
                                $(this).val(newVal);
                                this.selectionStart = this.selectionEnd = start + remainder.length + 1;
                            } else if (matches.length > 1) {
                                const suggestion = "\n" + matches.join("   ") + "\nZXAN#" + lastLine;
                                const newVal = val.substring(0, start) + suggestion + val.substring(end);
                                $(this).val(newVal);
                                this.selectionStart = this.selectionEnd = start + suggestion.length;
                            }
                        }
                        return;
                    }

                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        const val = $(this).val();
                        const start = this.selectionStart;
                        const end = this.selectionEnd;

                        let scriptToRun = '';
                        if (start !== end) {
                            scriptToRun = val.substring(start, end);
                        } else {
                            const lines = val.substring(0, start).split('\n');
                            scriptToRun = lines[lines.length - 1];
                            // Remove prompt strings from the line
                            scriptToRun = scriptToRun.replace(/^Username:/, '')
                                .replace(/^Password:/, '')
                                .replace(/^ZXAN#/, '')
                                .trim();
                        }

                        if (termState === 'username') {
                            window.termUsername = scriptToRun;
                            $(this).val(val + '\nPassword:');
                            $(this).scrollTop($(this)[0].scrollHeight);
                            termState = 'password';
                            return;
                        }

                        if (termState === 'password') {
                            const termPassword = scriptToRun;
                            const $textarea = $(this);
                            $textarea.val(val + '\n> Verifying...');
                            $textarea.scrollTop($textarea[0].scrollHeight);

                            $.ajax({
                                url: '/api/olt/verify-login',
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    username: window.termUsername,
                                    password: termPassword
                                },
                                success: function(res) {
                                    let currentVal = $textarea.val().replace('\n> Verifying...',
                                    '');
                                    if (res.success) {
                                        $textarea.val(currentVal + '\n\nZXAN#');
                                        termState = 'connected';
                                    } else {
                                        $textarea.val(currentVal + '\n' + res.error +
                                        '\nUsername:');
                                        termState = 'username';
                                    }
                                    $textarea.scrollTop($textarea[0].scrollHeight);
                                },
                                error: function() {
                                    let currentVal = $textarea.val().replace('\n> Verifying...',
                                    '');
                                    $textarea.val(currentVal + '\nConnection Error\nUsername:');
                                    termState = 'username';
                                    $textarea.scrollTop($textarea[0].scrollHeight);
                                }
                            });
                            return;
                        }

                        if (!scriptToRun || scriptToRun.trim() === '') {
                            if (termState === 'connected') {
                                $(this).val(val + '\nZXAN#');
                                $(this).scrollTop($(this)[0].scrollHeight);
                            }
                            return;
                        }

                        const $textarea = $(this);
                        $textarea.val(val + '\n');
                        $textarea.scrollTop($textarea[0].scrollHeight);

                        $.ajax({
                            url: '/api/olt/execute',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                script: scriptToRun
                            },
                            success: function(res) {
                                let currentVal = $textarea.val();
                                if (res.success) {
                                    $textarea.val(currentVal + res.log + '\nZXAN#');
                                } else {
                                    $textarea.val(currentVal + 'Error: ' + res.error + '\nZXAN#');
                                }
                                $textarea.scrollTop($textarea[0].scrollHeight);
                            },
                            error: function() {
                                let currentVal = $textarea.val();
                                $textarea.val(currentVal + 'Connection Error\nZXAN#');
                                $textarea.scrollTop($textarea[0].scrollHeight);
                            }
                        });
                    }
                });
            });
        </script>
    @endsection
</x-layouts.app>
