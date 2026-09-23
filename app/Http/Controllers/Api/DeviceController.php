<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\GenieAcsService;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    protected GenieAcsService $acs;

    public function __construct(GenieAcsService $acs)
    {
        $this->acs = $acs;
    }

    /**
     * 1. Dapatkan informasi Wi-Fi & Status Perangkat ONT
     */
    public function wifi(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();
        $device = $this->acs->findDeviceForCustomer($customer);

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat ONT belum terdaftar di ACS atau belum terhubung.',
                'data'    => [
                    'device' => [
                        'serial_number' => $customer->sn_perangkat,
                        'jenis_modem'   => $customer->jenis_modem,
                        'is_online'     => false,
                    ],
                    'wlan1'  => null,
                    'wlan5'  => null,
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'device' => [
                    'device_id'     => $device['id'],
                    'serial_number' => $device['serial_number'] ?? $customer->sn_perangkat,
                    'model'         => $device['model'] ?? '-',
                    'manufacturer'  => $device['manufacturer'] ?? '-',
                    'is_online'     => (bool) ($device['is_online'] ?? false),
                    'rx_power'      => $device['rx_power'] ? ($device['rx_power'] . ' dBm') : null,
                    'temperature'   => $device['temperature'] ? ($device['temperature'] . ' °C') : null,
                    'pon_mode'      => $device['pon_mode'] ?? 'GPON/EPON',
                    'last_inform'   => $device['last_inform'] ?? '-',
                ],
                'wlan_2_4g' => $device['wlan1'] ? [
                    'ssid'     => $device['wlan1']['ssid'] ?? '',
                    'password' => $device['wlan1']['password'] ?? '',
                    'channel'  => $device['wlan1']['channel'] ?? 'Auto',
                    'status'   => $device['wlan1']['status'] ?? 'Up',
                    'enable'   => (bool) ($device['wlan1']['enable'] ?? true),
                ] : null,
                'wlan_5g' => $device['wlan5'] ? [
                    'ssid'     => $device['wlan5']['ssid'] ?? '',
                    'password' => $device['wlan5']['password'] ?? '',
                    'channel'  => $device['wlan5']['channel'] ?? 'Auto',
                    'status'   => $device['wlan5']['status'] ?? 'Up',
                    'enable'   => (bool) ($device['wlan5']['enable'] ?? true),
                ] : null,
            ]
        ]);
    }

    /**
     * 2. Dapatkan daftar perangkat yang sedang terhubung ke Wi-Fi / LAN (Connected Hosts)
     */
    public function hosts(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();
        $device = $this->acs->findDeviceForCustomer($customer);

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat ONT belum terhubung ke sistem ACS.',
                'data'    => [
                    'total_connected' => 0,
                    'hosts'           => []
                ]
            ], 404);
        }

        $hosts = $device['hosts'] ?? [];
        $activeHosts = array_values(array_filter($hosts, fn($h) => !empty($h['active'])));

        return response()->json([
            'success' => true,
            'data'    => [
                'total_connected' => count($activeHosts),
                'total_recorded'  => count($hosts),
                'hosts'           => $hosts
            ]
        ]);
    }

    /**
     * 3. Ganti Nama Wi-Fi (SSID) dan Sandi (Password) via TR-069
     */
    public function updateWifi(Request $request)
    {
        $request->validate([
            'ssid'       => 'required|string|min:1|max:32',
            'password'   => 'nullable|string|min:8|max:63',
            'wlan_index' => 'nullable|integer|in:1,5',
        ]);

        /** @var Customer $customer */
        $customer = $request->user();
        $device = $this->acs->findDeviceForCustomer($customer);

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat ONT tidak ditemukan di ACS. Pastikan perangkat online.'
            ], 404);
        }

        $wlanIndex = (int) $request->input('wlan_index', 1);
        $result = $this->acs->updateWifi($device['id'], $request->ssid, $request->password, $wlanIndex);

        if (!empty($result['success'])) {
            return response()->json([
                'success' => true,
                'message' => 'Perubahan nama dan sandi Wi-Fi berhasil dikirim ke perangkat ONT!',
                'data'    => [
                    'ssid'       => $request->ssid,
                    'wlan_index' => $wlanIndex
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Gagal mengirim konfigurasi Wi-Fi ke ONT.'
        ], 400);
    }

    /**
     * 4. Reboot Perangkat ONT via TR-069
     */
    public function reboot(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();
        $device = $this->acs->findDeviceForCustomer($customer);

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat ONT tidak ditemukan di ACS atau sedang offline.'
            ], 404);
        }

        $result = $this->acs->rebootDevice($device['id']);

        if (!empty($result['success'])) {
            return response()->json([
                'success' => true,
                'message' => 'Perintah reboot berhasil dikirim. Perangkat ONT akan restart dalam beberapa detik.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Gagal mengirim perintah reboot ke ONT.'
        ], 400);
    }
}
