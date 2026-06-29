<div class="flex items-center justify-center gap-2">
    <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl"
        onclick="openMaintenanceDetailModal(this)"
        data-nama="{{ $m->customer->nama_lengkap ?? 'N/A' }}"
        data-id-pel="{{ $m->customer->id_pelanggan ?? '-' }}"
        data-pppoe="{{ $m->customer->pppoe_username ?? '-' }}"
        data-wa="{{ $m->customer->no_whatsapp ?? '-' }}"
        data-tikor="{{ $m->customer->preOrder->titik_koordinat ?? 'N/A' }}"
        data-alamat="{{ $m->customer->alamat_lengkap ?? '-' }}"
        data-kendala="{{ $m->keterangan_kendala }}"
        data-foto="{{ $m->foto_kendala_awal ? Storage::url($m->foto_kendala_awal) : '' }}">
        <i class="fa-solid fa-circle-info text-primary"></i>
    </button>
    @if(auth()->user()->jabatan === 'Teknisi' || auth()->user()->isAdmin())
        <button type="button" class="btn-primary-admin !h-9 !px-3 !text-[11px]"
            onclick="openCompleteModal(this)" data-id="{{ $m->id }}"
            data-nama="{{ $m->customer->nama_lengkap }}"
            data-sn="{{ $m->customer->sn_perangkat }}">
            <i class="fa-solid fa-hand-holding-hand"></i> Ambil & Selesaikan
        </button>
    @endif
</div>
