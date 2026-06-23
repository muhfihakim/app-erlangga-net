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
        if (count($active) > 0 && count($active) <= 5) {
            $this->info("Sessions: " . json_encode($active, JSON_PRETTY_PRINT));
        }

        $this->info("Testing RAW connection...");
        $client = new \ZillEAli\MikrotikLaravel\Connections\RouterosClient(
            env('MIKROTIK_HOST'),
            env('MIKROTIK_PORT', 8728),
            env('MIKROTIK_USER'),
            env('MIKROTIK_PASS')
        );
        $client->connect();
        $raw = $client->send(['/ppp/active/print']);
        $this->info("Raw words count: " . count($raw));
        $reCount = 0;
        foreach($raw as $w) if ($w === '!re') $reCount++;
        $this->info("!re count: " . $reCount);

        $pppoe = $service->getPppoeActive();
        if (isset($pppoe['success']) && $pppoe['success']) {
            $this->info("Service returned " . count($pppoe['data']) . " merged sessions.");
        } else {
            $this->error("Service failed: " . json_encode($pppoe));
        }
    }
}
