<?php

namespace App\Console\Commands;

use App\Services\MikroTikService;
use Illuminate\Console\Command;

class PppoeTest extends Command
{
    protected $signature = 'pppoe:test';
    protected $description = 'Test fetch PPPoE directly';

    public function handle(MikroTikService $service)
    {
        $this->info("Fetching PPPoE...");
        $active = \MikroTik::pppoe()->getActiveSessions();
        $this->info("Mikrotik returned " . count($active) . " sessions.");

        $pppoe = $service->getPppoeActive();
        if (isset($pppoe['success']) && $pppoe['success']) {
            $this->info("Service returned " . count($pppoe['data']) . " merged sessions.");
        } else {
            $this->error("Service failed: " . json_encode($pppoe));
        }
    }
}
