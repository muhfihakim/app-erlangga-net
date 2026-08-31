<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Paket;
use App\Models\Pppoe;
use App\Models\PreOrder;
use App\Models\Transaction;
use App\Services\RadbooxImportService;
use Illuminate\Http\Request;

class MigrationController extends Controller
{
    protected RadbooxImportService $importService;

    public function __construct(RadbooxImportService $importService)
    {
        $this->importService = $importService;
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
        ];

        return view('migration.index', compact('dbStats'));
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
                'full_backup_file.required' => 'Silakan pilih file backup CSV lengkap Radboox terlebih dahulu.',
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
