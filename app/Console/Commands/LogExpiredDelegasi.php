<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PreOrder;
use App\Models\Customer;
use App\Models\ActivityLog;
use Carbon\Carbon;

class LogExpiredDelegasi extends Command
{
    protected $signature = 'log:expired-delegasi'; // <--- WAJIB SESUAI
    protected $description = 'Log otomatis untuk delegasi PO teknisi yang expired (> 2 hari) dan belum aktivasi';

    public function handle(): int
    {
        $expiredPOs = PreOrder::whereNotNull('delegated_at')
            ->where('delegated_at', '<=', now()->subDays(2))
            ->whereNotIn('id', Customer::pluck('pre_order_id'))
            ->get();

        foreach ($expiredPOs as $po) {
            $alreadyLogged = ActivityLog::where('activity_type', 'delegasi_expired')
                ->where('user_id', $po->id_teknisi_delegasi)
                ->where('description', 'like', "%{$po->id}%")
                ->exists();

            if (! $alreadyLogged) {
                ActivityLog::create([
                    'user_id' => $po->id_teknisi_delegasi,
                    'activity_type' => 'delegasi_expired',
                    'description' => "PO ID {$po->id} expired (delegasi ke teknisi ID {$po->id_teknisi_delegasi})",
                    'data' => [
                        'po_id' => $po->id,
                        'delegated_at' => $po->delegated_at,
                        'expired_at' => now(),
                    ],
                    'logged_at' => now(),
                ]);
            }
        }

        $this->info("Log expired delegasi berhasil diperbarui.");
        return Command::SUCCESS;
    }
}
