<x-layouts.app>
    <!-- Page header -->
    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Manajemen Akun</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola akses pengguna dan pengaturan keamanan akun.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="button" class="btn-primary-admin !flex !items-center !justify-center gap-2"
                onclick="openCustomModal('addUserModal')">
                <i class="fa-solid fa-user-plus"></i> Tambah Akun
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
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: center; width: 250px;">Informasi Pengguna</th>
                        <th style="text-align: center; width: 150px;">Identitas</th>
                        <th style="text-align: center; width: 120px;">Jabatan</th>
                        <th style="text-align: center; width: 200px;">Alamat</th>
                        <th style="text-align: center; width: 180px;">Opsi / Keamanan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $loop->iteration }}</td>
                            <td style="text-align: left;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $user->nama_lengkap }}</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                    <i class="fa-regular fa-envelope mr-1"></i> {{ $user->email }}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <div style="font-weight: 600; color: #475569; font-size: 12px;">NIK: {{ $user->nik ?? '-' }}</div>
                                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">{{ $user->jenis_kelamin ?? '-' }} | {{ $user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('d/m/Y') : '-' }}</div>
                            </td>
                            <td style="text-align: center;">
                                @if($user->jabatan === 'Administrator')
                                    <span class="badge" style="background: #fef2f2; color: #dc2626; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800;">ADMIN</span>
                                @elseif($user->jabatan === 'Sales')
                                    <span class="badge" style="background: #f0f9ff; color: #0ea5e9; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800;">SALES</span>
                                @else
                                    <span class="badge" style="background: #f0fdf4; color: #16a34a; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800;">TEKNISI</span>
                                @endif
                            </td>
                            <td style="text-align: left; font-size: 11px; color: #64748b; line-height: 1.4;">
                                {{ Str::limit($user->alamat ?? '-', 60) }}
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap;">
                                    <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                        title="Edit Akun" onclick="openCustomModal('editModal', this)"
                                        data-user-id="{{ $user->id }}"
                                        data-user-nama="{{ $user->nama_lengkap }}"
                                        data-user-nik="{{ $user->nik }}" 
                                        data-user-alamat="{{ $user->alamat }}"
                                        data-user-jk="{{ $user->jenis_kelamin }}"
                                        data-user-tgl="{{ $user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('Y-m-d') : '' }}"
                                        data-user-email="{{ $user->email }}"
                                        data-user-jabatan="{{ $user->jabatan }}">
                                        <i class="fa-solid fa-pen-to-square text-amber-500"></i>
                                    </button>

                                    @if ($user->google2fa_secret)
                                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                            onclick="show2FAQRCode({{ $user->id }})" title="Lihat QR Code 2FA">
                                            <i class="fa-solid fa-qrcode text-primary"></i>
                                        </button>
                                    @endif

                                    <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                        onclick="reset2FA({{ $user->id }})" title="Reset 2FA">
                                        <i class="fa-solid fa-shield-halved text-rose-500"></i>
                                    </button>

                                    <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                        title="Hapus Akun" onclick="confirmGlobalDelete('{{ route('accounts.destroy', $user->id) }}', '{{ $user->nama_lengkap }}')">
                                        <i class="fa-solid fa-trash text-rose-600"></i>
                                    </button>
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

            let qrModalInstance = null;
            function show2FAQRCode(userId) {
                const qrModalBody = document.getElementById('qrModalBody');
                qrModalBody.innerHTML = `<div class="flex justify-center py-8"><i class="fa-solid fa-circle-notch fa-spin text-3xl text-primary"></i></div>`;
                
                openCustomModal('qrModal');

                fetch(`/accounts/${userId}/2fa/show`)
                    .then(response => response.json())
                    .then(data => {
                        qrModalBody.innerHTML = `
                            <div class="text-center space-y-4">
                                <p class="text-sm font-medium text-slate-600">Scan QR Code ini menggunakan aplikasi Google Authenticator:</p>
                                <div class="bg-white border-2 border-slate-100 rounded-2xl p-4 inline-block shadow-sm">
                                    ${data.qr}
                                </div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Setup Key Manual</p>
                                    <code class="text-sm font-black text-rose-600 tracking-widest">${data.secret}</code>
                                </div>
                            </div>
                        `;
                    })
                    .catch(error => {
                        qrModalBody.innerHTML = `<div class="bg-rose-50 text-rose-600 p-4 rounded-xl text-sm font-bold text-center">Gagal memuat QR Code.</div>`;
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
