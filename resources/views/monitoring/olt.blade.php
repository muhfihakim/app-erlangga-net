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
            .script-area {
                width: 100%;
                height: 500px;
                padding: 1.5rem;
                background: #f8fafc;
                color: #1e293b;
                font-family: 'JetBrains Mono', 'Fira Code', monospace;
                font-size: 11px;
                line-height: 1.6;
                border-radius: 16px;
                border: 2px solid #e2e8f0;
                resize: none;
                box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
            }
        </style>
    @endsection

    <div style="margin-bottom: 1.5rem;">
        <h1 style="font-size:22px;font-weight:900;margin:0;">Manajemen OLT</h1>
        <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Generator skrip aktivasi ONU untuk berbagai tipe OLT.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Input Panel -->
        <div class="lg:col-span-4 space-y-6">
            <div class="olt-card">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center">
                        <i class="fa-solid fa-gears"></i>
                    </div>
                    <div>
                        <h6 class="text-sm font-black text-slate-800">Parameter ONU</h6>
                        <p class="text-[10px] text-slate-500 font-bold uppercase">Konfigurasi Perangkat</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="form-label">Tipe OLT / ONU</label>
                        <select id="olt_type" class="form-input">
                            <option value="ZTE_ERLANGGA">ZTE ERLANGGA</option>
                            <option value="ZTE_BBS">ZTE BBS</option>
                            <option value="HUAWEI">HUAWEI (Coming Soon)</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">OLT Port</label>
                            <input type="text" id="olt_port" class="form-input" placeholder="1/2/8" value="1/2/8">
                        </div>
                        <div>
                            <label class="form-label">ONU ID</label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="onu_id" class="form-input" placeholder="55" value="1">
                                <button type="button" id="btn_check_id" class="bg-indigo-100 text-indigo-700 hover:bg-indigo-200 px-3 py-2 rounded-xl text-xs font-bold transition-colors flex items-center justify-center min-w-[40px]">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
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
                        <input type="text" id="cust_id" class="form-input" placeholder="24000XXX" value="24000007801">
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="form-label">PPPoE Username</label>
                            <input type="text" id="pppoe_user" class="form-input" placeholder="user@erlangga.net">
                        </div>
                        <div>
                            <label class="form-label">PPPoE Password</label>
                            <input type="text" id="pppoe_pass" class="form-input" placeholder="password">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Description</label>
                        <input type="text" id="onu_desc" class="form-input" value="2$$2$$">
                    </div>
                </div>
            </div>
        </div>

        <!-- Script Panel -->
        <div class="lg:col-span-8 space-y-6">
            <div class="olt-card h-full">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-code"></i>
                        </div>
                        <div>
                            <h6 class="text-sm font-black text-slate-800">Generated Script</h6>
                            <p class="text-[10px] text-slate-500 font-bold uppercase">Siap digunakan di Terminal/Putty</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="copyScript()" class="btn-primary-admin !py-2 !px-4 !text-[11px] !bg-emerald-500 hover:!bg-emerald-600 border-none shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-copy"></i> SALIN
                        </button>
                        <button id="btn_execute" class="btn-primary-admin !py-2 !px-4 !text-[11px] !bg-rose-500 hover:!bg-rose-600 border-none shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-rocket"></i> EKSEKUSI DI OLT
                        </button>
                    </div>
                </div>

                <div class="relative mb-4">
                    <textarea id="script_output" readonly class="script-area"></textarea>
                </div>
                
                <!-- Terminal Output -->
                <div class="rounded-2xl overflow-hidden border-2 border-slate-800 bg-[#0c0c0c] mt-4 shadow-xl">
                    <div class="bg-slate-800 px-4 py-2 flex items-center gap-2">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono ml-2">OLT Terminal Output</span>
                    </div>
                    <textarea id="terminal_output" readonly class="w-full h-[250px] p-4 bg-transparent text-[#00ff00] font-mono text-[11px] leading-relaxed outline-none resize-none" placeholder="Menunggu eksekusi perintah..."></textarea>
                </div>

                <div class="mt-4 p-4 rounded-2xl bg-amber-50 border border-amber-100">
                    <div class="flex gap-3">
                        <i class="fa-solid fa-circle-exclamation text-amber-500 mt-1"></i>
                        <p class="text-[11px] text-amber-700 leading-relaxed font-medium">
                            <strong>Tips Integrasi Putty:</strong> Pastikan Anda sudah login ke OLT melalui Putty. Salin skrip di atas, lalu klik kanan pada jendela Putty untuk melakukan paste. Tekan enter sampai baris terakhir dijalankan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('Scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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

            Swal.fire({
                icon: 'success',
                title: 'Tersalin!',
                text: 'Skrip telah disalin ke clipboard.',
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }

        $(document).ready(function() {
            $('input, select').on('input change', generateScript);
            generateScript(); // Initial call

            // Handle Check Next ONU ID
            $('#btn_check_id').click(function() {
                const port = $('#olt_port').val();
                if (!port) {
                    Swal.fire('Error', 'Silakan isi OLT Port terlebih dahulu.', 'error');
                    return;
                }

                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.html('<i class="fa-solid fa-spinner fa-spin"></i>').prop('disabled', true);

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
                            Swal.fire('Berhasil', 'Next ONU ID ditemukan: ' + res.next_id, 'success');
                            
                            // Optional: Show raw output in terminal
                            $('#terminal_output').val(res.raw_output);
                        } else {
                            Swal.fire('Error', res.error, 'error');
                        }
                    },
                    error: function(err) {
                        Swal.fire('Error', 'Gagal terhubung ke server atau OLT.', 'error');
                    },
                    complete: function() {
                        $btn.html(originalHtml).prop('disabled', false);
                    }
                });
            });

            // Handle Execute Script
            $('#btn_execute').click(function() {
                const script = $('#script_output').val();
                if (!script) {
                    Swal.fire('Error', 'Skrip kosong.', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Eksekusi',
                    text: "Apakah Anda yakin ingin menjalankan skrip ini langsung ke OLT?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Ya, Jalankan!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const $btn = $(this);
                        const originalHtml = $btn.html();
                        $btn.html('<i class="fa-solid fa-spinner fa-spin"></i>').prop('disabled', true);
                        $('#terminal_output').val('Menghubungi OLT dan menjalankan perintah...\nMohon tunggu...');

                        $.ajax({
                            url: '/api/olt/execute',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                script: script
                            },
                            success: function(res) {
                                if (res.success) {
                                    $('#terminal_output').val(res.log);
                                    Swal.fire('Berhasil', 'Skrip selesai dieksekusi.', 'success');
                                } else {
                                    $('#terminal_output').val('ERROR: ' + res.error);
                                    Swal.fire('Error', res.error, 'error');
                                }
                            },
                            error: function(err) {
                                $('#terminal_output').val('Koneksi gagal atau timeout.');
                                Swal.fire('Error', 'Gagal terhubung ke server atau OLT.', 'error');
                            },
                            complete: function() {
                                $btn.html(originalHtml).prop('disabled', false);
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endsection
</x-layouts.app>
