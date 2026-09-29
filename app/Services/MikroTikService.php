<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Config;

class MikroTikService
{
    private ?\ZillEAli\MikrotikLaravel\Connections\RouterosClient $client = null;
    protected string $interface;

    public function __construct()
    {
        $this->interface = \App\Models\Setting::get('mikrotik_interface', Config::get('mikrotik.interface', 'ether1'));
    }

    public function getHost(): string
    {
        $host = \App\Models\Setting::get('mikrotik_host');
        if (empty($host)) {
            $nas = \App\Models\Nas::first();
            $host = $nas?->nasname ?? Config::get('mikrotik.host', '192.168.10.1');
        }
        return $host;
    }

    public function getInterface(): string
    {
        return $this->interface;
    }

    protected function getClient(): \ZillEAli\MikrotikLaravel\Connections\RouterosClient
    {
        if (!$this->client || !$this->client->isConnected()) {
            $host = $this->getHost();
            $port = (int) \App\Models\Setting::get('mikrotik_port', Config::get('mikrotik.port', 8728));
            $user = \App\Models\Setting::get('mikrotik_user', Config::get('mikrotik.username', 'admin'));
            $pass = \App\Models\Setting::get('mikrotik_pass', Config::get('mikrotik.password', ''));

            $this->client = new \ZillEAli\MikrotikLaravel\Connections\RouterosClient(
                $host,
                $port,
                $user,
                $pass
            );
            $this->client->connect();
        }
        return $this->client;
    }

    /**
     * Get real-time traffic stats for the configured interface.
     *
     * @return array{success: bool, data?: array{rx_bytes: int, tx_bytes: int, rx_rate: float, tx_rate: float, interface: string, timestamp: string}, error?: string}
     */
    public function getTraffic(): array
    {
        try {
            $client = $this->getClient();
            
            // Raw query for traffic
            $trafficRaw = $client->send([
                '/interface/monitor-traffic',
                '=interface=' . $this->interface,
                '=once='
            ]);
            
            // Raw query for interface info
            $ifaceRaw = $client->send([
                '/interface/print',
                '?name=' . $this->interface
            ]);

            $traffic = $this->parseRawResponse($trafficRaw)[0] ?? [];
            $iface = $this->parseRawResponse($ifaceRaw)[0] ?? [];

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
            $this->client = null; // force reconnect next time
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get real-time traffic stats for all non-pppoe interfaces.
     */
    public function getAllInterfacesTraffic(): array
    {
        try {
            $client = $this->getClient();
            
            // Raw query for interface info
            $ifaceRaw = $client->send([
                '/interface/print'
            ]);
            $interfaces = $this->parseRawResponse($ifaceRaw);
            
            $names = [];
            $ifaceDataMap = [];
            foreach ($interfaces as $iface) {
                $name = $iface['name'] ?? '';
                if ($name && !str_starts_with(strtolower($name), '<pppoe-')) {
                    $names[] = $name;
                    $ifaceDataMap[$name] = $iface;
                }
            }

            if (empty($names)) {
                return ['success' => false, 'error' => 'No interfaces found'];
            }

            $trafficRaw = $client->send([
                '/interface/monitor-traffic',
                '=interface=' . implode(',', $names),
                '=once='
            ]);
            
            $traffics = $this->parseRawResponse($trafficRaw);
            
            $result = [];
            foreach ($traffics as $traffic) {
                $name = $traffic['name'] ?? '';
                if (!$name || !isset($ifaceDataMap[$name])) continue;

                $iface = $ifaceDataMap[$name];
                $rxBps = (int) ($traffic['rx-bits-per-second'] ?? 0);
                $txBps = (int) ($traffic['tx-bits-per-second'] ?? 0);

                $result[$name] = [
                    'interface' => $name,
                    'rx_bytes' => $rxBps,
                    'tx_bytes' => $txBps,
                    'rx_rate' => $rxBps, // Already in bits per second from Mikrotik, keeping it as is
                    'tx_rate' => $txBps, // Already in bits per second
                    'total_rx' => (int) ($iface['rx-byte'] ?? 0),
                    'total_tx' => (int) ($iface['tx-byte'] ?? 0),
                    'last_link_up' => $iface['last-link-up-time'] ?? null,
                    'link_downs' => (int) ($iface['link-downs'] ?? 0),
                    'timestamp' => now()->toIso8601String(),
                ];
            }

            return [
                'success' => true,
                'data' => $result,
            ];
        } catch (Exception $e) {
            $this->client = null; // force reconnect next time
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Helper to parse raw API response manually (bypasses vendor !re bug)
     */
    protected function parseRawResponse(array $raw): array
    {
        $result = [];
        $row = [];
        foreach ($raw as $word) {
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
    }

    /**
     * Helper to run raw query and bypass vendor package !re bug
     */
    public function runRawQuery(string $command, array $params = []): array
    {
        try {
            $client = $this->getClient();
            $query = [$command];
            foreach ($params as $key => $val) {
                if (str_starts_with($key, '?') || str_starts_with($key, '=')) {
                    $query[] = "{$key}={$val}";
                } else {
                    $query[] = "={$key}={$val}";
                }
            }
            $raw = $client->send($query);
            return $this->parseRawResponse($raw);
        } catch (\Exception $e) {
            $this->client = null; // force reconnect next time
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
        $host = $this->getHost();
        try {
            $client = $this->getClient();
            $client->send([
                '/interface/print',
                '?name=' . $this->interface
            ]);
            return [
                'online' => true,
                'interface' => $this->interface,
                'host' => $host,
            ];
        } catch (Exception $e) {
            $this->client = null;
            return [
                'online' => false,
                'interface' => $this->interface,
                'host' => $host,
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
            $client = $this->getClient();
            $names = implode(',', $interfaceNames);
            $resultsRaw = $client->send([
                '/interface/monitor-traffic',
                '=interface=' . $names,
                '=once='
            ]);
            $results = $this->parseRawResponse($resultsRaw);

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
            $this->client = null;
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

    public function createOrUpdateIpPool(string $name, string $ranges): bool
    {
        try {
            $client = $this->getClient();
            $pools = $this->runRawQuery('/ip/pool/print');
            $id = null;
            foreach ($pools as $pool) {
                if (isset($pool['name']) && $pool['name'] === $name) {
                    $id = $pool['.id'] ?? null;
                    break;
                }
            }

            if ($id) {
                $client->send([
                    '/ip/pool/set',
                    '=.id=' . $id,
                    '=ranges=' . $ranges
                ]);
            } else {
                $client->send([
                    '/ip/pool/add',
                    '=name=' . $name,
                    '=ranges=' . $ranges
                ]);
            }
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("createOrUpdateIpPool Error: " . $e->getMessage());
            return false;
        }
    }

    public function createOrUpdatePppProfile(string $name, $paramsOrLocalAddress = '192.168.100.1'): bool
    {
        try {
            $client = $this->getClient();
            $profiles = $this->runRawQuery('/ppp/profile/print');
            $id = null;
            foreach ($profiles as $profile) {
                if (isset($profile['name']) && $profile['name'] === $name) {
                    $id = $profile['.id'] ?? null;
                    break;
                }
            }

            $params = is_array($paramsOrLocalAddress) ? $paramsOrLocalAddress : ['local-address' => $paramsOrLocalAddress];

            $cmd = [];
            if ($id) {
                $cmd[] = '/ppp/profile/set';
                $cmd[] = '=.id=' . $id;
            } else {
                $cmd[] = '/ppp/profile/add';
                $cmd[] = '=name=' . $name;
            }

            foreach ($params as $key => $val) {
                if ($val !== null && $val !== '') {
                    $cmd[] = '=' . $key . '=' . $val;
                }
            }

            $client->send($cmd);
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("createOrUpdatePppProfile Error: " . $e->getMessage());
            return false;
        }
    }

    public function removePppProfile(string $name): bool
    {
        try {
            $client = $this->getClient();
            $profiles = $this->runRawQuery('/ppp/profile/print');
            $id = null;
            foreach ($profiles as $profile) {
                if (isset($profile['name']) && $profile['name'] === $name) {
                    $id = $profile['.id'] ?? null;
                    break;
                }
            }

            if ($id) {
                $client->send([
                    '/ppp/profile/remove',
                    '=.id=' . $id
                ]);
            }
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("removePppProfile Error: " . $e->getMessage());
            return false;
        }
    }

    public function createRemoteTunnel(string $targetIp, string $targetPort = '80'): array
    {
        try {
            $this->cleanupOldTunnels(); // Clean up expired tunnels first
            
            $client = $this->getClient();
            $host = Config::get('mikrotik.host');
            $randomPort = rand(50000, 60000);
            $timestamp = time();
            $comment = "remote-tunnel-{$timestamp}";
            
            $client->send([
                '/ip/firewall/nat/add',
                '=chain=dstnat',
                '=protocol=tcp',
                '=dst-port=' . $randomPort,
                '=action=dst-nat',
                '=to-addresses=' . $targetIp,
                '=to-ports=' . $targetPort,
                '=comment=' . $comment
            ]);

            return [
                'success' => true,
                'url' => "http://{$host}:{$randomPort}",
                'port' => $randomPort,
                'comment' => $comment
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function cleanupOldTunnels()
    {
        try {
            $client = $this->getClient();
            $natsRaw = $client->send(['/ip/firewall/nat/print']);
            $nats = $this->parseRawResponse($natsRaw);
            
            $currentTime = time();
            // TTL 15 minutes = 900 seconds
            $ttl = 900; 

            foreach ($nats as $nat) {
                $comment = $nat['comment'] ?? '';
                if (str_starts_with($comment, 'remote-tunnel-')) {
                    $ts = (int) str_replace('remote-tunnel-', '', $comment);
                    if ($ts > 0 && ($currentTime - $ts) > $ttl) {
                        $client->send([
                            '/ip/firewall/nat/remove',
                            '=.id=' . $nat['.id']
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("cleanupOldTunnels warning: " . $e->getMessage());
        }
    }

    /**
     * Kick / Disconnect an active PPPoE session by username
     */
    public function kickPppoeUser(string $username): bool
    {
        try {
            $client = $this->getClient();
            $sessionsRaw = $client->send([
                '/ppp/active/print',
                '?name=' . $username,
            ]);
            $sessions = $this->parseRawResponse($sessionsRaw);

            foreach ($sessions as $sess) {
                if (isset($sess['.id'])) {
                    $client->send([
                        '/ppp/active/remove',
                        '=.id=' . $sess['.id'],
                    ]);
                }
            }
            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("MikroTik kickPppoeUser ({$username}) warning: " . $e->getMessage());
            return false;
        }
    }
}
