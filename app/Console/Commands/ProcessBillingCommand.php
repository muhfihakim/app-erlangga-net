<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Pppoe;
use App\Models\Radreply;
use App\Models\Radusergroup;
use App\Services\MikroTikService;
use App\Services\IsolirService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessBillingCommand extends Command
{
    protected $signature = 'billing:process 
                            {--force : Paksa generate invoice tanpa cek tanggal H-7}
                            {--only-isolir : Hanya periksa dan eksekusi isolir tagihan jatuh tempo}
                            {--only-billing : Hanya buat faktur baru tanpa eksekusi isolir}
                            {--kick : Paksa putus sesi MikroTik seluruh akun terisolir agar IP segera berganti ke pool isolir}
                            {--dry-run : Simulasi / preview tanpa mengubah database atau memutus koneksi pelanggan}';
    protected $description = 'Otomatisasi pembuatan faktur bulanan & isolir akun FreeRADIUS yang lewat jatuh tempo';

    public function handle(MikroTikService $mikrotikService, IsolirService $isolirService): int
    {
        $this->info("=========================================");
        $this->info("  MEMULAI PROSES BILLING & AUTO-ISOLIR  ");
        $this->info("=========================================");
        $this->info("Waktu Server: " . now()->format('Y-m-d H:i:s'));

        $isForce = (bool) $this->option('force');
        $onlyIsolir = (bool) $this->option('only-isolir');
        $onlyBilling = (bool) $this->option('only-billing');
        $forceKick = (bool) $this->option('kick');
        $isDryRun = (bool) $this->option('dry-run');

        if ($isDryRun) {
            $this->warn("[MODE DRY-RUN AKTIF] Tidak ada perubahan ke database atau MikroTik.");
        }

        // 1. Cek koneksi FreeRADIUS terlebih dahulu
        try {
            DB::connection('radius')->getPdo();
        } catch (\Throwable $e) {
            $config = config('database.connections.radius');
            $this->error("Koneksi ke database FreeRADIUS gagal: " . $e->getMessage());
            $this->warn("Pastikan DB_RADIUS_* sudah terkonfigurasi dengan benar di file .env server.");
            return Command::FAILURE;
        }

        // Pastikan attribute radgroupreply untuk ISOLIR lengkap
        if (!$isDryRun) {
            $isolirService->ensureIsolirGroupReply();
        }

        $generatedCount = 0;
        $newlyIsolatedCount = 0;
        $alreadyIsolatedCount = 0;

        // [1/2] Generate monthly invoices
        if (!$onlyIsolir) {
            $this->info("\n[1/2] Memeriksa akun untuk penerbitan faktur...");

            $pppoeAccounts = Pppoe::with('paket', 'invoices')->get();

            foreach ($pppoeAccounts as $pppoe) {
                if (!$pppoe->paket) {
                    continue;
                }

                $tipe = strtolower($pppoe->tipe_pembayaran ?? 'prepaid');
                $shouldGenerate = false;
                $dueDate = now()->addDays(7);

                if ($tipe === 'prepaid') {
                    $tanggalAktif = $pppoe->tanggal_aktif ? Carbon::parse($pppoe->tanggal_aktif) : now();
                    if ($isForce || $tanggalAktif->diffInDays(now(), false) >= -7) {
                        $hasUnpaid = $pppoe->invoices->where('status', 'unpaid')->isNotEmpty();
                        if (!$hasUnpaid) {
                            $shouldGenerate = true;
                            $dueDate = $tanggalAktif->isFuture() ? $tanggalAktif : now()->addDays(3);
                        }
                    }
                } else {
                    if ($isForce || now()->day === 1) {
                        $hasCurrentMonthInvoice = $pppoe->invoices->filter(function ($inv) {
                            return Carbon::parse($inv->created_at)->isCurrentMonth();
                        })->isNotEmpty();

                        if (!$hasCurrentMonthInvoice) {
                            $shouldGenerate = true;
                            $dueDate = now()->copy()->setDay(min(20, now()->daysInMonth));
                        }
                    }
                }

                if ($shouldGenerate) {
                    $amount = (float) $pppoe->paket->harga;
                    $tax = ($pppoe->pajak !== null && (float)$pppoe->pajak > 0) ? (float)$pppoe->pajak : (float)($pppoe->paket->pajak ?? 0);
                    $discount = ($pppoe->diskon !== null && (float)$pppoe->diskon > 0) ? (float)$pppoe->diskon : (float)($pppoe->paket->diskon ?? 0);
                    $total = max(0, $amount + $tax - $discount);

                    $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($pppoe->id, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999);

                    if (!$isDryRun) {
                        Invoice::create([
                            'pppoe_id' => $pppoe->id,
                            'invoice_number' => $invoiceNumber,
                            'amount' => $amount,
                            'tax' => $tax,
                            'discount' => $discount,
                            'total' => $total,
                            'due_date' => $dueDate->format('Y-m-d'),
                            'status' => 'unpaid',
                        ]);

                        $pppoe->update(['status_invoice' => 'belum_bayar']);
                    }

                    $generatedCount++;
                    $prefix = $isDryRun ? '  [DRY-RUN Faktur Baru] ' : '  [+] Faktur diterbitkan: ';
                    $this->line("{$prefix}{$pppoe->username} ({$invoiceNumber}) - Rp " . number_format($total, 0, ',', '.'));
                }
            }

            $this->info("Total Faktur Diterbitkan: {$generatedCount}");
        } else {
            $this->comment("\n[1/2] Penerbitan faktur dilewati (--only-isolir aktif)");
        }

        // [2/2] Process overdue isolir
        if (!$onlyBilling) {
            $this->info("\n[2/2] Memeriksa faktur jatuh tempo untuk Auto-Isolir...");

            $overdueInvoices = Invoice::with('pppoe.paket')
                ->where('status', 'unpaid')
                ->whereDate('due_date', '<', now()->toDateString())
                ->get();

            $processedUsers = [];

            foreach ($overdueInvoices as $inv) {
                $pppoe = $inv->pppoe;
                if (!$pppoe || isset($processedUsers[$pppoe->username])) {
                    continue;
                }

                $processedUsers[$pppoe->username] = true;
                $reason = "Auto-Isolir: Tagihan jatuh tempo ({$inv->due_date})";

                if ($pppoe->is_isolir) {
                    $alreadyIsolatedCount++;
                    if (!$isDryRun) {
                        // Jika ada flag --kick atau user masih memiliki Framed-IP-Address di radreply, paksa kick agar re-dial ke pool isolir
                        $hasStaticInRadius = Radreply::where('username', $pppoe->username)
                            ->where('attribute', 'Framed-IP-Address')
                            ->exists();
                        $shouldKick = $forceKick || $hasStaticInRadius;
                        $result = $isolirService->isolate($pppoe->username, $reason, $shouldKick);
                        $kicked = $result['kicked'] ?? false;
                        $this->line("  [-] SUDAH TERISOLIR: {$pppoe->username} (Jatuh tempo: {$inv->due_date})" . ($kicked ? " [MikroTik Session Reconnected -> Pool Isolir]" : ""));
                    } else {
                        $this->line("  [-] SUDAH TERISOLIR: {$pppoe->username} (Jatuh tempo: {$inv->due_date})");
                    }
                } else {
                    $newlyIsolatedCount++;
                    if ($isDryRun) {
                        $this->warn("  [DRY-RUN ISOLIR] Akan diisolir: {$pppoe->username} (Jatuh tempo: {$inv->due_date})");
                    } else {
                        try {
                            $result = $isolirService->isolate($pppoe->username, $reason, true);
                            $kicked = $result['kicked'] ?? false;
                            $this->warn("  [!] AKUN DIISOLIR: {$pppoe->username} (Jatuh tempo: {$inv->due_date})" . ($kicked ? " [MikroTik Session Disconnected -> Pool Isolir]" : ""));
                        } catch (\Throwable $e) {
                            $this->error("  [-] Gagal mengisolir {$pppoe->username}: " . $e->getMessage());
                            Log::error("Auto-isolir error ({$pppoe->username}): " . $e->getMessage());
                        }
                    }
                }
            }

            $this->info("Total Akun Baru Diisolir  : {$newlyIsolatedCount}");
            $this->info("Total Akun Sudah Terisolir: {$alreadyIsolatedCount}");
        } else {
            $this->comment("\n[2/2] Pemeriksaan isolir dilewati (--only-billing aktif)");
        }

        $this->info("\n=========================================");
        $this->info("  PROSES SELESAI ({$generatedCount} Faktur, {$newlyIsolatedCount} Baru Diisolir, {$alreadyIsolatedCount} Sudah Terisolir)  ");
        $this->info("=========================================");

        return Command::SUCCESS;
    }
}
