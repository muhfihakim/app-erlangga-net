<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Pppoe;
use App\Models\Radcheck;
use App\Models\Radusergroup;
use App\Models\Userinfo;
use App\Services\MikroTikService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOverdueIsolirCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:check-overdue-isolir 
                            {--days=90 : Batas hari isolir tanpa pembayaran (default 90 hari / 3 bulan)}
                            {--kick : Putuskan koneksi aktif MikroTik saat dinonaktifkan}
                            {--dry-run : Mode simulasi (preview) tanpa mengubah database atau memutus koneksi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pengecekan otomatis pelanggan yang terisolir selama 3 bulan (90 hari) tanpa pembayaran untuk dinonaktifkan';

    /**
     * Execute the console command.
     */
    public function handle(MikroTikService $mikrotikService): int
    {
        $daysOption = $this->option('days');
        $days = ($daysOption !== null && is_numeric($daysOption)) ? (int) $daysOption : 90;
        $isDryRun = (bool) $this->option('dry-run');
        $thresholdDate = now()->subDays($days);

        $this->info("==================================================================");
        $this->info("  CRONJOB OTOMATIS: PENGECEKAN PELANGGAN 3 BULAN ISOLIR / NON-AKTIF");
        $this->info("==================================================================");
        $this->info("Waktu Server    : " . now()->format('Y-m-d H:i:s'));
        $this->info("Ambang Batas    : {$days} hari (sebelum " . $thresholdDate->format('Y-m-d') . ")");
        if ($isDryRun) {
            $this->warn("[MODE SIMULASI / DRY-RUN AKTIF] Tidak ada data yang diubah.");
        }

        // 1. Ambil akun PPPoE yang sedang berstatus ISOLIR (Sangat cepat dan efisien)
        $isolirPppoes = Pppoe::where('is_isolir', 1)->get();

        $deactivatedCount = 0;
        $tableRows = [];

        foreach ($isolirPppoes as $pppoe) {
            $username = $pppoe->username;

            // Cari Customer aktif terkait yang belum soft-delete
            $customer = Customer::whereNull('deleted_at')
                ->where(function ($q) use ($username, $pppoe) {
                    $q->where('pppoe_username', $username);
                    if ($pppoe->pre_order_id) {
                        $q->orWhere('pre_order_id', $pppoe->pre_order_id);
                    }
                })
                ->where(function ($q) {
                    $q->whereNull('status_pelanggan')
                      ->orWhere('status_pelanggan', '!=', 'non_aktif');
                })
                ->first();

            if (!$customer) {
                continue;
            }

            // Cek apakah ada invoice yang sudah dibayar dalam periode ambang batas (90 hari terakhir)
            $hasRecentPayment = Invoice::where('pppoe_id', $pppoe->id)
                ->where('status', 'paid')
                ->where('updated_at', '>=', $thresholdDate)
                ->exists();

            if ($hasRecentPayment) {
                continue; // Ada pembayaran terbaru, tidak dinonaktifkan
            }

            // Evaluasi durasi isolir atau tunggakan tertua
            $isOverdueThreshold = false;
            $sinceDateStr = '-';

            if ($pppoe->isolir_at && Carbon::parse($pppoe->isolir_at)->lte($thresholdDate)) {
                $isOverdueThreshold = true;
                $sinceDateStr = Carbon::parse($pppoe->isolir_at)->format('Y-m-d') . ' (Isolir)';
            } else {
                // Fallback jika isolir_at kosong: cek faktur unpaid tertua
                $oldestUnpaid = Invoice::where('pppoe_id', $pppoe->id)
                    ->where('status', 'unpaid')
                    ->orderBy('due_date', 'asc')
                    ->first();

                if ($oldestUnpaid && $oldestUnpaid->due_date && Carbon::parse($oldestUnpaid->due_date)->lte($thresholdDate)) {
                    $isOverdueThreshold = true;
                    $sinceDateStr = Carbon::parse($oldestUnpaid->due_date)->format('Y-m-d') . ' (Jatuh Tempo #' . $oldestUnpaid->invoice_number . ')';
                }
            }

            if (!$isOverdueThreshold) {
                continue;
            }

            $deactivatedCount++;
            $tableRows[] = [
                $customer->id_pelanggan ?: $customer->id,
                $customer->nama_lengkap,
                $username,
                $sinceDateStr,
                $isDryRun ? 'Akan Dinonaktifkan' : 'Berhasil Dinonaktifkan',
            ];

            if (!$isDryRun) {
                try {
                    // 1. Update data Customer menjadi non_aktif dengan alasan isolir_3_bulan
                    $customer->update([
                        'status_pelanggan' => 'non_aktif',
                        'alasan_nonaktif'  => 'isolir_3_bulan',
                        'nonaktif_at'      => now(),
                    ]);
                    $customer->delete(); // Soft delete sesuai arsitektur arsip aplikasi

                    // 2. Update akun PPPoE
                    $pppoe->update([
                        'is_isolir'     => true,
                        'isolir_reason' => 'Non-Aktif Otomatis: Tenggat 3 bulan isolir tanpa pembayaran',
                    ]);

                    // 3. Putuskan dan bersihkan kredensial FreeRADIUS
                    if ($username) {
                        Radcheck::where('username', $username)->delete();
                        Radusergroup::where('username', $username)->delete();
                        Userinfo::where('username', $username)->delete();

                        // 4. Kick sesi aktif dari router MikroTik
                        $mikrotikService->kickPppoeUser($username);
                    }

                    // 5. Catat ke ActivityLog
                    ActivityLog::create([
                        'user_id'       => null, // Aksi sistem otomatis / cronjob
                        'activity_type' => 'customer_auto_deactivate',
                        'description'   => "Otomatis menonaktifkan pelanggan {$customer->nama_lengkap} ({$username}) karena telah melewati batas 3 bulan masa isolir tanpa pembayaran.",
                        'logged_at'     => now(),
                    ]);

                } catch (\Throwable $e) {
                    $this->error("Gagal menonaktifkan pelanggan {$username}: " . $e->getMessage());
                    Log::error("Auto-deactivate customer error ({$username}): " . $e->getMessage());
                }
            }
        }

        if (!empty($tableRows)) {
            $this->table(
                ['ID Pelanggan', 'Nama Lengkap', 'PPPoE Username', 'Sejak Tanggal', 'Status Aksi'],
                $tableRows
            );
        } else {
            $this->line("Tidak ada pelanggan isolir yang melebihi batas {$days} hari tanpa pembayaran.");
        }

        $this->info("\n==================================================================");
        $this->info("  PROSES SELESAI: {$deactivatedCount} pelanggan " . ($isDryRun ? "teridentifikasi (dry-run)" : "berhasil dinonaktifkan") . ".");
        $this->info("==================================================================");

        return Command::SUCCESS;
    }
}
