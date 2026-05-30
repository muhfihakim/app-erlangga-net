<?php

namespace App\Console\Commands;

use App\Services\MikroTikService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class MikrotikMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mikrotik:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Maintain persistent MikroTik connection and cache real-time traffic data';

    /**
     * Execute the console command.
     */
    public function handle(MikroTikService $service)
    {
        $this->info("Starting MikroTik Persistent Monitoring (Keep-Alive)...");
        $this->warn("Press Ctrl+C to stop.");

        while (true) {
            try {
                $traffic = $service->getTraffic();
                $status = $service->getStatus();
                $pppoe = $service->getPppoeActive();

                // Store in cache for 5 seconds (to handle small gaps)
                Cache::put('mikrotik_traffic_data', $traffic, 5);
                Cache::put('mikrotik_status_data', $status, 5);
                Cache::put('mikrotik_pppoe_active', $pppoe, 5);

                $this->info(now()->format('Y-m-d H:i:s') . " - Data updated successfully.");
            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
                // Wait a bit longer on error
                sleep(2);
            }

            sleep(1);
        }
    }
}
