<x-layouts.app title="Broadcast & Pemberitahuan Pelanggan">
    @section('Css')
        <style>
            #broadcast-table thead th {
                text-transform: uppercase !important;
                font-weight: 600;
                font-size: 11.5px;
            }

            #broadcast-table tbody td {
                font-size: 12px;
            }

            .badge-type {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 3px 8px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 600;
                letter-spacing: 0.01em;
            }

            .badge-maintenance {
                background: #fff7ed;
                color: #c2410c;
                border: 1px solid #ffedd5;
            }

            .badge-announcement {
                background: #f0f9ff;
                color: #0369a1;
                border: 1px solid #e0f2fe;
            }

            .badge-promo {
                background: #faf5ff;
                color: #7e22ce;
                border: 1px solid #f3e8ff;
            }

            .badge-billing {
                background: #ecfdf5;
                color: #047857;
                border: 1px solid #d1fae5;
            }

            .badge-general {
                background: #f8fafc;
                color: #475569;
                border: 1px solid #e2e8f0;
            }

            .mobile-preview-box {
                background: #ffffff;
                border-radius: 14px;
                box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
                border: 1px solid #e2e8f0;
                padding: 12px 14px;
                position: relative;
            }
        </style>
    @endsection

    <!-- Header Section -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;display:flex;align-items:center;gap:0.5rem;">
                Broadcast & Pemberitahuan Pelanggan
            </h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">
                Kirim notifikasi push ke aplikasi mobile & in-app untuk pemeliharaan jaringan, pengumuman, promo, dan tagihan.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('settings.index', ['tab' => 'firebase']) }}" class="btn-cancel !h-8.5 !px-3.5 text-xs font-semibold flex items-center gap-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-xs">
                <i class="fa-solid fa-fire text-amber-500"></i> Pengaturan Firebase FCM
            </a>
            <button type="button" onclick="openCreateModal()" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !h-8.5 !px-3.5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                <i class="fa-solid fa-paper-plane"></i> Kirim Pemberitahuan Baru
            </button>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="stats-grid mb-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0e7ff;color:#4338ca;">
                <i class="fa-solid fa-bullhorn"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($stats['total_broadcasts'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Total Broadcast Dikirim</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-val text-emerald-600">{{ number_format($stats['total_sent'], 0, ',', '.') }}</div>
                <div class="stat-lbl">FCM Push Terkirim</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#b45309;">
                <i class="fa-solid fa-mobile-screen-button"></i>
            </div>
            <div>
                <div class="stat-val text-amber-700">{{ number_format($stats['fcm_registered'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Pelanggan Mobile Aktif (FCM)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="stat-val text-slate-700">{{ number_format($stats['total_customers'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Total Pelanggan Terdaftar</div>
            </div>
        </div>
    </div>

    <!-- Table Toolbar & Filters -->
    <div class="table-toolbar mb-4">
        <form method="GET" action="{{ route('broadcasts.index') }}" class="w-full flex flex-wrap items-center gap-3" id="filterForm">
            <div style="flex: 1; min-width: 170px;">
                <label class="form-label-custom">Kategori Pemberitahuan</label>
                <select name="type" class="form-input-custom !h-8.5 !text-xs" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Kategori</option>
                    <option value="maintenance" {{ $typeFilter === 'maintenance' ? 'selected' : '' }}>Pemeliharaan (Maintenance)</option>
                    <option value="announcement" {{ $typeFilter === 'announcement' ? 'selected' : '' }}>Pengumuman Resmi</option>
                    <option value="promo" {{ $typeFilter === 'promo' ? 'selected' : '' }}>Promo & Penawaran</option>
                    <option value="billing" {{ $typeFilter === 'billing' ? 'selected' : '' }}>Tagihan / Billing</option>
                    <option value="general" {{ $typeFilter === 'general' ? 'selected' : '' }}>Informasi Umum</option>
                </select>
            </div>

            <div style="flex: 2; min-width: 250px;">
                <label class="form-label-custom">Pencarian Pesan</label>
                <div class="search-wrap !max-w-full">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="search" name="search" class="search-input custom-search !h-8.5 !text-xs"
                        value="{{ $search }}" placeholder="Cari berdasarkan judul atau isi notifikasi..." />
                </div>
            </div>

            <div style="margin-top: 18px; display: flex; gap: 8px;">
                <button type="submit" class="btn-primary-admin !h-8.5 !px-4 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if (!empty($search) || !empty($typeFilter))
                    <a href="{{ route('broadcasts.index') }}" class="btn-cancel !h-8.5 !px-3 text-xs font-semibold flex items-center gap-1 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50">
                        <i class="fa-solid fa-xmark"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="broadcast-table" class="admin-table w-full">
                <thead>
                    <tr>
                        <th style="width: 150px;">Waktu Kirim</th>
                        <th style="width: 140px;">Kategori</th>
                        <th>Judul & Isi Pesan</th>
                        <th style="width: 160px;">Target Pelanggan</th>
                        <th style="width: 140px;" class="text-center">Status Push FCM</th>
                        <th style="width: 120px;">Dibuat Oleh</th>
                        <th style="width: 80px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($broadcasts as $item)
                        @php
                            $badgeClass = match ($item->type) {
                                'maintenance' => 'badge-maintenance',
                                'announcement' => 'badge-announcement',
                                'promo' => 'badge-promo',
                                'billing' => 'badge-billing',
                                default => 'badge-general',
                            };

                            $typeLabel = match ($item->type) {
                                'maintenance' => 'Pemeliharaan',
                                'announcement' => 'Pengumuman',
                                'promo' => 'Promo',
                                'billing' => 'Tagihan',
                                default => 'Umum',
                            };

                            $typeIcon = match ($item->type) {
                                'maintenance' => 'fa-screwdriver-wrench',
                                'announcement' => 'fa-bullhorn',
                                'promo' => 'fa-gift',
                                'billing' => 'fa-file-invoice-dollar',
                                default => 'fa-bell',
                            };

                            $targetLabel = match ($item->target_type) {
                                'package' => 'Paket: ' . ($pakets->firstWhere('id', $item->target_id)->nama_paket ?? 'ID #' . $item->target_id),
                                'customer' => 'Pelanggan: ' . ($customers->firstWhere('id', $item->target_id)->nama_lengkap ?? 'ID #' . $item->target_id),
                                default => 'Semua Pelanggan',
                            };
                        @endphp
                        <tr>
                            <td class="text-slate-600 font-mono text-[11px] whitespace-nowrap">
                                <div>{{ $item->created_at->format('d M Y') }}</div>
                                <div class="text-slate-400 text-[10.5px]">{{ $item->created_at->format('H:i:s') }} WIB</div>
                            </td>
                            <td>
                                <span class="badge-type {{ $badgeClass }}">
                                    <i class="fa-solid {{ $typeIcon }}"></i>
                                    <span>{{ $typeLabel }}</span>
                                </span>
                            </td>
                            <td>
                                <div class="font-bold text-slate-800 text-xs mb-0.5">{{ $item->title }}</div>
                                <div class="text-slate-500 text-[11px] line-clamp-2 leading-relaxed" title="{{ $item->body }}">
                                    {{ Str::limit($item->body, 110) }}
                                </div>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1.5 text-xs text-slate-700 font-medium">
                                    <i class="fa-solid {{ $item->target_type === 'all' ? 'fa-globe text-sky-500' : ($item->target_type === 'package' ? 'fa-box text-indigo-500' : 'fa-user text-amber-500') }} text-[11px]"></i>
                                    <span>{{ $targetLabel }}</span>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="inline-flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100" title="Push Notifikasi Berhasil Dikirim">
                                        <i class="fa-solid fa-paper-plane text-[9px]"></i> {{ $item->total_sent }}
                                    </span>
                                    <span class="text-slate-400 text-[11px]">/</span>
                                    <span class="text-slate-600 text-[11px]" title="Total Pelanggan Target">{{ $item->total_targeted }}</span>
                                </div>
                            </td>
                            <td class="text-xs text-slate-600 whitespace-nowrap">
                                {{ $item->creator->name ?? 'Sistem' }}
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" class="btn-cancel !p-1.5 !h-7 !w-7 text-xs text-slate-500 hover:text-sky-600"
                                        title="Lihat Detail Pesan"
                                        onclick="showDetailModal({{ json_encode($item) }}, '{{ $typeLabel }}', '{{ $targetLabel }}')">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <form action="{{ route('broadcasts.destroy', $item->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus riwayat log pemberitahuan ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-cancel !p-1.5 !h-7 !w-7 text-xs text-slate-400 hover:text-rose-600"
                                            title="Hapus Log">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-bullhorn text-4xl text-slate-300 mb-2"></i>
                                    <span class="text-sm font-semibold text-slate-600">Belum Ada Riwayat Pemberitahuan</span>
                                    <span class="text-xs text-slate-400 mt-0.5">Klik tombol "Kirim Pemberitahuan Baru" untuk mulai broadcast pengumuman.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($broadcasts->hasPages())
            <div class="p-3 border-t border-slate-100 flex justify-end">
                {{ $broadcasts->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: Buat Pemberitahuan Baru -->
    <div id="modalCreateBroadcast" class="modal-backdrop hidden fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="modal-container bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-200">
            <div class="modal-header px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Kirim Pemberitahuan & Broadcast Baru</h3>
                        <p class="text-[11px] text-slate-500 m-0">Kirim notifikasi push real-time & simpan di feed notifikasi mobile pelanggan.</p>
                    </div>
                </div>
                <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 focus:outline-none text-base">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('broadcasts.store') }}" method="POST" id="formCreateBroadcast" onsubmit="return confirmSubmitBroadcast()">
                @csrf
                <div class="modal-body p-5 space-y-4 max-h-[75vh] overflow-y-auto">
                    <!-- Template Cepat -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-bolt text-amber-500 text-[11px]"></i> Template Cepat (Isi Otomatis):
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" onclick="applyTemplate('maintenance')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white border border-slate-200 hover:border-amber-300 hover:bg-amber-50 text-slate-700 transition">
                                <i class="fa-solid fa-screwdriver-wrench text-amber-600 mr-1"></i> Pemeliharaan Jaringan
                            </button>
                            <button type="button" onclick="applyTemplate('gangguan')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white border border-slate-200 hover:border-rose-300 hover:bg-rose-50 text-slate-700 transition">
                                <i class="fa-solid fa-triangle-exclamation text-rose-600 mr-1"></i> Info Gangguan
                            </button>
                            <button type="button" onclick="applyTemplate('tagihan')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50 text-slate-700 transition">
                                <i class="fa-solid fa-file-invoice-dollar text-emerald-600 mr-1"></i> Pengingat Tagihan
                            </button>
                            <button type="button" onclick="applyTemplate('promo')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white border border-slate-200 hover:border-purple-300 hover:bg-purple-50 text-slate-700 transition">
                                <i class="fa-solid fa-gift text-purple-600 mr-1"></i> Info Promo & Diskon
                            </button>
                        </div>
                    </div>

                    <!-- Kategori & Target -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-semibold text-xs">Kategori Pemberitahuan <span class="text-rose-500">*</span></label>
                            <select name="type" id="input_type" class="form-input-custom !h-8.5 !text-xs w-full" required onchange="updatePreview()">
                                <option value="maintenance">Pemeliharaan (Maintenance)</option>
                                <option value="announcement">Pengumuman Resmi</option>
                                <option value="promo">Promo & Penawaran</option>
                                <option value="billing">Tagihan / Billing</option>
                                <option value="general">Informasi Umum</option>
                            </select>
                        </div>

                        <div class="form-group !mb-0">
                            <label class="form-label text-slate-700 font-semibold text-xs">Target Penerima <span class="text-rose-500">*</span></label>
                            <select name="target_type" id="input_target_type" class="form-input-custom !h-8.5 !text-xs w-full" required onchange="toggleTargetDropdown()">
                                <option value="all">Semua Pelanggan (Broadcast)</option>
                                <option value="package">Berdasarkan Paket Layanan Tertentu</option>
                                <option value="customer">1 Pelanggan Tertentu</option>
                            </select>
                        </div>
                    </div>

                    <!-- Pilihan Target Dinamis -->
                    <div id="target_package_wrap" class="hidden">
                        <label class="form-label text-slate-700 font-semibold text-xs">Pilih Paket Layanan <span class="text-rose-500">*</span></label>
                        <select name="target_id" id="target_package_id" class="form-input-custom !h-8.5 !text-xs w-full" disabled>
                            <option value="">-- Pilih Paket Target --</option>
                            @foreach ($pakets as $p)
                                <option value="{{ $p->id }}">{{ $p->nama_paket }} ({{ $p->kecepatan ?? '-' }}) - Rp {{ number_format($p->harga, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="target_customer_wrap" class="hidden">
                        <label class="form-label text-slate-700 font-semibold text-xs">Pilih Pelanggan <span class="text-rose-500">*</span></label>
                        <select name="target_id" id="target_customer_id" class="form-input-custom !h-8.5 !text-xs w-full" disabled>
                            <option value="">-- Pilih Pelanggan Target --</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">
                                    {{ $c->id_pelanggan }} - {{ $c->nama_lengkap }} {{ !empty($c->fcm_token) ? ' [FCM Terhubung]' : ' [Belum Login Mobile]' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Judul Notifikasi -->
                    <div class="form-group !mb-0">
                        <label class="form-label text-slate-700 font-semibold text-xs">Judul Pemberitahuan <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" id="input_title" class="form-input !h-8.5 !text-xs" required
                            maxlength="150" placeholder="Contoh: Pemeliharaan Jaringan Terjadwal Area Timur" oninput="updatePreview()">
                    </div>

                    <!-- Isi Pesan -->
                    <div class="form-group !mb-0">
                        <div class="flex items-center justify-between mb-1">
                            <label class="form-label text-slate-700 font-semibold text-xs !mb-0">Isi Pesan Notifikasi <span class="text-rose-500">*</span></label>
                            <span class="text-[10.5px] text-slate-400" id="char_counter">0 / 2000 karakter</span>
                        </div>
                        <textarea name="body" id="input_body" rows="4" class="form-input !text-xs leading-relaxed" required
                            maxlength="2000" placeholder="Tuliskan detail pesan pengumuman atau informasi secara jelas..." oninput="updatePreview()"></textarea>
                    </div>

                    <!-- Live Preview Card Tampilan Mobile -->
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 block mb-1.5 uppercase tracking-wide">
                            <i class="fa-solid fa-mobile-screen mr-1"></i> Preview Notifikasi di HP Pelanggan:
                        </span>
                        <div class="mobile-preview-box">
                            <div class="flex items-center justify-between text-[10.5px] text-slate-400 mb-1.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-4 h-4 rounded bg-sky-500 text-white flex items-center justify-center font-bold text-[9px]">E</span>
                                    <span class="font-bold text-slate-700">Erlangga Net</span>
                                    <span>•</span>
                                    <span>Baru saja</span>
                                </div>
                                <span class="text-[10px] font-semibold text-amber-600" id="preview_type_label">Pemeliharaan</span>
                            </div>
                            <div class="text-xs font-bold text-slate-800" id="preview_title">Judul Pemberitahuan</div>
                            <div class="text-[11px] text-slate-600 mt-1 leading-snug whitespace-pre-wrap" id="preview_body">Detail pesan pemberitahuan akan tampil di sini saat Anda mengetik...</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer px-5 py-3 border-t border-slate-100 flex items-center justify-end gap-2 bg-slate-50/50">
                    <button type="button" onclick="closeCreateModal()" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitBroadcast" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !h-8.5 !px-5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Broadcast Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Detail Broadcast -->
    <div id="modalDetailBroadcast" class="modal-backdrop hidden fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="modal-container bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
            <div class="modal-header px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-sky-600"></i> Detail Riwayat Pemberitahuan
                </h3>
                <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-600 focus:outline-none text-base">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-5 space-y-3.5">
                <div>
                    <span class="text-[11px] text-slate-400 block mb-0.5">Kategori & Target</span>
                    <div class="flex items-center gap-2">
                        <span id="detail_type_badge" class="badge-type"></span>
                        <span id="detail_target" class="text-xs font-semibold text-slate-700"></span>
                    </div>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 block mb-0.5">Judul</span>
                    <div id="detail_title" class="text-sm font-bold text-slate-800"></div>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 block mb-0.5">Isi Pesan</span>
                    <div id="detail_body" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 leading-relaxed whitespace-pre-wrap"></div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100 text-xs">
                    <div>
                        <span class="text-[11px] text-slate-400 block">Waktu Pengiriman</span>
                        <span id="detail_time" class="font-medium text-slate-700 font-mono"></span>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400 block">Status Pengiriman FCM</span>
                        <span id="detail_stats" class="font-medium text-emerald-600"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-5 py-3 border-t border-slate-100 flex justify-end bg-slate-50/50">
                <button type="button" onclick="closeDetailModal()" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script>
            function openCreateModal() {
                document.getElementById('modalCreateBroadcast').classList.remove('hidden');
                document.getElementById('input_title').focus();
            }

            function closeCreateModal() {
                document.getElementById('modalCreateBroadcast').classList.add('hidden');
            }

            function toggleTargetDropdown() {
                const targetType = document.getElementById('input_target_type').value;
                const pkgWrap = document.getElementById('target_package_wrap');
                const pkgSelect = document.getElementById('target_package_id');
                const custWrap = document.getElementById('target_customer_wrap');
                const custSelect = document.getElementById('target_customer_id');

                if (targetType === 'package') {
                    pkgWrap.classList.remove('hidden');
                    pkgSelect.removeAttribute('disabled');
                    custWrap.classList.add('hidden');
                    custSelect.setAttribute('disabled', 'disabled');
                } else if (targetType === 'customer') {
                    custWrap.classList.remove('hidden');
                    custSelect.removeAttribute('disabled');
                    pkgWrap.classList.add('hidden');
                    pkgSelect.setAttribute('disabled', 'disabled');
                } else {
                    pkgWrap.classList.add('hidden');
                    pkgSelect.setAttribute('disabled', 'disabled');
                    custWrap.classList.add('hidden');
                    custSelect.setAttribute('disabled', 'disabled');
                }
            }

            function updatePreview() {
                const title = document.getElementById('input_title').value.trim() || 'Judul Pemberitahuan';
                const body = document.getElementById('input_body').value.trim() || 'Detail pesan pemberitahuan akan tampil di sini saat Anda mengetik...';
                const typeSelect = document.getElementById('input_type');
                const typeText = typeSelect.options[typeSelect.selectedIndex].text;

                document.getElementById('preview_title').textContent = title;
                document.getElementById('preview_body').textContent = body;
                document.getElementById('preview_type_label').textContent = typeText;

                const bodyLen = document.getElementById('input_body').value.length;
                document.getElementById('char_counter').textContent = `${bodyLen} / 2000 karakter`;
            }

            function applyTemplate(templateKey) {
                const typeSelect = document.getElementById('input_type');
                const titleInput = document.getElementById('input_title');
                const bodyInput = document.getElementById('input_body');

                if (templateKey === 'maintenance') {
                    typeSelect.value = 'maintenance';
                    titleInput.value = 'Pemberitahuan Pemeliharaan Jaringan';
                    bodyInput.value = 'Diberitahukan kepada seluruh pelanggan Erlangga Net, akan dilakukan pemeliharaan jaringan terjadwal pada dini hari nanti pukul 01:00 - 04:00 WIB untuk meningkatkan kehandalan koneksi. Mohon maaf atas ketidaknyamanannya.';
                } else if (templateKey === 'gangguan') {
                    typeSelect.value = 'announcement';
                    titleInput.value = 'Informasi Gangguan Jaringan Sementara';
                    bodyInput.value = 'Saat ini terjadi kendala teknis pada jalur fiber optik utama yang menyebabkan gangguan koneksi di beberapa area. Tim teknisi kami sudah berada di lokasi untuk penanganan cepat. Layanan akan normal kembali sesegera mungkin.';
                } else if (templateKey === 'tagihan') {
                    typeSelect.value = 'billing';
                    titleInput.value = 'Pengingat Pembayaran Tagihan Internet';
                    bodyInput.value = 'Tagihan internet bulanan Anda telah terbit. Segera lakukan pembayaran melalui aplikasi atau transfer sebelum batas tanggal jatuh tempo agar kenyamanan akses internet Anda tetap terjaga tanpa terputus.';
                } else if (templateKey === 'promo') {
                    typeSelect.value = 'promo';
                    titleInput.value = 'Promo Spesial Upgrade Kecepatan Internet!';
                    bodyInput.value = 'Nikmati penawaran spesial upgrade paket internet dengan kecepatan hingga 2x lipat tanpa biaya instalasi ulang. Hubungi layanan CS atau cek detail paket sekarang juga!';
                }

                updatePreview();
            }

            function confirmSubmitBroadcast() {
                const targetText = document.getElementById('input_target_type').options[document.getElementById('input_target_type').selectedIndex].text;
                return confirm(`Apakah Anda yakin ingin mengirim pemberitahuan ini ke ${targetText}? Notifikasi akan langsung terkirim ke perangkat HP pelanggan.`);
            }

            function showDetailModal(item, typeLabel, targetLabel) {
                document.getElementById('detail_title').textContent = item.title;
                document.getElementById('detail_body').textContent = item.body;
                document.getElementById('detail_type_badge').textContent = typeLabel;
                document.getElementById('detail_target').textContent = 'Target: ' + targetLabel;
                document.getElementById('detail_time').textContent = new Date(item.created_at).toLocaleString('id-ID');
                document.getElementById('detail_stats').textContent = `${item.total_sent} Terkirim dari ${item.total_targeted} Target`;

                document.getElementById('modalDetailBroadcast').classList.remove('hidden');
            }

            function closeDetailModal() {
                document.getElementById('modalDetailBroadcast').classList.add('hidden');
            }
        </script>
    @endsection
</x-layouts.app>
