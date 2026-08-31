<?php

namespace App\Console\Commands;

use App\Services\RadbooxImportService;
use Illuminate\Console\Command;

class ImportRadbooxBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-radboox 
                            {--file= : Path ke file backup CSV Radboox}
                            {--sync-radius : Sinkronisasi akun ke FreeRADIUS / daloRADIUS}
                            {--no-invoices : Jangan import invoice/faktur}
                            {--no-transactions : Jangan import transaksi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dan sinkronisasi data dari file backup CSV Radboox (Pelanggan, PPPoE, Faktur, Transaksi)';

    /**
     * Execute the console command.
     */
    public function handle(RadbooxImportService $importService)
    {
        $filePath = $this->option('file');

        if (!$filePath) {
            $defaultFile = base_path('backup/radboox_backup_8_31_2026 (1).csv');
            if (file_exists($defaultFile)) {
                $filePath = $defaultFile;
            } else {
                // Find any CSV in backup/
                $csvFiles = glob(base_path('backup/*.csv'));
                if (!empty($csvFiles)) {
                    $filePath = $csvFiles[0];
                } else {
                    $this->error('File backup CSV tidak ditemukan di folder backup/');
                    return 1;
                }
            }
        }

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: $filePath");
            return 1;
        }

        $this->info("=================================================");
        $this->info("     RADBOOX BACKUP MIGRATION & SYNC TOOL        ");
        $this->info("=================================================");
        $this->line("File: " . $filePath);

        $this->info("\nMenganalisis file backup...");
        $stats = $importService->analyze($filePath);

        $this->table(
            ['Entitas', 'Jumlah Record', 'Keterangan'],
            [
                ['Paket Layanan', $stats['pakets_count'], 'Paket internet'],
                ['Akun PPPoE & Pelanggan', $stats['pppoe_count'], 'Akun & Profil Pelanggan'],
                ['Faktur / Invoice', $stats['invoices_count'], 'Total: Rp ' . number_format($stats['total_invoice_amount'], 0, ',', '.')],
                ['Riwayat Transaksi', $stats['transactions_count'], 'Total: Rp ' . number_format($stats['total_transaction_amount'], 0, ',', '.')],
            ]
        );

        if (!$this->confirm('Apakah Anda yakin ingin memulai proses import & sinkronisasi data ke database?', true)) {
            $this->warn('Proses dibatalkan.');
            return 0;
        }

        $options = [
            'sync_radius' => $this->option('sync-radius'),
            'import_invoices' => !$this->option('no-invoices'),
            'import_transactions' => !$this->option('no-transactions'),
        ];

        $this->info("\nMemulai proses import data...");

        $bar = null;
        $currentPhase = '';

        $results = $importService->import($filePath, $options, function ($phase, $current, $total) use (&$bar, &$currentPhase) {
            if ($currentPhase !== $phase) {
                if ($bar) {
                    $bar->finish();
                    $this->newLine();
                }
                $currentPhase = $phase;
                $this->info("\n[Progress] Mengimpor $phase ($total records):");
                $bar = $this->output->createProgressBar($total);
                $bar->start();
            }
            if ($bar) {
                $bar->setProgress($current);
            }
        });

        if ($bar) {
            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $this->info("=================================================");
        $this->info("         MIGRASI DATA SELESAI DENGAN SUKSES       ");
        $this->info("=================================================");
        $this->table(
            ['Hasil Migrasi', 'Jumlah Berhasil'],
            [
                ['Paket Disinkronkan', $results['pakets_synced']],
                ['PreOrder Baru Dibuat', $results['preorders_created']],
                ['Customer Baru Dibuat', $results['customers_created']],
                ['PPPoE Baru Dibuat', $results['pppoe_created']],
                ['PPPoE Diperbarui', $results['pppoe_updated']],
                ['Faktur / Invoice Diimpor', $results['invoices_imported']],
                ['Transaksi Diimpor', $results['transactions_imported']],
                ['Radius Disinkronkan', $results['radius_synced']],
                ['Waktu Eksekusi', $results['time_elapsed'] . ' detik'],
            ]
        );

        if (!empty($results['errors'])) {
            $this->warn("\nCatatan / Peringatan (" . count($results['errors']) . "):");
            foreach (array_slice($results['errors'], 0, 10) as $err) {
                $this->line(" - $err");
            }
            if (count($results['errors']) > 10) {
                $this->line(" ... dan " . (count($results['errors']) - 10) . " lainnya.");
            }
        }

        return 0;
    }
}
