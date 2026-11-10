<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    protected static array $cache = [];

    /**
     * Kunci sensitif (password, secret token, API key) yang wajib dienkripsi otomatis di database (AES-256)
     */
    protected static array $sensitiveKeys = [
        'xendit_secret_key',
        'xendit_webhook_token',
        'db_radius_password',
        'mikrotik_pass',
        'olt_password',
        'genieacs_password',
        'whatsapp_api_key',
        'firebase_server_key',
        'radius_secret',
    ];

    /**
     * Get a setting value with fallback (mendekripsi otomatis jika kunci sensitif terenkripsi)
     */
    public static function get(string $key, $default = null)
    {
        if (isset(static::$cache[$key])) {
            return static::$cache[$key];
        }

        try {
            $setting = static::where('key', $key)->first();
            if ($setting !== null && $setting->value !== null && $setting->value !== '') {
                $val = $setting->value;
                if (in_array($key, static::$sensitiveKeys, true)) {
                    try {
                        $val = Crypt::decryptString($val);
                    } catch (\Throwable $e) {
                        // Jika data legacy belum terenkripsi (plain text lama), tetap gunakan nilai asli
                    }
                }
                static::$cache[$key] = $val;
                return $val;
            }
        } catch (\Throwable $e) {
            // DB not ready or table missing
        }

        return $default;
    }

    /**
     * Set a setting value (otomatis mengenkripsi kunci rahasia sebelum disimpan ke MySQL)
     */
    public static function set(string $key, $value, ?string $group = null)
    {
        $valToStore = $value;
        if (in_array($key, static::$sensitiveKeys, true) && !empty($value)) {
            try {
                $valToStore = Crypt::encryptString((string)$value);
            } catch (\Throwable $e) {
                // fallback
            }
        }

        // Simpan nilai terurai di cache proses untuk kecepatan eksekusi
        static::$cache[$key] = $value;

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $valToStore,
                'group' => $group,
            ]
        );
    }

    /**
     * Get all settings in a group as key => value array
     */
    public static function getGroup(string $group): array
    {
        $rows = static::where('group', $group)->get();
        $result = [];
        foreach ($rows as $row) {
            $val = $row->value;
            if (in_array($row->key, static::$sensitiveKeys, true) && !empty($val)) {
                try {
                    $val = Crypt::decryptString($val);
                } catch (\Throwable $e) {}
            }
            $result[$row->key] = $val;
        }
        return $result;
    }
}
