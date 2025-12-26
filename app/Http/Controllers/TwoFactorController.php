<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    public function show2faForm()
    {
        $user = Auth::user();
        $google2fa = new Google2FA();

        // Generate secret jika belum ada
        if (!$user->google2fa_secret) {
            $user->google2fa_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        // Format untuk QR Code URI
        $QR_Text = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );

        // Generate QR code menggunakan BaconQrCode
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($QR_Text);

        return view('auth.2fa_setup', [
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $user->google2fa_secret,
        ]);
    }


    public function showVerifyForm()
    {
        return view('auth.2fa_verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);

        $user = \App\Models\User::find(session('2fa:user:id'));
        $google2fa = new \PragmaRX\Google2FA\Google2FA();

        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->input('code'));

        if ($valid) {
            $request->session()->forget('2fa:user:id');
            $request->session()->put('2fa_verified', true);

            Auth::login($user);
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['code' => 'Kode 2FA tidak valid']);
    }

    public function reset(Request $request)
    {
        $user = Auth::user();

        // Hapus secret lama
        $user->google2fa_secret = null;
        $user->save();

        // Hapus status verifikasi 2FA di session
        $request->session()->forget('2fa_verified');

        return redirect()->route('2fa.setup')->with('status', '2FA berhasil direset. Silakan scan QR code baru.');
    }
}
