<x-layouts.app title="Promosi & Informasi">
    @section('Css')
        <style>
            .banner-preview-box {
                aspect-ratio: 3.2 / 1;
                max-width: 320px;
                width: 100%;
                background: #f8fafc;
                border: 2px dashed #cbd5e1;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                position: relative;
            }
            .banner-preview-box img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .table-banner-thumb {
                width: 112px;
                height: 35px;
                object-fit: cover;
                border-radius: 6px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 1px 3px rgba(0,0,0,0.05);
                transition: transform 0.2s ease;
            }
            .table-banner-thumb:hover {
                transform: scale(1.08);
            }
        </style>
    @endsection

    <!-- Header Section -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:22px;font-weight:900;margin:0;">Promosi & Informasi</h1>
            <p style="font-size:13px;color:#64748b;margin:3px 0 0;">
                Kelola banner slide show & promosi mobile apps, atur periode tayang, urutan, serta link tindakan WhatsApp / Web.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openCreateModal()" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !h-8.5 !px-3.5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Tambah Promosi
            </button>
        </div>
    </div>

    <!-- Stats Cards Grid (4 Kartu Kompak) -->
    <div class="stats-grid mb-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0284c7;">
                <i class="fa-solid fa-images"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($stats['total'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Total Banner Promosi</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
                <i class="fa-solid fa-circle-play"></i>
            </div>
            <div>
                <div class="stat-val text-emerald-600">{{ number_format($stats['showing'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Sedang Tayang di Mobile</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#b45309;">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div>
                <div class="stat-val text-amber-700">{{ number_format($stats['scheduled'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Banner Terjadwal</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#faf5ff;color:#9333ea;border:1px solid #f3e8ff;">
                <i class="fa-solid fa-arrow-pointer"></i>
            </div>
            <div>
                <div class="stat-val text-purple-700">{{ number_format($stats['clicks'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Total Klik Pelanggan</div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" class="search-input" id="custom-search" placeholder="Cari judul promosi, link, atau pembuat..."
                autocomplete="off" />
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-50 text-xs">
                <a href="{{ route('banners.index') }}" class="px-2.5 py-1 rounded-md font-medium transition {{ empty($statusFilter) ? 'bg-white shadow-xs text-sky-700 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua ({{ $stats['total'] }})
                </a>
                <a href="{{ route('banners.index', ['status' => 'showing']) }}" class="px-2.5 py-1 rounded-md font-medium transition {{ $statusFilter === 'showing' ? 'bg-white shadow-xs text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                    Tayang ({{ $stats['showing'] }})
                </a>
                <a href="{{ route('banners.index', ['status' => 'scheduled']) }}" class="px-2.5 py-1 rounded-md font-medium transition {{ $statusFilter === 'scheduled' ? 'bg-white shadow-xs text-sky-700 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                    Terjadwal ({{ $stats['scheduled'] }})
                </a>
                <a href="{{ route('banners.index', ['status' => 'expired']) }}" class="px-2.5 py-1 rounded-md font-medium transition {{ $statusFilter === 'expired' ? 'bg-white shadow-xs text-rose-700 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                    Kedaluwarsa
                </a>
                <a href="{{ route('banners.index', ['status' => 'inactive']) }}" class="px-2.5 py-1 rounded-md font-medium transition {{ $statusFilter === 'inactive' ? 'bg-white shadow-xs text-slate-800 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                    Nonaktif
                </a>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="banners-table" class="admin-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="text-align: left; width: 130px;">Banner</th>
                        <th style="text-align: left;">Judul & Promosi</th>
                        <th style="text-align: left; width: 190px;">Tanggal Penayangan</th>
                        <th style="text-align: left;">Tindakan / Link Tujuan</th>
                        <th style="text-align: center; width: 80px;">Klik</th>
                        <th style="text-align: center; width: 90px;">Status</th>
                        <th style="text-align: center; width: 90px;">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($banners as $b)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                                {{ $b->urutan }}
                            </td>
                            <td style="text-align: left;">
                                <a href="{{ $b->gambar_url }}" target="_blank" title="Klik untuk memperbesar">
                                    <img src="{{ $b->gambar_url }}" alt="{{ $b->judul }}" class="table-banner-thumb">
                                </a>
                            </td>
                            <td style="text-align: left;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 13px;">{{ $b->judul }}</div>
                                @if ($b->deskripsi)
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;" class="line-clamp-1">{{ $b->deskripsi }}</div>
                                @endif
                                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">
                                    Dibuat oleh: <span style="font-weight: 600; color: #475569;">{{ $b->creator?->nama_lengkap ?? 'Administrator' }}</span>
                                </div>
                            </td>
                            <td style="text-align: left;">
                                <div class="mb-1">{!! $b->status_badge_html !!}</div>
                                <div style="font-size: 11px; color: #475569; font-weight: 500;">
                                    {{ $b->tanggal_mulai ? $b->tanggal_mulai->translatedFormat('d M Y') : '-' }} s/d {{ $b->tanggal_selesai ? $b->tanggal_selesai->translatedFormat('d M Y') : 'Permanen' }}
                                </div>
                            </td>
                            <td style="text-align: left;">
                                @if ($b->link_url)
                                    @if ($b->is_whatsapp)
                                        <a href="{{ $b->link_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition text-[11px] font-medium max-w-[240px] truncate" title="{{ $b->link_url }}">
                                            <i class="fa-brands fa-whatsapp text-emerald-600 text-xs"></i>
                                            <span class="truncate">Chat WhatsApp CS</span>
                                        </a>
                                    @else
                                        <a href="{{ $b->link_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100 transition text-[11px] font-medium max-w-[240px] truncate" title="{{ $b->link_url }}">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-sky-600 text-[10px]"></i>
                                            <span class="truncate">{{ $b->link_url }}</span>
                                        </a>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Hanya Tampilan (Tanpa Link)</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="font-bold text-slate-700">{{ number_format($b->klik_count, 0, ',', '.') }}</span>
                            </td>
                            <td style="text-align: center;">
                                <button type="button" onclick="toggleBannerStatus({{ $b->id }}, this)" 
                                    class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold transition cursor-pointer {{ $b->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                    {{ $b->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td style="text-align: center;">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                        title="Edit Promosi" onclick='openEditModal(@json($b))'>
                                        <i class="fa-solid fa-pen-to-square text-slate-500 hover:text-amber-600"></i>
                                    </button>
                                    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                                        title="Hapus Promosi" onclick="confirmGlobalDelete('{{ route('banners.destroy', $b->id) }}', '{{ addslashes($b->judul) }}')">
                                        <i class="fa-solid fa-trash text-slate-500 hover:text-rose-600"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: TAMBAH PROMOSI / BANNER                                          -->
    <!-- ========================================================================= -->
    <div class="modal-overlay" id="createBannerModal">
        <div class="modal-box max-w-xl bg-white border border-slate-300 shadow-2xl">
            <form id="createBannerForm" action="{{ route('banners.store') }}" method="POST" enctype="multipart/form-data" onsubmit="handleCreateSubmit(event)">
                @csrf
                <!-- Header Modal -->
                <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 m-0">Tambah Promosi & Informasi</h3>
                            <p class="text-xs text-slate-500 m-0 mt-0.5">Unggah banner untuk slider mobile app.</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('createBannerModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Body Modal -->
                <div class="modal-body p-6 space-y-4 text-xs bg-white">
                    <!-- Judul Promosi -->
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Judul Promosi / Pengumuman <span class="text-rose-500">*</span></label>
                        <input type="text" name="judul" required maxlength="150" placeholder="Contoh: Promo Spesial Paket Gamer 50 Mbps" 
                            class="form-input !h-9 !text-xs w-full border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                    </div>

                    <!-- Upload Banner Image & Live Preview -->
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Gambar Banner <span class="text-rose-500">*</span></label>
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <div class="banner-preview-box" id="create_preview_wrap">
                                <span class="text-slate-400 text-center text-[11px] p-2" id="create_preview_placeholder">
                                    <i class="fa-regular fa-image text-2xl block mb-1 text-slate-300"></i>
                                    Preview Banner
                                </span>
                                <img id="create_preview_img" src="" alt="Preview Banner" class="hidden">
                            </div>
                            <div class="flex-1 w-full">
                                <input type="file" name="banner_image" id="create_banner_file" required accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                    onchange="handleImagePreview(event, 'create_preview_img', 'create_preview_placeholder')"
                                    class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer border border-slate-200 rounded-lg">
                                <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                                    Format: <b>JPG, PNG, WEBP</b>. Ukuran proporsional <b>320 x 100 px</b> agar tampilan slider di layar HP rapi tanpa terpotong.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Urutan Penayangan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Urutan Slide (Sorting)</label>
                            <select name="urutan" id="create_urutan" 
                                class="form-input !h-9 !text-xs w-full bg-white border-slate-300 rounded-lg">
                            </select>
                            <span class="text-[10px] text-slate-400">Urutan slide yang tersedia untuk mobile apps.</span>
                        </div>
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Status Banner</label>
                            <div class="mt-2 flex items-center gap-2">
                                <input type="checkbox" name="is_active" id="create_is_active" value="1" checked class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500">
                                <label for="create_is_active" class="text-xs font-medium text-slate-700 cursor-pointer">Langsung Aktifkan Penayangan</label>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal Penayangan (Tanggal Mulai & Selesai) -->
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between mb-2">
                            <label class="font-bold text-slate-700 text-xs">Tanggal Penayangan Banner (Mulai & Selesai)</label>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] text-slate-600">
                                <input type="checkbox" id="create_no_end_date" onchange="toggleEndDate('create', this.checked)" class="rounded text-sky-600 w-3.5 h-3.5">
                                <span>Tanpa Batas Waktu (Permanen)</span>
                            </label>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <span class="text-[11px] text-slate-500 block mb-1">Mulai Tayang:</span>
                                <input type="date" name="tanggal_mulai" id="create_tanggal_mulai" value="{{ date('Y-m-d') }}"
                                    class="form-input !h-8.5 !text-xs w-full border-slate-300 rounded-lg">
                            </div>
                            <div id="create_end_date_wrap">
                                <span class="text-[11px] text-slate-500 block mb-1">Berakhir Tayang:</span>
                                <input type="date" name="tanggal_selesai" id="create_tanggal_selesai"
                                    class="form-input !h-8.5 !text-xs w-full border-slate-300 rounded-lg">
                            </div>
                        </div>
                    </div>

                    <!-- Tindakan / Link Tujuan (Web / WhatsApp) -->
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Tindakan Saat Banner Diklik (Link URL / WhatsApp)</label>
                        <input type="text" name="link_url" id="create_link_url" placeholder="Contoh: https://erlangga.net/promo atau https://wa.me/628..." 
                            class="form-input !h-9 !text-xs w-full border-slate-300 rounded-lg">
                        <span class="text-[10px] text-slate-400 block mt-1">Kosongkan jika banner hanya untuk dilihat dan tidak bisa diklik.</span>
                    </div>

                    <!-- Deskripsi Singkat (Internal Note) -->
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Catatan / Deskripsi (Opsional)</label>
                        <textarea name="deskripsi" rows="2" placeholder="Catatan internal mengenai banner atau promosi ini..." 
                            class="form-input !text-xs w-full border-slate-300 rounded-lg"></textarea>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
                    <button type="button" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5"
                        onclick="closeCustomModal('createBannerModal')">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" id="btnSubmitCreate" class="btn-primary-admin !h-8.5 !px-5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: EDIT PROMOSI / BANNER                                            -->
    <!-- ========================================================================= -->
    <div class="modal-overlay" id="editBannerModal">
        <div class="modal-box max-w-xl bg-white border border-slate-300 shadow-2xl">
            <form id="editBannerForm" method="POST" enctype="multipart/form-data" onsubmit="handleEditSubmit(event)">
                @csrf
                @method('PUT')
                <!-- Header Modal -->
                <div class="modal-header border-b border-slate-200 bg-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm border border-slate-200 shrink-0">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 m-0">Edit Promosi & Informasi</h3>
                            <p class="text-xs text-slate-500 m-0 mt-0.5">Perbarui detail banner atau ganti gambar promosi.</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" onclick="closeCustomModal('editBannerModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Body Modal -->
                <div class="modal-body p-6 space-y-4 text-xs bg-white">
                    <!-- Judul Promosi -->
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Judul Promosi / Pengumuman <span class="text-rose-500">*</span></label>
                        <input type="text" name="judul" id="edit_judul" required maxlength="150" 
                            class="form-input !h-9 !text-xs w-full border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                    </div>

                    <!-- Upload Banner Image & Live Preview -->
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Ganti Gambar Banner (Biarkan kosong jika tidak ingin mengganti)</label>
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <div class="banner-preview-box" id="edit_preview_wrap">
                                <img id="edit_preview_img" src="" alt="Preview Banner" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 w-full">
                                <input type="file" name="banner_image" id="edit_banner_file" accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                    onchange="handleImagePreview(event, 'edit_preview_img', null)"
                                    class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer border border-slate-200 rounded-lg">
                                <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                                    Format: <b>JPG, PNG, WEBP</b>. Ukuran proporsional <b>320 x 100 px</b>. File baru akan menggantikan gambar banner lama.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Urutan Penayangan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Urutan Slide (Sorting)</label>
                            <select name="urutan" id="edit_urutan" 
                                class="form-input !h-9 !text-xs w-full bg-white border-slate-300 rounded-lg">
                            </select>
                            <span class="text-[10px] text-slate-400">Pilih urutan penayangan banner ini.</span>
                        </div>
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Status Banner</label>
                            <div class="mt-2 flex items-center gap-2">
                                <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500">
                                <label for="edit_is_active" class="text-xs font-medium text-slate-700 cursor-pointer">Aktifkan Penayangan</label>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal Penayangan (Tanggal Mulai & Selesai) -->
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between mb-2">
                            <label class="font-bold text-slate-700 text-xs">Tanggal Penayangan Banner (Mulai & Selesai)</label>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] text-slate-600">
                                <input type="checkbox" id="edit_no_end_date" onchange="toggleEndDate('edit', this.checked)" class="rounded text-sky-600 w-3.5 h-3.5">
                                <span>Tanpa Batas Waktu (Permanen)</span>
                            </label>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <span class="text-[11px] text-slate-500 block mb-1">Mulai Tayang:</span>
                                <input type="date" name="tanggal_mulai" id="edit_tanggal_mulai" 
                                    class="form-input !h-8.5 !text-xs w-full border-slate-300 rounded-lg">
                            </div>
                            <div id="edit_end_date_wrap">
                                <span class="text-[11px] text-slate-500 block mb-1">Berakhir Tayang:</span>
                                <input type="date" name="tanggal_selesai" id="edit_tanggal_selesai" 
                                    class="form-input !h-8.5 !text-xs w-full border-slate-300 rounded-lg">
                            </div>
                        </div>
                    </div>

                    <!-- Tindakan / Link Tujuan -->
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Tindakan Saat Banner Diklik (Link URL / WhatsApp)</label>
                        <input type="text" name="link_url" id="edit_link_url" placeholder="Contoh: https://erlangga.net/promo atau https://wa.me/628..." 
                            class="form-input !h-9 !text-xs w-full border-slate-300 rounded-lg">
                        <span class="text-[10px] text-slate-400 block mt-1">Kosongkan jika banner hanya untuk dilihat dan tidak bisa diklik.</span>
                    </div>

                    <!-- Deskripsi Singkat -->
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Catatan / Deskripsi (Opsional)</label>
                        <textarea name="deskripsi" id="edit_deskripsi" rows="2" 
                            class="form-input !text-xs w-full border-slate-300 rounded-lg"></textarea>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3 flex items-center justify-between">
                    <button type="button" onclick="closeCustomModal('editBannerModal')" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </button>
                    <button type="submit" id="btnSubmitEdit" class="btn-primary-admin !h-8.5 !px-5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('Scripts')
        <script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
        <script>
            // Ambil daftar nomor urutan yang sudah tersimpan
            const savedUrutans = @json($banners->pluck('urutan')->map(fn($u) => (int)$u)->values());

            function getAvailableUrutans(currentUrutan = null) {
                const currentNum = (currentUrutan !== null && currentUrutan !== '') ? Number(currentUrutan) : null;
                const taken = new Set(
                    savedUrutans.filter(u => currentNum === null || u !== currentNum)
                );

                const maxTaken = taken.size > 0 ? Math.max(...taken) : 0;
                const maxOption = Math.max(maxTaken + 10, 15);

                const available = [];
                for (let i = 1; i <= maxOption; i++) {
                    if (!taken.has(i)) {
                        available.push(i);
                    }
                }
                return available;
            }

            function renderUrutanSelect(elementId, selectedUrutan = null) {
                const select = document.getElementById(elementId);
                if (!select) return;

                const currentNum = (selectedUrutan !== null && selectedUrutan !== '') ? Number(selectedUrutan) : null;
                const available = getAvailableUrutans(currentNum);

                if (currentNum !== null && !available.includes(currentNum)) {
                    available.push(currentNum);
                    available.sort((a, b) => a - b);
                }

                select.innerHTML = '';
                available.forEach(num => {
                    const opt = document.createElement('option');
                    opt.value = num;
                    if (currentNum !== null && num === currentNum) {
                        opt.textContent = `Slide ${num} (Urutan ${num}) - Saat Ini`;
                        opt.selected = true;
                    } else {
                        opt.textContent = `Slide ${num} (Urutan ${num})`;
                    }
                    select.appendChild(opt);
                });

                if (currentNum === null && available.length > 0) {
                    select.value = available[0];
                }
            }

            $(document).ready(function() {
                const table = $('#banners-table').DataTable({
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

                $('#custom-search').on('keyup input', function() {
                    table.search(this.value).draw();
                });

                // Inisialisasi awal dropdown urutan di create form
                renderUrutanSelect('create_urutan', null);

                const flashToast = sessionStorage.getItem('flash_toast');
                if (flashToast) {
                    try {
                        const parsed = JSON.parse(flashToast);
                        if (typeof showToast === 'function' && parsed.message) {
                            showToast(parsed.message, parsed.type || 'success');
                        }
                    } catch (e) {}
                    sessionStorage.removeItem('flash_toast');
                }

                $('.modal-overlay').on('click', function(e) {
                    if (e.target === this) closeCustomModal(this.id);
                });
            });

            function openCustomModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('open');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeCustomModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('open');
                    if (!document.querySelector('.modal-overlay.open')) {
                        document.body.style.overflow = '';
                    }
                }
            }

            function openCreateModal() {
                document.getElementById('createBannerForm').reset();
                document.getElementById('create_preview_img').src = '';
                document.getElementById('create_preview_img').classList.add('hidden');
                document.getElementById('create_preview_placeholder').classList.remove('hidden');
                toggleEndDate('create', false);
                renderUrutanSelect('create_urutan', null);
                openCustomModal('createBannerModal');
            }

            function openEditModal(banner) {
                const form = document.getElementById('editBannerForm');
                form.action = `/banners/${banner.id}`;

                document.getElementById('edit_judul').value = banner.judul || '';
                renderUrutanSelect('edit_urutan', banner.urutan);
                document.getElementById('edit_is_active').checked = !!banner.is_active;
                document.getElementById('edit_link_url').value = banner.link_url || '';
                document.getElementById('edit_deskripsi').value = banner.deskripsi || '';

                // Tanggal
                const mulai = banner.tanggal_mulai ? banner.tanggal_mulai.substring(0, 10) : '';
                const selesai = banner.tanggal_selesai ? banner.tanggal_selesai.substring(0, 10) : '';

                document.getElementById('edit_tanggal_mulai').value = mulai;
                document.getElementById('edit_tanggal_selesai').value = selesai;

                const noEndDate = !selesai;
                document.getElementById('edit_no_end_date').checked = noEndDate;
                toggleEndDate('edit', noEndDate);

                // Image preview
                document.getElementById('edit_preview_img').src = banner.gambar_url;

                openCustomModal('editBannerModal');
            }

            function handleImagePreview(event, imgId, placeholderId) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.getElementById(imgId);
                        img.src = e.target.result;
                        img.classList.remove('hidden');
                        if (placeholderId) {
                            document.getElementById(placeholderId).classList.add('hidden');
                        }
                    };
                    reader.readAsDataURL(file);
                }
            }

            function toggleEndDate(prefix, isChecked) {
                const wrap = document.getElementById(`${prefix}_end_date_wrap`);
                const input = document.getElementById(`${prefix}_tanggal_selesai`);
                if (isChecked) {
                    input.value = '';
                    wrap.classList.add('opacity-40', 'pointer-events-none');
                } else {
                    wrap.classList.remove('opacity-40', 'pointer-events-none');
                }
            }

            function handleCreateSubmit(e) {
                e.preventDefault();
                const form = document.getElementById('createBannerForm');
                const btn = document.getElementById('btnSubmitCreate');
                const origHtml = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = "<i class='fa-solid fa-circle-notch fa-spin'></i> Menyimpan...";

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json().then(data => ({ ok: r.ok, data })))
                .then(res => {
                    if (res.ok && res.data.success) {
                        closeCustomModal('createBannerModal');
                        sessionStorage.setItem('flash_toast', JSON.stringify({ message: res.data.message, type: 'success' }));
                        window.location.reload();
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = origHtml;
                        if (typeof showToast === 'function') {
                            showToast(res.data.message || 'Periksa kembali formulir.', 'error');
                        }
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    if (typeof showToast === 'function') {
                        showToast('Terjadi kesalahan jaringan.', 'error');
                    }
                });
            }

            function handleEditSubmit(e) {
                e.preventDefault();
                const form = document.getElementById('editBannerForm');
                const btn = document.getElementById('btnSubmitEdit');
                const origHtml = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = "<i class='fa-solid fa-circle-notch fa-spin'></i> Menyimpan...";

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json().then(data => ({ ok: r.ok, data })))
                .then(res => {
                    if (res.ok && res.data.success) {
                        closeCustomModal('editBannerModal');
                        sessionStorage.setItem('flash_toast', JSON.stringify({ message: res.data.message, type: 'success' }));
                        window.location.reload();
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = origHtml;
                        if (typeof showToast === 'function') {
                            showToast(res.data.message || 'Periksa kembali formulir.', 'error');
                        }
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    if (typeof showToast === 'function') {
                        showToast('Terjadi kesalahan jaringan.', 'error');
                    }
                });
            }

            function toggleBannerStatus(id, btn) {
                const origText = btn.textContent;
                btn.textContent = '...';
                btn.disabled = true;

                fetch(`/banners/${id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    if (data.success) {
                        if (data.is_active) {
                            btn.className = 'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold transition cursor-pointer bg-emerald-100 text-emerald-800 hover:bg-emerald-200';
                            btn.textContent = 'Aktif';
                        } else {
                            btn.className = 'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold transition cursor-pointer bg-slate-200 text-slate-600 hover:bg-slate-300';
                            btn.textContent = 'Nonaktif';
                        }
                        if (typeof showToast === 'function') {
                            showToast(data.message, 'success');
                        }
                    } else {
                        btn.textContent = origText;
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Gagal mengubah status banner.', 'error');
                        }
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.textContent = origText;
                    if (typeof showToast === 'function') {
                        showToast('Gagal mengubah status banner: kesalahan jaringan.', 'error');
                    }
                });
            }

            // Daftarkan fungsi ke window scope agar selalu dapat diakses oleh onclick
            window.openCustomModal = openCustomModal;
            window.closeCustomModal = closeCustomModal;
            window.openCreateModal = openCreateModal;
            window.openEditModal = openEditModal;
            window.handleImagePreview = handleImagePreview;
            window.toggleEndDate = toggleEndDate;
            window.handleCreateSubmit = handleCreateSubmit;
            window.handleEditSubmit = handleEditSubmit;
            window.toggleBannerStatus = toggleBannerStatus;
        </script>
    @endsection
</x-layouts.app>
