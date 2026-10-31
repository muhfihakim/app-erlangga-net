<div class="flex items-center justify-center gap-1.5 whitespace-nowrap flex-nowrap">
    <button type="button" class="btn-primary-admin !h-8 !px-2.5 text-xs font-semibold shadow-xs flex items-center justify-center gap-1.5 !bg-sky-600 hover:!bg-sky-700"
        onclick="openCustomModal('detailModal', this)"
        data-po-id="{{ $po->id }}"
        data-nama="{{ $po->nama_lengkap }}"
        data-identitas="{{ $po->no_identitas ?: '-' }}"
        data-email-lengkap="{{ $po->alamat_email ?: '-' }}"
        data-wa="{{ $po->no_whatsapp ?: '-' }}"
        data-sales="{{ $po->sales ? $po->sales->nama_lengkap : ($po->nama_sales ?: '-') }}"
        data-tgl="{{ $po->created_at ? $po->created_at->format('d/m/Y H:i') : '-' }}"
        data-alamat-lengkap="{{ $po->alamat_lengkap ?: '-' }}"
        data-paket="{{ $po->paket ? $po->paket->nama_paket : '-' }}"
        data-paket-harga="{{ $po->paket ? 'Rp ' . number_format($po->paket->harga, 0, ',', '.') : '' }}"
        data-koordinat="{{ $po->titik_koordinat ?: '' }}"
        data-status="{{ $po->is_unspek ? ($po->is_unspek_approved ? 'Unspek' : 'Unspek (Pending)') : ($po->pppoe ? 'PPPoE Siap' : 'Menunggu PPPoE') }}"
        data-status-badge="{{ $po->is_unspek ? ($po->is_unspek_approved ? 'badge-archived' : 'badge-warning') : ($po->pppoe ? 'badge-published' : 'badge-draft') }}"
        data-foto-ktp="{{ $po->foto_ktp ? Storage::url($po->foto_ktp) : '' }}"
        data-foto-rumah="{{ $po->foto_rumah ? Storage::url($po->foto_rumah) : '' }}"
        title="Detail Informasi Pelanggan">
        <i class="fa-solid fa-eye text-xs"></i> Detail
    </button>
    @if ($po->is_unspek)
        @if ($po->is_unspek_approved)
            <span class="badge text-[10px] font-bold" style="background:#fef2f2;color:#be123c;border:1px solid #fecaca;">UNSPEK</span>
            @if (Auth::user()->isAdmin() || (auth()->user()->jabatan === 'Sales' && $po->id_sales == auth()->id()))
                <form action="{{ route('pre_orders.reopen_unspek', $po->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" title="Buka Kembali" class="btn-primary-admin !h-8 !px-2.5 text-xs font-semibold shadow-xs flex items-center justify-center gap-1.5 !bg-sky-600 hover:!bg-sky-700">
                        <i class="fa-solid fa-folder-open text-xs"></i> Buka
                    </button>
                </form>
            @endif
        @else
            <span class="badge text-[10px] font-bold" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;">UNSPEK (PENDING)</span>
            @if (Auth::user()->isAdmin() || (auth()->user()->jabatan === 'Sales' && $po->id_sales == auth()->id()))
                <form action="{{ route('pre_orders.approve_unspek', $po->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" title="Setujui Unspek" class="btn-primary-admin !h-8 !px-2.5 text-xs font-semibold shadow-xs flex items-center justify-center gap-1.5 !bg-emerald-600 hover:!bg-emerald-700">
                        <i class="fa-solid fa-check text-xs"></i> Setuju
                    </button>
                </form>
                <form action="{{ route('pre_orders.reopen_unspek', $po->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" title="Tolak (Buka Kembali)" class="btn-primary-admin !h-8 !px-2.5 text-xs font-semibold shadow-xs flex items-center justify-center gap-1.5 !bg-rose-600 hover:!bg-rose-700 text-white">
                        <i class="fa-solid fa-xmark text-xs"></i> Tolak
                    </button>
                </form>
            @endif
        @endif
    @else
        @if (in_array(auth()->user()->jabatan, ['Administrator', 'Teknisi']))
            <button type="button" class="btn-primary-admin !h-8 !px-2.5 text-xs font-semibold shadow-xs flex items-center justify-center gap-1.5 !bg-amber-600 hover:!bg-amber-700 text-white"
                onclick="openCustomModal('unspekModal', this)"
                data-po-id="{{ $po->id }}" data-po-nama="{{ $po->nama_lengkap }}"
                title="Tangguhkan (Unspek)">
                <i class="fa-solid fa-ban text-xs"></i> Unspek
            </button>
        @endif
        @if (auth()->user()->jabatan === 'Teknisi')
            <button type="button" class="btn-primary-admin !h-8 !px-2.5 text-xs font-semibold shadow-xs flex items-center justify-center gap-1.5"
                onclick="openActivationModal(this)" data-po-id="{{ $po->id }}"
                data-po-sales-id="{{ $po->id_sales }}"
                data-po-nama="{{ $po->nama_lengkap }}"
                data-po-paket="{{ $po->paket_id }}"
                title="Aktivasi Pelanggan">
                <i class="fa-solid fa-bolt text-xs"></i> Aktivasi
            </button>
        @endif
        @if ($po->pppoe)
            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded-md border border-emerald-200" title="Akun PPPoE telah disiapkan ({{ $po->pppoe->username }})">
                <i class="fa-solid fa-check mr-0.5"></i> PPPoE Siap
            </span>
        @else
            @if (auth()->user()->isAdmin())
                <button type="button" class="btn-primary-admin !h-8 !px-2.5 text-xs font-semibold shadow-xs flex items-center justify-center gap-1.5 !bg-indigo-600 hover:!bg-indigo-700"
                    onclick="openCustomModal('createPppoeModal', this)" data-po-id="{{ $po->id }}"
                    data-po-nama="{{ $po->nama_lengkap }}" data-po-alamat="{{ $po->alamat_lengkap }}"
                    data-po-paket="{{ $po->paket_id }}"
                    title="Buat Akun PPPoE">
                    <i class="fa-solid fa-network-wired text-xs"></i> PPPoE
                </button>
            @endif
        @endif
        @if (in_array(auth()->user()->jabatan, ['Administrator', 'Sales']))
            <button type="button" class="btn-primary-admin !h-8 !px-2.5 text-xs font-semibold shadow-xs flex items-center justify-center gap-1.5 !bg-amber-500 hover:!bg-amber-600"
                onclick="openCustomModal('editModal', this)"
                data-po-id="{{ $po->id }}" data-po-nama="{{ $po->nama_lengkap }}"
                data-po-paket="{{ $po->paket_id }}"
                data-po-wa="{{ $po->no_whatsapp }}"
                data-po-email="{{ $po->alamat_email }}"
                data-po-alamat="{{ $po->alamat_lengkap }}"
                data-po-identitas="{{ $po->no_identitas }}"
                data-po-koordinat="{{ $po->titik_koordinat }}"
                data-foto-ktp="{{ $po->foto_ktp ? Storage::url($po->foto_ktp) : '' }}"
                data-foto-rumah="{{ $po->foto_rumah ? Storage::url($po->foto_rumah) : '' }}"
                title="Edit">
                <i class="fa-solid fa-pen-to-square text-xs"></i> Edit
            </button>
            <button type="button" class="btn-primary-admin !h-8 !px-2.5 text-xs font-semibold shadow-xs flex items-center justify-center gap-1.5 !bg-rose-600 hover:!bg-rose-700 text-white"
                onclick="confirmGlobalDelete('{{ route('pre_orders.destroy', $po->id) }}', '{{ $po->nama_lengkap }}')"
                title="Hapus">
                <i class="fa-solid fa-trash text-xs"></i> Hapus
            </button>
        @endif
    @endif
</div>
