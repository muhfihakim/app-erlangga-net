<?php

namespace App\Http\Controllers;

use App\Models\BackupLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Paket;
use App\Models\Pppoe;
use App\Models\PreOrder;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BackupService;
use App\Services\RadbooxImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MigrationController extends Controller
{
    protected RadbooxImportService $importService;
    protected BackupService $backupService;

    public function __construct(RadbooxImportService $importService, BackupService $backupService)
    {
        $this->importService = $importService;
        $this->backupService = $backupService;
    }

    public function index()
    {
        $dbStats = [
            'preorders' => PreOrder::count(),
            'customers' => Customer::count(),
            'pppoes' => Pppoe::count(),
            'invoices' => Invoice::count(),
            'transactions' => Transaction::count(),
            'pakets' => Paket::count(),
            'users' => User::count(),
        ];

        $lastBackup = $this->backupService->getLastBackup();
        $backupLogs = $this->backupService->getRecentLogs(20);

        return view('migration.index', compact('dbStats', 'lastBackup', 'backupLogs'));
    }

    /**
     * Ekspor dan unduh file backup CSV sesuai opsi yang dipilih
     */
    public function backup(Request $request, string $type)
    {
        $user = auth()->user();

        try {
            switch ($type) {
                case 'full':
                    $result = $this->backupService->exportFull($user);
                    break;
                case 'customers_pppoe':
                    $result = $this->backupService->exportCustomersPppoe($user);
                    break;
                case 'users':
                    $result = $this->backupService->exportUsers($user);
                    break;
                case 'finance':
                    $result = $this->backupService->exportFinance($user);
                    break;
                case 'pakets':
                    $result = $this->backupService->exportPakets($user);
                    break;
                default:
                    return redirect()->back()->with('error', 'Kategori backup tidak dikenal.');
            }

            return response()->download($result['path'], $result['name'], [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal memproses backup: ' . $e->getMessage());
        }
    }

    /**
     * Unduh file backup dari riwayat log
     */
    public function downloadBackup(int $id)
    {
        $log = BackupLog::findOrFail($id);
        $fullPath = storage_path('app/' . $log->file_path);

        if (!File::exists($fullPath)) {
            return redirect()->back()->with('error', 'File backup tidak ditemukan di server atau sudah dibersihkan.');
        }

        return response()->download($fullPath, $log->file_name, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Hapus riwayat log dan file backup
     */
    public function deleteBackup(int $id)
    {
        $log = BackupLog::findOrFail($id);
        $fullPath = storage_path('app/' . $log->file_path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }

        $log->delete();

        return redirect()->back()->with('success', 'Riwayat log backup berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $importType = $request->input('import_type', 'full');
        $filePath = null;

        if ($importType === 'pppoe_only') {
            $request->validate([
                'pppoe_file' => 'required|file|mimes:xlsx,csv,txt|max:51200', // max 50MB
            ], [
                'pppoe_file.required' => 'Silakan pilih file Excel (.xlsx) atau CSV akun PPPoE terlebih dahulu.',
                'pppoe_file.mimes' => 'Format file harus berupa .xlsx atau .csv.',
            ]);

            $file = $request->file('pppoe_file');
            $filename = 'pppoe_upload_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(base_path('backup'), $filename);
            $filePath = base_path('backup/' . $filename);

            $options = [
                'sync_radius' => $request->has('sync_radius'),
                'import_invoices' => false,
                'import_transactions' => false,
            ];
        } else {
            $request->validate([
                'full_backup_file' => 'required|file|mimes:csv,txt|max:102400', // max 100MB
            ], [
                'full_backup_file.required' => 'Silakan pilih file backup CSV lengkap terlebih dahulu.',
                'full_backup_file.mimes' => 'Format file backup lengkap harus berupa file .csv.',
            ]);

            $file = $request->file('full_backup_file');
            $filename = 'full_backup_upload_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(base_path('backup'), $filename);
            $filePath = base_path('backup/' . $filename);

            $options = [
                'sync_radius' => $request->has('sync_radius'),
                'import_invoices' => $request->has('import_invoices'),
                'import_transactions' => $request->has('import_transactions'),
            ];
        }

        if (!$filePath || !file_exists($filePath)) {
            return redirect()->back()->with('error', 'File yang dipilih gagal diunggah atau tidak ditemukan.');
        }

        try {
            set_time_limit(600); // 10 minutes limit for large files
            $results = $this->importService->import($filePath, $options);

            if ($importType === 'pppoe_only') {
                $msg = "Import Akun PPPoE Berhasil! Memproses {$results['pppoe_created']} Akun Baru, {$results['pppoe_updated']} Akun Diperbarui, dan {$results['radius_synced']} Akun Radius dalam {$results['time_elapsed']} detik.";
            } else {
                $msg = "Import Full Backup Berhasil! Memproses {$results['pppoe_created']} Akun Baru, {$results['pppoe_updated']} Akun Diperbarui, {$results['invoices_imported']} Faktur, dan {$results['transactions_imported']} Transaksi dalam {$results['time_elapsed']} detik.";
            }

            return redirect()->route('migration.index')->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function reset(Request $request)
    {
        try {
            $results = $this->importService->resetData([
                'clear_radius' => true,
                'recreate_pakets' => true,
            ]);

            $msg = "Data berhasil dikosongkan! Telah dihapus: {$results['pppoes_cleared']} Akun PPPoE, {$results['invoices_cleared']} Invoice, {$results['transactions_cleared']} Transaksi, dan {$results['pakets_cleared']} Paket lama. 4 Paket resmi standar telah dibuat kembali.";
            return redirect()->route('migration.index')->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membersihkan data: ' . $e->getMessage());
        }
    }
}
