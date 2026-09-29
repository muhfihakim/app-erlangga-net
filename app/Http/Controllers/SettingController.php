<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $nasSecret = null;
        try {
            $nasRecord = \Illuminate\Support\Facades\DB::connection('radius')->table('nas')->first();
            $nasSecret = $nasRecord?->secret;
        } catch (\Throwable $e) {}

        $settings = [
            'mikrotik' => [
                'host' => Setting::get('mikrotik_host', env('MIKROTIK_HOST', '192.168.10.1')),
                'port' => Setting::get('mikrotik_port', env('MIKROTIK_PORT', 8728)),
                'user' => Setting::get('mikrotik_user', env('MIKROTIK_USER', 'admin')),
                'pass' => Setting::get('mikrotik_pass', env('MIKROTIK_PASS', '')),
                'interface' => Setting::get('mikrotik_interface', env('MIKROTIK_INTERFACE', 'ether1')),
                'timeout' => Setting::get('mikrotik_timeout', env('MIKROTIK_TIMEOUT', 10)),
                // IP Pool & RADIUS Client for PNP MikroTik Script
                'radius_server_ip' => Setting::get('radius_server_ip', Setting::get('db_radius_host', '192.168.21.202')),
                'radius_secret' => Setting::get('radius_secret', $nasSecret ?? 'testing123'),
                'pool_normal_name' => Setting::get('pool_normal_name', 'pool-normal'),
                'pool_normal_range' => Setting::get('pool_normal_range', '10.10.0.2-10.10.15.254'),
                'pool_normal_gateway' => Setting::get('pool_normal_gateway', '10.10.0.1'),
                'pool_isolir_name' => Setting::get('pool_isolir_name', 'pool-isolir'),
                'pool_isolir_range' => Setting::get('pool_isolir_range', '172.30.0.2-172.30.255.254'),
                'pool_isolir_gateway' => Setting::get('pool_isolir_gateway', '172.30.0.1'),
                'dns_servers' => Setting::get('mikrotik_dns_servers', '8.8.8.8,1.1.1.1'),
                'web_portal_ip' => Setting::get('web_portal_ip', request()->getHost()),
            ],
            'olt' => [
                'host' => Setting::get('olt_host', env('OLT_HOST', '10.10.10.2')),
                'port' => Setting::get('olt_port', env('OLT_PORT', 23)),
                'username' => Setting::get('olt_username', env('OLT_USERNAME', 'admin')),
                'password' => Setting::get('olt_password', env('OLT_PASSWORD', '')),
            ],
            'radius' => [
                'host' => Setting::get('db_radius_host', env('DB_RADIUS_HOST', '127.0.0.1')),
                'port' => Setting::get('db_radius_port', env('DB_RADIUS_PORT', '3306')),
                'database' => Setting::get('db_radius_database', env('DB_RADIUS_DATABASE', 'radius')),
                'username' => Setting::get('db_radius_username', env('DB_RADIUS_USERNAME', 'root')),
                'password' => Setting::get('db_radius_password', env('DB_RADIUS_PASSWORD', '')),
            ],
            'xendit' => [
                'secret_key' => Setting::get('xendit_secret_key', env('XENDIT_SECRET_KEY', '')),
                'webhook_token' => Setting::get('xendit_webhook_token', env('XENDIT_WEBHOOK_TOKEN', '')),
                'webhook_url' => url('/api/webhooks/xendit'),
            ],
            'whatsapp' => [
                'api_url' => Setting::get('whatsapp_api_url', env('WHATSAPP_API_URL', '')),
                'api_key' => Setting::get('whatsapp_api_key', env('WHATSAPP_API_KEY', '')),
                'sender' => Setting::get('whatsapp_sender', env('WHATSAPP_SENDER', '')),
            ],
            'genieacs' => [
                'url' => Setting::get('genieacs_url', env('GENIEACS_URL', 'http://203.2.151.130:3000')),
                'username' => Setting::get('genieacs_username', env('GENIEACS_USERNAME', 'admin')),
                'password' => Setting::get('genieacs_password', env('GENIEACS_PASSWORD', 'admin')),
                'timeout' => Setting::get('genieacs_timeout', env('GENIEACS_TIMEOUT', 10)),
            ],
        ];

        $pakets = \App\Models\Paket::all();
        return view('settings.index', compact('settings', 'pakets'));
    }

    public function update(Request $request)
    {
        $tab = $request->input('tab', 'mikrotik');

        if ($tab === 'mikrotik') {
            $request->validate([
                'mikrotik_host' => 'required|string',
                'mikrotik_port' => 'required|numeric',
                'mikrotik_user' => 'required|string',
                'mikrotik_pass' => 'nullable|string',
                'mikrotik_interface' => 'nullable|string',
                'mikrotik_timeout' => 'nullable|numeric',
                'radius_server_ip' => 'nullable|string',
                'radius_secret' => 'nullable|string',
                'pool_normal_name' => 'nullable|string',
                'pool_normal_range' => 'nullable|string',
                'pool_normal_gateway' => 'nullable|string',
                'pool_isolir_name' => 'nullable|string',
                'pool_isolir_range' => 'nullable|string',
                'pool_isolir_gateway' => 'nullable|string',
                'mikrotik_dns_servers' => 'nullable|string',
                'web_portal_ip' => 'nullable|string',
            ]);

            Setting::set('mikrotik_host', $request->mikrotik_host, 'mikrotik');
            Setting::set('mikrotik_port', $request->mikrotik_port, 'mikrotik');
            Setting::set('mikrotik_user', $request->mikrotik_user, 'mikrotik');
            Setting::set('mikrotik_pass', $request->mikrotik_pass ?? '', 'mikrotik');
            Setting::set('mikrotik_interface', $request->mikrotik_interface ?? 'ether1', 'mikrotik');
            Setting::set('mikrotik_timeout', $request->mikrotik_timeout ?? 10, 'mikrotik');

            // Save IP Pool & RADIUS settings
            Setting::set('radius_server_ip', $request->radius_server_ip ?? '192.168.21.202', 'mikrotik');
            Setting::set('radius_secret', $request->radius_secret ?? 'testing123', 'mikrotik');
            Setting::set('pool_normal_name', $request->pool_normal_name ?? 'pool-normal', 'mikrotik');
            Setting::set('pool_normal_range', $request->pool_normal_range ?? '10.10.0.2-10.10.15.254', 'mikrotik');
            Setting::set('pool_normal_gateway', $request->pool_normal_gateway ?? '10.10.0.1', 'mikrotik');
            Setting::set('pool_isolir_name', $request->pool_isolir_name ?? 'pool-isolir', 'mikrotik');
            Setting::set('pool_isolir_range', $request->pool_isolir_range ?? '172.30.0.2-172.30.255.254', 'mikrotik');
            Setting::set('pool_isolir_gateway', $request->pool_isolir_gateway ?? '172.30.0.1', 'mikrotik');
            Setting::set('mikrotik_dns_servers', $request->mikrotik_dns_servers ?? '8.8.8.8,1.1.1.1', 'mikrotik');
            Setting::set('web_portal_ip', $request->web_portal_ip ?? request()->getHost(), 'mikrotik');

            // Apply immediately to runtime config
            config([
                'mikrotik.host' => $request->mikrotik_host,
                'mikrotik.port' => (int) $request->mikrotik_port,
                'mikrotik.username' => $request->mikrotik_user,
                'mikrotik.password' => $request->mikrotik_pass ?? '',
                'mikrotik.interface' => $request->mikrotik_interface ?? 'ether1',
                'mikrotik.timeout' => (int) ($request->mikrotik_timeout ?? 10),
            ]);
        } elseif ($tab === 'olt') {
            $request->validate([
                'olt_host' => 'required|string',
                'olt_port' => 'required|numeric',
                'olt_username' => 'required|string',
                'olt_password' => 'nullable|string',
            ]);

            Setting::set('olt_host', $request->olt_host, 'olt');
            Setting::set('olt_port', $request->olt_port, 'olt');
            Setting::set('olt_username', $request->olt_username, 'olt');
            Setting::set('olt_password', $request->olt_password ?? '', 'olt');

            config([
                'olt.host' => $request->olt_host,
                'olt.port' => (int) $request->olt_port,
                'olt.username' => $request->olt_username,
                'olt.password' => $request->olt_password ?? '',
            ]);
        } elseif ($tab === 'radius') {
            $request->validate([
                'db_radius_host' => 'required|string',
                'db_radius_port' => 'required|numeric',
                'db_radius_database' => 'required|string',
                'db_radius_username' => 'required|string',
                'db_radius_password' => 'nullable|string',
            ]);

            Setting::set('db_radius_host', $request->db_radius_host, 'radius');
            Setting::set('db_radius_port', $request->db_radius_port, 'radius');
            Setting::set('db_radius_database', $request->db_radius_database, 'radius');
            Setting::set('db_radius_username', $request->db_radius_username, 'radius');
            Setting::set('db_radius_password', $request->db_radius_password ?? '', 'radius');

            config([
                'database.connections.radius.host' => $request->db_radius_host,
                'database.connections.radius.port' => $request->db_radius_port,
                'database.connections.radius.database' => $request->db_radius_database,
                'database.connections.radius.username' => $request->db_radius_username,
                'database.connections.radius.password' => $request->db_radius_password ?? '',
            ]);
        } elseif ($tab === 'xendit') {
            $request->validate([
                'xendit_secret_key' => 'nullable|string',
                'xendit_webhook_token' => 'nullable|string',
            ]);

            Setting::set('xendit_secret_key', $request->xendit_secret_key ?? '', 'xendit');
            Setting::set('xendit_webhook_token', $request->xendit_webhook_token ?? '', 'xendit');

            config([
                'services.xendit.secret_key' => $request->xendit_secret_key ?? '',
                'services.xendit.webhook_token' => $request->xendit_webhook_token ?? '',
            ]);
        } elseif ($tab === 'whatsapp') {
            $request->validate([
                'whatsapp_api_url' => 'required|string',
                'whatsapp_api_key' => 'nullable|string',
                'whatsapp_sender' => 'nullable|string',
            ]);

            Setting::set('whatsapp_api_url', $request->whatsapp_api_url, 'whatsapp');
            Setting::set('whatsapp_api_key', $request->whatsapp_api_key ?? '', 'whatsapp');
            Setting::set('whatsapp_sender', $request->whatsapp_sender ?? '', 'whatsapp');
        } elseif ($tab === 'genieacs') {
            $request->validate([
                'genieacs_url' => 'required|string',
                'genieacs_username' => 'required|string',
                'genieacs_password' => 'nullable|string',
                'genieacs_timeout' => 'nullable|numeric',
            ]);

            Setting::set('genieacs_url', $request->genieacs_url, 'genieacs');
            Setting::set('genieacs_username', $request->genieacs_username, 'genieacs');
            Setting::set('genieacs_password', $request->genieacs_password ?? '', 'genieacs');
            Setting::set('genieacs_timeout', $request->genieacs_timeout ?? 10, 'genieacs');
        }

        return redirect()->route('settings.index', ['tab' => $tab])->with('success', 'Pengaturan ' . strtoupper($tab) . ' berhasil disimpan ke Database!');
    }

    public function testConnection(Request $request)
    {
        $type = $request->input('type');
        $startTime = microtime(true);

        try {
            if ($type === 'mikrotik') {
                $host = $request->input('host', Setting::get('mikrotik_host', env('MIKROTIK_HOST', '192.168.10.1')));
                $port = (int) $request->input('port', Setting::get('mikrotik_port', env('MIKROTIK_PORT', 8728)));
                $user = $request->input('user', Setting::get('mikrotik_user', env('MIKROTIK_USER', 'admin')));
                $pass = $request->input('pass', Setting::get('mikrotik_pass', env('MIKROTIK_PASS', '')));

                $client = new \ZillEAli\MikrotikLaravel\Connections\RouterosClient($host, $port, $user, $pass);
                $client->connect();
                
                $identityRaw = $client->send(['/system/identity/print']);
                $resourceRaw = $client->send(['/system/resource/print']);

                $identity = $identityRaw[0]['name'] ?? 'MikroTik';
                $version = $resourceRaw[0]['version'] ?? 'RouterOS';
                $board = $resourceRaw[0]['board-name'] ?? '';

                $elapsed = round((microtime(true) - $startTime) * 1000, 2);

                return response()->json([
                    'success' => true,
                    'message' => "Terhubung ke MikroTik ($identity) - $board v$version [{$elapsed}ms]",
                ]);
            }

            if ($type === 'olt') {
                $host = $request->input('host', Setting::get('olt_host', env('OLT_HOST', '10.10.10.2')));
                $port = (int) $request->input('port', Setting::get('olt_port', env('OLT_PORT', 23)));
                $username = $request->input('username', Setting::get('olt_username', env('OLT_USERNAME', 'admin')));
                $password = $request->input('password', Setting::get('olt_password', env('OLT_PASSWORD', '')));

                $fp = @fsockopen($host, $port, $errno, $errstr, 10);
                if (!$fp) {
                    throw new \Exception("Gagal membuka port telnet {$host}:{$port} - $errstr ($errno)");
                }

                stream_set_timeout($fp, 5);
                $banner = fread($fp, 2048);
                fclose($fp);

                $elapsed = round((microtime(true) - $startTime) * 1000, 2);

                return response()->json([
                    'success' => true,
                    'message' => "Port Telnet OLT {$host}:{$port} Aktif & Merespons [{$elapsed}ms]",
                ]);
            }

            if ($type === 'radius') {
                $host = $request->input('host', Setting::get('db_radius_host', env('DB_RADIUS_HOST', '127.0.0.1')));
                $port = $request->input('port', Setting::get('db_radius_port', env('DB_RADIUS_PORT', 3306)));
                $database = $request->input('database', Setting::get('db_radius_database', env('DB_RADIUS_DATABASE', 'radius')));
                $username = $request->input('username', Setting::get('db_radius_username', env('DB_RADIUS_USERNAME', 'root')));
                $password = $request->input('password', Setting::get('db_radius_password', env('DB_RADIUS_PASSWORD', '')));

                // Test dynamic PDO connection
                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
                $pdo = new \PDO($dsn, $username, $password, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_TIMEOUT => 5,
                ]);

                $stmt = $pdo->query("SELECT COUNT(*) FROM radcheck");
                $count = $stmt->fetchColumn();

                $elapsed = round((microtime(true) - $startTime) * 1000, 2);

                return response()->json([
                    'success' => true,
                    'message' => "Koneksi Database FreeRADIUS Sukses! Ditemukan {$count} akun di tabel radcheck [{$elapsed}ms]",
                ]);
            }

            if ($type === 'xendit') {
                $secretKey = $request->input('secret_key', Setting::get('xendit_secret_key', env('XENDIT_SECRET_KEY')));
                if (!$secretKey) {
                    throw new \Exception("Secret Key Xendit belum diisi.");
                }

                \Xendit\Configuration::setXenditKey($secretKey);
                $apiInstance = new \Xendit\BalanceAndTransaction\BalanceApi();
                $balance = $apiInstance->getBalance('CASH');

                $amount = number_format($balance->getBalance() ?? 0, 0, ',', '.');
                $elapsed = round((microtime(true) - $startTime) * 1000, 2);

                return response()->json([
                    'success' => true,
                    'message' => "API Key Xendit Valid! Saldo Kas: Rp {$amount} [{$elapsed}ms]",
                ]);
            }

            if ($type === 'whatsapp') {
                $target = $request->input('target') 
                    ?: auth()->user()->no_whatsapp 
                    ?: Setting::get('company_phone') 
                    ?: '081234567890';

                $msg = "🔔 *Tes Notifikasi WhatsApp Gateway*\n\nIntegrasi API WhatsApp Erlangga Net berhasil terhubung dengan sukses pada " . date('d/m/Y H:i:s') . " WIB.";
                $result = \App\Services\WhatsAppService::sendMessage($target, $msg);

                $elapsed = round((microtime(true) - $startTime) * 1000, 2);

                if ($result['success']) {
                    $simMsg = ($result['simulated'] ?? false) ? ' (Mode Simulasi - API Key kosong)' : '';
                    return response()->json([
                        'success' => true,
                        'message' => "Pengujian WhatsApp API Berhasil{$simMsg}! Pesan diproses untuk {$target} [{$elapsed}ms]",
                    ]);
                } else {
                    throw new \Exception($result['message'] ?? 'Gagal mengirim pesan WhatsApp.');
                }
            }

            if ($type === 'genieacs') {
                $acs = app(\App\Services\GenieAcsService::class);
                if ($request->filled('url')) {
                    $acs->setConfig(
                        $request->input('url'),
                        $request->input('username', 'admin'),
                        $request->input('password', 'admin')
                    );
                }
                $res = $acs->testConnection();
                if ($res['success']) {
                    return response()->json($res);
                } else {
                    throw new \Exception($res['message']);
                }
            }

            throw new \Exception("Tipe pengujian tidak dikenal.");
        } catch (\Throwable $e) {
            $elapsed = round((microtime(true) - $startTime) * 1000, 2);
            return response()->json([
                'success' => false,
                'message' => "Gagal terhubung ({$elapsed}ms): " . $e->getMessage(),
            ], 422);
        }
    }
}
