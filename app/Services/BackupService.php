<?php

namespace App\Services;

use App\Models\BackupLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Paket;
use App\Models\Pppoe;
use App\Models\PreOrder;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class BackupService
{
    protected string $storageDir;

    public function __construct()
    {
        $this->storageDir = storage_path('app/backups');
        if (!File::exists($this->storageDir)) {
            File::makeDirectory($this->storageDir, 0755, true);
        }
    }

    /**
     * Dapatkan informasi backup terakhir
     */
    public function getLastBackup(): ?BackupLog
    {
        return BackupLog::latest()->first();
    }

    /**
     * Dapatkan log riwayat backup terbaru
     */
    public function getRecentLogs(int $limit = 20)
    {
        return BackupLog::latest()->take($limit)->get();
    }

    /**
     * Backup Data Pelanggan & Akun PPPoE ke format .csv
     */
    public function exportCustomersPppoe(?User $user = null): array
    {
        $headers = [
            'ID Pelanggan',
            'Nama Lengkap',
            'No WhatsApp',
            'Email',
            'NIK',
            'Alamat Lengkap',
            'Titik Koordinat',
            'Username PPPoE',
            'Password PPPoE',
            'IP Address',
            'Nama Paket',
            'Harga Paket',
            'Router NAS',
            'Tipe Pembayaran',
            'Status Invoice',
            'Serial Number Perangkat (SN)',
            'Jenis Modem',
            'Teknisi Utama',
            'Sales',
            'Status Pelanggan',
            'Tanggal Registrasi',
        ];

        $pppoes = Pppoe::with('paket')->get()->keyBy('username');
        $customers = Customer::withTrashed()
            ->with(['preOrder', 'teknisiUtama', 'sales'])
            ->get();

        $rows = [];
        $handledUsernames = [];

        foreach ($customers as $c) {
            $po = $c->preOrder;
            $pppoeUsername = $c->pppoe_username ?? '';
            $pppoe = !empty($pppoeUsername) && isset($pppoes[$pppoeUsername]) ? $pppoes[$pppoeUsername] : null;

            if ($pppoeUsername) {
                $handledUsernames[$pppoeUsername] = true;
            }

            $rows[] = [
                $c->id_pelanggan ?? '-',
                $c->nama_lengkap ?? ($po ? $po->nama_lengkap : '-'),
                $c->no_whatsapp ?? ($po ? $po->no_whatsapp : '-'),
                $po ? ($po->alamat_email ?? '-') : '-',
                $po ? ($po->no_identitas ?? '-') : '-',
                $c->alamat_lengkap ?? ($po ? $po->alamat_lengkap : '-'),
                $po ? ($po->titik_koordinat ?? '-') : '-',
                $pppoeUsername ?: '-',
                $pppoe ? $pppoe->password : ($c->pppoe_password ?? '-'),
                $pppoe ? $pppoe->ip_address : '-',
                $pppoe && $pppoe->paket ? $pppoe->paket->nama_paket : '-',
                $pppoe && $pppoe->paket ? (string) $pppoe->paket->harga : '-',
                $pppoe ? $pppoe->router : 'All',
                $c->pembayaran ?? 'Cash',
                $pppoe ? $pppoe->status_invoice : 'belum_bayar',
                $c->sn_perangkat ?? '-',
                $c->jenis_modem ?? '-',
                $c->teknisiUtama ? $c->teknisiUtama->nama_lengkap : '-',
                $c->sales ? $c->sales->nama_lengkap : '-',
                $c->trashed() ? 'Non-Aktif' : 'Aktif',
                $c->created_at ? $c->created_at->format('Y-m-d H:i:s') : '-',
            ];
        }

        // Tambahkan akun PPPoE yang belum terhubung ke relasi tabel Customer
        foreach ($pppoes as $username => $p) {
            if (isset($handledUsernames[$username])) {
                continue;
            }

            $rows[] = [
                '-',
                $username,
                '-',
                '-',
                '-',
                '-',
                '-',
                $username,
                $p->password ?? '-',
                $p->ip_address ?? '-',
                $p->paket ? $p->paket->nama_paket : '-',
                $p->paket ? (string) $p->paket->harga : '-',
                $p->router ?? 'All',
                $p->tipe_pembayaran ?? 'prepaid',
                $p->status_invoice ?? 'belum_bayar',
                '-',
                '-',
                '-',
                '-',
                'Aktif',
                $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : '-',
            ];
        }

        $fileName = 'backup_pelanggan_pppoe_' . date('Y-m-d_His') . '.csv';
        return $this->saveAndLogCsv(
            'customers_pppoe',
            'Pelanggan & PPPoE',
            $fileName,
            $headers,
            $rows,
            $user
        );
    }

    /**
     * Backup Akun Pengguna Sistem ke format .csv
     */
    public function exportUsers(?User $user = null): array
    {
        $headers = [
            'ID User',
            'Username',
            'Nama Lengkap',
            'Email',
            'No WhatsApp',
            'Jabatan / Role',
            'NIK',
            'Alamat',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Status Akun',
            'Tanggal Dibuat',
        ];

        $users = User::withTrashed()->get();
        $rows = [];

        foreach ($users as $u) {
            $rows[] = [
                $u->id,
                explode('@', $u->email)[0],
                $u->nama_lengkap ?? '-',
                $u->email,
                $u->no_whatsapp ?? '-',
                $u->jabatan ?? 'User',
                $u->nik ?? '-',
                $u->alamat ?? '-',
                $u->jenis_kelamin ?? '-',
                $u->tanggal_lahir ? $u->tanggal_lahir->format('Y-m-d') : '-',
                $u->trashed() ? 'Dihapus (Soft Delete)' : ($u->is_active ? 'Aktif' : 'Non-Aktif'),
                $u->created_at ? $u->created_at->format('Y-m-d H:i:s') : '-',
            ];
        }

        $fileName = 'backup_akun_sistem_' . date('Y-m-d_His') . '.csv';
        return $this->saveAndLogCsv(
            'users',
            'Akun Sistem (Users)',
            $fileName,
            $headers,
            $rows,
            $user
        );
    }

    /**
     * Backup Data Tagihan & Transaksi Keuangan ke format .csv
     */
    public function exportFinance(?User $user = null): array
    {
        $headers = [
            'ID Transaksi',
            'Kode Transaksi',
            'No Invoice',
            'Username PPPoE',
            'Nama Pelanggan',
            'Nominal Pembayaran',
            'Biaya Admin',
            'Metode / Tipe Pembayaran',
            'Status Transaksi',
            'Tanggal Bayar',
            'Keterangan',
            'Tanggal Dibuat',
        ];

        $transactions = Transaction::with(['invoice', 'invoice.pppoe'])->get();
        $rows = [];

        foreach ($transactions as $t) {
            $inv = $t->invoice;
            $pppoe = $inv ? $inv->pppoe : null;

            $rows[] = [
                $t->id,
                $t->xendit_payment_id ?? '-',
                $inv ? ($inv->invoice_number ?? '-') : '-',
                $pppoe ? $pppoe->username : '-',
                $pppoe && $pppoe->customer ? ($pppoe->customer->nama_lengkap ?? '-') : '-',
                (string) ($t->amount_paid ?? 0),
                '0',
                $t->payment_method ?? 'cash',
                $t->status ?? 'success',
                $t->payment_date ? Carbon::parse($t->payment_date)->format('Y-m-d H:i:s') : '-',
                '-',
                $t->created_at ? $t->created_at->format('Y-m-d H:i:s') : '-',
            ];
        }

        $fileName = 'backup_keuangan_transaksi_' . date('Y-m-d_His') . '.csv';
        return $this->saveAndLogCsv(
            'finance',
            'Data Transaksi & Keuangan',
            $fileName,
            $headers,
            $rows,
            $user
        );
    }

    /**
     * Backup Daftar Paket Layanan ke format .csv
     */
    public function exportPakets(?User $user = null): array
    {
        $headers = [
            'ID Paket',
            'Nama Paket',
            'Kecepatan',
            'Harga',
            'Profil MikroTik',
            'Pajak (%)',
            'Diskon (Rp)',
            'Deskripsi',
            'Tanggal Dibuat',
        ];

        $pakets = Paket::all();
        $rows = [];

        foreach ($pakets as $p) {
            $rows[] = [
                $p->id,
                $p->nama_paket,
                $p->kecepatan ?? '-',
                (string) ($p->harga ?? 0),
                $p->profil_mikrotik ?? '-',
                (string) ($p->pajak ?? 0),
                (string) ($p->diskon ?? 0),
                $p->deskripsi ?? '-',
                $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : '-',
            ];
        }

        $fileName = 'backup_paket_layanan_' . date('Y-m-d_His') . '.csv';
        return $this->saveAndLogCsv(
            'pakets',
            'Paket Layanan',
            $fileName,
            $headers,
            $rows,
            $user
        );
    }

    /**
     * Backup Seluruh Data Sistem (Full Backup Master CSV)
     */
    public function exportFull(?User $user = null): array
    {
        $fileName = 'backup_seluruh_data_' . date('Y-m-d_His') . '.csv';
        $filePath = $this->storageDir . '/' . $fileName;

        $fp = fopen($filePath, 'w');
        // UTF-8 BOM for seamless Excel opening
        fwrite($fp, "\xEF\xBB\xBF");

        $totalRows = 0;

        // --- 1. PAKET LAYANAN ---
        fputcsv($fp, ['### TABEL: PAKET_LAYANAN ###'], ',', '"', "\\");
        $pHeaders = ['ID Paket', 'Nama Paket', 'Kecepatan', 'Harga', 'Profil MikroTik', 'Pajak', 'Diskon', 'Tanggal Dibuat'];
        fputcsv($fp, $pHeaders, ',', '"', "\\");
        $pakets = Paket::all();
        foreach ($pakets as $p) {
            fputcsv($fp, [
                $p->id,
                $p->nama_paket,
                $p->kecepatan ?? '-',
                $p->harga ?? 0,
                $p->profil_mikrotik ?? '-',
                $p->pajak ?? 0,
                $p->diskon ?? 0,
                $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : '-',
            ], ',', '"', "\\");
            $totalRows++;
        }
        fputcsv($fp, [], ',', '"', "\\");

        // --- 2. AKUN SISTEM (USERS) ---
        fputcsv($fp, ['### TABEL: AKUN_SISTEM (USERS) ###'], ',', '"', "\\");
        $uHeaders = ['ID User', 'Username', 'Nama Lengkap', 'Email', 'No WhatsApp', 'Jabatan', 'NIK', 'Status', 'Tanggal Dibuat'];
        fputcsv($fp, $uHeaders, ',', '"', "\\");
        $users = User::withTrashed()->get();
        foreach ($users as $u) {
            fputcsv($fp, [
                $u->id,
                explode('@', $u->email)[0],
                $u->nama_lengkap ?? '-',
                $u->email,
                $u->no_whatsapp ?? '-',
                $u->jabatan ?? 'User',
                $u->nik ?? '-',
                $u->trashed() ? 'Dihapus' : ($u->is_active ? 'Aktif' : 'Non-Aktif'),
                $u->created_at ? $u->created_at->format('Y-m-d H:i:s') : '-',
            ], ',', '"', "\\");
            $totalRows++;
        }
        fputcsv($fp, [], ',', '"', "\\");

        // --- 3. PELANGGAN & PPPOE ---
        fputcsv($fp, ['### TABEL: PELANGGAN_DAN_PPPOE ###'], ',', '"', "\\");
        $cHeaders = ['ID Pelanggan', 'Nama Lengkap', 'No WhatsApp', 'Alamat', 'Koordinat', 'Username PPPoE', 'Password PPPoE', 'IP Address', 'Paket', 'Harga', 'Router', 'Tipe Pembayaran', 'Status Invoice', 'SN Perangkat', 'Teknisi', 'Sales', 'Status', 'Tanggal Registrasi'];
        fputcsv($fp, $cHeaders, ',', '"', "\\");

        $pppoes = Pppoe::with('paket')->get()->keyBy('username');
        $customers = Customer::withTrashed()->with(['preOrder', 'teknisiUtama', 'sales'])->get();
        $handledUsernames = [];

        foreach ($customers as $c) {
            $po = $c->preOrder;
            $un = $c->pppoe_username ?? '';
            $pp = $un && isset($pppoes[$un]) ? $pppoes[$un] : null;
            if ($un) $handledUsernames[$un] = true;

            fputcsv($fp, [
                $c->id_pelanggan ?? '-',
                $c->nama_lengkap ?? ($po ? $po->nama_lengkap : '-'),
                $c->no_whatsapp ?? ($po ? $po->no_whatsapp : '-'),
                $c->alamat_lengkap ?? ($po ? $po->alamat_lengkap : '-'),
                $po ? ($po->titik_koordinat ?? '-') : '-',
                $un ?: '-',
                $pp ? $pp->password : ($c->pppoe_password ?? '-'),
                $pp ? $pp->ip_address : '-',
                $pp && $pp->paket ? $pp->paket->nama_paket : '-',
                $pp && $pp->paket ? $pp->paket->harga : '-',
                $pp ? $pp->router : 'All',
                $c->pembayaran ?? 'Cash',
                $pp ? $pp->status_invoice : 'belum_bayar',
                $c->sn_perangkat ?? '-',
                $c->teknisiUtama ? $c->teknisiUtama->nama_lengkap : '-',
                $c->sales ? $c->sales->nama_lengkap : '-',
                $c->trashed() ? 'Non-Aktif' : 'Aktif',
                $c->created_at ? $c->created_at->format('Y-m-d H:i:s') : '-',
            ], ',', '"', "\\");
            $totalRows++;
        }

        foreach ($pppoes as $username => $p) {
            if (isset($handledUsernames[$username])) continue;
            fputcsv($fp, [
                '-',
                $username,
                '-',
                '-',
                '-',
                $username,
                $p->password ?? '-',
                $p->ip_address ?? '-',
                $p->paket ? $p->paket->nama_paket : '-',
                $p->paket ? $p->paket->harga : '-',
                $p->router ?? 'All',
                $p->tipe_pembayaran ?? 'prepaid',
                $p->status_invoice ?? 'belum_bayar',
                '-',
                '-',
                '-',
                'Aktif',
                $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : '-',
            ], ',', '"', "\\");
            $totalRows++;
        }
        fputcsv($fp, [], ',', '"', "\\");

        // --- 4. FAKTUR INVOICE ---
        fputcsv($fp, ['### TABEL: FAKTUR_INVOICE ###'], ',', '"', "\\");
        $iHeaders = ['ID Invoice', 'Nomor Invoice', 'Username PPPoE', 'Periode', 'Jatuh Tempo', 'Total Tagihan', 'Diskon', 'Pajak', 'Status Tagihan', 'Tanggal Bayar', 'Tanggal Dibuat'];
        fputcsv($fp, $iHeaders, ',', '"', "\\");
        $invoices = Invoice::with('pppoe')->get();
        foreach ($invoices as $inv) {
            fputcsv($fp, [
                $inv->id,
                $inv->invoice_number ?? '-',
                $inv->pppoe ? $inv->pppoe->username : '-',
                $inv->due_date ? Carbon::parse($inv->due_date)->format('Y-m') : '-',
                $inv->due_date ?? '-',
                $inv->total ?? ($inv->amount ?? 0),
                $inv->discount ?? 0,
                $inv->tax ?? 0,
                $inv->status ?? 'unpaid',
                $inv->status === 'paid' ? ($inv->updated_at ? $inv->updated_at->format('Y-m-d H:i:s') : '-') : '-',
                $inv->created_at ? $inv->created_at->format('Y-m-d H:i:s') : '-',
            ], ',', '"', "\\");
            $totalRows++;
        }
        fputcsv($fp, [], ',', '"', "\\");

        // --- 5. LOG TRANSAKSI ---
        fputcsv($fp, ['### TABEL: LOG_TRANSAKSI ###'], ',', '"', "\\");
        $tHeaders = ['ID Transaksi', 'Kode Transaksi', 'ID Invoice', 'Pelanggan', 'Nominal', 'Biaya Admin', 'Tipe', 'Status', 'Tanggal Bayar', 'Tanggal Dibuat'];
        fputcsv($fp, $tHeaders, ',', '"', "\\");
        $transactions = Transaction::with(['invoice', 'invoice.pppoe'])->get();
        foreach ($transactions as $t) {
            $inv = $t->invoice;
            $pppoe = $inv ? $inv->pppoe : null;
            fputcsv($fp, [
                $t->id,
                $t->xendit_payment_id ?? '-',
                $inv ? ($inv->invoice_number ?? '-') : '-',
                $pppoe && $pppoe->customer ? ($pppoe->customer->nama_lengkap ?? '-') : '-',
                $t->amount_paid ?? 0,
                0,
                $t->payment_method ?? 'cash',
                $t->status ?? 'success',
                $t->payment_date ? Carbon::parse($t->payment_date)->format('Y-m-d H:i:s') : '-',
                $t->created_at ? $t->created_at->format('Y-m-d H:i:s') : '-',
            ], ',', '"', "\\");
            $totalRows++;
        }

        fclose($fp);

        $fileSize = file_exists($filePath) ? filesize($filePath) : 0;

        $log = BackupLog::create([
            'user_id' => $user ? $user->id : (auth()->check() ? auth()->id() : null),
            'user_name' => $user ? ($user->nama_lengkap ?? $user->name) : (auth()->check() ? (auth()->user()->nama_lengkap ?? auth()->user()->name) : 'Sistem'),
            'backup_type' => 'full',
            'backup_type_label' => 'Seluruh Data (Full Backup)',
            'file_name' => $fileName,
            'file_path' => 'backups/' . $fileName,
            'file_size' => $fileSize,
            'total_rows' => $totalRows,
            'notes' => 'Master CSV mencakup Paket, Users, Pelanggan, PPPoE, Invoice, dan Transaksi.',
        ]);

        return [
            'log' => $log,
            'path' => $filePath,
            'name' => $fileName,
        ];
    }

    /**
     * Helper untuk menulis array CSV dan menyimpan log
     */
    protected function saveAndLogCsv(
        string $type,
        string $label,
        string $fileName,
        array $headers,
        array $rows,
        ?User $user = null
    ): array {
        $filePath = $this->storageDir . '/' . $fileName;

        $fp = fopen($filePath, 'w');
        // UTF-8 BOM
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, $headers, ',', '"', "\\");

        foreach ($rows as $r) {
            fputcsv($fp, $r, ',', '"', "\\");
        }

        fclose($fp);

        $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
        $totalRows = count($rows);

        $log = BackupLog::create([
            'user_id' => $user ? $user->id : (auth()->check() ? auth()->id() : null),
            'user_name' => $user ? ($user->nama_lengkap ?? $user->name) : (auth()->check() ? (auth()->user()->nama_lengkap ?? auth()->user()->name) : 'Sistem'),
            'backup_type' => $type,
            'backup_type_label' => $label,
            'file_name' => $fileName,
            'file_path' => 'backups/' . $fileName,
            'file_size' => $fileSize,
            'total_rows' => $totalRows,
            'notes' => "Ekspor {$label} ke format .csv berhasil.",
        ]);

        return [
            'log' => $log,
            'path' => $filePath,
            'name' => $fileName,
        ];
    }
}
