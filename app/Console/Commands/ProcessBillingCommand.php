<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Pppoe;
use App\Models\Radusergroup;
use App\Services\MikroTikService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessBillingCommand extends Command
{
    protected $signature = 'billing:process {--force : Paksa generate invoice tanpa cek tanggal H-7}';
    protected $description = 'Otomatisasi pembuatan faktur bulanan & isolir akun FreeRADIUS yang lewat jatuh tempo';

    public function handle(MikroTikService $mikrotikService): int
    {
        $this->info("=========================================");
        $this->info("  MEMULAI PROSES BILLING & AUTO-ISOLIR  ");
        $this->info("=========================================");
        $this->info("Waktu Server: " . now()->format('Y-m-d H:i:s'));

        $isForce = $this->option('force');
        $generatedCount = 0;
        $isolatedCount = 0;

        // -------------------------------------------------------------
        // BAGIAN 1: AUTO-GENERATE FAKTUR BULANAN
        // -------------------------------------------------------------
        $this->info("\n[1/2] Memeriksa akun untuk penerbitan faktur...");

        $pppoeAccounts = Pppoe::with('paket', 'invoices')->get();

        foreach ($pppoeAccounts as $pppoe) {
            // Cek apakah paket valid
            if (!$pppoe->paket) {
                continue;
            }

            $tipe = strtolower($pppoe->tipe_pembayaran ?? 'prepaid');
            $shouldGenerate = false;
            $dueDate = now()->addDays(7);

            if ($tipe === 'prepaid') {
                // Prepaid: terbitkan jika masa aktif habis dalam H-7 atau sudah expired
                $tanggalAktif = $pppoe->tanggal_aktif ? Carbon::parse($pppoe->tanggal_aktif) : now();
                if ($isForce || $tanggalAktif->diffInDays(now(), false) >= -7) {
                    // Cek apakah sudah ada invoice unpaid aktif
                    $hasUnpaid = $pppoe->invoices->where('status', 'unpaid')->isNotEmpty();
                    if (!$hasUnpaid) {
                        $shouldGenerate = true;
                        $dueDate = $tanggalAktif->isFuture() ? $tanggalAktif : now()->addDays(3);
                    }
                }
            } else {
                // Postpaid: terbitkan tiap tanggal 1 atau jika dipaksa (--force)
                if ($isForce || now()->day === 1) {
                    $hasCurrentMonthInvoice = $pppoe->invoices->filter(function ($inv) {
                        return Carbon::parse($inv->created_at)->isCurrentMonth();
                    })->isNotEmpty();

                    if (!$hasCurrentMonthInvoice) {
                        $shouldGenerate = true;
                        // Jatuh tempo tanggal 20 bulan berjalan
                        $dueDate = now()->copy()->setDay(min(20, now()->daysInMonth));
                    }
                }
            }

            if ($shouldGenerate) {
                $amount = (float) $pppoe->paket->harga;
                $tax = (float) ($pppoe->pajak ?? 0);
                $discount = (float) ($pppoe->diskon ?? 0);
                $total = max(0, $amount + $tax - $discount);

                $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($pppoe->id, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999);

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
                $generatedCount++;

                $this->line("  [+] Faktur diterbitkan: {$pppoe->username} ({$invoiceNumber}) - Rp " . number_format($total, 0, ',', '.'));
            }
        }

        $this->info("Total Faktur Diterbitkan: {$generatedCount}");

        // -------------------------------------------------------------
        // BAGIAN 2: AUTO-ISOLIR OVERDUE INVOICES
        // -------------------------------------------------------------
        $this->info("\n[2/2] Memeriksa faktur jatuh tempo untuk Auto-Isolir...");

        $overdueInvoices = Invoice::with('pppoe.paket')
            ->where('status', 'unpaid')
            ->whereDate('due_date', '<', now()->toDateString())
            ->get();

        $isolatedUsers = [];

        foreach ($overdueInvoices as $inv) {
            $pppoe = $inv->pppoe;
            if (!$pppoe || in_array($pppoe->username, $isolatedUsers)) {
                continue;
            }

            try {
                // 1. Update FreeRADIUS radusergroup to ISOLIR
                try {
                    Radusergroup::where('username', $pppoe->username)->update(['groupname' => 'ISOLIR']);
                } catch (\Throwable $e) {
                    Log::warning("Gagal update FreeRADIUS group ISOLIR untuk {$pppoe->username}: " . $e->getMessage());
                }

                // 2. Update status_invoice di DB Laravel
                $pppoe->update(['status_invoice' => 'belum_bayar']);

                // 3. Kick active session on MikroTik agar langsung dialihkan ke profile isolir
                $kicked = $mikrotikService->kickPppoeUser($pppoe->username);

                $isolatedUsers[] = $pppoe->username;
                $isolatedCount++;

                $this->warn("  [!] AKUN DIISOLIR: {$pppoe->username} (Jatuh tempo: {$inv->due_date})" . ($kicked ? " [MikroTik Session Disconnected]" : ""));
            } catch (\Throwable $e) {
                $this->error("  [-] Gagal mengisolir {$pppoe->username}: " . $e->getMessage());
                Log::error("Auto-isolir error ({$pppoe->username}): " . $e->getMessage());
            }
        }

        $this->info("Total Akun Diisolir: {$isolatedCount}");

        $this->info("\n=========================================");
        $this->info("  PROSES SELESAI ({$generatedCount} Faktur Baru, {$isolatedCount} Diisolir)  ");
        $this->info("=========================================");

        return Command::SUCCESS;
    }
}
