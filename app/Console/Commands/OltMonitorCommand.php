<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\OltController;
use Illuminate\Support\Facades\Cache;

class OltMonitorCommand extends Command
{
    protected $signature = 'olt:monitor';
    protected $description = 'Sync OLT dashboard data every 5 minutes in background';

    public function handle(OltController $oltController)
    {
        $this->info("Starting OLT Background Sync Monitor (Keep-Alive)...");
        $this->warn("Press Ctrl+C to stop.");

        while (true) {
            try {
                $this->info(now()->format('Y-m-d H:i:s') . " - Fetching global OLT data...");
                
                $result = $oltController->syncGlobalData(true);

                if (isset($result['success']) && $result['success']) {
                    $this->info(now()->format('Y-m-d H:i:s') . " - OLT Data synced successfully!");
                } else {
                    $this->error(now()->format('Y-m-d H:i:s') . " - Failed to sync OLT data: " . ($result['error'] ?? 'Unknown Error'));
                }
            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
                app()->forgetInstance('App\Services\OltTelnetService');
                sleep(5);
            }

            // Sleep 5 minutes (300 seconds)
            sleep(300);
        }
    }
}
