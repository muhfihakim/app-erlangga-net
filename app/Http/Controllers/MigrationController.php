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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\Facades\DataTables;

class MigrationController extends Controller
{
    protected RadbooxImportService $importService;
    protected BackupService $backupService;

    public function __construct(RadbooxImportService $importService, BackupService $backupService)
    {
        $this->importService = $importService;
        $this->backupService = $backupService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = BackupLog::query()->orderBy('created_at', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('created_at', function ($log) {
                    if (!$log->created_at) {
                        return '-';
                    }
                    $carbon = Carbon::parse($log->created_at)->locale('id');
                    $waktu = $carbon->translatedFormat('d F Y, H:i') . ' WIB';
                    $diff = $carbon->diffForHumans();
                    return '<div class="font-bold text-slate-800 text-xs">' . htmlspecialchars($waktu) . '</div>' .
                           '<div class="text-[10px] text-slate-400 mt-0.5">' . htmlspecialchars($diff) . '</div>';
                })
                ->editColumn('backup_type', function ($log) {
                    $label = htmlspecialchars($log->backup_type_label ?? $log->backup_type);
                    $bg = '#f1f5f9';
                    $color = '#64748b';
                    $border = '#e2e8f0';

                    if ($log->backup_type === 'full') {
                        $bg = '#e0e7ff';
                        $color = '#4338ca';
                        $border = '#c7d2fe';
                    } elseif ($log->backup_type === 'customers_pppoe') {
                        $bg = '#ecfdf5';
                        $color = '#059669';
                        $border = '#a7f3d0';
                    } elseif ($log->backup_type === 'users') {
                        $bg = '#fef3c7';
                        $color = '#d97706';
                        $border = '#fde68a';
                    } elseif ($log->backup_type === 'finance') {
                        $bg = '#f0f9ff';
                        $color = '#0284c7';
                        $border = '#bae6fd';
                    }

                    return '<span class="badge text-[10px] font-bold py-0.5 px-2" style="background:' . $bg . ';color:' . $color . ';border:1px solid ' . $border . ';">' . $label . '</span>';
                })
                ->editColumn('file_name', function ($log) {
                    $fileName = htmlspecialchars($log->file_name);
                    $html = '<div class="font-mono text-xs font-semibold text-slate-800 truncate max-w-xs" title="' . $fileName . '">' . $fileName . '</div>';
                    if ($log->notes) {
                        $html .= '<div class="text-[11px] text-slate-400 truncate max-w-xs" title="' . htmlspecialchars($log->notes) . '">' . htmlspecialchars($log->notes) . '</div>';
                    }
                    return $html;
                })
                ->editColumn('total_rows', function ($log) {
                    return '<span class="font-bold text-slate-700 text-xs">' . number_format($log->total_rows) . '</span>';
                })
                ->editColumn('formatted_size', function ($log) {
                    return '<span class="font-mono text-xs text-slate-600">' . htmlspecialchars($log->formatted_size) . '</span>';
                })
                ->addColumn('user_name', function ($log) {
                    return '<span class="text-xs text-slate-700 font-medium">' . htmlspecialchars($log->user_name ?: 'Sistem') . '</span>';
                })
                ->addColumn('aksi', function ($log) {
                    $downloadUrl = route('migration.backup.download', $log->id);
                    $safeFileName = addslashes(htmlspecialchars($log->file_name));
                    return '<div class="flex items-center justify-center gap-1.5">
                        <a href="' . $downloadUrl . '" class="btn-primary-admin !h-7 !px-2.5 !text-[11px] !bg-indigo-600 hover:!bg-indigo-700 font-semibold shadow-xs">Unduh</a>
                        <button type="button" onclick="confirmDeleteBackup(' . $log->id . ', \'' . $safeFileName . '\')" class="btn-cancel !h-7 !px-2.5 !text-[11px] hover:!text-rose-600 hover:!border-rose-300 font-semibold">Hapus</button>
                    </div>';
                })
                ->rawColumns(['created_at', 'backup_type', 'file_name', 'total_rows', 'formatted_size', 'user_name', 'aksi'])
                ->make(true);
        }

        $dbStats = [
            'preorders' => PreOrder::count(),
            'customers' => Customer::count(),
            'pppoes' => Pppoe::count(),
            'invoices' => Invoice::count(),
            'transactions' => Transaction::count(),
            'pakets' => Paket::count(),
            'users' => User::count(),
        ];

        $totalBackupLogs = BackupLog::count();

        return view('migration.index', compact('dbStats', 'totalBackupLogs'));
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

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Riwayat log backup berhasil dihapus.']);
        }

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
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya Administrator yang diizinkan mereset database.');
        }

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
