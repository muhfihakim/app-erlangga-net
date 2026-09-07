<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenieAcsService
{
    protected string $url;
    protected string $username;
    protected string $password;
    protected int $timeout;

    public function __construct()
    {
        $this->url = rtrim(Setting::get('genieacs_url', env('GENIEACS_URL', 'http://203.2.151.130:3000')), '/');
        $this->username = Setting::get('genieacs_username', env('GENIEACS_USERNAME', 'admin'));
        $this->password = Setting::get('genieacs_password', env('GENIEACS_PASSWORD', 'admin'));
        $this->timeout = (int) Setting::get('genieacs_timeout', 10);
    }

    /**
     * Get JWT token for GenieACS UI API
     */
    public function getToken(bool $forceRefresh = false): ?string
    {
        $cacheKey = 'genieacs_jwt_token_' . md5($this->url . $this->username);

        if (!$forceRefresh) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        try {
            $response = Http::asForm()
                ->timeout($this->timeout)
                ->post("{$this->url}/login", [
                    'username' => $this->username,
                    'password' => $this->password,
                ]);

            if ($response->successful()) {
                $token = trim($response->body(), " \t\n\r\0\x0B\"");
                if (!empty($token)) {
                    Cache::put($cacheKey, $token, now()->addMinutes(50));
                    return $token;
                }
            }
        } catch (\Throwable $e) {
            Log::error("GenieACS login error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Test connection to GenieACS server
     */
    public function testConnection(): array
    {
        $startTime = microtime(true);

        try {
            $token = $this->getToken(true);
            if (!$token) {
                // If login fails, try basic auth on /devices (for standard NBI port 7557)
                $nbiTest = Http::withBasicAuth($this->username, $this->password)
                    ->timeout(5)
                    ->get("{$this->url}/devices?limit=1");

                if ($nbiTest->successful()) {
                    $elapsed = round((microtime(true) - $startTime) * 1000, 2);
                    return [
                        'success' => true,
                        'message' => "Terhubung ke GenieACS NBI [{$elapsed}ms]",
                    ];
                }

                return [
                    'success' => false,
                    'message' => "Gagal autentikasi ke GenieACS ({$this->url}). Periksa URL & Kredensial.",
                ];
            }

            // Fetch 1 device projection to verify token
            $response = Http::withToken($token)
                ->timeout(5)
                ->get("{$this->url}/api/devices/?projection=_id");

            $elapsed = round((microtime(true) - $startTime) * 1000, 2);

            if ($response->successful()) {
                $count = count($response->json() ?? []);
                return [
                    'success' => true,
                    'message' => "Terhubung ke GenieACS! Ditemukan {$count} ONU terdaftar [{$elapsed}ms]",
                ];
            }

            return [
                'success' => false,
                'message' => "HTTP Error " . $response->status() . " saat memverifikasi API GenieACS.",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => "Koneksi ke GenieACS gagal: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Get all devices with 30s caching
     */
    public function getAllDevices(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget('genieacs_all_devices');
        }

        return Cache::remember('genieacs_all_devices', 30, function () {
            return $this->fetchDevicesFromApi();
        });
    }

    /**
     * Fetch all raw devices from GenieACS and format rows
     */
    public function fetchDevicesFromApi(): array
    {
        $token = $this->getToken();
        if (!$token) {
            return [];
        }

        // Lightweight projection for listing
        $projection = implode(',', [
            '_id',
            '_lastInform',
            '_registered',
            'DeviceID.Manufacturer',
            'DeviceID.ProductClass',
            'DeviceID.SerialNumber',
            'InternetGatewayDevice.DeviceInfo.SoftwareVersion',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1.Username',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1.ExternalIPAddress',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.KeyPassphrase',
            'VirtualParameters.pppoeUsername',
            'VirtualParameters.pppoeIP',
            'VirtualParameters.RXPower',
            'VirtualParameters.gettemp',
            'VirtualParameters.getponmode',
        ]);

        try {
            $response = Http::withToken($token)
                ->timeout($this->timeout)
                ->get("{$this->url}/api/devices/?projection=" . urlencode($projection));

            if ($response->status() === 403) {
                // Token might be expired, refresh and retry once
                $token = $this->getToken(true);
                $response = Http::withToken($token)
                    ->timeout($this->timeout)
                    ->get("{$this->url}/api/devices/?projection=" . urlencode($projection));
            }

            if (!$response->successful()) {
                return [];
            }

            $rawDevices = $response->json();
            if (!is_array($rawDevices)) {
                return [];
            }

            $devices = [];
            foreach ($rawDevices as $dev) {
                $devices[] = $this->formatDeviceRow($dev);
            }

            // Sort by is_online (descending) and last_inform (descending)
            usort($devices, function ($a, $b) {
                if ($a['is_online'] !== $b['is_online']) {
                    return $b['is_online'] <=> $a['is_online'];
                }
                return ($b['last_inform_timestamp'] ?? 0) <=> ($a['last_inform_timestamp'] ?? 0);
            });

            return $devices;
        } catch (\Throwable $e) {
            Log::error("GenieAcsService::fetchDevicesFromApi error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get list of devices with projection and optional search filter
     */
    public function getDevices(?string $search = null, bool $forceRefresh = false): array
    {
        $devices = $this->getAllDevices($forceRefresh);

        if ($search) {
            $s = strtolower($search);
            return array_values(array_filter($devices, function ($parsed) use ($s) {
                return str_contains(strtolower($parsed['serial_number'] ?? ''), $s)
                    || str_contains(strtolower($parsed['pppoe_username'] ?? ''), $s)
                    || str_contains(strtolower($parsed['ssid'] ?? ''), $s)
                    || str_contains(strtolower($parsed['ip_address'] ?? ''), $s)
                    || str_contains(strtolower($parsed['model'] ?? ''), $s)
                    || str_contains(strtolower($parsed['manufacturer'] ?? ''), $s);
            }));
        }

        return $devices;
    }


    /**
     * Get single device details
     */
    public function getDevice(string $deviceId): ?array
    {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        try {
            $encodedId = rawurlencode($deviceId);
            $response = Http::withToken($token)
                ->timeout($this->timeout)
                ->get("{$this->url}/api/devices/{$encodedId}");

            if ($response->status() === 403) {
                $token = $this->getToken(true);
                $response = Http::withToken($token)
                    ->timeout($this->timeout)
                    ->get("{$this->url}/api/devices/{$encodedId}");
            }

            if (!$response->successful()) {
                return null;
            }

            $raw = $response->json();
            if (!is_array($raw)) {
                return null;
            }

            return $this->formatDeviceDetail($deviceId, $raw);
        } catch (\Throwable $e) {
            Log::error("GenieAcsService::getDevice error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update Wi-Fi SSID and Password
     */
    public function updateWifi(string $deviceId, string $ssid, ?string $password = null, int $wlanIndex = 1): array
    {
        $token = $this->getToken();
        if (!$token) {
            return ['success' => false, 'message' => 'Gagal autentikasi ke GenieACS.'];
        }

        $paramValues = [
            ["InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.SSID", $ssid, "xsd:string"],
        ];

        if ($password !== null && $password !== '') {
            $paramValues[] = ["InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.KeyPassphrase", $password, "xsd:string"];
            $paramValues[] = ["InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.PreSharedKey.1.KeyPassphrase", $password, "xsd:string"];
        }

        $task = [
            'name' => 'setParameterValues',
            'parameterValues' => $paramValues,
        ];

        return $this->sendTasks($deviceId, [$task]);
    }

    /**
     * Reboot ONT
     */
    public function rebootDevice(string $deviceId): array
    {
        return $this->sendTasks($deviceId, [
            ['name' => 'reboot']
        ]);
    }

    /**
     * Refresh ONT parameters
     */
    public function refreshDevice(string $deviceId, string $objectName = ''): array
    {
        return $this->sendTasks($deviceId, [
            [
                'name' => 'refreshObject',
                'objectName' => $objectName
            ]
        ]);
    }

    /**
     * Send tasks to device via GenieACS tasks API
     */
    protected function sendTasks(string $deviceId, array $tasks): array
    {
        $token = $this->getToken();
        if (!$token) {
            return ['success' => false, 'message' => 'Koneksi ke GenieACS terputus.'];
        }

        try {
            $encodedId = rawurlencode($deviceId);
            $response = Http::withToken($token)
                ->withHeaders(['Connection-Request' => 'true'])
                ->timeout(15)
                ->post("{$this->url}/api/devices/{$encodedId}/tasks", $tasks);

            if ($response->status() === 403) {
                $token = $this->getToken(true);
                $response = Http::withToken($token)
                    ->withHeaders(['Connection-Request' => 'true'])
                    ->timeout(15)
                    ->post("{$this->url}/api/devices/{$encodedId}/tasks", $tasks);
            }

            if ($response->successful() || $response->status() === 200 || $response->status() === 202) {
                Cache::forget('genieacs_all_devices');
                return [
                    'success' => true,
                    'message' => 'Perintah berhasil dikirim ke ONT via TR-069!',
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal mengirim tugas ke ONT (HTTP ' . $response->status() . '): ' . $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format lightweight device row for list table
     */
    protected function formatDeviceRow(array $dev): array
    {
        $id = $this->extractVal($dev, 'DeviceID.ID', '');
        if (empty($id)) {
            $id = $this->extractVal($dev, '_id', '');
        }

        $serial = $this->extractVal($dev, 'DeviceID.SerialNumber', '-');
        $model = $this->extractVal($dev, 'DeviceID.ProductClass', '-');
        $manufacturer = $this->extractVal($dev, 'DeviceID.Manufacturer', '-');

        // PPPoE Username
        $pppoeUsername = $this->extractVal($dev, 'VirtualParameters.pppoeUsername', '');
        if (empty($pppoeUsername)) {
            $pppoeUsername = $this->extractVal($dev, 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username', '');
        }
        if (empty($pppoeUsername)) {
            $pppoeUsername = $this->extractVal($dev, 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1.Username', '-');
        }

        // IP
        $ip = $this->extractVal($dev, 'VirtualParameters.pppoeIP', '');
        if (empty($ip)) {
            $ip = $this->extractVal($dev, 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1.ExternalIPAddress', '-');
        }

        // SSID
        $ssid = $this->extractVal($dev, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID', '-');

        // Optical RX Power & Temperature
        $rxPower = $this->extractVal($dev, 'VirtualParameters.RXPower', null);
        $temp = $this->extractVal($dev, 'VirtualParameters.gettemp', null);
        $ponMode = $this->extractVal($dev, 'VirtualParameters.getponmode', 'GPON/EPON');

        // Inform timestamp
        $lastInformTs = $this->extractVal($dev, 'Events.Inform', null);
        if (!$lastInformTs) {
            $lastInformTs = $this->extractVal($dev, '_lastInform', null);
        }

        $isOnline = false;
        $lastInformStr = '-';
        $lastInformHuman = '-';
        if ($lastInformTs) {
            // ts could be in milliseconds
            $ts = $lastInformTs > 10000000000 ? intval($lastInformTs / 1000) : intval($lastInformTs);
            $lastInformStr = date('d M Y H:i:s', $ts);
            $lastInformHuman = \Carbon\Carbon::createFromTimestamp($ts)->diffForHumans();
            // Considered online if inform occurred within last 10 minutes
            $isOnline = (time() - $ts) < 600;
        }

        return [
            'id' => $id,
            'serial_number' => $serial,
            'model' => $model,
            'manufacturer' => $manufacturer,
            'pppoe_username' => $pppoeUsername,
            'ip_address' => $ip,
            'ssid' => $ssid,
            'rx_power' => $rxPower ? ($rxPower . ' dBm') : null,
            'temperature' => $temp ? ($temp . ' °C') : null,
            'pon_mode' => $ponMode,
            'last_inform' => $lastInformStr,
            'last_inform_human' => $lastInformHuman,
            'last_inform_timestamp' => $lastInformTs,
            'is_online' => $isOnline,
        ];
    }

    /**
     * Format full device details for show view
     */
    protected function formatDeviceDetail(string $deviceId, array $raw): array
    {
        $serial = $this->extractVal($raw, 'DeviceID.SerialNumber', '-');
        $model = $this->extractVal($raw, 'DeviceID.ProductClass', '-');
        $manufacturer = $this->extractVal($raw, 'DeviceID.Manufacturer', '-');
        $oui = $this->extractVal($raw, 'DeviceID.OUI', '-');
        $softwareVersion = $this->extractVal($raw, 'InternetGatewayDevice.DeviceInfo.SoftwareVersion', '-');
        $hardwareVersion = $this->extractVal($raw, 'InternetGatewayDevice.DeviceInfo.HardwareVersion', '-');

        // WAN / PPPoE
        $pppoeUsername = $this->extractVal($raw, 'VirtualParameters.pppoeUsername', '');
        if (empty($pppoeUsername)) {
            $pppoeUsername = $this->extractVal($raw, 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1.Username', '');
        }
        if (empty($pppoeUsername)) {
            $pppoeUsername = $this->extractVal($raw, 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username', '-');
        }

        $ip = $this->extractVal($raw, 'VirtualParameters.pppoeIP', '');
        if (empty($ip)) {
            $ip = $this->extractVal($raw, 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1.ExternalIPAddress', '-');
        }

        $vlan = $this->extractVal($raw, 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.X_CT-COM_WANEponLinkConfig.VLANIDMark', null);
        $uptime = $this->extractVal($raw, 'VirtualParameters.getpppuptime', null);

        // Optic & Health
        $rxPower = $this->extractVal($raw, 'VirtualParameters.RXPower', null);
        if (!$rxPower) {
            $rawRx = $this->extractVal($raw, 'InternetGatewayDevice.WANDevice.1.X_CT-COM_EponInterfaceConfig.RXPower', null);
            if ($rawRx) {
                $rxPower = $rawRx;
            }
        }

        $temp = $this->extractVal($raw, 'VirtualParameters.gettemp', null);
        if (!$temp) {
            $rawTemp = $this->extractVal($raw, 'InternetGatewayDevice.WANDevice.1.X_CT-COM_EponInterfaceConfig.TransceiverTemperature', null);
            if ($rawTemp) {
                $temp = round($rawTemp / 256, 1);
            }
        }

        $ponMode = $this->extractVal($raw, 'VirtualParameters.getponmode', 'EPON / GPON');

        // Wi-Fi 2.4G (WLAN 1)
        $wlan1 = [
            'ssid' => $this->extractVal($raw, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID', ''),
            'password' => $this->extractVal($raw, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.KeyPassphrase', ''),
            'channel' => $this->extractVal($raw, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Channel', 'Auto'),
            'status' => $this->extractVal($raw, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Status', 'Up'),
            'enable' => $this->extractVal($raw, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Enable', true),
            'standard' => $this->extractVal($raw, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Standard', '802.11b/g/n'),
        ];

        // Wi-Fi 5G (WLAN 5 or WLAN 2 if dual-band)
        $wlan5 = null;
        $ssid5 = $this->extractVal($raw, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID', null);
        if ($ssid5) {
            $wlan5 = [
                'ssid' => $ssid5,
                'password' => $this->extractVal($raw, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.KeyPassphrase', ''),
                'channel' => $this->extractVal($raw, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.Channel', 'Auto'),
                'status' => $this->extractVal($raw, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.Status', 'Up'),
                'enable' => $this->extractVal($raw, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.Enable', true),
            ];
        }

        // Connected Devices / Hosts
        $hosts = [];
        foreach ($raw as $key => $node) {
            if (preg_match('/InternetGatewayDevice\.LANDevice\.1\.Hosts\.Host\.(\d+)\.MACAddress/', $key, $m)) {
                $idx = $m[1];
                $mac = $this->extractVal($raw, "InternetGatewayDevice.LANDevice.1.Hosts.Host.{$idx}.MACAddress", '-');
                $ipHost = $this->extractVal($raw, "InternetGatewayDevice.LANDevice.1.Hosts.Host.{$idx}.IPAddress", '-');
                $name = $this->extractVal($raw, "InternetGatewayDevice.LANDevice.1.Hosts.Host.{$idx}.HostName", 'Perangkat Tanpa Nama');
                $interface = $this->extractVal($raw, "InternetGatewayDevice.LANDevice.1.Hosts.Host.{$idx}.InterfaceType", 'Wi-Fi');
                $active = $this->extractVal($raw, "InternetGatewayDevice.LANDevice.1.Hosts.Host.{$idx}.Active", true);

                $hosts[] = [
                    'index' => $idx,
                    'hostname' => $name,
                    'ip' => $ipHost,
                    'mac' => $mac,
                    'interface' => $interface,
                    'active' => $active,
                ];
            }
        }

        // Sort active hosts first
        usort($hosts, function ($a, $b) {
            return $b['active'] <=> $a['active'];
        });

        // Inform timestamps
        $lastInformTs = $this->extractVal($raw, 'Events.Inform', null);
        if (!$lastInformTs) {
            $lastInformTs = $this->extractVal($raw, '_lastInform', null);
        }

        $isOnline = false;
        $lastInformStr = '-';
        if ($lastInformTs) {
            $ts = $lastInformTs > 10000000000 ? intval($lastInformTs / 1000) : intval($lastInformTs);
            $lastInformStr = date('d M Y H:i:s', $ts);
            $isOnline = (time() - $ts) < 600;
        }

        return [
            'id' => $deviceId,
            'serial_number' => $serial,
            'model' => $model,
            'manufacturer' => $manufacturer,
            'oui' => $oui,
            'software_version' => $softwareVersion,
            'hardware_version' => $hardwareVersion,
            'pppoe_username' => $pppoeUsername,
            'ip_address' => $ip,
            'vlan' => $vlan,
            'uptime' => $uptime,
            'rx_power' => $rxPower,
            'temperature' => $temp,
            'pon_mode' => $ponMode,
            'last_inform' => $lastInformStr,
            'is_online' => $isOnline,
            'wlan1' => $wlan1,
            'wlan5' => $wlan5,
            'hosts' => $hosts,
        ];
    }

    /**
     * Helper to safely extract value from GenieACS node structure
     */
    protected function extractVal(array $data, string $key, $default = null)
    {
        if (!isset($data[$key])) {
            return $default;
        }

        $node = $data[$key];
        if (is_array($node)) {
            if (isset($node['value'])) {
                if (is_array($node['value'])) {
                    return $node['value'][0] ?? $default;
                }
                return $node['value'];
            }
            if (isset($node[0])) {
                return $node[0];
            }
            // If it is metadata without 'value', fallback to default
            return $default;
        }

        return $node ?? $default;
    }
}
