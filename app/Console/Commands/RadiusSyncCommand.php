<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RadiusSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'radius:sync';
    protected $description = 'Sinkronisasi akun PPPoE dari FreeRADIUS / daloRADIUS ke database Laravel';

    public function handle()
    {
        $this->info("Memulai sinkronisasi dari FreeRADIUS...");
        $startTime = microtime(true);

        // 1. Preload Pakets
        $pakets = \App\Models\Paket::all();
        $paketMap = [];
        foreach ($pakets as $p) {
            $cleanP = strtolower(trim(preg_replace('/\s+/', ' ', $p->nama_paket)));
            $paketMap[$cleanP] = $p->id;
            if (str_contains($cleanP, 'bronze') || str_contains($cleanP, '112')) {
                $paketMap['bronze'] = $p->id;
                $paketMap['112k'] = $p->id;
            }
            if (str_contains($cleanP, 'silver') || str_contains($cleanP, '179')) {
                $paketMap['silver'] = $p->id;
                $paketMap['179k'] = $p->id;
            }
            if (str_contains($cleanP, 'gold') || str_contains($cleanP, '239')) {
                $paketMap['gold'] = $p->id;
                $paketMap['239k'] = $p->id;
            }
            if (str_contains($cleanP, 'platinum') || str_contains($cleanP, '279')) {
                $paketMap['platinum'] = $p->id;
                $paketMap['279k'] = $p->id;
            }
        }
        $defaultPaketId = $pakets->first()?->id ?? 1;

        // 2. Preload existing PreOrders & PPPoEs
        $preOrdersByPhone = [];
        $preOrdersByName = [];
        $preOrdersById = [];
        foreach (\App\Models\PreOrder::all() as $po) {
            $preOrdersById[$po->id] = $po;
            if ($po->nama_lengkap) {
                $preOrdersByName[strtolower(trim(preg_replace('/\s+/', ' ', $po->nama_lengkap)))] = $po;
            }
            if ($po->no_whatsapp) {
                $digits = preg_replace('/[^0-9]/', '', $po->no_whatsapp);
                if (str_starts_with($digits, '628')) $digits = '08' . substr($digits, 3);
                if ($digits) $preOrdersByPhone[$digits] = $po;
            }
        }

        $existingPppoes = \App\Models\Pppoe::all()->keyBy(function ($item) {
            return strtolower(trim($item->username));
        });

        // 3. Load Radius data
        $radchecks = \App\Models\Radcheck::where('attribute', 'Cleartext-Password')->get()->keyBy('username');
        $radreplies = \App\Models\Radreply::where('attribute', 'Framed-IP-Address')->get()->keyBy('username');
        $radgroups = \App\Models\Radusergroup::all()->keyBy('username');
        $userinfos = \App\Models\Userinfo::all()->keyBy('username');

        $totalRadius = count($radchecks);
        $this->info("Total akun di FreeRADIUS: $totalRadius");

        $bar = $this->output->createProgressBar($totalRadius);
        $bar->start();

        $createdCount = 0;
        $updatedCount = 0;

        \Illuminate\Support\Facades\DB::connection()->disableQueryLog();
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            foreach ($radchecks as $username => $radcheck) {
                $userKey = strtolower(trim($username));
                $password = $radcheck->value ?: '123';
                $ipAddress = $radreplies->get($username)?->value ?? null;
                $groupName = $radgroups->get($username)?->groupname ?? '';
                $userInfo = $userinfos->get($username);

                // Resolve Paket ID
                $paketId = $defaultPaketId;
                $cleanGroup = strtolower(trim(preg_replace('/\s+/', ' ', $groupName)));
                if (isset($paketMap[$cleanGroup])) {
                    $paketId = $paketMap[$cleanGroup];
                } elseif (isset($paketMap['bronze']) && str_contains($cleanGroup, 'bronze')) {
                    $paketId = $paketMap['bronze'];
                } elseif (isset($paketMap['silver']) && str_contains($cleanGroup, 'silver')) {
                    $paketId = $paketMap['silver'];
                } elseif (isset($paketMap['gold']) && str_contains($cleanGroup, 'gold')) {
                    $paketId = $paketMap['gold'];
                } elseif (isset($paketMap['platinum']) && str_contains($cleanGroup, 'platinum')) {
                    $paketId = $paketMap['platinum'];
                }

                // Customer information from userinfo
                $name = $userInfo?->firstname ?: $username;
                $phone = $userInfo?->mobilephone ?: '';
                $email = $userInfo?->email ?: ($username . '@erlangga.net');
                $address = $userInfo?->address ?: '';
                $activeDate = $userInfo?->creationdate ? \Carbon\Carbon::parse($userInfo->creationdate) : now();

                $cleanName = strtolower(trim(preg_replace('/\s+/', ' ', $name)));
                $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                if (str_starts_with($cleanPhone, '628')) $cleanPhone = '08' . substr($cleanPhone, 3);

                // Match PreOrder
                $preOrder = null;
                if ($cleanPhone && isset($preOrdersByPhone[$cleanPhone])) {
                    $preOrder = $preOrdersByPhone[$cleanPhone];
                } elseif ($cleanName && isset($preOrdersByName[$cleanName])) {
                    $preOrder = $preOrdersByName[$cleanName];
                }

                if ($preOrder) {
                    $preOrderId = $preOrder->id;
                    $preOrder->update([
                        'paket_id' => $paketId,
                    ]);
                } else {
                    $newPo = \App\Models\PreOrder::create([
                        'id_sales' => 1,
                        'nama_sales' => 'Administrator',
                        'nama_lengkap' => $name,
                        'no_whatsapp' => $phone ?: '081234567890',
                        'alamat_email' => $email,
                        'alamat_lengkap' => $address ?: 'Alamat belum diisi',
                        'paket_id' => $paketId,
                        'created_at' => $activeDate,
                        'updated_at' => $activeDate,
                    ]);
                    $preOrderId = $newPo->id;
                    $preOrdersById[$preOrderId] = $newPo;
                    if ($cleanName) $preOrdersByName[$cleanName] = $newPo;
                    if ($cleanPhone) $preOrdersByPhone[$cleanPhone] = $newPo;
                }

                // Prepare PPPoE data
                $pppoeData = [
                    'pre_order_id' => $preOrderId,
                    'paket_id' => $paketId,
                    'username' => $username,
                    'password' => $password,
                    'router' => 'All',
                    'ip_address' => $ipAddress,
                    'tipe_pembayaran' => 'prepaid',
                    'tanggal_aktif' => $activeDate,
                    'periode_billing' => 'fixed',
                    'pajak' => 0,
                    'diskon' => 0,
                    'status_invoice' => 'terbayar',
                    'generate_invoice' => true,
                    'created_at' => $activeDate,
                    'updated_at' => now(),
                ];

                if (isset($existingPppoes[$userKey])) {
                    $existingPppoes[$userKey]->update($pppoeData);
                    $updatedCount++;
                } else {
                    $created = \App\Models\Pppoe::create($pppoeData);
                    $existingPppoes[$userKey] = $created;
                    $createdCount++;
                }

                $bar->advance();
            }

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            $bar->finish();
            $this->error("\nError: " . $e->getMessage());
            return 1;
        }

        $bar->finish();
        $this->newLine(2);

        $elapsed = round(microtime(true) - $startTime, 2);
        $this->info("=================================================");
        $this->info("     SINKRONISASI FREERADIUS SELESAI ($elapsed s)   ");
        $this->info("=================================================");
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total di FreeRADIUS', $totalRadius],
                ['PPPoE Baru Dibuat', $createdCount],
                ['PPPoE Diperbarui', $updatedCount],
                ['Total di Laravel PPPoE', \App\Models\Pppoe::count()],
            ]
        );

        return 0;
    }
}
