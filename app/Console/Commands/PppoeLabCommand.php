<?php

namespace App\Console\Commands;

use App\Models\Pppoe;
use App\Models\Radcheck;
use App\Models\Radgroupreply;
use App\Models\Radusergroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PppoeLabCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pppoe:lab 
                            {action : sync (sinkronkan ke FreeRADIUS) | export (buat file script dial MikroTik) | check (cek kesiapan lab)}
                            {--interface=ether2 : Nama interface tester pada MikroTik Client}
                            {--output=dial_all.rsc : Nama file output script MikroTik}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Utilitas lab simulasi PPPoE: sinkronisasi 100% akun ke FreeRADIUS dan generate script dial MikroTik untuk ribuan akun.';

    public function handle(): int
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'check':
                return $this->handleCheck();
            case 'sync':
                return $this->handleSync();
            case 'export':
                return $this->handleExport();
            default:
                $this->error("Aksi tidak valid! Gunakan: check, sync, atau export.");
                return 1;
        }
    }

    protected function handleCheck(): int
    {
        $this->info("=== PENGECEKAN KESIAPAN LAB PPPOE ===");
        
        $totalPppoe = Pppoe::count();
        $totalRadcheck = Radcheck::count();
        $totalRadgroup = Radusergroup::count();
        
        $missingInRadcheck = Pppoe::whereNotIn('username', Radcheck::pluck('username'))->count();
        $isolirGroup = Radgroupreply::where('groupname', 'ISOLIR')->first();

        $this->table(
            ['Komponen', 'Status / Jumlah', 'Keterangan'],
            [
                ['Total PPPoE di App', $totalPppoe, 'Data pelanggan di Laravel'],
                ['Total di FreeRADIUS (radcheck)', $totalRadcheck, $missingInRadcheck > 0 ? "Kurang {$missingInRadcheck} akun" : 'Lengkap'],
                ['Total Usergroup (radusergroup)', $totalRadgroup, 'Relasi paket akun'],
                ['Group ISOLIR di radgroupreply', $isolirGroup ? 'Sudah Ada' : 'Belum Ada', $isolirGroup ? 'OK' : 'Perlu ditambahkan (Jalankan: php artisan pppoe:lab sync)'],
            ]
        );

        if ($missingInRadcheck > 0 || !$isolirGroup) {
            $this->warn("\nDisarankan jalankan: php artisan pppoe:lab sync");
        } else {
            $this->info("\nSemua data FreeRADIUS siap untuk simulasi dial.");
        }

        return 0;
    }

    protected function handleSync(): int
    {
        $this->info("1. Memeriksa group 'ISOLIR' pada radgroupreply FreeRADIUS...");
        
        $isolirProfile = Radgroupreply::where('groupname', 'ISOLIR')
            ->where('attribute', 'Mikrotik-Group')
            ->first();

        if (!$isolirProfile) {
            Radgroupreply::create([
                'groupname' => 'ISOLIR',
                'attribute' => 'Mikrotik-Group',
                'op' => ':=',
                'value' => 'profile-isolir',
            ]);
            $this->info("   [+] Ditambahkan: ISOLIR -> Mikrotik-Group := profile-isolir");
        }

        $isolirRate = Radgroupreply::where('groupname', 'ISOLIR')
            ->where('attribute', 'Mikrotik-Rate-Limit')
            ->first();

        if (!$isolirRate) {
            Radgroupreply::create([
                'groupname' => 'ISOLIR',
                'attribute' => 'Mikrotik-Rate-Limit',
                'op' => ':=',
                'value' => '128k/128k',
            ]);
            $this->info("   [+] Ditambahkan: ISOLIR -> Mikrotik-Rate-Limit := 128k/128k");
        }

        $this->info("\n2. Memeriksa akun PPPoE yang belum terdaftar di FreeRADIUS...");
        $allPppoes = Pppoe::with('paket')->get();
        $existingRadchecks = Radcheck::pluck('username')->flip();
        $existingRadgroups = Radusergroup::pluck('username')->flip();

        $addedCount = 0;
        foreach ($allPppoes as $pppoe) {
            $uname = trim($pppoe->username);
            if (empty($uname)) continue;

            $pass = $pppoe->password ?: '123';
            $paketName = $pppoe->paket->nama_paket ?? 'Paket Bronze 112K';

            // Insert radcheck if missing
            if (!isset($existingRadchecks[$uname])) {
                Radcheck::create([
                    'username' => $uname,
                    'attribute' => 'Cleartext-Password',
                    'op' => ':=',
                    'value' => $pass,
                ]);
                $addedCount++;
            }

            // Insert radusergroup if missing
            if (!isset($existingRadgroups[$uname])) {
                Radusergroup::create([
                    'username' => $uname,
                    'groupname' => $paketName,
                    'priority' => 1,
                ]);
            }
        }

        $this->info("   [+] Berhasil menyinkronkan {$addedCount} akun baru ke FreeRADIUS!");
        $this->info("Total akun di FreeRADIUS sekarang: " . Radcheck::count());

        return 0;
    }

    protected function handleExport(): int
    {
        $interface = $this->option('interface');
        $fileName = $this->option('output');
        $outputPath = base_path($fileName);

        $this->info("Menyiapkan script dial MikroTik untuk interface [{$interface}]...");

        $pppoes = Pppoe::all();
        $total = $pppoes->count();

        $lines = [];
        $lines[] = "# =====================================================================";
        $lines[] = "# MIKROTIK ROUTEROS SCRIPT: DIAL ALL PPPOE CLIENTS ({$total} AKUN)";
        $lines[] = "# Digunakan pada MikroTik CHR / Router Tester (Client Generator)";
        $lines[] = "# Generated by Laravel Erlangga.Net at " . now()->format('Y-m-d H:i:s');
        $lines[] = "# =====================================================================";
        $lines[] = "";
        $lines[] = "# 1. Script Bersihkan Akun Tester Sebelumnya (Jika ada):";
        $lines[] = "# /interface pppoe-client remove [find name~\"^dial-\"]";
        $lines[] = "";
        $lines[] = "# 2. Menambahkan {$total} PPPoE Client interfaces:";
        $lines[] = "/interface pppoe-client";

        $counter = 1;
        foreach ($pppoes as $p) {
            $name = "dial-" . $counter;
            $user = addslashes($p->username);
            $pass = addslashes($p->password ?: '123');

            // allow=pap,chap avoids ms-chapv2 mutual auth failure with FreeRADIUS
            // add-default-route=no is essential to avoid conflicting 1600+ default routes
            // use-peer-dns=no is essential to avoid overwriting router dns
            $lines[] = "add name=\"{$name}\" interface=\"{$interface}\" user=\"{$user}\" password=\"{$pass}\" allow=pap,chap add-default-route=no use-peer-dns=no disabled=no comment=\"simulasi-lab\"";
            $counter++;
        }

        $lines[] = "";
        $lines[] = ":put \"Selesai menambahkan {$total} akun PPPoE Client ke interface {$interface}!\"";

        File::put($outputPath, implode("\n", $lines));

        $this->info("File script berhasil dibuat:");
        $this->info("-> " . $outputPath);
        $this->info("Total akun yang diexport: {$total}");
        $this->newLine();
        $this->comment("Cara penggunaan:");
        $this->comment("1. Upload file [{$fileName}] ke MikroTik Client Tester (via WinBox File atau FTP).");
        $this->comment("2. Buka Terminal MikroTik Tester, lalu jalankan:");
        $this->comment("   /import {$fileName}");
        $this->comment("3. Buka MikroTik Server CHR (192.168.21.203), cek sesi aktif di: /ppp active print");

        return 0;
    }
}
