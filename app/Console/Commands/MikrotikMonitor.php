<?php

namespace App\Console\Commands;

use App\Services\MikroTikService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class MikrotikMonitor extends Command
{
    protected $signature = 'mikrotik:monitor';
    protected $description = 'Maintain persistent MikroTik connection and cache real-time traffic data';

    public function handle(MikroTikService $service)
    {
        $this->info("Starting MikroTik Persistent Monitoring (Keep-Alive)...");
        $this->warn("Press Ctrl+C to stop.");

        $lastPppoeFetch = 0;

        while (true) {
            try {
                $traffic = $service->getAllInterfacesTraffic();
                $status = $service->getStatus();

                Cache::put('mikrotik_traffic_data_all', $traffic, 5);
                Cache::put('mikrotik_status_data', $status, 5);

                // Fetch PPPoE heavily every 15 seconds to avoid API desync/timeout
                if (time() - $lastPppoeFetch >= 15) {
                    $pppoe = $service->getPppoeActive();
                    if (isset($pppoe['success']) && $pppoe['success']) {
                        Cache::put('mikrotik_pppoe_active', $pppoe, 25);
                        $count = count($pppoe['data'] ?? []);
                        $this->info(now()->format('Y-m-d H:i:s') . " - PPPoE updated: {$count} sessions.");
                    } else {
                        $this->error("PPPoE fetch failed: " . json_encode($pppoe));
                    }
                    $lastPppoeFetch = time();
                }

                $this->info(now()->format('Y-m-d H:i:s') . " - Traffic updated successfully.");
            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
                // Force reconnect on error if possible
                app()->forgetInstance('MikroTik');
                sleep(2);
            }

            sleep(1);
        }
    }
}
