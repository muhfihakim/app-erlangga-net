<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Maintenance;
use App\Models\ActivityLog;
use Carbon\Carbon;

class LogExpiredMaintenance extends Command
{
    protected $signature = 'log:expired-maintenance';
    protected $description = 'Log otomatis untuk penugasan maintenance yang expired (> 2 hari) dan belum selesai';

    public function handle(): int
    {
        // Cari maintenance yang: ditugaskan ke teknisi, masih pending, dan sudah lewat 2 hari
        $expiredMaintenances = Maintenance::where('status', 'Pending')
            ->whereNotNull('id_teknisi_delegasi')
            ->where('delegated_at', '<=', now()->subDays(2))
            ->get();

        foreach ($expiredMaintenances as $m) {
            // Cek apakah sudah pernah di-log agar tidak duplikat
            $alreadyLogged = ActivityLog::where('activity_type', 'maintenance_expired')
                ->where('description', 'like', "%Maintenance ID {$m->id}%")
                ->exists();

            if (!$alreadyLogged) {
                ActivityLog::create([
                    'user_id' => $m->id_teknisi_delegasi,
                    'activity_type' => 'maintenance_expired',
                    'description' => "Maintenance ID {$m->id} expired (Pelanggan: {$m->customer->nama_lengkap})",
                    'data' => [
                        'maintenance_id' => $m->id,
                        'delegated_at' => $m->delegated_at,
                        'expired_at' => now(),
                    ],
                    'logged_at' => now(),
                ]);
            }
        }

        $this->info("Log expired maintenance berhasil diperbarui.");
        return Command::SUCCESS;
    }
}
