<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        if (auth()->user()->jabatan !== 'Administrator') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $logs = ActivityLog::with('user')->latest()->paginate(50);
        return response()->json($logs);
    }
}
