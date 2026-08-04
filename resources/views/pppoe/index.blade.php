<x-layouts.app>
    @section('Css')
        <style>
            #pppoe-table thead th,
            #pppoe-table tbody td {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 12px;
            }
        </style>
    @endsection

    <div
        style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Manage PPPoE</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">Kelola data akun PPPoE dan pembayaran.</p>
        </div>
    </div>

    <div class="table-card mt-6">
        <div class="table-responsive">
            <table class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: center;">Pelanggan</th>
                        <th style="text-align: center;">Username</th>
                        <th style="text-align: center;">Profile/Paket</th>
                        <th style="text-align: center;">Router</th>
                        <th style="text-align: center;">IP Address</th>
                        <th style="text-align: center;">Tipe Bayar</th>
                        <th style="text-align: center;">Status Invoice</th>
                        <th style="text-align: center; width: 100px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pppoes as $pppoe)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-center">{{ $pppoe->preOrder->nama_lengkap ?? '-' }}</td>
                            <td class="text-center font-bold">{{ $pppoe->username }}</td>
                            <td class="text-center"><span class="badge"
                                    style="background:#e0f2fe;color:#0369a1;">{{ $pppoe->paket ? $pppoe->paket->nama_paket : '' }}</span></td>
                            <td class="text-center">{{ $pppoe->router ?? 'All' }}</td>
                            <td class="text-center">{{ $pppoe->ip_address ?? '-' }}</td>
                            <td class="text-center">{{ ucfirst($pppoe->tipe_pembayaran) ?? '-' }}</td>
                            <td class="text-center">
                                @if ($pppoe->status_invoice == 'terbayar')
                                    <span class="badge badge-published">TERBAYAR</span>
                                @else
                                    <span class="badge" style="background:#fef2f2;color:#be123c;">BELUM BAYAR</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                        onclick="openEditModal({{ $pppoe->id }}, '{{ $pppoe->username }}', '{{ $pppoe->password }}', '{{ $pppoe->paket_id }}', '{{ $pppoe->router }}', '{{ $pppoe->ip_address }}', '{{ $pppoe->tipe_pembayaran }}', '{{ $pppoe->tanggal_aktif ? $pppoe->tanggal_aktif->format('Y-m-d') : '' }}', '{{ $pppoe->periode_billing }}', '{{ $pppoe->pajak }}', '{{ $pppoe->diskon }}', '{{ $pppoe->status_invoice }}', '{{ $pppoe->generate_invoice }}')"
                                        title="Edit PPPoE"><i
                                            class="fa-solid fa-pen-to-square text-amber-500"></i></button>
                                    <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
                                        onclick="confirmGlobalDelete('{{ route('pppoe_accounts.destroy', $pppoe->id) }}', 'PPPoE {{ $pppoe->username }}')"
                                        title="Hapus"><i class="fa-solid fa-trash text-rose-500"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-slate-400 py-4">Belum ada akun PPPoE dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal-overlay" id="editPppoeModal">
        <div class="modal-box max-w-2xl bg-white border border-slate-300 shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white rounded-t-2xl">
                <div class="modal-title">
                    <i class="fa-solid fa-pen-to-square text-amber-500"></i>
                    <span class="text-slate-900 font-bold text-[17px]">Edit Akun PPPoE</span>
                </div>
                <button type="button" class="modal-close" onclick="closeCustomModal('editPppoeModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-tabs bg-white border-b border-slate-200">
                <button class="modal-tab active" data-tab="edit-pppoe-tab-akun">
                    <i class="fa-solid fa-user-shield"></i> Akun PPPoE
                </button>
                <button class="modal-tab" data-tab="edit-pppoe-tab-pembayaran">
                    <i class="fa-solid fa-money-bill"></i> Pembayaran
                </button>
            </div>

            <form id="editPppoeForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body bg-white">
                    <!-- Tab Akun PPPoE -->
                    <div class="tab-panel active" id="edit-pppoe-tab-akun">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">Username <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                                    id="edit_username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">Password <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                                    id="edit_password" name="password" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Profile / Paket <span
                                    class="text-rose-500">*</span></label>
                                <select class="form-input-custom w-full bg-white text-slate-900 border-slate-300"
                                    id="edit_paket_id" name="paket_id" required>
                                    @foreach(\App\Models\Paket::all() as $paket)
                                        <option value="{{ $paket->id }}">{{ $paket->nama_paket }} - Rp {{ number_format($paket->harga, 0, ',', '.') }}</option>
                                    @endforeach
                                </select>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">Router</label>
                                <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                                    id="edit_router" name="router">
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">IP Address</label>
                                <input type="text" class="form-input bg-white text-slate-900 border-slate-300"
                                    id="edit_ip_address" name="ip_address">
                            </div>
                        </div>
                    </div>

                    <!-- Tab Pembayaran -->
                    <div class="tab-panel" id="edit-pppoe-tab-pembayaran">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">Tipe Pembayaran</label>
                                <select class="form-input-custom w-full bg-white text-slate-900 border-slate-300"
                                    id="edit_tipe_pembayaran" name="tipe_pembayaran">
                                    <option value="postpaid">Postpaid</option>
                                    <option value="prepaid">Prepaid</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">Tanggal Aktif</label>
                                <input type="date" class="form-input bg-white text-slate-900 border-slate-300"
                                    id="edit_tanggal_aktif" name="tanggal_aktif">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Periode Billing</label>
                            <select class="form-input-custom w-full bg-white text-slate-900 border-slate-300"
                                id="edit_periode_billing" name="periode_billing">
                                <option value="fixed">Fixed</option>
                                <option value="renewal">Renewal</option>
                            </select>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">Pajak (Rp)</label>
                                <input type="number" class="form-input bg-white text-slate-900 border-slate-300"
                                    id="edit_pajak" name="pajak">
                            </div>
                            <div class="form-group">
                                <label class="form-label text-slate-800 font-bold">Diskon (Rp)</label>
                                <input type="number" class="form-input bg-white text-slate-900 border-slate-300"
                                    id="edit_diskon" name="diskon">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label text-slate-800 font-bold">Status Invoice</label>
                            <select class="form-input-custom w-full bg-white text-slate-900 border-slate-300"
                                id="edit_status_invoice" name="status_invoice">
                                <option value="belum_bayar">Belum Bayar</option>
                                <option value="terbayar">Terbayar</option>
                            </select>
                        </div>
                        <div class="form-group mt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="edit_generate_invoice" name="generate_invoice"
                                    class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                <span class="text-sm font-medium text-slate-700">Generate Invoice</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="btn-cancel !flex !items-center !justify-center gap-2"
                        onclick="closeCustomModal('editPppoeModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit"
                        class="btn-save !flex !items-center !justify-center gap-2 !bg-amber-500 hover:!bg-amber-600">
                        <i class="fa-solid fa-rotate"></i> Update PPPoE
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script>
            function openEditModal(id, username, password, paket_id, router, ip_address, tipe, tgl, periode, pajak, diskon,
                status, gen) {
                document.getElementById('editPppoeForm').action = `/pppoe-accounts/${id}`;
                document.getElementById('edit_username').value = username;
                document.getElementById('edit_password').value = password;
                document.getElementById('edit_paket_id').value = paket_id;
                document.getElementById('edit_router').value = router;
                document.getElementById('edit_ip_address').value = ip_address;
                document.getElementById('edit_tipe_pembayaran').value = tipe;
                document.getElementById('edit_tanggal_aktif').value = tgl;
                document.getElementById('edit_periode_billing').value = periode;
                document.getElementById('edit_pajak').value = pajak;
                document.getElementById('edit_diskon').value = diskon;
                document.getElementById('edit_status_invoice').value = status;
                document.getElementById('edit_generate_invoice').checked = (gen == '1');

                $('#editPppoeModal .modal-tab').first().trigger('click');
                const modal = document.getElementById('editPppoeModal');
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            $(document).on('click', '.modal-tab', function() {
                const tabId = $(this).data('tab');
                const $modalBox = $(this).closest('.modal-box');
                $modalBox.find('.modal-tab').removeClass('active');
                $(this).addClass('active');
                $modalBox.find('.tab-panel').removeClass('active');
                $('#' + tabId).addClass('active');
            });
        </script>
    @endsection
</x-layouts.app>
