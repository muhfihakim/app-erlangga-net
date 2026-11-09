<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\OltController;

class OltSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'olt:sync {--force : Paksa refresh data terbaru dari OLT}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data OLT (SNMP/Telnet) ke cache sistem di background';

    /**
     * Execute the console command.
     */
    public function handle(OltController $oltController): int
    {
        $this->info(now()->format('Y-m-d H:i:s') . ' - Memulai sinkronisasi data OLT...');
        $force = (bool) $this->option('force');

        try {
            $result = $oltController->syncGlobalData($force);

            if (!empty($result['success'])) {
                $driver = strtoupper($result['driver'] ?? 'UNKNOWN');
                $countCfg = count($result['configured'] ?? ($result['data'] ?? []));
                $countUncfg = count($result['unconfigured'] ?? []);
                $elapsed = $result['elapsed_sec'] ?? '-';

                $this->info(now()->format('Y-m-d H:i:s') . " - [SUKSES] Driver: {$driver} | ONU: {$countCfg} | Unconfigured: {$countUncfg} | Durasi: {$elapsed}s");
                return Command::SUCCESS;
            }

            $error = $result['error'] ?? ($result['fallback_reason'] ?? 'Gagal menghubungi OLT');
            $this->error(now()->format('Y-m-d H:i:s') . " - [GAGAL] {$error}");
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $this->error(now()->format('Y-m-d H:i:s') . ' - [EXCEPTION] ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
