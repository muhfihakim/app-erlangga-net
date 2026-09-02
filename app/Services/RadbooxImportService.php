<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Paket;
use App\Models\Pppoe;
use App\Models\PreOrder;
use App\Models\Radcheck;
use App\Models\Radreply;
use App\Models\Radusergroup;
use App\Models\Transaction;
use App\Models\Userinfo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadbooxImportService
{
    /**
     * Standard official packages list
     */
    public const DEFAULT_PAKETS = [
        [
            'nama_paket' => 'Paket Bronze 112K',
            'harga' => 112000,
            'rate_limit' => '10M/10M',
            'keterangan' => 'Paket Internet 10 Mbps',
        ],
        [
            'nama_paket' => 'Paket Silver 179K',
            'harga' => 179000,
            'rate_limit' => '20M/20M',
            'keterangan' => 'Paket Internet 20 Mbps',
        ],
        [
            'nama_paket' => 'Paket Gold 239K',
            'harga' => 239000,
            'rate_limit' => '30M/30M',
            'keterangan' => 'Paket Internet 30 Mbps',
        ],
        [
            'nama_paket' => 'Paket Platinum 279K',
            'harga' => 279000,
            'rate_limit' => '50M/50M',
            'keterangan' => 'Paket Internet 50 Mbps',
        ],
    ];

    /**
     * Helper to clean strings for robust matching
     */
    protected function cleanStr(string $str): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $str)));
    }

    /**
     * Helper to clean phone numbers
     */
    protected function cleanPhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($digits, '628')) {
            $digits = '08' . substr($digits, 3);
        }
        return $digits;
    }

    /**
     * Reset and clear PPPoE, Invoices, Transactions, Pakets, and Radius tables.
     */
    public function resetData(array $options = []): array
    {
        $clearRadius = $options['clear_radius'] ?? true;
        $recreatePakets = $options['recreate_pakets'] ?? true;

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $deletedTransactions = Transaction::count();
        Transaction::truncate();

        $deletedInvoices = Invoice::count();
        Invoice::truncate();

        $deletedPppoes = Pppoe::count();
        Pppoe::truncate();

        $deletedPakets = Paket::count();
        Paket::truncate();

        // Clear pppoe username/password on existing customers so they are cleanly re-linked
        Customer::query()->update([
            'pppoe_username' => null,
            'pppoe_password' => null,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Reset Radius tables
        $deletedRadius = 0;
        if ($clearRadius) {
            try {
                $deletedRadius = Radcheck::count();
                Radcheck::truncate();
                Radreply::truncate();
                Radusergroup::truncate();
                Userinfo::truncate();
            } catch (\Throwable $e) {
                Log::warning("Radius truncate warning: " . $e->getMessage());
            }
        }

        // Recreate clean official pakets
        $createdPakets = 0;
        if ($recreatePakets) {
            foreach (self::DEFAULT_PAKETS as $p) {
                Paket::create($p);
                $createdPakets++;
            }
        }

        return [
            'transactions_cleared' => $deletedTransactions,
            'invoices_cleared' => $deletedInvoices,
            'pppoes_cleared' => $deletedPppoes,
            'pakets_cleared' => $deletedPakets,
            'pakets_created' => $createdPakets,
            'radius_cleared' => $deletedRadius,
        ];
    }

    /**
     * Analyze backup file (CSV or XLSX) and return data summary.
     */
    public function analyze(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File backup tidak ditemukan di: $filePath");
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext === 'xlsx') {
            return $this->analyzeXlsx($filePath);
        }

        $fp = fopen($filePath, 'r');
        if (!$fp) {
            throw new \Exception("Gagal membuka file backup: $filePath");
        }

        $stats = [
            'file_type' => 'csv',
            'nas_count' => 0,
            'pakets_count' => 0,
            'pppoe_count' => 0,
            'invoices_count' => 0,
            'transactions_count' => 0,
            'total_invoice_amount' => 0,
            'total_transaction_amount' => 0,
            'pakets' => [],
            'earliest_date' => null,
            'latest_date' => null,
            'file_size' => filesize($filePath),
            'file_name' => basename($filePath),
        ];

        while (($row = fgetcsv($fp, 0, ',', '"', '\\')) !== false) {
            $type = $row[0] ?? '';
            switch ($type) {
                case '1':
                    $stats['nas_count']++;
                    break;
                case '2':
                    $stats['pakets_count']++;
                    $stats['pakets'][] = [
                        'name' => $row[1] ?? '',
                        'price' => (float) ($row[2] ?? 0),
                        'rate_limit' => $row[3] ?? '',
                    ];
                    break;
                case '6':
                    $stats['pppoe_count']++;
                    break;
                case '5':
                    $stats['invoices_count']++;
                    $amount = (float) ($row[5] ?? 0);
                    $stats['total_invoice_amount'] += $amount;
                    $date = $row[8] ?? null;
                    if ($date) {
                        if (!$stats['earliest_date'] || $date < $stats['earliest_date']) $stats['earliest_date'] = $date;
                        if (!$stats['latest_date'] || $date > $stats['latest_date']) $stats['latest_date'] = $date;
                    }
                    break;
                case '4':
                    $stats['transactions_count']++;
                    $amount = (float) ($row[5] ?? 0);
                    $stats['total_transaction_amount'] += $amount;
                    break;
            }
        }
        fclose($fp);

        return $stats;
    }

    /**
     * Analyze XLSX file.
     */
    protected function analyzeXlsx(string $filePath): array
    {
        $rows = XlsxParser::parse($filePath);
        $totalRows = max(0, count($rows) - 1); // Exclude header

        $pakets = [];
        $header = $rows[0] ?? [];
        $profileIdx = array_search('profile', array_map('strtolower', $header));
        if ($profileIdx === false) $profileIdx = 3;

        for ($i = 1; $i < count($rows); $i++) {
            $pName = trim($rows[$i][$profileIdx] ?? '');
            if ($pName && !isset($pakets[$pName])) {
                $pakets[$pName] = true;
            }
        }

        return [
            'file_type' => 'xlsx',
            'nas_count' => 0,
            'pakets_count' => count($pakets),
            'pppoe_count' => $totalRows,
            'invoices_count' => 0,
            'transactions_count' => 0,
            'total_invoice_amount' => 0,
            'total_transaction_amount' => 0,
            'pakets' => array_keys($pakets),
            'earliest_date' => null,
            'latest_date' => null,
            'file_size' => filesize($filePath),
            'file_name' => basename($filePath),
        ];
    }

    /**
     * Run import from CSV or XLSX.
     */
    public function import(string $filePath, array $options = [], ?callable $progressCallback = null): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File backup tidak ditemukan di: $filePath");
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext === 'xlsx') {
            return $this->importXlsx($filePath, $options, $progressCallback);
        }

        return $this->importCsv($filePath, $options, $progressCallback);
    }

    /**
     * Import from XLSX file (Users PPP).
     */
    public function importXlsx(string $filePath, array $options = [], ?callable $progressCallback = null): array
    {
        $startTime = microtime(true);
        $syncRadius = $options['sync_radius'] ?? true;

        $rows = XlsxParser::parse($filePath);
        if (empty($rows) || count($rows) < 2) {
            throw new \Exception("File Excel tidak memiliki data pengguna.");
        }

        $header = array_map('strtolower', array_map('trim', $rows[0]));
        $userCol = array_search('user', $header) !== false ? array_search('user', $header) : 1;
        $passCol = array_search('password', $header) !== false ? array_search('password', $header) : 2;
        $profCol = array_search('profile', $header) !== false ? array_search('profile', $header) : 3;
        $ipCol = array_search('ip', $header) !== false ? array_search('ip', $header) : 6;
        $nameCol = array_search('name', $header) !== false ? array_search('name', $header) : 7;
        $phoneCol = array_search('phone', $header) !== false ? array_search('phone', $header) : 8;
        $addrCol = array_search('address', $header) !== false ? array_search('address', $header) : 9;

        $results = [
            'pakets_synced' => 0,
            'pppoe_created' => 0,
            'pppoe_updated' => 0,
            'preorders_created' => 0,
            'customers_created' => 0,
            'customers_linked' => 0,
            'invoices_imported' => 0,
            'transactions_imported' => 0,
            'radius_synced' => 0,
            'errors' => [],
            'time_elapsed' => 0,
        ];

        // Ensure pakets exist
        $paketMap = $this->ensureDefaultPakets();
        $results['pakets_synced'] = count($paketMap);

        // Preload memory caches
        $customersById = [];
        $customersByUsername = [];
        $customersByIdPelanggan = [];
        $customersByName = [];
        $customersByPhone = [];
        $customersByPreOrderId = [];

        foreach (Customer::withTrashed()->get() as $c) {
            if ($c->trashed()) {
                $c->restore();
            }
            $customersById[$c->id] = $c;
            if ($c->pppoe_username) {
                $customersByUsername[strtolower(trim($c->pppoe_username))] = $c;
            }
            if ($c->id_pelanggan) {
                $customersByIdPelanggan[trim($c->id_pelanggan)] = $c;
            }
            if ($c->nama_lengkap) {
                $customersByName[$this->cleanStr($c->nama_lengkap)] = $c;
            }
            if ($c->no_whatsapp) {
                $p = $this->cleanPhone($c->no_whatsapp);
                if ($p) $customersByPhone[$p] = $c;
            }
            if ($c->pre_order_id) {
                $customersByPreOrderId[$c->pre_order_id] = $c;
            }
        }

        $preOrdersById = [];
        $preOrdersByName = [];
        $preOrdersByPhone = [];
        $preOrdersByNik = [];

        foreach (PreOrder::all() as $po) {
            $preOrdersById[$po->id] = $po;
            if ($po->nama_lengkap) {
                $preOrdersByName[$this->cleanStr($po->nama_lengkap)] = $po;
            }
            if ($po->no_whatsapp) {
                $p = $this->cleanPhone($po->no_whatsapp);
                if ($p) $preOrdersByPhone[$p] = $po;
            }
            if ($po->no_identitas) {
                $preOrdersByNik[trim($po->no_identitas)] = $po;
            }
        }

        $pppoesByUsername = [];
        $pppoesByPreOrderId = [];
        foreach (Pppoe::all() as $p) {
            $pppoesByUsername[strtolower(trim($p->username))] = $p;
            if ($p->pre_order_id) {
                $pppoesByPreOrderId[$p->pre_order_id] = $p;
            }
        }

        $radiusAvailable = false;
        if ($syncRadius) {
            try {
                Radcheck::count();
                $radiusAvailable = true;
            } catch (\Throwable $e) {
                $results['errors'][] = "Radius connection unavailable: " . $e->getMessage();
            }
        }

        $radcheckBatch = [];
        $radreplyBatch = [];
        $radusergroupBatch = [];
        $userinfoBatch = [];

        $totalUsers = count($rows) - 1;
        $processed = 0;

        DB::connection()->disableQueryLog();
        DB::beginTransaction();
        try {
            for ($i = 1; $i < count($rows); $i++) {
                $processed++;
                $row = $rows[$i];
                $username = trim($row[$userCol] ?? '');
                if (!$username) continue;

                $userKey = strtolower($username);
                $password = trim($row[$passCol] ?? '123');
                $profile = trim($row[$profCol] ?? '');
                $paketId = $this->resolvePaketId($profile, $paketMap);
                $ip = trim($row[$ipCol] ?? '');
                $name = trim($row[$nameCol] ?? $username);
                if (!$name) $name = $username;
                $phone = trim($row[$phoneCol] ?? '');
                $address = trim($row[$addrCol] ?? '');
                $createdAt = now();

                $cleanName = $this->cleanStr($name);
                $cleanPhone = $phone ? $this->cleanPhone($phone) : '';

                // SMART MATCHING to existing records
                $existingCustomer = $customersByUsername[$userKey] ?? null;
                if (!$existingCustomer && $cleanName && isset($customersByName[$cleanName])) {
                    $existingCustomer = $customersByName[$cleanName];
                }
                if (!$existingCustomer && $cleanPhone && isset($customersByPhone[$cleanPhone])) {
                    $existingCustomer = $customersByPhone[$cleanPhone];
                }

                $existingPreOrder = null;
                if ($existingCustomer && $existingCustomer->pre_order_id && isset($preOrdersById[$existingCustomer->pre_order_id])) {
                    $existingPreOrder = $preOrdersById[$existingCustomer->pre_order_id];
                }
                if (!$existingPreOrder && $cleanName && isset($preOrdersByName[$cleanName])) {
                    $existingPreOrder = $preOrdersByName[$cleanName];
                }
                if (!$existingPreOrder && $cleanPhone && isset($preOrdersByPhone[$cleanPhone])) {
                    $existingPreOrder = $preOrdersByPhone[$cleanPhone];
                }

                // If PreOrder exists, use its ID; otherwise create a new PreOrder
                if ($existingPreOrder) {
                    $preOrderId = $existingPreOrder->id;
                    $existingPreOrder->update([
                        'paket_id' => $paketId ?: $existingPreOrder->paket_id,
                    ]);
                } else {
                    $preOrder = PreOrder::create([
                        'id_sales' => 1,
                        'nama_sales' => 'Administrator',
                        'nama_lengkap' => $name,
                        'no_whatsapp' => $phone ?: '081234567890',
                        'alamat_email' => $username . '@erlangga.net',
                        'alamat_lengkap' => $address ?: 'Alamat belum diisi',
                        'paket_id' => $paketId,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                    $preOrderId = $preOrder->id;
                    $preOrdersById[$preOrderId] = $preOrder;
                    if ($cleanName) $preOrdersByName[$cleanName] = $preOrder;
                    if ($cleanPhone) $preOrdersByPhone[$cleanPhone] = $preOrder;
                    $results['preorders_created']++;
                }

                // Re-check customer by pre_order_id to guarantee no duplicate constraint
                if (!$existingCustomer && isset($customersByPreOrderId[$preOrderId])) {
                    $existingCustomer = $customersByPreOrderId[$preOrderId];
                }
                if (!$existingCustomer) {
                    $existingCustomer = Customer::withTrashed()->where('pre_order_id', $preOrderId)->first();
                    if ($existingCustomer) {
                        if ($existingCustomer->trashed()) $existingCustomer->restore();
                        $customersByPreOrderId[$preOrderId] = $existingCustomer;
                    }
                }

                // If Customer exists, LINK to it; otherwise create a new Customer
                if ($existingCustomer) {
                    $existingCustomer->update([
                        'pppoe_username' => $username,
                        'pppoe_password' => $password,
                        'paket_id' => $paketId ?: $existingCustomer->paket_id,
                    ]);
                    $customersByUsername[$userKey] = $existingCustomer;
                    $customersByPreOrderId[$existingCustomer->pre_order_id] = $existingCustomer;
                    $results['customers_linked']++;
                } else {
                    $cust = Customer::create([
                        'pre_order_id' => $preOrderId,
                        'id_sales' => 1,
                        'id_teknisi' => 3,
                        'pembayaran' => 'Cash',
                        'nama_lengkap' => $name,
                        'alamat_lengkap' => $address ?: 'Alamat belum diisi',
                        'no_whatsapp' => $phone ?: '',
                        'pppoe_username' => $username,
                        'pppoe_password' => $password,
                        'paket_id' => $paketId,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                    $customersById[$cust->id] = $cust;
                    $customersByUsername[$userKey] = $cust;
                    $customersByPreOrderId[$preOrderId] = $cust;
                    if ($cleanName) $customersByName[$cleanName] = $cust;
                    if ($cleanPhone) $customersByPhone[$cleanPhone] = $cust;
                    $results['customers_created']++;
                }

                // PPPoE record
                $pppoeData = [
                    'pre_order_id' => $preOrderId,
                    'paket_id' => $paketId,
                    'username' => $username,
                    'password' => $password,
                    'ip_address' => $ip ?: null,
                    'tipe_pembayaran' => 'prepaid',
                    'tanggal_aktif' => $createdAt,
                    'periode_billing' => 'fixed',
                    'pajak' => 0,
                    'diskon' => 0,
                    'status_invoice' => 'terbayar',
                    'generate_invoice' => true,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                $pppoe = $pppoesByUsername[$userKey] ?? ($pppoesByPreOrderId[$preOrderId] ?? null);
                if (!$pppoe) {
                    $pppoe = Pppoe::create($pppoeData);
                    $pppoesByUsername[$userKey] = $pppoe;
                    $pppoesByPreOrderId[$preOrderId] = $pppoe;
                    $results['pppoe_created']++;
                } else {
                    $pppoe->update($pppoeData);
                    $pppoesByUsername[$userKey] = $pppoe;
                    $pppoesByPreOrderId[$preOrderId] = $pppoe;
                    $results['pppoe_updated']++;
                }

                if ($radiusAvailable) {
                    $radcheckBatch[] = [
                        'username' => $username,
                        'attribute' => 'Cleartext-Password',
                        'op' => ':=',
                        'value' => $password,
                    ];
                    if ($ip) {
                        $radreplyBatch[] = [
                            'username' => $username,
                            'attribute' => 'Framed-IP-Address',
                            'op' => ':=',
                            'value' => $ip,
                        ];
                    }
                    $radusergroupBatch[] = [
                        'username' => $username,
                        'groupname' => $profile ?: 'default',
                        'priority' => 1,
                    ];
                    $userinfoBatch[] = [
                        'username' => $username,
                        'firstname' => $name,
                        'lastname' => '',
                        'email' => $username . '@erlangga.net',
                        'address' => $address ?: '',
                        'mobilephone' => $phone ?: '',
                        'creationdate' => date('Y-m-d H:i:s'),
                        'updatedate' => date('Y-m-d H:i:s'),
                        'creationby' => 'administrator',
                        'updateby' => 'administrator',
                    ];
                }

                if ($progressCallback && $processed % 200 === 0) {
                    $progressCallback('pppoe', $processed, $totalUsers);
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        if ($progressCallback) {
            $progressCallback('pppoe', $totalUsers, $totalUsers);
        }

        // Bulk sync Radius
        if ($radiusAvailable && !empty($radcheckBatch)) {
            try {
                foreach (array_chunk($radcheckBatch, 500) as $chunk) {
                    Radcheck::upsert($chunk, ['username', 'attribute'], ['op', 'value']);
                }
                foreach (array_chunk($radreplyBatch, 500) as $chunk) {
                    Radreply::upsert($chunk, ['username', 'attribute'], ['op', 'value']);
                }
                foreach (array_chunk($radusergroupBatch, 500) as $chunk) {
                    Radusergroup::upsert($chunk, ['username'], ['groupname', 'priority']);
                }
                foreach (array_chunk($userinfoBatch, 500) as $chunk) {
                    Userinfo::upsert($chunk, ['username'], ['firstname', 'email', 'address', 'mobilephone', 'updatedate']);
                }
                $results['radius_synced'] = count($radcheckBatch);
            } catch (\Throwable $e) {
                Log::warning("Batch Radius sync warning: " . $e->getMessage());
            }
        }

        $results['time_elapsed'] = round(microtime(true) - $startTime, 2);
        return $results;
    }

    /**
     * High-performance import from CSV backup.
     */
    public function importCsv(string $filePath, array $options = [], ?callable $progressCallback = null): array
    {
        $syncRadius = $options['sync_radius'] ?? true;
        $importInvoices = $options['import_invoices'] ?? true;
        $importTransactions = $options['import_transactions'] ?? true;

        $fp = fopen($filePath, 'r');
        if (!$fp) {
            throw new \Exception("Gagal membuka file backup: $filePath");
        }

        $startTime = microtime(true);

        $type2Rows = [];
        $type6Rows = [];
        $type5Rows = [];
        $type4Rows = [];

        while (($row = fgetcsv($fp, 0, ',', '"', '\\')) !== false) {
            $type = $row[0] ?? '';
            if ($type === '2') $type2Rows[] = $row;
            elseif ($type === '6') $type6Rows[] = $row;
            elseif ($type === '5') $type5Rows[] = $row;
            elseif ($type === '4') $type4Rows[] = $row;
        }
        fclose($fp);

        $results = [
            'pakets_synced' => 0,
            'pppoe_created' => 0,
            'pppoe_updated' => 0,
            'preorders_created' => 0,
            'customers_created' => 0,
            'customers_linked' => 0,
            'invoices_imported' => 0,
            'transactions_imported' => 0,
            'radius_synced' => 0,
            'errors' => [],
            'time_elapsed' => 0,
        ];

        // 1. Sync & Map Pakets
        $paketMap = $this->syncPakets($type2Rows);
        $results['pakets_synced'] = count($paketMap);

        if ($progressCallback) {
            $progressCallback('pakets', count($paketMap), count($paketMap));
        }

        // Preload memory caches for instant multi-field matching
        $customersById = [];
        $customersByUsername = [];
        $customersByIdPelanggan = [];
        $customersByName = [];
        $customersByPhone = [];
        $customersByPreOrderId = [];

        foreach (Customer::withTrashed()->get() as $c) {
            if ($c->trashed()) {
                $c->restore();
            }
            $customersById[$c->id] = $c;
            if ($c->pppoe_username) {
                $customersByUsername[strtolower(trim($c->pppoe_username))] = $c;
            }
            if ($c->id_pelanggan) {
                $customersByIdPelanggan[trim($c->id_pelanggan)] = $c;
            }
            if ($c->nama_lengkap) {
                $customersByName[$this->cleanStr($c->nama_lengkap)] = $c;
            }
            if ($c->no_whatsapp) {
                $p = $this->cleanPhone($c->no_whatsapp);
                if ($p) $customersByPhone[$p] = $c;
            }
            if ($c->pre_order_id) {
                $customersByPreOrderId[$c->pre_order_id] = $c;
            }
        }

        $preOrdersById = [];
        $preOrdersByName = [];
        $preOrdersByPhone = [];
        $preOrdersByNik = [];

        foreach (PreOrder::all() as $po) {
            $preOrdersById[$po->id] = $po;
            if ($po->nama_lengkap) {
                $preOrdersByName[$this->cleanStr($po->nama_lengkap)] = $po;
            }
            if ($po->no_whatsapp) {
                $p = $this->cleanPhone($po->no_whatsapp);
                if ($p) $preOrdersByPhone[$p] = $po;
            }
            if ($po->no_identitas) {
                $preOrdersByNik[trim($po->no_identitas)] = $po;
            }
        }

        $pppoesByUsername = [];
        $pppoesByPreOrderId = [];
        foreach (Pppoe::all() as $p) {
            $pppoesByUsername[strtolower(trim($p->username))] = $p;
            if ($p->pre_order_id) {
                $pppoesByPreOrderId[$p->pre_order_id] = $p;
            }
        }

        $radiusAvailable = false;
        if ($syncRadius) {
            try {
                Radcheck::count();
                $radiusAvailable = true;
            } catch (\Throwable $e) {
                $results['errors'][] = "Radius connection unavailable: " . $e->getMessage();
            }
        }

        // 2. Import Customers & PPPoE Accounts
        $radbooxCustToPppoeId = [];
        $usernameToPppoeId = [];
        $custNameToPppoeId = [];

        $totalPppoe = count($type6Rows);
        $processedPppoe = 0;

        $radcheckBatch = [];
        $radreplyBatch = [];
        $radusergroupBatch = [];
        $userinfoBatch = [];

        DB::connection()->disableQueryLog();

        DB::beginTransaction();
        try {
            foreach ($type6Rows as $row) {
                $processedPppoe++;
                $username = trim($row[1] ?? '');
                if (!$username) continue;

                $userKey = strtolower($username);
                $password = trim($row[2] ?? '123');
                $paketName = trim($row[3] ?? '');
                $paketId = $this->resolvePaketId($paketName, $paketMap);
                $ipAddress = trim($row[6] ?? '');
                $createdAt = !empty($row[10]) ? Carbon::parse($row[10]) : now();

                $isFullRow = count($row) >= 27;
                $radbooxCustId = $isFullRow ? trim($row[12] ?? '') : '';
                $name = $isFullRow ? trim($row[13] ?? '') : $username;
                if (!$name) $name = $username;
                $email = $isFullRow ? trim($row[14] ?? '') : '';
                $phone = $isFullRow ? trim($row[15] ?? '') : '';
                $nik = $isFullRow ? trim($row[16] ?? '') : '';
                $address = $isFullRow ? trim($row[17] ?? '') : '';
                $activeDate = ($isFullRow && !empty($row[22])) ? Carbon::parse($row[22]) : $createdAt;
                $idPelanggan = $isFullRow ? trim($row[23] ?? '') : '';
                $lat = $isFullRow ? trim($row[25] ?? '') : '';
                $lng = $isFullRow ? trim($row[26] ?? '') : '';
                $tikor = ($lat && $lng) ? "$lat,$lng" : null;

                $cleanName = $this->cleanStr($name);
                $cleanPhone = $phone ? $this->cleanPhone($phone) : '';

                // SMART MULTI-TIER MATCHING to existing records
                $existingCustomer = null;
                // Tier 1: ID Pelanggan (most accurate)
                if ($idPelanggan && isset($customersByIdPelanggan[$idPelanggan])) {
                    $existingCustomer = $customersByIdPelanggan[$idPelanggan];
                }
                // Tier 2: Username
                if (!$existingCustomer && isset($customersByUsername[$userKey])) {
                    $existingCustomer = $customersByUsername[$userKey];
                }
                // Tier 3: Phone
                if (!$existingCustomer && $cleanPhone && isset($customersByPhone[$cleanPhone])) {
                    $existingCustomer = $customersByPhone[$cleanPhone];
                }
                // Tier 4: Name
                if (!$existingCustomer && $cleanName && isset($customersByName[$cleanName])) {
                    $existingCustomer = $customersByName[$cleanName];
                }

                $existingPreOrder = null;
                if ($existingCustomer && $existingCustomer->pre_order_id && isset($preOrdersById[$existingCustomer->pre_order_id])) {
                    $existingPreOrder = $preOrdersById[$existingCustomer->pre_order_id];
                }
                if (!$existingPreOrder && $nik && isset($preOrdersByNik[$nik])) {
                    $existingPreOrder = $preOrdersByNik[$nik];
                }
                if (!$existingPreOrder && $cleanPhone && isset($preOrdersByPhone[$cleanPhone])) {
                    $existingPreOrder = $preOrdersByPhone[$cleanPhone];
                }
                if (!$existingPreOrder && $cleanName && isset($preOrdersByName[$cleanName])) {
                    $existingPreOrder = $preOrdersByName[$cleanName];
                }

                // Link to PreOrder or create new
                if ($existingPreOrder) {
                    $preOrderId = $existingPreOrder->id;
                    $existingPreOrder->update([
                        'paket_id' => $paketId ?: $existingPreOrder->paket_id,
                        'no_identitas' => $nik ?: $existingPreOrder->no_identitas,
                        'titik_koordinat' => $tikor ?: $existingPreOrder->titik_koordinat,
                    ]);
                } else {
                    $preOrder = PreOrder::create([
                        'id_sales' => 1,
                        'nama_sales' => 'Administrator',
                        'nama_lengkap' => $name,
                        'no_identitas' => $nik ?: null,
                        'no_whatsapp' => $phone ?: '081234567890',
                        'alamat_email' => $email ?: ($username . '@erlangga.net'),
                        'alamat_lengkap' => $address ?: 'Alamat belum diisi',
                        'titik_koordinat' => $tikor,
                        'paket_id' => $paketId,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                    $preOrderId = $preOrder->id;
                    $preOrdersById[$preOrderId] = $preOrder;
                    if ($nik) $preOrdersByNik[$nik] = $preOrder;
                    if ($cleanName) $preOrdersByName[$cleanName] = $preOrder;
                    if ($cleanPhone) $preOrdersByPhone[$cleanPhone] = $preOrder;
                    $results['preorders_created']++;
                }

                // Re-check customer by pre_order_id to guarantee no duplicate constraint
                if (!$existingCustomer && isset($customersByPreOrderId[$preOrderId])) {
                    $existingCustomer = $customersByPreOrderId[$preOrderId];
                }
                if (!$existingCustomer) {
                    $existingCustomer = Customer::withTrashed()->where('pre_order_id', $preOrderId)->first();
                    if ($existingCustomer) {
                        if ($existingCustomer->trashed()) $existingCustomer->restore();
                        $customersByPreOrderId[$preOrderId] = $existingCustomer;
                    }
                }

                // Link to Customer or create new with safe unique constraint handling
                if ($existingCustomer) {
                    $updateCust = [
                        'pppoe_username' => $username,
                        'pppoe_password' => $password,
                        'paket_id' => $paketId ?: $existingCustomer->paket_id,
                    ];

                    // Safely check if id_pelanggan can be updated without duplicate error
                    if ($idPelanggan && $existingCustomer->id_pelanggan !== $idPelanggan) {
                        if (!isset($customersByIdPelanggan[$idPelanggan])) {
                            $updateCust['id_pelanggan'] = $idPelanggan;
                            $customersByIdPelanggan[$idPelanggan] = $existingCustomer;
                        }
                    }

                    $existingCustomer->update($updateCust);
                    $customersByUsername[$userKey] = $existingCustomer;
                    $customersByPreOrderId[$existingCustomer->pre_order_id] = $existingCustomer;
                    $results['customers_linked']++;
                } else {
                    // Safe ID Pelanggan for new customer
                    $assignedIdPelanggan = ($idPelanggan && !isset($customersByIdPelanggan[$idPelanggan])) ? $idPelanggan : null;

                    $cust = Customer::create([
                        'pre_order_id' => $preOrderId,
                        'id_sales' => 1,
                        'id_teknisi' => 3,
                        'pembayaran' => 'Cash',
                        'nama_lengkap' => $name,
                        'alamat_lengkap' => $address ?: 'Alamat belum diisi',
                        'no_whatsapp' => $phone ?: '',
                        'id_pelanggan' => $assignedIdPelanggan,
                        'pppoe_username' => $username,
                        'pppoe_password' => $password,
                        'paket_id' => $paketId,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                    $customersById[$cust->id] = $cust;
                    $customersByUsername[$userKey] = $cust;
                    $customersByPreOrderId[$preOrderId] = $cust;
                    if ($assignedIdPelanggan) $customersByIdPelanggan[$assignedIdPelanggan] = $cust;
                    if ($cleanName) $customersByName[$cleanName] = $cust;
                    if ($cleanPhone) $customersByPhone[$cleanPhone] = $cust;
                    $results['customers_created']++;
                }

                // PPPoE record
                $pppoeData = [
                    'pre_order_id' => $preOrderId,
                    'paket_id' => $paketId,
                    'username' => $username,
                    'password' => $password,
                    'ip_address' => $ipAddress ?: null,
                    'tipe_pembayaran' => 'prepaid',
                    'tanggal_aktif' => $activeDate,
                    'periode_billing' => 'fixed',
                    'pajak' => 0,
                    'diskon' => 0,
                    'status_invoice' => 'terbayar',
                    'generate_invoice' => true,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                $pppoe = $pppoesByUsername[$userKey] ?? ($pppoesByPreOrderId[$preOrderId] ?? null);
                if (!$pppoe) {
                    $pppoe = Pppoe::create($pppoeData);
                    $pppoesByUsername[$userKey] = $pppoe;
                    $pppoesByPreOrderId[$preOrderId] = $pppoe;
                    $results['pppoe_created']++;
                } else {
                    $pppoe->update($pppoeData);
                    $pppoesByUsername[$userKey] = $pppoe;
                    $pppoesByPreOrderId[$preOrderId] = $pppoe;
                    $results['pppoe_updated']++;
                }

                // Map for invoice and transaction linking
                if ($radbooxCustId) {
                    $radbooxCustToPppoeId[$radbooxCustId] = $pppoe->id;
                }
                $usernameToPppoeId[$userKey] = $pppoe->id;
                $custNameToPppoeId[$cleanName] = $pppoe->id;

                // Prepare Radius batch items
                if ($radiusAvailable) {
                    $radcheckBatch[] = [
                        'username' => $username,
                        'attribute' => 'Cleartext-Password',
                        'op' => ':=',
                        'value' => $password,
                    ];
                    if ($ipAddress) {
                        $radreplyBatch[] = [
                            'username' => $username,
                            'attribute' => 'Framed-IP-Address',
                            'op' => ':=',
                            'value' => $ipAddress,
                        ];
                    }
                    $radusergroupBatch[] = [
                        'username' => $username,
                        'groupname' => $paketName ?: 'default',
                        'priority' => 1,
                    ];
                    $userinfoBatch[] = [
                        'username' => $username,
                        'firstname' => $name,
                        'lastname' => '',
                        'email' => $email ?: ($username . '@erlangga.net'),
                        'address' => $address ?: '',
                        'mobilephone' => $phone ?: '',
                        'creationdate' => $createdAt->format('Y-m-d H:i:s'),
                        'updatedate' => now()->format('Y-m-d H:i:s'),
                        'creationby' => 'administrator',
                        'updateby' => 'administrator',
                    ];
                }

                if ($progressCallback && $processedPppoe % 200 === 0) {
                    $progressCallback('pppoe', $processedPppoe, $totalPppoe);
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        if ($progressCallback) {
            $progressCallback('pppoe', $totalPppoe, $totalPppoe);
        }

        // Bulk upsert to Radius in chunks of 500
        if ($radiusAvailable && !empty($radcheckBatch)) {
            try {
                foreach (array_chunk($radcheckBatch, 500) as $chunk) {
                    Radcheck::upsert($chunk, ['username', 'attribute'], ['op', 'value']);
                }
                foreach (array_chunk($radreplyBatch, 500) as $chunk) {
                    Radreply::upsert($chunk, ['username', 'attribute'], ['op', 'value']);
                }
                foreach (array_chunk($radusergroupBatch, 500) as $chunk) {
                    Radusergroup::upsert($chunk, ['username'], ['groupname', 'priority']);
                }
                foreach (array_chunk($userinfoBatch, 500) as $chunk) {
                    Userinfo::upsert($chunk, ['username'], ['firstname', 'email', 'address', 'mobilephone', 'updatedate']);
                }
                $results['radius_synced'] = count($radcheckBatch);
            } catch (\Throwable $e) {
                Log::warning("Batch Radius sync warning: " . $e->getMessage());
            }
        }

        // 3. Import Invoices (Type 5)
        $radbooxInvToDbInvId = [];
        if ($importInvoices && !empty($type5Rows)) {
            $totalInvoices = count($type5Rows);
            $processedInvoices = 0;
            $invoiceBatch = [];

            foreach ($type5Rows as $row) {
                $processedInvoices++;
                $radbooxInvId = trim($row[1] ?? '');
                $radbooxCustId = trim($row[2] ?? '');
                $invSuffix = trim($row[3] ?? $radbooxInvId);
                $desc = trim($row[4] ?? '');
                $total = (float) ($row[5] ?? 0);
                $createdAt = !empty($row[8]) ? Carbon::parse($row[8]) : now();
                $dueDate = !empty($row[9]) ? Carbon::parse($row[9]) : $createdAt->copy()->addDays(7);
                $paidDateRaw = trim($row[14] ?? '');
                $statusFlag = trim($row[15] ?? '');

                // Radboox format: 1 = Unpaid, 2 = Paid. Also if payment date is empty, it is unpaid.
                $isPaid = ($statusFlag === '2' && !empty($paidDateRaw));
                $status = $isPaid ? 'paid' : 'unpaid';
                $payMethodCode = $row[13] ?? '1';
                $payMethod = ($payMethodCode == '1') ? 'cash' : 'cashless';
                $paidDate = $isPaid ? Carbon::parse($paidDateRaw) : null;

                $pppoeId = $radbooxCustToPppoeId[$radbooxCustId] ?? null;

                if (!$pppoeId && preg_match('/Internet\s*:\s*([a-zA-Z0-9_\-\.@]+)/i', $desc, $matches)) {
                    $extractedUsername = strtolower(trim($matches[1]));
                    $pppoeId = $usernameToPppoeId[$extractedUsername] ?? null;
                }

                if (!$pppoeId) continue;

                $invoiceNumber = 'INV-' . str_pad($invSuffix, 6, '0', STR_PAD_LEFT);

                $invoiceBatch[] = [
                    'radboox_inv_id' => $radbooxInvId,
                    'data' => [
                        'pppoe_id' => $pppoeId,
                        'invoice_number' => $invoiceNumber,
                        'amount' => $total,
                        'tax' => 0,
                        'discount' => 0,
                        'total' => $total,
                        'status' => $status,
                        'due_date' => $dueDate->format('Y-m-d'),
                        'payment_method' => $isPaid ? $payMethod : null,
                        'created_at' => $createdAt->format('Y-m-d H:i:s'),
                        'updated_at' => ($isPaid ? $paidDate : $createdAt)->format('Y-m-d H:i:s'),
                    ]
                ];
            }

            DB::beginTransaction();
            try {
                foreach (array_chunk($invoiceBatch, 500) as $chunk) {
                    $insertData = array_map(fn($item) => $item['data'], $chunk);
                    Invoice::upsert(
                        $insertData,
                        ['invoice_number'],
                        ['amount', 'tax', 'discount', 'total', 'status', 'due_date', 'payment_method', 'updated_at']
                    );
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }

            $allDbInvoices = Invoice::pluck('id', 'invoice_number')->toArray();
            foreach ($invoiceBatch as $item) {
                $invNum = $item['data']['invoice_number'];
                $radbooxInvId = $item['radboox_inv_id'];
                if ($radbooxInvId && isset($allDbInvoices[$invNum])) {
                    $radbooxInvToDbInvId[$radbooxInvId] = $allDbInvoices[$invNum];
                }
            }

            $results['invoices_imported'] = count($invoiceBatch);

            if ($progressCallback) {
                $progressCallback('invoices', $totalInvoices, $totalInvoices);
            }
        }

        // 4. Import Transactions (Type 4)
        if ($importTransactions && !empty($type4Rows)) {
            $totalTx = count($type4Rows);
            $transactionBatch = [];

            if (empty($allDbInvoices)) {
                $allDbInvoices = Invoice::pluck('id', 'invoice_number')->toArray();
            }

            foreach ($type4Rows as $row) {
                $radbooxInvId = trim($row[1] ?? '');
                $notes = trim($row[4] ?? '');
                $amount = (float) ($row[5] ?? 0);
                $payDate = !empty($row[6]) ? Carbon::parse($row[6]) : now();
                $payMethodCode = $row[7] ?? '2';
                $payMethod = ($payMethodCode == '1') ? 'cash' : 'cashless';

                $invoiceId = $radbooxInvToDbInvId[$radbooxInvId] ?? null;

                if (!$invoiceId) {
                    if (preg_match('/#0*([0-9]+)/', $notes, $m)) {
                        $invCode = 'INV-' . str_pad($m[1], 6, '0', STR_PAD_LEFT);
                        $invoiceId = $allDbInvoices[$invCode] ?? null;
                    }
                }

                if (!$invoiceId) {
                    $parts = explode('-', $notes, 2);
                    $custName = $this->cleanStr($parts[1] ?? '');
                    if ($custName && isset($custNameToPppoeId[$custName])) {
                        $pppoeId = $custNameToPppoeId[$custName];
                        $inv = Invoice::where('pppoe_id', $pppoeId)->latest()->first();
                        if ($inv) {
                            $invoiceId = $inv->id;
                        }
                    }
                }

                if (!$invoiceId) continue;

                $transactionBatch[] = [
                    'invoice_id' => $invoiceId,
                    'payment_method' => $payMethod,
                    'amount_paid' => $amount,
                    'payment_date' => $payDate->format('Y-m-d H:i:s'),
                    'status' => 'success',
                    'created_at' => $payDate->format('Y-m-d H:i:s'),
                    'updated_at' => $payDate->format('Y-m-d H:i:s'),
                ];
            }

            DB::beginTransaction();
            try {
                foreach (array_chunk($transactionBatch, 500) as $chunk) {
                    Transaction::upsert(
                        $chunk,
                        ['invoice_id', 'payment_date'],
                        ['payment_method', 'amount_paid', 'status', 'updated_at']
                    );
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                foreach (array_chunk($transactionBatch, 500) as $chunk) {
                    Transaction::insertOrIgnore($chunk);
                }
            }

            $results['transactions_imported'] = count($transactionBatch);

            if ($progressCallback) {
                $progressCallback('transactions', $totalTx, $totalTx);
            }
        }

        // Sync PPPoE status_invoice based on active invoices
        $unpaidPppoeIds = Invoice::where('status', 'unpaid')->pluck('pppoe_id')->unique()->toArray();
        if (!empty($unpaidPppoeIds)) {
            Pppoe::whereIn('id', $unpaidPppoeIds)->update(['status_invoice' => 'belum_bayar']);
            Pppoe::whereNotIn('id', $unpaidPppoeIds)->update(['status_invoice' => 'terbayar']);
        }

        $results['time_elapsed'] = round(microtime(true) - $startTime, 2);
        return $results;
    }

    /**
     * Ensure default pakets exist and map them.
     */
    protected function ensureDefaultPakets(): array
    {
        $map = [];

        if (Paket::count() === 0) {
            foreach (self::DEFAULT_PAKETS as $p) {
                $paket = Paket::create($p);
                $map[strtolower($paket->nama_paket)] = $paket->id;
            }
        } else {
            foreach (Paket::all() as $p) {
                $map[strtolower(trim($p->nama_paket))] = $p->id;
            }
        }

        return $map;
    }

    /**
     * Sync and map pakets from Type 2 rows.
     */
    protected function syncPakets(array $type2Rows): array
    {
        $map = [];

        foreach ($type2Rows as $row) {
            $name = trim($row[1] ?? '');
            if (!$name) continue;

            $price = (float) ($row[2] ?? 0);
            $rateLimit = trim($row[3] ?? '');

            $paket = Paket::where('nama_paket', $name)->first();
            if (!$paket) {
                $paket = Paket::create([
                    'nama_paket' => $name,
                    'harga' => $price,
                    'rate_limit' => $rateLimit,
                    'keterangan' => 'Paket import dari Radboox',
                ]);
            } else {
                if ($price > 0 && ($paket->harga == 0 || !$paket->harga)) {
                    $paket->update(['harga' => $price, 'rate_limit' => $rateLimit ?: $paket->rate_limit]);
                }
            }

            $map[strtolower($name)] = $paket->id;
        }

        foreach (Paket::all() as $p) {
            $map[strtolower(trim($p->nama_paket))] = $p->id;
        }

        return $map;
    }

    /**
     * Resolve paket ID with smart fallback.
     */
    protected function resolvePaketId(string $name, array $paketMap): int
    {
        $n = strtolower(trim($name));
        if (isset($paketMap[$n])) return $paketMap[$n];

        if (strpos($n, 'bronze') !== false) {
            return $paketMap['paket bronze 112k'] ?? 1;
        }
        if (strpos($n, 'silver') !== false) {
            return $paketMap['paket silver 179k'] ?? 2;
        }
        if (strpos($n, 'gold') !== false) {
            return $paketMap['paket gold 239k'] ?? 4;
        }
        if (strpos($n, 'platinum') !== false) {
            return $paketMap['paket platinum 279k'] ?? 3;
        }

        return 1;
    }
}
