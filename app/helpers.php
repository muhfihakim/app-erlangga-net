<?php

use App\Models\ActivityLog;

if (!function_exists('logActivity')) {
    function logActivity($type, $description = null, $data = null)
    {
        ActivityLog::create([
            'user_id' => auth()->id() ?? null,
            'activity_type' => $type,
            'description' => $description,
            'data' => $data ? json_encode($data) : null,
            'logged_at' => now(),
        ]);
    }
}

if (!function_exists('btoa_safe')) {
    function btoa_safe($str) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($str));
    }
}

if (!function_exists('format_uptime_hms')) {
    function format_uptime_hms($uptime) {
        if (!$uptime) return '00:00:00';
        $w=0; $d=0; $h=0; $m=0; $s=0;
        preg_match('/(\d+)w/', $uptime, $mw);
        preg_match('/(\d+)d/', $uptime, $md);
        preg_match('/(\d+)h/', $uptime, $mh);
        preg_match('/(\d+)m/', $uptime, $mm);
        preg_match('/(\d+)s/', $uptime, $ms);
        if($mw)$w=(int)$mw[1]; if($md)$d=(int)$md[1]; if($mh)$h=(int)$mh[1]; if($mm)$m=(int)$mm[1]; if($ms)$s=(int)$ms[1];
        $totalH = ($w*168)+($d*24)+$h;
        return sprintf('%02d:%02d:%02d', $totalH, $m, $s);
    }
}
