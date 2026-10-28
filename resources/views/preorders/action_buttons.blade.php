<div class="flex items-center justify-center gap-1">
    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
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
        data-status="{{ $po->is_unspek ? ($po->is_unspek_approved ? 'Unspek' : 'Unspek (Pending)') : 'Menunggu Aktivasi' }}"
        data-status-badge="{{ $po->is_unspek ? ($po->is_unspek_approved ? 'badge-archived' : 'badge-warning') : 'badge-draft' }}"
        data-foto-ktp="{{ $po->foto_ktp ? Storage::url($po->foto_ktp) : '' }}"
        data-foto-rumah="{{ $po->foto_rumah ? Storage::url($po->foto_rumah) : '' }}"
        title="Detail Informasi Pelanggan">
        <i class="fa-solid fa-circle-info text-slate-500"></i>
    </button>
    @if ($po->is_unspek)
        @if ($po->is_unspek_approved)
            <span class="badge text-[10px] font-bold" style="background:#fef2f2;color:#be123c;border:1px solid #fecaca;">UNSPEK</span>
            @if (Auth::user()->isAdmin() || (auth()->user()->jabatan === 'Sales' && $po->id_sales == auth()->id()))
                <form action="{{ route('pre_orders.reopen_unspek', $po->id) }}" method="POST"
                    class="inline">@csrf<button type="submit" title="Buka Kembali"
                        class="topbar-icon-btn !w-8 !h-8 !rounded-lg"><i
                            class="fa-solid fa-folder-open text-sky-600"></i></button></form>
            @endif
        @else
            <span class="badge text-[10px] font-bold" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;">UNSPEK (PENDING)</span>
            @if (Auth::user()->isAdmin() || (auth()->user()->jabatan === 'Sales' && $po->id_sales == auth()->id()))
                <form action="{{ route('pre_orders.approve_unspek', $po->id) }}" method="POST"
                    class="inline">@csrf<button type="submit" title="Setujui Unspek"
                        class="topbar-icon-btn !w-8 !h-8 !rounded-lg"><i
                            class="fa-solid fa-check text-emerald-600"></i></button></form>
                <form action="{{ route('pre_orders.reopen_unspek', $po->id) }}" method="POST"
                    class="inline">@csrf<button type="submit" title="Tolak (Buka Kembali)"
                        class="topbar-icon-btn !w-8 !h-8 !rounded-lg"><i
                            class="fa-solid fa-xmark text-rose-500"></i></button></form>
            @endif
        @endif
    @else
        @if (in_array(auth()->user()->jabatan, ['Administrator', 'Teknisi']))
            <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                onclick="openCustomModal('unspekModal', this)"
                data-po-id="{{ $po->id }}" data-po-nama="{{ $po->nama_lengkap }}"
                title="Tangguhkan (Unspek)"><i
                    class="fa-solid fa-ban text-amber-600"></i></button>
        @endif
        @if (auth()->user()->jabatan === 'Teknisi')
            <button type="button" class="btn-primary-admin !h-8 !px-2.5 !text-[11px] flex items-center gap-1"
                onclick="openActivationModal(this)" data-po-id="{{ $po->id }}"
                data-po-sales-id="{{ $po->id_sales }}"
                data-po-nama="{{ $po->nama_lengkap }}"
                data-po-paket="{{ $po->paket_id }}"
                title="Aktivasi Pelanggan"><i
                    class="fa-solid fa-bolt"></i> Aktivasi</button>
        @endif
        @if ($po->pppoe)
            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded-md border border-emerald-200" title="Akun PPPoE telah disiapkan ({{ $po->pppoe->username }})">
                <i class="fa-solid fa-check mr-0.5"></i> PPPoE Siap
            </span>
        @else
            @if (auth()->user()->isAdmin())
                <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                    onclick="openCustomModal('createPppoeModal', this)" data-po-id="{{ $po->id }}"
                    data-po-nama="{{ $po->nama_lengkap }}" data-po-alamat="{{ $po->alamat_lengkap }}"
                    data-po-paket="{{ $po->paket_id }}"
                    title="Buat Akun PPPoE"><i class="fa-solid fa-network-wired text-sky-600"></i></button>
            @endif
        @endif
        @if (in_array(auth()->user()->jabatan, ['Administrator', 'Sales']))
            <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
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
                title="Edit Data"><i
                    class="fa-solid fa-pen-to-square text-slate-600"></i></button>
            <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-rose-300 hover:!bg-rose-50"
                onclick="confirmGlobalDelete('{{ route('pre_orders.destroy', $po->id) }}', '{{ $po->nama_lengkap }}')"
                title="Hapus Pre-Order"><i
                    class="fa-solid fa-trash text-rose-500"></i></button>
        @endif
    @endif
</div>
