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
