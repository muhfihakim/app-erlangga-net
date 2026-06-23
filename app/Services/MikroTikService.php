<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Config;

class MikroTikService
{
    private string $interface;

    public function __construct()
    {
        $this->interface = Config::get('mikrotik.interface', 'ether1');
    }

    /**
     * Get real-time traffic stats for the configured interface.
     *
     * @return array{success: bool, data?: array{rx_bytes: int, tx_bytes: int, rx_rate: float, tx_rate: float, interface: string, timestamp: string}, error?: string}
     */
    public function getTraffic(): array
    {
        try {
            $traffic = \MikroTik::interfaces()->getTraffic($this->interface, 1);
            $iface = \MikroTik::interfaces()->getInterface($this->interface);

            if (empty($traffic)) {
                return ['success' => false, 'error' => 'No traffic data returned'];
            }

            $rxBps = (int) ($traffic['rx-bits-per-second'] ?? 0);
            $txBps = (int) ($traffic['tx-bits-per-second'] ?? 0);

            return [
                'success' => true,
                'data' => [
                    'interface' => $this->interface,
                    'rx_bytes' => $rxBps,
                    'tx_bytes' => $txBps,
                    'rx_rate' => round($rxBps / 8, 2),
                    'tx_rate' => round($txBps / 8, 2),
                    'total_rx' => (int) ($iface['rx-byte'] ?? 0),
                    'total_tx' => (int) ($iface['tx-byte'] ?? 0),
                    'last_link_up' => $iface['last-link-up-time'] ?? null,
                    'link_downs' => (int) ($iface['link-downs'] ?? 0),
                    'timestamp' => now()->toIso8601String(),
                ],
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Helper to run raw query and bypass vendor package !re bug
     */
    public function runRawQuery(string $command): array
    {
        try {
            $client = new \ZillEAli\MikrotikLaravel\Connections\RouterosClient(
                config('mikrotik.host', env('MIKROTIK_HOST')),
                config('mikrotik.port', env('MIKROTIK_PORT', 8728)),
                config('mikrotik.username', env('MIKROTIK_USER')),
                config('mikrotik.password', env('MIKROTIK_PASS'))
            );
            $client->connect();
            $raw = $client->send([$command]);
            $client->disconnect();

            $result = [];
            $row = [];
            foreach ($raw as $word) {
                // If we see !re or a new .id, start a new row
                if ($word === '!re' || str_starts_with($word, '=.id=')) {
                    if (!empty($row)) {
                        $result[] = $row;
                        $row = [];
                    }
                    if ($word === '!re') continue;
                }

                if (str_starts_with($word, '=')) {
                    $parts = explode('=', substr($word, 1), 2);
                    $row[$parts[0]] = $parts[1] ?? '';
                }
            }
            if (!empty($row)) {
                $result[] = $row;
            }
            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("runRawQuery Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check connection status to MikroTik.
     *
     * @return array{online: bool, interface: string, host: string}
     */
    public function getStatus(): array
    {
        try {
            \MikroTik::interfaces()->getInterface($this->interface);
            return [
                'online' => true,
                'interface' => $this->interface,
                'host' => Config::get('mikrotik.host'),
            ];
        } catch (Exception $e) {
            return [
                'online' => false,
                'interface' => $this->interface,
                'host' => Config::get('mikrotik.host'),
            ];
        }
    }

    /**
     * Get real-time traffic for specific PPPoE interfaces.
     * 
     * @param array $interfaceNames List of interface names (e.g., ["<pppoe-user1>", "<pppoe-user2>"])
     * @return array
     */
    public function getPppoeTraffic(array $interfaceNames): array
    {
        if (empty($interfaceNames)) return [];

        try {
            // monitor-traffic can take multiple interfaces comma-separated
            $names = implode(',', $interfaceNames);
            $results = \MikroTik::interfaces()->getTraffic($names, 1);

            // If only one interface, library might return single array instead of array of arrays
            if (count($interfaceNames) === 1 && isset($results['name'])) {
                $results = [$results];
            }

            $formatted = [];
            foreach ($results as $res) {
                $name = $res['name'] ?? '';
                $formatted[$name] = [
                    'rx_rate' => (int) ($res['rx-bits-per-second'] ?? 0),
                    'tx_rate' => (int) ($res['tx-bits-per-second'] ?? 0),
                ];
            }

            return $formatted;
        } catch (Exception $e) {
            return [];
        }
    }
    public function getPppoeActive(): array
    {
        try {
            $active = $this->runRawQuery('/ppp/active/print');
            
            // Get all interfaces to find the PPPoE dynamic ones
            $interfaces = $this->runRawQuery('/interface/print');

            $ifaceMap = [];
            foreach ($interfaces as $iface) {
                // PPPoE dynamic interfaces usually follow <pppoe-username> pattern
                if (isset($iface['name'])) {
                    $ifaceMap[$iface['name']] = $iface;
                }
            }

            $merged = [];
            foreach ($active as $session) {
                $name = $session['name'] ?? '';
                $ifaceName = "<pppoe-{$name}>";
                
                $ifaceData = $ifaceMap[$ifaceName] ?? null;
                
                $merged[] = array_merge($session, [
                    'bytes-in' => $ifaceData['rx-byte'] ?? 0,
                    'bytes-out' => $ifaceData['tx-byte'] ?? 0,
                    'local-address' => $ifaceData['address'] ?? ($session['local-address'] ?? '-'),
                ]);
            }

            return [
                'success' => true,
                'data' => array_values($merged),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
