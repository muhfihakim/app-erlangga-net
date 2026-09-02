<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    protected static array $cache = [];

    /**
     * Get a setting value with fallback
     */
    public static function get(string $key, $default = null)
    {
        if (isset(static::$cache[$key])) {
            return static::$cache[$key];
        }

        try {
            $setting = static::where('key', $key)->first();
            if ($setting !== null && $setting->value !== null && $setting->value !== '') {
                static::$cache[$key] = $setting->value;
                return $setting->value;
            }
        } catch (\Throwable $e) {
            // DB not ready or table missing
        }

        return $default;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, ?string $group = null)
    {
        static::$cache[$key] = $value;

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
            ]
        );
    }

    /**
     * Get all settings in a group as key => value array
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }
}
