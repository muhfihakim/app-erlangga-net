<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    /**
     * Tampilkan Halaman Manajemen Banner Promosi & Informasi (Web Admin)
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status');

        $query = Banner::with('creator')->orderBy('urutan', 'asc')->orderBy('id', 'desc');

        $today = Carbon::today()->toDateString();

        if ($statusFilter === 'showing') {
            $query->where('is_active', true)
                ->where(function ($q) use ($today) {
                    $q->whereNull('tanggal_mulai')->orWhere('tanggal_mulai', '<=', $today);
                })
                ->where(function ($q) use ($today) {
                    $q->whereNull('tanggal_selesai')->orWhere('tanggal_selesai', '>=', $today);
                });
        } elseif ($statusFilter === 'scheduled') {
            $query->where('is_active', true)
                ->whereNotNull('tanggal_mulai')
                ->where('tanggal_mulai', '>', $today);
        } elseif ($statusFilter === 'expired') {
            $query->where('is_active', true)
                ->whereNotNull('tanggal_selesai')
                ->where('tanggal_selesai', '<', $today);
        } elseif ($statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $banners = $query->get();

        // Statistik Ringkasan Kartu
        $allBanners = Banner::all();
        $stats = [
            'total'     => $allBanners->count(),
            'showing'   => $allBanners->filter(fn($b) => $b->status_penayangan === 'Sedang Tayang')->count(),
            'scheduled' => $allBanners->filter(fn($b) => $b->status_penayangan === 'Terjadwal')->count(),
            'clicks'    => (int) $allBanners->sum('klik_count'),
        ];

        return view('banners.index', compact('banners', 'stats', 'statusFilter'));
    }

    /**
     * Simpan Banner Promosi Baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul'           => 'required|string|max:150',
            'banner_image'    => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'urutan'          => 'nullable|integer|min:1|max:999',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'link_url'        => 'nullable|string|max:500',
            'deskripsi'       => 'nullable|string|max:500',
            'is_active'       => 'nullable',
        ], [
            'judul.required'              => 'Judul promosi / banner wajib diisi.',
            'banner_image.required'       => 'File gambar banner wajib diunggah.',
            'banner_image.image'          => 'File yang diunggah harus berformat gambar.',
            'banner_image.mimes'          => 'Format gambar yang didukung: JPG, PNG, WEBP, SVG.',
            'banner_image.max'            => 'Ukuran gambar maksimal 5 MB.',
            'tanggal_selesai.after_or_equal' => 'Tanggal berakhir penayangan tidak boleh sebelum tanggal mulai.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $imagePath = $request->file('banner_image')->store('banners', 's3');

            $linkUrl = trim($request->input('link_url') ?? '');
            $tindakanTipe = 'url';
            if (!empty($linkUrl)) {
                if (str_contains($linkUrl, 'wa.me') || str_contains($linkUrl, 'whatsapp.com')) {
                    $tindakanTipe = 'whatsapp';
                }
            } else {
                $tindakanTipe = 'none';
            }

            $banner = Banner::create([
                'judul'           => $request->input('judul'),
                'gambar'          => $imagePath,
                'tanggal_mulai'   => $request->filled('tanggal_mulai') ? $request->input('tanggal_mulai') : Carbon::today()->toDateString(),
                'tanggal_selesai' => $request->filled('tanggal_selesai') ? $request->input('tanggal_selesai') : null,
                'urutan'          => $request->input('urutan', 1) ?: 1,
                'link_url'        => $linkUrl ?: null,
                'tindakan_tipe'   => $tindakanTipe,
                'deskripsi'       => $request->input('deskripsi'),
                'is_active'       => $request->has('is_active') ? (bool) $request->input('is_active') : true,
                'klik_count'      => 0,
                'created_by'      => auth()->id(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Banner promosi berhasil ditambahkan.',
                    'data'    => $banner
                ]);
            }

            return redirect()->route('banners.index')->with('success', 'Banner promosi berhasil ditambahkan.');
        } catch (\Throwable $e) {
            Log::error('Gagal menambah banner promosi: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem saat menyimpan banner: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal menyimpan banner: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Perbarui Banner Promosi
     */
    public function update(Request $request, Banner $banner)
    {
        $validator = Validator::make($request->all(), [
            'judul'           => 'required|string|max:150',
            'banner_image'    => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'urutan'          => 'nullable|integer|min:1|max:999',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'link_url'        => 'nullable|string|max:500',
            'deskripsi'       => 'nullable|string|max:500',
            'is_active'       => 'nullable',
        ], [
            'judul.required'              => 'Judul promosi / banner wajib diisi.',
            'banner_image.image'          => 'File yang diunggah harus berformat gambar.',
            'banner_image.mimes'          => 'Format gambar yang didukung: JPG, PNG, WEBP, SVG.',
            'banner_image.max'            => 'Ukuran gambar maksimal 5 MB.',
            'tanggal_selesai.after_or_equal' => 'Tanggal berakhir penayangan tidak boleh sebelum tanggal mulai.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = [
                'judul'           => $request->input('judul'),
                'tanggal_mulai'   => $request->filled('tanggal_mulai') ? $request->input('tanggal_mulai') : null,
                'tanggal_selesai' => $request->filled('tanggal_selesai') ? $request->input('tanggal_selesai') : null,
                'urutan'          => $request->input('urutan', 1) ?: 1,
                'deskripsi'       => $request->input('deskripsi'),
                'is_active'       => $request->has('is_active') ? (bool) $request->input('is_active') : false,
            ];

            $linkUrl = trim($request->input('link_url') ?? '');
            $data['link_url'] = $linkUrl ?: null;
            if (!empty($linkUrl)) {
                $data['tindakan_tipe'] = (str_contains($linkUrl, 'wa.me') || str_contains($linkUrl, 'whatsapp.com')) ? 'whatsapp' : 'url';
            } else {
                $data['tindakan_tipe'] = 'none';
            }

            // Ganti gambar jika ada unggahan baru (Media disimpan di S3)
            if ($request->hasFile('banner_image')) {
                $oldImage = $banner->gambar;
                $newPath = $request->file('banner_image')->store('banners', 's3');
                $data['gambar'] = $newPath;

                // Hapus gambar lama dari S3
                if ($oldImage) {
                    try {
                        Storage::disk('s3')->delete($oldImage);
                    } catch (\Throwable $e) {}
                }
            }

            $banner->update($data);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Banner promosi berhasil diperbarui.',
                    'data'    => $banner
                ]);
            }

            return redirect()->route('banners.index')->with('success', 'Banner promosi berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('Gagal memperbarui banner: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem saat memperbarui banner: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal memperbarui banner: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Toggle status aktif / nonaktif banner
     */
    public function toggleStatus(Banner $banner)
    {
        $newStatus = !$banner->is_active;
        $banner->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'success'   => true,
            'message'   => "Banner promosi berhasil {$statusText}.",
            'is_active' => $newStatus,
            'badge'     => $banner->status_badge_html
        ]);
    }

    /**
     * Hapus Banner Promosi
     */
    public function destroy(Banner $banner)
    {
        try {
            $imagePath = $banner->gambar;

            if ($imagePath) {
                try {
                    Storage::disk('s3')->delete($imagePath);
                } catch (\Throwable $e) {}
            }

            $banner->delete();

            return response()->json([
                'success' => true,
                'message' => 'Banner promosi berhasil dihapus.'
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * =========================================================================
     * API ENDPOINT UNTUK MOBILE APPS (SLIDER / SLIDESHOW BANNER)
     * URL: GET /api/banner/ (atau GET /api/banner / GET /api/banners)
     * =========================================================================
     */
    public function apiIndex(Request $request)
    {
        $banners = Banner::active()->get();

        $data = $banners->map(function (Banner $b) {
            return [
                'id'              => $b->id,
                'judul'           => (string) $b->judul,
                'gambar_url'      => (string) $b->gambar_url,
                'link_url'        => (string) ($b->link_url ?: ''),
                'urutan'          => (int) $b->urutan,
                'tanggal_mulai'   => $b->tanggal_mulai ? $b->tanggal_mulai->format('Y-m-d') : '',
                'tanggal_selesai' => $b->tanggal_selesai ? $b->tanggal_selesai->format('Y-m-d') : '',
                'is_active'       => (bool) $b->is_active,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data banner promosi berhasil dimuat.',
            'total'   => $data->count(),
            'data'    => $data,
        ]);
    }

    /**
     * API Track Klik Banner Promosi
     * URL: POST/GET /api/banner/{id}/click
     */
    public function apiClick(Request $request, $id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Banner promosi tidak ditemukan.'
            ], 404);
        }

        // Catat hitungan klik
        $banner->increment('klik_count');

        // Jika diakses lewat browser (misal redirect link), arahkan langsung ke target
        if (!$request->expectsJson() && $request->isMethod('get') && !empty($banner->link_url)) {
            return redirect()->away($banner->link_url);
        }

        return response()->json([
            'success' => true,
            'message' => 'Klik banner berhasil dicatat.',
            'data'    => [
                'id'         => $banner->id,
                'judul'      => $banner->judul,
                'link_url'   => $banner->link_url ?: '',
                'tindakan'   => $banner->link_url ?: '',
                'klik_count' => (int) $banner->klik_count,
            ]
        ]);
    }
}
