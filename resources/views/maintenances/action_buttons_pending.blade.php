<div class="flex items-center justify-center gap-1.5">
    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" title="Detail Kendala"
        onclick="openMaintenanceDetailModal(this)"
        data-nama="{{ $m->customer->nama_lengkap ?? 'N/A' }}"
        data-id-pel="{{ $m->customer->id_pelanggan ?? '-' }}"
        data-pppoe="{{ $m->customer->pppoe_username ?? '-' }}"
        data-wa="{{ $m->customer->no_whatsapp ?? '-' }}"
        data-tikor="{{ $m->customer->preOrder->titik_koordinat ?? 'N/A' }}"
        data-alamat="{{ $m->customer->alamat_lengkap ?? '-' }}"
        data-kendala="{{ $m->keterangan_kendala }}"
        data-foto="{{ $m->foto_kendala_awal ? Storage::url($m->foto_kendala_awal) : '' }}">
        <i class="fa-solid fa-circle-info text-slate-500"></i>
    </button>
    @if(auth()->user()->jabatan === 'Teknisi' || auth()->user()->isAdmin())
        <button type="button" class="btn-primary-admin !h-8 !px-3 !text-xs font-semibold"
            onclick="openCompleteModal(this)" data-id="{{ $m->id }}"
            data-nama="{{ $m->customer->nama_lengkap }}"
            data-sn="{{ $m->customer->sn_perangkat }}">
            Selesaikan
        </button>
    @endif
</div>
