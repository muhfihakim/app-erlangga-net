<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Events\PttAudioSent;

class PttController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'audio' => 'required|file',
        ]);

        $file = $request->file('audio');
        $path = $file->store('ptt', 's3');
        $url = Storage::disk('s3')->url($path);

        $user = auth()->user();
        broadcast(new PttAudioSent($url, $user->id, $user->nama_lengkap ?? 'Teknisi'))->toOthers();

        return response()->json([
            'status' => 'success',
            'url' => $url,
        ]);
    }
}
