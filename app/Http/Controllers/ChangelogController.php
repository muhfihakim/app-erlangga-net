<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChangelogController extends Controller
{
    public function index(Request $request)
    {
        $milestones = [
            [
                'version' => 'v2.5.0',
                'title' => 'Standardisasi UI/UX Sistem, Icon-Only Opsi & Pembersihan Arsitektur',
                'date' => '2 November 2026',
                'badge' => 'Rilis Terbaru',
                'badge_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'highlight' => true,
                'summary' => 'Penyempurnaan tampilan antarmuka (UI/UX) di seluruh tabel modul operasional, penyeragaman tombol aksi (OPSI) menjadi icon-only dengan standar interaksi mikro yang konsisten, serta penghapusan modul traffic monitoring router yang usang.',
                'sections' => [
                    [
                        'name' => 'Penyelarasan & Standardisasi Desain',
                        'type' => 'design',
                        'badge' => 'UI / UX',
                        'badge_color' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'items' => [
                            'Standardisasi seluruh tombol aksi tabel (OPSI) menjadi icon-only seragam (`topbar-icon-btn !w-8 !h-8 !rounded-lg`) di modul Pre-Order, Waiting List, Pelanggan, Pemeliharaan, PPPoE, OLT, ODP, ACS, Invoice, Transaksi, Kasbon, dan Penarikan.',
                            'Penyeragaman warna tombol hapus destruktif di semua tabel menjadi abu-abu netral secara default (`text-slate-500`) dan berubah merah lembut saat di-hover (`hover:text-rose-600` dengan latar `hover:!bg-rose-50`).',
                            'Perampingan lebar kolom tabel (Status / Opsi) di berbagai modul untuk memberikan ruang maksimal pada data utama pelanggan dan jaringan.',
                            'Penyeragaman tombol CRUD di semua modal form (Simpan & Batal) dengan icon FontAwesome yang proporsional.',
                        ]
                    ],
                    [
                        'name' => 'Pembersihan & Refactoring Sistem',
                        'type' => 'refactor',
                        'badge' => 'Refactor',
                        'badge_color' => 'bg-slate-100 text-slate-700 border-slate-300',
                        'items' => [
                            'Penghapusan bersih tuntas modul Traffic Monitoring Router (`/monitoring`) dan API endpoint yang tidak efisien.',
                            'Pembersihan menu sidebar, navbar breadcrumbs, dan mobile navigation grid dari item monitoring router.',
                        ]
                    ]
                ]
            ],
            [
                'version' => 'v2.4.0',
                'title' => 'Sistem Prospek Tunggu (Waiting List Ekspansi) & Optimalisasi DataTables Server-Side',
                'date' => '25 - 31 Oktober 2026',
                'badge' => 'Fitur Utama',
                'badge_color' => 'bg-sky-50 text-sky-700 border-sky-200',
                'highlight' => false,
                'summary' => 'Implementasi alur penanganan calon pelanggan kendala lapangan (Unspek) ke antrean prospek tunggu ekspansi jaringan, autofill sinkronisasi kredensial aktivasi teknisi, dan optimalisasi beban loading awal tabel pemeliharaan.',
                'sections' => [
                    [
                        'name' => 'Fitur Baru',
                        'type' => 'feature',
                        'badge' => 'Fitur Baru',
                        'badge_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'items' => [
                            'Modul Prospek Tunggu (`/pre-orders/waiting-list`) untuk menampung calon pelanggan unspek yang menunggu perluasan jaringan fiber optik.',
                            'Alur verifikasi sales & approval admin untuk memasukkan calon pelanggan ke waiting list ekspansi atau mengembalikan antrean ke Pre-Order.',
                            'Tampilan filter default halaman Pre-Order berbasis data yang belum dibuatkan akun PPPoE.',
                        ]
                    ],
                    [
                        'name' => 'Peningkatan Kinerja & Integrasi',
                        'type' => 'perf',
                        'badge' => 'Optimasi',
                        'badge_color' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'items' => [
                            'Migrasi tabel Pemeliharaan (`/maintenances`) ke DataTables Server-Side murni dengan query teroptimasi sehingga pembukaan halaman awal instan dan ringan.',
                            'Sinkronisasi otomatis form aktivasi teknisi dengan data PPPoE yang telah disiapkan admin.',
                            'Pembersihan casing username PPPoE asli saat proses edit dan binding modem.',
                        ]
                    ]
                ]
            ],
            [
                'version' => 'v2.3.0',
                'title' => 'Otomasi Tagihan (Billing), Invoice, & Integrasi Xendit Payment Gateway',
                'date' => '15 - 24 Oktober 2026',
                'badge' => 'Sistem Billing',
                'badge_color' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'highlight' => false,
                'summary' => 'Ekosistem penagihan otomatis terintegrasi Xendit Payment Gateway (Virtual Account, QRIS, E-Wallet), modul invoice pelanggan mandiri, dan portal isolir pelanggan otomatis.',
                'sections' => [
                    [
                        'name' => 'Sistem Pembayaran & Tagihan',
                        'type' => 'feature',
                        'badge' => 'Billing & Keuangan',
                        'badge_color' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'items' => [
                            'Manajemen Invoice Pelanggan (`/invoices` dan `/invoices/{id}`) dengan perhitungan PPN, diskon, dan siklus tagihan fixed/prepaid.',
                            'Integrasi Xendit Payment Gateway untuk pembuatan tautan tagihan checkout online dan webhook sinkronisasi otomatis.',
                            'Dukungan pembayaran manual dengan pencatatan bukti transaksi dan cetak struk PDF faktur tagihan.',
                            'Portal Pelanggan Terisolir (`/isolir`) self-service untuk bayar mandiri langsung membuka isolir MikroTik seketika.',
                        ]
                    ],
                    [
                        'name' => 'Pencairan Dana & Transaksi',
                        'type' => 'feature',
                        'badge' => 'XenPlatform',
                        'badge_color' => 'bg-teal-50 text-teal-700 border-teal-200',
                        'items' => [
                            'Modul Transaksi Masuk (`/transactions`) dengan filter tanggal, status transaksi, dan metode bayar.',
                            'Modul Penarikan Dana Superadmin (`/withdrawals`) terhubung XenPlatform Disbursement dengan verifikasi OTP.',
                        ]
                    ]
                ]
            ],
            [
                'version' => 'v2.2.0',
                'title' => 'Manajemen Infrastruktur FTTH: OLT, ODP, & TR-069 GenieACS',
                'date' => '1 - 14 Oktober 2026',
                'badge' => 'Infrastruktur',
                'badge_color' => 'bg-amber-50 text-amber-700 border-amber-200',
                'highlight' => false,
                'summary' => 'Pusat komando terpadu untuk monitoring dan konfigurasi perangkat keras lapangan, mencakup Optical Line Terminal (OLT), Optical Distribution Point (ODP), dan modem pelanggan melalui Auto Configuration Server (ACS).',
                'sections' => [
                    [
                        'name' => 'Integrasi Perangkat Jaringan',
                        'type' => 'network',
                        'badge' => 'FTTH Hardware',
                        'badge_color' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'items' => [
                            'Dashboard OLT (`/olt`) dengan terminal SSH/Telnet interaktif, pengecekan ONU Next ID, dan telemetri sinyal optik (Rx/Tx Power).',
                            'Peta ODP (`/odp`) interaktif berbasis koordinat Google Maps dengan penghitung kapasitas port dan galeri foto lokasi.',
                            'Integrasi GenieACS TR-069 (`/genieacs`) untuk manajemen jarak jauh modem pelanggan: ubah SSID/Password Wi-Fi 2.4G & 5G, reboot modem, dan pantau host client.',
                        ]
                    ]
                ]
            ],
            [
                'version' => 'v2.1.0',
                'title' => 'FreeRADIUS Gateway, Siklus Hidup Pelanggan, & Audit Trail',
                'date' => 'September 2026',
                'badge' => 'Sistem Inti',
                'badge_color' => 'bg-blue-50 text-blue-700 border-blue-200',
                'highlight' => false,
                'summary' => 'Integrasi autentikasi jaringan FreeRADIUS terpusat, pengarsipan pelanggan non-aktif (soft delete / dismantle) yang aman, dan pencatatan audit log aktivitas seluruh staf.',
                'sections' => [
                    [
                        'name' => 'Autentikasi & Database Pelanggan',
                        'type' => 'auth',
                        'badge' => 'FreeRADIUS',
                        'badge_color' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'items' => [
                            'Sinkronisasi tabel FreeRADIUS (`radcheck`, `radreply`, `radusergroup`, `userinfo`) untuk manajemen user PPPoE berkecepatan tinggi.',
                            'Mekanisme isolir pelanggan otomatis via radius reply group `d_isolir` dengan redirect ke portal isolir.',
                            'Sistem daur hidup pelanggan lengkap: Aktivasi Baru -> Pelanggan Aktif -> Isolir -> Non-Aktif (Arsip) -> Hapus Permanen.',
                            'Pencatatan Log Aktivitas Audit Trail (`/activity-log`) untuk seluruh aksi tambah, ubah, dan hapus data staf.',
                        ]
                    ]
                ]
            ],
            [
                'version' => 'v2.0.0',
                'title' => 'Pondasi Sistem Erlangga Net Core, Modul Lapangan, & Multi-Role',
                'date' => 'Agustus 2026',
                'badge' => 'Fondasi v2.0',
                'badge_color' => 'bg-slate-100 text-slate-800 border-slate-300',
                'highlight' => false,
                'summary' => 'Arsitektur modular Laravel 11 dengan pemisahan peran hak akses ketat (Superadmin, Administrator, Teknisi, dan Sales), manajemen aset barang gudang, serta pencatatan kasbon teknisi.',
                'sections' => [
                    [
                        'name' => 'Fondasi Aplikasi & Modul Operasional',
                        'type' => 'core',
                        'badge' => 'Fondasi',
                        'badge_color' => 'bg-slate-100 text-slate-700 border-slate-300',
                        'items' => [
                            'Role Based Access Control (RBAC): Superadmin, Administrator, Teknisi Lapangan, dan Sales Marketing.',
                            'Modul Manajemen Gudang & Aset (`/assets`) dengan pencatatan mutasi barang masuk/keluar real-time.',
                            'Modul Kasbon Operasional Teknisi (`/finance/kasbon`) dengan upload bukti struk dan approval admin.',
                            'Modul Log Pendukung / Supporting (`/supporting`) untuk catatan maintenance berkala.',
                            'Fitur Backup & Restore Database CSV (`/migration`) untuk mitigasi bencana data.',
                        ]
                    ]
                ]
            ]
        ];

        return view('changelogs.index', compact('milestones'));
    }
}
