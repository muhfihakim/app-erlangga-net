<x-layouts.app>
    <!-- Page header -->
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Manajemen Akun</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola akses pengguna dan pengaturan keamanan akun.</p>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="button" class="btn-primary-admin !h-9 !px-4 text-xs font-semibold"
                onclick="openCustomModal('addUserModal')">
                Tambah Akun
            </button>
        </div>
    </div>

    <!-- Stats cards -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalUsers }}</div>
                <div class="stat-lbl">Total Pengguna</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalAdmin }}</div>
                <div class="stat-lbl">Administrator</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="fa-solid fa-user-tag"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalSales }}</div>
                <div class="stat-lbl">Tim Sales</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef2f2;color:#dc2626;">
                <i class="fa-solid fa-user-gear"></i>
            </div>
            <div>
                <div class="stat-val">{{ $totalTeknisi }}</div>
                <div class="stat-lbl">Tim Teknisi</div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input" id="custom-search" placeholder="Cari nama, email, atau jabatan..."
                autocomplete="off" />
        </div>
        <div style="display:flex;gap:8px;">
            <div
                style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                Data Keseluruhan
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="accounts-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 45px;">#</th>
                        <th style="text-align: left; width: 250px;">Informasi Pengguna</th>
                        <th style="text-align: center; width: 150px;">Identitas</th>
                        <th style="text-align: center; width: 120px;">Jabatan</th>
                        <th style="text-align: left; width: 200px;">Alamat</th>
                        <th style="text-align: center; width: 160px;">Opsi / Keamanan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                            <td style="text-align: left;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 13px;">{{ $user->nama_lengkap }}</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                    {{ $user->email }}
                                </div>
                                @if($user->no_whatsapp)
                                    <div style="font-size: 11px; color: #16a34a; margin-top: 1px; font-weight: 600;">
                                        {{ $user->no_whatsapp }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div style="font-weight: 600; color: #475569; font-size: 12px;">NIK: {{ $user->nik ?? '-' }}</div>
                                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">{{ $user->jenis_kelamin ?? '-' }} | {{ $user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('d/m/Y') : '-' }}</div>
                            </td>
                            <td style="text-align: center;">
                                @if($user->isSuperAdmin())
                                    <span class="badge text-[10px] font-bold py-0.5 px-2 inline-block mb-1" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">SUPER ADMIN</span>
                                @elseif($user->isAdmin())
                                    <span class="badge text-[10px] font-bold py-0.5 px-2 inline-block mb-1" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">{{ strtoupper($user->jabatan) }}</span>
                                @elseif($user->jabatan === 'Sales')
                                    <span class="badge text-[10px] font-bold py-0.5 px-2 inline-block mb-1" style="background: #f0f9ff; color: #0ea5e9; border: 1px solid #bae6fd;">SALES</span>
                                @else
                                    <span class="badge text-[10px] font-bold py-0.5 px-2 inline-block mb-1" style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;">TEKNISI</span>
                                @endif
                                <br>
                                @if($user->is_active)
                                    <span class="badge text-[9px] font-bold py-0.5 px-1.5" style="background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe;">Aktif</span>
                                @else
                                    <span class="badge text-[9px] font-bold py-0.5 px-1.5" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;">Nonaktif</span>
                                @endif
                            </td>
                            <td style="text-align: left; font-size: 11px; color: #64748b; line-height: 1.4;">
                                {{ Str::limit($user->alamat ?? '-', 60) }}
                            </td>
                            <td style="text-align: center;">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                        title="Edit Akun" onclick="openCustomModal('editModal', this)"
                                        data-user-id="{{ $user->id }}"
                                        data-user-nama="{{ $user->nama_lengkap }}"
                                        data-user-nik="{{ $user->nik }}" 
                                        data-user-wa="{{ $user->no_whatsapp }}"
                                        data-user-alamat="{{ $user->alamat }}"
                                        data-user-jk="{{ $user->jenis_kelamin }}"
                                        data-user-tgl="{{ $user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('Y-m-d') : '' }}"
                                        data-user-email="{{ $user->email }}"
                                        data-user-jabatan="{{ $user->jabatan }}">
                                        <i class="fa-solid fa-pen-to-square text-slate-500 hover:text-amber-600"></i>
                                    </button>

                                    @if ($user->google2fa_secret)
                                        <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                            onclick="show2FAQRCode({{ $user->id }})" title="Lihat QR Code 2FA">
                                            <i class="fa-solid fa-qrcode text-slate-500 hover:text-indigo-600"></i>
                                        </button>
                                    @endif

                                    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                        onclick="reset2FA({{ $user->id }})" title="Reset 2FA">
                                        <i class="fa-solid fa-shield-halved text-slate-500 hover:text-amber-600"></i>
                                    </button>

                                    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                        title="Hapus Akun" onclick="confirmGlobalDelete('{{ route('accounts.destroy', $user->id) }}', '{{ $user->nama_lengkap }}')">
                                        <i class="fa-solid fa-trash text-slate-500 hover:text-rose-600"></i>
                                    </button>

                                    @if(auth()->id() !== $user->id)
                                        <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                            title="{{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}"
                                            onclick="confirmToggleStatus('{{ route('accounts.toggleStatus', $user->id) }}', '{{ $user->nama_lengkap }}', {{ $user->is_active ? 'true' : 'false' }})">
                                            @if($user->is_active)
                                                <i class="fa-solid fa-user-slash text-slate-400 hover:text-rose-600"></i>
                                            @else
                                                <i class="fa-solid fa-user-check text-slate-400 hover:text-emerald-600"></i>
                                            @endif
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('accounts.modal')
    @section('Scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>

        <script>
            // Custom Modal Functions
            function openCustomModal(modalId, triggerEl = null) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    if (triggerEl) {
                        const $btn = $(triggerEl);
                        
                        // Handle Edit Modal Population
                        if (modalId === 'editModal') {
                            const $form = $('#editForm');
                            $form.attr('action', `/accounts/${$btn.data('user-id')}`);
                            $('#edit_nama_lengkap').val($btn.data('user-nama'));
                            $('#edit_email').val($btn.data('user-email'));
                            $('#edit_no_whatsapp').val($btn.data('user-wa'));
                            $('#edit_nik').val($btn.data('user-nik'));
                            $('#edit_alamat').val($btn.data('user-alamat'));
                            $('#edit_jenis_kelamin').val($btn.data('user-jk'));
                            $('#edit_tanggal_lahir').val($btn.data('user-tgl'));
                            $('#edit_jabatan').val($btn.data('user-jabatan'));
                            $('#edit_modal_title').text(`Edit Akun: ${$btn.data('user-nama')}`);
                        }
                    }
                    
                    modal.classList.add('open');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeCustomModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('open');
                    document.body.style.overflow = '';
                }
            }

            function reset2FA(userId) {
                const resetModal = document.getElementById('reset2FAModal');
                const resetForm = document.getElementById('reset2FAForm');
                resetForm.action = `/accounts/${userId}/2fa/reset`;
                openCustomModal('reset2FAModal');
            }

            function confirmToggleStatus(actionUrl, userName, isActive) {
                const form = document.getElementById('toggleStatusForm');
                const nameEl = document.getElementById('toggle_user_name');
                const textEl = document.getElementById('toggle-status-text');
                const iconEl = document.getElementById('toggle-status-icon');
                const btnSubmit = document.getElementById('toggle-status-btn-submit');
                
                if (form && nameEl && textEl && iconEl) {
                    form.action = actionUrl;
                    nameEl.textContent = userName;
                    textEl.textContent = isActive ? 'menonaktifkan' : 'mengaktifkan';
                    
                    if (isActive) {
                        iconEl.className = 'fa-solid fa-user-slash text-rose-500 text-3xl mb-3';
                        btnSubmit.className = 'btn-primary-admin !h-9 !px-5 text-xs font-semibold !bg-rose-600 hover:!bg-rose-700';
                    } else {
                        iconEl.className = 'fa-solid fa-user-check text-green-500 text-3xl mb-3';
                        btnSubmit.className = 'btn-primary-admin !h-9 !px-5 text-xs font-semibold !bg-green-600 hover:!bg-green-700';
                    }
                    
                    openCustomModal('toggleStatusModal');
                }
            }

            let qrModalInstance = null;
            function show2FAQRCode(userId) {
                const qrModalBody = document.getElementById('qrModalBody');
                qrModalBody.innerHTML = `<div class="flex justify-center py-8"><i class="fa-solid fa-circle-notch fa-spin text-2xl text-slate-400"></i></div>`;
                
                openCustomModal('qrModal');

                fetch(`/accounts/${userId}/2fa/show`)
                    .then(response => response.json())
                    .then(data => {
                        qrModalBody.innerHTML = `
                            <div class="text-center space-y-3.5">
                                <p class="text-xs font-medium text-slate-600">Scan QR Code ini menggunakan aplikasi Google Authenticator:</p>
                                <div class="bg-white border border-slate-200 rounded-xl p-3 inline-block shadow-sm">
                                    ${data.qr}
                                </div>
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Setup Key Manual</p>
                                    <code class="text-xs font-bold text-slate-800 tracking-widest font-mono">${data.secret}</code>
                                </div>
                            </div>
                        `;
                    })
                    .catch(error => {
                        qrModalBody.innerHTML = `<div class="bg-rose-50 text-rose-600 p-3 rounded-xl text-xs font-bold text-center">Gagal memuat QR Code.</div>`;
                    });
            }

            $(document).ready(function() {
                const table = $('#accounts-table').DataTable({
                    "pageLength": 10,
                    "lengthChange": false,
                    "ordering": false,
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

                $('#custom-search').on('keyup', function() {
                    table.search(this.value).draw();
                });
            });
        </script>
    @endsection
</x-layouts.app>