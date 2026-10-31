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
    @if ($m->status === 'Selesai')
        <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" title="Hasil Pemeliharaan"
            onclick="openMaintenanceResultModal(this)"
            data-nama="{{ $m->customer->nama_lengkap ?? 'N/A' }}"
            data-eksekutor="{{ $m->teknisiEksekutor->nama_lengkap ?? 'N/A' }}"
            data-partner="{{ $m->partner ?: '-' }}"
            data-kategori="{{ $m->kategori_kendala }}"
            data-sn-baru="{{ $m->sn_baru }}" data-sn-lama="{{ $m->sn_lama }}"
            data-solusi="{{ $m->solusi_kendala }}"
            data-foto="{{ $m->foto_kendala_awal ? Storage::url($m->foto_kendala_awal) : '' }}"
            data-foto-before="{{ Storage::url($m->foto_sebelum_perbaikan) }}"
            data-foto-after="{{ Storage::url($m->foto_setelah_perbaikan) }}"
            data-foto-speed="{{ Storage::url($m->foto_speedtest) }}"
            data-foto-selfie="{{ Storage::url($m->foto_selfie_teknisi) }}">
            <i class="fa-solid fa-file-circle-check text-emerald-600"></i>
        </button>
    @endif
    <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-rose-300 hover:!bg-rose-50" title="Hapus"
        onclick="confirmGlobalDelete('{{ route('maintenances.destroy', $m->id) }}', '{{ $m->customer->nama_lengkap ?? 'N/A' }}')">
        <i class="fa-solid fa-trash text-slate-400 hover:text-rose-500"></i>
    </button>
</div>
