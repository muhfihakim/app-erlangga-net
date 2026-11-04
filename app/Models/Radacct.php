<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Radacct extends Model
{
    protected $connection = 'radius';
    protected $table = 'radacct';
    protected $primaryKey = 'radacctid';
    public $timestamps = false;

    protected $fillable = [
        'acctsessionid',
        'acctuniqueid',
        'username',
        'realm',
        'nasipaddress',
        'nasportid',
        'nasporttype',
        'acctstarttime',
        'acctupdatetime',
        'acctstoptime',
        'acctinterval',
        'acctsessiontime',
        'acctauthentic',
        'connectinfo_start',
        'connectinfo_stop',
        'acctinputoctets',
        'acctoutputoctets',
        'calledstationid',
        'callingstationid',
        'acctterminatecause',
        'servicetype',
        'framedprotocol',
        'framedipaddress',
    ];

    protected $casts = [
        'acctstarttime' => 'datetime',
        'acctupdatetime' => 'datetime',
        'acctstoptime' => 'datetime',
        'acctsessiontime' => 'integer',
        'acctinputoctets' => 'integer',
        'acctoutputoctets' => 'integer',
    ];

    /**
     * Cek apakah sesi saat ini sedang online/aktif.
     */
    public function getIsOnlineAttribute(): bool
    {
        return is_null($this->acctstoptime);
    }

    /**
     * Format durasi waktu sesi menjadi format ramah manusia.
     */
    public function getFormattedSessionTimeAttribute(): string
    {
        $seconds = (int) $this->acctsessiontime;
        if ($seconds <= 0) {
            return '0 detik';
        }

        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        $parts = [];
        if ($days > 0) $parts[] = $days . 'h';
        if ($hours > 0) $parts[] = $hours . 'j';
        if ($minutes > 0) $parts[] = $minutes . 'm';
        if (empty($parts) || ($days == 0 && count($parts) < 2)) {
            $parts[] = $secs . 'd';
        }

        return implode(' ', $parts);
    }

    /**
     * Format bytes menjadi KB / MB / GB.
     */
    public static function formatBytes(?int $bytes, int $precision = 2): string
    {
        $bytes = max(0, (float) $bytes);
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return number_format($bytes / (1024 ** $power), $precision, ',', '.') . ' ' . $units[$power];
    }
}
