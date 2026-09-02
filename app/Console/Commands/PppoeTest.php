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
            \App\Models\Setting::get('mikrotik_host', config('mikrotik.host')),
            (int) \App\Models\Setting::get('mikrotik_port', config('mikrotik.port', 8728)),
            \App\Models\Setting::get('mikrotik_user', config('mikrotik.username')),
            \App\Models\Setting::get('mikrotik_pass', config('mikrotik.password', ''))
        );
        $client->connect();
        $raw = $client->send(['/ppp/active/print']);
        $this->info("Raw words count: " . count($raw));
        $reCount = 0;
        $exclamations = [];
        $nonAttr = [];
        foreach($raw as $w) {
            if ($w === '!re') $reCount++;
            if (str_starts_with($w, '!')) {
                $exclamations[$w] = ($exclamations[$w] ?? 0) + 1;
            }
            if (!str_starts_with($w, '=')) {
                $nonAttr[$w] = ($nonAttr[$w] ?? 0) + 1;
            }
        }
        $this->info("!re count: " . $reCount);
        $this->info("Words starting with ! : " . json_encode($exclamations));
        $this->info("Non-attribute words : " . json_encode($nonAttr));

        $pppoe = $service->getPppoeActive();
        if (isset($pppoe['success']) && $pppoe['success']) {
            $this->info("Service returned " . count($pppoe['data']) . " merged sessions.");
        } else {
            $this->error("Service failed: " . json_encode($pppoe));
        }
    }
}
