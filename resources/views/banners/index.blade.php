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
                <i class="fa-solid fa-calendar-clock"></i>
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

    <!-- Filter & Toolbar -->
    <div class="table-toolbar mb-4 flex flex-wrap items-center justify-between gap-3 bg-white p-3 rounded-xl border border-slate-200">
        <div class="flex items-center gap-2 flex-wrap">
            <label class="text-xs font-semibold text-slate-600">Filter Status:</label>
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

    <!-- Table Section -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-3.5 text-center w-14">Urutan</th>
                        <th class="py-3 px-3.5 w-32">Banner</th>
                        <th class="py-3 px-3.5">Judul & Promosi</th>
                        <th class="py-3 px-3.5 w-48">Tanggal Penayangan</th>
                        <th class="py-3 px-3.5">Tindakan / Link Tujuan</th>
                        <th class="py-3 px-3 text-center w-20">Klik</th>
                        <th class="py-3 px-3.5 text-center w-24">Status</th>
                        <th class="py-3 px-3.5 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($banners as $b)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-3.5 text-center font-bold text-slate-800">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 border border-slate-200 text-slate-700 font-semibold text-xs">
                                    {{ $b->urutan }}
                                </span>
                            </td>
                            <td class="py-3 px-3.5">
                                <a href="{{ $b->gambar_url }}" target="_blank" title="Klik untuk memperbesar">
                                    <img src="{{ $b->gambar_url }}" alt="{{ $b->judul }}" class="table-banner-thumb">
                                </a>
                            </td>
                            <td class="py-3 px-3.5">
                                <div class="font-bold text-slate-900 text-xs">{{ $b->judul }}</div>
                                @if ($b->deskripsi)
                                    <div class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $b->deskripsi }}</div>
                                @endif
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    Dibuat oleh: <span class="font-medium text-slate-600">{{ $b->creator?->nama_lengkap ?? 'Administrator' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-3.5">
                                <div class="mb-1">{!! $b->status_badge_html !!}</div>
                                <div class="text-[11px] text-slate-600 font-medium">
                                    {{ $b->tanggal_mulai ? $b->tanggal_mulai->translatedFormat('d M Y') : '-' }} s/d {{ $b->tanggal_selesai ? $b->tanggal_selesai->translatedFormat('d M Y') : 'Permanen' }}
                                </div>
                            </td>
                            <td class="py-3 px-3.5">
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
                            <td class="py-3 px-3 text-center">
                                <span class="font-bold text-slate-700">{{ number_format($b->klik_count, 0, ',', '.') }}</span>
                            </td>
                            <td class="py-3 px-3.5 text-center">
                                <button type="button" onclick="toggleBannerStatus({{ $b->id }}, this)" 
                                    class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold transition cursor-pointer {{ $b->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                    {{ $b->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="py-3 px-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" onclick='openEditModal(@json($b))' class="w-7 h-7 rounded-md bg-amber-50 text-amber-600 border border-amber-200 hover:bg-amber-100 flex items-center justify-center transition" title="Edit Promosi">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <button type="button" onclick="confirmDeleteBanner({{ $b->id }}, '{{ addslashes($b->judul) }}')" class="w-7 h-7 rounded-md bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100 flex items-center justify-center transition" title="Hapus Promosi">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-regular fa-images text-4xl mb-3 text-slate-300"></i>
                                    <p class="font-medium text-slate-600 text-sm">Belum ada banner promosi.</p>
                                    <p class="text-xs text-slate-400 mt-1">Klik tombol <b>Tambah Promosi</b> di atas untuk menambahkan banner slide show mobile.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($banners->hasPages())
            <div class="p-3 border-t border-slate-200 bg-slate-50">
                {{ $banners->links() }}
            </div>
        @endif
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
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Tambah Promosi & Informasi</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Unggah banner untuk slider mobile app.</p>
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
                            <input type="number" name="urutan" value="1" min="1" max="999" 
                                class="form-input !h-9 !text-xs w-full border-slate-300 rounded-lg">
                            <span class="text-[10px] text-slate-400">Angka 1 akan tayang di urutan slide pertama.</span>
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
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-semibold text-slate-700 block">Tindakan Saat Banner Diklik (Link / WhatsApp)</label>
                            <button type="button" onclick="fillWaTemplate('create')" class="text-emerald-600 hover:text-emerald-700 font-semibold text-[11px] flex items-center gap-1">
                                <i class="fa-brands fa-whatsapp"></i> Gunakan WhatsApp CS
                            </button>
                        </div>
                        <input type="text" name="link_url" id="create_link_url" placeholder="Contoh: https://wa.me/{{ $defaultWa }}?text=Halo%20Admin%20saya%20tertarik%20promo" 
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
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3.5 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeCustomModal('createBannerModal')" class="btn-secondary !h-8.5 !px-4 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitCreate" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !h-8.5 !px-5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Simpan Banner
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
                    <div>
                        <h3 class="text-base font-bold text-slate-900 m-0">Edit Promosi & Informasi</h3>
                        <p class="text-xs text-slate-500 m-0 mt-0.5">Perbarui detail banner atau ganti gambar promosi.</p>
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
                            <input type="number" name="urutan" id="edit_urutan" min="1" max="999" 
                                class="form-input !h-9 !text-xs w-full border-slate-300 rounded-lg">
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
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-semibold text-slate-700 block">Tindakan Saat Banner Diklik (Link / WhatsApp)</label>
                            <button type="button" onclick="fillWaTemplate('edit')" class="text-emerald-600 hover:text-emerald-700 font-semibold text-[11px] flex items-center gap-1">
                                <i class="fa-brands fa-whatsapp"></i> Gunakan WhatsApp CS
                            </button>
                        </div>
                        <input type="text" name="link_url" id="edit_link_url" 
                            class="form-input !h-9 !text-xs w-full border-slate-300 rounded-lg">
                    </div>

                    <!-- Deskripsi Singkat -->
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1">Catatan / Deskripsi (Opsional)</label>
                        <textarea name="deskripsi" id="edit_deskripsi" rows="2" 
                            class="form-input !text-xs w-full border-slate-300 rounded-lg"></textarea>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="modal-footer border-t border-slate-200 bg-slate-50 px-6 py-3.5 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeCustomModal('editBannerModal')" class="btn-secondary !h-8.5 !px-4 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitEdit" class="btn-primary-admin !bg-sky-600 hover:!bg-sky-700 !h-8.5 !px-5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i> Perbarui Banner
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: KONFIRMASI HAPUS BANNER                                          -->
    <!-- ========================================================================= -->
    <div class="modal-overlay modal-centered" id="deleteBannerModal">
        <div class="modal-box max-w-sm bg-white border border-slate-300 shadow-2xl p-6">
            <div class="text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-trash-can text-lg"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800 m-0">Hapus Promosi & Informasi</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Banner <b id="delete_banner_title" class="text-slate-800"></b> akan dihapus permanen dari sistem dan aplikasi mobile pelanggan.
                </p>
            </div>
            <div class="mt-6 flex items-center justify-center gap-2">
                <button type="button" onclick="closeCustomModal('deleteBannerModal')" class="btn-cancel !h-8.5 !px-4 text-xs font-semibold">
                    Batal
                </button>
                <button type="button" id="btnConfirmDelete" onclick="executeDeleteBanner()" class="btn-primary-admin !bg-rose-600 hover:!bg-rose-700 !h-8.5 !px-5 text-xs font-semibold shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-trash"></i> Hapus Sekarang
                </button>
            </div>
        </div>
    </div>

    @section('Scripts')
        <script>
            const defaultSupportWa = "{{ $defaultWa }}";
            let pendingDeleteId = null;

            $(document).ready(function() {
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
                openCustomModal('createBannerModal');
            }

            function openEditModal(banner) {
                const form = document.getElementById('editBannerForm');
                form.action = `/banners/${banner.id}`;

                document.getElementById('edit_judul').value = banner.judul || '';
                document.getElementById('edit_urutan').value = banner.urutan || 1;
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

            function fillWaTemplate(prefix) {
                const judul = document.getElementById(`${prefix}_judul`).value.trim() || 'Paket Internet';
                const text = encodeURIComponent(`Halo Admin Erlangga Net, saya tertarik dengan promo: "${judul}". Boleh info detailnya?`);
                document.getElementById(`${prefix}_link_url`).value = `https://wa.me/${defaultSupportWa}?text=${text}`;
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
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        closeCustomModal('createBannerModal');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => window.location.reload());
                        } else {
                            alert(data.message);
                            window.location.reload();
                        }
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = origHtml;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Periksa kembali formulir.' });
                        } else {
                            alert(data.message || 'Periksa kembali formulir.');
                        }
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    alert('Terjadi kesalahan jaringan.');
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
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        closeCustomModal('editBannerModal');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => window.location.reload());
                        } else {
                            alert(data.message);
                            window.location.reload();
                        }
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = origHtml;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Periksa kembali formulir.' });
                        } else {
                            alert(data.message || 'Periksa kembali formulir.');
                        }
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    alert('Terjadi kesalahan jaringan.');
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
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.textContent = origText;
                });
            }

            function confirmDeleteBanner(id, title) {
                pendingDeleteId = id;
                document.getElementById('delete_banner_title').textContent = `"${title}"`;
                openCustomModal('deleteBannerModal');
            }

            function executeDeleteBanner() {
                if (!pendingDeleteId) return;

                const btn = document.getElementById('btnConfirmDelete');
                const origHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = "<i class='fa-solid fa-circle-notch fa-spin'></i> Menghapus...";

                fetch(`/banners/${pendingDeleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        closeCustomModal('deleteBannerModal');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Dihapus!',
                                text: data.message,
                                timer: 1200,
                                showConfirmButton: false
                            }).then(() => window.location.reload());
                        } else {
                            window.location.reload();
                        }
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = origHtml;
                        alert(data.message || 'Gagal menghapus.');
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    alert('Gagal menghapus banner.');
                });
            }

            // Daftarkan fungsi ke window scope agar selalu dapat diakses oleh onclick
            window.openCustomModal = openCustomModal;
            window.closeCustomModal = closeCustomModal;
            window.openCreateModal = openCreateModal;
            window.openEditModal = openEditModal;
            window.handleImagePreview = handleImagePreview;
            window.toggleEndDate = toggleEndDate;
            window.fillWaTemplate = fillWaTemplate;
            window.handleCreateSubmit = handleCreateSubmit;
            window.handleEditSubmit = handleEditSubmit;
            window.toggleBannerStatus = toggleBannerStatus;
            window.confirmDeleteBanner = confirmDeleteBanner;
            window.executeDeleteBanner = executeDeleteBanner;
        </script>
    @endsection
</x-layouts.app>
