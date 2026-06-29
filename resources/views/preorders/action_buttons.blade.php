<div class="flex items-center justify-center gap-1">
    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
        onclick="openCustomModal('detailModal', this)"
        data-nama="{{ $po->nama_lengkap }}" data-identitas="{{ $po->no_identitas }}"
        data-email-lengkap="{{ $po->alamat_email }}"
        data-alamat-lengkap="{{ $po->alamat_lengkap }}"
        data-paket="{{ $po->paket_internet }}"
        data-koordinat="{{ $po->titik_koordinat }}"
        data-foto-ktp="{{ $po->foto_ktp ? Storage::url($po->foto_ktp) : '' }}"
        data-foto-rumah="{{ $po->foto_rumah ? Storage::url($po->foto_rumah) : '' }}"><i
            class="fa-solid fa-circle-info text-primary"></i></button>
    @if ($po->is_unsubscribed)
        <span class="badge badge-archived">BERHENTI</span>
    @elseif ($po->is_active)
        <span class="badge badge-published">AKTIF</span>
    @elseif ($po->is_unspek)
        @if ($po->is_unspek_approved)
            <span class="badge" style="background:#fef2f2;color:#be123c;">UNSPEK</span>
            @if (Auth::user()->isAdmin() || (auth()->user()->jabatan === 'Sales' && $po->id_sales == auth()->id()))
                <form action="{{ route('pre_orders.reopen_unspek', $po->id) }}" method="POST"
                    class="inline">@csrf<button type="submit" title="Buka Kembali"
                        class="topbar-icon-btn !w-8 !h-8 !rounded-lg"><i
                            class="fa-solid fa-folder-open text-sky-500"></i></button></form>
            @endif
        @else
            <span class="badge" style="background:#fef3c7;color:#b45309;">UNSPEK (PENDING)</span>
            @if (Auth::user()->isAdmin() || (auth()->user()->jabatan === 'Sales' && $po->id_sales == auth()->id()))
                <form action="{{ route('pre_orders.approve_unspek', $po->id) }}" method="POST"
                    class="inline">@csrf<button type="submit" title="Setujui Unspek"
                        class="topbar-icon-btn !w-8 !h-8 !rounded-lg"><i
                            class="fa-solid fa-check text-emerald-500"></i></button></form>
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
                data-po-id="{{ $po->id }}" data-po-nama="{{ $po->nama_lengkap }}"><i
                    class="fa-solid fa-ban text-amber-500"></i></button>
        @endif
        @if (auth()->user()->jabatan === 'Teknisi')
            <button type="button" class="btn-primary-admin !h-8 !px-2 !text-[10px]"
                onclick="openActivationModal(this)" data-po-id="{{ $po->id }}"
                data-po-sales-id="{{ $po->id_sales }}"
                data-po-nama="{{ $po->nama_lengkap }}"
                data-po-paket="{{ $po->paket_internet }}"><i
                    class="fa-solid fa-bolt"></i></button>
        @endif
        @if (in_array(auth()->user()->jabatan, ['Administrator', 'Sales']))
            <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                onclick="openCustomModal('editModal', this)"
                data-po-id="{{ $po->id }}" data-po-nama="{{ $po->nama_lengkap }}"
                data-po-paket="{{ $po->paket_internet }}"
                data-po-wa="{{ $po->no_whatsapp }}"
                data-po-email="{{ $po->alamat_email }}"
                data-po-alamat="{{ $po->alamat_lengkap }}"
                data-po-identitas="{{ $po->no_identitas }}"
                data-po-koordinat="{{ $po->titik_koordinat }}"><i
                    class="fa-solid fa-pen-to-square text-amber-500"></i></button>
            <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg"
                onclick="confirmGlobalDelete('{{ route('pre_orders.destroy', $po->id) }}', '{{ $po->nama_lengkap }}')"><i
                    class="fa-solid fa-trash text-rose-600"></i></button>
        @endif
    @endif
</div>
