<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasFactory;

    protected $table = 'banners';

    protected $fillable = [
        'judul',
        'gambar',
        'tanggal_mulai',
        'tanggal_selesai',
        'urutan',
        'link_url',
        'tindakan_tipe',
        'deskripsi',
        'is_active',
        'klik_count',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'is_active'       => 'boolean',
        'urutan'          => 'integer',
        'klik_count'      => 'integer',
    ];

    protected $appends = [
        'gambar_url',
        'status_penayangan',
        'is_whatsapp',
    ];

    /**
     * Relasi ke user pembuat banner
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * URL absolut file gambar banner dari Cloud Storage (S3)
     */
    public function getGambarUrlAttribute(): string
    {
        if (empty($this->gambar)) {
            return asset('dist/assets/img/logo.png');
        }

        if (str_starts_with($this->gambar, 'http://') || str_starts_with($this->gambar, 'https://')) {
            return $this->gambar;
        }

        try {
            return Storage::disk('s3')->url($this->gambar);
        } catch (\Throwable $e) {
            return Storage::url($this->gambar);
        }
    }

    /**
     * Cek apakah link tindakan mengarah ke WhatsApp
     */
    public function getIsWhatsappAttribute(): bool
    {
        if (empty($this->link_url)) {
            return false;
        }
        return str_contains($this->link_url, 'wa.me') || str_contains($this->link_url, 'whatsapp.com');
    }

    /**
     * Status penayangan banner saat ini
     * Nilai: 'Sedang Tayang', 'Terjadwal', 'Kedaluwarsa', 'Nonaktif'
     */
    public function getStatusPenayanganAttribute(): string
    {
        if (!$this->is_active) {
            return 'Nonaktif';
        }

        $today = Carbon::today();

        if ($this->tanggal_mulai && Carbon::parse($this->tanggal_mulai)->isAfter($today)) {
            return 'Terjadwal';
        }

        if ($this->tanggal_selesai && Carbon::parse($this->tanggal_selesai)->isBefore($today)) {
            return 'Kedaluwarsa';
        }

        return 'Sedang Tayang';
    }

    /**
     * Badge status penayangan untuk tampilan admin
     */
    public function getStatusBadgeHtmlAttribute(): string
    {
        $status = $this->status_penayangan;

        return match ($status) {
            'Sedang Tayang' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Sedang Tayang</span>',
            'Terjadwal'     => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200"><i class="fa-solid fa-clock text-[10px]"></i>Terjadwal</span>',
            'Kedaluwarsa'   => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200"><i class="fa-solid fa-calendar-xmark text-[10px]"></i>Kedaluwarsa</span>',
            default         => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200"><i class="fa-solid fa-pause text-[10px]"></i>Nonaktif</span>',
        };
    }

    /**
     * Scope untuk banner yang aktif dan sedang dalam masa tayang (untuk API mobile slider)
     */
    public function scopeActive($query)
    {
        $today = Carbon::today()->toDateString();

        return $query->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_mulai')
                  ->orWhere('tanggal_mulai', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_selesai')
                  ->orWhere('tanggal_selesai', '>=', $today);
            })
            ->orderBy('urutan', 'asc')
            ->orderBy('id', 'desc');
    }
}
