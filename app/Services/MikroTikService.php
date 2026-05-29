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
}